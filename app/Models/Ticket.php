<?php
require_once APP_PATH . "/Models/BaseModel.php";
class Ticket extends BaseModel {
  /** Libera reservas (tickets 'reservado' con orden 'pendiente' > N minutos) */
  public function releaseExpiredReservations(int $minutes = 15): int {
    $pdo = Database::connect();
    $pdo->beginTransaction();
    try {
      // Buscar órdenes pendientes antiguas
      $st = $pdo->prepare("SELECT DISTINCT o.id
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        JOIN tickets t ON t.id = oi.ticket_id
        WHERE o.status='pendiente' AND t.status='reservado' AND o.created_at < (NOW() - INTERVAL ? MINUTE)");
      $st->execute([$minutes]);
      $orders = $st->fetchAll(PDO::FETCH_COLUMN);
      $released = 0;
      foreach ($orders as $oid) {
        // Liberar tickets asociados a la orden
        $pdo->prepare("UPDATE tickets t JOIN order_items oi ON oi.ticket_id=t.id SET t.status='disponible', t.order_id=NULL WHERE oi.order_id=?")->execute([$oid]);
        // Eliminar items (o podríamos mantenerlos como histórico, pero preferimos limpiar)
        $pdo->prepare("DELETE FROM order_items WHERE order_id=?")->execute([$oid]);
        // Marcar orden como rechazada por timeout
        $pdo->prepare("UPDATE orders SET status='rechazado' WHERE id=? AND status='pendiente'")->execute([$oid]);
        $released++;
        // Log de actividad
        if (class_exists('Activity')) {
          (new Activity())->log(null, 'timeout', 'order', (int)$oid, 'Orden rechazada por timeout y boletos liberados', ['minutes'=>$minutes]);
        }
      }
      $pdo->commit();
      return (int)$released;
    } catch (Throwable $e) {
      $pdo->rollBack();
      return 0;
    }
  }

  public function allByRaffle($raffle_id) {
    $st = $this->db->prepare("SELECT * FROM tickets WHERE raffle_id=? ORDER BY number");
    $st->execute([$raffle_id]);
    return $st->fetchAll();
  }

  public function createBatch($raffle_id, $total) {
    $this->db->beginTransaction();
    try {
      $st = $this->db->prepare("INSERT INTO tickets(raffle_id, number, status) VALUES(?,?, 'disponible')");
      for ($i=1; $i<=$total; $i++) {
        $st->execute([$raffle_id,$i]);
      }
      $this->db->commit();
    } catch (Throwable $e) {
      $this->db->rollBack();
      throw $e;
    }
  }
  public function availableByRaffle($raffle_id) {
    $st = $this->db->prepare("SELECT * FROM tickets WHERE raffle_id=? AND status='disponible' ORDER BY number");
    $st->execute([$raffle_id]);
    return $st->fetchAll();
  }
  public function reserveTickets($ids) {
    if (empty($ids)) return 0;
    $in = implode(',', array_fill(0, count($ids), '?'));
    $this->db->beginTransaction();
    try {
      // Reserve only if currently available
      $st = $this->db->prepare("UPDATE tickets SET status='reservado' WHERE id IN ($in) AND status='disponible'");
      $st->execute($ids);
      $count = $st->rowCount();
      $this->db->commit();
      return $count;
    } catch (Throwable $e) {
      $this->db->rollBack();
      throw $e;
    }
  }
  public function markSoldForOrder($order_id) {
    $st = $this->db->prepare("UPDATE tickets SET status='vendido', order_id=? WHERE order_id=?");
    $st->execute([$order_id,$order_id]);
  }
  public function attachToOrder($ticket_ids, $order_id) {
    if (empty($ticket_ids)) return;
    $in = implode(',', array_fill(0, count($ticket_ids), '?'));
    $params = $ticket_ids;
    array_unshift($params, $order_id);
    $st = $this->db->prepare("UPDATE tickets SET order_id=? WHERE id IN ($in)");
    $st->execute($params);
  }
  public function ticketsByOrder($order_id) {
    $st = $this->db->prepare("SELECT t.*, r.title FROM tickets t JOIN raffles r ON r.id=t.raffle_id WHERE t.order_id=?");
    $st->execute([$order_id]);
    return $st->fetchAll();
  }
  public function releaseByOrder($order_id) {
    $st = $this->db->prepare("UPDATE tickets SET status='disponible', order_id=NULL WHERE order_id=?");
    $st->execute([$order_id]);
  }
  public function byRaffleAndIds($raffle_id, $ids) {
    if (empty($ids)) return [];
    $in = implode(',', array_fill(0, count($ids), '?'));
    $params = $ids;
    array_unshift($params, $raffle_id);
    $st = $this->db->prepare("SELECT * FROM tickets WHERE raffle_id=? AND id IN ($in) AND status='disponible'");
    $st->execute($params);
    return $st->fetchAll();
  }
}