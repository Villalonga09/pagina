<h2>Orden <?=$order['code']?></h2>
<p class="section-subtitle order-sub">Revisa el estado de tu orden y sube el comprobante de pago si aún está pendiente.</p>
<div class="card">
  <p><strong>Cliente:</strong> <?=Utils::e($order['buyer_name'])?> — <?=Utils::e($order['buyer_email'])?> — <?=Utils::e($order['buyer_phone'])?></p>
  <?php $bcvLive = (new Setting())->getBcvRateAuto(); $bsHoy = $order['total_usd'] * $bcvLive; $amount_usd = floatval($order['total_usd']); $amount_ves = floatval($bsHoy); ?>
<p><strong>Total:</strong> $<?=Utils::money($order['total_usd'])?> / Bs. <?=Utils::money($order['total_ves'])?> <span class="small">(Bs hoy: <?=Utils::money($bsHoy)?>)</span></p>
  <?php
  $remaining = isset($remaining) ? (int)$remaining : 0; ?>
<p><strong>Estado:</strong> <span class="tag"><?=$order['status']?></span></p>
<?php if ($order['status']==='pendiente'): ?>
<div id="countdownBox" style="margin:8px 0 0 0; padding:10px 12px; border:1px solid #fecaca; background:#fee2e2; color:#b91c1c; border-radius:12px;">
  Tiempo restante para pagar: <strong><span id="countdown" data-seconds="<?=$remaining?>"></span></strong>
</div>
<?php endif; ?>
  <p><a class="btn" href="/orden/<?=$order['code']?>/comprobante">Ver comprobante</a> 
     <a class="btn" href="/orden/<?=$order['code']?>/comprobante.pdf">Descargar PDF</a></p>
</div>
<div class="card">
  <h3>Boletos</h3>
  <table class="table">
    <tr><th>Rifa</th><th>#</th><th>QR</th></tr>
    <?php require_once APP_PATH . '/Models/Setting.php'; foreach ($items as $it): $url = Utils::url('/orden/'.$order['code']); ?>
      <tr>
        <td><?=Utils::e($it['title'])?></td>
        <td>#<?=$it['number']?></td>
        <td><img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?=urlencode($url)?>" alt="QR"></td>
      </tr>
    <?php endforeach; ?>
  </table>
  </div>
<?php if (!empty($_GET['uploaded'])): ?>
  <style>
    .alert-success{margin:8px 0 12px;padding:10px 12px;border:1px solid var(--success);background:rgba(52,211,153,.15);color:var(--success);border-radius:12px;}
  </style>
  <div class="alert-success" id="paymentUploadedMsg">¡Comprobante recibido! Está en revisión.</div>
  <script>
    setTimeout(function(){ var el=document.getElementById('paymentUploadedMsg'); if(el){ el.style.display='none'; } }, 5000);
  </script>
<?php endif; ?>
<div class="card">
  <h3>Pago</h3>
  <style>
  /* paydetails styles */
  .paybox{margin-top:8px; padding:10px 12px; border:1px solid #eee; border-radius:12px; background:#fafafa}
  .payrow{display:flex; justify-content:space-between; gap:8px; padding:6px 0; border-bottom:1px dashed #e5e7eb}
  .payrow:last-child{border-bottom:none}
  .paylabel{opacity:.75}
  .payvalue{font-weight:600; cursor:pointer; user-select:all}
  .payactions{display:flex; gap:8px; margin-top:8px}
  .btn-slim{padding:6px 10px; border-radius:10px; border:1px solid #e5e7eb; background:white; cursor:pointer}
  .toast{position:fixed; left:50%; transform:translateX(-50%); bottom:18px; background:#111; color:#fff; padding:8px 12px; border-radius:10px; font-size:12px; opacity:0; transition:opacity .2s}
  .toast.show{opacity:.95}
    .modal{position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:1000}
  .modal.show{display:flex}
  .modal-backdrop{position:absolute; inset:0; background:rgba(0,0,0,.55)}
  .modal-content{position:relative; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.25); max-width:90vw; max-height:90vh; overflow:hidden}
  .modal-content img{display:block; max-width:90vw; max-height:90vh}
  .modal-close{position:absolute; top:6px; right:8px; border:0; background:#fff; width:32px; height:32px; border-radius:9999px; cursor:pointer; box-shadow:0 1px 6px rgba(0,0,0,.2)}
  .linkish{cursor:pointer; text-decoration:underline}
  </style>
  <a id="pago"></a>
<form action="/orden/<?=$order['code']?>/pago" method="post" enctype="multipart/form-data">
    <?= CSRF::field() ?>
<div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; align-items:start">
      <div class="col-left" style="display:grid; gap:12px">
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
        <div><label>Referencia</label><input class="input" name="reference"><p class="small" style="margin-top:6px; opacity:.8">Al enviar el pago, la verificación puede tomar hasta 2 horas.</p></div>
      </div>
      <div class="col-right" style="display:grid; gap:12px">
        <div id="paymentDetails" class="small"></div>
        <div class="uploadbox" style="padding:12px; border:1px dashed #d1d5db; border-radius:12px; background:#f9fafb">
          <div style="font-weight:600; margin-bottom:6px">Comprobante</div>
          <p class="small" style="margin:6px 0 10px; opacity:.8">Adjunta la imagen del pago (JPG, PNG o WEBP, máx. 5MB).</p>
          <input id="receiptInput" type="file" name="receipt" accept="image/*" style="display:none">
          <div style="display:flex; gap:10px; align-items:center">
            <label for="receiptInput" class="btn-slim" style="display:inline-block; padding:8px 12px; border:1px solid #e5e7eb; border-radius:10px; background:white; cursor:pointer">Seleccionar archivo</label>
            <span id="receiptName" class="small" style="opacity:.8">Ningún archivo seleccionado</span>
          </div>
          <div id="receiptPreview" style="margin-top:10px; display:none">
            <img id="receiptImg" src="" alt="Vista previa" style="max-width:100%; max-height:220px; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,.06)">
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
  <?php if (!empty($payments)): ?>
    <h4>Pagos enviados</h4>
    <ul>
    <?php foreach ($payments as $p): ?>
      <li><?=$p['method']?> — <?=$p['status']?> — ref: <?=Utils::e($p['reference'])?> <?php if($p['receipt_path']): ?> — <a href="/file/receipt/<?=Utils::e($p['receipt_path'])?>">ver archivo</a><?php endif; ?></li>
    <?php endforeach; ?>
    </ul>
  <?php endif; ?>
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

  function copyText(t){
    if (!t) return;
    try { navigator.clipboard.writeText(t); showToast('Copiado'); }
    catch(e){
      var ta = document.createElement('textarea');
      ta.value = t; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
      showToast('Copiado');
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
    var html = '<div class="paybox">';
    html += '<div style="font-weight:600; margin-bottom:6px">'+ d.title +'</div>';
    d.rows.forEach(function(r){
      var label = r[0], val = r[1];
      html += '<div class="payrow">'
           +    '<span class="paylabel">'+ label +'</span>'
           +    '<span class="payvalue" data-copy="'+ String(val).replace(/"/g,'&quot;') +'" title="Copiar">'+ val +'</span>'
           +  '</div>';
    });
    html += '</div>';
    detailsNode.innerHTML = html;

    detailsNode.querySelectorAll('.payvalue').forEach(function(el){
      el.addEventListener('click', function(){ copyText(this.getAttribute('data-copy')); });
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
})();
</script>




<script>
(function(){
  function fmt(n){ return n<10?('0'+n):(''+n); }
  var cd = document.getElementById('countdown');
  if (cd){
    var s = parseInt(cd.getAttribute('data-seconds')||'0',10);
    function tick(){
      if (s <= 0){
        cd.textContent = '00:00';
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
      s--; setTimeout(tick, 1000);
    }
    tick();
  }
})();
</script>
