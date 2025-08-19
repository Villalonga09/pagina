<h2>Órdenes</h2>
<table class="table">
  <tr><th>ID</th><th>Código</th><th>Email</th><th>Cédula/DNI</th><th>Total $</th><th>Estado</th><th>Fecha</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= $r['id'] ?></td>
    <td><?= Utils::e($r['code']) ?></td>
    <td><?= Utils::e($r['buyer_email']) ?></td>
    <td><?= Utils::e($r['buyer_dni'] ?? '') ?></td>
    <td>$<?= Utils::money($r['total_usd']) ?></td>
    <td><?= Utils::e($r['status']) ?></td>
    <td><?= Utils::e($r['created_at']) ?></td>
    <td><a class="btn" href="/admin/ordenes/<?= $r['id'] ?>">Ver</a></td>
  </tr>

  <?php endforeach; ?>
</table>