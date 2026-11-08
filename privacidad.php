<?php
require __DIR__ . '/inc/bootstrap.php';
$legal = cfg('razon_social') ?: cfg('store_name');
page_header('Política de privacidad');
?>
<article class="wrap prose">
  <p class="eyebrow">Legal</p>
  <h1>Política de privacidad</h1>
  <p class="muted">Última actualización: <?= date('d/m/Y', filemtime(__FILE__)) ?></p>

  <p>En <strong><?= e($legal) ?></strong><?= cfg('ruc') ? ' (RUC ' . e(cfg('ruc')) . ')' : '' ?> protegemos tus datos personales conforme a la Ley N.º 29733, Ley de Protección de Datos Personales, y su Reglamento (D.S. 016-2024-JUS).</p>

  <h2>¿Qué datos recogemos?</h2>
  <ul>
    <li><strong>Cuenta:</strong> nombre, correo, celular y contraseña (guardada cifrada; nadie puede leerla, ni nosotros).</li>
    <li><strong>Pedidos:</strong> dirección de entrega, referencia, productos comprados y montos.</li>
    <li><strong>Pagos:</strong> solo el número de operación y el estado del pago que nos informa Mercado Pago. Los datos de tu tarjeta los procesa Mercado Pago y nunca llegan a nuestra web.</li>
    <li><strong>Navegación:</strong> usamos Google Analytics para saber cuántas personas visitan la tienda, qué productos ven y desde dónde llegan. Google puede usar cookies para esto.</li>
    <li><strong>Búsquedas y asistente:</strong> guardamos las palabras que buscas (sin asociarlas a tu nombre) para mejorar nuestro catálogo.</li>
    <li><strong>Libro de Reclamaciones:</strong> los datos que registres en él.</li>
  </ul>

  <h2>¿Para qué los usamos?</h2>
  <ul>
    <li>Procesar, entregar y dar seguimiento a tus pedidos.</li>
    <li>Atender tus consultas y reclamos.</li>
    <li>Cumplir obligaciones legales y tributarias.</li>
    <li>Mejorar la tienda con estadísticas generales de uso.</li>
  </ul>
  <p>No vendemos ni alquilamos tus datos. Solo los compartimos con quienes nos ayudan a operar: Mercado Pago (pagos), empresas de courier o agencias (entregas), Google (estadísticas) y nuestro proveedor de hosting.</p>

  <h2>¿Cuánto tiempo los guardamos?</h2>
  <p>Mientras tengas una cuenta activa, y luego el tiempo que exijan las normas tributarias y de protección al consumidor.</p>

  <h2>Tus derechos</h2>
  <p>Puedes pedir acceder, rectificar, cancelar u oponerte al uso de tus datos (derechos ARCO) escribiendo a <?= e(cfg('email')) ?> o por <a href="<?= e(whatsapp_link('Hola, quiero ejercer mis derechos sobre mis datos personales.')) ?>" target="_blank" rel="noopener">WhatsApp</a>. Responderemos en los plazos de ley. Si no estás conforme, puedes acudir a la Autoridad Nacional de Protección de Datos Personales.</p>

  <h2>Cookies</h2>
  <p>Usamos una cookie de sesión necesaria para mantener tu carrito y tu sesión, y las cookies de Google Analytics. Puedes bloquearlas desde la configuración de tu navegador; la tienda seguirá funcionando, salvo el inicio de sesión y el carrito.</p>

  <h2>Seguridad</h2>
  <p>Usamos conexión cifrada (https), contraseñas cifradas y acceso restringido al panel de administración.</p>
</article>
<?php page_footer(); ?>
