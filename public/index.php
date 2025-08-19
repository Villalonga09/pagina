<?php
require_once __DIR__ . '/../config/config.php';
require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Core/Router.php';
require_once APP_PATH . '/Core/View.php';
require_once APP_PATH . '/Core/Auth.php';
require_once APP_PATH . '/Core/CSRF.php';
require_once APP_PATH . '/Core/Utils.php';

$pdo = Database::connect();
if (!$pdo) { include APP_PATH . '/Views/errors/db_down.php'; exit; }

$router = new Router();

// Public
$router->get('/', ['HomeController','index']);
$router->get('/rifa/{id}', ['RaffleController','show']);
$router->post('/orden', ['OrderController','create']);
$router->get('/orden/{code}', ['OrderController','show']);
$router->post('/orden/{code}/pago', ['OrderController','uploadPayment']);
$router->get('/orden/{code}/comprobante', ['OrderController','receiptHtml']);
$router->get('/orden/{code}/comprobante.pdf', ['OrderController','receiptPdf']);
$router->get('/mis-boletos', ['OrderController','myTickets']);

// Serve receipt files securely
$router->get('/file/receipt/{name}', ['FileController','receipt']);
$router->get('/file/site/{name}', ['FileController','site']);

// Admin
if (!empty($_ENV['ADMIN_SETUP_TOKEN'])) {
  $router->get('/admin/seed-admin', ['AuthController','seedAdmin']);
}
$router->get('/admin/login', ['AuthController','loginForm']);
$router->post('/admin/login', ['AuthController','login']);
$router->post('/admin/logout', ['AuthController','logout']);
$router->get('/admin', ['DashboardController','index']);
$router->get('/admin/rifas', ['RafflesController','index']);
$router->get('/admin/rifas/crear', ['RafflesController','createForm']);
$router->post('/admin/rifas', ['RafflesController','create']);
$router->get('/admin/rifas/{id}/editar', ['RafflesController','editForm']);
$router->post('/admin/rifas/{id}', ['RafflesController','update']);
$router->post('/admin/rifas/{id}/eliminar', ['RafflesController','delete']);
$router->get('/admin/ordenes', ['OrdersController','index']);
$router->get('/admin/ordenes/{id}', ['OrdersController','show']);
$router->post('/admin/pagos/{id}/aprobar', ['PaymentsController','approve']);
$router->post('/admin/pagos/{id}/rechazar', ['PaymentsController','reject']);
$router->get('/admin/reportes', ['ReportsController','index']);
$router->get('/admin/ajustes', ['SettingsController','index']);
$router->post('/admin/ajustes', ['SettingsController','save']);
$router->get('/admin/ajustes/actualizar-bcv', ['SettingsController','updateRate']);
$router->dispatch();