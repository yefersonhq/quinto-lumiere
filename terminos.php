<?php
require __DIR__ . '/inc/bootstrap.php';
$store = cfg('store_name');
$legal = cfg('razon_social') ?: $store;
$ship = cfg('shipping');
page_header('Términos y condiciones');
?>
<article class="wrap prose">
  <p class="eyebrow">Legal</p>
  <h1>Términos y condiciones</h1>
  <p class="muted">Última actualización: <?= date('d/m/Y', filemtime(__FILE__)) ?></p>

  <p>Estos términos regulan el uso de este sitio web y las compras realizadas en él, operado por <strong><?= e($legal) ?></strong><?= cfg('ruc') ? ', con RUC ' . e(cfg('ruc')) : '' ?>, con domicilio en <?= e(cfg('address')) ?>, Lima, Perú («<?= e($store) ?>», «nosotros»). Al navegar o comprar aceptas estas condiciones, de acuerdo con la Ley N.º 29571, Código de Protección y Defensa del Consumidor.</p>

  <h2>1. Productos</h2>
  <ul>
    <li>Vendemos productos de cosmética, perfumería y peluquería originales. Las fotos son referenciales; el envase puede variar según el lote del fabricante.</li>
    <li>Los <strong>perfumes de catálogo</strong> (Avon, Ésika, Natura, Yanbal y otros) pueden venderse a pedido según el catálogo vigente de cada marca. Te confirmaremos disponibilidad y plazo antes de procesar tu pedido.</li>
    <li>Los tonos de tintes que ves en pantalla son aproximados. El resultado final depende del color base, porosidad y tratamientos previos del cabello. Te recomendamos hacer una prueba de mechón y de alergia 48 horas antes de aplicar cualquier coloración.</li>
  </ul>

  <h2>2. Precios y stock</h2>
  <ul>
    <li>Los precios están expresados en soles (S/) y son precios finales.</li>
    <li>Los precios y promociones son válidos mientras se muestren en la web o hasta agotar stock. El stock se confirma al momento del pago.</li>
    <li>Si por un error evidente un producto se publicó con un precio incorrecto, te contactaremos para confirmar el precio correcto o anular el pedido con devolución total del pago.</li>
  </ul>

  <h2>3. Cuenta de usuario</h2>
  <p>Puedes ver el catálogo sin registrarte. Para comprar debes crear una cuenta con datos verdaderos. Eres responsable de mantener tu contraseña en reserva.</p>

  <h2>4. Pagos</h2>
  <ul>
    <li>Aceptamos pagos con tarjeta de crédito o débito, Yape y PagoEfectivo a través de <strong>Mercado Pago</strong>. Los datos de tu tarjeta los procesa directamente Mercado Pago; nosotros nunca los vemos ni almacenamos.</li>
    <li>También puedes pagar por Yape, Plin o transferencia coordinando por WhatsApp. En ese caso el pedido se reserva por 24 horas; si no se confirma el pago, se anula.</li>
    <li>El pedido se procesa una vez confirmado el pago.</li>
  </ul>

  <h2>5. Entregas</h2>
  <ul>
    <li><strong>Recojo en tienda</strong> (<?= money($ship['recojo_tienda']) ?>): <?= e(cfg('address')) ?>. Te avisaremos cuando esté listo.</li>
    <li><strong>Delivery Lima / Lurín</strong> (<?= money($ship['lima']) ?>): de 1 a 3 días hábiles desde la confirmación del pago.</li>
    <li><strong>Provincia</strong> (<?= money($ship['provincia']) ?>): envío por agencia, de 2 a 5 días hábiles más el tiempo de la agencia.</li>
    <?php if ((float) $ship['gratis_desde'] > 0): ?><li>Delivery gratis en compras desde <?= money($ship['gratis_desde']) ?> (no aplica a recojo, que ya es gratis).</li><?php endif; ?>
    <li>Los plazos pueden variar por causas ajenas (feriados, clima, agencia). Revisa tu pedido al recibirlo y avísanos de inmediato cualquier daño.</li>
  </ul>

  <h2>6. Cambios y devoluciones</h2>
  <ul>
    <li>Por razones de <strong>higiene y salud</strong>, no se aceptan cambios ni devoluciones de productos abiertos, usados o sin sello de seguridad (tintes, cremas, perfumes, tratamientos, extensiones, etc.).</li>
    <li>Puedes solicitar el cambio de un producto <strong>cerrado y sellado</strong> dentro de los 7 días calendario de recibido, con su comprobante. Los gastos de envío del cambio corren por tu cuenta, salvo error nuestro.</li>
    <li>Si el producto llegó dañado, vencido o no corresponde a lo que compraste, escríbenos dentro de las 48 horas con fotos: lo cambiamos o te devolvemos el dinero sin costo.</li>
    <li>Las devoluciones de dinero se hacen por el mismo medio de pago. En pagos con tarjeta, el plazo depende de tu banco.</li>
  </ul>

  <h2>7. Uso adecuado de los productos</h2>
  <p>Sigue siempre las instrucciones del fabricante. Los productos de peluquería profesional (oxidantes, líquidos de ondulación, decolorantes) deben ser usados por personas capacitadas. No nos responsabilizamos por daños derivados del mal uso.</p>

  <h2>8. Asistente virtual</h2>
  <p>«Lumi», el asistente del sitio, sugiere productos según lo que escribes. Sus recomendaciones son orientativas y no reemplazan la asesoría de un profesional ni las indicaciones del fabricante.</p>

  <h2>9. Comprobantes</h2>
  <p>Por cada compra emitimos <strong>boleta de venta</strong>. Al estar acogidos al Nuevo RUS, no emitimos facturas.</p>

  <h2>10. Reclamos</h2>
  <p>Ponemos a tu disposición nuestro <a href="<?= url('reclamaciones.php') ?>">Libro de Reclamaciones virtual</a>. Responderemos en un plazo máximo de 15 días hábiles. También puedes escribirnos por <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener">WhatsApp</a> o al correo <?= e(cfg('email')) ?>.</p>

  <h2>11. Privacidad</h2>
  <p>El tratamiento de tus datos personales se rige por nuestra <a href="<?= url('privacidad.php') ?>">Política de privacidad</a>.</p>

  <h2>12. Cambios a estos términos y ley aplicable</h2>
  <p>Podemos actualizar estos términos; la versión vigente es la publicada en esta página al momento de tu compra. Se rigen por las leyes de la República del Perú.</p>
</article>
<?php page_footer(); ?>
