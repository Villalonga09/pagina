<h2>Orden <?=$order['code']?></h2>
<div class="card">
  <p>Estado: <span class="tag"><?=$order['status']?></span></p>
  <p>Cliente: <?=$order['buyer_name']?> — <?=$order['buyer_email']?> — Cédula/DNI: <?=$order['buyer_dni']??''?></p>
  <p>Total: $<?=Utils::money($order['total_usd'])?> / Bs. <?=Utils::money($order['total_ves'])?></p>
</div>
<div class="card">
  <h3>Items</h3>
  <table class="table"><tr><th>Rifa</th><th>#</th><th>$</th></tr>
  <?php foreach ($items as $it): ?>
    <tr><td><?=$it['title']?></td><td>#<?=$it['number']?></td><td><?=Utils::money($it['price_usd'])?></td></tr>
  <?php endforeach; ?>
  </table>
</div>
<div class="card">
  <h3>Pagos</h3>
  <table class="table"><tr><th>ID</th><th>Método</th><th>Monto Bs</th><th>Monto $</th><th>Ref</th><th>Estado</th><th>Comprobante</th><th>Acciones</th></tr>
  <?php foreach ($payments as $p): ?>
    <tr>
      <td><?=$p['id']?></td><td><?=$p['method']?></td><td><?=$p['amount_ves']?></td><td><?=$p['amount_usd']?></td><td><?=$p['reference']?></td><td><?=$p['status']?></td>
      <td><?php if($p['receipt_path']): ?><a href="/file/receipt/<?=$p['receipt_path']?>">Ver</a><?php endif; ?></td>
      <td>
        <form action="/admin/pagos/<?=$p['id']?>/aprobar" method="post" style="display:inline"><?=CSRF::field()?><button class="btn">Aprobar</button></form>
        <form action="/admin/pagos/<?=$p['id']?>/rechazar" method="post" style="display:inline"><?=CSRF::field()?><button class="btn">Rechazar</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  </table>
</div>