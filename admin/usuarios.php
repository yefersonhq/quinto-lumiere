<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$me = require_admin();

$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) post('id');
    $action = post('action');
    if ($id === (int) $me['id'] && $action !== 'password') {
        flash('No puedes cambiar tu propio rol ni eliminar tu propia cuenta.', 'err');
    } elseif ($action === 'role') {
        $role = post('role') === 'admin' ? 'admin' : 'cliente';
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $id]);
        flash($role === 'admin' ? 'Ahora es administrador.' : 'Ahora es cliente.');
    } elseif ($action === 'password') {
        $pass = (string) ($_POST['password'] ?? '');
        if (strlen($pass) < 8) flash('La contraseña debe tener al menos 8 caracteres.', 'err');
        else {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $id]);
            flash('Contraseña actualizada. Compártela con la persona por un medio privado.');
        }
    } elseif ($action === 'delete') {
        $has = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
        $has->execute([$id]);
        if ($has->fetchColumn()) flash('Este usuario tiene pedidos; no se puede eliminar.', 'err');
        else { $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]); flash('Usuario eliminado.'); }
    }
    redirect('admin/usuarios.php' . (post('q') ? '?q=' . urlencode(post('q')) : ''));
}

$q = trim((string) ($_GET['q'] ?? ''));
$st = $pdo->prepare('SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS orders FROM users u
                     WHERE (? = \'\' OR u.name LIKE ? OR u.email LIKE ?) ORDER BY u.role, u.id DESC LIMIT 500');
$st->execute([$q, "%$q%", "%$q%"]);
$users = $st->fetchAll();

page_header('Usuarios', ['admin' => true]);
?>
<div class="wrap admin">
  <div class="admin__head"><h1>Usuarios</h1></div>
  <p class="notice" style="margin-bottom:16px"><strong>Administrador:</strong> acceso total (inventario, pedidos, usuarios, reclamos). <strong>Cliente:</strong> compra y ve sus propios pedidos. Los visitantes sin cuenta pueden ver la tienda y armar su carrito.</p>
  <form class="toolbar"><input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre o correo"><button class="btn btn--ghost btn--sm">Buscar</button></form>
  <div class="table-wrap">
    <table class="t">
      <thead><tr><th>Nombre</th><th>Correo</th><th>Celular</th><th class="num">Pedidos</th><th>Rol</th><th>Nueva contraseña</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): $self = $u['id'] == $me['id']; ?>
        <tr>
          <td><?= e($u['name']) ?><?= $self ? ' <small class="muted">(tú)</small>' : '' ?></td>
          <td><?= e($u['email']) ?></td>
          <td><?= e($u['phone']) ?></td>
          <td class="num"><?= (int) $u['orders'] ?></td>
          <td>
            <?php if ($self): ?><span class="pill pill--ok">Administrador</span><?php else: ?>
            <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="role"><input type="hidden" name="q" value="<?= e($q) ?>">
              <select class="select" name="role" onchange="this.form.submit()" aria-label="Rol de <?= e($u['name']) ?>">
                <option value="cliente" <?= $u['role'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
              </select></form>
            <?php endif; ?>
          </td>
          <td>
            <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="password"><input type="hidden" name="q" value="<?= e($q) ?>">
              <input class="input" type="password" name="password" minlength="8" placeholder="Mín. 8" style="width:110px" autocomplete="new-password" aria-label="Nueva contraseña para <?= e($u['name']) ?>"><button class="btn btn--ghost btn--sm">Cambiar</button></form>
          </td>
          <td><?php if (!$self): ?>
            <form method="post" data-confirm="¿Eliminar la cuenta de <?= e($u['name']) ?>?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="delete">
              <button class="icon-btn" style="color:var(--err)" aria-label="Eliminar <?= e($u['name']) ?>"><?= icon('trash', 18) ?></button></form>
          <?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php page_footer(['admin' => true]); ?>
