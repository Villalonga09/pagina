<?php require_once APP_PATH . "/Models/Setting.php"; $bcvRate = floatval((new Setting())->getBcvRateAuto()); ?>
<h2>Editar rifa</h2>
<div class="card">
  <form action="/admin/rifas/<?= (int)$r['id'] ?>" method="post" enctype="multipart/form-data">
    <?= CSRF::field() ?>
    <label>Título</label><input class="input" name="title" value="<?= Utils::e($r['title']) ?>" required>
    <label>Descripción</label><textarea class="input" name="description"><?= Utils::e($r['description']) ?></textarea>
    <label>Premio</label><input class="input" name="prize" value="<?= Utils::e($r['prize']) ?>" required>
    <div class="form-row">
      <div><label>Precio USD</label><input class="input" name="price_usd" id="priceUsd" type="number" step="0.01" min="0" value="<?= Utils::e($r['price_usd']) ?>" required></div>
      <div><label>Precio VES (referencial)</label><input class="input" name="price_ves" id="priceVes" type="number" step="0.01" min="0" value="<?= Utils::e($r['price_ves']) ?>" required></div>
    </div>
    <div class="form-row">
      <div><label>Total boletos</label><input class="input" name="total_tickets" type="number" min="1" step="1" value="<?= Utils::e($r['total_tickets']) ?>" required></div>
      <div><label>Banner (opcional)</label><input class="input" name="banner" type="file" accept="image/*"></div>
    </div>
    <div class="form-row">
      <div><label>Estado</label><select name="status">
        <option value="activa" <?= $r['status']=='activa'?'selected':'' ?>>Activa</option>
        <option value="borrador" <?= $r['status']=='borrador'?'selected':'' ?>>Borrador</option>
        <option value="finalizada" <?= $r['status']=='finalizada'?'selected':'' ?>>Finalizada</option>
      </select></div>
      <div><label>Fechas</label>
        <input class="input" name="starts_at" value="<?= Utils::e($r['starts_at']) ?>" placeholder="YYYY-MM-DD HH:MM">
        <input class="input" name="ends_at" value="<?= Utils::e($r['ends_at']) ?>" placeholder="YYYY-MM-DD HH:MM"></div>
    </div>
    <input type="hidden" id="bcvRate" value="<?=$bcvRate?>">
    <p><button class="btn btn-primary">Guardar cambios</button></p>
  </form>
</div>


<script>
(function(){
  const usd = document.getElementById('priceUsd');
  const ves = document.getElementById('priceVes');
  const rate = parseFloat(document.getElementById('bcvRate').value || '0') || 0;
  const toNum = (v)=> parseFloat(String(v).replace(',', '.')) || 0;

  function syncVesFromUsd(){
    const u = toNum(usd.value);
    if (!isFinite(u)) return;
    ves.value = (u * rate).toFixed(2);
  }
  usd.addEventListener('input', syncVesFromUsd);
  usd.addEventListener('change', syncVesFromUsd);
})();
</script>
