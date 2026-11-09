<?php
/**
 * Catálogo ampliado (noviembre 2026): perfumes y maquillaje de Ésika, Yanbal, Natura y Avon,
 * y productos de barbería Bandido, con foto.
 *
 * Se agrega UNA sola vez a la base de datos (también a una tienda ya en uso):
 * solo inserta los productos cuyo slug todavía no existe. A los que ya tenías no les cambia nada,
 * salvo ponerles la foto si no tienen ninguna.
 * Las fotos van en uploads/catalogo/ (no se suben a GitHub).
 *
 * Precios: los de catálogo de la campaña vigente (Ésika C10, Avon C17, web de Natura y Yanbal Perú).
 * Los de Bandido son referenciales. Cambian cada campaña: revísalos en Admin → Inventario.
 */

function catalogo_extra(): array
{
    // Atajos: [nombre, marca, categoría, descripción, etiquetas, variantes [[etiqueta, precio, stock, color]], destacado, foto]
    $perfume = fn(string $name, string $brand, string $gender, string $notes, string $desc, array $variants, string $img, int $featured = 0) => [
        "Perfume $name — $brand", $brand, 'perfumes',
        $desc . "\nProducto original de catálogo. Si no lo tenemos en stock inmediato, lo pedimos en la campaña vigente: escríbenos y te confirmamos precio y fecha de entrega.",
        "perfume fragancia colonia catalogo $brand $gender " . ($gender === 'mujer' ? 'femenino dama ella regalo' : ($gender === 'hombre' ? 'masculino caballero varon el regalo' : 'unisex regalo')) . " $notes",
        $variants, $featured, $img,
    ];

    $P = [];

    // ---------------- ÉSIKA (catálogo C10 2026) ----------------
    $P[] = $perfume('Expression', 'Ésika', 'mujer', 'floral flores blancas jazmin azahar flor de naranjo clasico',
        'Fragancia femenina floral de flores blancas: un clásico bouquet con notas de jazmín y flor de naranjo. Concentración alta.',
        [['Eau de parfum 50 ml', 69.90, 3, null]], 'esika-expression');
    $P[] = $perfume('City Love', 'Ésika', 'mujer', 'floral frutal lirio del valle mandarina vetiver luminoso',
        'Perfume floral frutal y luminoso con notas de lirio del valle, aceite esencial de mandarina de Madagascar y vetiver. Concentración alta.',
        [['Perfume 50 ml', 59.90, 3, null]], 'esika-city-love', 1);
    $P[] = $perfume('Dream Nova', 'Ésika', 'mujer', 'floral frutal pera magnolia cedro',
        'Floral frutal con brillantes notas de pera francesa, esencia de flor de magnolia y cálida madera de cedro.',
        [['Eau de parfum 45 ml', 59.90, 3, null]], 'esika-dream-nova');
    $P[] = $perfume('Dream', 'Ésika', 'mujer', 'floral fresca citricos lirios sandalo',
        'Floral fresca: chispeantes notas cítricas que se envuelven con un delicado bouquet de lirios y madera de sándalo.',
        [['Eau de parfum 45 ml', 59.90, 3, null]], 'esika-dream');
    $P[] = $perfume('Vibranza', 'Ésika', 'mujer', 'oriental dulce ambarada orquidea vainilla flor de cafe intenso noche',
        'Oriental dulce ambarada con irresistibles notas de orquídea de vainilla y adictiva flor de café. Concentración muy alta. N.° 1 en ventas de Ésika.',
        [['Parfum 45 ml', 72.90, 3, null]], 'esika-vibranza', 1);
    $P[] = $perfume('Vibranza Addiction', 'Ésika', 'mujer', 'oriental dulce amaderada rosa cassis oud intenso noche',
        'Oriental dulce amaderada: seductor elixir de pétalos de rosa, licor de cassis y maderas oud. Concentración muy alta.',
        [['Parfum 45 ml', 72.90, 3, null]], 'esika-vibranza-addiction');
    $P[] = $perfume('Vibranza Provocative', 'Ésika', 'mujer', 'oriental dulce floral grosella negra gardenia musk',
        'Oriental dulce floral con envolventes notas de grosella negra, gardenia cristalina y musk. Concentración muy alta.',
        [['Parfum 45 ml', 64.90, 3, null]], 'esika-vibranza-provocative');
    $P[] = $perfume('Impredecible', 'Ésika', 'mujer', 'floral frutal oriental dulce pera lirio sandalo bayas rojas vainilla',
        'Elige tu versión: Glamour, floral frutal con pera, lirios del valle y sándalo; o For Woman, oriental dulce frutal con bayas rojas y vainilla de Madagascar.',
        [['Glamour — Eau de parfum 50 ml', 54.90, 3, null], ['For Woman — Eau de parfum 50 ml', 54.90, 3, null]], 'esika-impredecible');
    $P[] = $perfume('Magnat Elite', 'Ésika', 'hombre', 'amaderado oriental cedro pimienta negra elegante',
        'Perfume masculino maderoso oriental con sofisticado aceite de cedro y notas de pimienta negra de Madagascar. Concentración muy alta.',
        [['Perfume 90 ml', 79.90, 3, null]], 'esika-magnat-elite');
    $P[] = $perfume('Kalos Champion', 'Ésika', 'hombre', 'herbal aromatica citrico lavanda musgo de roble deportivo',
        'Herbal aromática: explosivo mix cítrico junto a la frescura de la lavanda y la fuerza del musgo de roble.',
        [['Eau de toilette 100 ml', 64.90, 3, null]], 'esika-kalos-champion');
    $P[] = $perfume('You', 'Ésika', 'unisex', 'frutal fresco limon mandarina citrico juvenil',
        'Fragancia unisex frutal fresca con energizantes notas de limón y mandarina. N.° 1 en ventas de Ésika.',
        [['Eau de toilette 90 ml', 54.90, 3, null]], 'esika-you');

    $P[] = ['Labial Colorfix Barra 24H — Ésika', 'Ésika', 'maquillaje',
        "Labial en barra de larga duración (24 horas), acabado mate e intenso. 2 g.\nElige tu tono; si pides dos, pregunta por la promoción de la campaña.",
        'labial lapiz labios barra mate larga duracion colorfix esika maquillaje rojo nude rosa vino',
        [['Rojo seductor', 29.90, 4, '#a3243b'], ['Rosa esencia', 29.90, 4, '#b4697a'], ['Vino seductor', 29.90, 4, '#6d1f2e'],
         ['Rosa suspiro', 29.90, 4, '#c98a8a'], ['Coral natural', 29.90, 4, '#c4564f'], ['Vino latido', 29.90, 4, '#9b2b3a'],
         ['Nude art', 29.90, 4, '#9a5f4f'], ['Rosa soñadora', 29.90, 4, '#c07f7a'], ['Rosa guerrera', 29.90, 4, '#d23a72']], 0, 'esika-colorfix-labial'];
    $P[] = ['Máscara de pestañas Mega Full Size — Ésika', 'Ésika', 'maquillaje',
        'Máscara efecto largo extremo (hasta 10 veces más largo y 24 horas de volumen) con biotinil y pantenol. 8 g.',
        'mascara rimel pestañas volumen largo a prueba de agua esika maquillaje ojos',
        [['Negro — a prueba de agua', 21.90, 5, '#111111'], ['Azul — a prueba de agua', 21.90, 3, '#1f3f9a'],
         ['Negro-marrón — a prueba de agua', 21.90, 3, '#3b2a22'], ['Negro — fácil retirado', 21.90, 4, '#111111']], 0, 'esika-mega-full-size'];

    // ---------------- YANBAL (web Yanbal Perú) ----------------
    $P[] = $perfume('Zentro Privé', 'Yanbal', 'hombre', 'amaderado elegante intenso',
        'Eau de parfum masculino de la línea Zentro, versión Privé: elegante, intensa y de larga duración.',
        [['Eau de parfum', 144.00, 2, null]], 'yanbal-zentro-prive');
    $P[] = $perfume('L\'Essence Orquídea Exótica', 'Yanbal', 'mujer', 'floral orquidea colonia fresca diaria',
        'Colonia femenina L\'Essence de orquídea exótica, ideal para el uso diario.',
        [['Colonia', 34.00, 4, null]], 'yanbal-lessence-orquidea');
    $P[] = $perfume('Soy Sexy', 'Yanbal', 'mujer', 'colonia juvenil dulce',
        'Colonia femenina Soy Sexy, fresca y coqueta para todos los días.',
        [['Colonia', 34.00, 4, null]], 'yanbal-soy-sexy');
    $P[] = $perfume('Soy Glow', 'Yanbal', 'mujer', 'colonia juvenil frutal',
        'Colonia femenina Soy Glow, luminosa y frutal para todos los días.',
        [['Colonia', 34.00, 4, null]], 'yanbal-soy-glow');
    $P[] = $perfume('L\'Essence Mimosa Radiante', 'Yanbal', 'mujer', 'floral mimosa colonia fresca diaria',
        'Colonia femenina L\'Essence de mimosa radiante, floral y fresca.',
        [['Colonia', 37.00, 4, null]], 'yanbal-lessence-mimosa');
    $P[] = $perfume('43N Paralel', 'Yanbal', 'hombre', 'amaderado intenso elegante',
        'Parfum masculino 43N Paralel, de alta concentración y gran duración.',
        [['Parfum', 161.00, 2, null]], 'yanbal-43n-paralel');
    $P[] = $perfume('Indómito Wood Essence', 'Yanbal', 'hombre', 'amaderado madera intenso',
        'Perfume masculino Indómito en su versión Wood Essence, amaderado e intenso.',
        [['Perfume', 161.00, 2, null]], 'yanbal-indomito-wood');
    $P[] = $perfume('Liberatta', 'Yanbal', 'mujer', 'floral elegante intenso',
        'Parfum femenino Liberatta, de alta concentración, para la mujer libre y segura de sí misma.',
        [['Parfum', 120.00, 2, null]], 'yanbal-liberatta', 1);
    $P[] = $perfume('Ohm Now', 'Yanbal', 'hombre', 'citrico amaderado moderno',
        'Parfum masculino Ohm Now, la versión moderna de un clásico de Yanbal.',
        [['Parfum', 116.00, 2, null]], 'yanbal-ohm-now');
    $P[] = $perfume('Jaque', 'Yanbal', 'hombre', 'amaderado elegante intenso',
        'Parfum masculino Jaque, elegante y sofisticado.',
        [['Parfum', 222.00, 2, null]], 'yanbal-jaque');
    $P[] = $perfume('Ccori Rose', 'Yanbal', 'mujer', 'floral rosa elegante',
        'Parfum femenino Ccori Rose, floral y elegante, de la icónica línea Ccori.',
        [['Parfum', 214.00, 2, null]], 'yanbal-ccori-rose');

    $P[] = ['Micro-gel Dual para cejas — Yanbal', 'Yanbal', 'maquillaje',
        'Gel con color para cejas, define y fija con acabado natural.',
        'cejas gel color definir fijar yanbal maquillaje marron',
        [['Marrón cenizo', 39.50, 3, '#6b5a4c'], ['Marrón', 39.50, 3, '#5a3d2b']], 0, 'yanbal-microgel-dual'];
    $P[] = ['Base Ya! Instamate full cobertura — Yanbal', 'Yanbal', 'maquillaje',
        'Base de maquillaje de acabado mate y cobertura total.',
        'base maquillaje mate cobertura rostro piel yanbal instamate',
        [['Tono claro', 37.50, 3, '#e9c7a7'], ['Tono mediano claro', 37.50, 3, '#d9ae88'], ['Tono mediano', 37.50, 3, '#c8946b']], 0, 'yanbal-instamate-base'];
    $P[] = ['Labial Hydra-Lip Intense Tint Infused — Yanbal', 'Yanbal', 'maquillaje',
        'Labial hidratante con color intenso. Tono Chocolate Mousse.',
        'labial labios hidratante yanbal hydra lip chocolate maquillaje',
        [['Chocolate Mousse', 36.00, 3, '#7a4134']], 0, 'yanbal-hydra-lip'];
    $P[] = ['Esmalte de uñas — Yanbal', 'Yanbal', 'maquillaje',
        'Esmalte de uñas de color intenso y brillo duradero.',
        'esmalte uñas pintauñas color yanbal manicure',
        [['Golden Orange', 18.50, 4, '#d9772b'], ['Ever Green', 18.50, 4, '#1f5b3e'], ['Golden Wish', 18.50, 4, '#c9a24a']], 0, 'yanbal-esmalte'];

    // ---------------- NATURA (web Natura Perú) ----------------
    $P[] = $perfume('Ilía Secreto', 'Natura', 'mujer', 'floral oriental intenso noche',
        'Eau de parfum femenino Ilía Secreto: sensual y envolvente.',
        [['Eau de parfum 50 ml', 79.00, 3, null]], 'natura-ilia-secreto', 1);
    $P[] = $perfume('Kaiak Clásico Femenino', 'Natura', 'mujer', 'fresco acuatico citrico diario',
        'Eau de toilette femenino Kaiak Clásico, fresco y acuático para el día a día.',
        [['Eau de toilette 100 ml', 59.00, 3, null]], 'natura-kaiak-clasico-fem');
    $P[] = $perfume('Essencial Exclusivo Floral', 'Natura', 'mujer', 'floral elegante intenso',
        'Eau de parfum femenino Essencial Exclusivo Floral, sofisticado y duradero.',
        [['Eau de parfum 50 ml', 69.00, 3, null]], 'natura-essencial-exclusivo-floral');
    $P[] = $perfume('Luna Absoluta', 'Natura', 'mujer', 'floral oriental intenso noche',
        'Eau de toilette femenino Luna Absoluta, intenso y seductor.',
        [['Eau de toilette 75 ml', 69.00, 3, null]], 'natura-luna-absoluta');
    $P[] = $perfume('Kriska Shock', 'Natura', 'mujer', 'frutal dulce juvenil',
        'Eau de toilette femenino Kriska Shock, frutal y juvenil.',
        [['Eau de toilette 100 ml', 69.00, 3, null]], 'natura-kriska-shock');
    $P[] = $perfume('Humor Primero', 'Natura', 'mujer', 'floral frutal alegre',
        'Eau de toilette femenino Humor Primero, alegre y divertido.',
        [['Eau de toilette 75 ml', 59.00, 3, null]], 'natura-humor-primero');
    $P[] = $perfume('Ekos Frescor Maracuyá', 'Natura', 'unisex', 'frutal fresco maracuya colonia diaria',
        'Eau de toilette Ekos Frescor de maracuyá, refrescante y natural.',
        [['Eau de toilette 150 ml', 49.00, 3, null]], 'natura-ekos-maracuya');
    $P[] = ['Body splash Tododia Manzana caramelizada y vainilla — Natura', 'Natura', 'perfumes',
        'Body splash perfumado para el cuerpo con notas de manzana caramelizada y vainilla. 200 ml.',
        'body splash colonia corporal perfume cuerpo tododia natura manzana vainilla dulce',
        [['200 ml', 39.00, 4, null]], 0, 'natura-tododia-manzana-vainilla'];
    $P[] = $perfume('Kaiak Urbe', 'Natura', 'hombre', 'fresco amaderado urbano diario',
        'Eau de toilette masculino Kaiak Urbe, fresco y moderno.',
        [['Eau de toilette 100 ml', 59.00, 3, null]], 'natura-kaiak-urbe', 1);
    $P[] = $perfume('Kaiak Clásico Masculino', 'Natura', 'hombre', 'fresco acuatico citrico diario',
        'Eau de toilette masculino Kaiak Clásico, el aroma fresco y acuático más reconocido de Natura.',
        [['Eau de toilette 100 ml', 59.00, 3, null]], 'natura-kaiak-clasico-masc');
    $P[] = $perfume('Homem Clásico', 'Natura', 'hombre', 'amaderado clasico elegante',
        'Eau de toilette masculino Homem Clásico, amaderado y elegante.',
        [['Eau de toilette 100 ml', 79.00, 3, null]], 'natura-homem-clasico');
    $P[] = $perfume('Homem Essence', 'Natura', 'hombre', 'amaderado intenso noche',
        'Eau de parfum masculino Homem Essence, intenso y duradero.',
        [['Eau de parfum 100 ml', 69.00, 3, null]], 'natura-homem-essence');
    $P[] = $perfume('Essencial Oud', 'Natura', 'hombre', 'oud amaderado oriental intenso',
        'Eau de parfum masculino Essencial Oud, amaderado oriental de gran presencia.',
        [['Eau de parfum 100 ml', 99.00, 2, null]], 'natura-essencial-oud');
    $P[] = $perfume('Kaiak Océano', 'Natura', 'hombre', 'fresco acuatico marino deportivo',
        'Eau de toilette masculino Kaiak Océano, fresco y marino.',
        [['Eau de toilette 100 ml', 59.00, 3, null]], 'natura-kaiak-oceano');

    // ---------------- AVON (revista C17 2026 Perú) ----------------
    $P[] = $perfume('Far Away Glamour', 'Avon', 'mujer', 'floral oriental amaderada grosella negra vainilla almizcle',
        'Fragancia floral oriental amaderada con grosella negra, vainilla blanca y almizcle negro.',
        [['Perfume 50 ml', 59.90, 3, null]], 'avon-far-away-glamour', 1);
    $P[] = $perfume('Far Away Splendoria', 'Avon', 'mujer', 'oud blanco ciruela oriental',
        'Fragancia de 8 horas de duración con el exclusivo oud blanco y notas de ciruela.',
        [['Eau de parfum 50 ml', 46.90, 3, null]], 'avon-far-away-splendoria');
    $P[] = $perfume('Lov U', 'Avon', 'mujer', 'floral frutal frambuesa rosa cachemira diario',
        'Floral frutal ideal para el día: madera de cachemira con notas dulces de frambuesa y rosa.',
        [['Perfume 50 ml', 54.90, 3, null]], 'avon-lov-u');
    $P[] = $perfume('Imari', 'Avon', 'mujer', 'oriental citricos jazmin vainilla',
        'Fragancia oriental con notas de cítricos, jazmín y vainilla.',
        [['Eau de parfum 50 ml', 38.90, 3, null]], 'avon-imari');
    $P[] = $perfume('Secret Fantasy', 'Avon', 'mujer', 'floral frutal fresa dulce juvenil',
        'Fragancia floral frutal con la dulzura de la fresa.',
        [['Eau de parfum 50 ml', 38.90, 3, null]], 'avon-secret-fantasy');
    $P[] = $perfume('Attraction para Ella', 'Avon', 'mujer', 'frutal amaderada frambuesa mora almizcle ambar vainilla',
        'Frutal amaderada: frambuesas y moras, almizcle magnético y ámbar de vainilla.',
        [['Perfume 50 ml', 64.90, 3, null]], 'avon-attraction-ella');
    $P[] = $perfume('Soft Musk', 'Avon', 'mujer', 'floral musk rosa jazmin vainilla melocoton',
        'Floral musk con rosa, jazmín, vainilla y jugosos melocotones blancos.',
        [['Eau de parfum 50 ml', 38.90, 3, null]], 'avon-soft-musk');
    $P[] = $perfume('Latin Attitude', 'Avon', 'mujer', 'floral frutal mandarina fresa almizcle',
        'Floral frutal: mezcla de frescas mandarinas, fresas y almizcle.',
        [['Eau de toilette 50 ml', 38.90, 3, null]], 'avon-latin-attitude');
    $P[] = $perfume('Avon Collection', 'Avon', 'mujer', 'gourmand dulce cafe caramelo vainilla coco praline',
        'Línea de eau de parfum de 30 ml en seis aromas. Café Gourmand (naranja, café y ámbar), Caramel Rouge (azafrán, jazmín y caramelo), Paris en Rose (flor de durazno, nardo y cedro), Blush Bouquet (peonía, rosa y vainilla), Coco Lumière (coco, mango y sándalo) y Macaron Amour (pera, violeta y praliné).',
        [['Café Gourmand 30 ml', 32.90, 3, null], ['Caramel Rouge 30 ml', 32.90, 3, null], ['Paris en Rose 30 ml', 32.90, 3, null],
         ['Blush Bouquet 30 ml', 32.90, 3, null], ['Coco Lumière 30 ml', 32.90, 3, null], ['Macaron Amour 30 ml', 32.90, 3, null]], 'avon-collection');
    $P[] = $perfume('Iconic Collection Charisma', 'Avon', 'mujer', 'floral ambar jazmin lirio clasico',
        'Floral ámbar: carisma y magnetismo con jazmín y lirio de los valles.',
        [['Eau de parfum 100 ml', 87.90, 2, null]], 'avon-iconic-charisma');
    $P[] = $perfume('300 Km/h', 'Avon', 'hombre', 'citrico amaderado deportivo fresco',
        'Cítrica amaderada con notas verdes aromáticas y cítricos burbujeantes.',
        [['Eau de parfum 100 ml', 48.90, 3, null]], 'avon-300kmh');
    $P[] = $perfume('Musk Marine', 'Avon', 'hombre', 'acuatico herbal cardamomo menta lavanda fresco',
        'Herbal aromática con aroma acuático: cardamomo fresco, menta y lavanda.',
        [['Eau de parfum 75 ml', 33.90, 4, null]], 'avon-musk-marine');
    $P[] = $perfume('Exclusive Para Él', 'Avon', 'hombre', 'herbal fresca cardamomo romero sandalo',
        'Herbal fresca con cardamomo, romero y sándalo.',
        [['Eau de parfum 75 ml', 58.90, 3, null]], 'avon-exclusive-para-el');
    $P[] = $perfume('Black Suede Intense', 'Avon', 'hombre', 'amaderado dulce pimienta negra haba tonka violeta',
        'Oriental amaderado con pimienta negra, haba tonka y hojas de violeta.',
        [['Eau de toilette 100 ml', 50.90, 3, null]], 'avon-black-suede-intense', 1);
    $P[] = $perfume('Attraction Awaken Para Él', 'Avon', 'hombre', 'oriental amaderado nuez moscada naranja',
        'Oriental amaderada con nuez moscada, brandy de naranja y elixir de pasión.',
        [['Eau de parfum 75 ml', 75.90, 2, null]], 'avon-attraction-awaken-el');
    $P[] = $perfume('Wild Country Rush', 'Avon', 'hombre', 'aromatico amaderado pimienta lavanda cedro',
        'Aromática amaderada con pimienta, lavanda y cedro.',
        [['Eau de toilette 100 ml', 33.90, 3, null]], 'avon-wild-country-rush');
    $P[] = ['Spray corporal Avon Senses — Avon', 'Avon', 'perfumes',
        'Spray perfumado para el cuerpo, ligero y refrescante. 200 ml. Elige tu aroma.',
        'body splash spray corporal colonia cuerpo avon senses frutal floral',
        [['Flor de cerezo y orquídea', 21.90, 4, null], ['Piña rosada y guayaba', 21.90, 4, null], ['Arándano y orquídea', 21.90, 4, null],
         ['Frambuesa y cassis', 21.90, 4, null], ['Violeta y lichi', 21.90, 4, null]], 0, 'avon-senses'];

    // ---------------- BANDIDO (barbería) — precios referenciales ----------------
    $P[] = ['Cera Bandido Aqua Hard Wax 150 ml', 'Bandido', 'ceras-geles',
        'Cera a base de agua de fijación máxima, para peinados que duran todo el día. Se retira fácilmente con agua.',
        'cera bandido hard wax fijacion maxima fuerte peinado barberia hombre roja negra',
        [['Roja — fijación máxima', 14.90, 10, '#c62828'], ['Negra — fijación máxima', 14.90, 10, '#1b1b1b']], 1, 'bandido-hard-wax'];
    $P[] = ['Cera Bandido Aqua Gel Wax 150 ml', 'Bandido', 'ceras-geles',
        'Cera gel a base de agua con brillo y fijación fuerte. Elige tu variedad por color de envase.',
        'cera bandido gel wax aqua brillo fijacion peinado barberia azul verde mate morada naranja marron',
        [['Azul — fijación máxima', 13.90, 10, '#1e4fa3'], ['Verde — acabado mate', 13.90, 8, '#2e7d32'], ['Morada', 13.90, 8, '#6a1b9a'],
         ['Naranja', 13.90, 8, '#ef6c00'], ['Marrón', 13.90, 8, '#6d4c41']], 0, 'bandido-gel-wax'];
    $P[] = ['Cera Bandido Fiber Wax 150 ml', 'Bandido', 'ceras-geles',
        'Cera de fibra para peinados texturizados con acabado natural y fijación flexible.',
        'cera bandido fiber wax fibra textura mate natural peinado barberia',
        [['150 ml', 16.90, 8, null]], 0, 'bandido-fiber-wax'];
    $P[] = ['Cera Bandido Aqua Hair Wax 6 Violeta 125 ml', 'Bandido', 'ceras-geles',
        'Cera a base de agua, fijación extra fuerte, en lata metálica de colección.',
        'cera bandido aqua wax violeta lata fijacion peinado barberia',
        [['125 ml', 13.90, 8, null]], 0, 'bandido-aqua-hair-wax-violeta'];
    $P[] = ['Gel de afeitar Bandido', 'Bandido', 'barberia',
        "Gel de afeitar transparente que suaviza la barba y permite ver el contorno al rasurar. Hidrata la piel.\nIdeal para barberías.",
        'gel afeitar rasurar afeitado barba bandido shaving barberia',
        [['Azul 1000 ml', 24.90, 6, '#1e88e5'], ['Rojo 1000 ml', 24.90, 6, '#d32f2f'], ['Metallic 2 — 1000 ml', 24.90, 4, '#9e9e9e'],
         ['Metallic Gold 3 — 1000 ml', 24.90, 4, '#c9a24a'], ['Marijuana 500 ml', 16.90, 6, '#43a047']], 0, 'bandido-gel-afeitar'];
    $P[] = ['Crema after shave Bandido 350 ml', 'Bandido', 'barberia',
        'Crema para después del afeitado: calma, refresca e hidrata la piel.',
        'after shave crema despues afeitar bandido barberia hidratante',
        [['Fresh', 18.90, 6, null], ['New York', 18.90, 6, null], ['Cologen Sports', 18.90, 6, null]], 0, 'bandido-after-shave-cream'];
    $P[] = ['Colonia after shave Bandido 350 ml', 'Bandido', 'barberia',
        'Colonia para después del afeitado: refresca, tonifica y deja un aroma masculino.',
        'after shave colonia locion despues afeitar bandido barberia army lemon',
        [['Army', 18.90, 6, null], ['Lemon', 18.90, 6, null]], 0, 'bandido-after-shave-cologne'];
    $P[] = ['Aceite para barba Bandido 40 ml', 'Bandido', 'barberia',
        'Aceite que nutre en profundidad la barba y la piel, suaviza y da brillo.',
        'aceite barba beard oil bandido barberia nutrir suavizar',
        [['40 ml', 29.90, 6, null]], 0, 'bandido-aceite-barba'];
    $P[] = ['Laca Bandido Extremely Black 400 ml', 'Bandido', 'ceras-geles',
        'Laca de fijación extra fuerte y volumen que mantiene el peinado todo el día.',
        'laca spray fijador cabello volumen bandido peinado',
        [['400 ml', 19.90, 6, null]], 0, 'bandido-laca'];
    $P[] = ['Papel de cuello Bandido (neck strips)', 'Bandido', 'barberia',
        'Tiras de papel elástico para proteger el cuello del cliente durante el corte.',
        'papel cuello neck strips barberia peluqueria corte bandido',
        [['Paquete', 16.90, 6, null]], 0, 'bandido-papel-cuello'];
    $P[] = ['Aceite para máquina Bandido 200 ml', 'Bandido', 'barberia',
        'Aceite lubricante para máquinas de corte: protege las cuchillas y reduce el calor.',
        'aceite maquina clipper cuchillas lubricante barberia bandido',
        [['200 ml', 16.90, 6, null]], 0, 'bandido-aceite-maquina'];

    return $P;
}

/** Inserta el catálogo ampliado si aún no está (rápido: revisa un solo slug centinela). */
function import_catalogo_extra(PDO $pdo): void
{
    $sentinel = 'aceite-para-maquina-bandido-200-ml';
    $st = $pdo->prepare('SELECT 1 FROM products WHERE slug = ?');
    $st->execute([$sentinel]);
    if ($st->fetchColumn()) return;

    $cat = function (string $slug, string $name, string $icon) use ($pdo): int {
        $s = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
        $s->execute([$slug]);
        if ($id = $s->fetchColumn()) return (int) $id;
        $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort), 0) + 1 FROM categories')->fetchColumn();
        $pdo->prepare('INSERT INTO categories (name, slug, icon, sort) VALUES (?,?,?,?)')->execute([$name, $slug, $icon, $sort]);
        return (int) $pdo->lastInsertId();
    };
    $brand = function (string $name) use ($pdo): int {
        $s = $pdo->prepare('SELECT id FROM brands WHERE slug = ?');
        $s->execute([slugify($name)]);
        if ($id = $s->fetchColumn()) return (int) $id;
        $pdo->prepare('INSERT INTO brands (name, slug) VALUES (?,?)')->execute([$name, slugify($name)]);
        return (int) $pdo->lastInsertId();
    };
    $catNames = ['perfumes' => ['Perfumes de catálogo', 'perfume'], 'maquillaje' => ['Maquillaje', 'jar'],
        'ceras-geles' => ['Ceras y geles', 'jar'], 'barberia' => ['Barbería y afeitado', 'scissors']];

    $exists = $pdo->prepare('SELECT id FROM products WHERE slug = ?');
    $hasImg = $pdo->prepare('SELECT 1 FROM product_images WHERE product_id = ?');
    $insP = $pdo->prepare('INSERT INTO products (name, slug, brand_id, category_id, description, tags, featured, active, created_at, updated_at)
                           VALUES (?,?,?,?,?,?,?,1,?,?)');
    $insV = $pdo->prepare('INSERT INTO product_variants (product_id, label, sku, price, stock, sort, color) VALUES (?,?,?,?,?,?,?)');
    $insI = $pdo->prepare('INSERT INTO product_images (product_id, path, sort) VALUES (?,?,0)');

    $pdo->beginTransaction();
    try {
        foreach (catalogo_extra() as [$name, $brandName, $catSlug, $desc, $tags, $variants, $featured, $img]) {
            $slug = slugify($name);
            $path = "uploads/catalogo/$img.jpg";
            $exists->execute([$slug]);
            if ($oldId = $exists->fetchColumn()) {
                // Ya lo tenías: no se toca, solo se le pone foto si no tiene ninguna
                $hasImg->execute([$oldId]);
                if (!$hasImg->fetchColumn() && is_file(ROOT . '/' . $path)) $insI->execute([$oldId, $path]);
                continue;
            }
            [$cn, $ci] = $catNames[$catSlug];
            $insP->execute([$name, $slug, $brand($brandName), $cat($catSlug, $cn, $ci), $desc, $tags, $featured, now(), now()]);
            $pid = (int) $pdo->lastInsertId();
            foreach ($variants as $i => [$label, $price, $stock, $color]) {
                $insV->execute([$pid, $label, strtoupper(substr($slug, 0, 10)) . '-' . ($i + 1), $price, $stock, $i, $color]);
            }
            if (is_file(ROOT . '/' . $path)) $insI->execute([$pid, $path]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('catalogo-extra: ' . $e->getMessage());
    }
}
