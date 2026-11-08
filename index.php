<?php
require __DIR__ . '/inc/bootstrap.php';

if (!admin_exists()) redirect('instalar.php');

$featured = db()->query(product_query('p.active = 1 AND p.featured = 1', 'p.updated_at DESC', 'LIMIT 8'))->fetchAll();
$newest   = db()->query(product_query('p.active = 1', 'p.created_at DESC, p.id DESC', 'LIMIT 4'))->fetchAll();
$cats = array_filter(categories(), fn($c) => $c['n'] > 0);

page_header('', ['body' => 'home']);
?>
<section class="hero">
  <div class="wrap hero__grid">
    <div>
      <p class="eyebrow">Cosmética y belleza · Lurín</p>
      <h1>Belleza que <em>ilumina</em> desde el alma</h1>
      <p class="hero__lead">Perfumes de catálogo, tintes, tratamientos y todo lo que tu salón necesita. Productos originales, precios de mercado y atención cercana.</p>
      <div class="hero__cta">
        <a class="btn" href="<?= url('tienda.php') ?>">Ver la tienda</a>
        <button class="btn btn--ghost" type="button" data-chat-open>Pregúntale a Lumi</button>
      </div>
      <div class="hero__brands" aria-label="Marcas">
        <span>Avon</span><span>Ésika</span><span>Natura</span><span>Yanbal</span><span>Koleston</span><span>Placenta Life</span>
      </div>
    </div>
    <div class="hero__art" aria-hidden="true">
      <div class="hero__logo"><?= sun_svg(360) ?><span>Quinto<br>Lumière</span></div>
      <span class="hero__badge hero__badge--a"><?= icon('spark', 16) ?> Productos originales</span>
      <span class="hero__badge hero__badge--b"><?= icon('truck', 16) ?> Delivery a Lima y provincias</span>
    </div>
  </div>
</section>

<div class="perks">
  <div class="perk"><?= icon('shield', 22) ?><div><strong>Pago seguro</strong>Tarjeta, Yape o PagoEfectivo con Mercado Pago</div></div>
  <div class="perk"><?= icon('truck', 22) ?><div><strong>Envíos</strong>Lima, Lurín y provincias<?= (float) cfg('shipping.gratis_desde') > 0 ? ' · gratis desde ' . money(cfg('shipping.gratis_desde')) : '' ?></div></div>
  <div class="perk"><?= icon('pin', 22) ?><div><strong>Recojo en tienda</strong>Mercado San Pedro de Lurín, Stand 36</div></div>
  <div class="perk"><?= icon('wa', 22) ?><div><strong>Asesoría</strong>Te ayudamos a elegir tu tono por WhatsApp</div></div>
</div>

<section class="section">
  <div class="wrap">
    <div class="section__head reveal">
      <div><p class="eyebrow">Explora</p><h2>Categorías</h2></div>
      <a class="btn btn--ghost btn--sm" href="<?= url('tienda.php') ?>">Ver todo</a>
    </div>
    <div class="cats">
      <?php foreach ($cats as $c): ?>
        <a class="cat reveal" href="<?= url('tienda.php?cat=' . urlencode($c['slug'])) ?>">
          <span class="cat__name"><?= e($c['name']) ?></span>
          <span class="cat__n"><?= (int) $c['n'] ?> producto<?= $c['n'] == 1 ? '' : 's' ?> →</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="wrap">
    <a class="visit__card reveal" style="text-decoration:none;display:grid;grid-template-columns:1fr auto;align-items:center;gap:24px" href="<?= url('carta-de-colores.php') ?>">
      <div>
        <p class="eyebrow" style="color:var(--rose)">Nuevo · Carta de colores</p>
        <h2 style="margin-bottom:8px">Encuentra tu tono ideal</h2>
        <p style="margin:0">Elige el color que quieres y te decimos qué oxidante usar, cómo cubrir canas y qué combinaciones te quedan.</p>
      </div>
      <span class="combo" aria-hidden="true" style="flex-wrap:nowrap">
        <?php foreach (['3.0', '5.5', '6.66', '7.4', '8.3', '9.1'] as $c): ?><span class="sw sw--main" style="--c:<?= e(tone_hex(tone_parse($c))) ?>;margin-left:-14px;border:3px solid var(--ink)"></span><?php endforeach; ?>
      </span>
    </a>
  </div>
</section>

<?php if ($featured): ?>
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="section__head reveal">
      <div><p class="eyebrow">Lo más pedido</p><h2>Favoritos de la casa</h2></div>
    </div>
    <div class="grid">
      <?php foreach ($featured as $p) echo product_card($p); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($newest): ?>
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="section__head reveal">
      <div><p class="eyebrow">Recién llegados</p><h2>Novedades</h2></div>
    </div>
    <div class="grid">
      <?php foreach ($newest as $p) echo product_card($p); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php $brs = array_filter(brands(), fn($b) => $b['n'] > 0); if ($brs): ?>
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="section__head reveal"><div><p class="eyebrow">Originales</p><h2>Nuestras marcas</h2></div></div>
    <div class="brands">
      <?php foreach ($brs as $b): ?>
        <a class="brandtile reveal" href="<?= url('tienda.php?marca=' . urlencode($b['slug'])) ?>" aria-label="Ver productos <?= e($b['name']) ?>"><?= brand_logo($b['name'], $b['logo']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" id="visitanos" style="padding-top:0">
  <div class="wrap visit">
    <div class="visit__card reveal">
      <p class="eyebrow" style="color:var(--rose)">Visítanos</p>
      <h2>Te esperamos en nuestro stand</h2>
      <p><?= e(cfg('address')) ?></p>
      <p>Prueba tonos, pide asesoría para tu coloración o recoge tu pedido online sin costo de envío.</p>
      <a class="btn" href="<?= e(whatsapp_link('Hola, quiero visitar la tienda. ¿Cuál es su horario de atención?')) ?>" target="_blank" rel="noopener"><?= icon('wa', 18) ?> Consultar horario</a>
    </div>
    <iframe title="Mapa: Mercado San Pedro de Lurín" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
      src="https://maps.google.com/maps?q=<?= rawurlencode('Mercado San Pedro de Lurín, Antigua Panamericana Sur km 36, Lurín, Lima') ?>&z=16&output=embed"></iframe>
  </div>
</section>
<?php page_footer(); ?>
