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
            $balanceValue = is_array($balanceData) && isset($balanceData['balance']) ? $balanceData['balance'] : null;
        } catch (\Throwable $e) {
            $balanceData = ['error' => $e->getMessage()];
            $balanceValue = null;
        }
        require __DIR__.'/../Views/dashboard.php';
    }
}
