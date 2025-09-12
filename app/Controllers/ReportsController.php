<?php
namespace App\Controllers;

use App\Helpers\DB;

class ReportsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ header('Location: /login'); exit; } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    public function index(){
        $this->requireAuth();
        $db = DB::conn();
        $from = $_GET['from'] ?? date('Y-m-d 00:00:00', strtotime('-6 days'));
        $to   = $_GET['to']   ?? date('Y-m-d 23:59:59');
        $groupFilter = null;
        if ($this->isSuper()) {
            $groupFilter = isset($_GET['group_id']) && $_GET['group_id'] !== '' ? (int)$_GET['group_id'] : null;
        } else {
            $groupFilter = (int)($_SESSION['user']['group_id'] ?? 0);
        }

        $where = 'start BETWEEN ? AND ?';
        $types = 'ss';
        $params = [$from, $to];
        if ($groupFilter) { $where .= ' AND group_id=?'; $types.='i'; $params[] = $groupFilter; }

        // Summary per group
        $sql = "SELECT group_id, COUNT(*) calls, SUM(duration) duration, SUM(billsec) billsec, SUM(cost_api) cost_api, SUM(amount_charged) revenue, (SUM(amount_charged)-SUM(cost_api)) profit FROM calls WHERE $where GROUP BY group_id ORDER BY profit DESC";
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $summary = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Map group names
        $groups = [];
        $res = $db->query('SELECT id, name FROM groups');
        while($row=$res->fetch_assoc()){ $groups[(int)$row['id']] = $row['name']; }

        // Daily trend
        $sql2 = "SELECT DATE(start) d, SUM(cost_api) cost, SUM(amount_charged) revenue FROM calls WHERE $where GROUP BY DATE(start) ORDER BY d";
        $stmt = $db->prepare($sql2);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $trend = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require __DIR__.'/../Views/reports/index.php';
    }
}

