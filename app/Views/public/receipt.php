<?php $statusColor = ['pendiente'=>'#fbbf24','pagado'=>'#34d399','rechazado'=>'#f87171'][$order['status']] ?? '#b9c1d1'; ?>
<div style="max-width:740px;margin:0 auto;background:#fff;color:#111;padding:24px;border-radius:12px">
  <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #eee;padding-bottom:12px;margin-bottom:12px">
    <div><h2 style="margin:0">Comprobante</h2><div style="font-size:12px;color:#555">Orden <?=$order['code']?></div></div>
    <div style="padding:6px 10px;border-radius:999px;background:<?=$statusColor?>;color:#000;font-weight:700;text-transform:uppercase"><?=$order['status']?></div>
  </div>
  <p><strong>Cliente:</strong> <?=Utils::e($order['buyer_name'])?> — <?=Utils::e($order['buyer_email'])?></p>
  <p><strong>Total:</strong> $<?=Utils::money($order['total_usd'])?> / Bs. <?=Utils::money($order['total_ves'])?></p>
  <h3>Boletos</h3>
  <ul>
    <?php foreach ($items as $it): $url = Utils::url('/orden/'.$order['code']); ?>
      <li><?=Utils::e($it['title'])?> — #<?=$it['number']?> — <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?=urlencode($url)?>" alt="QR"></li>
    <?php endforeach; ?>
  </ul>
  <p style="font-size:12px;color:#555">Escanea el QR para ver tu orden: <?=Utils::url('/orden/'.$order['code'])?></p>
</div>