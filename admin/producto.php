<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
require_admin();

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$p = null;
if ($id) {
    $st = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch();
    if (!$p) { flash('Ese producto ya no existe.', 'err'); redirect('admin/productos.php'); }
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (post('action') === 'delete_image' && $id) {
        $st = $pdo->prepare('SELECT path FROM product_images WHERE id = ? AND product_id = ?');
        $st->execute([(int) post('image'), $id]);
        if ($path = $st->fetchColumn()) {
            @unlink(ROOT . '/' . $path);
            $pdo->prepare('DELETE FROM product_images WHERE id = ?')->execute([(int) post('image')]);
            flash('Foto eliminada.');
        }
        redirect('admin/producto.php?id=' . $id);
    }
    if (post('action') === 'cover_image' && $id) {
        $pdo->prepare('UPDATE product_images SET sort = sort + 1 WHERE product_id = ?')->execute([$id]);
        $pdo->prepare('UPDATE product_images SET sort = 0 WHERE id = ? AND product_id = ?')->execute([(int) post('image'), $id]);
        flash('Foto principal actualizada.');
        redirect('admin/producto.php?id=' . $id);
    }

    $data = [
        'name' => mb_substr(post('name'), 0, 200),
        'brand_id' => (int) post('brand_id') ?: null,
        'category_id' => (int) post('category_id') ?: null,
        'description' => mb_substr(post('description'), 0, 5000),
        'tags' => mb_substr(post('tags'), 0, 1000),
        'featured' => post('featured') ? 1 : 0,
        'active' => post('active') ? 1 : 0,
    ];
    // nueva marca escrita a mano
    $newBrand = trim(post('new_brand'));
    if ($newBrand !== '') {
        $st = $pdo->prepare('SELECT id FROM brands WHERE LOWER(name) = LOWER(?)');
        $st->execute([$newBrand]);
        $data['brand_id'] = $st->fetchColumn() ?: null;
        if (!$data['brand_id']) {
            $pdo->prepare('INSERT INTO brands (name, slug) VALUES (?, ?)')->execute([$newBrand, unique_slug('brands', $newBrand)]);
            $data['brand_id'] = (int) $pdo->lastInsertId();
        }
    }

    $vRows = [];
    foreach ((array) ($_POST['variants'] ?? []) as $row) {
        $label = trim((string) ($row['label'] ?? ''));
        $price = (string) ($row['price'] ?? '');
        if ($label === '' && $price === '') continue;
        $vRows[] = [
            'id' => (int) ($row['id'] ?? 0),
            'label' => mb_substr($label ?: 'Única', 0, 120),
            'sku' => mb_substr(trim((string) ($row['sku'] ?? '')), 0, 80),
            'price' => max(0, round((float) str_replace(',', '.', $price), 2)),
            'compare_price' => ($cp = trim((string) ($row['compare_price'] ?? ''))) !== '' ? max(0, round((float) str_replace(',', '.', $cp), 2)) : null,
            'stock' => max(0, (int) ($row['stock'] ?? 0)),
            'color' => preg_match('/^#[0-9a-f]{6}$/i', trim((string) ($row['color'] ?? ''))) ? strtolower(trim($row['color'])) : null,
        ];
    }

    if (mb_strlen($data['name']) < 3) $errors[] = 'El nombre es obligatorio.';
    if (!$vRows) $errors[] = 'Agrega al menos una variante con precio (por ejemplo «Única», «500 ml» o un tono).';
    foreach ($vRows as $v) if ($v['price'] <= 0) { $errors[] = "La variante «{$v['label']}» necesita un precio mayor a 0."; break; }

    if (!$errors) {
        $pdo->beginTransaction();
        if ($p) {
            $pdo->prepare('UPDATE products SET name=?, slug=?, brand_id=?, category_id=?, description=?, tags=?, featured=?, active=?, updated_at=? WHERE id=?')
                ->execute([$data['name'], unique_slug('products', $data['name'], $id), $data['brand_id'], $data['category_id'], $data['description'],
                    $data['tags'], $data['featured'], $data['active'], now(), $id]);
        } else {
            $pdo->prepare('INSERT INTO products (name, slug, brand_id, category_id, description, tags, featured, active, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute([$data['name'], unique_slug('products', $data['name']), $data['brand_id'], $data['category_id'], $data['description'],
                    $data['tags'], $data['featured'], $data['active'], now(), date('Y-m-d H:i:s', time() + 1)]);
            $id = (int) $pdo->lastInsertId();
        }
        // variantes: actualizar, crear y borrar las que se quitaron
        $keep = [];
        $upd = $pdo->prepare('UPDATE product_variants SET label=?, sku=?, price=?, compare_price=?, stock=?, sort=?, color=? WHERE id=? AND product_id=?');
        $ins = $pdo->prepare('INSERT INTO product_variants (product_id, label, sku, price, compare_price, stock, sort, color) VALUES (?,?,?,?,?,?,?,?)');
        foreach ($vRows as $i => $v) {
            if ($v['id']) {
                $upd->execute([$v['label'], $v['sku'], $v['price'], $v['compare_price'], $v['stock'], $i, $v['color'], $v['id'], $id]);
                $keep[] = $v['id'];
            } else {
                $ins->execute([$id, $v['label'], $v['sku'], $v['price'], $v['compare_price'], $v['stock'], $i, $v['color']]);
                $keep[] = (int) $pdo->lastInsertId();
            }
        }
        $pdo->prepare('DELETE FROM product_variants WHERE product_id = ? AND id NOT IN (' . implode(',', array_map('intval', $keep)) . ')')->execute([$id]);
        $pdo->commit();

        // fotos
        $up = 0;
        if (!empty($_FILES['images']['name'][0])) {
            $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort), -1) FROM product_images WHERE product_id = ' . $id)->fetchColumn();
            foreach ($_FILES['images']['name'] as $k => $_) {
                $file = ['name' => $_FILES['images']['name'][$k], 'tmp_name' => $_FILES['images']['tmp_name'][$k], 'error' => $_FILES['images']['error'][$k], 'size' => $_FILES['images']['size'][$k]];
                if ($path = save_image_upload($file, 'p' . $id)) {
                    $pdo->prepare('INSERT INTO product_images (product_id, path, sort) VALUES (?,?,?)')->execute([$id, $path, ++$sort]);
                    $up++;
                } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    flash('No se pudo subir «' . $file['name'] . '». Usa JPG, PNG o WEBP de hasta 8 MB.', 'err');
                }
            }
        }
        flash($p ? 'Producto actualizado.' : 'Producto creado.');
        redirect('admin/producto.php?id=' . $id);
    }
    // si hubo errores, mantener lo escrito
    $p = array_merge($p ?: [], $data);
    $variants = $vRows;
}

$variants = $variants ?? ($id ? product_variants($id) : [['id' => 0, 'label' => 'Única', 'sku' => '', 'price' => '', 'compare_price' => '', 'stock' => 0]]);
$images = $id ? product_images($id) : [];
$cats = categories();
$brs = brands();
$slug = $id ? $pdo->query('SELECT slug FROM products WHERE id = ' . $id)->fetchColumn() : null;

page_header($id ? 'Editar producto' : 'Nuevo producto', ['admin' => true]);
?>
<div class="wrap admin">
  <p class="crumbs" style="padding-top:0"><a href="<?= url('admin/productos.php') ?>">← Inventario</a></p>
  <div class="admin__head">
    <h1><?= $id ? e($p['name']) : 'Nuevo producto' ?></h1>
    <?php if ($slug): ?><a class="btn btn--ghost btn--sm" href="<?= url('producto.php?p=' . urlencode($slug)) ?>" target="_blank">Ver en la tienda ↗</a><?php endif; ?>
  </div>
  <?php if ($errors): ?><div class="flash flash--err" role="alert" style="margin-bottom:18px"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="grid-2">
    <?= csrf_field() ?>
    <div>
      <section class="panel form">
        <div class="field"><label for="name">Nombre del producto</label><input class="input" id="name" name="name" required value="<?= e($p['name'] ?? '') ?>" placeholder="Ej: Tinte Koleston 7.0 Rubio medio"></div>
        <div class="row">
          <div class="field"><label for="category_id">Categoría</label>
            <select class="select" id="category_id" name="category_id">
              <option value="">— Sin categoría —</option>
              <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($p['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
            </select></div>
          <div class="field"><label for="brand_id">Marca</label>
            <select class="select" id="brand_id" name="brand_id">
              <option value="">— Sin marca —</option>
              <?php foreach ($brs as $b): ?><option value="<?= $b['id'] ?>" <?= ($p['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
            </select>
            <input class="input" style="margin-top:6px" name="new_brand" placeholder="…o escribe una marca nueva"></div>
        </div>
        <div class="field"><label for="description">Descripción</label>
          <textarea class="textarea" id="description" name="description" rows="6" placeholder="Para qué sirve, cómo se usa, contenido, beneficios…"><?= e($p['description'] ?? '') ?></textarea></div>
        <div class="field"><label for="tags">Palabras clave para el buscador y el chatbot</label>
          <input class="input" id="tags" name="tags" value="<?= e($p['tags'] ?? '') ?>" placeholder="Ej: tinte rubio canas con amoniaco caja cobertura">
          <small>Escribe cómo lo buscarían tus clientes, incluso con errores (ej: «amopolla», «oxigenta»). No hace falta separarlas con comas.</small></div>
      </section>

      <section class="panel">
        <h2 style="font-size:1.5rem">Variantes, precios y stock</h2>
        <p class="muted" style="font-size:.88rem">Una fila por tamaño, tono o color. Si el producto es único, deja una sola fila llamada «Única».</p>
        <div class="table-wrap" style="border:0">
          <table class="t vtable">
            <thead><tr><th>Variante</th><th>Precio S/</th><th>Precio antes <small>(oferta)</small></th><th>Stock</th><th>Código</th><th title="Solo para tintes: si lo dejas vacío se calcula del número de tono">Color</th><th></th></tr></thead>
            <tbody data-variants>
            <?php foreach ($variants as $i => $v): $auto = ($t = tone_parse($v['label'] ?? '')) ? tone_hex($t) : ''; ?>
              <tr>
                <td><input type="hidden" name="variants[<?= $i ?>][id]" value="<?= (int) $v['id'] ?>"><input class="input" name="variants[<?= $i ?>][label]" value="<?= e($v['label']) ?>" placeholder="Ej: 500 ml · 7.3 Rubio medio dorado"></td>
                <td><input class="input" style="width:90px" name="variants[<?= $i ?>][price]" value="<?= $v['price'] !== '' ? number_format((float) $v['price'], 2, '.', '') : '' ?>" inputmode="decimal" placeholder="0.00"></td>
                <td><input class="input" style="width:90px" name="variants[<?= $i ?>][compare_price]" value="<?= $v['compare_price'] ? number_format((float) $v['compare_price'], 2, '.', '') : '' ?>" inputmode="decimal"></td>
                <td><input class="input" style="width:76px" type="number" min="0" name="variants[<?= $i ?>][stock]" value="<?= (int) $v['stock'] ?>"></td>
                <td><input class="input" style="width:110px" name="variants[<?= $i ?>][sku]" value="<?= e($v['sku'] ?? '') ?>"></td>
                <td style="white-space:nowrap"><span class="sw" style="--c:<?= e(($v['color'] ?? '') ?: ($auto ?: '#ffffff')) ?>;width:22px;height:22px;vertical-align:middle"></span>
                  <input class="input" style="width:88px;display:inline-block" name="variants[<?= $i ?>][color]" value="<?= e($v['color'] ?? '') ?>" placeholder="<?= $auto ? 'auto' : '—' ?>" pattern="#[0-9a-fA-F]{6}" title="Formato #RRGGBB. Vacío = automático"></td>
                <td><button type="button" class="icon-btn" data-remove-row aria-label="Quitar variante" style="color:var(--err)"><?= icon('trash', 18) ?></button></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <button type="button" class="btn btn--ghost btn--sm" style="margin-top:10px" data-add-variant><?= icon('plus', 16) ?> Agregar variante</button>
      </section>
    </div>

    <div>
      <section class="panel form">
        <label class="check"><input type="checkbox" name="active" value="1" <?= ($p['active'] ?? 1) ? 'checked' : '' ?>> <span><strong>Visible en la tienda</strong><br><small class="muted">Desmárcalo para ocultarlo sin borrarlo.</small></span></label>
        <label class="check"><input type="checkbox" name="featured" value="1" <?= !empty($p['featured']) ? 'checked' : '' ?>> <span><strong>Destacado</strong><br><small class="muted">Aparece en «Favoritos de la casa» en el inicio.</small></span></label>
      </section>

      <section class="panel">
        <h2 style="font-size:1.5rem">Fotos</h2>
        <?php if ($images): ?>
          <div class="imgs" style="margin-bottom:14px">
            <?php foreach ($images as $i => $img): ?>
              <figure>
                <img src="<?= e(url($img['path'])) ?>" alt="">
                <?php if ($i === 0): ?><small class="pill pill--ok" style="margin-top:4px">Principal</small>
                <?php else: ?><button class="linkbtn" style="font-size:.8rem" form="f-cover-<?= $img['id'] ?>">Hacer principal</button><?php endif; ?>
                <button class="linkbtn" style="font-size:.8rem;color:var(--err)" form="f-del-<?= $img['id'] ?>">Eliminar</button>
              </figure>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="field"><label for="images">Subir fotos</label><input class="input" type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
          <small>JPG, PNG o WEBP, hasta 8 MB cada una. Se optimizan automáticamente. Mejor fotos cuadradas con fondo claro.</small></div>
      </section>

      <button class="btn btn--block" type="submit" style="margin-top:18px"><?= $id ? 'Guardar cambios' : 'Crear producto' ?></button>
    </div>
  </form>

  <?php foreach ($images as $img): ?>
    <form id="f-del-<?= $img['id'] ?>" method="post" data-confirm="¿Eliminar esta foto?"><?= csrf_field() ?><input type="hidden" name="action" value="delete_image"><input type="hidden" name="image" value="<?= $img['id'] ?>"></form>
    <form id="f-cover-<?= $img['id'] ?>" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="cover_image"><input type="hidden" name="image" value="<?= $img['id'] ?>"></form>
  <?php endforeach; ?>
</div>
<?php page_footer(['admin' => true]); ?>
