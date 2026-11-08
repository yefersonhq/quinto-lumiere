<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
require dirname(__DIR__) . '/inc/mercadopago.php';
require_admin();

$pdo = db();
$states = ['pendiente', 'pagado', 'preparando', 'enviado', 'entregado', 'cancelado'];
$pill = ['pendiente' => 'warn', 'pagado' => 'ok', 'preparando' => 'info', 'enviado' => 'info', 'entregado' => 'ok', 'cancelado' => 'err'];

// Verificar pagos con Mercado Pago (para hostings que no reciben los avisos automáticos)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(post('action'), ['mp_check', 'mp_check_all'], true)) {
    csrf_check();
    if (!mp_enabled()) { flash('Mercado Pago no está configurado.', 'err'); redirect('admin/pedidos.php'); }
    try {
        if (post('action') === 'mp_check_all') {
            $n = mp_sync_pending();
            flash($n ? "$n pedido(s) confirmados como pagados." : 'No hay pagos nuevos aprobados en Mercado Pago.');
            redirect('admin/pedidos.php');
        }
        $st = $pdo->prepare('SELECT code FROM orders WHERE id = ?');
        $st->execute([(int) post('id')]);
        $o = mp_sync_order((string) $st->fetchColumn());
        flash($o ? 'Mercado Pago: ' . ($o['mp_status'] ?: 'sin estado') . ' → ' . order_status_label($o['status']) : 'Mercado Pago aún no registra pagos para este pedido.', $o ? 'ok' : 'info');
    } catch (Throwable $e) {
        flash('No se pudo consultar Mercado Pago: ' . $e->getMessage(), 'err');
    }
    redirect('admin/pedidos.php?ver=' . (int) post('id'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $oid = (int) post('id');
    $new = post('status');
    if (in_array($new, $states, true)) {
        $st = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $st->execute([$oid]);
        $o = $st->fetch();
        if ($o) {
            $pdo->prepare('UPDATE orders SET status = ?, paid_at = COALESCE(paid_at, ?) WHERE id = ?')
                ->execute([$new, in_array($new, ['pagado', 'preparando', 'enviado', 'entregado'], true) ? now() : null, $oid]);
            // al confirmar el pago (Yape/Plin/transferencia) se descuenta el stock
            if (in_array($new, ['pagado', 'preparando', 'enviado', 'entregado'], true)) apply_order_stock($oid);
            // si se cancela un pedido que ya descontó stock, se devuelve
            if ($new === 'cancelado' && $o['stock_applied']) {
                $items = $pdo->prepare('SELECT variant_id, qty FROM order_items WHERE order_id = ?');
                $items->execute([$oid]);
                $up = $pdo->prepare('UPDATE product_variants SET stock = stock + ? WHERE id = ?');
                foreach ($items->fetchAll() as $it) $up->execute([$it['qty'], $it['variant_id']]);
                $pdo->prepare('UPDATE orders SET stock_applied = 0 WHERE id = ?')->execute([$oid]);
            }
            flash('Pedido ' . $o['code'] . ': ' . order_status_label($new) . '.');
        }
    }
    redirect('admin/pedidos.php?ver=' . $oid);
}

$ver = (int) ($_GET['ver'] ?? 0);
$filter = (string) ($_GET['estado'] ?? '');
$q = trim((string) ($_GET['q'] ?? ''));

page_header('Pedidos', ['admin' => true]);

if ($ver):
    $st = $pdo->prepare('SELECT o.*, u.name AS customer, u.email, u.phone AS user_phone FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?');
    $st->execute([$ver]);
    $o = $st->fetch();
    $items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $items->execute([$ver]);
    $items = $items->fetchAll();
    if (!$o) { echo '<div class="wrap admin"><p>Pedido no encontrado.</p></div>'; page_footer(['admin' => true]); exit; }
    $method = ['recojo' => 'Recojo en tienda', 'lima' => 'Delivery Lima', 'provincia' => 'Envío a provincia'][$o['ship_method']] ?? $o['ship_method'];
    $wa = preg_replace('/\D/', '', $o['ship_phone']);
    if (strlen($wa) === 9) $wa = '51' . $wa;
?>
<div class="wrap admin">
  <p class="crumbs" style="padding-top:0"><a href="<?= url('admin/pedidos.php') ?>">← Pedidos</a></p>
  <div class="admin__head">
    <h1>Pedido <?= e($o['code']) ?></h1>
    <span class="pill pill--<?= $pill[$o['status']] ?>" style="font-size:.9rem"><?= e(order_status_label($o['status'])) ?></span>
  </div>
  <div class="grid-2">
    <div>
      <div class="table-wrap">
        <table class="t">
          <thead><tr><th>Producto</th><th class="num">Precio</th><th class="num">Cant.</th><th class="num">Total</th></tr></thead>
          <tbody>
          <?php foreach ($items as $i): ?>
            <tr><td><?= e($i['name']) ?><br><small class="muted"><?= e($i['variant_label']) ?></small></td><td class="num"><?= money($i['price']) ?></td><td class="num"><?= (int) $i['qty'] ?></td><td class="num"><?= money($i['price'] * $i['qty']) ?></td></tr>
          <?php endforeach; ?>
            <tr><td colspan="3" class="num">Subtotal</td><td class="num"><?= money($o['subtotal']) ?></td></tr>
            <tr><td colspan="3" class="num">Envío</td><td class="num"><?= money($o['shipping']) ?></td></tr>
            <tr><td colspan="3" class="num"><strong>Total</strong></td><td class="num"><strong><?= money($o['total']) ?></strong></td></tr>
          </tbody>
        </table>
      </div>
      <?php if ($o['notes']): ?><div class="panel" style="margin-top:16px"><strong>Notas del cliente</strong><p style="margin:6px 0 0"><?= nl2br(e($o['notes'])) ?></p></div><?php endif; ?>
    </div>
    <div>
      <section class="panel">
        <h2 style="font-size:1.4rem">Cliente y entrega</h2>
        <p><strong><?= e($o['ship_name']) ?></strong><br><?= e($o['email']) ?><br><?= e($o['ship_phone']) ?></p>
        <p><strong><?= e($method) ?></strong><?php if ($o['ship_method'] !== 'recojo'): ?><br><?= e($o['ship_address']) ?><br><?= e($o['ship_district']) ?><?= $o['ship_ref'] ? '<br><small class="muted">Ref: ' . e($o['ship_ref']) . '</small>' : '' ?><?php endif; ?></p>
        <p class="muted" style="font-size:.88rem">Creado: <?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?><?= $o['paid_at'] ? '<br>Pagado: ' . e(date('d/m/Y H:i', strtotime($o['paid_at']))) : '' ?><br>
          Pago: <?= $o['payment_method'] === 'mercadopago' ? 'Mercado Pago' . ($o['mp_payment_id'] ? ' #' . e($o['mp_payment_id']) . ' (' . e($o['mp_status']) . ')' : '') : 'Yape / Plin / transferencia (WhatsApp)' ?></p>
        <?php if ($wa): ?><a class="btn btn--ghost btn--sm" target="_blank" rel="noopener" href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode('Hola ' . explode(' ', $o['ship_name'])[0] . ', te escribimos de Quinto Lumière por tu pedido ' . $o['code'] . '.') ?>"><?= icon('wa', 16) ?> Escribir al cliente</a><?php endif; ?>
      </section>
      <?php if ($o['payment_method'] === 'mercadopago' && mp_enabled()): ?>
      <section class="panel">
        <h2 style="font-size:1.4rem">Mercado Pago</h2>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="mp_check"><input type="hidden" name="id" value="<?= $o['id'] ?>">
          <button class="btn btn--ghost btn--block">Verificar pago en Mercado Pago</button></form>
        <small class="muted">Consulta directamente a Mercado Pago si este pedido ya fue pagado.</small>
      </section>
      <?php endif; ?>
      <section class="panel">
        <h2 style="font-size:1.4rem">Cambiar estado</h2>
        <form method="post" class="form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $o['id'] ?>">
          <select class="select" name="status">
            <?php foreach ($states as $s): ?><option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e(order_status_label($s)) ?></option><?php endforeach; ?>
          </select>
          <button class="btn btn--block">Actualizar</button>
          <small class="muted">Al marcar «Pagado» se descuenta el stock. Al cancelar un pedido pagado, el stock se devuelve.</small>
        </form>
      </section>
    </div>
  </div>
</div>
<?php
else:
    $where = ['1=1']; $args = [];
    if (in_array($filter, $states, true)) { $where[] = 'o.status = ?'; $args[] = $filter; }
    if ($q !== '') { $where[] = '(o.code LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR o.ship_phone LIKE ?)'; array_push($args, "%$q%", "%$q%", "%$q%", "%$q%"); }
    $st = $pdo->prepare('SELECT o.*, u.name AS customer FROM orders o JOIN users u ON u.id = o.user_id WHERE ' . implode(' AND ', $where) . ' ORDER BY o.id DESC LIMIT 300');
    $st->execute($args);
    $orders = $st->fetchAll();
?>
<div class="wrap admin">
  <div class="admin__head"><h1>Pedidos</h1>
    <?php if (mp_enabled()): ?>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="mp_check_all">
        <button class="btn btn--ghost btn--sm" title="Consulta en Mercado Pago los pedidos pendientes de los últimos 7 días">Verificar pagos pendientes</button></form>
    <?php endif; ?>
  </div>
  <form class="toolbar">
    <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Código, cliente, correo o celular">
    <select class="select" name="estado" onchange="this.form.submit()">
      <option value="">Todos los estados</option>
      <?php foreach ($states as $s): ?><option value="<?= $s ?>" <?= $filter === $s ? 'selected' : '' ?>><?= e(order_status_label($s)) ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn--ghost btn--sm">Buscar</button>
  </form>
  <div class="table-wrap">
    <table class="t">
      <thead><tr><th>Pedido</th><th>Fecha</th><th>Cliente</th><th>Entrega</th><th>Pago</th><th>Estado</th><th class="num">Total</th></tr></thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><a href="?ver=<?= $o['id'] ?>"><strong><?= e($o['code']) ?></strong></a></td>
          <td><?= e(date('d/m H:i', strtotime($o['created_at']))) ?></td>
          <td><?= e($o['customer']) ?></td>
          <td><?= e(['recojo' => 'Recojo', 'lima' => 'Lima', 'provincia' => 'Provincia'][$o['ship_method']] ?? '') ?></td>
          <td><?= $o['payment_method'] === 'mercadopago' ? 'Mercado Pago' : 'WhatsApp' ?></td>
          <td><span class="pill pill--<?= $pill[$o['status']] ?? 'info' ?>"><?= e(order_status_label($o['status'])) ?></span></td>
          <td class="num"><?= money($o['total']) ?></td>
        </tr>
      <?php endforeach; if (!$orders): ?><tr><td colspan="7" class="muted">No hay pedidos.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif;
page_footer(['admin' => true]);
