<?php
/** Imagen elegante de reemplazo mientras el producto no tiene foto. */
$cat = preg_replace('/[^a-z0-9-]/', '', $_GET['c'] ?? '');
$title = mb_substr((string) ($_GET['t'] ?? ''), 0, 70);

$icons = [
    'perfumes'          => '<rect x="38" y="52" width="44" height="50" rx="8"/><rect x="50" y="38" width="20" height="14" rx="2"/><path d="M55 30h10v8H55z"/>',
    'tintes-sachet'     => '<path d="M36 34h48v64H36z"/><path d="M36 44h48M36 88h48"/><circle cx="60" cy="66" r="10"/>',
    'tintes-caja'       => '<path d="M34 40h52v58H34z"/><path d="M34 40l10-10h52l-10 10M86 40l10-10v58l-10 10"/><circle cx="60" cy="68" r="11"/>',
    'cuidado-capilar'   => '<path d="M44 44h32v56a6 6 0 0 1-6 6H50a6 6 0 0 1-6-6z"/><path d="M52 34h16v10H52zM56 26h12"/>',
    'shampoo-matizador' => '<path d="M44 44h32v56a6 6 0 0 1-6 6H50a6 6 0 0 1-6-6z"/><path d="M52 34h16v10H52z"/><path d="M50 70h20"/>',
    'tratamientos'      => '<path d="M60 26c12 18 22 30 22 44a22 22 0 0 1-44 0c0-14 10-26 22-44z"/>',
    'ampollas'          => '<path d="M52 40h16v56a8 8 0 0 1-16 0z"/><path d="M55 40V30l5-6 5 6v10"/>',
    'oxidantes'         => '<path d="M42 50h36v50a6 6 0 0 1-6 6H48a6 6 0 0 1-6-6z"/><path d="M50 38h20v12H50zM56 30h8"/>',
    'ondulacion'        => '<path d="M30 50c8-10 16 10 24 0s16 10 24 0 16 10 24 0M30 70c8-10 16 10 24 0s16 10 24 0 16 10 24 0M30 90c8-10 16 10 24 0s16 10 24 0 16 10 24 0"/>',
    'ceras-geles'       => '<rect x="34" y="56" width="52" height="38" rx="8"/><rect x="30" y="46" width="60" height="12" rx="4"/>',
    'cremas-corporales' => '<rect x="34" y="56" width="52" height="38" rx="8"/><rect x="30" y="46" width="60" height="12" rx="4"/><path d="M50 74h20"/>',
    'extensiones'       => '<path d="M44 28c-4 30 4 50-4 78M56 28c-4 30 4 50-4 78M68 28c-4 30 4 50-4 78M80 28c-4 30 4 50-4 78"/><path d="M40 30h44"/>',
    'cabezales'         => '<ellipse cx="60" cy="58" rx="22" ry="28"/><path d="M38 52c0-22 44-22 44 0M48 100h24l-4-14H52z"/>',
    'herramientas'      => '<circle cx="44" cy="92" r="9"/><circle cx="76" cy="92" r="9"/><path d="M50 85 82 30M70 85 38 30"/>',
];
$icon = $icons[$cat] ?? $icons['cuidado-capilar'];

// Paleta suave por categoría
$tones = ['#EFE6E1', '#EAE3DE', '#F1E8E4', '#E8E4E0', '#F0E6E6', '#ECE6E0'];
$bg = $tones[abs(crc32($cat)) % count($tones)];

// Partir el título en hasta 3 líneas
$words = preg_split('/\s+/', $title);
$lines = [''];
foreach ($words as $w) {
    if (mb_strlen(end($lines) . ' ' . $w) > 22 && count($lines) < 3) $lines[] = $w;
    else $lines[count($lines) - 1] = trim(end($lines) . ' ' . $w);
}
$text = '';
foreach ($lines as $i => $l) {
    $text .= '<text x="200" y="' . (300 + $i * 26) . '">' . htmlspecialchars($l, ENT_XML1, 'UTF-8') . '</text>';
}

header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=604800');
echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
  <rect width="400" height="400" fill="$bg"/>
  <circle cx="200" cy="150" r="92" fill="#F7F2EF"/>
  <g transform="translate(140 88)" fill="none" stroke="#5E6D73" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">$icon</g>
  <g font-family="Georgia, 'Times New Roman', serif" font-size="21" fill="#5E6D73" text-anchor="middle">$text</g>
  <g stroke="#D9A5A0" stroke-width="1.4" stroke-linecap="round"><line x1="170" y1="268" x2="230" y2="268"/></g>
</svg>
SVG;
