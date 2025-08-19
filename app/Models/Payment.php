<?php
require_once APP_PATH . "/Models/BaseModel.php";
class Payment extends BaseModel {
  public function create($order_id, $data) {
    $st = $this->db->prepare("INSERT INTO payments(order_id,method,amount_ves,amount_usd,reference,receipt_path,status) VALUES(?,?,?,?,?,?, 'pendiente')");
    $st->execute([$order_id,$data['method'],$data['amount_ves'],$data['amount_usd'],$data['reference'],$data['receipt_path']]);
    return (int)$this->db->lastInsertId();
  }
  public function find($id) {
    $st = $this->db->prepare("SELECT * FROM payments WHERE id=?");
    $st->execute([$id]);
    return $st->fetch();
  }
  public function byOrder($order_id) {
    $st = $this->db->prepare("SELECT * FROM payments WHERE order_id=? ORDER BY created_at DESC");
    $st->execute([$order_id]);
    return $st->fetchAll();
  }
  public function referenceExists($reference) {
    $st = $this->db->prepare("SELECT 1 FROM payments WHERE reference=? LIMIT 1");
    $st->execute([$reference]);
    return (bool)$st->fetchColumn();
  }
  public function setStatus($id,$status,$user_id=null) {
    $st = $this->db->prepare("UPDATE payments SET status=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?");
    $st->execute([$status,$user_id,$id]);
  }
}
