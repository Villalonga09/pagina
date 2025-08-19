<?php
// Carga .env simple
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
  $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
    $_ENV[trim($k)] = trim($v);
  }
}

define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'rifas_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('BCV_SOURCE_URL', $_ENV['BCV_SOURCE_URL'] ?? 'https://ve.dolarapi.com/v1/dolares/oficial');
define('BCV_HTTP_TIMEOUT', 2);
define('BCV_DEFAULT_RATE', floatval($_ENV['BCV_DEFAULT_RATE'] ?? 40));
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('UPLOADS_PATH', STORAGE_PATH . '/uploads/receipts');
define('SITE_UPLOADS_PATH', STORAGE_PATH . '/uploads/site');
define('LOGS_PATH', STORAGE_PATH . '/logs');

if (!is_dir(UPLOADS_PATH)) @mkdir(UPLOADS_PATH, 0775, true);
if (!is_dir(SITE_UPLOADS_PATH)) @mkdir(SITE_UPLOADS_PATH, 0775, true);
if (!is_dir(LOGS_PATH)) @mkdir(LOGS_PATH, 0775, true);

// Error handling
function app_log($message) {
  $f = LOGS_PATH . '/app.log';
  $date = date('Y-m-d H:i:s');
  @file_put_contents($f, "[$date] $message\n", FILE_APPEND);
}

?>