<?php
require __DIR__ . '/inc/bootstrap.php';

$user = require_login();

// Actualiza con Mercado Pago los pedidos pendientes recientes de esta clienta
require __DIR__ . '/inc/mercadopago.php';
if (mp_enabled()) {
    $p = db()->prepare("SELECT code FROM orders WHERE user_id = ? AND payment_method = 'mercadopago' AND status = 'pendiente' AND created_at >= ? ORDER BY id DESC LIMIT 3");
    $p->execute([$user['id'], date('Y-m-d H:i:s', strtotime('-7 days'))]);
    foreach ($p->fetchAll(PDO::FETCH_COLUMN) as $code) {
        try { mp_sync_order($code); } catch (Throwable $e) { error_log('[cuenta] ' . $e->getMessage()); }
    }
}

$st = db()->prepare('SELECT o.*, (SELECT SUM(qty) FROM order_items i WHERE i.order_id = o.id) AS n FROM orders o WHERE user_id = ? ORDER BY id DESC');
$st->execute([$user['id']]);
$orders = $st->fetchAll();
$pill = ['pendiente' => 'warn', 'pagado' => 'ok', 'preparando' => 'info', 'enviado' => 'info', 'entregado' => 'ok', 'cancelado' => 'err'];

page_header('Mis pedidos');
?>
<div class="wrap" style="padding:36px 0 80px">
  <div class="admin__head">
    <div><p class="eyebrow">Mi cuenta</p><h1 style="font-size:2.4rem;margin:0">Hola, <?= e(explode(' ', $user['name'])[0]) ?></h1></div>
    <?php if ($user['role'] === 'admin'): ?><a class="btn" href="<?= url('admin/') ?>">Ir al panel de administración</a><?php endif; ?>
  </div>
  <?php if (!$orders): ?>
    <div class="empty"><h2>Aún no tienes pedidos</h2><a class="btn" href="<?= url('tienda.php') ?>">Empezar a comprar</a></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="t">
        <thead><tr><th>Pedido</th><th>Fecha</th><th>Productos</th><th>Estado</th><th class="num">Total</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><strong><?= e($o['code']) ?></strong></td>
            <td><?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td>
            <td><?= (int) $o['n'] ?></td>
            <td><span class="pill pill--<?= $pill[$o['status']] ?? 'info' ?>"><?= e(order_status_label($o['status'])) ?></span></td>
            <td class="num"><?= money($o['total']) ?></td>
            <td><a href="<?= url('pago.php?o=' . urlencode($o['code'])) ?>">Ver</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php page_footer(); ?>
