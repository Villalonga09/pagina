<?php
abstract class BaseModel {
  protected PDO $db;
  public function __construct() {
    $pdo = Database::connect();
    if (!$pdo) {
      include APP_PATH . "/Views/errors/db_down.php";
      exit;
    }
    $this->db = $pdo;
  }
}