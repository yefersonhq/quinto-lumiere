<?php
require __DIR__ . '/inc/bootstrap.php';

if (current_user()) redirect('cuenta.php');

$errors = [];
$v = ['name' => '', 'email' => '', 'phone' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v = ['name' => mb_substr(post('name'), 0, 120), 'email' => mb_strtolower(post('email')), 'phone' => mb_substr(post('phone'), 0, 20)];
    $pass = (string) ($_POST['password'] ?? '');
    if (mb_strlen($v['name']) < 3) $errors[] = 'Escribe tu nombre completo.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'El correo no es válido.';
    if (strlen($pass) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
    if (empty($_POST['terms'])) $errors[] = 'Debes aceptar los Términos y la Política de privacidad.';
    if (!$errors) {
        $st = db()->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$v['email']]);
        if ($st->fetchColumn()) $errors[] = 'Ya existe una cuenta con ese correo. Inicia sesión.';
    }
    if (!$errors) {
        db()->prepare('INSERT INTO users (name, email, phone, password_hash, role, created_at) VALUES (?,?,?,?,?,?)')
            ->execute([$v['name'], $v['email'], $v['phone'], password_hash($pass, PASSWORD_DEFAULT), 'cliente', now()]);
        login_user((int) db()->lastInsertId());
        ga_event('sign_up', ['method' => 'email']);
        flash('¡Tu cuenta está lista, ' . explode(' ', $v['name'])[0] . '!');
        $to = $_SESSION['after_login'] ?? null;
        unset($_SESSION['after_login']);
        if ($to && str_starts_with($to, '/')) { header('Location: ' . $to); exit; }
        redirect('tienda.php');
    }
}

page_header('Crear cuenta');
?>
<div class="wrap">
  <div class="auth panel">
    <?= sun_svg(70) ?>
    <h1>Crea tu cuenta</h1>
    <p class="muted" style="text-align:center">Compra más rápido y sigue tus pedidos.</p>
    <?php if ($errors): ?><div class="flash flash--err" role="alert" style="margin-bottom:14px"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
    <form method="post" class="form">
      <?= csrf_field() ?>
      <div class="field"><label for="name">Nombre completo</label><input class="input" id="name" name="name" required autocomplete="name" value="<?= e($v['name']) ?>"></div>
      <div class="field"><label for="email">Correo</label><input class="input" type="email" id="email" name="email" required autocomplete="email" value="<?= e($v['email']) ?>"></div>
      <div class="field"><label for="phone">Celular (opcional)</label><input class="input" id="phone" name="phone" inputmode="tel" autocomplete="tel" value="<?= e($v['phone']) ?>"></div>
      <div class="field"><label for="password">Contraseña</label><input class="input" type="password" id="password" name="password" required minlength="8" autocomplete="new-password"><small>Mínimo 8 caracteres.</small></div>
      <label class="check"><input type="checkbox" name="terms" value="1" required>
        <span>Acepto los <a href="<?= url('terminos.php') ?>" target="_blank">Términos y condiciones</a> y la <a href="<?= url('privacidad.php') ?>" target="_blank">Política de privacidad</a>.</span></label>
      <button class="btn btn--block" type="submit">Crear cuenta</button>
    </form>
    <p class="auth__alt">¿Ya tienes cuenta? <a href="<?= url('login.php') ?>">Inicia sesión</a></p>
  </div>
</div>
<?php page_footer(); ?>
