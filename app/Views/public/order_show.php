<div class="order-page">
<h2>Orden <?=$order['code']?></h2>
<p class="section-subtitle order-sub">Revisa el estado de tu orden y sube el comprobante de pago si aún está pendiente.</p>
<div class="order-grid">
<div class="card order-summary">
  <p><strong>Cliente:</strong> <?=Utils::e($order['buyer_name'])?> — <?=Utils::e($order['buyer_email'])?> — <?=Utils::e($order['buyer_phone'])?></p>
  <?php $bcvLive = (new Setting())->getBcvRateAuto(); $bsHoy = $order['total_usd'] * $bcvLive; $amount_usd = floatval($order['total_usd']); $amount_ves = floatval($bsHoy); ?>
<p><strong>Total:</strong> $<?=Utils::money($order['total_usd'])?> / Bs. <?=Utils::money($order['total_ves'])?> <span class="small">(Bs hoy: <?=Utils::money($bsHoy)?>)</span></p>
  <?php
  $remaining = isset($remaining) ? (int)$remaining : 0;
  $expiry_ts = time() + $remaining;
  ?>
<p><strong>Estado:</strong> <span class="tag"><?=Utils::e($order['status'])?></span></p>
  <?php if ($order['status']==='pendiente'): ?>
  <div id="countdownBox" class="alert countdown-box countdown-box--minimal" role="status" aria-live="polite">
    Tiempo restante para pagar: <strong><span id="countdown" data-expiry="<?=$expiry_ts?>" aria-live="polite"></span></strong>
    <div id="countdownProgress" class="cd-progress" role="progressbar" aria-label="Tiempo restante" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100"><span></span></div>
  </div>
  <?php endif; ?>
  </div>
  <br>
<div class="card order-tickets">
  <h3 class="card-title">Boletos</h3>
  <table class="table card-table">
    <tr><th>Rifa</th><th>#</th></tr>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?=Utils::e($it['title'])?></td>
        <td>#<?=$it['number']?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  </div>
  <br>
</div>
<?php if (!empty($_GET['uploaded'])): ?>
  <div class="alert alert-success" id="paymentUploadedMsg">¡Comprobante recibido! Está en revisión.</div>
  <script>
    setTimeout(function(){ var el=document.getElementById('paymentUploadedMsg'); if(el){ el.style.display='none'; } }, 5000);
  </script>
<?php endif; ?>
<div class="card order-payment">
  <h3 class="card-title">Pago</h3>
  <?php if ($order['status'] === 'pendiente'): ?>
  <div class="security-notice"><span class="lock-icon">🔒</span> Tus datos se transmiten de forma segura</div>
  <a id="pago"></a>
<form action="/orden/<?=$order['code']?>/pago" method="post" enctype="multipart/form-data">
    <?= CSRF::field() ?>
<div class="form-grid order-form-grid">
      <div class="col-left order-column-left">
        <div>
          <label>Método</label>
          <select name="method">
            <option value="pago_movil">Pago Móvil</option>
            <option value="zelle">Zelle</option>
            <option value="binance">Binance</option>
          </select>
        </div>
        <div id="vesGroup" style="display:none"><label>Monto Bs.</label><input class="input" name="amount_ves" value="<?= number_format($amount_ves, 2, '.', '') ?>" readonly></div>
        <div id="usdGroup" style="display:none"><label>Monto $</label><input class="input" name="amount_usd" value="<?= number_format($amount_usd, 2, '.', '') ?>" readonly></div>
        <div>
          <label>Referencia</label>
          <input class="input" name="reference">
          <div class="verification-info verification-info--badge small" role="note">
            <div id="verifTip" class="tooltip" role="tooltip">La verificación se realiza manualmente y puede tardar hasta 2 horas.</div>
          </div>
        </div>
      </div>
      <div class="col-right order-column-right">
        <div id="paymentDetails" class="small payment-details payment-details--minimal" data-paybox-variant="minimal" role="region" aria-label="Detalles del pago"></div>
        <div class="uploadbox uploadbox--minimal" role="group" aria-labelledby="receiptLabel" aria-describedby="receiptHelp">
          <div id="receiptLabel" class="uploadbox__title">Comprobante</div>
          <p id="receiptHelp" class="small uploadbox__help">Adjunta la imagen del pago (JPG, PNG o WEBP, máx. 5MB).</p>
          <input id="receiptInput" type="file" name="receipt" accept="image/*" required aria-describedby="receiptHelp">
          <div class="uploadbox__actions">
            <label for="receiptInput" class="btn-slim uploadbox__button" role="button" tabindex="0">Seleccionar archivo</label>
            <span id="receiptName" class="small uploadbox__filename" aria-live="polite">Ningún archivo seleccionado</span>
          </div>
          <!-- Modal para vista previa -->
          <div id="imgModal" class="modal" aria-hidden="true" role="dialog" aria-label="Vista previa del comprobante">
            <div id="modalBackdrop" class="modal-backdrop"></div>
            <div class="modal-content">
              <button id="modalClose" type="button" class="modal-close" aria-label="Cerrar">×</button>
              <img id="modalImg" src="" alt="Comprobante">
            </div>
          </div>
        </div>
      </div>
    </div>
    <p><button class="btn btn-primary">Enviar pago</button></p>
  </form>
  <?php else: ?>
  <div class="alert alert-warning">
    <?php if ($order['status'] === 'aprobado'): ?>
      Esta orden ya fue aprobada. ¡Gracias por tu pago!
    <?php elseif ($order['status'] === 'rechazado'): ?>
      Esta orden fue rechazada. Si crees que es un error, contacta al soporte.
    <?php else: ?>
      Esta orden ya no está pendiente.
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php if (!empty($payments)): ?>
    <h4 class="card-title mt-16">Pagos enviados</h4>
    <table class="table card-table">
      <tr>
        <th>Método</th>
        <th>Estado</th>
        <th>Referencia</th>
        <th>Comprobante</th>
      </tr>
      <?php foreach ($payments as $p):
        $stClass = [
          'pendiente' => 'badge-warning',
          'aprobado' => 'badge-success',
          'rechazado' => 'badge-danger'
        ][$p['status']] ?? 'badge';
      ?>
        <tr>
          <td><?=Utils::e($p['method'])?></td>
          <td><span class="badge <?=$stClass?>"><?=Utils::e($p['status'])?></span></td>
          <td><?=Utils::e($p['reference'])?></td>
          <td>
            <?php if ($p['receipt_path']): ?>
              <img src="/file/receipt/<?=Utils::e($p['receipt_path'])?>" alt="Comprobante" class="receipt-thumb">
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
</div>




<script>
(function(){
  // Safety: ensure amounts are filled & readonly on DOM ready
  var ves = document.querySelector('input[name="amount_ves"]');
  var usd = document.querySelector('input[name="amount_usd"]');
  if (ves) { ves.readOnly = true; }
  if (usd) { usd.readOnly = true; }

  var amtUsd = parseFloat('<?= number_format($amount_usd, 2, ".", "") ?>');
  var amtVes = parseFloat('<?= number_format($amount_ves, 2, ".", "") ?>');
  if (usd && !usd.value) { usd.value = amtUsd.toFixed(2); }
  if (ves && !ves.value) { ves.value = amtVes.toFixed(2); }

  var methodSel = document.querySelector('select[name="method"]');
  var usdGroup = document.getElementById('usdGroup');
  var vesGroup = document.getElementById('vesGroup');
  function toggleAmounts(){
    var m = methodSel ? methodSel.value : 'pago_movil';
    var showUsd = (m === 'zelle' || m === 'binance');
    var showVes = (m === 'pago_movil');
    if (usdGroup) { usdGroup.style.display = showUsd ? '' : 'none'; }
    if (vesGroup) { vesGroup.style.display = showVes ? '' : 'none'; }
    if (usd) { usd.disabled = !showUsd; }
    if (ves) { ves.disabled = !showVes; }
  }

  var detailsNode = document.getElementById('paymentDetails');
  if (!detailsNode) return;

  var details = <?php
    $s = new Setting();
    $pm_bank = $s->get('pago_movil_bank', 'Banco Ejemplo');
    $pm_phone = $s->get('pago_movil_phone', '0412-0000000');
    $pm_id    = $s->get('pago_movil_id', 'V-00.000.000');
    $pm_name  = $s->get('pago_movil_name', 'Nombre y Apellido');
    $z_email  = $s->get('zelle_email', 'correo@zelle.com');
    $z_name   = $s->get('zelle_name', 'Nombre Zelle');
    $bi_user  = $s->get('binance_user', 'usuario_binance');
    $bi_id    = $s->get('binance_id', 'ID/Pay ID');
    echo json_encode([
      'pago_movil'=>['title'=>'Pago Móvil','rows'=>[['Banco',$pm_bank],['Teléfono',$pm_phone],['Cédula/RIF',$pm_id],['Titular',$pm_name]]],
      'zelle'=>['title'=>'Zelle','rows'=>[['Email',$z_email],['Titular',$z_name]]],
      'binance'=>['title'=>'Binance Pay','rows'=>[['Usuario',$bi_user],['ID/Pay ID',$bi_id]]],
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  ?>;

  function copyText(t, el){
    if (!t) return;
    try { navigator.clipboard.writeText(t); }
    catch(e){
      var ta = document.createElement('textarea');
      ta.value = t; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
    }
    showToast('Copiado');
    if (el){
      el.classList.add('copied');
      setTimeout(function(){ el.classList.remove('copied'); }, 800);
    }
  }

  var toast;
  function showToast(msg){
    if (!toast){
      toast = document.createElement('div');
      toast.className = 'toast';
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(function(){ toast.classList.remove('show'); }, 1200);
  }

  function renderDetails(key){
    var d = details[key];
    if (!d){ detailsNode.innerHTML = ''; return; }
    var variant = detailsNode.getAttribute('data-paybox-variant') || 'minimal';
    var html = '<div class="paybox paybox--'+ variant +'" role="list">';
    html += '<div class="paybox__title">'+ d.title +'</div>';
    d.rows.forEach(function(r){
      var label = r[0], val = r[1];
      html += '<div class="payrow" role="listitem">'
           +    '<span class="paylabel">'+ label +'</span>'
           +    '<span class="payvalue" tabindex="0" data-copy="'+ String(val).replace(/"/g,'&quot;') +'" aria-label="Copiar '+ label +'">'+ val +'</span>'
           +  '</div>';
    });
    html += '</div>';
    detailsNode.innerHTML = html;

    detailsNode.querySelectorAll('.payvalue').forEach(function(el){
      el.addEventListener('click', function(){ copyText(this.getAttribute('data-copy'), this); });
      el.addEventListener('keydown', function(e){ if(e.key==='Enter' || e.key===' '){ copyText(this.getAttribute('data-copy'), this); }});
    });
  }

  function onChange(){
    toggleAmounts();
    renderDetails(methodSel ? methodSel.value : 'pago_movil');
  }

  toggleAmounts();
  renderDetails(methodSel ? methodSel.value : 'pago_movil');
  if (methodSel){ methodSel.addEventListener('change', onChange); }

  // Modal preview logic (only on filename click)
    var input = document.getElementById('receiptInput');
    var nameEl = document.getElementById('receiptName');
    var modal = document.getElementById('imgModal');
    var modalImg = document.getElementById('modalImg');
    var modalClose = document.getElementById('modalClose');
    var modalBackdrop = document.getElementById('modalBackdrop');
    var dataUrl = null;

  function openModal(){
    if (!dataUrl) return;
    modalImg.src = dataUrl;
    modal.classList.add('show');
    modal.setAttribute('aria-hidden','false');
  }
  function closeModal(){
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden','true');
    setTimeout(function(){ modalImg.src=''; }, 200);
  }

      if (nameEl){ nameEl.addEventListener('click', openModal); }
      if (modalClose){ modalClose.addEventListener('click', closeModal); }
      if (modalBackdrop){ modalBackdrop.addEventListener('click', closeModal); }
      document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeModal(); }});

  if (input){
      input.addEventListener('change', function(){
        var f = input.files && input.files[0];
        dataUrl = null;
        if (!f){
          if (nameEl){ nameEl.textContent = 'Ningún archivo seleccionado'; nameEl.classList.remove('linkish'); }
          return;
        }
        if (nameEl){ nameEl.textContent = f.name + ' (' + Math.round(f.size/1024) + ' KB)'; }
        if (f.type && f.type.indexOf('image/') === 0){
          var reader = new FileReader();
          reader.onload = function(e){
            dataUrl = e.target.result;
            if (nameEl){ nameEl.classList.add('linkish'); }
          };
          reader.readAsDataURL(f);
        } else {
          if (nameEl){ nameEl.classList.remove('linkish'); }
        }
      });
    }

  document.querySelectorAll('.receipt-thumb').forEach(function(img){
    img.addEventListener('click', function(){
      dataUrl = this.src;
      openModal();
    });
  });
})();
</script>




<script>
(function(){
  function fmt(n){ return n<10?('0'+n):(''+n); }
    var cd = document.getElementById('countdown');
    if (cd){
      var expiry = parseInt(cd.getAttribute('data-expiry')||'0',10);
      var bar = document.getElementById('countdownProgress');
      var barSpan = bar ? bar.querySelector('span') : null;
      var total = Math.max(1, expiry - Math.floor(Date.now()/1000));
      if (bar && barSpan){ barSpan.style.width = '100%'; bar.setAttribute('aria-valuenow','100'); }
    function tick(){
      var now = Math.floor(Date.now()/1000);
      var s = expiry - now;
        if (s <= 0){
          cd.textContent = '00:00';
          if (bar && barSpan){ barSpan.style.width = '0%'; bar.setAttribute('aria-valuenow','0'); }
        // disable payment form
        var form = document.querySelector('form[action*="/pago"]');
        if (form){
          form.querySelectorAll('input,select,button').forEach(function(el){ el.disabled = true; });
          var warn = document.createElement('div');
          warn.style.cssText = "margin:8px 0 0 0; padding:8px 12px; border:1px solid #fecaca; background:#fee2e2; color:#b91c1c; border-radius:10px;";
          warn.textContent = "Tiempo agotado: la reserva fue liberada si no se registró el pago.";
          form.parentElement.insertBefore(warn, form);
        }
        return;
      }
      var m = Math.floor(s/60), ss = s%60;
        cd.textContent = fmt(m)+':'+fmt(ss);
        if (bar && barSpan){
          var pct = (s/total)*100;
          barSpan.style.width = pct+'%';
          bar.setAttribute('aria-valuenow', Math.round(pct));
        }
        setTimeout(tick, 1000);
      }
      tick();
  }
})();
</script>
