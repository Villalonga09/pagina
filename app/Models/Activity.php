<?php
require_once APP_PATH . "/Models/BaseModel.php";
class Activity extends BaseModel {
  public function log($user_id,$action,$entity_type,$entity_id,$message,$meta=[]) {
    $st = $this->db->prepare("INSERT INTO activity_log(user_id,action,entity_type,entity_id,message,meta_json) VALUES(?,?,?,?,?,?)");
    $st->execute([$user_id,$action,$entity_type,$entity_id,$message,json_encode($meta)]);
  }
  public function recent($limit=20) {
    $st = $this->db->prepare("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT ?");
    $st->bindValue(1, (int)$limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
  }
}