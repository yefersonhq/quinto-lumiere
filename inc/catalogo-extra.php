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

/**
 * Segunda tanda (noviembre 2026): tintes Kuul (carta de colores oficial), Placenta Life / Be Natural,
 * Hanna Caball y Lanosterin. Precios de Hanna y Lanosterin: tiendas peruanas (Kymsalons, Aruma);
 * Kuul y Placenta Life: referenciales (sus catálogos no traen precio en soles).
 */
function catalogo_extra_2(): array
{
    $P = [];

    // ---------------- KUUL COLOR SYSTEM (carta de colores oficial) ----------------
    // [código, nombre, color de la muestra de la carta]
    $kuul = fn(array $tones, float $price = 19.90, int $stock = 6) => array_map(fn($t) => ["{$t[0]} {$t[1]}", $price, $stock, $t[2]], $tones);
    $kuulDesc = "Tinte en crema Kuul Color System, coloración permanente de cobertura total de canas y color duradero. Mezcla 1:1.5 con oxidante Kuul (10, 20, 30 o 40 vol. según el resultado que busques).\nPregúntanos por el oxidante y la mezcla ideal para tu tono en la carta de colores.";
    $kuulTags = 'tinte kuul color system crema coloracion permanente canas tubo';
    $P[] = ['Tinte Kuul Color System — Naturales', 'Kuul', 'tintes-caja', $kuulDesc, "$kuulTags naturales negro castaño rubio",
        $kuul([['1', 'Negro', '#222936'], ['1A', 'Negro azulado', '#1d3036'], ['1V', 'Negro violeta', '#21253b'], ['2', 'Castaño extra oscuro', '#282627'],
            ['3', 'Castaño oscuro', '#312f30'], ['4', 'Castaño', '#393736'], ['5', 'Castaño claro', '#372f2f'], ['6', 'Rubio oscuro', '#3b3730'],
            ['7', 'Rubio', '#514839'], ['8', 'Rubio claro', '#63533f'], ['9', 'Rubio clarísimo', '#a68760'], ['10', 'Rubio extra claro', '#cdaf86']]), 1, 'kuul-naturales'];
    $P[] = ['Tinte Kuul Color System — Cenizos, irisados y nacarados', 'Kuul', 'tintes-caja', $kuulDesc, "$kuulTags cenizo irisado nacarado matizado anticobrizo rubio frio",
        $kuul([['5.1', 'Castaño claro cenizo', '#332d2e'], ['6.1', 'Rubio oscuro cenizo', '#3a332c'], ['7.1', 'Rubio cenizo', '#463d32'], ['8.1', 'Rubio claro cenizo', '#504a41'],
            ['9.1', 'Rubio clarísimo cenizo', '#7a756f'], ['10.1', 'Rubio extra claro cenizo', '#afaba3'], ['6.11', 'Rubio oscuro cenizo intenso', '#343332'],
            ['7.11', 'Rubio cenizo intenso', '#443c34'], ['8.11', 'Rubio claro cenizo intenso', '#554335'], ['9.11', 'Rubio clarísimo cenizo intenso', '#796a55'],
            ['10.11', 'Rubio extra claro cenizo intenso', '#a5a197'], ['6.12', 'Rubio oscuro cenizo nacarado', '#3a332c'], ['7.12', 'Rubio cenizo nacarado', '#463d32'],
            ['9.12', 'Rubio clarísimo cenizo nacarado', '#a68760'], ['6.22', 'Rubio oscuro nacarado intenso', '#625545'], ['7.22', 'Rubio nacarado intenso', '#685647'],
            ['8.22', 'Rubio claro nacarado intenso', '#8b714d'], ['9.22', 'Rubio clarísimo nacarado intenso', '#8b7353'], ['10.22', 'Rubio extra claro nacarado intenso', '#bf9765'],
            ['6.23', 'Rubio oscuro nacarado dorado', '#4a372c']]), 0, 'kuul-cenizos'];
    $P[] = ['Tinte Kuul Color System — Cobres y rojos', 'Kuul', 'tintes-caja', $kuulDesc, "$kuulTags cobre cobrizo rojo rojizo intenso borgoña",
        $kuul([['6.34', 'Rubio oscuro dorado cobrizo', '#55352f'], ['7.44', 'Rubio cobrizo intenso', '#82321e'], ['7.64', 'Rubio rojo cobrizo', '#902e23'],
            ['4.65', 'Borgoña', '#3e2b30'], ['6.66', 'Rubio oscuro rojo intenso', '#621014'], ['7.62', 'Rubio rojo nacarado', '#641925'],
            ['7.66', 'Rubio rojo intenso', '#7c1c1e'], ['8.62', 'Rubio claro rojo nacarado', '#841f2e'], ['8.66', 'Rubio claro rojo intenso', '#8b1f20'],
            ['9.66', 'Rubio clarísimo rojo intenso', '#a82626']]), 1, 'kuul-cobres-rojos'];
    $P[] = ['Tinte Kuul Color System — Chocolates, caobas y dorados', 'Kuul', 'tintes-caja', $kuulDesc, "$kuulTags chocolate moka capuchino latte caoba dorado miel",
        $kuul([['5.31', 'Chocolate', '#3f3538'], ['7.31', 'Latte macchiato', '#5a4634'], ['5.45', 'Moka', '#4b3436'], ['7.35', 'Capuchino', '#53433a'],
            ['5.5', 'Castaño claro caoba', '#422f2f'], ['7.5', 'Rubio caoba', '#592a2a'], ['7.3', 'Rubio dorado', '#543d2e'], ['8.3', 'Rubio claro dorado', '#926633'],
            ['9.33', 'Rubio clarísimo dorado intenso', '#ce9e50'], ['10.31', 'Rubio extra claro dorado cenizo', '#b49066']]), 0, 'kuul-chocolates-dorados'];
    $P[] = ['Tinte Kuul Color System — Superaclarantes', 'Kuul', 'tintes-caja',
        "Superaclarantes Kuul para aclarar hasta 4 o 5 niveles sin decolorar. Úsalos con oxidante de 40 vol.\nEl 000 refuerza la aclaración de cualquier tono.",
        "$kuulTags superaclarante aclarar platino rubio muy claro sin decolorar",
        $kuul([['9.9', 'Rubio platino', '#c9c8c0'], ['902', 'Rubio ultra claro nacarado', '#e0d1c3'], ['000', 'Reforzador de la aclaración', '#e2e1db']]), 0, 'kuul-superaclarantes'];
    $P[] = ['Tinte fantasía Kuul Funny Colors', 'Kuul', 'tintes-caja',
        "Colores de fantasía Kuul Funny Colors, intensos y vibrantes. Para mejores resultados aplícalos sobre cabello decolorado.\nEl blanco sirve como diluyente para crear tonos pastel.",
        'tinte fantasia kuul funny colors azul verde rosa violeta morado neon pastel colores vivos',
        $kuul([['Azul', '', '#1f3880'], ['Azul océano', '', '#14263a'], ['Azul jeans', '', '#204b6a'], ['Azul celeste', '', '#2994a1'], ['Azul neón', '', '#1385be'],
            ['Verde', '', '#117452'], ['Verde esmeralda', '', '#11aaa3'], ['Verde pera', '', '#9fad5c'], ['Verde neón', '', '#5eab41'],
            ['Violeta', '', '#4f2d72'], ['Violeta neón', '', '#7855a6'], ['Magenta', '', '#bd3785'], ['Bugambilia', '', '#8d3881'], ['Rosa', '', '#c65ca7'],
            ['Rosa neón', '', '#e047a1'], ['Coral', '', '#fc9887'], ['Rojo', '', '#d02d27'], ['Rojo rosado', '', '#961c4a'], ['Rojo violeta', '', '#531837'],
            ['Naranja', '', '#d25723'], ['Amarillo neón', '', '#efd13b'], ['Plata', '', '#afb3a6'], ['Blanco titanio', '', '#a6abb5'], ['Blanco (diluyente)', '', '#e2e1db']], 22.90, 4), 1, 'kuul-funny-colors'];
    // Funny Colors no tiene código: la etiqueta queda solo con el nombre
    $last = count($P) - 1;
    $P[$last][5] = array_map(fn($v) => [rtrim($v[0]), $v[1], $v[2], $v[3]], $P[$last][5]);

    // ---------------- PLACENTA LIFE · BE NATURAL ----------------
    $pl = fn(string $name, string $cat, string $desc, string $tags, array $variants, string $img, int $featured = 0) =>
        ["$name — Placenta Life", 'Placenta Life', $cat, $desc, "placenta life be natural $tags", $variants, $featured, $img];
    $P[] = $pl('Be Natural Gold Manzanilla', 'cuidado-capilar',
        'Línea con extracto natural de manzanilla que aclara suavemente e ilumina el cabello rubio y castaño claro. Sin sal ni sulfatos.',
        'manzanilla aclarante rubio iluminar shampoo acondicionador locion sin sal',
        [['Shampoo aclarante 350 ml', 18.90, 6, null], ['Acondicionador 350 ml', 18.90, 6, null], ['Loción intensiva', 22.90, 4, null]], 'placenta-gold-manzanilla', 1);
    $P[] = $pl('Be Natural Curly Monoi', 'cuidado-capilar',
        'Línea para rizos con aceite de monoi: define, hidrata y controla el frizz.',
        'rizos rizado ondas curly monoi definir frizz crema de peinar activador',
        [['Shampoo', 18.90, 6, null], ['Mascarilla', 24.90, 4, null], ['Crema para peinar (styling cream)', 22.90, 4, null], ['Activador de rizos', 22.90, 4, null]], 'placenta-curly-monoi');
    $P[] = $pl('Be Natural Virgin Coconut', 'cuidado-capilar',
        'Línea con aceite de coco virgen que nutre e hidrata en profundidad el cabello seco.',
        'coco aceite de coco hidratacion cabello seco mascarilla acondicionador',
        [['Acondicionador', 18.90, 6, null], ['Mascarilla intensiva', 24.90, 4, null]], 'placenta-virgin-coconut');
    $P[] = $pl('Be Natural Lisso Keratina', 'cuidado-capilar',
        'Línea con keratina para cabello lacio y controlado. El termo protector alisante cuida el cabello del calor de la plancha y la secadora.',
        'keratina lacio alisado liso termo protector plancha frizz',
        [['Acondicionador', 18.90, 6, null], ['Acondicionador 1 litro', 42.90, 3, null], ['Termo protector alisante', 22.90, 4, null]], 'placenta-lisso-keratina');
    $P[] = $pl('Be Natural Blue Berry protector de color', 'cuidado-capilar',
        'Línea con arándano para cabello teñido: protege y prolonga el color. El desenredante facilita el peinado.',
        'arandano blueberry cabello teñido protector de color shampoo desenredante',
        [['Shampoo', 24.90, 5, null], ['Acondicionador', 24.90, 5, null], ['Desenredante protector de color', 22.90, 4, null]], 'placenta-blue-berry');
    $P[] = $pl('Be Natural Hydra Macadamia', 'cuidado-capilar',
        'Línea con aceite de macadamia que hidrata y controla el frizz.',
        'macadamia hidratacion frizz mascarilla desenredante',
        [['Mascarilla', 24.90, 4, null], ['Desenredante anti-frizz', 22.90, 4, null]], 'placenta-hydra-macadamia');
    $P[] = $pl('Be Natural Nutri Quinua', 'cuidado-capilar',
        'Línea con quinua que nutre y fortalece el cabello débil.',
        'quinua nutricion fortalecer shampoo acondicionador',
        [['Shampoo', 18.90, 6, null], ['Acondicionador', 18.90, 6, null]], 'placenta-nutri-quinua');
    $P[] = $pl('Vital Placenta', 'cuidado-capilar',
        'Línea con placenta vegetal que revitaliza y da fuerza al cabello.',
        'placenta vegetal revitalizar fuerza shampoo acondicionador caida',
        [['Shampoo', 18.90, 6, null], ['Acondicionador', 18.90, 6, null]], 'placenta-vital');
    $P[] = $pl('Be Natural Repair Argán shampoo', 'cuidado-capilar',
        'Shampoo con aceite de argán que repara el cabello maltratado.',
        'argan reparacion cabello maltratado dañado shampoo',
        [['Shampoo', 18.90, 6, null]], 'placenta-repair-argan');
    $P[] = $pl('Be Natural Fresh Menta acondicionador', 'cuidado-capilar',
        'Acondicionador refrescante con menta para cabello graso.',
        'menta fresco cabello graso acondicionador',
        [['Acondicionador', 18.90, 6, null]], 'placenta-fresh-menta');
    $P[] = $pl('Shock Hidro Nutritivo', 'ampollas',
        'Tratamiento de choque en ampollas que hidrata y nutre el cabello en una sola aplicación.',
        'ampolla shock hidratacion nutricion tratamiento',
        [['Caja de ampollas', 19.90, 6, null]], 'placenta-shock-hidro');

    // ---------------- HANNA CABALL (precios Kymsalons) ----------------
    $P[] = ['Hanna Caball Keratina & Biotina control caída', 'Hanna', 'cuidado-capilar',
        'Línea con keratina y biotina para cabello frágil y desvitalizado: fortaleza, nutrición y brillo intenso. Control de caída, sin sal ni parabenos.',
        'hanna caball keratina biotina caida anticaida fortalecer shampoo acondicionador sin sal',
        [['Shampoo 400 ml', 52.80, 4, null], ['Acondicionador 400 ml', 52.80, 4, null], ['Shampoo 1000 ml', 95.04, 2, null], ['Acondicionador 1000 ml', 95.04, 2, null]],
        1, 'hanna-keratina-biotina'];
    $P[] = ['Hanna Caball Mascarilla intensiva Keratina & Biotina 400 g', 'Hanna', 'tratamientos',
        'Mascarilla intensiva con keratina y biotina, control de caída. Sin sal ni parabenos.',
        'hanna caball mascarilla keratina biotina caida tratamiento',
        [['400 g', 57.60, 3, null]], 0, 'hanna-mascarilla-keratina'];
    $P[] = ['Hanna Caball Ampolla intensiva Keratina & Biotina x 12', 'Hanna', 'ampollas',
        'Caja de 12 ampollas de 13 ml con keratina y biotina para controlar la caída del cabello.',
        'hanna caball ampolla ampollas keratina biotina caida tratamiento',
        [['Caja x 12 (13 ml c/u)', 91.20, 2, null]], 0, 'hanna-ampolla-intensiva'];
    $P[] = ['Hanna Caball Keratina bifásico 95 ml', 'Hanna', 'tratamientos',
        'Reparador instantáneo bifásico con keratina: desenreda, da brillo y protege.',
        'hanna caball bifasico keratina reparador desenredante brillo',
        [['95 ml', 41.28, 3, null]], 0, 'hanna-bifasico'];
    $P[] = ['Hanna Caball Gel máxima fijación Keratina & Biotina 150 ml', 'Hanna', 'ceras-geles',
        'Gel de máxima fijación con keratina y biotina que fortalece mientras peina.',
        'hanna caball gel fijacion keratina biotina peinado',
        [['150 ml', 33.60, 4, null]], 0, 'hanna-gel-biotina'];
    $P[] = ['Hanna Caball Tónico capilar intensivo 50 ml', 'Hanna', 'tratamientos',
        'Loción tónica con keratina y biotina para controlar la caída del cabello.',
        'hanna caball tonico locion caida anticaida keratina biotina',
        [['50 ml', 48.00, 3, null]], 0, 'hanna-tonico-keratina'];
    $P[] = ['Hanna Caball Men control anticaída', 'Hanna', 'cuidado-capilar',
        'Línea para hombre con biotina y procapil contra la caída del cabello débil y frágil.',
        'hanna caball men hombre caida anticaida biotina procapil shampoo locion',
        [['Shampoo 400 ml', 52.80, 3, null], ['Loción 50 ml', 48.00, 3, null]], 0, 'hanna-men-anticaida'];
    $P[] = ['Hanna Caball Shampoo control caspa y grasa 400 ml', 'Hanna', 'cuidado-capilar',
        'Shampoo para cabello con caspa y grasa que también ayuda a controlar la caída.',
        'hanna caball caspa grasa cabello graso shampoo',
        [['400 ml', 52.80, 3, null]], 0, 'hanna-control-caspa'];

    // ---------------- LANOSTERIN (precios Aruma) ----------------
    $P[] = ['Lanosterin Keratin Essence', 'Lanosterin', 'cuidado-capilar',
        'Línea con keratina que repara, suaviza y da brillo al cabello.',
        'lanosterin keratin essence keratina shampoo acondicionador mascarilla brillo',
        [['Shampoo 250 ml', 14.90, 8, null], ['Shampoo 1000 ml', 39.90, 4, null], ['Acondicionador 250 ml', 16.90, 8, null],
         ['Acondicionador 1000 ml', 44.90, 4, null], ['Mascarilla 300 g', 24.90, 6, null]], 1, 'lanosterin-keratin-essence'];
    $P[] = ['Lanosterin Nutrilan', 'Lanosterin', 'cuidado-capilar',
        'Línea nutritiva con lanolina y keratina para cabello seco y maltratado.',
        'lanosterin nutrilan nutricion lanolina keratina cabello seco shampoo acondicionador',
        [['Shampoo con keratina 250 ml', 22.90, 6, null], ['Shampoo 1000 ml', 58.90, 3, null], ['Acondicionador 250 ml', 24.90, 6, null], ['Acondicionador 1000 ml', 59.90, 3, null]],
        0, 'lanosterin-nutrilan'];
    $P[] = ['Mascarilla Lanosterin 500 g', 'Lanosterin', 'tratamientos',
        'Mascarilla capilar de 500 g: hidratante para cabello seco o reparadora para cabello dañado.',
        'lanosterin mascarilla hidratante reparador tratamiento',
        [['Hidratante', 39.90, 4, null], ['Reparador', 39.90, 4, null]], 0, 'lanosterin-mascarilla-500'];
    $P[] = ['Mascarilla Lanosterin Keratin 300 g', 'Lanosterin', 'tratamientos',
        'Mascarilla con keratina hidrolizada: Deep Care para reparar en profundidad o Color Care para proteger el color.',
        'lanosterin mascarilla keratin deep care color care keratina cabello teñido',
        [['Deep Care', 33.90, 4, null], ['Color Care', 33.90, 4, null]], 0, 'lanosterin-mascarilla-keratin-care'];
    $P[] = ['Lanosterin Curly Hair para rizos', 'Lanosterin', 'cuidado-capilar',
        'Línea para rizos: limpia, hidrata, define y da volumen sin frizz.',
        'lanosterin rizos rizado curly hair activador mousse definir ondas',
        [['Shampoo 300 ml', 33.90, 4, null], ['Mascarilla multibeneficios 300 g', 39.90, 4, null], ['Mousse foam 180 ml', 29.90, 4, null], ['Activador de rizos Wave 250 ml', 19.90, 6, null]],
        0, 'lanosterin-curly-hair'];
    $P[] = ['Ampollas capilares Lanosterin (caja x 4)', 'Lanosterin', 'ampollas',
        'Caja de 4 ampollas de tratamiento intensivo. Elige la que necesita tu cabello.',
        'lanosterin ampolla ampollas tratamiento caida placenta semilla de lino medula reestructurante',
        [['SDL loción reestructurante', 18.90, 6, null], ['Loción a la médula', 18.90, 6, null], ['Semilla de lino aceite esencial', 18.90, 6, null],
         ['Placenta Vita', 18.90, 6, null], ['Shock anticaída', 22.90, 6, null]], 0, 'lanosterin-ampollas'];
    $P[] = ['Shampoo Lanosterin Profesional Silver 250 ml', 'Lanosterin', 'shampoo-matizador',
        'Shampoo matizador violeta que neutraliza los tonos amarillos en cabello rubio, canoso o decolorado.',
        'lanosterin silver matizador violeta morado amarillo rubio canas platinado',
        [['250 ml', 29.90, 5, null]], 0, 'lanosterin-silver'];
    $P[] = ['Protector térmico Lanosterin Thermic', 'Lanosterin', 'tratamientos',
        'Protege el cabello del calor de la plancha y la secadora.',
        'lanosterin thermic protector termico calor plancha secadora',
        [['110 ml', 9.90, 6, null], ['250 ml', 19.90, 4, null]], 0, 'lanosterin-thermic'];
    $P[] = ['Lanstyle Gotas de Cristal 30 ml — Lanosterin', 'Lanosterin', 'tratamientos',
        'Serum de silicona que sella las puntas, da brillo inmediato y controla el frizz.',
        'lanosterin gotas de cristal serum silicona brillo puntas frizz',
        [['30 ml', 19.90, 6, null]], 0, 'lanosterin-gotas-cristal'];
    $P[] = ['Gel Lanstyle Forte 180 g — Lanosterin', 'Lanosterin', 'ceras-geles',
        'Gel fijador de fijación fuerte.',
        'lanosterin gel lanstyle forte fijador fijacion peinado',
        [['180 g', 9.90, 8, null]], 0, 'lanosterin-gel-forte'];
    $P[] = ['Laca fijadora Lano Spray B 200 ml — Lanosterin', 'Lanosterin', 'ceras-geles',
        'Laca en spray de fijación duradera.',
        'lanosterin laca spray fijador lano spray',
        [['200 ml', 26.90, 4, null]], 0, 'lanosterin-laca'];
    $P[] = ['Botx mini kit capilar — Lanosterin', 'Lanosterin', 'tratamientos',
        'Kit de tratamiento capilar efecto botox: shampoo 70 ml, acondicionador 70 ml y tratamiento 50 g.',
        'lanosterin botox botx kit tratamiento reparacion',
        [['Kit', 39.90, 4, null]], 0, 'lanosterin-botox-kit'];

    return $P;
}

/**
 * Tercera tanda (noviembre 2026): fibra Kanekalon para trenzas y rastas, y las cartas de colores
 * oficiales de Palette Intensive Color Creme (micolorpalette.com) y Alfaparf Evolution of the Color.
 * Precios referenciales; la fibra a S/ 20 como pidió la tienda.
 */
function catalogo_extra_3(): array
{
    $P = [];

    // ---------------- FIBRA KANEKALON ----------------
    $P[] = ['Fibra Kanekalon para trenzas y rastas 120 cm', null, 'extensiones',
        "Fibra sintética Kanekalon encrespada de 120 cm, ligera y con mucho volumen, para box braids, trenzas afro y rastas.\nSe trenza junto al cabello natural desde la raíz. Combina colores naturales y fantasía para degradados o destellos.\nPrecio por paquete. Para una cabeza completa se suelen usar de 4 a 6 paquetes: pregúntanos por WhatsApp.",
        'kanekalon fibra sintetica trenzas rastas box braids trenzas africanas afro extensiones colores fantasia',
        array_map(fn($c) => [$c[0], 20.00, 10, $c[1]], [
            ['1 Negro', '#2d3138'], ['1B Negro natural', '#2a292e'], ['2 Castaño oscuro', '#3c373c'], ['4 Castaño', '#423c3b'],
            ['6 Castaño claro', '#49332d'], ['8 Castaño medio', '#6a534a'], ['12 Rubio oscuro', '#93856d'], ['27 Rubio miel', '#9c7b4f'],
            ['30 Cobre', '#b07d5d'], ['33 Caoba', '#643a27'], ['613 Rubio platino', '#eadfb1'], ['Burdeos', '#89404e'],
            ['Rojo', '#e42d39'], ['Naranja', '#fa9a1c'], ['Amarillo', '#f6d157'], ['Rosa', '#ff6db5'],
            ['Morado', '#5d3d73'], ['Verde', '#50941a'], ['Azul', '#3d45c3']]),
        1, 'kanekalon-colores'];

    // ---------------- PALETTE INTENSIVE COLOR CREME ----------------
    // [código, nombre, color de la muestra oficial]
    $pal = fn(array $t) => array_map(fn($x) => ["{$x[0]} {$x[1]}", 18.90, 6, $x[2]], $t);
    $palDesc = "Tinte en crema Palette Intensive Color Creme de Schwarzkopf: color intenso hasta por 10 semanas y 100 % de cobertura de canas, con cuidado multi-protección.\nCaja lista para usar (crema colorante, revelador y tratamiento).";
    $palTags = 'tinte palette schwarzkopf intensive color creme caja kit coloracion permanente canas';
    $palImgs = fn(array $t) => array_map(fn($x) => 'palette-' . $x[0], $t);
    $rubios = [['6-0', 'Rubio oscuro', '#493b30'], ['6-65', 'Rubio vainilla', '#5c4737'], ['7-0', 'Rubio medio', '#624e3a'], ['7-1', 'Rubio medio cenizo', '#534637'],
        ['7-55', 'Rubio miel', '#624b38'], ['8-0', 'Rubio claro', '#947d59'], ['8-1', 'Rubio claro cenizo', '#7b6a58'], ['9-1', 'Rubio extra claro cenizo', '#ad9f86'],
        ['9-65', 'Diosa rubio de oro', '#a28155'], ['10-1', 'Rubio plata cenizo', '#baad9d']];
    $rojos = [['6-888', 'Rubí seductor', '#481c1b'], ['4-88', 'Castaño rojizo', '#412c2a'], ['4-89', 'Borgoña intenso', '#2e1c1d'], ['4-90', 'Violeta rojizo', '#301924'],
        ['6-78', 'Rubio cobrizo oscuro', '#5f2e25'], ['6-88', 'Rubí', '#381713'], ['6-99', 'Violeta profundo', '#2e1924'], ['7-764', 'Rubio chocolate cobrizo', '#53230b'],
        ['9-7', 'Rubio cobrizo', '#99502f']];
    $castanos = [['5-68', 'Chocolate', '#482d1f'], ['3-0', 'Castaño oscuro', '#272018'], ['4-0', 'Castaño medio', '#322821'], ['5-0', 'Castaño claro', '#41362d'],
        ['5-1', 'Castaño claro cenizo', '#605037'], ['5-60', 'Chocolate mocca', '#2e1c0e'], ['5-63', 'Castaño avellana', '#403528'], ['5-80', 'Chocolate cereza', '#2f1d10'],
        ['6-460', 'Chocolate avellana', '#231810'], ['6-60', 'Chocolate deluxe', '#5d452a'], ['6-68', 'Chocolate claro', '#44291b']];
    $negros = [['1-1', 'Negro azul', '#1c1d20'], ['1-0', 'Negro', '#1e1c16']];
    foreach ([['Rubios', $rubios, 'rubio miel cenizo plata vainilla', 1], ['Rojos y violetas', $rojos, 'rojo rubi borgoña violeta cobrizo', 1],
              ['Castaños y chocolates', $castanos, 'castaño chocolate avellana mocca', 0], ['Negros', $negros, 'negro negro azulado', 0]] as [$fam, $t, $tg, $ft]) {
        $P[] = ["Tinte Palette Intensive Color Creme — $fam", 'Palette', 'tintes-caja', $palDesc, "$palTags $tg", $pal($t), $ft, $palImgs($t)];
    }

    // ---------------- ALFAPARF EVOLUTION OF THE COLOR ----------------
    $alf = fn(array $codes, array $names = []) => array_map(fn($c) => [$names[$c] ?? tone_label($c), 45.90, 3, null], $codes);
    $alfDesc = "Tinte profesional Alfaparf Milano Evolution of the Color (tubo 60 ml): fórmula con ácido hialurónico, sin PPD y con bajo amoníaco. Cobertura excepcional de canas y color de larga duración.\nMezcla 1:1.5 con Oxid'o Alfaparf. Pregúntanos el oxidante ideal para tu tono.";
    $alfTags = 'tinte alfaparf evolution of the color profesional tubo coloracion permanente canas sin ppd peluqueria';
    $alfImgs = ['alfaparf-tubo', 'alfaparf-carta'];
    $P[] = ['Tinte Alfaparf Evolution of the Color — Naturales', 'Alfaparf Milano', 'tintes-caja', $alfDesc, "$alfTags naturales naturales intensos bahia",
        $alf(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '4NB', '5NB', '6NB', '7NB', '8NB', '9NB', '10NB', '4NI', '5NI', '6NI', '7NI', '8NI', '9NI'],
            ['4NB' => '4NB Castaño natural bahía', '5NB' => '5NB Castaño claro natural bahía', '6NB' => '6NB Rubio oscuro natural bahía', '7NB' => '7NB Rubio natural bahía',
             '8NB' => '8NB Rubio claro natural bahía', '9NB' => '9NB Rubio muy claro natural bahía', '10NB' => '10NB Rubio extra claro natural bahía',
             '4NI' => '4NI Castaño natural intenso', '5NI' => '5NI Castaño claro natural intenso', '6NI' => '6NI Rubio oscuro natural intenso',
             '7NI' => '7NI Rubio natural intenso', '8NI' => '8NI Rubio claro natural intenso', '9NI' => '9NI Rubio muy claro natural intenso']), 1, $alfImgs];
    $P[] = ['Tinte Alfaparf Evolution of the Color — Fríos: cenizos, irisados, nácar, arena y beige', 'Alfaparf Milano', 'tintes-caja', $alfDesc, "$alfTags ceniza cenizo irise irisado nacar mate arena beige iced coffee frio",
        $alf(['6.01', '7.01', '8.01', '1.11', '5.1', '6.1', '7.1', '8.1', '9.1', '10.1', '9.2', '7.21', '8.21', '9.21', '10.21', '5.22', '8.221', '6.23', '8.23', '6.7',
              '7.13', '8.13', '9.13', '7.31', '8.31', '9.31', '10.31'], ['6.7' => '6.7 Rubio oscuro mate', '8.221' => '8.221 Rubio claro Crystal Rose']), 0, $alfImgs];
    $P[] = ['Tinte Alfaparf Evolution of the Color — Marrones: glacé, chocolate, tropicales y suedes', 'Alfaparf Milano', 'tintes-caja', $alfDesc, "$alfTags marron glace chocolate tropical suede castaño",
        $alf(['4.32', '5.32', '6.32', '7.32', '8.32', '5.35', '6.35', '7.35', '5.53', '6.53', '7.53', '5.75', '6.15', '7.51', '8.05', '9.45', '10.05', '5.542', '7.24'],
            ['5.542' => '5.542 Castaño claro Crystal Brown', '7.24' => '7.24 Rubio medio Crystal Brown']), 0, $alfImgs];
    $P[] = ['Tinte Alfaparf Evolution of the Color — Dorados, cobres, caobas y rojos', 'Alfaparf Milano', 'tintes-caja', $alfDesc, "$alfTags dorado cobre cobrizo bronce caoba rojo pure reds cover reds",
        $alf(['5.3', '6.3', '7.3', '8.3', '9.3', '10.3', '10.342', '6.4', '7.4', '7.43', '8.34', '8.4', '9.04', '9.423', '6.45', '7.45', '4.52', '5.5', '6.5', '6.562',
              '5.62', '7.62', '4.65', '8.65', '4.66I', '5.66I', '6.66I', '7.66I', '8.66I', '5.6', '6.6', '7.6'],
            ['4.66I' => '4.66I Castaño Pure Red', '5.66I' => '5.66I Castaño claro Pure Red', '6.66I' => '6.66I Rubio oscuro Pure Red',
             '7.66I' => '7.66I Rubio Pure Red', '8.66I' => '8.66I Rubio claro Pure Red']), 1, $alfImgs];
    $P[] = ['Tinte Alfaparf Evolution of the Color — Superaclarantes, harmonizer y correctores', 'Alfaparf Milano', 'tintes-caja', $alfDesc . "\nLos superaclarantes Platinum aclaran hasta 5 tonos.", "$alfTags superaclarante platinum harmonizer corrector booster grafito",
        $alf(['11.00', '11.10', '11.11', '11.13', '11.02', '11.20', '11.21', '11.32', 'OSP', '01', '013', '02', '03', '410', '7000', '2000', 'RB', '000SSS'],
            ['OSP' => 'OSP Super reforzador de aclaración', '01' => '01 Harmonizer ceniza', '013' => '013 Harmonizer arena', '02' => '02 Harmonizer irisado',
             '03' => '03 Harmonizer dorado', '410' => '410 Grafito (atenúa rojos y naranjas)', '7000' => '7000 Azul (corrector)', '2000' => '2000 Violeta (corrector)',
             'RB' => 'RB Red Booster', '000SSS' => '000SSS Reforzador de aclaración']), 0, $alfImgs];

    return $P;
}

/**
 * Cuarta tanda (noviembre 2026): uñas Cherimoya (catálogo profesional; precios referenciales),
 * desodorantes Axe, Dove, Rexona y Old Spice, y geles Moco de Gorila y Ego (precios de Plaza Vea Perú).
 */
function catalogo_extra_4(): array
{
    $P = [];
    $v = fn(array $list, int $stock = 6) => array_map(fn($x) => [$x[0], $x[1], $x[2] ?? $stock, $x[3] ?? null], $list);

    // ---------------- DESODORANTES ----------------
    $P[] = ['Desodorante Axe aerosol 150 ml', 'Axe', 'desodorantes',
        "Deocolonia en aerosol para hombre con protección de 48 horas y fragancias intensas. Elige tu aroma.",
        'desodorante axe aerosol spray hombre deocolonia antitranspirante apollo musk dark temptation ice chill',
        $v([['Apollo', 15.50], ['Musk', 15.50], ['Dark Temptation', 15.50], ['Ice Chill (efecto frío)', 15.50], ['Aqua Citrus', 18.90], ['Cherry Spritz', 18.90], ['Peach Infusion', 18.90]], 8),
        1, ['axe-dark-temptation', 'axe-apollo', 'axe-musk', 'axe-ice-chill', 'axe-aqua-citrus', 'axe-cherry-spritz', 'axe-peach-infusion']];
    $P[] = ['Desodorante Dove mujer', 'Dove', 'desodorantes',
        "Antitranspirante Dove con 1/4 de crema humectante que cuida la piel de las axilas. Protección de 48 a 72 horas.",
        'desodorante dove mujer aerosol roll on barra antitranspirante tono uniforme aclarante clinical',
        $v([['Original — aerosol 150 ml', 15.90], ['Original — roll on 50 ml', 14.90], ['Clinical Tono Uniforme — aerosol 150 ml', 19.90],
            ['Caléndula — barra 45 g', 14.90], ['Raspberry — aerosol', 15.90]]),
        0, ['dove-original-aerosol', 'dove-original-rollon', 'dove-clinical-tono', 'dove-calendula-barra', 'dove-raspberry']];
    $P[] = ['Desodorante Dove Men+Care', 'Dove', 'desodorantes',
        "Antitranspirante para hombre Dove Men+Care: protección de 72 horas que cuida la piel.",
        'desodorante dove men care hombre aerosol barra invisible dry proteccion total',
        $v([['Invisible Dry — aerosol 150 ml', 17.90], ['Protección Total — aerosol 150 ml', 17.90], ['Invisible Dry — barra 50 g', 17.50]]),
        0, ['dove-men-invisible', 'dove-men-proteccion', 'dove-men-barra']];
    $P[] = ['Desodorante Rexona mujer aerosol', 'Rexona', 'desodorantes',
        "Antitranspirante Rexona MotionSense: más movimiento, más protección (hasta 72 horas).",
        'desodorante rexona mujer aerosol antitranspirante powder dry active emotion clinical invisible aclarado',
        $v([['Active Emotion 150 ml', 13.90], ['Powder Dry 150 ml', 13.90], ['Antibacterial Invisible 150 ml', 13.90], ['Aclarado Perfecto 150 ml', 13.90],
            ['Bamboo 175 ml', 13.90], ['Clinical Refresh 150 ml', 17.90]]),
        0, ['rexona-active-emotion', 'rexona-powder-dry', 'rexona-antibacterial-invisible', 'rexona-aclarado', 'rexona-bamboo', 'rexona-clinical-refresh']];
    $P[] = ['Desodorante Rexona Men aerosol 150 ml', 'Rexona', 'desodorantes',
        "Antitranspirante Rexona Men MotionSense con protección de 72 horas.",
        'desodorante rexona men hombre aerosol v8 xtra cool antibacterial forest',
        $v([['V8', 15.50], ['Xtra Cool', 15.50], ['Antibacterial', 15.50], ['Forest', 15.50]], 8),
        0, ['rexona-men-v8', 'rexona-men-xtra-cool', 'rexona-men-antibacterial', 'rexona-men-forest']];
    $P[] = ['Desodorante Old Spice', 'Old Spice', 'desodorantes',
        "Desodorante y antitranspirante para hombre Old Spice, con sus aromas clásicos.",
        'desodorante old spice hombre aerosol spray barra original fresh wolfthorn bearglove pure sport',
        $v([['Original — aerosol 150 ml', 16.50], ['Fresh — aerosol 150 ml', 16.50], ['Wolfthorn — aerosol 150 ml', 16.50], ['Bearglove — aerosol 150 ml', 16.50],
            ['Pure Sport — barra 50 g', 16.50], ['Fresh — barra 50 g', 16.50], ['Dragonblast — barra 50 g', 16.50]]),
        1, ['oldspice-original', 'oldspice-fresh', 'oldspice-wolfthorn', 'oldspice-bearglove', 'oldspice-pure-sport-barra', 'oldspice-fresh-barra', 'oldspice-dragonblast-barra']];

    // ---------------- GELES ----------------
    $P[] = ['Gel Moco de Gorila', 'Moco de Gorila', 'ceras-geles',
        "Gel fijador de fijación extrema que mantiene el peinado todo el día. Punk (máxima fijación) y Gamer.",
        'gel moco de gorila fijador fijacion extrema peinado punk gamer',
        $v([['Punk 80 g', 11.00], ['Punk 270 g', 21.40], ['Punk 340 g', 24.80], ['Gamer 340 g', 19.80]], 8), 0, ['moco-punk', 'moco-gamer']];
    $P[] = ['Gel Ego para hombre', 'Ego', 'ceras-geles',
        "Gel para hombre Ego en varias fórmulas: Extreme Max (fijación máxima), Attraction, Vitamin+Care y Ultra Intense.",
        'gel ego hombre fijador extreme max attraction vitamin ultra intense peinado',
        $v([['Extreme Max 100 g', 5.90], ['Extreme Max 500 g', 18.50], ['Attraction 110 ml', 5.90], ['Attraction 500 ml', 18.50],
            ['Vitamin+Care 110 ml', 5.90], ['Vitamin+Care 240 ml', 11.90], ['Vitamin+Care 500 ml', 18.50], ['Ultra Intense 110 ml', 5.90]], 8),
        0, ['ego-extreme-max', 'ego-attraction', 'ego-vitamin', 'ego-ultra-intense']];

    // ---------------- UÑAS CHERIMOYA (precios referenciales) ----------------
    $ch = fn(string $name, string $desc, string $tags, array $variants, $img, int $featured = 0) =>
        ["$name — Cherimoya", 'Cherimoya', 'unas', $desc . "\nProducto vegano, no testeado en animales.", "cherimoya uñas manicura nail $tags", $v($variants, 4), $featured, $img];
    $P[] = $ch('Preparadores para uñas 15 ml', 'Preparadores esenciales: pH Balancing deshidrata y equilibra la uña, Ultrabond y Primer sin ácido mejoran la adherencia del esmaltado o la estructura.',
        'preparador ph balancing ultrabond primer sin acido adherencia', [['pH Balancing Agent', 22.90], ['Ultrabond (sin ácido)', 22.90], ['Primer sin ácido', 22.90]], 'cherimoya-preparadores');
    $P[] = $ch('Base y Top Coat soak off 15 ml', 'Base y selladores para esmaltado semipermanente y uñas esculpidas: brillo impecable y duradero. Top No-Cleanse sin capa pegajosa.',
        'base coat top coat matte sellador semipermanente uv led no cleanse', [['Base Coat', 24.90], ['Top Coat No-Cleanse', 24.90], ['Matte Top Coat', 24.90], ['Base Aquarelle', 24.90]], 'cherimoya-base-top');
    $P[] = $ch('Esmalte semipermanente UV/LED 15 ml', 'Más de 400 tonos. Pincel de 1000 cerdas para una aplicación fácil. Libre de tolueno y formaldehído. Escríbenos el código del color que quieres.',
        'esmalte semipermanente gel uv led colores', [['Color a elegir (consulta el catálogo)', 24.90]], 'cherimoya-esmalte-15', 1);
    $P[] = $ch('Esmalte semipermanente UV/LED 8 ml', 'Colores súper pigmentados de larga duración y brillo impactante en formato de 8 ml. Más de 500 tonos.',
        'esmalte semipermanente gel uv led 8ml colores', [['Color a elegir (consulta el catálogo)', 15.90]], 'cherimoya-esmalte-8');
    $P[] = $ch('CRMY Series', 'Línea avanzada: Velvet (top efecto terciopelo ultra matte), Reinforcement (reforzador y nivelador), Tempered Seal (top efecto vidrio templado) y Activation Repair (activador de color).',
        'crmy velvet reinforcement tempered seal activation repair top', [['Velvet', 29.90], ['Reinforcement', 29.90], ['Tempered Seal', 29.90], ['Activation Repair', 29.90]], 'cherimoya-crmy');
    $P[] = $ch('Powder Gel 50 ml', 'Polygel profesional de fácil aplicación y acabado impecable para estructuras. 24 tonos, con y sin glitter.',
        'powder gel polygel estructura construccion', [['Clear', 39.90], ['Tono a elegir (24 tonos)', 39.90]], 'cherimoya-powder-gel');
    $P[] = $ch('Solución Powder Gel (Slip Solution)', 'Líquido para moldear la perla de Powder Gel en la uña. Aroma agradable y no evapora rápido.',
        'solucion powder gel slip solution polygel', [['Frasco', 24.90]], 'cherimoya-solucion');
    $P[] = $ch('Acrylic Powder (polvo acrílico)', 'Acrílicos de alta gama con 100 % de cobertura, 12 colores incluidos tonos cover. Fácil aplicación y tiempos de activación adecuados.',
        'acrilico polvo acrilico acrylic powder cover nude pink esculpidas',
        [['Clear (cristal) 15 g', 22.90], ['Clear (cristal) 30 g', 34.90], ['White (blanco) 15 g', 22.90], ['Pink 15 g', 22.90], ['Nude Pink 15 g', 22.90],
         ['Natural 15 g', 22.90], ['Ultra Nature 15 g', 22.90], ['Nude Diamonds 15 g', 22.90], ['Cover Shine Rose 15 g', 22.90], ['Cover Soft Beige 15 g', 22.90],
         ['Cover Rose 15 g', 22.90], ['Cover Peach 15 g', 22.90], ['Beauty Tan 15 g', 22.90]], 'cherimoya-acrylic');
    $P[] = $ch('Monómero de bajo olor', 'Líquido activador de los polvos acrílicos, indispensable para diseñar la estructura.',
        'monomero liquido acrilico bajo olor', [['100 ml', 24.90], ['250 ml', 44.90], ['500 ml', 79.90], ['1 litro', 139.90]], 'cherimoya-monomero');
    $P[] = $ch('Gel de construcción UV/LED', 'Gel para construir uñas en un solo color o manicura francesa, con camuflaje para uñas mordidas o estriadas.',
        'gel construccion builder uv led esculpidas', [['Cristal 15 g', 24.90], ['Cristal 30 g', 39.90], ['Tono a elegir 15 g', 24.90], ['Tono a elegir 30 g', 39.90]], 'cherimoya-gel-construccion');
    $P[] = $ch('Pinceles profesionales', 'Pinceles de cerdas sintéticas premium para gel, acrílico y powder gel (2 en 1), y pinceles de nail art y liner para decoración.',
        'pincel pinceles gel acrilico powder gel nail art liner', [['Pincel para gel', 24.90], ['Pincel para acrílico', 34.90], ['Pincel powder gel 2 en 1', 29.90], ['Pincel nail art / liner', 14.90]], 'cherimoya-pinceles');
    $P[] = $ch('Moldes para esculpir uñas', 'Moldes adhesivos premium para esculpir en cualquier material.',
        'moldes uñas esculpir mariposa stiletto', [['Clásicos x 500', 34.90], ['Mariposa x 500', 34.90], ['Stiletto premium x 300', 29.90], ['XXL extremo', 29.90]], 'cherimoya-moldes');
    $P[] = $ch('Limas zodiacales', 'Lima 2 en 1 (Zebra 100/180 y buffer sponge) con diseño exclusivo de cada signo zodiacal.',
        'lima limas buffer pulidor zodiacal', [['Unidad', 4.90]], 'cherimoya-limas');
    $P[] = $ch('Kit inicial de acrílico', 'Todo lo indispensable para empezar en el sistema de acrílico: ideal para estudiantes y técnicas que dan sus primeros pasos.',
        'kit acrilico inicial estudiante principiante', [['Kit', 149.90]], 'cherimoya-kit-acrilico');
    $P[] = $ch('Kit inicial de gel', 'Todo lo indispensable para empezar en el sistema de gel: ideal para estudiantes y técnicas que dan sus primeros pasos.',
        'kit gel inicial estudiante principiante', [['Kit', 149.90]], 'cherimoya-kit-gel');

    return $P;
}

/**
 * Quinta tanda: carta oficial de Smoll Keratina & Aceite de Argán (sachet), 50 tonos
 * leídos de la carta de la marca, con el color de cada muestra.
 */
function catalogo_extra_5(): array
{
    $s = fn(array $t) => array_map(fn($x) => ["{$x[0]} {$x[1]}", 4.50, 12, $x[2]], $t);
    $desc = "Coloración en crema Smoll en sachet, enriquecida con keratina y aceite de argán: color intenso, brillo y cobertura de canas. Rinde una aplicación en cabello corto a medio.\nPídelo por unidad o por caja de 12. Pregúntanos el oxidante ideal para tu tono.";
    $tags = 'tinte smoll sachet keratina aceite de argan coloracion crema permanente canas';
    $P = [];
    $P[] = ['Tinte Smoll Keratina & Argán sachet — Naturales', 'Smoll', 'tintes-sachet', $desc, "$tags naturales negro castaño rubio platino intenso",
        $s([['01N', 'Negro azul', '#1f2626'], ['1', 'Negro', '#2b2a28'], ['3', 'Castaño oscuro', '#312d2c'], ['4', 'Castaño', '#453930'], ['5', 'Castaño claro', '#453327'],
            ['6', 'Rubio oscuro', '#2d2515'], ['7', 'Rubio', '#765943'], ['8', 'Rubio claro', '#8e6b50'], ['9', 'Rubio clarísimo', '#ae9364'],
            ['6.0', 'Rubio oscuro intenso', '#59493e'], ['7.0', 'Rubio intenso', '#6e5a40'], ['10.1', 'Platino', '#baae7c']]),
        1, ['smoll-naturales', 'smoll-carta-naturales', 'smoll-carta']];
    $P[] = ['Tinte Smoll Keratina & Argán sachet — Cenizos y dorados', 'Smoll', 'tintes-sachet', $desc, "$tags cenizo cenizos verdosos matizado dorado dorado menta rubio",
        $s([['5.1NC', 'Castaño claro cenizo', '#60553f'], ['6.1NC', 'Rubio oscuro cenizo', '#6b5f47'], ['7.1NC', 'Rubio cenizo', '#7a704a'], ['8.1NC', 'Rubio claro cenizo', '#a29061'],
            ['9.1NC', 'Rubio clarísimo cenizo', '#a3906b'], ['6.10', 'Rubio oscuro cenizo intenso', '#5a4f29'], ['7.10', 'Rubio cenizo intenso', '#76704b'],
            ['7.11', 'Rubio cenizo profundo', '#82805e'], ['8.11', 'Rubio claro cenizo profundo', '#747457'], ['9.11', 'Rubio clarísimo cenizo profundo', '#a09e79'],
            ['8.3TM', 'Rubio claro dorado menta', '#8c7c47'], ['5.3D', 'Castaño claro dorado', '#543821'], ['6.3D', 'Rubio oscuro dorado', '#6b5734'],
            ['7.3D', 'Rubio dorado', '#ae8240'], ['8.3D', 'Rubio claro dorado', '#a78336'], ['9.3D', 'Rubio muy claro dorado', '#bc9c46']]),
        0, ['smoll-cenizos-dorados', 'smoll-carta-cenizos', 'smoll-cenizos-verdosos', 'smoll-carta']];
    $P[] = ['Tinte Smoll Keratina & Argán sachet — Chocolates', 'Smoll', 'tintes-sachet', $desc, "$tags chocolate chocolates encantados marron",
        $s([['5.4CH', 'Chocolate', '#35211b'], ['5.68CH', 'Chocolate natural', '#482e24'], ['7.4CH', 'Chocolate mediano', '#6d4c3b'], ['8.4CH', 'Chocolate claro', '#724e3b'],
            ['5.23CH', 'Chocolate intenso', '#51322c'], ['7.23CH', 'Chocolate intenso mediano', '#603b31'], ['5.35', 'Castaño claro chocolate', '#3c2e2a'],
            ['6.73', 'Chocolate dorado', '#432c27'], ['8.73', 'Chocolate claro dorado', '#463831']]),
        1, ['smoll-chocolates', 'smoll-carta-chocolates', 'smoll-carta']];
    $P[] = ['Tinte Smoll Keratina & Argán sachet — Rojos y cobrizos', 'Smoll', 'tintes-sachet', $desc, "$tags rojo rojizo violeta granate borgoña ciruela cobre cobrizo caoba fuego",
        $s([['RG', 'Rojo granate', '#401418'], ['RP', 'Rojo pasión', '#4c2524'], ['CARIBE', 'Caribe', '#772d2c'], ['4RF', 'Castaño mediano borgoña', '#2d1c25'],
            ['4.88', 'Castaño rojizo', '#53141d'], ['6.62RR', 'Rubio rojo intenso', '#863037'], ['5.20', 'Ciruela', '#342336'], ['0006', 'Rojo fuego ardiente', '#3c1318'],
            ['7.006', 'Rubio rojo fuego ardiente', '#6c2725'], ['5.7', 'Caoba cobrizo', '#4a2d1f'], ['6.37CB', 'Rubio oscuro dorado cobre', '#4c281f'],
            ['0064', 'Rojo cobre fuego ardiente', '#6d1519'], ['6.6CB', 'Rubio caoba rojizo', '#652329']]),
        0, ['smoll-rojos', 'smoll-carta-rojos', 'smoll-cobrizos', 'smoll-carta']];
    return $P;
}

/** Tras la quinta tanda: oculta el sachet genérico de ejemplo de Smoll con amoníaco (si nunca se vendió). */
function catalogo_extra_5_after(PDO $pdo): void
{
    $pdo->prepare('UPDATE products SET active = 0, updated_at = ? WHERE slug = ?
                   AND NOT EXISTS (SELECT 1 FROM order_items o WHERE o.product_id = products.id)')->execute([now(), 'tinte-en-sachet-smoll-con-amoniaco']);
}

/** Tras la tercera tanda: oculta los tintes genéricos de ejemplo de Palette y Evolution (si nunca se vendieron). */
function catalogo_extra_3_after(PDO $pdo): void
{
    foreach (['tinte-en-caja-palette-con-amoniaco', 'tinte-en-caja-evolution-con-amoniaco'] as $slug) {
        $pdo->prepare('UPDATE products SET active = 0, updated_at = ? WHERE slug = ?
                       AND NOT EXISTS (SELECT 1 FROM order_items o WHERE o.product_id = products.id)')->execute([now(), $slug]);
    }
}

/** Inserta cada tanda del catálogo ampliado si aún no está (rápido: revisa un slug centinela por tanda). */
function import_catalogo_extra(PDO $pdo): void
{
    $batches = [
        'aceite-para-maquina-bandido-200-ml' => 'catalogo_extra',
        'botx-mini-kit-capilar-lanosterin' => 'catalogo_extra_2',
        'fibra-kanekalon-para-trenzas-y-rastas-120-cm' => 'catalogo_extra_3',
        'desodorante-axe-aerosol-150-ml' => 'catalogo_extra_4',
        'tinte-smoll-keratina-argan-sachet-naturales' => 'catalogo_extra_5',
    ];
    $st = $pdo->prepare('SELECT 1 FROM products WHERE slug = ?');
    foreach ($batches as $sentinel => $fn) {
        $st->execute([$sentinel]);
        if ($st->fetchColumn()) continue;
        import_catalogo_batch($pdo, $fn());
        if (function_exists($fn . '_after')) ($fn . '_after')($pdo);
    }
}

function import_catalogo_batch(PDO $pdo, array $list): void
{

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
        'ceras-geles' => ['Ceras y geles', 'jar'], 'barberia' => ['Barbería y afeitado', 'scissors'],
        'tintes-caja' => ['Tintes en caja', 'box'], 'cuidado-capilar' => ['Shampoo y cuidado capilar', 'bottle'],
        'tratamientos' => ['Tratamientos capilares', 'drop'], 'ampollas' => ['Ampollas', 'ampoule'],
        'shampoo-matizador' => ['Shampoo matizador', 'bottle'], 'extensiones' => ['Extensiones', 'hair'],
        'desodorantes' => ['Desodorantes', 'spark'], 'unas' => ['Uñas', 'spark'], 'tintes-sachet' => ['Tintes en sachet', 'sachet']];

    $exists = $pdo->prepare('SELECT id FROM products WHERE slug = ?');
    $hasImg = $pdo->prepare('SELECT 1 FROM product_images WHERE product_id = ?');
    $insP = $pdo->prepare('INSERT INTO products (name, slug, brand_id, category_id, description, tags, featured, active, created_at, updated_at)
                           VALUES (?,?,?,?,?,?,?,1,?,?)');
    $insV = $pdo->prepare('INSERT INTO product_variants (product_id, label, sku, price, stock, sort, color) VALUES (?,?,?,?,?,?,?)');
    $insI = $pdo->prepare('INSERT INTO product_images (product_id, path, sort) VALUES (?,?,?)');

    $pdo->beginTransaction();
    try {
        foreach ($list as [$name, $brandName, $catSlug, $desc, $tags, $variants, $featured, $img]) {
            $slug = slugify($name);
            // una foto o varias (galería): solo las que existen en uploads/catalogo
            $paths = array_values(array_filter(array_map(fn($f) => "uploads/catalogo/$f.jpg", (array) $img), fn($p) => is_file(ROOT . '/' . $p)));
            $exists->execute([$slug]);
            if ($oldId = $exists->fetchColumn()) {
                // Ya lo tenías: no se toca, solo se le pone foto si no tiene ninguna
                $hasImg->execute([$oldId]);
                if (!$hasImg->fetchColumn() && $paths) $insI->execute([$oldId, $paths[0], 0]);
                continue;
            }
            [$cn, $ci] = $catNames[$catSlug];
            $insP->execute([$name, $slug, $brandName ? $brand($brandName) : null, $cat($catSlug, $cn, $ci), $desc, $tags, $featured, now(), now()]);
            $pid = (int) $pdo->lastInsertId();
            foreach ($variants as $i => [$label, $price, $stock, $color]) {
                $insV->execute([$pid, $label, strtoupper(substr($slug, 0, 10)) . '-' . ($i + 1), $price, $stock, $i, $color]);
            }
            foreach ($paths as $i => $p) $insI->execute([$pid, $p, $i]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('catalogo-extra: ' . $e->getMessage());
    }
}
