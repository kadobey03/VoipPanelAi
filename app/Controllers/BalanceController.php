<?php
namespace App\Controllers;

use App\Helpers\ApiClient;

class BalanceController {
    private function startSession() { if (session_status() === PHP_SESSION_NONE) session_start(); }
    private function requireAuth() {
        $this->startSession();
        if (!isset($_SESSION['user'])) { header('Location: /login'); exit; }
    }

    public function index() {
        $this->requireAuth();
        $api = new ApiClient();
        $message = null; $error = null;
        $balance = $api->getBalance();
        require __DIR__.'/../Views/balance/topup.php';
    }
}
