<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Core/Auth.php";
require_once APP_PATH . "/Core/CSRF.php";
require_once APP_PATH . "/Core/Database.php";
require_once APP_PATH . "/Core/Utils.php";
require_once APP_PATH . "/Models/Raffle.php";
require_once APP_PATH . "/Models/Ticket.php";
require_once APP_PATH . "/Models/Activity.php";

class RafflesController extends Controller {

  private function requireAuth() {
    if (!Auth::check()) {
      $this->redirect('/admin/login');
    }
  }

  // Listado de rifas (todas)
  public function index() {
    $this->requireAuth();
    $pdo = Database::connect();
    $rows = $pdo->query("SELECT * FROM raffles ORDER BY created_at DESC")->fetchAll();
    View::render('admin/raffles/index.php', ['raffles' => $rows], 'layouts/admin.php');
  }

  // Form crear
  public function createForm() {
    $this->requireAuth();
    View::render('admin/raffles/create.php', [], 'layouts/admin.php');
  }

  // Crear
  public function create() {
    $this->requireAuth();
    CSRF::validate();

    $data = [
      'title'         => $_POST['title'] ?? '',
      'description'   => $_POST['description'] ?? '',
      'prize'         => $_POST['prize'] ?? '',
      'price_usd'     => floatval($_POST['price_usd'] ?? 0),
      'price_ves'     => floatval($_POST['price_ves'] ?? 0),
      'total_tickets' => intval($_POST['total_tickets'] ?? 0),
      'status'        => $_POST['status'] ?? 'activa',
      'starts_at'     => $_POST['starts_at'] ?? null,
      'ends_at'       => $_POST['ends_at'] ?? null,
      'banner_path'   => null,
    ];

    // Banner (opcional)
    if (!empty($_FILES['banner']['name']) && is_uploaded_file($_FILES['banner']['tmp_name'])) {
      $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
      $type = mime_content_type($_FILES['banner']['tmp_name']);
      if (isset($allowed[$type])) {
        $ext   = $allowed[$type];
        $fname = 'banner_' . time() . '.' . $ext;
        $imgDir = PUBLIC_PATH . '/images';
        if (!is_dir($imgDir)) { @mkdir($imgDir, 0775, true); }
        $dest  = $imgDir . '/' . $fname;
        if (move_uploaded_file($_FILES['banner']['tmp_name'], $dest)) {
          $old = $current['banner_path'] ?? null;
          $data['banner_path'] = '/images/' . $fname;
          if ($old && is_string($old)) { $oldPath = PUBLIC_PATH . $old; if (is_file($oldPath)) { @unlink($oldPath); } }
        }
      }
    }

    $rid = (new Raffle())->create($data);
    (new Ticket())->createBatch($rid, $data['total_tickets']);
    (new Activity())->log(Auth::user()['id'] ?? null, 'create', 'raffle', $rid, "Rifa creada {$data['title']}", ['total' => $data['total_tickets']]);
    $this->redirect('/admin/rifas');
  }

  // Form editar
  public function editForm($id) {
    $this->requireAuth();
    $raffle = (new Raffle())->find((int)$id);
    if (!$raffle) { http_response_code(404); echo "Rifa no encontrada"; return; }
    View::render('admin/raffles/edit.php', ['r' => $raffle], 'layouts/admin.php');
  }

  // Actualizar
  public function update($id) {
    $this->requireAuth();
    CSRF::validate();

    
    $current = (new Raffle())->find((int)$id);
$data = [
      'title'         => $_POST['title'] ?? '',
      'description'   => $_POST['description'] ?? '',
      'prize'         => $_POST['prize'] ?? '',
      'price_usd'     => floatval($_POST['price_usd'] ?? 0),
      'price_ves'     => floatval($_POST['price_ves'] ?? 0),
      'total_tickets' => intval($_POST['total_tickets'] ?? 0),
      'status'        => $_POST['status'] ?? 'activa',
      'starts_at'     => $_POST['starts_at'] ?? null,
      'ends_at'       => $_POST['ends_at'] ?? null,
    ];

    // Banner (opcional)
    if (!empty($_FILES['banner']['name']) && is_uploaded_file($_FILES['banner']['tmp_name'])) {
      $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
      $type = mime_content_type($_FILES['banner']['tmp_name']);
      if (isset($allowed[$type])) {
        $ext   = $allowed[$type];
        $fname = 'banner_' . time() . '.' . $ext;
        $imgDir = PUBLIC_PATH . '/images';
        if (!is_dir($imgDir)) { @mkdir($imgDir, 0775, true); }
        $dest  = $imgDir . '/' . $fname;
        if (move_uploaded_file($_FILES['banner']['tmp_name'], $dest)) {
          $old = $current['banner_path'] ?? null;
          $data['banner_path'] = '/images/' . $fname;
          if ($old && is_string($old)) { $oldPath = PUBLIC_PATH . $old; if (is_file($oldPath)) { @unlink($oldPath); } }
        }
      }
    }

    (new Raffle())->update((int)$id, $data);
    (new Activity())->log(Auth::user()['id'] ?? null, 'update', 'raffle', (int)$id, "Rifa actualizada {$data['title']}", []);
    $this->redirect('/admin/rifas');
  }

  // Eliminar
  public function delete($id) {
    $this->requireAuth();
    CSRF::validate();
    (new Raffle())->delete((int)$id);
    (new Activity())->log(Auth::user()['id'] ?? null, 'delete', 'raffle', (int)$id, "Rifa eliminada ID {$id}", []);
    $this->redirect('/admin/rifas');
  }
}
