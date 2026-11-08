<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
require_admin();

$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $type = post('type') === 'brand' ? 'brands' : 'categories';
    $action = post('action');
    $name = mb_substr(post('name'), 0, 120);
    $id = (int) post('id');
    if ($action === 'add' && mb_strlen($name) >= 2) {
        if ($type === 'categories') {
            $pdo->prepare('INSERT INTO categories (name, slug, sort) VALUES (?,?,?)')->execute([$name, unique_slug('categories', $name), 99]);
        } else {
            $pdo->prepare('INSERT INTO brands (name, slug) VALUES (?,?)')->execute([$name, unique_slug('brands', $name)]);
        }
        flash("«$name» agregado.");
    } elseif ($action === 'rename' && $id && mb_strlen($name) >= 2) {
        $pdo->prepare("UPDATE $type SET name = ? WHERE id = ?")->execute([$name, $id]);
        if ($type === 'categories' && post('sort') !== '') {
            $pdo->prepare('UPDATE categories SET sort = ? WHERE id = ?')->execute([(int) post('sort'), $id]);
        }
        flash('Cambios guardados.');
    } elseif (in_array($action, ['logo', 'remove_logo'], true) && $id && $type === 'brands') {
        $old = $pdo->prepare('SELECT logo FROM brands WHERE id = ?');
        $old->execute([$id]);
        $old = (string) $old->fetchColumn();
        $new = $action === 'logo' ? save_image_upload($_FILES['logo'] ?? [], 'logo-' . $id, 500) : null;
        if ($action === 'logo' && !$new) {
            flash('No se pudo subir el logo. Usa PNG, JPG o WEBP (mejor con fondo transparente).', 'err');
        } else {
            if ($old && str_starts_with($old, 'uploads/')) @unlink(ROOT . '/' . $old);
            $pdo->prepare('UPDATE brands SET logo = ? WHERE id = ?')->execute([$new, $id]);
            flash($new ? 'Logo actualizado.' : 'Logo quitado: se mostrará el nombre de la marca.');
        }
    } elseif ($action === 'delete' && $id) {
        $col = $type === 'categories' ? 'category_id' : 'brand_id';
        $pdo->prepare("UPDATE products SET $col = NULL WHERE $col = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM $type WHERE id = ?")->execute([$id]);
        flash('Eliminado. Los productos que lo usaban quedaron sin ' . ($type === 'categories' ? 'categoría' : 'marca') . '.');
    }
    redirect('admin/categorias.php');
}

$cats = categories();
$brs = brands();
page_header('Categorías y marcas', ['admin' => true]);

$block = function (string $title, string $type, array $rows) { ?>
  <section class="panel">
    <h2 style="font-size:1.5rem"><?= e($title) ?></h2>
    <form method="post" class="toolbar"><?= csrf_field() ?><input type="hidden" name="type" value="<?= $type ?>"><input type="hidden" name="action" value="add">
      <input class="input" name="name" required placeholder="Nuevo nombre"><button class="btn btn--sm"><?= icon('plus', 16) ?> Agregar</button></form>
    <div class="table-wrap">
      <table class="t">
        <thead><tr><?= $type === 'brand' ? '<th>Logo</th>' : '' ?><th>Nombre</th><?= $type === 'category' ? '<th>Orden</th>' : '' ?><th class="num">Productos</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <?php if ($type === 'brand'): ?>
            <td style="min-width:150px">
              <div style="margin-bottom:6px"><?= brand_logo($r['name'], $r['logo']) ?></div>
              <form method="post" enctype="multipart/form-data" class="inline-form"><?= csrf_field() ?><input type="hidden" name="type" value="brand"><input type="hidden" name="action" value="logo"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                <label class="linkbtn" style="font-size:.8rem;cursor:pointer"><?= $r['logo'] ? 'Cambiar' : 'Subir logo' ?><input type="file" name="logo" accept="image/png,image/jpeg,image/webp" hidden onchange="this.form.submit()"></label></form>
              <?php if ($r['logo']): ?>
              <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="type" value="brand"><input type="hidden" name="action" value="remove_logo"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="linkbtn" style="font-size:.8rem;color:var(--err)">Quitar</button></form>
              <?php endif; ?>
            </td>
            <?php endif; ?>
            <td colspan="<?= $type === 'category' ? 2 : 1 ?>">
              <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="type" value="<?= $type ?>"><input type="hidden" name="action" value="rename"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                <input class="input" name="name" value="<?= e($r['name']) ?>" style="min-width:180px">
                <?php if ($type === 'category'): ?><input class="input stock-input" type="number" name="sort" value="<?= (int) $r['sort'] ?>" title="Orden en el menú"><?php endif; ?>
                <button class="btn btn--ghost btn--sm">Guardar</button></form>
            </td>
            <td class="num"><?= (int) $r['n'] ?></td>
            <td><form method="post" data-confirm="¿Eliminar «<?= e($r['name']) ?>»?"><?= csrf_field() ?><input type="hidden" name="type" value="<?= $type ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="icon-btn" style="color:var(--err)" aria-label="Eliminar <?= e($r['name']) ?>"><?= icon('trash', 18) ?></button></form></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php };
?>
<div class="wrap admin">
  <div class="admin__head"><h1>Categorías y marcas</h1></div>
  <p class="notice" style="margin-bottom:16px">Sube el logo de cada marca (PNG con fondo transparente se ve mejor). Si una marca no tiene logo, se muestra su nombre con letra elegante.</p>
  <div class="grid-2">
    <div><?php $block('Categorías', 'category', $cats); ?></div>
    <div><?php $block('Marcas', 'brand', $brs); ?></div>
  </div>
</div>
<?php page_footer(['admin' => true]); ?>
