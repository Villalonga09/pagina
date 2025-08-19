<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Core/Auth.php";

class ReportsController extends Controller {
  public function index() {
    if (!Auth::check()) { $this->redirect('/admin/login'); }
    $from = $_GET['fecha_desde'] ?? date('Y-m-01');
    $to = $_GET['fecha_hasta'] ?? date('Y-m-d');
    $raffle_id = $_GET['raffle_id'] ?? null;
    $pdo = Database::connect();
    $sql = "SELECT o.*, oi.raffle_id FROM orders o 
            JOIN order_items oi ON oi.order_id=o.id
            WHERE DATE(o.created_at) BETWEEN ? AND ?";
    $params = [$from, $to];
    if ($raffle_id) { $sql .= " AND oi.raffle_id=?"; $params[] = $raffle_id; }
    $sql .= " ORDER BY o.created_at DESC";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();
    if (($_GET['format'] ?? '') === 'csv') {
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename="reporte.csv"');
      $out = fopen('php://output', 'w');
      fputcsv($out, ['Orden','Email','Total USD','Total VES','Estado','Fecha','Rifa ID']);
      foreach ($rows as $r) fputcsv($out, [$r['code'],$r['buyer_email'],$r['total_usd'],$r['total_ves'],$r['status'],$r['created_at'],$r['raffle_id']]);
      fclose($out);
      return;
    }
    View::render('admin/reports/index.php', ['rows'=>$rows,'from'=>$from,'to'=>$to], 'layouts/admin.php');
  }
}