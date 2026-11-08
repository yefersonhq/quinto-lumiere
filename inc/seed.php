<?php
/**
 * Catálogo inicial. Se carga UNA sola vez, cuando la base de datos está vacía.
 * Los PRECIOS y el STOCK son de ejemplo: revísalos en Admin → Productos.
 */

function seed_catalog(PDO $pdo): void
{
    $cats = [
        ['perfumes',          'Perfumes de catálogo',       'perfume'],
        ['tintes-sachet',     'Tintes en sachet',           'sachet'],
        ['tintes-caja',       'Tintes en caja',             'box'],
        ['cuidado-capilar',   'Shampoo y cuidado capilar',  'bottle'],
        ['shampoo-matizador', 'Shampoo matizador',          'bottle'],
        ['tratamientos',      'Tratamientos capilares',     'drop'],
        ['ampollas',          'Ampollas',                   'ampoule'],
        ['oxidantes',         'Oxidantes y activadores',    'bottle'],
        ['ondulacion',        'Ondulación',                 'wave'],
        ['ceras-geles',       'Ceras y geles',              'jar'],
        ['cremas-corporales', 'Cremas corporales',          'jar'],
        ['extensiones',       'Extensiones',                'hair'],
        ['cabezales',         'Cabezales de práctica',      'head'],
        ['herramientas',      'Tijeras y herramientas',     'scissors'],
    ];
    $catId = [];
    $st = $pdo->prepare('INSERT INTO categories (name, slug, icon, sort) VALUES (?,?,?,?)');
    foreach ($cats as $i => [$slug, $name, $icon]) {
        $st->execute([$name, $slug, $icon, $i]);
        $catId[$slug] = (int) $pdo->lastInsertId();
    }

    $brandNames = ['Avon', 'Ésika', 'Natura', 'Yanbal', 'Smoll', 'Stereo Color', 'Color Flex', 'Issue', 'Palette',
        'Placenta Life', 'Keracolor', 'Kuul', 'Evolution', 'Duo', 'BMT', 'Koleston', 'Radiant', 'Naturals',
        'Be Natural', 'Hanna', 'Bandido', 'Roldán', 'Lanshein'];
    // logos incluidos en assets/img/brands (los demás se suben desde Admin → Categorías y marcas)
    $logos = [];
    foreach (glob(ROOT . '/assets/img/brands/*') ?: [] as $f) {
        if (!preg_match('/\.(svg|png|webp|jpe?g)$/i', $f)) continue;
        $logos[pathinfo($f, PATHINFO_FILENAME)] = 'assets/img/brands/' . basename($f);
    }
    $brandId = [];
    $st = $pdo->prepare('INSERT INTO brands (name, slug, logo) VALUES (?,?,?)');
    foreach ($brandNames as $b) {
        $slug = slugify($b);
        $st->execute([$b, $slug, $logos[$slug] ?? null]);
        $brandId[$b] = (int) $pdo->lastInsertId();
    }

    // Carta de tonos de ejemplo (sistema internacional). Ajusta cada marca en Admin → Inventario.
    $sachetCodes = ['1.0', '2.0', '3.0', '4.0', '5.0', '6.0', '7.0', '8.0', '9.0', '4.5', '5.5', '6.66', '7.4', '7.3', '8.1', '0.11'];
    $boxCodes = ['1.0', '2.0', '3.0', '4.0', '5.0', '6.0', '7.0', '8.0', '9.0', '10.0', '4.5', '5.5', '5.6', '6.66', '6.4', '7.4',
        '6.3', '7.3', '8.3', '9.3', '7.1', '8.1', '9.1', '10.1', '9.2', '6.7', '8.8'];
    $tonosVar = fn(float $price, int $stock = 12, array $codes = []) => array_map(fn($c) => [tone_label($c), $price, $stock], $codes ?: $boxCodes);

    // [nombre, marca, categoría, descripción, etiquetas, variantes [[etiqueta, precio, stock]], destacado]
    $P = [];

    // Perfumes de catálogo: [nombre, género, notas conocidas, precio de referencia]
    // Los precios cambian cada campaña: actualízalos en Admin → Inventario.
    $perfumes = [
        'Avon' => [
            ['Far Away', 'mujer', 'oriental floral vainilla dulce', 59.90],
            ['Little Black Dress', 'mujer', 'floral elegante', 59.90],
            ['Luck', 'mujer', 'floral amaderado', 64.90],
            ['Incandessence', 'mujer', 'floral luminoso', 59.90],
            ['Today Tomorrow Always', 'mujer', 'floral romantico', 64.90],
            ['Black Suede', 'hombre', 'amaderado cuero', 54.90],
            ['Black Suede Real', 'hombre', 'amaderado intenso', 59.90],
            ['Black Suede Dark', 'hombre', 'amaderado nocturno', 59.90],
            ['Wild Country', 'hombre', 'clasico fresco', 44.90],
        ],
        'Ésika' => [
            ['Vibranza', 'mujer', '', 69.90],
            ['Expression', 'mujer', '', 69.90],
            ['Merci', 'mujer', '', 64.90],
            ['Vanilla Scent', 'mujer', 'vainilla dulce', 59.90],
            ['Impredecible Mysterious', 'mujer', '', 69.90],
            ['Fiori', 'mujer', 'floral', 59.90],
            ['It\'s You', 'mujer', '', 69.90],
            ['Dorsay', 'hombre', 'clasico', 69.90],
            ['Pulso Absolute', 'hombre', '', 69.90],
            ['Leyenda Absolute', 'hombre', '', 79.90],
            ['Vanilla Hombre', 'hombre', 'vainilla', 59.90],
        ],
        'Natura' => [
            ['Luna Absoluta', 'mujer', 'orquidea floral intenso', 129.90],
            ['Una', 'mujer', '', 149.90],
            ['Essencial Femenino', 'mujer', '', 149.90],
            ['Kaiak Aero Femenino', 'mujer', 'fresco ligero', 99.90],
            ['Kaiak Sonar', 'hombre', '', 99.90],
            ['Kaiak Urbe', 'hombre', '', 99.90],
            ['Kaiak Aero Masculino', 'hombre', 'fresco ligero', 99.90],
            ['Kaiak 21K', 'hombre', 'deportivo fresco', 99.90],
            ['Essencial Oud', 'hombre', 'oud amaderado intenso', 159.90],
        ],
        'Yanbal' => [
            ['Osadía', 'mujer', '', 89.90],
            ['Ccori Rose', 'mujer', 'floral rosa', 99.90],
            ['Cielo', 'mujer', '', 79.90],
            ['Xiss', 'mujer', '', 79.90],
            ['Ohm', 'hombre', 'citrico amaderado', 89.90],
            ['Solo Elixir', 'hombre', '', 99.90],
            ['Zentro', 'hombre', '', 79.90],
            ['Arom Element', 'hombre', 'herbal acuatico fresco', 79.90],
        ],
    ];
    foreach ($perfumes as $b => $list) {
        foreach ($list as $i => [$name, $gender, $notes, $price]) {
            $P[] = ["Perfume $name — $b", $b, 'perfumes',
                "Fragancia " . ($gender === 'mujer' ? 'femenina' : 'masculina') . " $name de $b" . ($notes ? ", de carácter " . str_replace(' ', ', ', $notes) : '') . ".\n"
                . "Producto original de catálogo. Si no lo tenemos en stock inmediato, lo pedimos en la campaña vigente: escríbenos y te confirmamos precio, presentación y fecha de entrega.",
                "perfume fragancia colonia catalogo $b $gender " . ($gender === 'mujer' ? 'femenino dama' : 'masculino caballero varon') . " regalo $notes",
                [['Eau de parfum / toilette', $price, 4]], $i === 0 ? 1 : 0];
        }
    }

    foreach (['Smoll', 'Stereo Color', 'Color Flex', 'Issue'] as $b) {
        $P[] = ["Tinte en sachet $b con amoníaco", $b, 'tintes-sachet',
            "Coloración permanente en sachet de $b. Cobertura de canas, color intenso y duradero. Rinde para una aplicación en cabello corto a medio.",
            'tinte coloracion color sachet con amoniaco permanente canas cobertura', $tonosVar(4.50, 12, $sachetCodes), $b === 'Smoll' ? 1 : 0];
    }
    $P[] = ['Tinte en sachet Smoll sin amoníaco', 'Smoll', 'tintes-sachet',
        'Coloración en sachet sin amoníaco: color suave, menos olor y más cuidado para el cabello sensibilizado.',
        'tinte coloracion sachet sin amoniaco suave semipermanente cabello sensible', $tonosVar(5.00, 12, $sachetCodes), 0];

    foreach (['Palette', 'Placenta Life', 'Keracolor', 'Kuul', 'Evolution', 'Duo', 'BMT', 'Koleston'] as $b) {
        $price = ['Koleston' => 28.90, 'Palette' => 19.90, 'Kuul' => 24.90, 'BMT' => 22.90][$b] ?? 18.90;
        $P[] = ["Tinte en caja $b con amoníaco", $b, 'tintes-caja',
            "Kit de coloración permanente $b: incluye crema colorante, revelador y tratamiento post color. Cobertura total de canas.",
            'tinte coloracion caja kit con amoniaco permanente canas revelador', $tonosVar($price, 6), in_array($b, ['Koleston', 'Kuul'], true) ? 1 : 0];
    }
    foreach (['Radiant', 'Naturals'] as $b) {
        $P[] = ["Tinte en caja $b sin amoníaco", $b, 'tintes-caja',
            "Coloración en caja $b sin amoníaco, con aceites nutritivos. Ideal para cabellos maltratados o con sensibilidad.",
            'tinte coloracion caja kit sin amoniaco suave nutritivo', $tonosVar(21.90, 6), 0];
    }

    $care = [
        ['Placenta Life', ['Shampoo' => 18.90, 'Acondicionador' => 18.90, 'Mascarilla' => 24.90, 'Crema de peinar' => 16.90]],
        ['Be Natural',    ['Shampoo' => 22.90, 'Mascarilla' => 27.90, 'Crema de peinar' => 19.90]],
        ['Radiant',       ['Shampoo' => 15.90, 'Acondicionador' => 15.90, 'Crema de peinar' => 14.90]],
        ['Hanna',         ['Shampoo' => 13.90, 'Acondicionador' => 13.90, 'Mascarilla' => 17.90]],
    ];
    $careTags = [
        'Shampoo'         => 'shampoo champu lavado limpieza cabello',
        'Acondicionador'  => 'acondicionador suavidad desenredar brillo',
        'Mascarilla'      => 'mascarilla tratamiento hidratacion reparacion cabello seco danado',
        'Crema de peinar' => 'crema de peinar peinado rizos frizz definicion',
    ];
    foreach ($care as [$b, $items]) {
        foreach ($items as $type => $price) {
            $P[] = ["$type $b", $b, 'cuidado-capilar',
                "$type $b para el cuidado diario del cabello. Nutre, aporta brillo y suavidad.",
                $careTags[$type] . ' ' . $b, [['Presentación regular', $price, 15]], $b === 'Placenta Life' && $type === 'Mascarilla' ? 1 : 0];
        }
    }

    $P[] = ['Shampoo matizador violeta', null, 'shampoo-matizador',
        'Neutraliza tonos amarillos y anaranjados en cabellos rubios, decolorados, con mechas o canas. Úsalo 1 a 2 veces por semana.',
        'shampoo matizador violeta morado silver rubio decolorado mechas canas amarillo platinado',
        [['250 ml', 19.90, 15], ['500 ml', 32.90, 10], ['1000 ml', 54.90, 6]], 1];

    $trat = [
        ['Tratamiento bifásico', 'Fórmula de dos fases que hidrata, desenreda y da brillo al instante. Sin enjuague.', 'bifasico hidratacion desenredante brillo sin enjuague spray'],
        ['Lanshein', 'Tratamiento de brillo intenso y sellado de cutícula para cabello opaco.', 'brillo sellado cuticula opaco lanshein'],
        ['Lan Wave', 'Tratamiento para definir y nutrir ondas y rizos, controla el frizz.', 'rizos ondas definicion frizz lan wave'],
        ['Color Clean', 'Removedor de color de tinte: corrige tonos no deseados sin decolorar.', 'removedor quitar tinte color clean corrector'],
        ['Lan Thermic', 'Protector térmico para plancha y secadora, evita el quiebre.', 'protector termico plancha secadora calor lan thermic'],
    ];
    foreach ($trat as [$n, $d, $t]) {
        $P[] = [$n, $n === 'Tratamiento bifásico' ? null : 'Lanshein', 'tratamientos', $d, 'tratamiento capilar ' . $t,
            [['Presentación regular', 24.90, 10]], $n === 'Tratamiento bifásico' ? 1 : 0];
    }

    $P[] = ['Ampolla capilar reparadora', null, 'ampollas',
        'Ampolla de tratamiento intensivo: reparación, brillo y nutrición en una sola aplicación.',
        'ampolla amopolla ampollas tratamiento reparacion nutricion caida brillo',
        [['Unidad', 3.50, 40], ['Caja x 12', 36.00, 5]], 0];

    $P[] = ['Oxidante / activador para tinte', null, 'oxidantes',
        'Agua oxigenada en crema para activar tintes y decolorantes. Disponible en 10, 20, 30 y 40 volúmenes (indícalo en la nota del pedido).',
        'oxigenta oxigenada oxidante activador revelador peroxido volumen 10 20 30 40 vol tinte decolorante',
        [['100 ml', 3.50, 30], ['500 ml', 9.90, 20], ['1000 ml', 16.90, 12]], 1];

    $P[] = ['Líquido de ondulación permanente', null, 'ondulacion',
        'Líquido para permanente / ondulación de cabello. Rizos definidos y duraderos.',
        'liquido ondulacion permanente rizos ondas rizado',
        [['114 ml', 9.90, 15], ['500 ml', 29.90, 8], ['1000 ml', 49.90, 5]], 0];

    $P[] = ['Cera para cabello Bandido', 'Bandido', 'ceras-geles',
        'Cera modeladora Bandido de fijación fuerte con acabado natural. Elige tu variedad por color de envase.',
        'cera bandido peinado fijacion modelar barberia hombre',
        [['Rojo', 12.90, 12], ['Negro', 12.90, 12], ['Blanco', 12.90, 12], ['Dorado', 12.90, 12], ['Azul', 12.90, 12]], 1];

    $P[] = ['Gel Roldán', 'Roldán', 'ceras-geles',
        'Gel fijador Roldán, fijación duradera y sin residuos blancos.',
        'gel roldan fijador fijacion peinado',
        [['120 g', 4.50, 25], ['250 g', 7.90, 20], ['500 g', 12.90, 12], ['1000 g', 21.90, 8]], 0];

    $P[] = ['Crema corporal hidratante', null, 'cremas-corporales',
        'Crema corporal de rápida absorción que hidrata y perfuma la piel durante todo el día.',
        'crema corporal cuerpo piel hidratante humectante locion',
        [['200 ml', 14.90, 15], ['400 ml', 24.90, 10]], 0];

    $P[] = ['Extensiones de cabello con clip', null, 'extensiones',
        'Extensiones de cabello con clips, fáciles de colocar. Consulta tonos disponibles por WhatsApp.',
        'extensiones cabello clip pelo largo volumen postizo',
        [['Negro natural', 59.90, 5], ['Castaño', 59.90, 5], ['Rubio', 64.90, 3]], 0];

    $P[] = ['Cabezal de práctica para peluquería', null, 'cabezales',
        'Cabeza de maniquí con cabello para practicar cortes, peinados y colorimetría.',
        'cabezal cabeza maniqui practica peluqueria estudiante academia',
        [['Cabello sintético', 45.00, 4], ['Cabello natural', 120.00, 2]], 0];

    $P[] = ['Tijera de corte profesional', null, 'herramientas',
        'Tijera de acero inoxidable para corte de cabello, filo preciso y agarre ergonómico.',
        'tijera tijeras corte peluqueria barberia profesional',
        [['5.5 pulgadas', 39.90, 6], ['6 pulgadas', 44.90, 6], ['Tijera de entresacar', 49.90, 4]], 0];

    $insP = $pdo->prepare('INSERT INTO products (name, slug, brand_id, category_id, description, tags, featured, active, created_at, updated_at)
                           VALUES (?,?,?,?,?,?,?,1,?,?)');
    $insV = $pdo->prepare('INSERT INTO product_variants (product_id, label, sku, price, stock, sort) VALUES (?,?,?,?,?,?)');
    $slugs = [];
    foreach ($P as [$name, $brand, $cat, $desc, $tags, $variants, $featured]) {
        $slug = slugify($name);
        while (isset($slugs[$slug])) $slug .= '-2';
        $slugs[$slug] = true;
        $insP->execute([$name, $slug, $brand ? $brandId[$brand] : null, $catId[$cat], $desc, $tags, $featured, now(), now()]);
        $pid = (int) $pdo->lastInsertId();
        foreach ($variants as $i => [$label, $price, $stock]) {
            $insV->execute([$pid, $label, strtoupper(substr(slugify($name), 0, 10)) . '-' . ($i + 1), $price, $stock, $i]);
        }
    }
}
