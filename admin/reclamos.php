<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
require_admin();

$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $status = post('status') === 'cerrado' ? 'cerrado' : 'abierto';
    $pdo->prepare('UPDATE complaints SET response = ?, status = ? WHERE id = ?')->execute([mb_substr(post('response'), 0, 3000), $status, (int) post('id')]);
    flash('Reclamo actualizado. Recuerda enviar la respuesta al correo del cliente.');
    redirect('admin/reclamos.php');
}
$rows = $pdo->query('SELECT * FROM complaints ORDER BY status = \'abierto\' DESC, id DESC')->fetchAll();

page_header('Reclamos', ['admin' => true]);
?>
<div class="wrap admin">
  <div class="admin__head"><h1>Libro de Reclamaciones</h1></div>
  <p class="notice" style="margin-bottom:16px">Por ley tienes <strong>15 días hábiles</strong> para responder cada reclamo. Conserva el registro al menos 2 años.</p>
  <?php foreach ($rows as $r): $days = (int) floor((time() - strtotime($r['created_at'])) / 86400); ?>
    <details class="panel" <?= $r['status'] === 'abierto' ? 'open' : '' ?>>
      <summary style="cursor:pointer;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <strong><?= e($r['code']) ?></strong> · <?= e(ucfirst($r['type'])) ?> · <?= e($r['name']) ?>
        <span class="pill pill--<?= $r['status'] === 'abierto' ? ($days > 10 ? 'err' : 'warn') : 'ok' ?>"><?= $r['status'] === 'abierto' ? "Abierto · hace $days días" : 'Cerrado' ?></span>
      </summary>
      <div class="grid-2" style="margin-top:14px">
        <div>
          <p><small class="muted">DNI/CE:</small> <?= e($r['doc']) ?> · <small class="muted">Correo:</small> <a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a> · <small class="muted">Tel:</small> <?= e($r['phone']) ?></p>
          <p><small class="muted">Domicilio:</small> <?= e($r['address']) ?></p>
          <p><small class="muted"><?= e(ucfirst($r['item_type'])) ?> · Monto:</small> <?= $r['amount'] !== null ? money($r['amount']) : '—' ?> · <small class="muted">Fecha:</small> <?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></p>
          <p><strong>Detalle:</strong><br><?= nl2br(e($r['description'])) ?></p>
          <p><strong>Pedido:</strong><br><?= nl2br(e($r['request'])) ?></p>
        </div>
        <form method="post" class="form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <div class="field"><label>Respuesta / acciones tomadas</label><textarea class="textarea" name="response"><?= e($r['response']) ?></textarea></div>
          <select class="select" name="status"><option value="abierto" <?= $r['status'] === 'abierto' ? 'selected' : '' ?>>Abierto</option><option value="cerrado" <?= $r['status'] === 'cerrado' ? 'selected' : '' ?>>Cerrado (respondido)</option></select>
          <button class="btn btn--sm">Guardar</button>
        </form>
      </div>
    </details>
  <?php endforeach; if (!$rows): ?><div class="empty"><p class="muted" style="margin:0">No hay reclamos registrados.</p></div><?php endif; ?>
</div>
<?php page_footer(['admin' => true]); ?>
