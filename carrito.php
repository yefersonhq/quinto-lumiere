<?php
require __DIR__ . '/inc/bootstrap.php';

$lines = cart_lines();
// sincroniza el carrito con el stock real
$_SESSION['cart'] = array_column(array_map(fn($l) => [$l['variant_id'], $l['qty']], $lines), 1, 0);
$subtotal = array_sum(array_column($lines, 'line_total'));
$free = (float) cfg('shipping.gratis_desde');

ga_event('view_cart', [
    'currency' => 'PEN', 'value' => $subtotal,
    'items' => array_map(fn($l) => ga_item($l, ['id' => $l['variant_id'], 'label' => $l['label'], 'price' => $l['price']], (int) $l['qty']), $lines),
]);

page_header('Tu carrito');
?>
<div class="wrap">
  <div class="two">
    <section class="panel">
      <h1 style="font-size:2.3rem">Tu carrito</h1>
      <?php if (!$lines): ?>
        <div class="empty" style="margin-top:12px">
          <h2>Tu carrito está vacío</h2>
          <p class="muted">Descubre nuestros perfumes, tintes y tratamientos.</p>
          <a class="btn" href="<?= url('tienda.php') ?>">Ir a la tienda</a>
        </div>
      <?php else: foreach ($lines as $l): ?>
        <div class="line">
          <img src="<?= e(product_cover($l)) ?>" alt="">
          <div>
            <a class="line__name" href="<?= url('producto.php?p=' . urlencode($l['slug'])) ?>"><?= e($l['name']) ?></a>
            <div class="line__meta"><?= e($l['label']) ?> · <?= money($l['price']) ?> c/u</div>
            <form method="post" action="<?= url('api/cart.php') ?>" class="inline-form" style="margin-top:8px">
              <?= csrf_field() ?><input type="hidden" name="action" value="set"><input type="hidden" name="variant" value="<?= (int) $l['variant_id'] ?>">
              <div class="qty">
                <button type="button" data-qty="-1" aria-label="Menos"><?= icon('minus', 14) ?></button>
                <input type="number" name="qty" value="<?= (int) $l['qty'] ?>" min="0" max="<?= (int) $l['stock'] ?>" aria-label="Cantidad" data-autosubmit>
                <button type="button" data-qty="1" aria-label="Más"><?= icon('plus', 14) ?></button>
              </div>
            </form>
          </div>
          <div class="line__right">
            <strong><?= money($l['line_total']) ?></strong>
            <form method="post" action="<?= url('api/cart.php') ?>">
              <?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="variant" value="<?= (int) $l['variant_id'] ?>">
              <button class="linkbtn" type="submit" style="font-size:.84rem">Quitar</button>
            </form>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </section>

    <?php if ($lines): ?>
    <aside class="panel sticky">
      <h2>Resumen</h2>
      <div class="totals">
        <div><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
        <div><span>Envío</span><span class="muted">Se calcula al pagar</span></div>
        <div class="total"><span>Total</span><span><?= money($subtotal) ?></span></div>
      </div>
      <?php if ($free > 0 && $subtotal < $free): ?>
        <p class="notice" style="margin-top:16px">Te faltan <strong><?= money($free - $subtotal) ?></strong> para el envío gratis.</p>
      <?php endif; ?>
      <a class="btn btn--block" style="margin-top:18px" href="<?= url('checkout.php') ?>">Continuar con la compra</a>
      <?php if (!current_user()): ?><p class="muted" style="font-size:.85rem;margin-top:10px;text-align:center">Te pediremos iniciar sesión o crear tu cuenta.</p><?php endif; ?>
      <a class="btn btn--ghost btn--block" style="margin-top:10px" href="<?= url('tienda.php') ?>">Seguir comprando</a>
    </aside>
    <?php endif; ?>
  </div>
</div>
<?php page_footer(); ?>
