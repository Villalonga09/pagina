<?php
class Router {
  private array $routes = [];
  public function get($path, $handler) { $this->routes['GET'][$path] = $handler; }
  public function post($path, $handler) { $this->routes['POST'][$path] = $handler; }

  public function dispatch() {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $handler = $this->match($method, $uri);
    if (!$handler) {
      http_response_code(404);
      echo "404 Not Found";
      return;
    }
    [$controller, $action, $params] = $handler;
    // Resolve controller file: support Admin/ subdir
    $file = APP_PATH . "/Controllers/" . $controller . ".php";
    if (!file_exists($file)) {
      // Try Admin subdir
      $alt = APP_PATH . "/Controllers/Admin/" . $controller . ".php";
      if (file_exists($alt)) {
        $file = $alt;
      } else {
        // Support slash notation e.g. "Admin/AuthController"
        $alt2 = APP_PATH . "/Controllers/" . str_replace('/', DIRECTORY_SEPARATOR, $controller) . ".php";
        if (file_exists($alt2)) $file = $alt2;
      }
    }
    if (!file_exists($file)) {
      http_response_code(500);
      echo "Controller not found: " . htmlentities($controller);
      return;
    }
    require_once $file;
    $ctrl = new $controller();
    call_user_func_array([$ctrl, $action], $params);
  }

  private function match($method, $uri) {
    $routes = $this->routes[$method] ?? [];
    foreach ($routes as $path => $handler) {
      $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
      $pattern = '#^' . rtrim($pattern, '/') . '/?$#';
      if (preg_match($pattern, $uri, $m)) {
        $params = [];
        foreach ($m as $k => $v) if (!is_int($k)) $params[] = $v;
        return [$handler[0], $handler[1], $params];
      }
    }
    return null;
  }
}