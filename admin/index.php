<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
require dirname(__DIR__) . '/inc/mercadopago.php';
require_admin();

$pdo = db();

// Revisa en Mercado Pago los pedidos pendientes (cada 10 minutos como máximo),
// por si el hosting no recibe los avisos automáticos.
if (mp_enabled() && (time() - ($_SESSION['mp_synced_at'] ?? 0)) > 600) {
    $_SESSION['mp_synced_at'] = time();
    if ($n = mp_sync_pending(7, 15)) flash("Mercado Pago confirmó $n pago(s) nuevo(s).");
}
$paidStates = "('pagado','preparando','enviado','entregado')";
$month = date('Y-m-01 00:00:00');
$stats = [
    'ventas_mes'  => (float) $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status IN $paidStates AND created_at >= '$month'")->fetchColumn(),
    'pedidos_mes' => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN $paidStates AND created_at >= '$month'")->fetchColumn(),
    'por_atender' => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pendiente','pagado','preparando')")->fetchColumn(),
    'productos'   => (int) $pdo->query('SELECT COUNT(*) FROM products WHERE active = 1')->fetchColumn(),
    'clientes'    => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'cliente'")->fetchColumn(),
    'reclamos'    => (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'abierto'")->fetchColumn(),
];
$low = $pdo->query("SELECT v.id, v.label, v.stock, p.name, p.id AS pid FROM product_variants v JOIN products p ON p.id = v.product_id
                    WHERE p.active = 1 AND v.stock <= 3 ORDER BY v.stock, p.name LIMIT 12")->fetchAll();
$recent = $pdo->query('SELECT o.*, u.name AS customer FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC LIMIT 8')->fetchAll();
$top = $pdo->query("SELECT i.name, SUM(i.qty) AS n FROM order_items i JOIN orders o ON o.id = i.order_id
                    WHERE o.status IN $paidStates GROUP BY i.name ORDER BY n DESC LIMIT 6")->fetchAll();
$since = date('Y-m-d H:i:s', strtotime('-30 days'));
$searches = $pdo->prepare('SELECT q, COUNT(*) AS n, MAX(results) AS r FROM searches WHERE created_at >= ? GROUP BY q ORDER BY n DESC LIMIT 10');
$searches->execute([$since]);
$searches = $searches->fetchAll();
$missing = $pdo->prepare('SELECT q, COUNT(*) AS n FROM searches WHERE results = 0 AND created_at >= ? GROUP BY q ORDER BY n DESC LIMIT 10');
$missing->execute([$since]);
$missing = $missing->fetchAll();
$untouched = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE created_at = updated_at')->fetchColumn();
$noPhoto = (int) $pdo->query('SELECT COUNT(*) FROM products p WHERE active = 1 AND NOT EXISTS (SELECT 1 FROM product_images i WHERE i.product_id = p.id)')->fetchColumn();
$pill = ['pendiente' => 'warn', 'pagado' => 'ok', 'preparando' => 'info', 'enviado' => 'info', 'entregado' => 'ok', 'cancelado' => 'err'];

page_header('Panel', ['admin' => true]);
?>
<div class="wrap admin">
  <div class="admin__head">
    <h1>Resumen</h1>
    <a class="btn" href="<?= url('admin/producto.php') ?>"><?= icon('plus', 16) ?> Nuevo producto</a>
  </div>

  <?php if (!mp_enabled()): ?>
    <p class="notice notice--warn" style="margin-bottom:12px"><strong>Mercado Pago aún no está conectado.</strong> Mientras tanto, los pedidos se coordinan por WhatsApp (Yape/Plin). Agrega tus credenciales en <code>config.php</code>.</p>
  <?php endif; ?>
  <?php if (!cfg('ga_measurement_id')): ?>
    <p class="notice notice--warn" style="margin-bottom:12px"><strong>Google Analytics no está conectado.</strong> Pega tu ID de medición (G-XXXXXXX) en <code>config.php</code>.</p>
  <?php endif; ?>
  <?php if ($untouched > 0): ?>
    <p class="notice" style="margin-bottom:12px"><strong><?= $untouched ?> productos tienen precios y stock de ejemplo.</strong> Revísalos en <a href="<?= url('admin/productos.php') ?>">Inventario</a> antes de vender.</p>
  <?php endif; ?>
  <?php if ($noPhoto > 0): ?>
    <p class="notice" style="margin-bottom:20px"><?= $noPhoto ?> productos aún no tienen foto. <a href="<?= url('admin/productos.php?sinfoto=1') ?>">Ver cuáles</a></p>
  <?php endif; ?>

  <div class="stats">
    <div class="stat"><small>Ventas del mes</small><b><?= money($stats['ventas_mes']) ?></b></div>
    <div class="stat"><small>Pedidos pagados (mes)</small><b><?= $stats['pedidos_mes'] ?></b></div>
    <div class="stat"><small>Pedidos por atender</small><b><?= $stats['por_atender'] ?></b></div>
    <div class="stat"><small>Productos activos</small><b><?= $stats['productos'] ?></b></div>
    <div class="stat"><small>Clientes</small><b><?= $stats['clientes'] ?></b></div>
    <div class="stat"><small>Reclamos abiertos</small><b><?= $stats['reclamos'] ?></b></div>
  </div>

  <div class="grid-2">
    <div>
      <h2 style="font-size:1.6rem">Últimos pedidos</h2>
      <div class="table-wrap">
        <table class="t">
          <thead><tr><th>Pedido</th><th>Cliente</th><th>Estado</th><th class="num">Total</th></tr></thead>
          <tbody>
          <?php foreach ($recent as $o): ?>
            <tr><td><a href="<?= url('admin/pedidos.php?ver=' . $o['id']) ?>"><?= e($o['code']) ?></a></td><td><?= e($o['customer']) ?></td>
              <td><span class="pill pill--<?= $pill[$o['status']] ?? 'info' ?>"><?= e(order_status_label($o['status'])) ?></span></td><td class="num"><?= money($o['total']) ?></td></tr>
          <?php endforeach; if (!$recent): ?><tr><td colspan="4" class="muted">Aún no hay pedidos.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>

      <h2 style="font-size:1.6rem;margin-top:28px">Lo que buscan tus clientes <small class="muted" style="font-size:.9rem">(30 días)</small></h2>
      <div class="table-wrap">
        <table class="t">
          <thead><tr><th>Búsqueda</th><th class="num">Veces</th><th class="num">Resultados</th></tr></thead>
          <tbody>
          <?php foreach ($searches as $s): ?>
            <tr><td><?= e($s['q']) ?></td><td class="num"><?= (int) $s['n'] ?></td><td class="num"><?= (int) $s['r'] ?: '<span class="pill pill--err">0</span>' ?></td></tr>
          <?php endforeach; if (!$searches): ?><tr><td colspan="3" class="muted">Sin búsquedas todavía.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ($missing): ?>
        <p class="notice" style="margin-top:12px"><strong>Buscaron y no encontraron:</strong> <?= e(implode(', ', array_column($missing, 'q'))) ?>. Agrégalos al inventario o añade esas palabras en las <em>etiquetas</em> del producto.</p>
      <?php endif; ?>
    </div>

    <div>
      <h2 style="font-size:1.6rem">Stock bajo</h2>
      <div class="table-wrap">
        <table class="t">
          <thead><tr><th>Producto</th><th class="num">Stock</th></tr></thead>
          <tbody>
          <?php foreach ($low as $l): ?>
            <tr><td><a href="<?= url('admin/producto.php?id=' . $l['pid']) ?>"><?= e($l['name']) ?></a><br><small class="muted"><?= e($l['label']) ?></small></td>
              <td class="num"><span class="pill pill--<?= $l['stock'] <= 0 ? 'err' : 'warn' ?>"><?= (int) $l['stock'] ?></span></td></tr>
          <?php endforeach; if (!$low): ?><tr><td colspan="2" class="muted">Todo con stock suficiente.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>

      <h2 style="font-size:1.6rem;margin-top:28px">Más vendidos</h2>
      <div class="panel bars">
        <?php $max = max(1, ...array_map('intval', array_column($top, 'n') ?: [1]));
        foreach ($top as $t): ?>
          <div class="bar"><span><?= e(mb_strimwidth($t['name'], 0, 22, '…')) ?></span><i style="width:<?= round($t['n'] / $max * 100) ?>%"></i><span class="num"><?= (int) $t['n'] ?></span></div>
        <?php endforeach; if (!$top): ?><p class="muted" style="margin:0">Aparecerán cuando tengas ventas pagadas.</p><?php endif; ?>
      </div>
      <p class="muted" style="font-size:.85rem;margin-top:14px">Las estadísticas de visitas (de dónde vienen, qué páginas ven, conversiones) están en <a href="https://analytics.google.com/" target="_blank" rel="noopener">Google Analytics</a>.</p>
    </div>
  </div>
</div>
<?php page_footer(['admin' => true]); ?>
