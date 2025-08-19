<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Core/Auth.php";
require_once APP_PATH . "/Models/Order.php";
require_once APP_PATH . "/Models/Payment.php";

class OrdersController extends Controller {
  public function index() {
    if (!Auth::check()) { $this->redirect('/admin/login'); }
    $pdo = Database::connect();
    $rows = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
    View::render('admin/orders/index.php', ['rows'=>$rows], 'layouts/admin.php');
  }
  public function show($id) {
    if (!Auth::check()) { $this->redirect('/admin/login'); }
    $o = new Order();
    $order = $o->find($id);
    $items = $o->items($id);
    $payments = (new Payment())->byOrder($id);
    View::render('admin/orders/show.php', ['order'=>$order,'items'=>$items,'payments'=>$payments], 'layouts/admin.php');
  }
}