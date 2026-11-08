<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/mercadopago.php';

$user = require_login();
$lines = cart_lines();
if (!$lines) {
    flash('Tu carrito está vacío.', 'info');
    redirect('carrito.php');
}
$subtotal = array_sum(array_column($lines, 'line_total'));
$mp = mp_enabled();
$errors = [];
$old = [
    'ship_method' => 'lima', 'ship_name' => $user['name'], 'ship_phone' => $user['phone'] ?? '',
    'ship_address' => '', 'ship_district' => '', 'ship_ref' => '', 'notes' => '', 'pay' => $mp ? 'mercadopago' : 'whatsapp',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($old as $k => $_) $old[$k] = mb_substr((string) post($k), 0, $k === 'notes' ? 1000 : 250);
    if (!in_array($old['ship_method'], ['recojo', 'lima', 'provincia'], true)) $errors[] = 'Elige un método de entrega.';
    if (!in_array($old['pay'], ['mercadopago', 'whatsapp'], true) || ($old['pay'] === 'mercadopago' && !$mp)) $errors[] = 'Elige un método de pago.';
    if (mb_strlen($old['ship_name']) < 3) $errors[] = 'Escribe el nombre de quien recibe.';
    if (!preg_match('/^[0-9+\s]{9,15}$/', $old['ship_phone'])) $errors[] = 'Escribe un celular válido (9 dígitos).';
    if ($old['ship_method'] !== 'recojo' && (mb_strlen($old['ship_address']) < 6 || mb_strlen($old['ship_district']) < 3)) {
        $errors[] = 'Completa la dirección y el distrito/provincia de entrega.';
    }
    if (empty($_POST['terms'])) $errors[] = 'Debes aceptar los Términos y condiciones.';

    if (!$errors) {
        $shipping = shipping_cost($old['ship_method'], $subtotal);
        $total = $subtotal + $shipping;
        $code = 'QL-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO orders (code, user_id, status, payment_method, subtotal, shipping, total, ship_method, ship_name, ship_phone,
                       ship_address, ship_district, ship_ref, notes, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$code, $user['id'], 'pendiente', $old['pay'], $subtotal, $shipping, $total, $old['ship_method'], $old['ship_name'],
                $old['ship_phone'], $old['ship_address'], $old['ship_district'], $old['ship_ref'], $old['notes'], now()]);
        $orderId = (int) $pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, variant_id, name, variant_label, price, qty) VALUES (?,?,?,?,?,?,?)');
        $items = [];
        foreach ($lines as $l) {
            $ins->execute([$orderId, $l['product_id'], $l['variant_id'], $l['name'], $l['label'], $l['price'], $l['qty']]);
            $items[] = ['variant_id' => $l['variant_id'], 'name' => $l['name'], 'variant_label' => $l['label'], 'price' => $l['price'], 'qty' => $l['qty']];
        }
        if ($user['phone'] === null || $user['phone'] === '') {
            $pdo->prepare('UPDATE users SET phone = ? WHERE id = ?')->execute([$old['ship_phone'], $user['id']]);
        }
        $pdo->commit();
        $_SESSION['cart'] = [];

        if ($old['pay'] === 'mercadopago') {
            try {
                $order = ['id' => $orderId, 'code' => $code, 'shipping' => $shipping];
                redirect(mp_create_preference($order, $items, $user));
            } catch (Throwable $e) {
                error_log('[checkout] ' . $e->getMessage());
                flash('No pudimos abrir Mercado Pago. Tu pedido quedó guardado: puedes reintentar o coordinar el pago por WhatsApp.', 'err');
            }
        }
        redirect('pago.php?o=' . urlencode($code) . '&nuevo=1');
    }
}

$ship = cfg('shipping');
$free = (float) $ship['gratis_desde'];
ga_event('begin_checkout', [
    'currency' => 'PEN', 'value' => $subtotal,
    'items' => array_map(fn($l) => ga_item($l, ['id' => $l['variant_id'], 'label' => $l['label'], 'price' => $l['price']], (int) $l['qty']), $lines),
]);

page_header('Finalizar compra');
?>
<div class="wrap">
  <form method="post" class="two" data-checkout
        data-subtotal="<?= $subtotal ?>" data-free="<?= $free ?>"
        data-ship='<?= e(json_encode(['recojo' => (float) $ship['recojo_tienda'], 'lima' => (float) $ship['lima'], 'provincia' => (float) $ship['provincia']])) ?>'>
    <?= csrf_field() ?>
    <div>
      <h1 style="font-size:2.3rem">Finalizar compra</h1>
      <?php if ($errors): ?>
        <div class="flash flash--err" role="alert" style="margin-bottom:18px"><?= implode('<br>', array_map('e', $errors)) ?></div>
      <?php endif; ?>

      <section class="panel">
        <h2>1. Entrega</h2>
        <div class="radios">
          <label class="radio"><input type="radio" name="ship_method" value="recojo" <?= $old['ship_method'] === 'recojo' ? 'checked' : '' ?>>
            <span><strong>Recojo en tienda</strong> — <?= money($ship['recojo_tienda']) ?><small>Mercado San Pedro de Lurín, Stand 36</small></span></label>
          <label class="radio"><input type="radio" name="ship_method" value="lima" <?= $old['ship_method'] === 'lima' ? 'checked' : '' ?>>
            <span><strong>Delivery Lima / Lurín</strong> — <?= money($ship['lima']) ?><small>De 1 a 3 días hábiles</small></span></label>
          <label class="radio"><input type="radio" name="ship_method" value="provincia" <?= $old['ship_method'] === 'provincia' ? 'checked' : '' ?>>
            <span><strong>Envío a provincia</strong> — <?= money($ship['provincia']) ?><small>Por agencia (Olva / Shalom), de 2 a 5 días hábiles</small></span></label>
        </div>
        <?php if ($free > 0): ?><p class="muted" style="font-size:.86rem;margin:10px 0 0">Delivery gratis en compras desde <?= money($free) ?>.</p><?php endif; ?>
      </section>

      <section class="panel">
        <h2>2. Tus datos</h2>
        <div class="form">
          <div class="row">
            <div class="field"><label for="ship_name">Nombre de quien recibe</label><input class="input" id="ship_name" name="ship_name" required value="<?= e($old['ship_name']) ?>" autocomplete="name"></div>
            <div class="field"><label for="ship_phone">Celular</label><input class="input" id="ship_phone" name="ship_phone" required inputmode="tel" value="<?= e($old['ship_phone']) ?>" autocomplete="tel" placeholder="9XX XXX XXX"></div>
          </div>
          <div data-address>
            <div class="field"><label for="ship_address">Dirección</label><input class="input" id="ship_address" name="ship_address" value="<?= e($old['ship_address']) ?>" autocomplete="street-address" placeholder="Av. / Calle, número, dpto."></div>
            <div class="row" style="margin-top:14px">
              <div class="field"><label for="ship_district">Distrito / Provincia</label><input class="input" id="ship_district" name="ship_district" value="<?= e($old['ship_district']) ?>" placeholder="Ej: Lurín, Lima"></div>
              <div class="field"><label for="ship_ref">Referencia</label><input class="input" id="ship_ref" name="ship_ref" value="<?= e($old['ship_ref']) ?>" placeholder="Cerca de…"></div>
            </div>
          </div>
          <div class="field"><label for="notes">Notas del pedido (opcional)</label><textarea class="textarea" id="notes" name="notes" placeholder="Ej: oxidante de 20 vol, horario de entrega, etc."><?= e($old['notes']) ?></textarea></div>
        </div>
      </section>

      <section class="panel">
        <h2>3. Pago</h2>
        <div class="radios">
          <?php if ($mp): ?>
          <label class="radio"><input type="radio" name="pay" value="mercadopago" <?= $old['pay'] === 'mercadopago' ? 'checked' : '' ?>>
            <span><strong>Mercado Pago</strong><small>Tarjeta de crédito o débito, Yape o PagoEfectivo. Pago 100% seguro.</small></span></label>
          <?php endif; ?>
          <label class="radio"><input type="radio" name="pay" value="whatsapp" <?= $old['pay'] === 'whatsapp' ? 'checked' : '' ?>>
            <span><strong>Yape, Plin o transferencia</strong><small>Te enviamos los datos de pago por WhatsApp y confirmamos tu pedido.</small></span></label>
        </div>
      </section>
    </div>

    <aside class="panel sticky">
      <h2>Tu pedido</h2>
      <?php foreach ($lines as $l): ?>
        <div class="totals" style="margin-bottom:8px"><div><span><?= (int) $l['qty'] ?> × <?= e($l['name']) ?> <small class="muted">(<?= e($l['label']) ?>)</small></span><span><?= money($l['line_total']) ?></span></div></div>
      <?php endforeach; ?>
      <div class="totals" style="margin-top:14px;padding-top:12px;border-top:1px solid var(--line)">
        <div><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
        <div><span>Envío</span><span data-ship-cost>—</span></div>
        <div class="total"><span>Total</span><span data-total><?= money($subtotal) ?></span></div>
      </div>
      <label class="check" style="margin-top:18px"><input type="checkbox" name="terms" value="1" required>
        <span>He leído y acepto los <a href="<?= url('terminos.php') ?>" target="_blank">Términos y condiciones</a> y la <a href="<?= url('privacidad.php') ?>" target="_blank">Política de privacidad</a>.</span></label>
      <button class="btn btn--block" style="margin-top:16px" type="submit">Confirmar y pagar</button>
      <p class="muted" style="font-size:.8rem;text-align:center;margin-top:10px"><?= icon('shield', 14) ?> Tus datos de tarjeta los procesa Mercado Pago; nunca pasan por nuestra web.</p>
    </aside>
  </form>
</div>
<?php page_footer(); ?>
