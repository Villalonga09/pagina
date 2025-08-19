<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Models/Raffle.php";
require_once APP_PATH . "/Models/Ticket.php";
require_once APP_PATH . "/Models/Setting.php";

class RaffleController extends Controller {
    public function show($id) {
        $r = new Raffle();
        $t = new Ticket();
        $t->releaseExpiredReservations(15);

        $raffle = $r->find($id);
        if (!$raffle) {
            http_response_code(404);
            echo "Rifa no encontrada";
            return;
        }

        $tickets = $t->allByRaffle($id);
        $bcv = floatval((new Setting())->getBcvRateAuto());

        // Fallback: si no existen tickets (seed no corrió), crear automáticamente
        if (empty($tickets)) {
            (new Ticket())->createBatch($id, (int)$raffle['total_tickets']);
            $tickets = $t->allByRaffle($id);
        }

        $this->view('public/raffle_show.php', [
            'raffle' => $raffle,
            'tickets' => $tickets,
            'bcv' => $bcv,
        ]);
    }
}

