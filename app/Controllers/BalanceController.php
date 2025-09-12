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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = (float)($_POST['amount'] ?? 0);
            if ($amount > 0) {
                try {
                    $res = $api->addBalance($amount);
                    if (isset($res['status']) && $res['status'] === 'success') {
                        $message = 'Bakiye yüklendi';
                    } else {
                        $error = 'Bakiye yükleme başarısız';
                    }
                } catch (\Throwable $e) {
                    $error = 'Hata: '.$e->getMessage();
                }
            } else {
                $error = 'Geçerli bir tutar giriniz';
            }
        }
        $balance = $api->getBalance();
        require __DIR__.'/../Views/balance/topup.php';
    }
}

