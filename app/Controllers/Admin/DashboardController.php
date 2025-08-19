<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Core/Auth.php";
require_once APP_PATH . "/Models/Activity.php";

class DashboardController extends Controller {
  public function index() {
    if (!Auth::check()) { $this->redirect('/admin/login'); }
    $pdo = Database::connect();
    // KPIs
    $totals = $pdo->query("SELECT 
      COALESCE(SUM(CASE WHEN status='pagado' THEN total_usd END),0) as usd,
      COALESCE(SUM(CASE WHEN status='pagado' THEN total_ves END),0) as ves
      FROM orders")->fetch();
    $sold = $pdo->query("SELECT COALESCE(SUM(sold_tickets),0) as sold FROM raffles")->fetch();
    $active = $pdo->query("SELECT COUNT(*) as c FROM raffles WHERE status='activa'")->fetch();
    $activity = (new Activity())->recent(20);
    View::render('admin/dashboard.php', ['totals'=>$totals,'sold'=>$sold['sold'],'active'=>$active['c'],'activity'=>$activity], 'layouts/admin.php');
  }
}