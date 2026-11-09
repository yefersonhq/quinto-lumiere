<?php
// Dibuja una carta nítida (800x800) por producto de tinte cuyas fotos empiezan con "carta-".
// Uso: php -c _dev/php.ini _dev/dibujar-cartas.php  (desde la carpeta del proyecto)
require __DIR__ . '/../inc/bootstrap.php'; // salida: _tmp/out (créala antes)
require_once __DIR__ . '/../inc/catalogo-extra.php';

$font = 'C:/Windows/Fonts/arial.ttf';
$bold = 'C:/Windows/Fonts/arialbd.ttf';
$black = 'C:/Windows/Fonts/ariblk.ttf';
$brandColor = ['Color Flex' => [218, 18, 125], 'Issue' => [32, 74, 135], 'Stereo Color' => [196, 30, 110]];

$rgb = fn(string $hex) => sscanf(ltrim($hex, '#'), '%02x%02x%02x');
$center = function ($im, $size, $f, $text, $cx, $y, $col) {
    $b = imagettfbbox($size, 0, $f, $text);
    imagettftext($im, $size, 0, (int) round($cx - ($b[2] - $b[0]) / 2), (int) $y, $col, $f, $text);
};
// parte el nombre en 2 líneas si no entra
$wrap = function (string $s, float $size, string $f, int $maxW): array {
    $words = explode(' ', $s); $lines = [''];
    foreach ($words as $w) {
        $try = trim(end($lines) . ' ' . $w);
        $b = imagettfbbox($size, 0, $f, $try);
        if ($b[2] - $b[0] > $maxW && end($lines) !== '') $lines[] = $w; else $lines[count($lines) - 1] = $try;
    }
    return array_slice($lines, 0, 2);
};

$n = 0;
foreach (catalogo_extra_6() as [$name, $brand, $cat, $desc, $tags, $variants, $featured, $imgs]) {
    $file = null;
    foreach ((array) $imgs as $f) if (str_starts_with($f, 'carta-')) $file = $f;
    if (!$file) continue;
    $S = 800;
    $im = imagecreatetruecolor($S, $S);
    imageantialias($im, true);
    $white = imagecolorallocate($im, 255, 255, 255);
    imagefilledrectangle($im, 0, 0, $S, $S, $white);
    [$r, $g, $b] = $brandColor[$brand] ?? [60, 70, 80];
    $head = imagecolorallocate($im, $r, $g, $b);
    imagefilledrectangle($im, 0, 0, $S, 96, $head);
    $title = explode(' — ', $name)[1] ?? '';
    $center($im, 26, $black, $brand === 'Stereo Color' ? 'EstereoColor' : $brand, $S / 2, 46, $white);
    $center($im, 15, $bold, mb_strtoupper($title), $S / 2, 80, $white);

    $count = count($variants);
    $cols = $count <= 9 ? 3 : ($count <= 16 ? 4 : 5);
    $rows = (int) ceil($count / $cols);
    $cw = $S / $cols; $ch = ($S - 112) / $rows;
    $d = (int) (min($cw, $ch) * 0.46);
    $ink = imagecolorallocate($im, 40, 48, 58);
    $grey = imagecolorallocate($im, 95, 105, 115);
    foreach ($variants as $i => [$label, $price, $stock, $hex]) {
        $cx = ($i % $cols) * $cw + $cw / 2;
        $top = 108 + intdiv($i, $cols) * $ch;
        [$cr, $cg, $cb] = $rgb($hex ?: '#777777');
        // mechón: círculo con degradado suave de claro (arriba) a oscuro (abajo)
        for ($k = 0; $k < $d; $k++) {
            $t = $k / $d; $lift = (int) (55 * (1 - $t));
            $col = imagecolorallocate($im, min(255, $cr + $lift), min(255, $cg + $lift), min(255, $cb + $lift));
            $half = sqrt(max(0, ($d / 2) ** 2 - ($k - $d / 2) ** 2));
            imageline($im, (int) ($cx - $half), (int) ($top + $k), (int) ($cx + $half), (int) ($top + $k), $col);
        }
        imageellipse($im, (int) $cx, (int) ($top + $d / 2), $d, $d, imagecolorallocatealpha($im, 0, 0, 0, 100));
        // código (primera palabra si tiene número) y nombre
        $parts = explode(' ', $label, 2);
        $hasCode = preg_match('/\d/', $parts[0]) || strlen($parts[0]) <= 2;
        $code = $hasCode ? $parts[0] : '';
        $nameTxt = $hasCode ? ($parts[1] ?? '') : $label;
        $y = $top + $d + ($code ? 26 : 22);
        if ($code) { $center($im, $rows > 4 ? 14 : 16, $bold, $code, $cx, $y, $ink); $y += 19; }
        foreach ($wrap(mb_strtolower($nameTxt) === $nameTxt ? ucfirst($nameTxt) : $nameTxt, $rows > 4 ? 9.5 : 10.5, $code ? $font : $bold, (int) $cw - 14) as $line) {
            $center($im, $rows > 4 ? 9.5 : 10.5, $code ? $font : $bold, $line, $cx, $y, $code ? $grey : $ink);
            $y += 15;
        }
    }
    imagejpeg($im, __DIR__ . "/../_tmp/out/$file.jpg", 92);
    imagedestroy($im);
    echo "$file ($count tonos)\n";
    $n++;
}
echo "$n cartas\n";
