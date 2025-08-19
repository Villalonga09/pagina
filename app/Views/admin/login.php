<!doctype html>
<html><head><meta charset="utf-8"><link rel="stylesheet" href="/css/app.css"><title>Login</title></head>
<body>
  <div class="container" style="max-width:480px">
    <div class="card">
      <h2>Acceso Admin</h2>
      <?php if (!empty($error)): ?><div class="small" style="color:var(--danger)"><?=$error?></div><?php endif; ?>
      <form action="/admin/login" method="post">
        <?= CSRF::field() ?>
        <label>Email</label><input class="input" name="email" type="email" required>
        <label>Contraseña</label><input class="input" name="password" type="password" required>
        <p><button class="btn btn-primary" type="submit">Entrar</button></p>
      </form>
    </div>
  </div>
</body></html>