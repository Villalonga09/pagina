<h2>Dashboard</h2>
<div class="kpis">
  <div class="kpi"><div class="small">Ventas USD</div><div style="font-size:24px">$<?=Utils::money($totals['usd'])?></div></div>
  <div class="kpi"><div class="small">Ventas VES</div><div style="font-size:24px">Bs. <?=Utils::money($totals['ves'])?></div></div>
  <div class="kpi"><div class="small">Boletos vendidos</div><div style="font-size:24px"><?=$sold?></div></div>
  <div class="kpi"><div class="small">Rifas activas</div><div style="font-size:24px"><?=$active?></div></div>
</div>
<h3>Actividad reciente</h3>
<?php if (empty($activity)): ?>
  <p class="small">Sin actividad</p>
<?php else: ?>
  <ul>
    <?php foreach ($activity as $act): ?>
      <li>[<?=$act['created_at']?>] <?=$act['action']?> - <?=$act['entity_type']?> #<?=$act['entity_id']?> — <?=$act['message']?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>