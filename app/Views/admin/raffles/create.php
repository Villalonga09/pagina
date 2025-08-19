<h2>Nueva rifa</h2>
<div class="card">
  <form action="/admin/rifas" method="post" enctype="multipart/form-data">
    <?= CSRF::field() ?>
    <label>Título</label><input class="input" name="title" required>
    <label>Descripción</label><textarea class="input" name="description"></textarea>
    <label>Premio</label><input class="input" name="prize" required>
    <div class="form-row">
      <div><label>Precio USD</label><input class="input" name="price_usd" type="number" step="0.01" required></div>
      <div><label>Precio VES</label><input class="input" name="price_ves" type="number" step="0.01" required></div>
    </div>
    <div class="form-row">
      <div><label>Total boletos</label><input class="input" name="total_tickets" type="number" required></div>
      <div><label>Banner</label><input class="input" name="banner" type="file" accept="image/*"></div>
    </div>
    <div class="form-row">
      <div><label>Estado</label><select name="status"><option value="activa">Activa</option><option value="borrador">Borrador</option><option value="finalizada">Finalizada</option></select></div>
      <div><label>Fechas</label><input class="input" name="starts_at" placeholder="YYYY-MM-DD HH:MM"> <input class="input" name="ends_at" placeholder="YYYY-MM-DD HH:MM"></div>
    </div>
    <p><button class="btn btn-primary">Crear</button></p>
  </form>
</div>