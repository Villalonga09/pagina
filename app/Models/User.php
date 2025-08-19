<?php
require_once APP_PATH . "/Models/BaseModel.php";
class User extends BaseModel {
  public function findByEmail($email) {
    $st = $this->db->prepare("SELECT * FROM users WHERE email = ?");
    $st->execute([$email]);
    return $st->fetch();
  }
  public function create($name,$email,$hash,$role='admin') {
    $st = $this->db->prepare("INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)");
    $st->execute([$name,$email,$hash,$role]);
    return $this->db->lastInsertId();
  }
}