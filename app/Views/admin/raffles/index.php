<div style="display:flex;justify-content:space-between;align-items:center">
  <h2>Rifas</h2>
  <a class="btn btn-primary" href="/admin/rifas/crear">Crear</a>
</div>
<table class="table">
  <tr><th>ID</th><th>Título</th><th>Descripción</th><th>Precio</th><th>Total</th><th>Vendidos</th><th>Estado</th><th></th></tr>
  <?php foreach ($raffles as $r): ?>
  <tr>
    <td><?= $r['id'] ?></td>
    <td><?= Utils::e($r['title']) ?></td>
    <td class="small" style="max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= Utils::e($r['description']) ?></td>
    <td>$<?= Utils::money($r['price_usd']) ?></td>
    <td><?= $r['total_tickets'] ?></td>
    <td><?= $r['sold_tickets'] ?></td>
    <td><?= $r['status'] ?></td>
    <td style="white-space:nowrap; display:flex; gap:8px;"><a class="btn" href="/admin/rifas/<?= $r['id'] ?>/editar">Editar</a><form action="/admin/rifas/<?= $r['id'] ?>/eliminar" method="post" onsubmit="return confirm('¿Eliminar esta rifa? Esta acción no se puede deshacer.')"><?= CSRF::field() ?><button class="btn btn-danger">Eliminar</button></form></td>
  </tr>
  <?php endforeach; ?>
</table>