<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = post('action');
    if ($action === 'toggle') {
        $pdo->prepare('UPDATE products SET active = 1 - active, updated_at = ? WHERE id = ?')->execute([now(), (int) post('id')]);
        flash('Visibilidad actualizada.');
    } elseif ($action === 'delete') {
        $id = (int) post('id');
        foreach (product_images($id) as $img) @unlink(ROOT . '/' . $img['path']);
        $pdo->prepare('DELETE FROM product_images WHERE product_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM product_variants WHERE product_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        flash('Producto eliminado.');
    } elseif ($action === 'bulk') {
        $up = $pdo->prepare('UPDATE product_variants SET stock = ?, price = ? WHERE id = ?');
        $touch = $pdo->prepare('UPDATE products SET updated_at = ? WHERE id = (SELECT product_id FROM product_variants WHERE id = ?)');
        $n = 0;
        foreach ((array) ($_POST['v'] ?? []) as $vid => $row) {
            $stock = max(0, (int) ($row['stock'] ?? 0));
            $price = max(0, round((float) str_replace(',', '.', (string) ($row['price'] ?? 0)), 2));
            $up->execute([$stock, $price, (int) $vid]);
            $touch->execute([now(), (int) $vid]);
            $n++;
        }
        flash("Inventario guardado ($n variantes).");
    }
    $back = $_SERVER['HTTP_REFERER'] ?? '';
    if (parse_url($back, PHP_URL_HOST) !== parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
        $back = url('admin/productos.php');
    }
    header('Location: ' . $back);
    exit;
}

$q = trim((string) ($_GET['q'] ?? ''));
$cat = (int) ($_GET['cat'] ?? 0);
$view = ($_GET['vista'] ?? '') === 'stock' ? 'stock' : 'lista';
$where = ['1=1'];
$args = [];
if ($q !== '') { $where[] = '(p.name LIKE ? OR p.tags LIKE ? OR b.name LIKE ?)'; array_push($args, "%$q%", "%$q%", "%$q%"); }
if ($cat) { $where[] = 'p.category_id = ?'; $args[] = $cat; }
if (!empty($_GET['sinfoto'])) $where[] = 'NOT EXISTS (SELECT 1 FROM product_images i WHERE i.product_id = p.id)';
if (!empty($_GET['bajo'])) $where[] = 'EXISTS (SELECT 1 FROM product_variants v WHERE v.product_id = p.id AND v.stock <= 3)';
$st = $pdo->prepare(product_query(implode(' AND ', $where), 'c.sort, p.name'));
$st->execute($args);
$products = $st->fetchAll();

$variants = [];
if ($view === 'stock' && $products) {
    $ids = array_column($products, 'id');
    $in = implode(',', array_map('intval', $ids));
    foreach ($pdo->query("SELECT * FROM product_variants WHERE product_id IN ($in) ORDER BY sort, id")->fetchAll() as $v) {
        $variants[$v['product_id']][] = $v;
    }
}
$cats = categories();
$qs = fn(array $c) => url('admin/productos.php?' . http_build_query(array_filter(array_merge($_GET, $c), fn($v) => $v !== '' && $v !== null)));

page_header('Inventario', ['admin' => true]);
?>
<div class="wrap admin">
  <div class="admin__head">
    <h1>Inventario <small class="muted" style="font-size:1rem">(<?= count($products) ?>)</small></h1>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a class="btn btn--ghost btn--sm" href="<?= $qs(['vista' => $view === 'stock' ? '' : 'stock']) ?>"><?= $view === 'stock' ? 'Vista de lista' : 'Editar stock y precios rápido' ?></a>
      <a class="btn btn--sm" href="<?= url('admin/producto.php') ?>"><?= icon('plus', 16) ?> Nuevo producto</a>
    </div>
  </div>

  <form class="toolbar">
    <?php if ($view === 'stock'): ?><input type="hidden" name="vista" value="stock"><?php endif; ?>
    <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre, marca o etiqueta">
    <select class="select" name="cat" onchange="this.form.submit()">
      <option value="">Todas las categorías</option>
      <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn--ghost btn--sm">Filtrar</button>
    <a class="chip" href="<?= $qs(['bajo' => empty($_GET['bajo']) ? '1' : '']) ?>" <?= !empty($_GET['bajo']) ? 'style="background:var(--ink);color:#fff"' : '' ?>>Stock bajo</a>
    <a class="chip" href="<?= $qs(['sinfoto' => empty($_GET['sinfoto']) ? '1' : '']) ?>" <?= !empty($_GET['sinfoto']) ? 'style="background:var(--ink);color:#fff"' : '' ?>>Sin foto</a>
  </form>

  <?php if ($view === 'stock'): ?>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="bulk">
      <div class="table-wrap">
        <table class="t vtable">
          <thead><tr><th>Producto</th><th>Variante</th><th class="num">Precio (S/)</th><th class="num">Stock</th></tr></thead>
          <tbody>
          <?php foreach ($products as $p): foreach ($variants[$p['id']] ?? [] as $i => $v): ?>
            <tr>
              <td><?= $i === 0 ? '<a href="' . url('admin/producto.php?id=' . $p['id']) . '">' . e($p['name']) . '</a>' : '' ?></td>
              <td><?= e($v['label']) ?></td>
              <td class="num"><input class="input stock-input" style="width:90px!important" name="v[<?= $v['id'] ?>][price]" value="<?= number_format((float) $v['price'], 2, '.', '') ?>" inputmode="decimal"></td>
              <td class="num"><input class="input stock-input" type="number" min="0" name="v[<?= $v['id'] ?>][stock]" value="<?= (int) $v['stock'] ?>"></td>
            </tr>
          <?php endforeach; endforeach; ?>
          </tbody>
        </table>
      </div>
      <div style="position:sticky;bottom:12px;margin-top:14px;text-align:right"><button class="btn" type="submit">Guardar todos los cambios</button></div>
    </form>
  <?php else: ?>
    <div class="table-wrap">
      <table class="t">
        <thead><tr><th></th><th>Producto</th><th>Categoría</th><th class="num">Precio</th><th class="num">Stock</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><img src="<?= e(product_cover($p)) ?>" alt=""></td>
            <td><a href="<?= url('admin/producto.php?id=' . $p['id']) ?>"><strong><?= e($p['name']) ?></strong></a><br><small class="muted"><?= e($p['brand'] ?: 'Sin marca') ?> · <?= (int) $p['variant_count'] ?> variante<?= $p['variant_count'] == 1 ? '' : 's' ?></small></td>
            <td><?= e($p['category'] ?: '—') ?></td>
            <td class="num"><?= money($p['min_price']) ?><?= $p['max_price'] > $p['min_price'] ? '<br><small class="muted">a ' . money($p['max_price']) . '</small>' : '' ?></td>
            <td class="num"><span class="pill pill--<?= $p['stock'] <= 0 ? 'err' : ($p['stock'] <= 5 ? 'warn' : 'ok') ?>"><?= (int) $p['stock'] ?></span></td>
            <td>
              <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button class="pill pill--<?= $p['active'] ? 'ok' : 'err' ?>" style="border:0;cursor:pointer" title="Clic para cambiar"><?= $p['active'] ? 'Visible' : 'Oculto' ?></button></form>
            </td>
            <td style="white-space:nowrap">
              <a class="btn btn--ghost btn--sm" href="<?= url('admin/producto.php?id=' . $p['id']) ?>">Editar</a>
              <form method="post" class="inline-form" data-confirm="¿Eliminar «<?= e($p['name']) ?>»? Esta acción no se puede deshacer."><?= csrf_field() ?>
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button class="icon-btn" aria-label="Eliminar <?= e($p['name']) ?>" style="color:var(--err)"><?= icon('trash', 18) ?></button></form>
            </td>
          </tr>
        <?php endforeach; if (!$products): ?><tr><td colspan="7" class="muted">No hay productos con ese filtro.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php page_footer(['admin' => true]); ?>
