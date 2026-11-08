<?php
/**
 * Colorimetría capilar (sistema internacional de numeración).
 *   Nivel (1 = negro … 10 = rubio clarísimo, 11–12 = superaclarantes) + reflejos.
 *   7.3 → nivel 7 (rubio medio), reflejo principal 3 (dorado).
 *   6.66 → reflejo doble = intenso.   5.35 → dorado con toque caoba.
 * Los colores de pantalla son aproximados: cada marca tiene su propia carta.
 */

const TINT_CATEGORIES = ['tintes-sachet', 'tintes-caja'];

const TONE_LEVELS = [
    1 => ['Negro', '#1c1614'], 2 => ['Castaño muy oscuro', '#2b1e18'], 3 => ['Castaño oscuro', '#3c291f'],
    4 => ['Castaño medio', '#503526'], 5 => ['Castaño claro', '#684630'], 6 => ['Rubio oscuro', '#835d3c'],
    7 => ['Rubio medio', '#9e774c'], 8 => ['Rubio claro', '#bb9562'], 9 => ['Rubio muy claro', '#d4b27d'],
    10 => ['Rubio clarísimo', '#e6cc99'], 11 => ['Superaclarante', '#eed8ab'], 12 => ['Superaclarante especial', '#f3e2bd'],
];

// reflejo => [nombre, color del pigmento, intensidad de mezcla]
const TONE_REFLECTS = [
    0 => ['natural', null, 0], 1 => ['ceniza', '#8d979c', .42], 2 => ['irisado', '#8b7290', .40],
    3 => ['dorado', '#d9a647', .42], 4 => ['cobrizo', '#c8622c', .50], 5 => ['caoba', '#8f2c3b', .50],
    6 => ['rojo', '#b51f2e', .55], 7 => ['marrón', '#5e4a33', .35], 8 => ['perla', '#9a8c96', .38],
];

/** Extrae el código de tono de una etiqueta como «7.3 Rubio medio dorado». */
function tone_parse(?string $label): ?array
{
    if (!$label || !preg_match('/^\s*(\d{1,2})[.,\/](\d{1,2})\b|^\s*(\d{1,2})\b(?![.,]?\d*\s*(ml|g|gr|cm|pulg|x))/iu', $label, $m)) return null;
    $level = (int) ($m[1] !== '' ? $m[1] : $m[3]);
    $refl = isset($m[2]) && $m[2] !== '' ? $m[2] : '0';
    if ($level > 12) return null;
    $r = array_map('intval', str_split($refl));
    if (array_filter($r, fn($x) => $x > 8)) return null;
    return ['level' => $level, 'reflects' => $r, 'code' => $level . '.' . $refl];
}

function tone_name(array $t): string
{
    if ($t['level'] === 0) return 'Mix ' . (TONE_REFLECTS[$t['reflects'][0]][0] ?? '') . ' (corrector)';
    $name = TONE_LEVELS[$t['level']][0] ?? 'Nivel ' . $t['level'];
    $r = $t['reflects'];
    if ($r === [0] || $r === [0, 0]) return $name . ($r === [0, 0] ? ' natural intenso' : ' natural');
    $parts = [];
    if ($r[0] === 0 && isset($r[1])) $parts[] = TONE_REFLECTS[$r[1]][0] . ' suave';
    else {
        $parts[] = TONE_REFLECTS[$r[0]][0];
        if (isset($r[1]) && $r[1] === $r[0]) $parts[0] .= ' intenso';
        elseif (isset($r[1]) && $r[1] > 0) $parts[] = TONE_REFLECTS[$r[1]][0];
    }
    return $name . ' ' . implode(' ', $parts);
}

function hex_mix(string $a, string $b, float $t): string
{
    [$r1, $g1, $b1] = sscanf($a, '#%02x%02x%02x');
    [$r2, $g2, $b2] = sscanf($b, '#%02x%02x%02x');
    return sprintf('#%02x%02x%02x', round($r1 + ($r2 - $r1) * $t), round($g1 + ($g2 - $g1) * $t), round($b1 + ($b2 - $b1) * $t));
}

function tone_hex(array $t): string
{
    if ($t['level'] === 0) return hex_mix('#e9e2dc', TONE_REFLECTS[$t['reflects'][0]][1] ?? '#999999', .7);
    $hex = TONE_LEVELS[$t['level']][1] ?? '#7a5a40';
    $r = $t['reflects'];
    $light = $t['level'] / 12;
    if ($r[0] > 0) {
        [$_, $pig, $k] = TONE_REFLECTS[$r[0]];
        $k *= (isset($r[1]) && $r[1] === $r[0]) ? 1.45 : 1;
        $k *= .55 + $light * .6; // los reflejos se notan más en tonos claros
        $hex = hex_mix($hex, $pig, min(.85, $k));
    }
    if (isset($r[1]) && $r[1] > 0 && $r[1] !== $r[0]) {
        $hex = hex_mix($hex, TONE_REFLECTS[$r[1]][1], .18 + ($r[0] === 0 ? .2 : 0));
    }
    return $hex;
}

/** Color de una variante: el que puso el admin, o el calculado por su código. */
function variant_swatch(array $v): ?string
{
    if (!empty($v['color']) && preg_match('/^#[0-9a-f]{6}$/i', $v['color'])) return $v['color'];
    $t = tone_parse($v['label'] ?? '');
    return $t ? tone_hex($t) : null;
}

function is_tint(array $p): bool
{
    return in_array($p['category_slug'] ?? '', TINT_CATEGORIES, true);
}

/** Etiqueta estándar para un código: «7.3 Rubio medio dorado». */
function tone_label(string $code): string
{
    $t = tone_parse($code);
    return $t ? $code . ' ' . tone_name($t) : $code;
}

/**
 * Recomendación profesional según el color actual y el tono deseado.
 * Devuelve: oxidante (vol), pasos, avisos, mezcla para canas y combinaciones.
 */
function tone_advice(int $current, bool $dyed, string $canas, array $target): array
{
    $L = $target['level'];
    $diff = $L - $current;
    $refl = $target['reflects'][0];
    $out = ['vol' => null, 'steps' => [], 'warnings' => [], 'mix' => null, 'combos' => [], 'needs_bleach' => false, 'matizador' => false];

    if ($target['level'] === 0) {
        $out['steps'][] = 'Los tonos 0.xx son correctores: se mezclan con otro tinte (unos centímetros por tubo) para neutralizar o intensificar un reflejo. No se usan solos.';
        $out['vol'] = 20;
        return $out;
    }

    if ($diff <= 0) {
        $out['vol'] = $canas === 'no' && $diff < 0 ? 10 : 20;
        $out['steps'][] = $diff === 0
            ? "Te quedas en el mismo nivel ({$L}): el tinte solo cambia el reflejo."
            : 'Vas a oscurecer ' . abs($diff) . ' nivel' . (abs($diff) > 1 ? 'es' : '') . '. Solo depositas color, no necesitas aclarar.';
        if ($diff <= -3 && $dyed === false && $current >= 8) {
            $out['warnings'][] = 'Al oscurecer mucho un cabello claro, el color puede quedar verdoso o apagado. Aplica antes una pre-pigmentación con un tono cobrizo (.4) o dorado (.3) del mismo nivel que buscas.';
        }
    } elseif ($dyed) {
        $out['needs_bleach'] = true;
        $out['warnings'][] = 'Un tinte no aclara otro tinte. Como tu cabello ya está teñido, para subir de tono necesitas decolorar primero (o usar un removedor de color si el tinte es reciente) y luego aplicar el tono deseado.';
        $out['vol'] = 20;
    } elseif ($diff <= 2) {
        $out['vol'] = 20;
        $out['steps'][] = "Aclaras {$diff} nivel" . ($diff > 1 ? 'es' : '') . ': con oxidante de 20 volúmenes es suficiente.';
    } elseif ($diff === 3) {
        $out['vol'] = 30;
        $out['steps'][] = 'Aclaras 3 niveles: usa oxidante de 30 volúmenes.';
    } elseif ($diff === 4 || ($diff <= 5 && $L >= 11)) {
        $out['vol'] = 40;
        $out['steps'][] = "Aclaras {$diff} niveles: usa oxidante de 40 volúmenes (o una serie superaclarante con 40 vol, mezcla 1 + 2).";
        $out['warnings'][] = 'El oxidante de 40 vol nunca debe tocar el cuero cabelludo por mucho tiempo. Ideal en manos de una profesional.';
    } else {
        $out['needs_bleach'] = true;
        $out['vol'] = 30;
        $out['warnings'][] = "Subir {$diff} niveles es demasiado para un tinte. Necesitas decolorar primero y luego tonalizar con el tono {$target['code']}.";
    }

    // pigmento de fondo que aparece al aclarar
    if ($diff >= 2 && $current <= 5 && $L >= 7 && !in_array($refl, [1, 2, 8], true)) {
        $out['warnings'][] = 'Al aclarar desde un castaño aparecen fondos anaranjados o amarillos. Si quieres un rubio frío, elige un tono ceniza (.1) o irisado (.2), o usa luego shampoo matizador.';
    }

    // canas
    if ($canas !== 'no') {
        $natural = $L . '.0';
        if ($canas === 'mucha' && $refl > 0) {
            $out['mix'] = "Para cubrir más de 50% de canas con un tono de fantasía, mezcla mitad {$target['code']} + mitad {$natural} (natural) y usa oxidante de 20 vol.";
        } elseif ($canas === 'media' && $refl > 0) {
            $out['mix'] = "Para 30–50% de canas, mezcla 2 partes de {$target['code']} + 1 parte de {$natural} (natural).";
        } else {
            $out['mix'] = 'Para cubrir canas usa tinte permanente con amoníaco y oxidante de 20 vol. Deja actuar el tiempo completo (normalmente 35–45 minutos).';
        }
        if ($out['vol'] === 10) $out['vol'] = 20;
        $out['steps'][] = 'Los tintes sin amoníaco cubren hasta ~50% de canas; para más, elige uno con amoníaco.';
    }

    // proporción
    $out['steps'][] = $L >= 11 ? 'Proporción de mezcla habitual: 1 parte de tinte + 2 de oxidante.' : 'Proporción de mezcla habitual: 1 parte de tinte + 1 de oxidante (revisa siempre la caja: algunas marcas usan 1 + 1,5).';
    $out['steps'][] = 'Haz una prueba de alergia detrás de la oreja 48 horas antes, y una prueba de mechón.';

    // productos complementarios
    if ($L >= 8 || in_array($refl, [1, 2, 8], true)) {
        $out['matizador'] = true;
        $out['combos'][] = 'Mantén el rubio sin amarillos con shampoo matizador 1–2 veces por semana.';
    }
    if (in_array($refl, [4, 5, 6], true)) {
        $out['combos'][] = 'Los rojos y caobas se lavan rápido: lava con agua tibia y usa tratamiento para cabello teñido.';
    }
    // ideas de combinación de tonos (mechas / balayage)
    $hl = min(10, $L + 2);
    $hlRefl = in_array($refl, [4, 5, 6], true) ? '4' : ($refl === 0 ? '3' : (string) $refl);
    $out['combos'][] = "Iluminación (mechas o balayage): base {$target['code']} con mechas en {$hl}.{$hlRefl} para dar luz.";
    if ($L >= 4) $out['combos'][] = 'Profundidad (lowlights): mechones en ' . ($L - 1) . '.0 para dar volumen y un look más natural.';
    if ($refl === 0 && $L >= 6) $out['combos'][] = "Versión más cálida: {$L}.3 (dorado). Versión más fría: {$L}.1 (ceniza).";

    return $out;
}

/** Tonos estándar que mostramos en la carta (niveles 1–10 × reflejos). */
function tone_chart(): array
{
    $rows = [];
    for ($l = 10; $l >= 1; $l--) {
        foreach ([0, 1, 2, 3, 4, 5, 6, 7, 8] as $r) {
            // combinaciones poco comunes en el mercado
            if ($l <= 2 && !in_array($r, [0, 1, 6], true)) continue;
            if ($l >= 9 && in_array($r, [5, 6, 7], true)) continue;
            $code = "$l.$r";
            $rows[$l][$r] = ['code' => $code, 't' => tone_parse($code)];
        }
    }
    return $rows;
}
