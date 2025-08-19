<?php
class CSRF {
  public static function token() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
  }
  public static function field() {
    $t = self::token();
    return '<input type="hidden" name="csrf_token" value="'.$t.'">';
  }
  public static function validate() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $ok = isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
    if (!$ok) {
      http_response_code(419);
      die("CSRF token inválido");
    }
  }
}