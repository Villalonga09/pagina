<h2>Ajustes</h2>
<div class="card">
  <form action="/admin/ajustes" method="post" enctype="multipart/form-data">
    <?= CSRF::field() ?>

    <div class="form-group">
      <label>Título del sitio</label>
      <input class="input" name="site_title" value="<?= Utils::e($site_title ?? 'Rifas') ?>" maxlength="120" />
      <p class="small">Se usa en el &lt;title&gt; del navegador y como texto del logo si no hay imagen.</p>
    </div>
    
    <div class="form-group">
      <label>Tasa BCV</label>
      <input class="input" name="bcv_rate" value="<?=Utils::e($bcv)?>" />
      <p class="small">Puedes configurar SMTP en config/.env para envío de correos.</p>
      <p><a class="btn btn-ghost" href="/admin/ajustes/actualizar-bcv">Actualizar desde API</a></p>
    </div>
    <hr style="border-color:var(--border);opacity:.5;margin:16px 0">
    <div class="form-group">
      <label>Slides del Hero (uno por línea: <em>Título | Descripción</em>)</label>
      <textarea class="input" name="hero_slides" rows="6" placeholder="Ejemplo:
Gana premios reales | Compra tus números y participa en minutos.
Paga en USD o Bs | Recibimos pagos al cambio BCV de forma segura.
Resultados transparentes | Publicamos los ganadores y tu comprobante al instante."><?= Utils::e($slides ?? '') ?></textarea>
      <p class="small">También puedes pegar un JSON con objetos {title, desc}. El hero rota cada 10 segundos.</p>
    </div>

    <hr style="border-color:var(--border);opacity:.5;margin:16px 0">
    <div class="grid" style="grid-template-columns:1fr 1fr;gap:16px">
      <div class="form-group">
        <label>Logo del header</label>
        <?php if (!empty($logo)): ?>
          <div class="small">Actual:</div>
          <div style="margin:.5rem 0"><img src="/file/site/<?=Utils::e($logo)?>" alt="logo" style="max-height:60px"></div>
        <?php else: ?>
          <div class="small">No hay logo configurado. Se mostrará el texto del sitio.</div>
        <?php endif; ?>
        <input class="input" type="file" name="site_logo" accept=".png,.jpg,.jpeg,.webp,.svg">
        <p class="small">Recomendado: PNG transparente (alto ~60px).</p>
      </div>
      <div class="form-group">
        <label>Favicon</label>
        <?php if (!empty($favicon)): ?>
          <div class="small">Actual:</div>
          <div style="margin:.5rem 0"><img src="/file/site/<?=Utils::e($favicon)?>" alt="favicon" style="height:32px;width:32px"></div>
        <?php else: ?>
          <div class="small">No hay favicon configurado.</div>
        <?php endif; ?>
        <input class="input" type="file" name="site_favicon" accept=".ico,.png,.jpg,.jpeg,.svg">
        <p class="small">Aceptado: .ico, .png, .jpg, .svg</p>
      </div>
    </div>
    
    <p><button class="btn btn-primary">Guardar</button></p>
  </form>
</div>
