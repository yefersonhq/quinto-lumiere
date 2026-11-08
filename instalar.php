<?php
/**
 * Primer arranque: crea la cuenta de administrador.
 * Solo funciona mientras NO exista ningún administrador.
 */
require __DIR__ . '/inc/bootstrap.php';

if (admin_exists()) redirect('login.php');

$errors = [];
$v = ['name' => '', 'email' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v = ['name' => mb_substr(post('name'), 0, 120), 'email' => mb_strtolower(post('email'))];
    $pass = (string) ($_POST['password'] ?? '');
    $st = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $st->execute([$v['email']]);
    $existing = $st->fetch();

    // ¿Ya tiene cuenta de cliente con ese correo? Con su contraseña correcta, la convertimos en administradora.
    if ($existing) {
        if (password_verify($pass, $existing['password_hash'])) {
            db()->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$existing['id']]);
            login_user((int) $existing['id']);
            flash('¡Listo! Tu cuenta ahora es de administradora. Revisa los precios y el stock de ejemplo.');
            redirect('admin/');
        }
        sleep(1);
        $errors[] = 'Ese correo ya tiene una cuenta. Escribe la contraseña de esa cuenta para convertirla en administradora.';
    }

    if (!$existing) {
        if (mb_strlen($v['name']) < 3) $errors[] = 'Escribe tu nombre.';
        if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo no válido.';
        if (strlen($pass) < 10) $errors[] = 'Usa una contraseña de al menos 10 caracteres.';
    }
    if (!$errors && !$existing) {
        db()->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?,?,?,?,?)')
            ->execute([$v['name'], $v['email'], password_hash($pass, PASSWORD_DEFAULT), 'admin', now()]);
        login_user((int) db()->lastInsertId());
        flash('¡Listo! Tu cuenta de administrador fue creada. Revisa los precios y el stock de ejemplo.');
        redirect('admin/');
    }
}

page_header('Configuración inicial');
?>
<div class="wrap">
  <div class="auth panel">
    <?= sun_svg(70) ?>
    <h1>Bienvenida a tu tienda</h1>
    <p class="muted" style="text-align:center">Crea la cuenta de <strong>administrador</strong>. Con ella podrás gestionar productos, pedidos y usuarios.</p>
    <p class="notice" style="font-size:.88rem">¿Ya creaste una cuenta en la tienda? Escribe ese mismo correo y su contraseña y la convertimos en administradora.</p>
    <?php if ($errors): ?><div class="flash flash--err" role="alert" style="margin-bottom:14px"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
    <form method="post" class="form">
      <?= csrf_field() ?>
      <div class="field"><label for="name">Tu nombre</label><input class="input" id="name" name="name" required value="<?= e($v['name']) ?>"></div>
      <div class="field"><label for="email">Correo de administrador</label><input class="input" type="email" id="email" name="email" required value="<?= e($v['email']) ?>"></div>
      <div class="field"><label for="password">Contraseña</label><input class="input" type="password" id="password" name="password" required minlength="10" autocomplete="new-password"><small>Mínimo 10 caracteres. Guárdala en un lugar seguro.</small></div>
      <button class="btn btn--block" type="submit">Crear administrador</button>
    </form>
  </div>
</div>
<?php page_footer(); ?>
