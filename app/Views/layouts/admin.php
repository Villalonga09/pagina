<?php
require_once APP_PATH.'/Core/Auth.php';
require_once APP_PATH . "/Models/Setting.php";

$user = Auth::user() ?? ['name' => 'Admin'];

$__s = new Setting();
$__logo = $__s->get('site_logo', '');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Rifas</title>
  <link rel="stylesheet" href="/css/app.css?v=1755259616?v=1755258034">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100">
  <aside
    id="admin-sidebar"
    data-user='<?= htmlspecialchars(json_encode($user), ENT_QUOTES, "UTF-8") ?>'
    data-logo='<?= htmlspecialchars($__logo, ENT_QUOTES, "UTF-8") ?>'
    class="peer group fixed left-0 top-0 h-screen w-20 hover:w-64 bg-gray-900 border-r border-gray-800 transition-all duration-300 overflow-hidden flex flex-col"
  >
    <noscript>
      <nav class="p-4">
        <ul class="space-y-2">
          <li><a href="/admin/rifas" class="block py-2">Rifas</a></li>
          <li><a href="/admin/ordenes" class="block py-2">Órdenes</a></li>
          <li><a href="/admin/logout" class="block py-2">Salir</a></li>
        </ul>
      </nav>
    </noscript>
  </aside>

  <main class="ml-20 p-4 transition-all duration-300 peer-hover:ml-64">
    <div class="container">
      <?= $content ?>
    </div>
  </main>

  <script type="module" src="/js/admin-sidebar.bundle.js"></script>
</body>
</html>
