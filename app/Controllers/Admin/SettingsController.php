<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Core/Auth.php";
require_once APP_PATH . "/Core/CSRF.php";
require_once APP_PATH . "/Core/Database.php";
require_once APP_PATH . "/Models/Setting.php";
require_once APP_PATH . "/Core/Utils.php";

class SettingsController extends Controller {
  public function index() {
    if (!Auth::check()) { $this->redirect('/admin/login'); }
    $s = new Setting();
    $bcv  = $s->get('bcv_rate', BCV_DEFAULT_RATE);

    // Slides (JSON en settings.hero_slides). Convertir a textarea "Titulo | Descripción"
    $slidesRaw = $s->get('hero_slides', '');
    $slidesTextarea = '';
    if ($slidesRaw) {
      $slides = json_decode($slidesRaw, true);
      if (is_array($slides)) {
        $lines = [];
        foreach ($slides as $sl) {
          $title = $sl['title'] ?? '';
          $desc  = $sl['desc'] ?? '';
          if ($title || $desc) $lines[] = trim($title) . " | " . trim($desc);
        }
        $slidesTextarea = implode("\n", $lines);
      } else {
        $slidesTextarea = (string)$slidesRaw;
      }
    }

    // Branding y título
    $logo    = $s->get('site_logo', '');
    $favicon = $s->get('site_favicon', '');
    $siteTitle = $s->get('site_title', 'Compraturifa | {{page}}');

    View::render('admin/settings/index.php', [
      'bcv' => $bcv,
      'slides' => $slidesTextarea,
      'logo' => $logo,
      'favicon' => $favicon,
      'site_title' => $siteTitle,
    ], 'layouts/admin.php');
  }

  public function save() {
    if (!Auth::check()) { $this->redirect('/admin/login'); }
    CSRF::validate();
    $s = new Setting();

    // BCV
    $bcv = floatval($_POST['bcv_rate'] ?? BCV_DEFAULT_RATE);
    $s->set('bcv_rate', (string)$bcv);

    // Título del sitio
    $title = trim($_POST['site_title'] ?? ''); if ($title === '') { $title = 'Compraturifa | {{page}}'; }
    $s->set('site_title', $title);

    // Slides
    $slidesInput = trim($_POST['hero_slides'] ?? '');
    if ($slidesInput !== '') {
      if ($slidesInput[0] === '[') {
        $tmp = json_decode($slidesInput, true);
        if (is_array($tmp)) {
          $s->set('hero_slides', json_encode($tmp, JSON_UNESCAPED_UNICODE));
        }
      } else {
        $lines = preg_split('/\r?\n/', $slidesInput);
        $slides = [];
        foreach ($lines as $ln) {
          $parts = array_map('trim', explode('|', $ln, 2));
          if (!empty($parts[0]) || !empty($parts[1] ?? '')) {
            $slides[] = ['title' => $parts[0] ?? '', 'desc' => $parts[1] ?? ''];
          }
        }
        if (!empty($slides)) {
          $s->set('hero_slides', json_encode($slides, JSON_UNESCAPED_UNICODE));
        }
      }
    }

    // Subidas: logo y favicon
    $this->handleUpload('site_logo', ['image/png','image/jpeg','image/webp','image/svg+xml']);
    $this->handleUpload('site_favicon', ['image/x-icon','image/vnd.microsoft.icon','image/png','image/svg+xml','image/jpeg']);

    $this->redirect('/admin/ajustes');
  }

  private function handleUpload($key, $allowedMimes) {
    if (!isset($_FILES[$key]) || !is_array($_FILES[$key])) return;
    if (($_FILES[$key]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return;
    $tmp = $_FILES[$key]['tmp_name'];
    if (!is_uploaded_file($tmp)) return;
    $mime = mime_content_type($tmp);
    if (!$mime || !in_array($mime, $allowedMimes)) { app_log("UPLOAD REJECTED ($key): mime=$mime"); return; }

    $map = [
      'image/png' => '.png',
      'image/jpeg' => '.jpg',
      'image/webp' => '.webp',
      'image/svg+xml' => '.svg',
      'image/x-icon' => '.ico',
      'image/vnd.microsoft.icon' => '.ico',
    ];
    $ext = isset($map[$mime]) ? $map[$mime] : '.bin';
    $base = $key . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . $ext;
    $dest = rtrim(SITE_UPLOADS_PATH, '/').'/'.$base;
    if (!is_dir(SITE_UPLOADS_PATH)) @mkdir(SITE_UPLOADS_PATH, 0775, true);
    if (@move_uploaded_file($tmp, $dest)) {
      $s = new Setting();
      $prev = $s->get($key, '');
      if ($prev) {
        $old = rtrim(SITE_UPLOADS_PATH,'/').'/'.basename($prev);
        if (is_file($old)) @unlink($old);
      }
      $s->set($key, basename($base));
    }
  }

  public function updateRate() {
    if (!Auth::check()) { $this->redirect('/admin/login'); }
    CSRF::validate();
    $s = new Setting();
    $rate = (new Setting())->getBcvRateAuto();
    $s->set('bcv_rate', (string)$rate);
    $pdo = Database::connect();
    $st = $pdo->prepare('UPDATE raffles SET price_ves = price_usd * ?');
    $st->execute([ (float)$rate ]);
    $this->redirect('/admin/ajustes');
  }
}
