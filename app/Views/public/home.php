<div class="hero-rotator" id="heroRot">
  <div class="hero-title enter" id="heroTitle"><?= Utils::e($hero_slides[0]['title'] ?? '') ?></div>
  <div class="hero-sub enter" id="heroSub"><?= Utils::e($hero_slides[0]['desc'] ?? '') ?></div>
</div>
<script>
(function(){
  const slides = <?= json_encode(array_values($hero_slides), JSON_UNESCAPED_UNICODE) ?>;
  let i = 0;
  const title = document.getElementById('heroTitle');
  const sub   = document.getElementById('heroSub');
  function swap(){
    i = (i + 1) % slides.length;
    title.classList.remove('enter'); sub.classList.remove('enter');
    title.classList.add('leave'); sub.classList.add('leave');
    setTimeout(()=>{
      const s = slides[i] || {title:'', desc:''};
      title.textContent = s.title || '';
      sub.textContent   = s.desc || '';
      title.classList.remove('leave'); sub.classList.remove('leave');
      title.classList.add('enter'); sub.classList.add('enter');
    }, 420);
  }
  if (slides.length > 1) setInterval(swap, 10000);
})();
</script>

<h2 class="accent-title align-center">Rifas disponibles</h2>
<p class="small align-center"><span class="badge">Tasa BCV vigente: Bs. <?= Utils::money($bcv) ?> por $1</span></p>
<div class="grid grid-center">
<?php foreach ($raffles as $r): ?>
  <div class="card">
    <?php
      $meses = [1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
      $dt = new DateTime($r['created_at'] ?? 'now');
      $fecha_es = intval($dt->format('j')) . ' de ' . $meses[intval($dt->format('n'))] . ' de ' . $dt->format('Y');
    ?>
    <?php if ($r['banner_path']): ?>
    <div class="banner-wrap">
      <img src="<?= Utils::e($r['banner_path']) ?>" class="banner">
      <span class="date-badge" title="Fecha de creación">
        <svg class="cal-ic" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <?= Utils::e($fecha_es) ?>
      </span>
      <div class="cta-blink" aria-hidden="true">Participa ahora</div>
    </div>
    <?php else: ?>
    <div class="banner-wrap">
      <div class="banner-placeholder">
        <div class="text"><?= Utils::e($r['title']) ?></div>
      </div>
      <span class="date-badge" title="Fecha de creación">
        <svg class="cal-ic" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <?= Utils::e($fecha_es) ?>
      </span>
      <div class="cta-blink" aria-hidden="true">Participa ahora</div>
    </div>
    <?php endif; ?>
    <h3><?= Utils::e($r['title']) ?></h3>
    <p class="small" style="min-height:36px;opacity:.85;">
      <?= Utils::e(mb_strimwidth((string)($r['description'] ?? ''), 0, 90, '…', 'UTF-8')) ?>
    </p>
    <div class="small">Premio: <span class="badge"><?= Utils::e($r['prize']) ?></span></div>
    <p>Precio: <strong>$<?= Utils::money($r['price_usd']) ?></strong> / <strong>Bs. <?= Utils::money($r['price_usd'] * $bcv) ?></strong></p>
    <?php $progress = $r['total_tickets']>0 ? intval(($r['sold_tickets']/$r['total_tickets'])*100) : 0; ?>
    <div class="progress"><span style="width: <?=$progress?>%"></span></div>

    <?php
      $meses = [1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
      $dt = new DateTime($r['created_at'] ?? 'now');
      $fecha_es = intval($dt->format('j')) . ' de ' . $meses[intval($dt->format('n'))] . ' de ' . $dt->format('Y');
    ?>
    <p><a href="/rifa/<?=$r['id']?>" class="btn btn-primary">Ver rifa</a></p>
  </div>
<?php endforeach; ?>
</div>