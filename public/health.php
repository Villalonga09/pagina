<?php
require_once __DIR__ . '/../config/config.php';
require_once APP_PATH . '/Core/Database.php';
$pdo = Database::connect();
if ($pdo) { http_response_code(200); echo "OK"; }
else { http_response_code(500); echo "DB DOWN"; }