<?php
namespace App\Controllers;

use App\Helpers\DB;

class ReportsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ header('Location: /login'); exit; } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }
    private function isUser(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='user'; }

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

        // Build a condition mapping calls.group_id to local groups via (groups.id OR groups.api_group_id)
        $where = 'c.start BETWEEN ? AND ?';
        $types = 'ss';
        $params = [$from, $to];

        // Summary per group (map to local group id)
        $sql = "SELECT cg.id AS group_id, COUNT(*) calls, SUM(c.duration) duration, SUM(c.billsec) billsec, SUM(c.cost_api) cost_api, SUM(c.amount_charged) revenue, (SUM(c.amount_charged)-SUM(c.cost_api)) profit
                FROM calls c
                LEFT JOIN groups cg ON (cg.id=c.group_id OR cg.api_group_id=c.group_id)
                WHERE $where";
        $types = 'ss'; $params = [$from, $to];
        if ($groupFilter) { $sql .= ' AND cg.id=?'; $types .= 'i'; $params[] = $groupFilter; }
        $sql .= ' GROUP BY cg.id ORDER BY profit DESC';
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
        $sql2 = "SELECT DATE(c.start) d, SUM(c.cost_api) cost, SUM(c.amount_charged) revenue
                  FROM calls c
                  LEFT JOIN groups cg ON (cg.id=c.group_id OR cg.api_group_id=c.group_id)
                  WHERE $where";
        $types2 = 'ss'; $params2 = [$from, $to];
        if ($groupFilter) { $sql2 .= ' AND cg.id=?'; $types2 .= 'i'; $params2[] = $groupFilter; }
        $sql2 .= ' GROUP BY DATE(c.start) ORDER BY d';
        $stmt = $db->prepare($sql2);
        $stmt->bind_param($types2, ...$params2);
        $stmt->execute();
        $trend = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Agent stats (Call Plane-like) from DB: join users by exten=src
        $sql3 = "SELECT
                    u.login AS user_login,
                    cg.name AS group_name,
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
                  LEFT JOIN groups cg ON (cg.id=c.group_id OR cg.api_group_id=c.group_id)
                  WHERE $where";
        $types3 = 'ss'; $params3 = [$from, $to];
        if ($groupFilter) { $sql3 .= ' AND cg.id=?'; $types3 .= 'i'; $params3[] = $groupFilter; }
        $sql3 .= ' GROUP BY u.login, cg.name, u.exten ORDER BY cost DESC';
        $stmt = $db->prepare($sql3);
        $stmt->bind_param($types3, ...$params3);
        $stmt->execute();
        $agentStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Group agents by group name and filter for group admin
        $agentsByGroup = [];
        $userGroupName = '';
        if (!$this->isSuper()) {
            $stmt = $db->prepare('SELECT name FROM groups WHERE id=?');
            $stmt->bind_param('i', $groupFilter);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($r) $userGroupName = $r['name'];
        }
        foreach ($agentStats as $agent) {
            $group = $agent['group_name'];
            if ($this->isSuper() || $group === $userGroupName) {
                $agentsByGroup[$group][] = $agent;
            }
        }

        // Disposition distribution for chart
        $sql4 = "SELECT UPPER(c.disposition) d, COUNT(*) n
                  FROM calls c
                  LEFT JOIN groups cg ON (cg.id=c.group_id OR cg.api_group_id=c.group_id)
                  WHERE $where";
        $types4 = 'ss'; $params4 = [$from, $to];
        if ($groupFilter) { $sql4 .= ' AND cg.id=?'; $types4 .= 'i'; $params4[] = $groupFilter; }
        $sql4 .= ' GROUP BY UPPER(c.disposition)';
        $stmt = $db->prepare($sql4);
        $stmt->bind_param($types4, ...$params4);
        $stmt->execute();
        $dispRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Group admin KPIs: spent and remaining balance
        $spent = 0.0; $callsCount = 0; $answerCount = 0; $noAnswerCount = 0; $balance = null;
        if (!$this->isSuper() && $groupFilter) {
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount_charged),0) s, COUNT(*) c, SUM(CASE WHEN UPPER(disposition) IN ('ANSWERED','ANSWER') THEN 1 ELSE 0 END) a FROM calls c WHERE c.group_id=? AND c.start BETWEEN ? AND ?");
            $stmt->bind_param('iss', $groupFilter, $from, $to);
            $stmt->execute(); $r=$stmt->get_result()->fetch_assoc(); $stmt->close();
            if ($r){ $spent=(float)$r['s']; $callsCount=(int)$r['c']; $answerCount=(int)$r['a']; $noAnswerCount=$callsCount-$answerCount; }
            $stmt=$db->prepare('SELECT balance FROM groups WHERE id=?');
            $stmt->bind_param('i',$groupFilter); $stmt->execute(); $r=$stmt->get_result()->fetch_assoc(); $stmt->close();
            if ($r){ $balance=(float)$r['balance']; }
        }

        // Flatten agents for JavaScript
        $allAgents = [];
        foreach ($agentsByGroup as $group => $agents) {
            $allAgents = array_merge($allAgents, $agents);
        }

        $role = $_SESSION['user']['role'] ?? '';
        require __DIR__.'/../Views/reports/index.php';
    }
}
