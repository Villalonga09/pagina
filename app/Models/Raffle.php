<?php
require_once APP_PATH . "/Models/BaseModel.php";
class Raffle extends BaseModel {
  public function allActive() {
    $st = $this->db->query("SELECT * FROM raffles WHERE status='activa' ORDER BY created_at DESC");
    return $st->fetchAll();
  }
  public function find($id) {
    $st = $this->db->prepare("SELECT * FROM raffles WHERE id = ?");
    $st->execute([$id]);
    return $st->fetch();
  }
  public function create($data) {
    $st = $this->db->prepare("INSERT INTO raffles(title,description,prize,price_usd,price_ves,total_tickets,banner_path,status,starts_at,ends_at) VALUES(?,?,?,?,?,?,?,?,?,?)");
    $st->execute([
      $data['title'],$data['description'],$data['prize'],$data['price_usd'],$data['price_ves'],$data['total_tickets'],$data['banner_path']??null,$data['status']??'activa',$data['starts_at']??null,$data['ends_at']??null
    ]);
    return (int)$this->db->lastInsertId();
  }
  
  public function update($id, $data) {
    // Build dynamic SET so banner_path is only updated when provided
    $set = "title=?, description=?, prize=?, price_usd=?, price_ves=?, total_tickets=?, status=?, starts_at=?, ends_at=?";
    $params = [
      $data['title'],
      $data['description'],
      $data['prize'],
      $data['price_usd'],
      $data['price_ves'],
      $data['total_tickets'],
      $data['status'],
      $data['starts_at'],
      $data['ends_at']
    ];
    if (array_key_exists('banner_path', $data) && $data['banner_path']) {
      $set .= ", banner_path=?";
      $params[] = $data['banner_path'];
    }
    $params[] = $id;
    $sql = "UPDATE raffles SET " . $set . " WHERE id=?";
    $st = $this->db->prepare($sql);
    $st->execute($params);
  }

  public function delete($id) {
    $st = $this->db->prepare("DELETE FROM raffles WHERE id=?");
    $st->execute([$id]);
  }
  public function incrementSold($raffle_id,$qty) {
    $st = $this->db->prepare("UPDATE raffles SET sold_tickets = sold_tickets + ? WHERE id=?");
    $st->execute([$qty,$raffle_id]);
  }
}