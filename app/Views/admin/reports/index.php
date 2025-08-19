<h2>Reportes</h2>
<form method="get" class="card">
  <div class="form-row">
    <div><label>Desde</label><input class="input" type="date" name="fecha_desde" value="<?=Utils::e($from)?>"></div>
    <div><label>Hasta</label><input class="input" type="date" name="fecha_hasta" value="<?=Utils::e($to)?>"></div>
  </div>
  <p><button class="btn">Filtrar</button> <a class="btn" href="/admin/reportes?fecha_desde=<?=$from?>&fecha_hasta=<?=$to?>&format=csv">Exportar CSV</a></p>
</form>
<table class="table">
  <tr><th>Orden</th><th>Email</th><th>Total $</th><th>Estado</th><th>Fecha</th><th>Rifa ID</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr><td><?=$r['code']?></td><td><?=$r['buyer_email']?></td><td><?=Utils::money($r['total_usd'])?></td><td><?=$r['status']?></td><td><?=$r['created_at']?></td><td><?=$r['raffle_id']?></td></tr>
  <?php endforeach; ?>
</table>