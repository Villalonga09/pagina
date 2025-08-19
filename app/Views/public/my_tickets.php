<h2>Mis boletos</h2>

<?php if (!empty($justUploaded)): ?>
  <p class="section-subtitle my-sub">Comprobante enviado correctamente. Estos son tus boletos.</p>
<?php else: ?>
  <p class="section-subtitle my-sub">Encuentra tus participaciones ingresando tu Cédula/DNI.</p>
<?php endif; ?>

<?php if (empty($justUploaded)): ?>
  <div class="card">
    <form method="get">
      <div class="form-row">
        <div>
          <label for="dni">Cédula/DNI</label>
          <input id="dni" type="text" class="input" name="dni" value="<?= Utils::e($dni ?? '') ?>" required>
        </div>
      </div>
      <p><button class="btn btn-primary">Buscar</button></p>
    </form>
    <?php if (!empty($error)): ?>
      <p class="small" style="color:var(--danger)"><?= Utils::e($error) ?></p>
    <?php endif; ?>
  </div>
<?php elseif (!empty($error)): ?>
  <div class="card">
    <p class="small" style="color:var(--danger)"><?= Utils::e($error) ?></p>
  </div>
<?php endif; ?>

<?php if (!empty($tickets)): ?>
  <div class="grid" style="gap:16px">
    <?php foreach ($tickets as $t): ?>
      <?php
        $orderCode = (string)($t['order_code'] ?? '');
        $orderUrl  = Utils::url('/orden/' . rawurlencode($orderCode));
        $isPending = ($t['order_status'] === 'pendiente');
        $hasReceipt = !empty($t['has_receipt']);

        $rem = (int)($t['remaining_seconds'] ?? 0);
        if ($rem < 0) { $rem = 0; }
        $init_mm = intdiv($rem, 60);
        $init_ss = $rem % 60;
        $init_text = sprintf('%02d:%02d', $init_mm, $init_ss);
      ?>
      <div class="card" style="margin:12px">
        <div style="margin-bottom:6px">
          <strong><?= Utils::e($t['title']) ?></strong> — #<?= Utils::e((string)($t['number'] ?? '')) ?>
        </div>

        <div class="small" style="margin:6px 0">
          Estado:
          <?php if ($t['order_status'] === 'pagado'): ?>
            <span style="color:#16a34a">Pagado</span>
          <?php elseif ($t['order_status'] === 'pendiente'): ?>
            <span style="color:#ca8a04">Pendiente</span>
          <?php else: ?>
            <span style="color:#ef4444">Expirado/Rechazado</span>
          <?php endif; ?>
        </div>

        <?php if ($isPending && !$hasReceipt): ?>
          <div class="small" style="margin:6px 0">
            Tiempo restante: <span class="countdown" data-seconds="<?= $rem ?>"><?= $init_text ?></span>
          </div>
          <p style="margin:8px 0 8px">
            <a class="btn btn-primary" href="<?= Utils::e($orderUrl) ?>#pago" aria-label="Ir a pago" style="margin:6px 0; display:inline-block">Pagar ahora</a>
          </p>
        <?php elseif ($t['order_status'] === 'pagado'): ?>
          <p class="small" style="color:var(--success); margin:6px 0">Pago aprobado.</p>
        <?php elseif ($isPending && $hasReceipt): ?>
          <p class="small" style="color:#ca8a04; margin:6px 0">Pago en revisión.</p>
        <?php else: ?>
          <p class="small" style="color:var(--danger); margin:6px 0">Orden expirada o rechazada.</p>
        <?php endif; ?>

        <p class="small" style="margin-top:8px">Orden:
          <?php if ($hasReceipt): ?>
            <a href="<?= Utils::e($orderUrl) ?>/comprobante.pdf"><strong><?= Utils::e($orderCode) ?></strong></a>
          <?php else: ?>
            <strong><?= Utils::e($orderCode) ?></strong>
          <?php endif; ?>
        </p>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($payments)): ?>
    <h3 class="card-title" style="margin-top:24px">Pagos enviados</h3>
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

        $methodLabel = ($p['method'] === 'pago_movil') ? 'Pago móvil' : (string)$p['method'];
      ?>
        <tr>
          <td><?= Utils::e($methodLabel) ?></td>
          <td><span class="badge <?= Utils::e($stClass) ?>"><?= Utils::e($p['status']) ?></span></td>
          <td><?= Utils::e($p['reference'] ?? '—') ?></td>
          <td>
            <?php if (!empty($p['receipt_path'])): ?>
              <button type="button" class="btn-slim view-receipt" data-img="/file/receipt/<?= Utils::e($p['receipt_path']) ?>">Ver comprobante</button>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>

    <div id="receiptModal" class="modal" aria-hidden="true" role="dialog" aria-label="Comprobante de pago">
      <div id="receiptModalBackdrop" class="modal-backdrop"></div>
      <div class="modal-content">
        <button id="receiptModalClose" type="button" class="modal-close" aria-label="Cerrar">&times;</button>
        <img id="receiptModalImg" src="" alt="Comprobante">
      </div>
    </div>

    <script>
      (function(){
        var modal = document.getElementById('receiptModal');
        var modalImg = document.getElementById('receiptModalImg');
        var modalClose = document.getElementById('receiptModalClose');
        var modalBackdrop = document.getElementById('receiptModalBackdrop');

        document.querySelectorAll('.view-receipt').forEach(function(btn){
          btn.addEventListener('click', function(){
            var src = this.getAttribute('data-img');
            modalImg.src = src || '';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden','false');
          });
        });

        function closeModal(){
          modal.classList.remove('show');
          modal.setAttribute('aria-hidden','true');
          setTimeout(function(){ modalImg.src=''; }, 200);
        }

        if (modalClose) modalClose.addEventListener('click', closeModal);
        if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
        document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeModal(); }});
      })();
    </script>
  <?php endif; ?>

  <script>
    (function(){
      var nodes = document.querySelectorAll('.countdown');
      function fmt(n){ return n<10?('0'+n):n; }
      nodes.forEach(function(el){
        var s = parseInt(el.getAttribute('data-seconds')||'0',10);
        if (isNaN(s) || s < 0) s = 0;
        function tick(){
          if (s<=0){ el.textContent = '00:00'; return; }
          var m = Math.floor(s/60), ss = s%60;
          el.textContent = fmt(m)+':'+fmt(ss);
          s--; setTimeout(tick, 1000);
        }
        tick();
      });
    })();
  </script>
<?php endif; ?>
