<?php
/**
 * ============================================================
 *  CONFIGURACIÓN DE QUINTO LUMIÈRE
 *  Edita solo los valores entre comillas. No borres las comas.
 * ============================================================
 */
return [

    // ---- Datos de la tienda ----
    'store_name'   => 'Quinto Lumière',
    'tagline'      => 'Una marca que ilumina desde el alma',
    'address'      => 'Km. 36 Antigua Panamericana Sur — Mercado San Pedro de Lurín, Stand 36',
    'whatsapp'     => '51959214464',             // con código de país, sin + ni espacios
    'email'        => 'contacto@quintolumiere.com',  // cámbialo por tu correo real
    'ruc'          => '10802022471',               // aparece en Términos y Libro de Reclamaciones
    'razon_social' => 'Quinto Figueroa de Huamán Lida Maritsa', // titular del RUC

    'social' => [
        'instagram' => 'https://www.instagram.com/quinto_lumiere.cosm/',
        'tiktok'    => 'https://www.tiktok.com/@quinto_lumiere.cosm/',
        'facebook'  => 'https://www.facebook.com/people/Quinto-Lumi%C3%A8re/61575147607213/',
        'x'         => 'https://x.com/QuintoLumiere',
    ],

    // ---- Dirección pública del sitio (sin / al final) ----
    // Ej: 'https://quintolumiere.com'. Déjalo vacío para detectarlo solo.
    'site_url' => 'https://quintolumiere.gt.tc',

    // ---- Base de datos ----
    // 'sqlite' funciona sin configurar nada (recomendado para empezar).
    // Para MySQL cambia driver a 'mysql' y completa los datos que te da tu hosting.
    //   Hostinger:    host 'localhost'
    //   InfinityFree: host tipo 'sql123.infinityfree.com', nombre 'if0_12345678_tienda',
    //                 usuario 'if0_12345678' y la contraseña de tu cuenta de hosting.
    'db' => [
        'driver'   => 'sqlite',
        'sqlite'   => __DIR__ . '/data/tienda.sqlite',
        'host'     => 'localhost',
        'name'     => '',
        'user'     => '',
        'password' => '',
    ],

    // ---- Mercado Pago (https://www.mercadopago.com.pe/developers/panel) ----
    // Usa primero las credenciales de PRUEBA (TEST-...). Cuando todo funcione,
    // cámbialas por las de PRODUCCIÓN (APP_USR-...).
    // Si lo dejas vacío, los pedidos se envían por WhatsApp (pago por Yape/Plin/transferencia).
    'mercadopago' => [
        'public_key'     => '',
        'access_token'   => '',
        'webhook_secret' => '',   // "Clave secreta" de Webhooks (opcional pero recomendado)
    ],

    // ---- Google Analytics 4 ----
    // ID de medición, se ve así: G-XXXXXXXXXX
    'ga_measurement_id' => '',

    // ---- Envíos ----
    'shipping' => [
        'recojo_tienda' => 0.00,   // recojo en el stand
        'lima'          => 10.00,  // delivery Lima Metropolitana / Lurín
        'provincia'     => 18.00,  // envío a provincia (Olva / Shalom)
        'gratis_desde'  => 150.00, // envío gratis desde este monto (0 = nunca)
    ],
];
