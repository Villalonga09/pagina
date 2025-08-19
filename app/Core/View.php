<?php
class View {
  public static function render($template, $data = [], $layout = 'layouts/main.php') {
    extract($data);
    ob_start();
    include APP_PATH . "/Views/" . $template;
    $content = ob_get_clean();
    if ($layout) {
      include APP_PATH . "/Views/" . $layout;
    } else {
      echo $content;
    }
  }
  public static function partial($template, $data = []) {
    extract($data);
    include APP_PATH . "/Views/" . $template;
  }
}