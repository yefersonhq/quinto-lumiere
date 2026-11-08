<?php
/** Libro de Reclamaciones virtual (Ley N.º 29571 — Código de Protección y Defensa del Consumidor, D.S. 011-2011-PCM). */
require __DIR__ . '/inc/bootstrap.php';

$errors = [];
$done = null;
$u = current_user();
$v = ['type' => 'reclamo', 'name' => $u['name'] ?? '', 'doc' => '', 'email' => $u['email'] ?? '', 'phone' => $u['phone'] ?? '', 'address' => '',
      'item_type' => 'producto', 'amount' => '', 'description' => '', 'request' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($v as $k => $_) $v[$k] = mb_substr((string) post($k), 0, in_array($k, ['description', 'request'], true) ? 3000 : 250);
    if (!in_array($v['type'], ['reclamo', 'queja'], true)) $errors[] = 'Elige reclamo o queja.';
    if (!in_array($v['item_type'], ['producto', 'servicio'], true)) $errors[] = 'Elige producto o servicio.';
    if (mb_strlen($v['name']) < 3) $errors[] = 'Escribe tu nombre completo.';
    if (!preg_match('/^[0-9A-Za-z]{8,12}$/', $v['doc'])) $errors[] = 'Escribe tu DNI o CE.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo no válido.';
    if (mb_strlen($v['description']) < 10) $errors[] = 'Describe lo ocurrido.';
    if (mb_strlen($v['request']) < 5) $errors[] = 'Indica tu pedido o solicitud.';
    if (empty($_POST['accept'])) $errors[] = 'Debes confirmar que la información es verdadera.';
    if (!$errors) {
        $n = (int) db()->query('SELECT COUNT(*) FROM complaints')->fetchColumn() + 1;
        $code = sprintf('LR-%s-%05d', date('Y'), $n);
        db()->prepare('INSERT INTO complaints (code, type, name, doc, email, phone, address, item_type, amount, description, request, created_at)
                       VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$code, $v['type'], $v['name'], $v['doc'], $v['email'], $v['phone'], $v['address'], $v['item_type'],
                $v['amount'] !== '' ? (float) $v['amount'] : null, $v['description'], $v['request'], now()]);
        $done = $code;
    }
}

page_header('Libro de Reclamaciones');
?>
<div class="wrap prose">
  <h1>Libro de Reclamaciones</h1>
  <p class="muted"><?= e(cfg('razon_social') ?: cfg('store_name')) ?><?= cfg('ruc') ? ' · RUC ' . e(cfg('ruc')) : '' ?> · <?= e(cfg('address')) ?></p>

  <?php if ($done): ?>
    <div class="panel">
      <h2 style="margin-top:0">Registramos tu <?= e($v['type']) ?></h2>
      <p>Tu código es <strong><?= e($done) ?></strong> (fecha: <?= date('d/m/Y H:i') ?>). Guárdalo. Te responderemos al correo <strong><?= e($v['email']) ?></strong> en un plazo máximo de 15 días hábiles.</p>
      <p class="muted" style="font-size:.9rem">Te recomendamos tomar una captura de esta pantalla como constancia.</p>
    </div>
  <?php else: ?>
    <p><strong>Reclamo:</strong> disconformidad relacionada a los productos o servicios. <strong>Queja:</strong> disconformidad no relacionada a los productos o servicios, o malestar respecto a la atención.</p>
    <?php if ($errors): ?><div class="flash flash--err" role="alert" style="margin-bottom:14px"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
    <form method="post" class="panel form">
      <?= csrf_field() ?>
      <h2 style="margin:0">1. Tus datos</h2>
      <div class="row">
        <div class="field"><label for="name">Nombre completo</label><input class="input" id="name" name="name" required value="<?= e($v['name']) ?>"></div>
        <div class="field"><label for="doc">DNI / CE</label><input class="input" id="doc" name="doc" required value="<?= e($v['doc']) ?>"></div>
      </div>
      <div class="row">
        <div class="field"><label for="email">Correo</label><input class="input" type="email" id="email" name="email" required value="<?= e($v['email']) ?>"></div>
        <div class="field"><label for="phone">Teléfono</label><input class="input" id="phone" name="phone" value="<?= e($v['phone']) ?>"></div>
      </div>
      <div class="field"><label for="address">Domicilio</label><input class="input" id="address" name="address" value="<?= e($v['address']) ?>"></div>

      <h2 style="margin:10px 0 0">2. Bien contratado</h2>
      <div class="row">
        <div class="field"><label for="item_type">Tipo</label><select class="select" id="item_type" name="item_type">
          <option value="producto" <?= $v['item_type'] === 'producto' ? 'selected' : '' ?>>Producto</option>
          <option value="servicio" <?= $v['item_type'] === 'servicio' ? 'selected' : '' ?>>Servicio</option></select></div>
        <div class="field"><label for="amount">Monto reclamado (S/)</label><input class="input" id="amount" name="amount" inputmode="decimal" value="<?= e($v['amount']) ?>"></div>
      </div>

      <h2 style="margin:10px 0 0">3. Detalle</h2>
      <div class="field"><label for="type">Tipo de registro</label><select class="select" id="type" name="type">
        <option value="reclamo" <?= $v['type'] === 'reclamo' ? 'selected' : '' ?>>Reclamo</option>
        <option value="queja" <?= $v['type'] === 'queja' ? 'selected' : '' ?>>Queja</option></select></div>
      <div class="field"><label for="description">Detalle (incluye número de pedido si lo tienes)</label><textarea class="textarea" id="description" name="description" required><?= e($v['description']) ?></textarea></div>
      <div class="field"><label for="request">Pedido del consumidor</label><textarea class="textarea" id="request" name="request" required><?= e($v['request']) ?></textarea></div>
      <label class="check"><input type="checkbox" name="accept" value="1" required> <span>Declaro que la información es verdadera.</span></label>
      <button class="btn">Enviar</button>
      <p class="muted" style="font-size:.84rem;margin:0">La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el INDECOPI. El proveedor deberá dar respuesta al reclamo en un plazo no mayor a quince (15) días hábiles.</p>
    </form>
  <?php endif; ?>
</div>
<?php page_footer(); ?>
