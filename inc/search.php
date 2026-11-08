<?php
/**
 * Motor de búsqueda de productos (lo usan la tienda y el chatbot "Lumi").
 * Entiende sinónimos, errores comunes de escritura, marcas, tamaños y
 * frases como "sin amoníaco". Todo sale de lo que cargas en el inventario:
 * mientras mejores sean el nombre, la descripción y las etiquetas, mejor busca.
 */

const SEARCH_STOP = ['de', 'del', 'la', 'el', 'los', 'las', 'un', 'una', 'unos', 'unas', 'y', 'o', 'en', 'para', 'por',
    'que', 'me', 'mi', 'mis', 'tu', 'te', 'se', 'lo', 'al', 'es', 'son', 'hay', 'tienen', 'tienes', 'tiene', 'quiero',
    'quisiera', 'busco', 'buscando', 'necesito', 'algo', 'favor', 'porfa', 'hola', 'buenas', 'cuanto', 'cuesta',
    'cuestan', 'precio', 'precios', 'vale', 'valen', 'recomienda', 'recomiendas', 'recomiendame', 'recomendacion',
    'recomendaciones', 'producto', 'productos', 'venden', 'vendes', 'cabello', 'pelo', 'pero', 'como', 'muy', 'mas',
    'bueno', 'buena', 'mejor', 'a', 'con', 'sin', 'stock', 'disponible', 'tienda', 'ver', 'puedo', 'comprar'];

/** Sinónimos y errores frecuentes → palabra que usamos en el catálogo. */
const SEARCH_ALIAS = [
    'tintes' => 'tinte', 'coloracion' => 'tinte', 'colorante' => 'tinte', 'tenir' => 'tinte', 'pintar' => 'tinte', 'tinta' => 'tinte',
    'oxigenta' => 'oxidante', 'oxigenada' => 'oxidante', 'oxigeno' => 'oxidante', 'agua' => 'oxidante', 'activador' => 'oxidante',
    'revelador' => 'oxidante', 'peroxido' => 'oxidante', 'oxidantes' => 'oxidante', 'oxigentas' => 'oxidante',
    'amopolla' => 'ampolla', 'amopollas' => 'ampolla', 'ampollas' => 'ampolla', 'ampoyas' => 'ampolla', 'ampoya' => 'ampolla',
    'champu' => 'shampoo', 'champoo' => 'shampoo', 'shampu' => 'shampoo', 'chanpu' => 'shampoo', 'shampoos' => 'shampoo', 'champus' => 'shampoo',
    'matizante' => 'matizador', 'violeta' => 'matizador', 'morado' => 'matizador', 'silver' => 'matizador', 'platinado' => 'matizador',
    'perfumes' => 'perfume', 'fragancia' => 'perfume', 'fragancias' => 'perfume', 'colonia' => 'perfume', 'colonias' => 'perfume',
    'mascarillas' => 'mascarilla', 'mask' => 'mascarilla', 'ceras' => 'cera', 'wax' => 'cera', 'geles' => 'gel', 'fijador' => 'gel',
    'extension' => 'extensiones', 'postizo' => 'extensiones', 'postizos' => 'extensiones',
    'cabezales' => 'cabezal', 'maniqui' => 'cabezal', 'cabeza' => 'cabezal', 'tijeras' => 'tijera',
    'permanente' => 'ondulacion', 'rizado' => 'ondulacion', 'ondulado' => 'ondulacion',
    'acondicionadores' => 'acondicionador', 'cremas' => 'crema', 'tratamientos' => 'tratamiento',
    // marcas mal escritas
    'yambal' => 'yanbal', 'yanval' => 'yanbal', 'esica' => 'esika', 'palet' => 'palette', 'paleta' => 'palette',
    'small' => 'smoll', 'smol' => 'smoll', 'coleston' => 'koleston', 'kolestone' => 'koleston', 'placenta' => 'placenta',
    'stereocolor' => 'stereo', 'colorflex' => 'flex', 'kul' => 'kuul', 'roldan' => 'roldan', 'bandido' => 'bandido',
    'radian' => 'radiant', 'natural' => 'natural', 'hana' => 'hanna', 'bifasico' => 'bifasico', 'lanshine' => 'lanshein',
];

function search_tokens(string $q): array
{
    $q = norm($q);
    $q = preg_replace('/(\d)\s*(ml|g|gr|grs|gramos|vol)\b/', '$1 $2', $q);
    $out = [];
    foreach (explode(' ', $q) as $t) {
        $t = trim($t, '.');
        if ($t === '' || in_array($t, SEARCH_STOP, true)) continue;
        if (in_array($t, ['gr', 'grs', 'gramos'], true)) $t = 'g';
        $t = SEARCH_ALIAS[$t] ?? $t;
        // plural simple: "mascarillas" → "mascarilla"
        if (strlen($t) > 4 && str_ends_with($t, 's') && !isset(SEARCH_ALIAS[$t])) $t = substr($t, 0, -1);
        $out[$t] = true;
    }
    return array_keys($out);
}

/** Índice de productos activos (en memoria; para catálogos de hasta varios miles va perfecto). */
function search_index(): array
{
    static $idx = null;
    if ($idx !== null) return $idx;
    $rows = db()->query(product_query('p.active = 1'))->fetchAll();
    $labels = [];
    foreach (db()->query('SELECT product_id, label FROM product_variants')->fetchAll() as $v) {
        $labels[$v['product_id']][] = $v['label'];
    }
    foreach ($rows as &$r) {
        $r['_name']  = norm($r['name']);
        $r['_brand'] = norm((string) $r['brand']);
        $r['_cat']   = norm((string) $r['category']);
        $r['_tags']  = norm((string) $r['tags']);
        $r['_var']   = norm(implode(' ', $labels[$r['id']] ?? []));
        $r['_desc']  = norm((string) $r['description']);
    }
    return $idx = $rows;
}

function field_has(string $field, string $tok): bool
{
    if ($field === '') return false;
    if (preg_match('/(^|\s)' . preg_quote($tok, '/') . '/', $field)) return true; // inicio de palabra
    return false;
}

/**
 * Busca productos. Devuelve filas de producto ordenadas por relevancia.
 */
function search_products(string $q, int $limit = 40): array
{
    $tokens = search_tokens($q);
    $nq = ' ' . norm($q) . ' ';
    $sinAmo = str_contains($nq, 'sin amoniaco');
    $conAmo = str_contains($nq, 'con amoniaco');
    $tokens = array_values(array_filter($tokens, fn($t) => !(($sinAmo || $conAmo) && $t === 'amoniaco')));
    if (!$tokens && !$sinAmo && !$conAmo) return [];

    $weights = ['_name' => 6, '_brand' => 6, '_cat' => 4, '_tags' => 3, '_var' => 3, '_desc' => 1];
    $res = [];
    foreach (search_index() as $p) {
        $score = 0; $hits = 0;
        foreach ($tokens as $t) {
            $best = 0;
            foreach ($weights as $f => $w) {
                if ($w > $best && field_has($p[$f], $t)) $best = $w;
            }
            if ($best) { $hits++; $score += $best; }
        }
        $blob = $p['_name'] . ' ' . $p['_tags'] . ' ' . $p['_desc'];
        if ($sinAmo) { if (str_contains($blob, 'sin amoniaco')) { $score += 8; $hits++; } else $score -= 6; }
        if ($conAmo) { if (str_contains($blob, 'con amoniaco')) { $score += 8; $hits++; } else $score -= 6; }
        if ($score <= 0) continue;
        $need = count($tokens) + ($sinAmo || $conAmo ? 1 : 0);
        $p['_hits'] = $hits;
        $p['_full'] = $hits >= $need;
        $p['_score'] = $score + ($p['_full'] ? 20 : 0) + ((int) $p['stock'] > 0 ? 2 : 0) + ($p['featured'] ? 1 : 0);
        $res[] = $p;
    }
    // si hay coincidencias completas, mostrar solo esas
    $full = array_filter($res, fn($p) => $p['_full']);
    if ($full) $res = array_values($full);
    usort($res, fn($a, $b) => $b['_score'] <=> $a['_score'] ?: strcmp($a['name'], $b['name']));
    return array_slice($res, 0, $limit);
}

function log_search(string $q, int $n, string $source): void
{
    $q = mb_substr(trim($q), 0, 200);
    if ($q === '') return;
    try {
        db()->prepare('INSERT INTO searches (q, results, source, created_at) VALUES (?,?,?,?)')->execute([$q, $n, $source, now()]);
    } catch (Throwable $e) { /* no es crítico */ }
}
