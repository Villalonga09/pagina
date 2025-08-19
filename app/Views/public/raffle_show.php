<?php /** Detalle de Rifa — solo disponibles, layout limpio */ ?>
<h2><?= Utils::e($raffle['title']) ?></h2>
<p class="section-subtitle raffle-sub">Elige tus boletos disponibles y confirma tu pago para participar en el sorteo.</p>
<?php if (!empty($raffle['banner_path'])): ?>
  <img src="<?= Utils::e($raffle['banner_path']) ?>" class="banner" style="height:220px">
<?php else: ?>
  <div class="banner-placeholder hero"><div class="text"><?= Utils::e($raffle['title']) ?></div></div>
<?php endif; ?>
<p class="small"><?= nl2br(Utils::e($raffle['description'])) ?></p>
<p class="small"><span class="badge">Tasa BCV vigente: Bs. <?= Utils::money($bcv) ?> por $1</span></p>

<div class="card">
  <form action="/orden" method="post" id="orderForm">
    <?= CSRF::field() ?>
    <input type="hidden" name="raffle_id" value="<?= (int)$raffle['id'] ?>">

    <div class="form-row">
      <!-- LEFT: Tickets -->
      <div class="ticket-panel">
        <div class="ticket-toolbar">
          <div class="section-title">Selecciona cantidad de boletos:</div>
          <div class="toolbar-left">
            <span class="counter">Seleccionados: <span id="selCount">0</span></span>
            <span class="counter">Total: <span id="totUsdTop">$0,00</span> / <span id="totVesTop">Bs. 0,00</span></span>
          
        <div class="quick-buy">
          <div class="quick-picks" id="quickPicks">
            <button type="button" class="qp" data-val="1"><span class="qp-num">1</span></button>
            <button type="button" class="qp is-active" data-val="2">
              <span class="qp-num">2</span>
              <span class="qp-tag">Más popular</span>
            </button>
            <button type="button" class="qp" data-val="5"><span class="qp-num">5</span></button>
            <button type="button" class="qp" data-val="10"><span class="qp-num">10</span></button>
            <button type="button" class="qp" data-val="25"><span class="qp-num">25</span></button>
            <button type="button" class="qp" data-val="50"><span class="qp-num">50</span></button>
          </div>

          <div class="quick-cta">
            <div class="qty-stepper" id="qtyStepper">
              <button type="button" class="qty-btn" id="qMinus">−</button>
              <input type="text" id="qInput" value="1" inputmode="numeric" pattern="[0-9]*" />
              <button type="button" class="qty-btn" id="qPlus">+</button>
            </div>
</div>
        </div>

</div>
        </div>
<div class="ticket-grid is-hidden" id="ticketGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(64px,1fr));gap:10px;max-height:380px;overflow:auto;padding:10px;border:1px dashed var(--border);border-radius:12px;background:rgba(255,255,255,.03)">
          <?php
          $has = false;
          if (!empty($tickets)):
            foreach ($tickets as $t):
              if ($t['status'] !== 'disponible') continue;
              $has = true; ?>
              <label class="ticket disponible" data-number="<?= (int)$t['number'] ?>" data-status="disponible">
                <input type="checkbox" name="ticket_ids[]" value="<?= (int)$t['id'] ?>">
                <span class="pill" style="display:flex;align-items:center;justify-content:center;width:100%;height:46px;border-radius:12px;border:1px solid rgba(255,255,255,.18);background:rgba(125,211,252,.06)">#<?= (int)$t['number'] ?></span>
              </label>
          <?php endforeach; endif; ?>
          <?php if (!$has): ?><div class="small">No hay boletos disponibles ahora mismo.</div><?php endif; ?>
        </div>
      </div>

      <!-- RIGHT: Form -->
      <div class="side-panel">
        <div class="section-title">Tus datos</div>
        <div class="card">
          <label>Nombre completo</label>
          <input class="input" name="buyer_name" placeholder="Nombre completo" required pattern="[\p{L} '.-]{2,}" title="Solo letras y espacios">
          <label>Email</label>
          <input class="input" name="buyer_email" placeholder="Email" required type="email">
          
          <label>Cédula/DNI</label>
          <input class="input" name="buyer_dni" placeholder="Cédula o DNI" required pattern="[0-9A-Za-z.\-]{4,20}" title="Ingresa tu Cédula o DNI"><label>Teléfono</label>
          <input class="input" name="buyer_phone" placeholder="Ej: 04121234567" type="tel" inputmode="numeric" pattern="[0-9]{6,15}" title="Solo números (6 a 15 dígitos)">

          <p class="small">Precio por boleto: <strong>$<?= Utils::money($raffle['price_usd']) ?></strong> /
            <strong>Bs. <?= Utils::money($raffle['price_usd'] * $bcv) ?></strong></p>

          <div class="total-box"><strong>Total:</strong> <span id="totUsdBox">$0,00</span> / <span id="totVesBox">Bs. 0,00</span></div>
          <div id="formError" style="display:none;margin:8px 0 10px 0;color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;padding:8px 12px;border-radius:10px;font-size:14px;">Selecciona al menos un boleto.</div>
          <p style="margin-top:10px"><button class="btn btn-primary" style="width:100%">Crear Orden</button></p>
        </div>
      </div>
    </div>
  
    <!-- Modal Términos -->
    <div id="termsModal" class="modal" style="position: fixed;inset: 0px;background: rgb(255 255 255 / 1%);backdrop-filter: blur(8px);display: none;align-items: center;justify-content: center;z-index: 100;">
      <div class="card" style="max-width:720px;width:92%;background:#fff;border-radius:16px;padding:18px;border:1px solid rgba(2,6,23,.35);">
        <h3 style="margin:0 0 6px 0">Términos y condiciones</h3>
        <div style="max-height:45vh;overflow:auto;color:#334155;font-size:14px;line-height:1.5;border:1px solid var(--border);border-radius:10px;padding:12px;background:#f8fafc">
          <?php include APP_PATH . '/Views/public/partials/terms.php'; ?>
        </div>
        <div id="modalError" style="display:none;margin:8px 0 10px 0;color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;padding:8px 12px;border-radius:10px;font-size:14px;">Debes aceptar los términos.</div>
<label style="display:flex;gap:8px;align-items:center;margin-top:12px">
          <input type="checkbox" id="acceptTerms"> Acepto los términos y condiciones
        </label>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
          <button type="button" class="btn" id="btnCloseModal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btnAgree">Aceptar y continuar</button>
        </div>
      </div>
    </div>
    
  </form>
</div>

<script>
(function(){
  let pendingSubmit=false;
const priceUsd = <?= json_encode((float)$raffle['price_usd']) ?>;
  const bcv = <?= json_encode((float)$bcv) ?>;
  const fmt = n => (new Intl.NumberFormat('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2})).format(n);

  const grid = document.getElementById('ticketGrid');
  const selCount = document.getElementById('selCount');
  const totUsdTop = document.getElementById('totUsdTop');
  const totVesTop = document.getElementById('totVesTop');
  const totUsdBox = document.getElementById('totUsdBox');
  const totVesBox = document.getElementById('totVesBox');
  let lastClickedNumber = null;
  const formError = document.getElementById('formError');

  function updateTotals(){
    const checked = grid.querySelectorAll('input[name="ticket_ids[]"]:checked');
    const qty = checked.length;
    const usd = qty * priceUsd;
    const ves = usd * bcv;
    selCount.textContent = qty;
    totUsdTop.textContent = '$' + fmt(usd);
    totVesTop.textContent = 'Bs. ' + fmt(ves);
    if (totUsdBox) totUsdBox.textContent = '$' + fmt(usd);
    if (totVesBox) totVesBox.textContent = 'Bs. ' + fmt(ves);
  }

  // Shift range select between available numbers
  grid.addEventListener('click', (e) => {
    const label = e.target.closest('label.ticket.disponible');
    if (!label) return;
    const cb = label.querySelector('input[type="checkbox"]');
    if (!cb) return;
    const number = parseInt(label.getAttribute('data-number'), 10);
    if (e.shiftKey && lastClickedNumber !== null){
      const min = Math.min(lastClickedNumber, number);
      const max = Math.max(lastClickedNumber, number);
      const boxes = Array.from(grid.querySelectorAll('label.ticket.disponible input[type="checkbox"]'));
      boxes.forEach(x => {
        const n = parseInt(x.parentElement.getAttribute('data-number'),10);
        if (n>=min && n<=max) x.checked = true;
      });
      updateTotals();
      e.preventDefault();
    } else {
      setTimeout(function(){ updateTotals(); if(formError) formError.style.display='none'; }, 0);
    }
    lastClickedNumber = number;
  });

  updateTotals();

  // Intercept form submit: require tickets and acceptance
  const form = document.getElementById('orderForm');
  const modal = document.getElementById('termsModal');
  const btnAgree = document.getElementById('btnAgree');
  const btnCloseModal = document.getElementById('btnCloseModal');
  const chk = document.getElementById('acceptTerms');
  function openModal(){ modal.style.display='flex'; var me=document.getElementById('modalError'); if(me){ me.style.display='none'; } }
  function closeModal(){ modal.style.display='none'; }
  form.addEventListener('submit', function(e){
    const selected = grid.querySelectorAll('input[name="ticket_ids[]"]:checked').length;
    if (selected===0){ e.preventDefault(); var fe=document.getElementById('formError'); if (fe){ fe.style.display='block'; fe.textContent='Selecciona al menos un boleto.'; } return; }
    if (!pendingSubmit){ e.preventDefault(); openModal(); }
  });
  btnCloseModal && btnCloseModal.addEventListener('click', closeModal);
  chk && chk.addEventListener('change', function(){ var me=document.getElementById('modalError'); if(me) me.style.display='none'; });
  btnAgree && btnAgree.addEventListener('click', function(){
    var me = document.getElementById('modalError');
    if (!chk.checked){ if (me){ me.style.display='block'; me.textContent='Debes aceptar los términos.'; } return; }
    pendingSubmit = true;
    closeModal();
    form.submit();
  });

  // === Quick buy controls ===
  const quickPicks = document.getElementById('quickPicks');
  const qMinus = document.getElementById('qMinus');
  const qPlus  = document.getElementById('qPlus');
  const qInput = document.getElementById('qInput');
  const boxes  = Array.from(grid.querySelectorAll('input[name="ticket_ids[]"]'));
  function clamp(n, min, max){ return Math.max(min, Math.min(max, n)); }
  function availableCount(){ return boxes.length; }
  function fillTo(qty){
    qty = clamp(parseInt(qty||0,10) || 0, 0, availableCount());
    // uncheck all
    boxes.forEach(b => { b.checked = false; });
    // check first N available
    for (let i=0; i<qty && i<boxes.length; i++){
      boxes[i].checked = true;
    }
    // UI reflect
    qInput.value = String(qty || 0);
    updateTotals();
  }
  // quick pick buttons
  if (quickPicks){
    quickPicks.addEventListener('click', (e)=>{
      const btn = e.target.closest('.qp');
      if (!btn) return;
      const val = parseInt(btn.getAttribute('data-val'),10) || 0;
      document.querySelectorAll('.qp.is-active').forEach(x=>x.classList.remove('is-active'));
      btn.classList.add('is-active');
      fillTo(val);
    });
  }
  // stepper
  qMinus && qMinus.addEventListener('click', ()=>{ fillTo((parseInt(qInput.value,10)||0)-1); });
  qPlus  && qPlus .addEventListener('click', ()=>{ fillTo((parseInt(qInput.value,10)||0)+1); });
  qInput && qInput.addEventListener('input', ()=>{
    const v = (qInput.value || '').replace(/\D+/g,'');
    qInput.value = v;
    fillTo(parseInt(v,10)||0);
  });
  // Set default selection (2 or max available)
  fillTo(Math.min(2, availableCount()));

})();</script>
