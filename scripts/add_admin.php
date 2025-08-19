<?php
// Simple one-off script to create/update an admin user
// Usage: php scripts/add_admin.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$email = 'villalonga.2000@gmail.com';
$name  = 'Villalonga';
$pass  = 'Rich@rd932';

$pdo = Database::connect();
if (!$pdo) { echo "DB connection failed\n"; exit(1); }

$hash = password_hash($pass, PASSWORD_BCRYPT, ['cost'=>12]);

$sql = "INSERT INTO users (name,email,password_hash,role)
        VALUES (:name,:email,:hash,'admin')
        ON DUPLICATE KEY UPDATE name=VALUES(name), password_hash=VALUES(password_hash), role='admin'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':name'=>$name, ':email'=>$email, ':hash'=>$hash]);

echo "Admin user ensured: $email\n";
