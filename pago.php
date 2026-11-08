<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/mercadopago.php';

$user = require_login();
$code = (string) ($_GET['o'] ?? '');
$st = db()->prepare('SELECT * FROM orders WHERE code = ?');
$st->execute([$code]);
$order = $st->fetch();
if (!$order || ($order['user_id'] != $user['id'] && !is_admin())) {
    http_response_code(404);
    exit('Pedido no encontrado');
}

$items = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
$items->execute([$order['id']]);
$items = $items->fetchAll();

// Reintentar pago con Mercado Pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'retry' && $order['status'] === 'pendiente' && mp_enabled()) {
    csrf_check();
    try {
        redirect(mp_create_preference($order, $items, $user));
    } catch (Throwable $e) {
        error_log('[retry] ' . $e->getMessage());
        flash('Mercado Pago no respondió. Intenta otra vez en unos minutos o paga por WhatsApp.', 'err');
        redirect('pago.php?o=' . urlencode($code));
    }
}

// Volvimos de Mercado Pago: verificamos el pago directamente con su API
$paymentId = (string) ($_GET['payment_id'] ?? $_GET['collection_id'] ?? '');
if ($paymentId !== '' && $paymentId !== 'null' && mp_enabled()) {
    try {
        $order = mp_sync_payment($paymentId) ?: $order;
    } catch (Throwable $e) {
        error_log('[pago] ' . $e->getMessage());
    }
}

$paid = in_array($order['status'], ['pagado', 'preparando', 'enviado', 'entregado'], true);
$mpStatus = $order['mp_status'] ?? '';

// Evento de compra para Google Analytics (una sola vez por pedido)
if ($paid && empty($_SESSION['ga_sent'][$order['code']])) {
    $_SESSION['ga_sent'][$order['code']] = true;
    ga_event('purchase', [
        'transaction_id' => $order['code'], 'currency' => 'PEN', 'value' => (float) $order['total'], 'shipping' => (float) $order['shipping'],
        'items' => array_map(fn($i) => ['item_id' => (string) $i['variant_id'], 'item_name' => $i['name'], 'item_variant' => $i['variant_label'], 'price' => (float) $i['price'], 'quantity' => (int) $i['qty']], $items),
    ]);
}

$waText = "Hola Quinto Lumière, acabo de hacer el pedido {$order['code']}:\n";
foreach ($items as $i) $waText .= "• {$i['qty']} × {$i['name']} ({$i['variant_label']}) — " . money($i['price'] * $i['qty']) . "\n";
$waText .= "Envío: " . money($order['shipping']) . "\nTotal: " . money($order['total']) . "\n";
$waText .= $order['ship_method'] === 'recojo' ? "Recojo en tienda.\n" : "Entrega en: {$order['ship_address']}, {$order['ship_district']}\n";
$waText .= $paid ? 'Pago realizado con Mercado Pago ✅' : '¿Me envían los datos para pagar por Yape/Plin? 🙏';

page_header('Pedido ' . $order['code']);
?>
<div class="wrap">
  <div class="auth" style="max-width:640px">
    <?= sun_svg(80) ?>
    <?php if ($paid): ?>
      <h1>¡Gracias por tu compra!</h1>
      <p style="text-align:center">Tu pago fue aprobado. Ya estamos preparando tu pedido <strong><?= e($order['code']) ?></strong>.</p>
    <?php elseif ($order['status'] === 'cancelado'): ?>
      <h1>Pedido cancelado</h1>
      <p style="text-align:center">El pedido <strong><?= e($order['code']) ?></strong> fue cancelado. Si crees que es un error, escríbenos.</p>
    <?php elseif ($order['payment_method'] === 'whatsapp'): ?>
      <h1>¡Pedido recibido!</h1>
      <p style="text-align:center">Tu pedido <strong><?= e($order['code']) ?></strong> está reservado. Envíanos el mensaje por WhatsApp para recibir los datos de Yape, Plin o transferencia.</p>
    <?php elseif ($mpStatus === 'in_process' || $mpStatus === 'pending'): ?>
      <h1>Pago en proceso</h1>
      <p style="text-align:center">Mercado Pago está confirmando tu pago (por ejemplo, si elegiste PagoEfectivo). Te avisaremos apenas se acredite.</p>
    <?php else: ?>
      <h1>Tu pago no se completó</h1>
      <p style="text-align:center">No te preocupes: tu pedido <strong><?= e($order['code']) ?></strong> está guardado. Puedes intentar de nuevo o pagar por Yape/Plin.</p>
    <?php endif; ?>

    <div class="panel" style="margin-top:22px">
      <?php foreach ($items as $i): ?>
        <div class="totals" style="margin-bottom:6px"><div><span><?= (int) $i['qty'] ?> × <?= e($i['name']) ?> <small class="muted">(<?= e($i['variant_label']) ?>)</small></span><span><?= money($i['price'] * $i['qty']) ?></span></div></div>
      <?php endforeach; ?>
      <div class="totals" style="margin-top:12px;padding-top:10px;border-top:1px solid var(--line)">
        <div><span>Envío</span><span><?= money($order['shipping']) ?></span></div>
        <div class="total"><span>Total</span><span><?= money($order['total']) ?></span></div>
      </div>
      <p class="muted" style="margin:14px 0 0;font-size:.88rem">Estado: <strong><?= e(order_status_label($order['status'])) ?></strong></p>
    </div>

    <div style="display:grid;gap:10px;margin-top:20px">
      <?php if (!$paid && $order['status'] === 'pendiente' && mp_enabled()): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="retry">
          <button class="btn btn--block" type="submit">Pagar con Mercado Pago</button></form>
      <?php endif; ?>
      <a class="btn <?= $paid ? 'btn--ghost' : '' ?> btn--block" href="<?= e(whatsapp_link($waText)) ?>" target="_blank" rel="noopener"><?= icon('wa', 18) ?> <?= $paid ? 'Escribirnos por WhatsApp' : 'Enviar pedido por WhatsApp' ?></a>
      <a class="btn btn--ghost btn--block" href="<?= url('cuenta.php') ?>">Ver mis pedidos</a>
    </div>
  </div>
</div>
<?php page_footer(); ?>
