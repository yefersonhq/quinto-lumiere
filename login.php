<?php
require __DIR__ . '/inc/bootstrap.php';

if (current_user()) redirect(is_admin() ? 'admin/' : 'cuenta.php');

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = mb_strtolower(post('email'));
    $pass = (string) ($_POST['password'] ?? '');
    $_SESSION['login_fails'] = $_SESSION['login_fails'] ?? 0;
    if ($_SESSION['login_fails'] >= 5) sleep(2);

    $st = db()->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($pass, $u['password_hash'])) {
        $_SESSION['login_fails'] = 0;
        login_user((int) $u['id']);
        $to = $_SESSION['after_login'] ?? null;
        unset($_SESSION['after_login']);
        flash('¡Hola de nuevo, ' . explode(' ', $u['name'])[0] . '!');
        if ($to && str_starts_with($to, '/')) { header('Location: ' . $to); exit; }
        redirect($u['role'] === 'admin' ? 'admin/' : 'tienda.php');
    }
    $_SESSION['login_fails']++;
    $error = 'Correo o contraseña incorrectos.';
}

page_header('Iniciar sesión');
?>
<div class="wrap">
  <div class="auth panel">
    <?= sun_svg(70) ?>
    <h1>Bienvenida</h1>
    <p class="muted" style="text-align:center">Ingresa para comprar y ver tus pedidos.</p>
    <?php if ($error): ?><div class="flash flash--err" role="alert" style="margin-bottom:14px"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form">
      <?= csrf_field() ?>
      <div class="field"><label for="email">Correo</label><input class="input" type="email" id="email" name="email" required autocomplete="email" value="<?= e($email) ?>"></div>
      <div class="field"><label for="password">Contraseña</label><input class="input" type="password" id="password" name="password" required autocomplete="current-password"></div>
      <button class="btn btn--block" type="submit">Ingresar</button>
    </form>
    <p class="auth__alt">¿Aún no tienes cuenta? <a href="<?= url('registro.php') ?>">Créala aquí</a></p>
    <p class="auth__alt muted" style="font-size:.84rem">¿Olvidaste tu contraseña? <a href="<?= e(whatsapp_link('Hola, olvidé la contraseña de mi cuenta en la web.')) ?>" target="_blank" rel="noopener">Escríbenos</a></p>
  </div>
</div>
<?php page_footer(); ?>
