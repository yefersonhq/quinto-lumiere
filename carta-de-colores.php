<?php
require __DIR__ . '/inc/bootstrap.php';

$in = implode(',', array_map(fn($s) => db()->quote($s), TINT_CATEGORIES));
$rows = db()->query("SELECT v.id AS variant_id, v.label, v.price, v.stock, v.color, p.id, p.name, p.slug, b.name AS brand, b.slug AS brand_slug,
                            b.logo AS brand_logo, c.slug AS category_slug,
                            (SELECT path FROM product_images i WHERE i.product_id = p.id ORDER BY sort, id LIMIT 1) AS cover
                     FROM product_variants v JOIN products p ON p.id = v.product_id
                     JOIN categories c ON c.id = p.category_id LEFT JOIN brands b ON b.id = p.brand_id
                     WHERE p.active = 1 AND c.slug IN ($in) ORDER BY b.name, p.name")->fetchAll();

$brandFilter = (string) ($_GET['marca'] ?? '');
$tintBrands = [];
$byCode = [];
foreach ($rows as $r) {
    if ($r['brand']) $tintBrands[$r['brand_slug']] = $r['brand'];
    if ($brandFilter && $r['brand_slug'] !== $brandFilter) continue;
    $t = tone_parse($r['label']);
    if (!$t) continue;
    $r['tone'] = $t;
    $r['hex'] = variant_swatch($r);
    $byCode[$t['code']][] = $r;
}
ksort($tintBrands);

$chart = tone_chart();
$inChart = [];
foreach ($chart as $cells) foreach ($cells as $c) $inChart[$c['code']] = true;
$extra = array_diff_key($byCode, $inChart);
uksort($extra, 'strnatcmp');

// ---- simulador ----
$tono = preg_replace('/[^0-9.]/', '', (string) ($_GET['tono'] ?? ''));
$target = $tono !== '' ? tone_parse($tono) : null;
$actual = max(1, min(10, (int) ($_GET['actual'] ?? 0))) ?: null;
if (empty($_GET['actual'])) $actual = null;
$tenido = ($_GET['tenido'] ?? 'no') === 'si';
$canas = in_array($_GET['canas'] ?? 'no', ['no', 'poca', 'media', 'mucha'], true) ? $_GET['canas'] : 'no';
$advice = $target && $actual ? tone_advice($actual, $tenido, $canas, $target) : null;

$matches = $target ? ($byCode[$target['code']] ?? []) : [];
$similar = [];
if ($target && !$matches) {
    foreach ($byCode as $code => $list) {
        $t = $list[0]['tone'];
        $score = abs($t['level'] - $target['level']) * 2 + ($t['reflects'][0] === $target['reflects'][0] ? 0 : 3);
        if ($score <= 3) foreach ($list as $r) $similar[] = $r + ['_s' => $score];
    }
    usort($similar, fn($a, $b) => $a['_s'] <=> $b['_s']);
    $similar = array_slice($similar, 0, 6);
}

function extra_products(string $cat, string $extra = '', int $limit = 2): array
{
    $st = db()->prepare(product_query("p.active = 1 AND c.slug = ?" . ($extra ? " AND $extra" : ''), 'p.featured DESC, p.name', "LIMIT $limit"));
    $st->execute([$cat]);
    return $st->fetchAll();
}
$extras = [];
if ($advice) {
    $extras['Oxidante ' . $advice['vol'] . ' vol'] = extra_products('oxidantes', '', 1);
    if ($advice['needs_bleach']) $extras['Para retirar color'] = extra_products('tratamientos', "p.tags LIKE '%removedor%'", 1);
    if ($advice['matizador']) $extras['Mantén tu rubio'] = extra_products('shampoo-matizador', '', 1);
    $extras['Cuida tu color'] = extra_products('tratamientos', "p.tags NOT LIKE '%removedor%'", 2);
}

$swatch = fn(string $hex, string $cls = 'sw') => '<span class="' . $cls . '" style="--c:' . e($hex) . '"></span>';

if ($target) ga_event('select_content', ['content_type' => 'tono', 'content_id' => $target['code']]);

page_header('Carta de colores', ['description' => 'Carta de colores de tintes: elige tu tono, descubre qué oxidante usar, cómo cubrir canas y qué combinaciones te quedan mejor.']);
?>
<div class="wrap chartpage">
  <header class="chartpage__head">
    <p class="eyebrow">Colorimetría</p>
    <h1>Carta de colores</h1>
    <p class="hero__lead">Elige el tono que sueñas y te decimos qué necesitas: el oxidante correcto, cómo cubrir tus canas, combinaciones para mechas y en qué marcas lo tenemos.</p>
  </header>

  <form method="get" action="<?= url('carta-de-colores.php') ?>#resultado" class="chartform" data-chartform>
    <section class="panel">
      <div class="chartbar">
        <h2>1. Elige el tono que quieres</h2>
        <label class="inline-form" style="font-size:.88rem">Marca
          <select class="select select--inline" name="marca" onchange="this.form.action='<?= url('carta-de-colores.php') ?>';this.form.submit()">
            <option value="">Todas</option>
            <?php foreach ($tintBrands as $slug => $name): ?><option value="<?= e($slug) ?>" <?= $brandFilter === $slug ? 'selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?>
          </select>
        </label>
      </div>
      <p class="muted" style="font-size:.86rem;margin-top:-4px">Los tonos con <span class="dot-av"></span> los tenemos en tienda. Los colores en pantalla son aproximados.</p>

      <div class="chart" role="radiogroup" aria-label="Tonos">
        <div class="chart__row chart__row--head">
          <span></span>
          <?php foreach (TONE_REFLECTS as $k => [$rn]): ?><span class="chart__col">.<?= $k ?><small><?= e($rn) ?></small></span><?php endforeach; ?>
        </div>
        <?php foreach ($chart as $level => $cells): ?>
          <div class="chart__row">
            <span class="chart__lvl"><b><?= $level ?></b><small><?= e(TONE_LEVELS[$level][0]) ?></small></span>
            <?php foreach (array_keys(TONE_REFLECTS) as $r):
              if (!isset($cells[$r])) { echo '<span class="chart__empty"></span>'; continue; }
              $c = $cells[$r]; $avail = isset($byCode[$c['code']]); ?>
              <label class="tone<?= $avail ? ' is-av' : '' ?>" title="<?= e($c['code'] . ' ' . tone_name($c['t'])) ?>">
                <input type="radio" name="tono" value="<?= e($c['code']) ?>" <?= $target && $target['code'] === $c['code'] ? 'checked' : '' ?> data-name="<?= e(tone_name($c['t'])) ?>" data-hex="<?= e(tone_hex($c['t'])) ?>">
                <?= $swatch(tone_hex($c['t'])) ?><span class="tone__code"><?= e($c['code']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($extra): ?>
        <h3 style="margin-top:22px">Más tonos disponibles en tienda</h3>
        <div class="tones-extra">
          <?php foreach ($extra as $code => $list): $t = $list[0]['tone']; ?>
            <label class="tone tone--wide is-av">
              <input type="radio" name="tono" value="<?= e($code) ?>" <?= $target && $target['code'] === $code ? 'checked' : '' ?> data-name="<?= e(tone_name($t)) ?>" data-hex="<?= e($list[0]['hex']) ?>">
              <?= $swatch($list[0]['hex']) ?><span class="tone__code"><?= e($code) ?> <small><?= e(tone_name($t)) ?></small></span>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <section class="panel">
      <h2>2. Cuéntanos cómo está tu cabello hoy</h2>
      <p class="muted" style="font-size:.88rem">Tu color actual (mira tu raíz, con luz natural):</p>
      <div class="levels" role="radiogroup" aria-label="Color actual">
        <?php for ($l = 1; $l <= 10; $l++): ?>
          <label class="tone tone--lvl" title="<?= e(TONE_LEVELS[$l][0]) ?>">
            <input type="radio" name="actual" value="<?= $l ?>" <?= $actual === $l ? 'checked' : '' ?> required>
            <?= $swatch(TONE_LEVELS[$l][1]) ?><span class="tone__code"><?= $l ?><small><?= e(TONE_LEVELS[$l][0]) ?></small></span>
          </label>
        <?php endfor; ?>
      </div>
      <div class="row-2">
        <div class="field"><label for="tenido">¿Tu cabello ya está teñido o decolorado?</label>
          <select class="select" id="tenido" name="tenido"><option value="no">No, es mi color natural</option><option value="si" <?= $tenido ? 'selected' : '' ?>>Sí, tiene tinte o decoloración</option></select></div>
        <div class="field"><label for="canas">¿Tienes canas?</label>
          <select class="select" id="canas" name="canas">
            <?php foreach (['no' => 'No', 'poca' => 'Pocas (menos de 30%)', 'media' => 'Entre 30% y 50%', 'mucha' => 'Más de 50%'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= $canas === $k ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select></div>
      </div>
      <?php if ($brandFilter): ?><input type="hidden" name="marca" value="<?= e($brandFilter) ?>"><?php endif; ?>
      <div class="chartsubmit">
        <div class="chartpick" data-chartpick><?= $target ? $swatch(tone_hex($target)) . '<span><b>' . e($target['code']) . '</b> ' . e(tone_name($target)) . '</span>' : '<span class="muted">Aún no eliges un tono</span>' ?></div>
        <button class="btn">Ver mi recomendación</button>
      </div>
    </section>
  </form>

  <?php if ($target): ?>
  <section class="panel result" id="resultado">
    <div class="result__head">
      <?php if ($actual): ?>
        <div class="result__sw"><?= $swatch(TONE_LEVELS[$actual][1], 'sw sw--xl') ?><small>Hoy<br><b><?= $actual ?></b> <?= e(TONE_LEVELS[$actual][0]) ?></small></div>
        <span class="result__arrow" aria-hidden="true">→</span>
      <?php endif; ?>
      <div class="result__sw"><?= $swatch(tone_hex($target), 'sw sw--xl') ?><small>Quieres<br><b><?= e($target['code']) ?></b> <?= e(tone_name($target)) ?></small></div>
    </div>

    <?php if (!$actual): ?>
      <p class="notice">Elige también tu color actual (paso 2) para darte la recomendación exacta de oxidante.</p>
    <?php else: ?>
      <div class="grid-2" style="margin-top:18px">
        <div>
          <h3>Cómo lograrlo</h3>
          <?php if ($advice['vol']): ?><p class="bigvol">Oxidante de <strong><?= (int) $advice['vol'] ?> volúmenes</strong></p><?php endif; ?>
          <?php foreach ($advice['warnings'] as $w): ?><p class="notice notice--warn"><?= e($w) ?></p><?php endforeach; ?>
          <ul class="checks"><?php foreach ($advice['steps'] as $s): ?><li><?= e($s) ?></li><?php endforeach; ?></ul>
          <?php if ($advice['mix']): ?><h3>Si tienes canas</h3><p><?= e($advice['mix']) ?></p><?php endif; ?>
        </div>
        <div>
          <h3>Combinaciones y recomendaciones</h3>
          <ul class="checks"><?php foreach ($advice['combos'] as $c): ?><li><?= e($c) ?></li><?php endforeach; ?></ul>
          <?php
          // muestras visuales de combinaciones
          $hl = tone_parse(min(10, $target['level'] + 2) . '.' . (in_array($target['reflects'][0], [4, 5, 6], true) ? 4 : ($target['reflects'][0] ?: 3)));
          $low = $target['level'] > 3 ? tone_parse(($target['level'] - 1) . '.0') : null; ?>
          <div class="combo">
            <?php if ($low): ?><?= $swatch(tone_hex($low)) ?><?php endif; ?><?= $swatch(tone_hex($target), 'sw sw--main') ?><?= $hl ? $swatch(tone_hex($hl)) : '' ?>
            <small class="muted">Profundidad · Base · Luz</small>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <h3 style="margin-top:26px"><?= $matches ? 'Lo tenemos en estas marcas' : 'Este tono exacto no está en tienda ahora' ?></h3>
    <?php if (!$matches && $similar): ?><p class="muted">Tonos parecidos disponibles:</p><?php endif; ?>
    <div class="quickgrid">
      <?php foreach ($matches ?: $similar as $m): ?>
        <div class="quick">
          <?= $swatch($m['hex']) ?>
          <div>
            <a href="<?= url('producto.php?p=' . urlencode($m['slug'])) ?>"><b><?= e($m['name']) ?></b></a>
            <small><?= e($m['label']) ?> · <?= money($m['price']) ?><?= $m['stock'] <= 0 ? ' · Agotado' : '' ?></small>
          </div>
          <form method="post" action="<?= url('api/cart.php') ?>" data-quick-add>
            <?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="variant" value="<?= (int) $m['variant_id'] ?>"><input type="hidden" name="qty" value="1">
            <button class="btn btn--sm" <?= $m['stock'] <= 0 ? 'disabled' : '' ?> aria-label="Agregar <?= e($m['name'] . ' ' . $m['label']) ?>"><?= icon('cart', 16) ?></button>
          </form>
        </div>
      <?php endforeach; ?>
      <?php if (!$matches && !$similar): ?>
        <a class="btn btn--ghost" href="<?= e(whatsapp_link('Hola, ¿consiguen el tinte tono ' . $target['code'] . ' (' . tone_name($target) . ')?')) ?>" target="_blank" rel="noopener"><?= icon('wa', 18) ?> Pedir este tono por WhatsApp</a>
      <?php endif; ?>
    </div>

    <?php if ($extras && array_filter($extras)): ?>
      <h3 style="margin-top:26px">Completa tu coloración</h3>
      <div class="grid">
        <?php foreach ($extras as $title => $list): foreach ($list as $p): ?>
          <div><p class="eyebrow" style="margin-bottom:8px"><?= e($title) ?></p><?= product_card($p) ?></div>
        <?php endforeach; endforeach; ?>
      </div>
    <?php endif; ?>
    <p class="muted" style="font-size:.84rem;margin-top:20px">Estas recomendaciones son orientativas y siguen las reglas generales de colorimetría. Lee siempre las indicaciones de la marca. ¿Dudas? <a href="<?= e(whatsapp_link('Hola, quiero asesoría para mi coloración: quiero el tono ' . $target['code'])) ?>" target="_blank" rel="noopener">Pide asesoría por WhatsApp</a>.</p>
  </section>
  <?php endif; ?>
</div>
<?php page_footer(); ?>
