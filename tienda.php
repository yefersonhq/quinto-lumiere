<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/search.php';

$q     = trim((string) ($_GET['q'] ?? ''));
$cat   = (string) ($_GET['cat'] ?? '');
$brand = (string) ($_GET['marca'] ?? '');
$sort  = (string) ($_GET['orden'] ?? '');
$stockOnly = !empty($_GET['stock']);

$cats = categories();
$brs  = brands();
$catRow   = current(array_filter($cats, fn($c) => $c['slug'] === $cat)) ?: null;
$brandRow = current(array_filter($brs, fn($b) => $b['slug'] === $brand)) ?: null;

if ($q !== '') {
    $products = search_products($q, 200);
    log_search($q, count($products), 'tienda');
} else {
    $products = db()->query(product_query('p.active = 1'))->fetchAll();
}
$products = array_values(array_filter($products, function ($p) use ($catRow, $brandRow, $stockOnly) {
    if ($catRow && $p['category_id'] != $catRow['id']) return false;
    if ($brandRow && $p['brand_id'] != $brandRow['id']) return false;
    if ($stockOnly && (int) $p['stock'] <= 0) return false;
    return true;
}));
if ($sort === 'precio-asc')  usort($products, fn($a, $b) => $a['min_price'] <=> $b['min_price']);
if ($sort === 'precio-desc') usort($products, fn($a, $b) => $b['min_price'] <=> $a['min_price']);
if ($sort === 'nombre')      usort($products, fn($a, $b) => strcmp($a['name'], $b['name']));
if ($sort === 'nuevos')      usort($products, fn($a, $b) => $b['id'] <=> $a['id']);

function shop_url(array $change): string
{
    $params = array_merge($_GET, $change);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return url('tienda.php' . ($params ? '?' . http_build_query($params) : ''));
}

$title = $q !== '' ? "Resultados para “{$q}”" : ($catRow['name'] ?? ($brandRow['name'] ?? 'Toda la tienda'));
if ($q !== '') ga_event('search', ['search_term' => $q]);
ga_event('view_item_list', [
    'item_list_name' => $title,
    'items' => array_map(fn($p) => ga_item($p), array_slice($products, 0, 20)),
]);

page_header($title);
?>
<div class="wrap">
  <p class="crumbs"><a href="<?= url('') ?>">Inicio</a> / <a href="<?= url('tienda.php') ?>">Tienda</a><?= $catRow ? ' / ' . e($catRow['name']) : '' ?></p>
  <div class="shop">
    <aside class="filters" id="filtros" aria-label="Filtros">
      <form action="<?= url('tienda.php') ?>">
        <label class="sr" for="q-side">Buscar</label>
        <input id="q-side" class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar producto, marca, tono…">
        <?php if ($cat): ?><input type="hidden" name="cat" value="<?= e($cat) ?>"><?php endif; ?>
      </form>
      <div>
        <h3>Categorías</h3>
        <ul>
          <li><a href="<?= shop_url(['cat' => '']) ?>" class="<?= !$catRow ? 'is-on' : '' ?>">Todas</a></li>
          <?php foreach ($cats as $c): if (!$c['n']) continue; ?>
            <li><a href="<?= shop_url(['cat' => $c['slug']]) ?>" class="<?= $catRow && $catRow['id'] == $c['id'] ? 'is-on' : '' ?>"><?= e($c['name']) ?> <small><?= (int) $c['n'] ?></small></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h3>Marcas</h3>
        <ul>
          <li><a href="<?= shop_url(['marca' => '']) ?>" class="<?= !$brandRow ? 'is-on' : '' ?>">Todas</a></li>
          <?php foreach ($brs as $b): if (!$b['n']) continue; ?>
            <li><a href="<?= shop_url(['marca' => $b['slug']]) ?>" class="<?= $brandRow && $brandRow['id'] == $b['id'] ? 'is-on' : '' ?>"><?= e($b['name']) ?> <small><?= (int) $b['n'] ?></small></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <label class="check"><input type="checkbox" <?= $stockOnly ? 'checked' : '' ?> onchange="location.href='<?= e(shop_url(['stock' => $stockOnly ? '' : '1'])) ?>'"> Solo con stock</label>
    </aside>

    <section>
      <div class="shop__bar">
        <div>
          <h1><?= e($title) ?></h1>
          <p class="muted" style="margin:4px 0 0"><?= count($products) ?> producto<?= count($products) === 1 ? '' : 's' ?></p>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <button class="btn btn--ghost btn--sm filters-toggle" type="button" data-toggle="#filtros">Filtros</button>
          <label class="sr" for="orden">Ordenar</label>
          <select id="orden" class="select select--inline" onchange="location.href=this.value">
            <?php foreach (['' => 'Relevancia', 'nuevos' => 'Más nuevos', 'precio-asc' => 'Precio: menor a mayor', 'precio-desc' => 'Precio: mayor a menor', 'nombre' => 'Nombre A–Z'] as $k => $v): ?>
              <option value="<?= e(shop_url(['orden' => $k])) ?>" <?= $sort === $k ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <?php if ($q || $catRow || $brandRow || $stockOnly): ?>
        <div class="chips" style="margin-bottom:18px">
          <?php if ($q): ?><a class="chip" href="<?= shop_url(['q' => '']) ?>">“<?= e($q) ?>” ✕</a><?php endif; ?>
          <?php if ($catRow): ?><a class="chip" href="<?= shop_url(['cat' => '']) ?>"><?= e($catRow['name']) ?> ✕</a><?php endif; ?>
          <?php if ($brandRow): ?><a class="chip" href="<?= shop_url(['marca' => '']) ?>"><?= e($brandRow['name']) ?> ✕</a><?php endif; ?>
          <?php if ($stockOnly): ?><a class="chip" href="<?= shop_url(['stock' => '']) ?>">Con stock ✕</a><?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($products): ?>
        <div class="grid">
          <?php foreach ($products as $p) echo product_card($p); ?>
        </div>
      <?php else: ?>
        <div class="empty">
          <h2>No encontramos ese producto</h2>
          <p class="muted">Puede que lo tengamos y aún no esté en la web. Escríbenos y te confirmamos.</p>
          <a class="btn" href="<?= e(whatsapp_link('Hola, ¿tienen ' . ($q ?: 'este producto') . '?')) ?>" target="_blank" rel="noopener"><?= icon('wa', 18) ?> Consultar por WhatsApp</a>
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>
<?php page_footer(); ?>
