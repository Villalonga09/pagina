<?php
class Auth {
  public static function start() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  }
  public static function check() {
    self::start();
    return isset($_SESSION['user']);
  }
  public static function user() {
    return $_SESSION['user'] ?? null;
  }
  public static function login($user) {
    self::start();
    $_SESSION['user'] = $user;
  }
  public static function logout() {
    self::start();
    unset($_SESSION['user']);
  }
}