<?php

$GLOBALS['GA_EVENTS'] = [];

/** Registra un evento de Google Analytics que se envía al cargar la página. */
function ga_event(string $name, array $params = []): void
{
    $GLOBALS['GA_EVENTS'][] = [$name, $params];
}

function ga_item(array $p, ?array $variant = null, int $qty = 1): array
{
    return array_filter([
        'item_id'       => (string) ($variant['id'] ?? $p['id'] ?? ''),
        'item_name'     => $p['name'] ?? '',
        'item_brand'    => $p['brand'] ?? null,
        'item_category' => $p['category'] ?? null,
        'item_variant'  => $variant['label'] ?? null,
        'price'         => (float) ($variant['price'] ?? $p['min_price'] ?? $p['price'] ?? 0),
        'quantity'      => $qty,
    ], fn($v) => $v !== null && $v !== '');
}

function icon(string $name, int $size = 20): string
{
    $paths = [
        'cart'   => '<path d="M6 7h12l-1 12H7L6 7Z"/><path d="M9 7a3 3 0 0 1 6 0"/>',
        'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'close'  => '<path d="M6 6l12 12M18 6 6 18"/>',
        'chat'   => '<path d="M4 5h16v11H9l-5 4V5Z"/>',
        'send'   => '<path d="M4 12 20 4l-6 16-3-7-7-1Z"/>',
        'menu'   => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'pin'    => '<path d="M12 21s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/>',
        'wa'     => '<path d="M4 20l1.3-4A8 8 0 1 1 8 18.7L4 20Z"/><path d="M9 9.5c.3 2 2.5 4.2 4.5 4.5l1-1 1.6.8c-.3 1-1.1 1.6-2.1 1.5-3-.4-5.4-2.8-5.8-5.8-.1-1 .5-1.8 1.5-2.1l.8 1.6-1 1Z"/>',
        'truck'  => '<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
        'shield' => '<path d="M12 3 5 6v6c0 4.4 3 7.7 7 9 4-1.3 7-4.6 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
        'spark'  => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6"/>',
        'plus'   => '<path d="M12 5v14M5 12h14"/>',
        'minus'  => '<path d="M5 12h14"/>',
        'trash'  => '<path d="M5 7h14M10 7V4h4v3M7 7l1 13h8l1-13"/>',
    ];
    return '<svg class="ico" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}

function sun_svg(int $size = 64, string $class = 'sun'): string
{
    $rays = '';
    for ($i = 0; $i < 13; $i++) {
        $a = deg2rad(180 + $i * 15);
        $x1 = 32 + cos($a) * 15; $y1 = 40 + sin($a) * 15;
        $x2 = 32 + cos($a) * 26; $y2 = 40 + sin($a) * 26;
        $rays .= sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f"/>', $x1, $y1, $x2, $y2);
    }
    return '<svg class="' . $class . '" width="' . $size . '" height="' . round($size * 0.7) . '" viewBox="0 0 64 44" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" aria-hidden="true">'
        . '<path d="M21 40a11 11 0 0 1 22 0"/>' . $rays . '<line x1="14" y1="40" x2="50" y2="40"/></svg>';
}

function page_header(string $title = '', array $opt = []): void
{
    $store = cfg('store_name');
    $fullTitle = $title ? "$title · $store" : "$store — " . cfg('tagline');
    $desc = $opt['description'] ?? 'Tienda de cosmética y belleza en Lurín: perfumes de catálogo Avon, Ésika, Natura y Yanbal, tintes, tratamientos capilares, oxidantes, ceras y herramientas de peluquería.';
    $u = current_user();
    $ga = cfg('ga_measurement_id');
    $admin = !empty($opt['admin']);
    ?>
<!doctype html>
<html lang="es-PE">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="theme-color" content="#F3EEEB">
<script>document.documentElement.classList.add('js')</script>
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:image" content="<?= e($opt['image'] ?? site_url('assets/img/logo.jpg')) ?>">
<link rel="icon" href="<?= asset('assets/img/logo.jpg') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/styles.css') ?>">
<?php if ($ga && !$admin): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', <?= json_encode($ga) ?><?= $u ? ", {user_id: " . json_encode('u' . $u['id']) . "}" : '' ?>);
</script>
<?php endif; ?>
<script>
window.QL = <?= json_encode([
    'base' => base_path(),
    'csrf' => csrf_token(),
    'ga'   => (bool) $ga && !$admin,
    'wa'   => cfg('whatsapp'),
], JSON_UNESCAPED_SLASHES) ?>;
</script>
<script defer src="<?= asset('assets/js/app.js') ?>"></script>
</head>
<body class="<?= $admin ? 'is-admin' : '' ?> <?= e($opt['body'] ?? '') ?>">
<a class="skip" href="#main">Saltar al contenido</a>
<header class="topbar">
  <div class="wrap topbar__in">
    <button class="icon-btn nav-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" data-nav-toggle><?= icon('menu') ?></button>
    <a class="brand" href="<?= url('') ?>" aria-label="<?= e($store) ?> — inicio">
      <img src="<?= asset('assets/img/logo.jpg') ?>" alt="" width="52" height="52">
      <span class="brand__name"><?= e($store) ?></span>
    </a>
    <nav class="mainnav" data-nav aria-label="Principal">
      <?php if ($admin): ?>
        <a href="<?= url('admin/') ?>">Resumen</a>
        <a href="<?= url('admin/productos.php') ?>">Inventario</a>
        <a href="<?= url('admin/pedidos.php') ?>">Pedidos</a>
        <a href="<?= url('admin/categorias.php') ?>">Categorías y marcas</a>
        <a href="<?= url('admin/usuarios.php') ?>">Usuarios</a>
        <a href="<?= url('admin/reclamos.php') ?>">Reclamos</a>
        <a href="<?= url('admin/diagnostico.php') ?>">Diagnóstico</a>
        <a href="<?= url('') ?>" class="muted">Ver tienda ↗</a>
      <?php else: ?>
        <a href="<?= url('tienda.php') ?>">Tienda</a>
        <a href="<?= url('tienda.php?cat=perfumes') ?>">Perfumes</a>
        <a href="<?= url('tienda.php?cat=tintes-caja') ?>">Tintes</a>
        <a href="<?= url('carta-de-colores.php') ?>">Carta de colores</a>
        <a href="<?= url('index.php#visitanos') ?>">Visítanos</a>
      <?php endif; ?>
    </nav>
    <div class="topbar__actions">
      <?php if (!$admin): ?>
      <form class="searchbox" action="<?= url('tienda.php') ?>" role="search">
        <label class="sr" for="q-top">Buscar productos</label>
        <input id="q-top" name="q" type="search" placeholder="Buscar…" value="<?= e($_GET['q'] ?? '') ?>">
        <button class="icon-btn" aria-label="Buscar"><?= icon('search', 18) ?></button>
      </form>
      <?php endif; ?>
      <div class="usermenu">
        <?php if ($u): ?>
          <button class="icon-btn" type="button" aria-haspopup="true" aria-expanded="false" data-usermenu aria-label="Mi cuenta"><?= icon('user') ?></button>
          <div class="usermenu__panel" hidden>
            <p class="usermenu__hello">Hola, <?= e(explode(' ', $u['name'])[0]) ?></p>
            <?php if ($u['role'] === 'admin'): ?><a href="<?= url('admin/') ?>">Panel de administración</a><?php endif; ?>
            <a href="<?= url('cuenta.php') ?>">Mis pedidos</a>
            <form method="post" action="<?= url('logout.php') ?>"><?= csrf_field() ?><button type="submit">Cerrar sesión</button></form>
          </div>
        <?php else: ?>
          <a class="icon-btn" href="<?= url('login.php') ?>" aria-label="Iniciar sesión"><?= icon('user') ?></a>
        <?php endif; ?>
      </div>
      <?php if (!$admin): ?>
      <a class="icon-btn cartbtn" href="<?= url('carrito.php') ?>" aria-label="Carrito">
        <?= icon('cart') ?><span class="cartbtn__n" data-cart-count <?= cart_count() ? '' : 'hidden' ?>><?= cart_count() ?></span>
      </a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main id="main">
<?php foreach (flash() as $f): ?>
  <div class="wrap"><div class="flash flash--<?= e($f['type']) ?>" role="status"><?= e($f['msg']) ?></div></div>
<?php endforeach;
}

function page_footer(array $opt = []): void
{
    $admin = !empty($opt['admin']);
    $s = cfg('social');
    ?>
</main>
<?php if (!$admin): ?>
<footer class="footer">
  <div class="wrap footer__grid">
    <div>
      <div class="footer__brand"><?= sun_svg(48) ?><span><?= e(cfg('store_name')) ?></span></div>
      <p class="muted"><?= e(cfg('tagline')) ?>.</p>
      <p class="footer__addr"><?= icon('pin', 16) ?> <?= e(cfg('address')) ?></p>
    </div>
    <div>
      <h3>Tienda</h3>
      <a href="<?= url('tienda.php') ?>">Todos los productos</a>
      <a href="<?= url('tienda.php?cat=perfumes') ?>">Perfumes de catálogo</a>
      <a href="<?= url('tienda.php?cat=tintes-sachet') ?>">Tintes en sachet</a>
      <a href="<?= url('carta-de-colores.php') ?>">Carta de colores</a>
      <a href="<?= url('tienda.php?cat=cuidado-capilar') ?>">Cuidado capilar</a>
    </div>
    <div>
      <h3>Ayuda</h3>
      <a href="<?= e(whatsapp_link('Hola Quinto Lumière, tengo una consulta')) ?>" target="_blank" rel="noopener">WhatsApp</a>
      <a href="<?= url('terminos.php') ?>">Términos y condiciones</a>
      <a href="<?= url('privacidad.php') ?>">Política de privacidad</a>
      <a href="<?= url('reclamaciones.php') ?>" class="libro"><img src="<?= asset('assets/img/libro-reclamaciones.svg') ?>" alt="" width="28" height="20"> Libro de Reclamaciones</a>
    </div>
    <div>
      <h3>Síguenos</h3>
      <a href="<?= e($s['instagram']) ?>" target="_blank" rel="noopener">Instagram</a>
      <a href="<?= e($s['tiktok']) ?>" target="_blank" rel="noopener">TikTok</a>
      <a href="<?= e($s['facebook']) ?>" target="_blank" rel="noopener">Facebook</a>
      <a href="<?= e($s['x']) ?>" target="_blank" rel="noopener">X / Twitter</a>
    </div>
  </div>
  <div class="wrap footer__legal">
    <span>© <?= date('Y') ?> <?= e(cfg('razon_social') ?: cfg('store_name')) ?><?= cfg('ruc') ? ' · RUC ' . e(cfg('ruc')) : '' ?></span>
    <span>Pagos seguros con Mercado Pago</span>
  </div>
  <div class="wrap" style="color:var(--muted);font-size:.74rem;padding-top:8px">
    Las marcas y logotipos mostrados pertenecen a sus respectivos titulares y se usan solo para identificar los productos originales que vendemos.
    Logo de Yanbal: <a href="https://commons.wikimedia.org/wiki/File:YANBAL.png" target="_blank" rel="noopener" style="display:inline;color:inherit">Ammelianus</a>, <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener" style="display:inline;color:inherit">CC BY 4.0</a>, vía Wikimedia Commons.
  </div>
</footer>

<a class="wa-float" href="<?= e(whatsapp_link('Hola Quinto Lumière 👋')) ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp"><?= icon('wa', 26) ?></a>

<section class="chat" data-chat aria-label="Asistente de compras">
  <button class="chat__fab" type="button" data-chat-toggle aria-expanded="false" aria-controls="chat-panel" aria-label="Abrir asistente Lumi: te ayudo a encontrar productos">
    <?= sun_svg(30, 'chat__sun') ?><span>¿Te ayudo a encontrar algo?</span>
  </button>
  <div class="chat__panel" id="chat-panel" hidden>
    <div class="chat__head">
      <div><strong>Lumi</strong><small>Asistente de Quinto Lumière</small></div>
      <button class="icon-btn" type="button" data-chat-toggle aria-label="Cerrar chat"><?= icon('close', 18) ?></button>
    </div>
    <div class="chat__log" data-chat-log aria-live="polite"></div>
    <form class="chat__form" data-chat-form>
      <label class="sr" for="chat-input">Escribe tu consulta</label>
      <input id="chat-input" type="text" autocomplete="off" maxlength="200" placeholder="Ej: tinte sin amoníaco rubio">
      <button class="icon-btn" aria-label="Enviar"><?= icon('send', 18) ?></button>
    </form>
  </div>
</section>
<?php endif; ?>
<?php if ($GLOBALS['GA_EVENTS']): ?>
<script>window.QL_EVENTS = <?= json_encode($GLOBALS['GA_EVENTS'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<?php endif; ?>
</body>
</html>
<?php
}

/** Colores de los tonos de un tinte (se cargan todos de una vez). */
function tint_swatches(int $pid): array
{
    static $map = null;
    if ($map === null) {
        $map = [];
        $in = implode(',', array_map(fn($s) => db()->quote($s), TINT_CATEGORIES));
        $rows = db()->query("SELECT v.product_id, v.label, v.color FROM product_variants v JOIN products p ON p.id = v.product_id
                             JOIN categories c ON c.id = p.category_id WHERE c.slug IN ($in) ORDER BY v.sort, v.id")->fetchAll();
        foreach ($rows as $r) if ($hex = variant_swatch($r)) $map[$r['product_id']][] = $hex;
    }
    return $map[$pid] ?? [];
}

/** Tarjeta de producto reutilizable. */
function product_card(array $p): string
{
    $out = (int) $p['stock'] <= 0;
    $price = (float) $p['min_price'];
    $from = (int) $p['variant_count'] > 1 && (float) $p['max_price'] > $price ? 'Desde ' : '';
    ob_start(); ?>
  <article class="card<?= $out ? ' is-out' : '' ?>">
    <a class="card__media" href="<?= url('producto.php?p=' . urlencode($p['slug'])) ?>">
      <img src="<?= e(product_cover($p)) ?>" alt="<?= e($p['name']) ?>" loading="lazy" width="400" height="400">
      <?php if ($out): ?><span class="tag tag--out">Agotado</span>
      <?php elseif (!empty($p['compare_price'])): ?><span class="tag">Oferta</span>
      <?php elseif ($p['featured']): ?><span class="tag tag--soft">Favorito</span><?php endif; ?>
    </a>
    <div class="card__body">
      <?php if (!empty($p['brand'])): ?><p class="card__brand"><?= e($p['brand']) ?></p><?php endif; ?>
      <h3 class="card__title"><a href="<?= url('producto.php?p=' . urlencode($p['slug'])) ?>"><?= e($p['name']) ?></a></h3>
      <p class="card__price">
        <?= $from ?><strong><?= money($price) ?></strong>
        <?php if (!empty($p['compare_price'])): ?><s><?= money($p['compare_price']) ?></s><?php endif; ?>
      </p>
      <?php if (is_tint($p) && ($dots = tint_swatches((int) $p['id']))): ?>
        <div class="card__dots" aria-label="<?= count($dots) ?> tonos disponibles">
          <?php foreach (array_slice($dots, 0, 7) as $hex): ?><span class="sw" style="--c:<?= e($hex) ?>"></span><?php endforeach; ?>
          <small><?= count($dots) ?> tonos</small>
        </div>
      <?php endif; ?>
    </div>
  </article>
<?php
    return ob_get_clean();
}
