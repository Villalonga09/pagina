<?php
class Controller {
  protected function view($template, $data = [], $layout = 'layouts/main.php') {
    View::render($template, $data, $layout);
  }
  protected function json($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
  }
  protected function redirect($url) {
    header("Location: $url");
    exit;
  }
}