<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin - Rifas</title>
<link rel="stylesheet" href="/css/app.css?v=1755259616?v=1755258034">
</head>
<body>
  <div class="nav">
    <div class="logo"><a href="/admin">🛠️ Admin</a></div>
    <div><a href="/admin/rifas" class="btn">Rifas</a> <a href="/admin/ordenes" class="btn">Órdenes</a> <a href="/admin/reportes" class="btn">Reportes</a> <a href="/admin/ajustes" class="btn">Ajustes</a> <form action="/admin/logout" method="post" style="display:inline"><?php require_once APP_PATH.'/Core/CSRF.php'; echo CSRF::field(); ?><button class="btn">Salir</button></form></div>
  </div>
  <div class="container">
    <?= $content ?>
  </div>
</body>
</html>