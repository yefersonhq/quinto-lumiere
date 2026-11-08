<?php
/**
 * "Lumi" — asistente de compras. Responde con productos reales del inventario.
 * No usa servicios externos: entiende intenciones comunes y busca en el catálogo.
 */
require dirname(__DIR__) . '/inc/bootstrap.php';
require dirname(__DIR__) . '/inc/search.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Método no permitido'], 405);
csrf_check();

// límite simple contra abuso: 40 mensajes por minuto por sesión
$now = time();
$_SESSION['chat_hits'] = array_values(array_filter($_SESSION['chat_hits'] ?? [], fn($t) => $t > $now - 60));
if (count($_SESSION['chat_hits']) >= 40) json_out(['text' => 'Vas muy rápido 😅 Espera un momento y vuelve a escribirme.']);
$_SESSION['chat_hits'][] = $now;

$msg = mb_substr(trim((string) post('message')), 0, 200);
$n = ' ' . norm($msg) . ' ';
$has = function (array $words) use ($n): bool {
    foreach ($words as $w) if (str_contains($n, ' ' . $w)) return true;
    return false;
};

$chips = ['Tintes sin amoníaco', 'Shampoo matizador', 'Oxidante 1000 ml', 'Perfumes Ésika', 'Cabello seco', 'Envíos'];

function card(array $p): array
{
    $from = (int) $p['variant_count'] > 1 && (float) $p['max_price'] > (float) $p['min_price'] ? 'desde ' : '';
    return [
        'name'  => $p['name'],
        'brand' => $p['brand'],
        'price' => $from . money($p['min_price']),
        'stock' => (int) $p['stock'] > 0,
        'url'   => url('producto.php?p=' . urlencode($p['slug'])),
        'img'   => product_cover($p),
    ];
}

function by_category(array $slugs, int $limit = 4, string $extra = ''): array
{
    $in = implode(',', array_fill(0, count($slugs), '?'));
    $st = db()->prepare(product_query("p.active = 1 AND c.slug IN ($in)" . ($extra ? " AND $extra" : ''), 'p.featured DESC, stock DESC, p.name', "LIMIT $limit"));
    $st->execute($slugs);
    return $st->fetchAll();
}

function reply(string $text, array $products = [], array $chips = [], ?array $link = null): never
{
    json_out(array_filter([
        'text' => $text,
        'products' => array_map('card', $products),
        'chips' => $chips,
        'link' => $link,
    ], fn($v) => $v !== [] && $v !== null));
}

$ship = cfg('shipping');
$wa = ['label' => 'Hablar con una asesora por WhatsApp', 'url' => whatsapp_link('Hola, vengo de la web y tengo una consulta: ' . $msg)];

if ($msg === '' || $msg === '__hola__') {
    reply("¡Hola! Soy Lumi ✨ Te ayudo a encontrar productos de la tienda.\nEscríbeme lo que buscas (por ejemplo «tinte rubio sin amoníaco» u «oxigenta 500 ml») o cuéntame qué necesita tu cabello.", [], $chips);
}

// ---------- intenciones generales ----------
if (preg_match('/^\s*(hola|holi|buenas|buenos dias|buenas tardes|buenas noches|hey|ola)\b/', norm($msg)) && count(search_tokens($msg)) === 0) {
    reply('¡Hola! 😊 ¿Qué producto estás buscando hoy?', [], $chips);
}
if ($has(['gracias', 'muchas gracias', 'genial', 'perfecto', 'ok gracias'])) {
    reply('¡Con gusto! Si necesitas algo más, aquí estoy 💛', [], ['Ver novedades', 'Envíos', 'Formas de pago']);
}
if ($has(['horario', 'direccion', 'donde estan', 'donde queda', 'ubicacion', 'ubicados', 'tienda fisica', 'local', 'stand', 'mercado'])) {
    reply("Estamos en {$GLOBALS['CONFIG']['address']}.\nTambién puedes recoger allí tus compras online sin costo. Para confirmar el horario de hoy, escríbenos por WhatsApp.", [], ['Envíos', 'Formas de pago'], ['label' => 'Ver en el mapa', 'url' => url('index.php#visitanos')]);
}
if ($has(['envio', 'envios', 'delivery', 'despacho', 'provincia', 'demora', 'cuanto tarda', 'llega', 'olva', 'shalom', 'enviar'])) {
    $free = (float) $ship['gratis_desde'] > 0 ? "\n🎁 Delivery gratis desde " . money($ship['gratis_desde']) . '.' : '';
    reply("Estas son nuestras opciones de entrega:\n• Recojo en tienda (Lurín): " . money($ship['recojo_tienda']) . "\n• Delivery Lima / Lurín: " . money($ship['lima']) . " (1 a 3 días hábiles)\n• Provincia por agencia: " . money($ship['provincia']) . " (2 a 5 días hábiles)$free", [], ['Formas de pago', 'Ver favoritos']);
}
if ($has(['pago', 'pagar', 'yape', 'plin', 'tarjeta', 'transferencia', 'efectivo', 'mercado pago', 'cuotas', 'visa'])) {
    reply("Puedes pagar con tarjeta de crédito o débito, Yape o PagoEfectivo a través de Mercado Pago 🔒, o por Yape/Plin/transferencia coordinando por WhatsApp.", [], ['Envíos', 'Ver favoritos']);
}
if ($has(['devolucion', 'devolver', 'cambio', 'cambiar', 'reclamo', 'garantia'])) {
    reply('Aceptamos cambios de productos cerrados y sellados dentro de 7 días. Si algo llegó dañado, avísanos en 48 horas con fotos y lo solucionamos.', [], [], ['label' => 'Leer términos y condiciones', 'url' => url('terminos.php')]);
}
if ($has(['asesor', 'asesora', 'humano', 'persona', 'whatsapp', 'llamar', 'hablar con', 'atencion'])) {
    reply('¡Claro! Te comunico con nuestra asesora por WhatsApp 👇', [], [], $wa);
}
if ($has(['novedades', 'nuevo', 'nuevos', 'recien llegado', 'favoritos', 'mas vendido', 'populares', 'ofertas', 'oferta', 'promocion'])) {
    $isOffer = $has(['oferta', 'ofertas', 'promocion']);
    $where = $isOffer ? 'p.active = 1 AND EXISTS (SELECT 1 FROM product_variants v WHERE v.product_id = p.id AND v.compare_price > v.price)' : 'p.active = 1';
    $order = $has(['nuevo', 'nuevos', 'novedades', 'recien']) ? 'p.created_at DESC, p.id DESC' : 'p.featured DESC, p.updated_at DESC';
    $list = db()->query(product_query($where, $order, 'LIMIT 5'))->fetchAll();
    if ($list) reply($isOffer ? 'Estas son nuestras ofertas del momento:' : 'Te muestro algunos de nuestros favoritos:', $list, ['Tintes', 'Perfumes', 'Tratamientos']);
    reply('Por ahora no tenemos ofertas activas, pero mira nuestros favoritos 👇', db()->query(product_query('p.active = 1 AND p.featured = 1', 'p.name', 'LIMIT 5'))->fetchAll());
}

// ---------- tonos de tinte y carta de colores ----------
$chartLink = ['label' => 'Abrir la carta de colores', 'url' => url('carta-de-colores.php')];
if (preg_match('/(?<![\d.])(\d{1,2})[.,\/](\d{1,2})(?![\d])/', $msg, $m) && !preg_match('/\d\s*(ml|g|gr|cm|pulg)/i', $msg)) {
    $code = (int) $m[1] . '.' . $m[2];
    $t = tone_parse($code);
    if ($t) {
        $in = implode(',', array_map(fn($s) => db()->quote($s), TINT_CATEGORIES));
        $rows = db()->query("SELECT v.label, p.id FROM product_variants v JOIN products p ON p.id = v.product_id JOIN categories c ON c.id = p.category_id
                             WHERE p.active = 1 AND v.stock > 0 AND c.slug IN ($in)")->fetchAll();
        $ids = [];
        foreach ($rows as $r) if (($rt = tone_parse($r['label'])) && $rt['code'] === $t['code']) $ids[$r['id']] = true;
        $list = [];
        if ($ids) $list = db()->query(product_query('p.id IN (' . implode(',', array_keys($ids)) . ')', 'p.featured DESC, p.name', 'LIMIT 5'))->fetchAll();
        log_search($msg, count($list), 'chat');
        $name = tone_name($t);
        $link = ['label' => "Ver el tono {$t['code']} en la carta de colores", 'url' => url('carta-de-colores.php?tono=' . $t['code']) . '#resultado'];
        if ($list) reply("El tono {$t['code']} es {$name}. Lo tenemos en estas marcas:", $list, ['¿Qué oxidante uso?', 'Tinte para canas'], $link);
        reply("El tono {$t['code']} es {$name}, pero ahora no lo tenemos en stock. En la carta de colores te muestro tonos parecidos.", [], ['Tintes en caja', 'Tintes en sachet'], $link);
    }
}
if ($has(['carta de colores', 'carta de color', 'que tono', 'cual tono', 'que color me', 'tonos', 'tono ', 'aclarar', 'oscurecer', 'colorimetria', 'que oxidante', 'cuantos volumenes', 'volumen de oxidante'])) {
    $txt = $has(['oxidante', 'volumen', 'volumenes'])
        ? "El oxidante depende de cuánto quieras aclarar:\n• Mismo tono u oscurecer: 10–20 vol\n• Aclarar 1–2 tonos: 20 vol\n• Aclarar 3 tonos: 30 vol\n• Aclarar 4 tonos: 40 vol\nOjo: un tinte no aclara otro tinte. En la carta de colores te digo el exacto según tu caso."
        : 'En nuestra carta de colores eliges el tono que quieres, me cuentas tu color actual y si tienes canas, y te digo qué oxidante usar, cómo mezclar y en qué marcas lo tenemos 🎨';
    reply($txt, by_category(['oxidantes'], 1), ['Tintes sin amoníaco', 'Tinte para canas', 'Shampoo matizador'], $chartLink);
}

// ---------- recomendaciones por necesidad ----------
$needs = [
    [['canas', 'cana', 'cubrir canas'], 'Para cubrir canas te recomiendo un tinte permanente con amoníaco y su oxidante de 20 vol. Si eliges un tono de fantasía (rojo, caoba, ceniza), mézclalo con el natural de su mismo número (.0) — en la carta de colores te digo la proporción:', ['tintes-caja', 'tintes-sachet', 'oxidantes'], "(p.tags LIKE '%canas%' OR c.slug = 'oxidantes')"],
    [['seco', 'reseco', 'danado', 'maltratado', 'quebradizo', 'quemado', 'reparar', 'hidratar', 'hidratacion', 'puntas abiertas', 'opaco'], 'Para cabello seco o dañado, estos productos nutren y reparan:', ['cuidado-capilar', 'tratamientos', 'ampollas'], "(p.tags LIKE '%hidrat%' OR p.tags LIKE '%repar%' OR p.tags LIKE '%brillo%' OR c.slug = 'ampollas')"],
    [['frizz', 'esponjado', 'rizos', 'rulos', 'ondas', 'crespo'], 'Para controlar el frizz y definir rizos:', ['cuidado-capilar', 'tratamientos'], "(p.tags LIKE '%rizos%' OR p.tags LIKE '%frizz%' OR p.tags LIKE '%peinar%')"],
    [['amarillo', 'naranja', 'anaranjado', 'decolorado', 'mechas', 'rubio platinado', 'platinado', 'balayage', 'reflejos'], 'Para neutralizar tonos amarillos en rubios y mechas, usa un shampoo matizador:', ['shampoo-matizador', 'tratamientos'], ''],
    [['plancha', 'secadora', 'calor', 'ondulador', 'alisar'], 'Si usas plancha o secadora, protege tu cabello con:', ['tratamientos'], "p.tags LIKE '%termic%'"],
    [['caida', 'se cae', 'debil', 'crecimiento'], 'Para cabello débil o con caída, prueba nuestras ampollas y tratamientos:', ['ampollas', 'tratamientos'], ''],
    [['regalo', 'regalar', 'cumpleanos', 'dia de la madre', 'aniversario', 'detalle'], 'Para regalar, los perfumes de catálogo y las cremas corporales siempre quedan bien 🎁', ['perfumes', 'cremas-corporales'], ''],
    [['barberia', 'barbero', 'peinado hombre', 'peinado para hombre', 'fijacion'], 'Para peinados con fijación, estos son los más pedidos:', ['ceras-geles'], ''],
    [['estudiante', 'academia', 'practicar', 'curso', 'aprender', 'salon', 'peluqueria', 'estilista'], 'Para tu salón o tus prácticas tenemos:', ['cabezales', 'herramientas', 'oxidantes'], ''],
    [['quitar tinte', 'remover tinte', 'removedor', 'me quedo oscuro', 'salio mal el tinte', 'corregir color'], 'Para retirar o corregir un tinte, usa un removedor de color:', ['tratamientos'], "p.tags LIKE '%removedor%'"],
];
foreach ($needs as [$words, $text, $cats, $extra]) {
    if ($has($words)) {
        $list = by_category($cats, 5, $extra);
        if (!$list) $list = by_category($cats, 5);
        log_search($msg, count($list), 'chat');
        if ($list) reply($text, $list, ['Formas de pago', 'Envíos'], $wa);
    }
}

// ---------- búsqueda en el catálogo ----------
$results = search_products($msg, 6);
log_search($msg, count($results), 'chat');

if (!$results) {
    $what = implode(' ', search_tokens($msg)) ?: $msg;
    reply("No encontré «{$what}» en la tienda online 😔 Puede que lo tengamos en el stand o lo podamos conseguir. ¿Le escribimos a la asesora?", [], ['Ver favoritos', 'Tintes', 'Perfumes'], $wa);
}

$top = $results[0];
$text = count($results) === 1 ? 'Encontré esto para ti:' : 'Encontré estas opciones:';
$inStock = array_filter($results, fn($p) => (int) $p['stock'] > 0);
if (!$inStock) $text = 'Lo tenemos en catálogo, pero ahora está agotado. Pregúntanos por WhatsApp cuándo llega:';

// complementos sugeridos
$complements = [
    'tintes-sachet' => ['oxidantes', 'Recuerda que necesitarás oxidante para aplicarlo 👇'],
    'tintes-caja'   => ['tratamientos', 'Para cuidar tu color después del tinte:', "p.tags NOT LIKE '%removedor%'"],
    'oxidantes'     => ['tintes-caja', '¿Ya tienes tu tinte?'],
    'ondulacion'    => ['tratamientos', 'Después de la permanente, hidrata con:'],
    'cuidado-capilar' => ['ampollas', 'Potencia tu rutina con una ampolla:'],
    'perfumes'      => ['cremas-corporales', 'Combínalo con una crema corporal:'],
    'herramientas'  => ['cabezales', 'Para practicar tus cortes:'],
    'shampoo-matizador' => ['tratamientos', 'Y para mantener el brillo:'],
];
$extraText = '';
$extraProducts = [];
if (isset($complements[$top['category_slug']]) && !$has(['solo'])) {
    [$cat, $t] = $complements[$top['category_slug']];
    $ids = array_column($results, 'id');
    $extraProducts = array_values(array_filter(by_category([$cat], 2, $complements[$top['category_slug']][2] ?? ''), fn($p) => !in_array($p['id'], $ids)));
    if ($extraProducts) $extraText = $t;
}

// respuesta en dos partes: resultados + complemento
$out = [
    'text' => $text,
    'products' => array_map('card', array_slice($results, 0, 5)),
    'chips' => array_slice(array_values(array_unique(array_filter([
        $top['brand'] ? 'Más de ' . $top['brand'] : null,
        $top['category'] ? 'Ver ' . mb_strtolower($top['category']) : null,
        'Envíos',
    ]))), 0, 3),
];
if (!$inStock) $out['link'] = $wa;
if ($extraText) {
    $out['followup'] = ['text' => $extraText, 'products' => array_map('card', $extraProducts)];
}
json_out($out);
