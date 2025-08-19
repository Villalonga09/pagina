<?php
class Database {
  public static ?PDO $pdo = null;

  public static function connect() {
    if (self::$pdo) return self::$pdo;
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    try {
      self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
      return self::$pdo;
    } catch (Throwable $e) {
      app_log("DB CONNECTION ERROR: " . $e->getMessage());
      return null;
    }
  }
}