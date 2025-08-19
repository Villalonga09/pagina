<?php
require_once APP_PATH . "/Models/BaseModel.php";
class Setting extends BaseModel {
  public function getBcvRateAuto() {
    $current = floatval($this->get('bcv_rate', BCV_DEFAULT_RATE));
    try {
      $ctx = stream_context_create(['http'=>['timeout'=>BCV_HTTP_TIMEOUT]]);
      $json = @file_get_contents(BCV_SOURCE_URL, false, $ctx);
      if ($json) {
        $data = json_decode($json, true);
        $candidates = [];
        if (is_array($data)) {
          foreach (['promedio','precio','valor','rate','oficial','usd','value'] as $k) {
            if (isset($data[$k])) $candidates[] = $data[$k];
          }
        }
        foreach ($candidates as $cand) {
          if (is_numeric($cand)) { $rate = floatval($cand); break; }
          if (is_string($cand)) {
            $tmp = str_replace(['.',','], ['','.' ], $cand);
            if (is_numeric($tmp)) { $rate = floatval($tmp); break; }
          }
        }
        if (!empty($rate) && $rate > 0) {
          if (abs($rate - $current) > 0.0001) $this->set('bcv_rate', (string)$rate);
          return $rate;
        }
      }
    } catch (Throwable $e) {}
    return $current;
  }

  public function get($key, $default=null) {
    $st = $this->db->prepare("SELECT svalue FROM settings WHERE skey=?");
    $st->execute([$key]);
    $r = $st->fetch();
    return $r ? $r['svalue'] : $default;
  }
  public function set($key, $value) {
    $st = $this->db->prepare("INSERT INTO settings(skey,svalue) VALUES(?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)");
    $st->execute([$key,$value]);
  }
}