<?php
namespace App\Controllers;

use App\Helpers\ApiClient;
use App\Helpers\DB;

class DashboardController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); }

        $user = $_SESSION['user'];
        $isSuper = ($user['role'] ?? '') === 'superadmin';

        $api = new ApiClient();
        $balanceValue = null; $balanceData = null;
        if ($isSuper) {
            try {
                $balanceData = $api->getBalance();
                $balanceValue = is_array($balanceData) && isset($balanceData['balance']) ? (float)$balanceData['balance'] : null;
            } catch (\Throwable $e) { $balanceData = ['error' => $e->getMessage()]; }
        }

        $db = DB::conn();
        $groupsTotal = 0.0;
        if ($res = $db->query('SELECT COALESCE(SUM(balance),0) AS total FROM groups')) {
            $row = $res->fetch_assoc(); $groupsTotal = (float)$row['total'];
        }

        $ownGroupBalance = null;
        if (!$isSuper) {
            $gid = (int)($user['group_id'] ?? 0);
            if ($gid) {
                $stmt = $db->prepare('SELECT balance FROM groups WHERE id=?');
                $stmt->bind_param('i', $gid); $stmt->execute(); $rs=$stmt->get_result();
                if ($g=$rs->fetch_assoc()) { $ownGroupBalance = (float)$g['balance']; }
                $stmt->close();
            }
        }

        $from = date('Y-m-d H:i:s', time()-7*24*3600);
        if ($isSuper) {
            $stmt = $db->prepare('SELECT COALESCE(SUM(amount_charged),0) AS rev, COALESCE(SUM(cost_api),0) AS cost FROM calls WHERE start >= ?');
            $stmt->bind_param('s', $from);
        } else {
            $stmt = $db->prepare('SELECT COALESCE(SUM(amount_charged),0) AS rev, COALESCE(SUM(cost_api),0) AS cost FROM calls WHERE start >= ? AND group_id=?');
            $gid = (int)($user['group_id'] ?? 0);
            $stmt->bind_param('si', $from, $gid);
        }
        $stmt->execute(); $profitRow=$stmt->get_result()->fetch_assoc(); $stmt->close();
        $weeklyRevenue = (float)($profitRow['rev'] ?? 0); $weeklyCost=(float)($profitRow['cost'] ?? 0);
        $weeklyProfit = $weeklyRevenue - $weeklyCost;

        // Build 7-day chart data
        $chartLabels = []; $chartRevenue = []; $chartCost = []; $chartCalls = [];
        if ($isSuper) {
            $stmt = $db->prepare('SELECT DATE(start) d, SUM(amount_charged) rev, SUM(cost_api) cost, COUNT(*) c FROM calls WHERE start >= ? GROUP BY DATE(start) ORDER BY d');
            $stmt->bind_param('s', $from);
        } else {
            $stmt = $db->prepare('SELECT DATE(start) d, SUM(amount_charged) rev, SUM(cost_api) cost, COUNT(*) c FROM calls WHERE start >= ? AND group_id=? GROUP BY DATE(start) ORDER BY d');
            $gid = (int)($user['group_id'] ?? 0);
            $stmt->bind_param('si', $from, $gid);
        }
        $stmt->execute(); $rs=$stmt->get_result();
        while ($row=$rs->fetch_assoc()) { $chartLabels[]=$row['d']; $chartRevenue[]=(float)$row['rev']; $chartCost[]=(float)$row['cost']; $chartCalls[]=(int)$row['c']; }
        $stmt->close();

        $diff = $isSuper && $balanceValue !== null ? $balanceValue - $groupsTotal : null;

        require __DIR__.'/../Views/dashboard.php';
    }
}
