<?php
require dirname(__DIR__) . '/inc/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Método no permitido'], 405);
csrf_check();

$ajax = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
$action = post('action');
$vid = (int) post('variant');
$qty = max(0, (int) post('qty', 1));
$cart = cart();
$msg = '';

$stockOf = function (int $vid): ?array {
    $st = db()->prepare('SELECT v.stock, v.label, p.name, p.active FROM product_variants v JOIN products p ON p.id = v.product_id WHERE v.id = ?');
    $st->execute([$vid]);
    return $st->fetch() ?: null;
};

if ($action === 'add') {
    $v = $stockOf($vid);
    if (!$v || !$v['active']) json_out(['error' => 'Producto no disponible'], 404);
    $want = ($cart[$vid] ?? 0) + max(1, $qty);
    if ($want > $v['stock']) {
        $want = (int) $v['stock'];
        $msg = $v['stock'] > 0 ? "Solo quedan {$v['stock']} unidades" : 'Producto agotado';
    }
    if ($want > 0) $cart[$vid] = $want;
    $msg = $msg ?: 'Agregado al carrito';
} elseif ($action === 'set') {
    $v = $stockOf($vid);
    if ($qty === 0 || !$v) unset($cart[$vid]);
    else $cart[$vid] = min($qty, (int) $v['stock']);
} elseif ($action === 'remove') {
    unset($cart[$vid]);
} elseif ($action === 'clear') {
    $cart = [];
}
$_SESSION['cart'] = array_filter($cart);

if ($ajax) {
    $lines = cart_lines();
    json_out([
        'ok' => true,
        'message' => $msg,
        'count' => cart_count(),
        'subtotal' => array_sum(array_column($lines, 'line_total')),
    ]);
}
if ($msg) flash($msg);
redirect('carrito.php');
