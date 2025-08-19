<?php
require_once APP_PATH . "/Models/BaseModel.php";

class Order extends BaseModel {

  /** Crea una orden, inserta items y reserva boletos. Devuelve ID de la orden. */
  public function create(array $data, array $items) {
    $this->db->beginTransaction();
    try {
      // Insertar orden
      $st = $this->db->prepare("
        INSERT INTO orders (code, buyer_name, buyer_email, buyer_phone, buyer_dni, total_usd, total_ves, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')
      ");
      $st->execute([
        $data['code'],
        $data['buyer_name'],
        $data['buyer_email'],
        $data['buyer_phone'],
        $data['buyer_dni'],
        $data['total_usd'],
        $data['total_ves'],
      ]);
      $order_id = (int)$this->db->lastInsertId();

      // Insertar items y reservar tickets
      $ist = $this->db->prepare("
        INSERT INTO order_items (order_id, raffle_id, ticket_id, price_usd, price_ves)
        VALUES (?, ?, ?, ?, ?)
      ");
      $ust = $this->db->prepare("UPDATE tickets SET status='reservado', order_id=? WHERE id=? AND status='disponible'");
      foreach ($items as $it) {
        $ist->execute([$order_id, $it['raffle_id'], $it['ticket_id'], $it['price_usd'], $it['price_ves']]);
        $ust->execute([$order_id, $it['ticket_id']]);
        if ($ust->rowCount() === 0) {
          throw new Exception("Ticket no disponible");
        }
      }

      $this->db->commit();
      return $order_id;

    } catch (Throwable $e) {
      $this->db->rollBack();
      throw $e;
    }
  }

  /** Busca orden por código */
  public function findByCode(string $code) {
    $st = $this->db->prepare("SELECT * FROM orders WHERE code=?");
    $st->execute([$code]);
    return $st->fetch();
  }

  /** Busca orden por id */
  public function find($id) {
    $st = $this->db->prepare("SELECT * FROM orders WHERE id=?");
    $st->execute([$id]);
    return $st->fetch();
  }

  /** Cambia estado de la orden */
  public function setStatus($order_id, $status) {
    $st = $this->db->prepare("UPDATE orders SET status=? WHERE id=?");
    $st->execute([$status, $order_id]);
  }

  /** Items de la orden con rifa y ticket */
  public function items($order_id) {
    $st = $this->db->prepare("
      SELECT oi.*, r.title, t.number
      FROM order_items oi
      JOIN raffles r ON r.id = oi.raffle_id
      JOIN tickets t ON t.id = oi.ticket_id
      WHERE oi.order_id=?
      ORDER BY oi.id ASC
    ");
    $st->execute([$order_id]);
    return $st->fetchAll();
  }
}
