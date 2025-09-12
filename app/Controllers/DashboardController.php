<?php
namespace App\Controllers;

use App\Helpers\ApiClient;

class DashboardController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        $api = new ApiClient();
        try {
            $balanceData = $api->getBalance();
        } catch (\Throwable $e) {
            $balanceData = ['error' => $e->getMessage()];
        }
        require __DIR__.'/../Views/dashboard.php';
    }
}
