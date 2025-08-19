<!doctype html>
<html lang="es">
<head>
<?php require_once APP_PATH . "/Models/Setting.php"; $__s = new Setting(); $__favicon = $__s->get('site_favicon',''); $__logo = $__s->get('site_logo',''); $__title = $__s->get('site_title','Compraturifa | {{page}}');
// Derivar nombre de sección si no se pasa $page_title desde el controlador
$__uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$__page = isset($page_title) && $page_title ? (string)$page_title : (function($u){
  if ($u === '/' || $u === '' || $u === false) return 'Home';
  $trim = trim($u, '/');
  if ($trim === '') return 'Home';
  $seg = explode('/', $trim);
  $first = $seg[0] ?? '';
  if ($first === 'rifa' && isset($seg[1]) && ctype_digit($seg[1])) return 'Rifa';
  $first = str_replace(['-','_'], ' ', $first);
  return mb_convert_case($first, MB_CASE_TITLE, 'UTF-8');
})($__uri);

// Reemplazo de placeholder en el título del sitio
function __replace_ph($tpl, $page){
  $had = false;
  $phs = ['{{page}}','{page}','[page]','%s','"Aqui"','"Aquí"','\'Aqui\'','\'Aquí\'','Aqui','Aquí'];
  foreach ($phs as $ph) {
    if (stripos($tpl, $ph) !== false) { $tpl = str_ireplace($ph, $page, $tpl); $had = true; }
  }
  return [$tpl, $had];
}
list($__fullTitle, $__hadPh) = __replace_ph($__title, $__page);
if (!$__hadPh && $__page && $__page !== 'Home') { $__fullTitle = $__title . ' | ' . $__page; }
// Para la marca del header, usar la base sin el placeholder
list($__brandBase, $__tmpHad) = __replace_ph($__title, '');
$__brandBase = trim(preg_replace('/(\s*[|\-–]\s*)+$/u', '', $__brandBase));
if ($__brandBase === '') { $__brandBase = $__title; }
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= Utils::e($__fullTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/app.css?v=1755260745">
<link rel="stylesheet" href="/css/husky-theme.css?v=1">
<?php if ($__favicon): ?><link rel="icon" href="/file/site/<?=Utils::e($__favicon)?>"><?php endif; ?>
</head>
<body>
  <header class="site-header">
  <div class="container nav-inner">
    <a class="brand" href="/">
<?php if ($__logo): ?>
  <img src="/file/site/<?=Utils::e($__logo)?>" alt="Logo" style="height:48px">
<?php else: ?>
  <?= Utils::e($__brandBase) ?>
<?php endif; ?>
</a>
    <div class="nav-actions">
            <a href="/mis-boletos" class="btn btn-primary">Mis boletos</a>
    </div>
  </div>
</header>
<div class="container">
    <?= $content ?>
  </div>
  
  
  <footer class="site-footer">
    <div class="container foot-inner">
      <div class="foot-brand">Compraturifa</div>
      <nav class="foot-links">
        <a href="/">Inicio</a>
        <a href="/mis-boletos">Mis boletos</a>
        <a href="/admin">Admin</a>
      </nav>
      <div class="copy">© <?=date('Y')?> Compraturifa — Todos los derechos reservados.</div>
    </div>
  </footer>


</body>
</html>