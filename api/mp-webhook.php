<?php
/**
 * Mercado Pago avisa aquí cada vez que cambia un pago.
 * Configura esta URL en tu panel de Mercado Pago → Webhooks → evento "Pagos":
 *   https://TU-DOMINIO/api/mp-webhook.php
 */
require dirname(__DIR__) . '/inc/bootstrap.php';
require dirname(__DIR__) . '/inc/mercadopago.php';

$body = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
$type = $_GET['type'] ?? $_GET['topic'] ?? ($body['type'] ?? $body['topic'] ?? '');
$id   = (string) ($_GET['data_id'] ?? $_GET['id'] ?? ($body['data']['id'] ?? ''));

if ($type !== 'payment' || $id === '' || !mp_enabled()) {
    http_response_code(200);
    exit('ignored');
}
if (!mp_valid_signature($id)) {
    http_response_code(401);
    exit('bad signature');
}
try {
    mp_sync_payment($id);
    http_response_code(200);
    echo 'ok';
} catch (Throwable $e) {
    error_log('[mp-webhook] ' . $e->getMessage());
    http_response_code(500); // Mercado Pago reintentará
    echo 'error';
}
