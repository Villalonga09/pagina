<h2>Mis boletos</h2>

<?php if (!empty($justUploaded)): ?>
  <p class="section-subtitle my-sub">Comprobante enviado correctamente. Estos son sus boletos.</p>
<?php else: ?>
  <p class="section-subtitle my-sub">Encuentra tus participaciones ingresando tu Cédula/DNI.</p>
<?php endif; ?>

<?php if (empty($justUploaded)): ?>
  <div class="card">
    <form method="get">
      <div class="form-row">
        <div>
          <label>Cédula/DNI</label>
          <input class="input" name="dni" value="<?= Utils::e($dni ?? '') ?>" required>
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
      <?php foreach ($tickets as $t): $url = Utils::url('/orden/'.$t['order_code']); ?>
        <div class="card" style="margin:12px">
          <div style="margin-bottom:6px"><strong><?= Utils::e($t['title']) ?></strong> — #<?= $t['number'] ?></div>
          <?php $isPending = ($t['order_status'] === 'pendiente'); $hasReceipt = !empty($t['has_receipt']); ?>
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
          <?php
            $rem = (int)($t['remaining_seconds'] ?? 0);
            $init_mm = intdiv($rem, 60); $init_ss = $rem % 60;
            $init_text = sprintf('%02d:%02d', $init_mm, $init_ss);
          ?>
          <?php if ($isPending && !$hasReceipt): ?>
            <div class="small" style="margin:6px 0">
              Tiempo restante: <span class="countdown" data-seconds="<?= $rem ?>"><?= $init_text ?></span>
            </div>
            <p style="margin:8px 0 8px">
              <a class="btn btn-primary" href="/orden/<?= $t['order_code'] ?>#pago" aria-label="Ir a pago" style="margin:6px 0; display:inline-block">Pagar ahora</a>
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
              <a href="/orden/<?= $t['order_code'] ?>/comprobante.pdf"><strong><?= $t['order_code'] ?></strong></a>
            <?php else: ?>
              <strong><?= $t['order_code'] ?></strong>
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
        ?>
          <tr>
            <td><?= Utils::e($p['method']) ?></td>
            <td><span class="badge <?= $stClass ?>"><?= Utils::e($p['status']) ?></span></td>
            <td><?= Utils::e($p['reference']) ?></td>
            <td>
              <?php if (!empty($p['receipt_path'])): ?>
                <img src="/file/receipt/<?= Utils::e($p['receipt_path']) ?>" alt="Comprobante" class="receipt-thumb">
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>

    <script>
    (function(){
      var nodes = document.querySelectorAll('.countdown');
      function fmt(n){ return n<10?('0'+n):n; }
      nodes.forEach(function(el){
        var s = parseInt(el.getAttribute('data-seconds')||'0',10);
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
