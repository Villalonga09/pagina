<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Core/Utils.php";
require_once APP_PATH . "/Core/CSRF.php";
require_once APP_PATH . "/Core/PDF.php";
require_once APP_PATH . "/Core/Database.php"; // <-- Faltaba, usado en show() y myTickets()
require_once APP_PATH . "/Models/Raffle.php";
require_once APP_PATH . "/Models/Ticket.php";
require_once APP_PATH . "/Models/Order.php";
require_once APP_PATH . "/Models/Payment.php";
require_once APP_PATH . "/Models/Setting.php";
require_once APP_PATH . "/Models/Activity.php";

class OrderController extends Controller {

  public function create() {
    CSRF::validate();

    $raffle_id  = (int)($_POST['raffle_id'] ?? 0);
    // Normaliza y deduplica ids de boletos
    $ticket_ids = array_values(array_unique(array_map('intval', $_POST['ticket_ids'] ?? [])));
    if (empty($ticket_ids)) {
      $this->view('public/error.php', ['message' => 'Debe seleccionar al menos un boleto.']);
      return;
    }

    $name  = trim(strip_tags($_POST['buyer_name'] ?? ''));
    $email = strtolower(trim($_POST['buyer_email'] ?? ''));
    $phone = preg_replace('/\D+/', '', $_POST['buyer_phone'] ?? '');
    $dni   = preg_replace('/[^0-9A-Za-z\.\-]/', '', $_POST['buyer_dni'] ?? '');

    if ($name === '') { $this->view('public/error.php', ['message' => 'Nombre es obligatorio']); return; }
    if ($dni  === '') { $this->view('public/error.php', ['message' => 'Cédula/DNI es obligatorio']); return; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $this->view('public/error.php', ['message' => 'Email inválido']);
      return;
    }

    $r = new Raffle(); $t = new Ticket(); $o = new Order(); $s = new Setting(); $a = new Activity();

    $raffle = $r->find($raffle_id);
    if (!$raffle) { $this->redirect('/'); return; }

    // Validar disponibilidad
    $available = $t->byRaffleAndIds($raffle_id, $ticket_ids);
    if (count($available) !== count($ticket_ids)) {
      $this->view('public/error.php', ['message' => 'Algunos boletos ya no están disponibles.']);
      return;
    }

    // Tasa BCV robusta
    $bcv = (float)$s->getBcvRateAuto();
    if ($bcv <= 0) {
      $this->view('public/error.php', ['message' => 'No se pudo obtener la tasa de cambio. Intente más tarde.']);
      return;
    }

    $items = [];
    $total_usd = 0.0;
    $total_ves = 0.0;

    $unit_usd = (float)$raffle['price_usd'];
    foreach ($ticket_ids as $tid) {
      $price_ves = round($unit_usd * $bcv, 2);
      $items[] = [
        'raffle_id' => $raffle_id,
        'ticket_id' => $tid,
        'price_usd' => round($unit_usd, 2),
        'price_ves' => $price_ves,
      ];
      $total_usd += $unit_usd;
      $total_ves += $price_ves;
    }
    $total_usd = round($total_usd, 2);
    $total_ves = round($total_ves, 2);

    $code = Utils::orderCode();

    try {
      $order_id = $o->create([
        'code'       => $code,
        'buyer_name' => $name,
        'buyer_email'=> $email,
        'buyer_phone'=> $phone,
        'buyer_dni'  => $dni,
        'total_usd'  => $total_usd,
        'total_ves'  => $total_ves
      ], $items);

      $a->log(null, 'create', 'order', $order_id, "Orden creada $code", ['email' => $email]);
      $this->redirect('/orden/' . $code);
      return;
    } catch (Throwable $e) {
      // Log interno para diagnóstico sin filtrar detalles al usuario
      (new Activity())->log(null, 'error', 'order', null, 'Error creando orden', ['error' => $e->getMessage()]);
      $this->view('public/error.php', ['message' => 'No fue posible crear la orden. Intente de nuevo.']);
      return;
    }
  }

  public function show($code) {
    // Liberar reservas expiradas automáticamente
    (new Ticket())->releaseExpiredReservations(15);

    $o = new Order();
    $order = $o->findByCode($code);
    if (!$order) { http_response_code(404); echo "Orden no encontrada"; return; }

    $items    = $o->items($order['id']);
    $payments = (new Payment())->byOrder($order['id']);

    // Cálculo de expiración usando la hora de la BD
    $pdo = Database::connect();
    $st = $pdo->prepare("SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(created_at, INTERVAL 15 MINUTE))) FROM orders WHERE id = ?");
    $st->execute([$order['id']]);
    $remaining = (int)$st->fetchColumn();

    $this->view('public/order_show.php', [
      'order'     => $order,
      'items'     => $items,
      'payments'  => $payments,
      'remaining' => $remaining
    ]);
  }

  public function uploadPayment($code) {
    CSRF::validate();

    $o = new Order();
    $order = $o->findByCode($code);
    if (!$order) { $this->view('public/error.php', ['message' => 'Orden inválida']); return; }
    if ($order['status'] !== 'pendiente') {
      $this->view('public/error.php', ['message' => 'La orden ya no acepta pagos']);
      return;
    }

    // Valida método contra lista conocida (ajusta según tus métodos)
    $allowedMethods = ['pago_movil', 'transferencia', 'zelle', 'efectivo'];
    $method = $_POST['method'] ?? 'pago_movil';
    if (!in_array($method, $allowedMethods, true)) { $method = 'pago_movil'; }

    // Referencia obligatoria
    $reference = strtoupper(preg_replace('/\s+/', '', trim($_POST['reference'] ?? '')));
    if ($reference === '') {
      $this->view('public/error.php', ['message' => 'La referencia del pago es obligatoria.']);
      return;
    }

    $p = new Payment();
    if ($p->referenceExists($reference)) {
      $this->view('public/error.php', ['message' => 'Esta referencia ya fue registrada.']);
      return;
    }

    // Recalcular montos desde servidor
    $bcvLive = (float)(new Setting())->getBcvRateAuto();
    if ($bcvLive <= 0) {
      $this->view('public/error.php', ['message' => 'No se pudo obtener la tasa de cambio. Intente más tarde.']);
      return;
    }
    $amount_usd = round((float)$order['total_usd'], 2);
    $amount_ves = round($amount_usd * $bcvLive, 2);

    // Validación de archivo
    if (!isset($_FILES['receipt']) || ($_FILES['receipt']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      $this->view('public/error.php', ['message' => 'Debe adjuntar el comprobante.']);
      return;
    }
    $tmp  = $_FILES['receipt']['tmp_name'];
    $size = (int)($_FILES['receipt']['size'] ?? 0);
    if (!is_uploaded_file($tmp) || $size <= 0) {
      $this->view('public/error.php', ['message' => 'Error subiendo archivo.']);
      return;
    }

    // Detecta MIME real
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $type  = $finfo->file($tmp);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$type]) || $size > 5 * 1024 * 1024) {
      $this->view('public/error.php', ['message' => 'Archivo inválido. Solo jpg/png/webp, máximo 5MB.']);
      return;
    }

    // Guarda archivo con nombre seguro
    if (!is_dir(UPLOADS_PATH)) { @mkdir(UPLOADS_PATH, 0755, true); }
    $ext   = $allowed[$type];
    $safeCode = preg_replace('/[^A-Za-z0-9_\-]/', '', $order['code']);
    $fname = 'order_' . $safeCode . '_' . time() . '.' . $ext;
    $dest  = rtrim(UPLOADS_PATH, '/').'/'.$fname;

    if (!move_uploaded_file($tmp, $dest)) {
      $this->view('public/error.php', ['message' => 'Error subiendo archivo.']);
      return;
    }
    $receipt_path = $fname;

    try {
      $pid = $p->create($order['id'], [
        'method'       => $method,
        'amount_ves'   => $amount_ves,
        'amount_usd'   => $amount_usd,
        'reference'    => $reference,
        'receipt_path' => $receipt_path
      ]);
    } catch (PDOException $e) {
      // Evita dejar archivos huérfanos
      @unlink($dest);

      if ($e->getCode() === '23000') {
        $this->view('public/error.php', ['message' => 'Esta referencia ya fue registrada.']);
        return;
      }
      throw $e;
    }

    (new Activity())->log(null, 'create', 'payment', $pid, "Pago registrado para orden {$order['code']}", ['reference' => $reference]);
    $this->redirect('/mis-boletos?uploaded=1');
  }

  public function receiptHtml($code) {
    $o = new Order();
    $order = $o->findByCode($code);
    if (!$order) { http_response_code(404); echo "Orden no encontrada"; return; }
    $items = $o->items($order['id']);
    $this->view('public/receipt.php', ['order' => $order, 'items' => $items], null);
  }

  public function receiptPdf($code) {
    $o = new Order();
    $order = $o->findByCode($code);
    if (!$order) { http_response_code(404); echo "Orden no encontrada"; return; }
    $items = $o->items($order['id']);
    ob_start();
    include APP_PATH . "/Views/public/receipt_pdf.php";
    $html = ob_get_clean();
    PDF::receipt($html, "comprobante_{$order['code']}.pdf");
  }

  public function myTickets() {
    // Normaliza el DNI igual que en create()
    $justUploaded = isset($_GET['uploaded']);
    $dni = preg_replace('/[^0-9A-Za-z\.\-]/', '', trim($_GET['dni'] ?? ''));
    if ($dni === '') {
      $this->view('public/my_tickets.php', ['tickets' => [], 'dni' => $dni, 'justUploaded' => $justUploaded]);
      return;
    }

    $pdo = Database::connect();
    $sql = "SELECT
              oi.*, r.title, t.number, o.code AS order_code, o.status AS order_status,
              GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(o.created_at, INTERVAL 15 MINUTE))) AS remaining_seconds,
              EXISTS(SELECT 1 FROM payments p WHERE p.order_id = o.id AND p.status IN ('pendiente','aprobado')) AS has_receipt
            FROM order_items oi
            JOIN orders  o ON o.id = oi.order_id
            JOIN raffles r ON r.id = oi.raffle_id
            JOIN tickets t ON t.id = oi.ticket_id
            WHERE o.buyer_dni = ?
            ORDER BY oi.id DESC";
    $st = $pdo->prepare($sql);
    $st->execute([$dni]);
    $tickets = $st->fetchAll();

    if (!$tickets) {
      $this->view('public/my_tickets.php', [
        'tickets'      => [],
        'dni'          => $dni,
        'error'        => 'No se encontraron boletos para esa Cédula/DNI.',
        'justUploaded' => $justUploaded
      ]);
      return;
    }

    $this->view('public/my_tickets.php', ['tickets' => $tickets, 'dni' => $dni, 'justUploaded' => $justUploaded]);
  }

}
