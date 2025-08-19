<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Models/Raffle.php";
require_once APP_PATH . "/Models/Setting.php";

class HomeController extends Controller {
  public function index() {
    $model = new Raffle();
    $raffles = $model->allActive();
    $s = new Setting();
    $bcv = floatval($s->getBcvRateAuto());

    $slidesRaw = $s->get('hero_slides', '');
    $slides = json_decode($slidesRaw, true);
    if (!is_array($slides) || empty($slides)) {
      $slides = [
        ['title'=>'Gana premios reales', 'desc'=>'Compra tus números y participa en minutos.'],
        ['title'=>'Paga en USD o Bs', 'desc'=>'Recibimos pagos al cambio BCV de forma segura.'],
        ['title'=>'Resultados transparentes', 'desc'=>'Publicamos los ganadores y tu comprobante al instante.'],
      ];
    }

    $this->view('public/home.php', [
      'raffles' => $raffles,
      'bcv' => $bcv,
      'hero_slides' => $slides,
    ]);
  }
}
