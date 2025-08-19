<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Core/Utils.php";
require_once APP_PATH . "/Core/CSRF.php";
require_once APP_PATH . "/Core/PDF.php";
require_once APP_PATH . "/Models/Raffle.php";
require_once APP_PATH . "/Models/Ticket.php";
require_once APP_PATH . "/Models/Order.php";
require_once APP_PATH . "/Models/Payment.php";
require_once APP_PATH . "/Models/Setting.php";
require_once APP_PATH . "/Models/Activity.php";

class OrderController extends Controller {
  public function create() {
    CSRF::validate();
    $raffle_id = (int)($_POST['raffle_id'] ?? 0);
    $ticket_ids = array_map('intval', $_POST['ticket_ids'] ?? []);
    if (!$ticket_ids) {
      $this->view('public/error.php', ['message'=>'Debe seleccionar al menos un boleto.']);
      return;
    }
    $name = trim(strip_tags($_POST['buyer_name'] ?? ''));
$email = trim($_POST['buyer_email'] ?? '');
$phone = preg_replace('/\D+/', '', $_POST['buyer_phone'] ?? '');
$dni = preg_replace('/[^0-9A-Za-z\.\-]/', '', $_POST['buyer_dni'] ?? '');
if (!$dni) { $this->view('public/error.php', ['message'=>'Cédula/DNI es obligatorio']); return; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->view('public/error.php', ['message'=>'Email inválido']); return; }
$r = new Raffle(); $t = new Ticket(); $o = new Order(); $s = new Setting(); $a = new Activity();
    $raffle = $r->find($raffle_id);
    if (!$raffle) { $this->redirect('/'); }

    // Validate available
    $available = $t->byRaffleAndIds($raffle_id, $ticket_ids);
    if (count($available) !== count($ticket_ids)) {
      $this->view('public/error.php', ['message'=>'Algunos boletos ya no están disponibles.']);
      return;
    }
    $bcv = floatval($s->getBcvRateAuto());
    $items = [];
    $total_usd = 0; $total_ves = 0;
    foreach ($ticket_ids as $tid) {
      $items[] = [
        'raffle_id' => $raffle_id,
        'ticket_id' => $tid,
        'price_usd' => $raffle['price_usd'],
        'price_ves' => $raffle['price_usd'] * $bcv,
      ];
      $total_usd += $raffle['price_usd'];
      $total_ves += $raffle['price_usd'] * $bcv;
    }
    $code = Utils::orderCode();
    try {
      $order_id = $o->create([
        'code'=>$code, 'buyer_name'=>$name, 'buyer_email'=>$email, 'buyer_phone'=>$phone,
         'buyer_dni'=>$dni, 'total_usd'=>$total_usd, 'total_ves'=>$total_ves
      ], $items);
      (new Activity())->log(null,'create','order',$order_id,"Orden creada $code",['email'=>$email]);
      $this->redirect('/orden/' . $code);
    } catch (Throwable $e) {
      $this->view('public/error.php', ['message'=>'No fue posible crear la orden. Intente de nuevo.']);
  }
}

  public function show($code) {
    // Liberar reservas expiradas automáticamente
    (new Ticket())->releaseExpiredReservations(15);
    $o = new Order();
    $order = $o->findByCode($code);
    if (!$order) { http_response_code(404); echo "Orden no encontrada"; return; }
    $items = $o->items($order['id']);
    $payments = (new Payment())->byOrder($order['id']);
    // Compute remaining seconds till expiration using DB time (avoids PHP/MySQL timezone drift)
    $pdo = Database::connect();
    $st = $pdo->prepare("SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(created_at, INTERVAL 15 MINUTE))) FROM orders WHERE id=?");
    $st->execute([$order['id']]);
    $remaining = (int) $st->fetchColumn();
    $this->view('public/order_show.php', ['order'=>$order,'items'=>$items,'payments'=>$payments,'remaining'=>$remaining]);
  }

  public function uploadPayment($code) {
    CSRF::validate();
    $o = new Order();
    $order = $o->findByCode($code);
    if (!$order) { $this->view('public/error.php',['message'=>'Orden inválida']); return; }
    if ($order['status'] !== 'pendiente') {
      $this->view('public/error.php',['message'=>'La orden ya no acepta pagos']);
      return;
    }

    $method = $_POST['method'] ?? 'pago_movil';
    $amount_ves = floatval($_POST['amount_ves'] ?? 0);
    $amount_usd = floatval($_POST['amount_usd'] ?? 0);
    $reference = trim($_POST['reference'] ?? '');
    $receipt_path = null;

    
    // Override client-provided amounts for security: compute from order + BCV
    $bcvLive = (new Setting())->getBcvRateAuto();
    $amount_usd = floatval($order['total_usd']);
    $amount_ves = floatval($order['total_usd'] * $bcvLive);

    if (empty($_FILES['receipt']['name'] ?? '')) {
      $this->view('public/error.php', ['message' => 'Debe adjuntar el comprobante.']);
      return;
    }

    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $type = mime_content_type($_FILES['receipt']['tmp_name']);
    $size = $_FILES['receipt']['size'];
    if (!isset($allowed[$type]) || $size > 5*1024*1024) {
      $this->view('public/error.php',['message'=>'Archivo inválido. Solo jpg/png/webp, máximo 5MB.']);
      return;
    }
    $ext = $allowed[$type];
    $fname = 'order_' . $order['code'] . '_' . time() . '.' . $ext;
    $dest = UPLOADS_PATH . '/' . $fname;
    if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $dest)) {
      $this->view('public/error.php',['message'=>'Error subiendo archivo.']);
      return;
    }
    $receipt_path = $fname;

    $pid = (new Payment())->create($order['id'], [
      'method'=>$method, 'amount_ves'=>$amount_ves, 'amount_usd'=>$amount_usd,
      'reference'=>$reference, 'receipt_path'=>$receipt_path
    ]);
    (new Activity())->log(null,'create','payment',$pid,"Pago registrado para orden {$order['code']}",['reference'=>$reference]);
    $this->redirect('/orden/' . $code . '?uploaded=1');
  }

  public function receiptHtml($code) {
    $o = new Order();
    $order = $o->findByCode($code);
    if (!$order) { http_response_code(404); echo "Orden no encontrada"; return; }
    $items = $o->items($order['id']);
    $this->view('public/receipt.php', ['order'=>$order,'items'=>$items], null);
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
    $dni = trim($_GET['dni'] ?? '');
    if (!$dni) {
      $this->view('public/my_tickets.php', ['tickets'=>[], 'dni'=>$dni]);
      return;
    }
    $pdo = Database::connect();
    // Fetch all tickets associated to orders for this DNI
    $sql = "SELECT oi.*, r.title, t.number, o.code AS order_code, o.status AS order_status,
                   GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(o.created_at, INTERVAL 15 MINUTE))) AS remaining_seconds
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            JOIN raffles r ON r.id = oi.raffle_id
            JOIN tickets t ON t.id = oi.ticket_id
            WHERE o.buyer_dni = ?
            ORDER BY oi.id DESC";
    $st = $pdo->prepare($sql);
    $st->execute([$dni]);
    $tickets = $st->fetchAll();
    if (!$tickets) {
      $this->view('public/my_tickets.php', ['tickets'=>[], 'dni'=>$dni, 'error'=>'No se encontraron boletos para esa Cédula/DNI.']);
      return;
    }
    $this->view('public/my_tickets.php', ['tickets'=>$tickets, 'dni'=>$dni]);
  }

}
