<?php
/** Revisa que el hosting, Mercado Pago y Google Analytics estén bien configurados. */
require dirname(__DIR__) . '/inc/bootstrap.php';
require dirname(__DIR__) . '/inc/mercadopago.php';
require_admin();

$checks = []; // [grupo, título, ok(true/false/null=aviso), detalle]
$add = function (string $g, string $t, ?bool $ok, string $d = '') use (&$checks) { $checks[] = [$g, $t, $ok, $d]; };

// ---- Hosting ----
$add('Hosting', 'Versión de PHP ' . PHP_VERSION, version_compare(PHP_VERSION, '8.1', '>='), 'Se necesita PHP 8.1 o superior (hPanel → Avanzado → Configuración de PHP).');
foreach (['pdo_sqlite' => 'Base de datos', 'curl' => 'Conexión con Mercado Pago', 'openssl' => 'Conexión segura', 'gd' => 'Optimizar fotos', 'mbstring' => 'Textos con tildes'] as $ext => $why) {
    $add('Hosting', "Extensión $ext ($why)", extension_loaded($ext), 'Actívala en hPanel → Avanzado → Configuración de PHP → Extensiones.');
}
$add('Hosting', 'Fotos en formato WebP', function_exists('imagewebp'), 'Sin WebP las fotos se guardan en su formato original (funciona igual).');
foreach (['data', 'uploads'] as $dir) {
    $add('Hosting', "Carpeta $dir/ con permiso de escritura", is_writable(ROOT . "/$dir"), "Pon permisos 755 a la carpeta $dir en el Administrador de archivos.");
}
$https = is_https();
$add('Hosting', 'Conexión segura (https)', $https ?: null, 'Activa el SSL de tu hosting y luego escribe https:// en site_url de config.php. Mercado Pago necesita https.');
$add('Hosting', 'Dirección del sitio en config.php', cfg('site_url') ? true : null, cfg('site_url') ?: 'Escribe tu dirección en site_url (ej. https://quintolumiere.infinityfreeapp.com).');
$add('Hosting', 'Base de datos: ' . (is_mysql() ? 'MySQL' : 'SQLite'), true, '');

// ¿El hosting puede conectarse a internet? (Mercado Pago lo necesita)
if (extension_loaded('curl')) {
    $c = curl_init('https://api.mercadopago.com/');
    curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 8]);
    curl_exec($c);
    $code = (int) curl_getinfo($c, CURLINFO_HTTP_CODE);
    $err = curl_error($c);
    curl_close($c);
    $add('Hosting', 'Conexión saliente a Mercado Pago', $code > 0, $code > 0 ? '' : 'El hosting no permite conectarse a api.mercadopago.com (' . $err . '). Sin esto no se puede cobrar con Mercado Pago aquí; usa pedidos por WhatsApp o un hosting de pago.');
}

// ---- Datos de la tienda ----
$add('Datos', 'Correo de contacto', cfg('email') !== 'contacto@quintolumiere.com' ? true : null, 'Cambia el correo de ejemplo por el tuyo en config.php.');
$add('Datos', 'RUC o DNI', cfg('ruc') ? true : null, 'Aparece en Términos y en el Libro de Reclamaciones.');

// ---- Mercado Pago ----
$token = (string) cfg('mercadopago.access_token');
$pub = (string) cfg('mercadopago.public_key');
if (!mp_enabled()) {
    $add('Mercado Pago', 'Credenciales', null, 'Sin credenciales: los pedidos se coordinan por WhatsApp. Pega tu Access Token y Public Key en config.php.');
} else {
    $mode = str_starts_with($token, 'TEST-') ? 'PRUEBA' : 'PRODUCCIÓN';
    try {
        $me = mp_request('GET', '/users/me');
        $add('Mercado Pago', "Access Token válido (modo $mode)", true, 'Cuenta: ' . ($me['nickname'] ?? '') . ' · ' . ($me['email'] ?? '') . ' · país ' . ($me['site_id'] ?? '?'));
        $add('Mercado Pago', 'Cuenta de Perú (soles)', ($me['site_id'] ?? '') === 'MPE', 'La cuenta debe ser de Mercado Pago Perú (MPE) para cobrar en soles.');
    } catch (Throwable $e) {
        $add('Mercado Pago', 'Access Token', false, $e->getMessage());
    }
    $add('Mercado Pago', 'Public Key', $pub !== '' ? true : null, 'Cópiala del mismo panel de credenciales.');
    $add('Mercado Pago', 'Clave secreta del webhook', cfg('mercadopago.webhook_secret') ? true : null, 'Recomendado: verifica que los avisos vengan realmente de Mercado Pago.');
    if ($mode === 'PRUEBA') $add('Mercado Pago', 'Modo prueba activo', null, 'Los pagos NO son reales. Cuando todo funcione, cambia a las credenciales de producción.');
}
$add('Mercado Pago', 'URL del webhook para configurar en Mercado Pago', $https ? true : null, site_url('api/mp-webhook.php'));
$add('Mercado Pago', 'Si tu hosting es gratuito', null, 'Los hostings gratis (como InfinityFree) suelen bloquear los avisos automáticos de Mercado Pago. No pasa nada: la tienda confirma el pago cuando la clienta vuelve a la web, al abrir este panel y con el botón «Verificar pagos pendientes» en Pedidos.');

// ---- Google Analytics ----
$ga = (string) cfg('ga_measurement_id');
if ($ga === '') $add('Google Analytics', 'ID de medición', null, 'Pega tu ID (G-XXXXXXXXXX) en config.php.');
else $add('Google Analytics', "ID de medición $ga", (bool) preg_match('/^G-[A-Z0-9]{6,12}$/', $ga), 'Debe tener el formato G-XXXXXXXXXX.');

page_header('Diagnóstico', ['admin' => true]);
$groups = [];
foreach ($checks as $c) $groups[$c[0]][] = $c;
?>
<div class="wrap admin">
  <div class="admin__head"><h1>Diagnóstico</h1><a class="btn btn--ghost btn--sm" href="">Volver a revisar</a></div>
  <div class="grid-2">
    <?php foreach ($groups as $g => $list): ?>
      <section class="panel">
        <h2 style="font-size:1.5rem"><?= e($g) ?></h2>
        <ul class="checks checks--diag" style="list-style:none;padding:0">
          <?php foreach ($list as [, $t, $ok, $d]): ?>
            <li style="padding-left:0;display:grid;grid-template-columns:24px 1fr;gap:8px">
              <span class="pill pill--<?= $ok === true ? 'ok' : ($ok === false ? 'err' : 'warn') ?>" style="padding:2px 6px;text-align:center"><?= $ok === true ? '✓' : ($ok === false ? '✕' : '!') ?></span>
              <span><strong style="font-weight:500"><?= e($t) ?></strong><?php if ($d && $ok !== true || str_starts_with($d, 'http') || str_starts_with($d, 'Cuenta')): ?><br><small class="muted" style="word-break:break-all"><?= e($d) ?></small><?php endif; ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endforeach; ?>
  </div>
  <p class="muted" style="margin-top:18px;font-size:.88rem">Para comprobar Google Analytics: abre tu tienda en otra pestaña y mira en analytics.google.com → <em>Informes → Tiempo real</em>. Deberías verte como usuario activo en menos de un minuto.</p>
</div>
<?php page_footer(['admin' => true]); ?>
