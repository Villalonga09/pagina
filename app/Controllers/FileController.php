<?php
require_once APP_PATH . "/Core/Controller.php";
class FileController extends Controller {
  public function site($name) {
    $name = basename($name);
    $path = SITE_UPLOADS_PATH . '/' . $name;
    if (!file_exists($path)) { http_response_code(404); echo "No existe"; return; }
    $mime = mime_content_type($path);
    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="'.basename($path).'"');
    readfile($path);
  }

  public function receipt($name) {
    $path = UPLOADS_PATH . '/' . basename($name);
    if (!file_exists($path)) { http_response_code(404); echo "No existe"; return; }
    $mime = mime_content_type($path);
    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="'.basename($path).'"');
    readfile($path);
  }
}