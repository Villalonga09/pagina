<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Core/Auth.php";
require_once APP_PATH . "/Core/CSRF.php";
require_once APP_PATH . "/Core/Mailer.php";
require_once APP_PATH . "/Models/Payment.php";
require_once APP_PATH . "/Models/Order.php";
require_once APP_PATH . "/Models/Ticket.php";
require_once APP_PATH . "/Models/Raffle.php";
require_once APP_PATH . "/Models/Activity.php";

class PaymentsController extends Controller {
  public function approve($id) {
    if (!Auth::check()) { $this->redirect('/admin/login'); }
    CSRF::validate();
    $p = new Payment(); $o = new Order(); $t = new Ticket(); $r = new Raffle(); $a = new Activity();
    $pay = $p->find($id);
    if (!$pay) die("Pago no encontrado");
    $order = $o->find($pay['order_id']);
    $pdo = Database::connect();
    $pdo->beginTransaction();
    try {
      $p->setStatus($id,'aprobado', Auth::user()['id'] ?? null);
      $o->setStatus($order['id'],'pagado');
      // mark tickets (already attached via order_items)
      $st = $pdo->prepare("UPDATE tickets t JOIN order_items oi ON oi.ticket_id=t.id SET t.status='vendido' WHERE oi.order_id=?");
      $st->execute([$order['id']]);
      // increment raffles sold_tickets
      $st2 = $pdo->prepare("UPDATE raffles r SET r.sold_tickets = (SELECT COUNT(*) FROM tickets t WHERE t.raffle_id=r.id AND t.status='vendido') WHERE r.id IN (SELECT DISTINCT raffle_id FROM order_items WHERE order_id=?)");
      $st2->execute([$order['id']]);
      $pdo->commit();
    } catch (Throwable $e) {
      $pdo->rollBack();
      die("No se pudo aprobar: " . $e->getMessage());
    }
    $a->log(Auth::user()['id'] ?? null,'approve_payment','payment',$id,"Pago aprobado para orden {$order['code']}",[]);
    // Send email (link al comprobante; adjunto solo si vendor/dompdf presente)
    $link = Utils::url('/orden/' . $order['code'] . '/comprobante');
    $html = "<p>Hola {$order['buyer_name']},</p><p>Tu pago ha sido aprobado.</p><p>Comprobante: <a href='{$link}'>{$link}</a></p>";
    Mailer::send($order['buyer_email'], "Pago aprobado - Orden {$order['code']}", $html);
    $this->redirect('/admin/ordenes/' . $order['id']);
  }

  public function reject($id) {
    if (!Auth::check()) { $this->redirect('/admin/login'); }
    CSRF::validate();
    $p = new Payment(); $o = new Order(); $t = new Ticket(); $a = new Activity();
    $pay = $p->find($id);
    if (!$pay) die("Pago no encontrado");
    $order = $o->find($pay['order_id']);
    $pdo = Database::connect();
    $pdo->beginTransaction();
    try {
      $p->setStatus($id,'rechazado', Auth::user()['id'] ?? null);
      $o->setStatus($order['id'],'rechazado');
      // release tickets
      $t->releaseByOrder($order['id']);
      $pdo->commit();
    } catch (Throwable $e) {
      $pdo->rollBack();
      die("No se pudo rechazar");
    }
    $a->log(Auth::user()['id'] ?? null,'reject_payment','payment',$id,"Pago rechazado para orden {$order['code']}",[]);
    $this->redirect('/admin/ordenes/' . $order['id']);
  }
}
