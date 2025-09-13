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

        $where = 'c.start BETWEEN ? AND ?';
        $types = 'ss';
        $params = [$from, $to];
        if ($groupFilter) { $where .= ' AND c.group_id=?'; $types.='i'; $params[] = $groupFilter; }

        // Summary per group
        $sql = "SELECT c.group_id, COUNT(*) calls, SUM(c.duration) duration, SUM(c.billsec) billsec, SUM(c.cost_api) cost_api, SUM(c.amount_charged) revenue, (SUM(c.amount_charged)-SUM(c.cost_api)) profit FROM calls c WHERE $where GROUP BY c.group_id ORDER BY profit DESC";
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $summary = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Map group names
        $groups = [];
        $res = $db->query('SELECT id, name FROM groups');
        while($row=$res->fetch_assoc()){ $groups[(int)$row['id']] = $row['name']; }

        // Daily trend (local aggregated)
        $sql2 = "SELECT DATE(c.start) d, SUM(c.cost_api) cost, SUM(c.amount_charged) revenue FROM calls c WHERE $where GROUP BY DATE(c.start) ORDER BY d";
        $stmt = $db->prepare($sql2);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $trend = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Agent stats (Call Plane-like) from DB: join users by exten=src
        $sql3 = "SELECT 
                    u.login AS user_login,
                    g.name AS group_name,
                    u.exten AS voip_exten,
                    COUNT(*) AS calls,
                    SUM(CASE WHEN UPPER(c.disposition) IN ('ANSWERED','ANSWER') THEN 1 ELSE 0 END) AS answer,
                    COUNT(DISTINCT c.dst) AS unique_numbers,
                    SUM(c.duration) AS duration,
                    SUM(c.billsec) AS billsec,
                    (CASE WHEN SUM(c.duration)>0 THEN ROUND(SUM(c.billsec)/SUM(c.duration)*100,2) ELSE 0 END) AS talk_percent,
                    SUM(CASE WHEN UPPER(c.disposition) IN ('ANSWERED','ANSWER') AND c.billsec>180 THEN 1 ELSE 0 END) AS jackpot,
                    COUNT(DISTINCT CASE WHEN UPPER(c.disposition) IN ('ANSWERED','ANSWER') AND c.billsec>180 THEN c.dst END) AS unique_jackpot,
                    0 AS spy_calls, 0 AS spy_duration, 0 AS promt_calls, 0 AS promt_duration, 0 AS echo_calls, 0 AS echo_duration,
                    ROUND(SUM(c.cost_api),6) AS cost
                 FROM calls c 
                 LEFT JOIN users u ON u.exten=c.src
                 LEFT JOIN groups g ON g.id=c.group_id
                 WHERE $where
                 GROUP BY u.login, g.name, u.exten
                 ORDER BY cost DESC";
        $stmt = $db->prepare($sql3);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $agentStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Disposition distribution for chart
        $sql4 = "SELECT UPPER(c.disposition) d, COUNT(*) n FROM calls c WHERE $where GROUP BY UPPER(c.disposition)";
        $stmt = $db->prepare($sql4);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $dispRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require __DIR__.'/../Views/reports/index.php';
    }
}
