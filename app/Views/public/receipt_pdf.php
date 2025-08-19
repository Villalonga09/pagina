<?php
ob_start();
include APP_PATH . '/Views/public/receipt.php';
$inner = ob_get_clean();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
</head>
<body><?=$inner?></body>
</html>
