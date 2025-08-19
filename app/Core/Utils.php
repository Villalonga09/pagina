<?php
class Utils {
  public static function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
  public static function orderCode() {
    $letters = strtoupper(bin2hex(random_bytes(2)));
    $nums = strtoupper(bin2hex(random_bytes(2)));
    return "RMX-" . substr($letters,0,2) . substr($nums,0,4);
  }
  public static function money($n) {
    return number_format((float)$n, 2, ',', '.');
  }
  public static function url($path) {
    return rtrim(APP_URL, '/') . $path;
  }
  public static function clientIp() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
  }
}