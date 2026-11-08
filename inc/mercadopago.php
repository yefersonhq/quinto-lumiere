<?php
/**
 * Integración con Mercado Pago Checkout Pro (Perú).
 * Docs: https://www.mercadopago.com.pe/developers/es/docs/checkout-pro
 */

function mp_enabled(): bool
{
    return trim((string) cfg('mercadopago.access_token')) !== '';
}

function mp_request(string $method, string $path, ?array $body = null): array
{
    $ch = curl_init('https://api.mercadopago.com' . $path);
    $headers = [
        'Authorization: Bearer ' . cfg('mercadopago.access_token'),
        'Content-Type: application/json',
    ];
    if ($method === 'POST') $headers[] = 'X-Idempotency-Key: ' . bin2hex(random_bytes(16));
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_CONNECTTIMEOUT => 8,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($raw === false) throw new RuntimeException('No se pudo conectar con Mercado Pago: ' . $err);
    $data = json_decode($raw, true) ?: [];
    if ($code >= 400) throw new RuntimeException('Mercado Pago respondió ' . $code . ': ' . ($data['message'] ?? $raw));
    return $data;
}

/** Crea la preferencia de pago y devuelve la URL a la que se redirige al cliente. */
function mp_create_preference(array $order, array $items, array $user): string
{
    $mpItems = [];
    foreach ($items as $it) {
        $mpItems[] = [
            'id'          => (string) $it['variant_id'],
            'title'       => mb_substr($it['name'] . ($it['variant_label'] ? ' — ' . $it['variant_label'] : ''), 0, 250),
            'quantity'    => (int) $it['qty'],
            'unit_price'  => round((float) $it['price'], 2),
            'currency_id' => 'PEN',
        ];
    }
    if ((float) $order['shipping'] > 0) {
        $mpItems[] = ['id' => 'envio', 'title' => 'Envío', 'quantity' => 1, 'unit_price' => round((float) $order['shipping'], 2), 'currency_id' => 'PEN'];
    }

    $return = site_url('pago.php?o=' . urlencode($order['code']));
    $public = str_starts_with(site_url(), 'https://');
    $body = [
        'items'              => $mpItems,
        'payer'              => ['name' => $user['name'], 'email' => $user['email']],
        'external_reference' => $order['code'],
        'back_urls'          => ['success' => $return, 'failure' => $return, 'pending' => $return],
        'statement_descriptor' => 'QUINTOLUMIERE',
        'metadata'           => ['order_code' => $order['code']],
    ];
    // Mercado Pago solo acepta auto-retorno y avisos (webhook) en sitios públicos con https
    if ($public) {
        $body['auto_return'] = 'approved';
        $body['notification_url'] = site_url('api/mp-webhook.php?source_news=webhooks');
    }
    $pref = mp_request('POST', '/checkout/preferences', $body);
    db()->prepare('UPDATE orders SET mp_preference_id = ? WHERE id = ?')->execute([$pref['id'] ?? null, $order['id']]);
    return $pref['init_point'] ?? $pref['sandbox_init_point'];
}

/**
 * Consulta un pago en Mercado Pago y actualiza el pedido.
 * Nunca confiamos en lo que llega por la URL: siempre preguntamos a Mercado Pago.
 */
function mp_sync_payment(string $paymentId): ?array
{
    if (!ctype_digit($paymentId)) return null;
    $pay = mp_request('GET', '/v1/payments/' . $paymentId);
    $code = (string) ($pay['external_reference'] ?? '');
    $st = db()->prepare('SELECT * FROM orders WHERE code = ?');
    $st->execute([$code]);
    $order = $st->fetch();
    if (!$order) return null;

    $status = (string) ($pay['status'] ?? '');
    db()->prepare('UPDATE orders SET mp_payment_id = ?, mp_status = ? WHERE id = ?')->execute([$paymentId, $status, $order['id']]);

    $amountOk = abs((float) ($pay['transaction_amount'] ?? 0) - (float) $order['total']) < 0.02
        && ($pay['currency_id'] ?? 'PEN') === 'PEN';

    if ($status === 'approved' && $amountOk && $order['status'] === 'pendiente') {
        db()->prepare("UPDATE orders SET status = 'pagado', paid_at = ? WHERE id = ? AND status = 'pendiente'")->execute([now(), $order['id']]);
        apply_order_stock((int) $order['id']);
    } elseif (in_array($status, ['refunded', 'charged_back'], true)) {
        db()->prepare("UPDATE orders SET status = 'cancelado' WHERE id = ?")->execute([$order['id']]);
    }
    $st->execute([$code]);
    return $st->fetch();
}

/**
 * Busca en Mercado Pago los pagos de un pedido (por su código) y actualiza el estado.
 * Sirve cuando el hosting no recibe los avisos automáticos (webhooks).
 */
function mp_sync_order(string $code): ?array
{
    $res = mp_request('GET', '/v1/payments/search?' . http_build_query([
        'external_reference' => $code, 'sort' => 'date_created', 'criteria' => 'desc', 'limit' => 10,
    ]));
    $pays = $res['results'] ?? [];
    if (!$pays) return null;
    // si hay uno aprobado, ese manda; si no, el más reciente
    $pick = current(array_filter($pays, fn($p) => ($p['status'] ?? '') === 'approved')) ?: $pays[0];
    return mp_sync_payment((string) $pick['id']);
}

/** Revisa los pedidos de Mercado Pago aún pendientes de los últimos días. Devuelve cuántos pasaron a pagado. */
function mp_sync_pending(int $days = 7, int $max = 25): int
{
    $st = db()->prepare("SELECT code FROM orders WHERE payment_method = 'mercadopago' AND status = 'pendiente' AND created_at >= ? ORDER BY id DESC LIMIT $max");
    $st->execute([date('Y-m-d H:i:s', strtotime("-$days days"))]);
    $n = 0;
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $code) {
        try {
            $o = mp_sync_order($code);
            if ($o && $o['status'] === 'pagado') $n++;
        } catch (Throwable $e) {
            error_log('[mp_sync_pending] ' . $e->getMessage());
            break; // si Mercado Pago no responde, no insistimos con los demás
        }
    }
    return $n;
}

/** Verifica la firma secreta del webhook (si configuraste webhook_secret). */
function mp_valid_signature(string $dataId): bool
{
    $secret = (string) cfg('mercadopago.webhook_secret');
    if ($secret === '') return true;
    $sig = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
    $reqId = $_SERVER['HTTP_X_REQUEST_ID'] ?? '';
    $parts = [];
    foreach (explode(',', $sig) as $kv) {
        [$k, $v] = array_map('trim', explode('=', $kv, 2) + [1 => '']);
        $parts[$k] = $v;
    }
    if (empty($parts['ts']) || empty($parts['v1'])) return false;
    $manifest = 'id:' . strtolower($dataId) . ';request-id:' . $reqId . ';ts:' . $parts['ts'] . ';';
    return hash_equals(hash_hmac('sha256', $manifest, $secret), $parts['v1']);
}
