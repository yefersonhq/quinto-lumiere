<?php
require __DIR__ . '/inc/bootstrap.php';

$slug = (string) ($_GET['p'] ?? '');
$st = db()->prepare(product_query('p.slug = ? AND (p.active = 1 OR ? = 1)'));
$st->execute([$slug, is_admin() ? 1 : 0]);
$p = $st->fetch();
if (!$p) {
    http_response_code(404);
    page_header('Producto no encontrado');
    echo '<div class="wrap prose"><h1>Producto no encontrado</h1><p><a class="btn" href="' . url('tienda.php') . '">Volver a la tienda</a></p></div>';
    page_footer();
    exit;
}
$variants = product_variants((int) $p['id']);
$images = product_images((int) $p['id']);
$first = current(array_filter($variants, fn($v) => $v['stock'] > 0)) ?: ($variants[0] ?? null);
$single = count($variants) === 1;
$tint = is_tint($p);

$rand = is_mysql() ? 'RAND()' : 'RANDOM()';
$rel = db()->prepare(product_query('p.active = 1 AND p.category_id = ? AND p.id <> ?', "p.featured DESC, $rand", 'LIMIT 4'));
$rel->execute([$p['category_id'], $p['id']]);
$related = $rel->fetchAll();

ga_event('view_item', ['currency' => 'PEN', 'value' => (float) ($first['price'] ?? 0), 'items' => [ga_item($p, $first)]]);

page_header($p['name'], [
    'description' => mb_substr(trim(preg_replace('/\s+/', ' ', (string) $p['description'])), 0, 155),
    'image' => $images ? site_url($images[0]['path']) : null,
]);
?>
<div class="wrap">
  <p class="crumbs"><a href="<?= url('') ?>">Inicio</a> / <a href="<?= url('tienda.php') ?>">Tienda</a>
    <?php if ($p['category']): ?> / <a href="<?= url('tienda.php?cat=' . urlencode($p['category_slug'])) ?>"><?= e($p['category']) ?></a><?php endif; ?></p>

  <?php if (!$p['active']): ?><div class="notice notice--warn" style="margin-top:12px">Este producto está oculto: solo los administradores lo ven.</div><?php endif; ?>

  <div class="pdp">
    <div class="gallery">
      <div class="gallery__main"><img src="<?= e($images ? url($images[0]['path']) : product_cover($p)) ?>" alt="<?= e($p['name']) ?>" data-gallery-main width="800" height="800"></div>
      <?php if (count($images) > 1): ?>
        <div class="gallery__thumbs">
          <?php foreach ($images as $i => $img): ?>
            <button type="button" class="<?= $i === 0 ? 'is-on' : '' ?>" data-gallery-thumb="<?= e(url($img['path'])) ?>" aria-label="Ver foto <?= $i + 1 ?>"><img src="<?= e(url($img['path'])) ?>" alt="" loading="lazy"></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div>
      <?php if ($p['brand']): ?><a class="pdp__brandlogo" href="<?= url('tienda.php?marca=' . urlencode($p['brand_slug'])) ?>" title="Ver todo <?= e($p['brand']) ?>"><?= brand_logo($p['brand'], $p['brand_logo']) ?></a><?php endif; ?>
      <h1><?= e($p['name']) ?></h1>
      <p class="pdp__price" data-price><?= money($first['price'] ?? 0) ?><?php if (!empty($first['compare_price']) && $first['compare_price'] > $first['price']): ?><s><?= money($first['compare_price']) ?></s><?php endif; ?></p>

      <?php if ($variants): ?>
      <form class="buy" method="post" action="<?= url('api/cart.php') ?>" data-add-to-cart
            data-product='<?= e(json_encode(ga_item($p), JSON_UNESCAPED_UNICODE)) ?>'>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <?php if ($tint && !$single): $ft = tone_parse($first['label'] ?? ''); ?>
          <div class="tonepicked" data-tonepicked>
            <span class="sw" style="--c:<?= e(variant_swatch($first) ?? '#999') ?>"></span>
            <span><small class="muted">Tono elegido</small><b data-tonename><?= e($first['label'] ?? '') ?></b></span>
          </div>
          <p style="margin:0;font-size:.88rem" class="muted">Carta de colores de <?= e($p['brand'] ?: 'este tinte') ?> — toca un tono:</p>
          <div class="swatches">
            <?php foreach ($variants as $v): $hex = variant_swatch($v); ?>
              <label title="<?= e($v['label']) ?><?= $v['stock'] <= 0 ? ' (agotado)' : '' ?>">
                <input type="radio" name="variant" value="<?= (int) $v['id'] ?>" <?= $first && $v['id'] == $first['id'] ? 'checked' : '' ?> <?= $v['stock'] <= 0 ? 'disabled' : '' ?>
                       data-price="<?= e(money($v['price'])) ?>" data-compare="<?= $v['compare_price'] > $v['price'] ? e(money($v['compare_price'])) : '' ?>"
                       data-stock="<?= (int) $v['stock'] ?>" data-label="<?= e($v['label']) ?>" data-raw-price="<?= (float) $v['price'] ?>" data-hex="<?= e($hex ?? '#999999') ?>">
                <span class="sw" style="--c:<?= e($hex ?? '#999999') ?>"></span>
                <span><?= e(($t = tone_parse($v['label'])) ? $t['code'] : $v['label']) ?><?php if ($t): ?><br><small class="muted"><?= e(mb_strimwidth(trim(preg_replace('/^\s*[\d.,\/]+\s*/', '', $v['label'])), 0, 22, '…')) ?></small><?php endif; ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php elseif (!$single): ?>
          <p style="margin:0;font-size:.88rem" class="muted">Elige opción:</p>
        <?php endif; ?>
        <div class="variants" <?= $single || $tint ? 'hidden' : '' ?>>
          <?php foreach (($tint && !$single) ? [] : $variants as $v): ?>
            <label>
              <input type="radio" name="variant" value="<?= (int) $v['id'] ?>" <?= $first && $v['id'] == $first['id'] ? 'checked' : '' ?> <?= $v['stock'] <= 0 ? 'disabled' : '' ?>
                     data-price="<?= e(money($v['price'])) ?>" data-compare="<?= $v['compare_price'] > $v['price'] ? e(money($v['compare_price'])) : '' ?>"
                     data-stock="<?= (int) $v['stock'] ?>" data-label="<?= e($v['label']) ?>" data-raw-price="<?= (float) $v['price'] ?>">
              <span><?= e($v['label']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
        <div class="buyrow">
          <div class="qty">
            <button type="button" data-qty="-1" aria-label="Menos"><?= icon('minus', 16) ?></button>
            <input type="number" name="qty" value="1" min="1" max="<?= (int) ($first['stock'] ?? 1) ?>" aria-label="Cantidad">
            <button type="button" data-qty="1" aria-label="Más"><?= icon('plus', 16) ?></button>
          </div>
          <button class="btn" type="submit" <?= (int) $p['stock'] <= 0 ? 'disabled' : '' ?>><?= icon('cart', 18) ?> Agregar al carrito</button>
        </div>
        <p class="stocknote" data-stocknote></p>
      </form>
      <?php endif; ?>

      <?php if ($tint): $ft = $first ? tone_parse($first['label']) : null; ?>
        <a class="cta-chart" data-chart-link href="<?= url('carta-de-colores.php?' . http_build_query(array_filter(['tono' => $ft['code'] ?? null, 'marca' => $p['brand_slug']]))) ?>#resultado">
          <span class="combo"><?php foreach (['4.0', '6.66', '7.3', '9.1'] as $c): ?><span class="sw" style="--c:<?= e(tone_hex(tone_parse($c))) ?>"></span><?php endforeach; ?></span>
          <span><strong>¿No sabes qué tono elegir?</strong><br><small>Usa la carta de colores: te decimos qué oxidante usar, cómo cubrir canas y qué combinaciones te quedan.</small></span>
        </a>
      <?php endif; ?>

      <a class="btn btn--ghost btn--block" style="margin-top:12px" target="_blank" rel="noopener"
         href="<?= e(whatsapp_link('Hola, tengo una consulta sobre: ' . $p['name'] . ' — ' . site_url('producto.php?p=' . $p['slug']))) ?>"><?= icon('wa', 18) ?> Consultar por WhatsApp</a>

      <div class="pdp__desc">
        <?= nl2br(e($p['description'])) ?>
        <?php if ($p['category']): ?><p class="muted" style="margin-top:14px;font-size:.88rem">Categoría: <?= e($p['category']) ?></p><?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($related): ?>
  <section style="padding-bottom:70px">
    <div class="section__head"><div><p class="eyebrow">También te puede gustar</p><h2>Combina con</h2></div></div>
    <div class="grid"><?php foreach ($related as $r) echo product_card($r); ?></div>
  </section>
  <?php endif; ?>
</div>
<?php page_footer(); ?>
