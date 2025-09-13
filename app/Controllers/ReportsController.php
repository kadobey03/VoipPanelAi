<?php
namespace App\Controllers;

use App\Helpers\DB;

class ReportsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ header('Location: /login'); exit; } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }
    private function isUser(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='user'; }
    private function isGroupMember(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='groupmember'; }

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

        // Groupmember için session'da agent_id yoksa veritabanından çek
        if ($this->isGroupMember() && !isset($_SESSION['user']['agent_id'])) {
            $userId = (int)$_SESSION['user']['id'];
            $stmt = $db->prepare('SELECT agent_id FROM users WHERE id=?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            if ($r && $r['agent_id']) {
                $_SESSION['user']['agent_id'] = $r['agent_id'];
            }
            $stmt->close();
        }

        // Build base condition
        $baseWhere = 'c.start BETWEEN ? AND ?';
        $baseTypes = 'ss';
        $baseParams = [$from, $to];

        // Add groupmember src filter to base
        if ($this->isGroupMember()) {
            $userExten = $_SESSION['user']['exten'] ?? '';
            if (!empty($userExten)) {
                $baseWhere .= ' AND c.src=?';
                $baseTypes .= 's';
                $baseParams[] = $userExten;
            }
        }

        // Summary per group (map to local group id)
        $summaryWhere = $baseWhere;
        $summaryTypes = $baseTypes;
        $summaryParams = $baseParams;
        if ($groupFilter && !$this->isGroupMember()) {
            $summaryWhere .= ' AND cg.id=?';
            $summaryTypes .= 'i';
            $summaryParams[] = $groupFilter;
        }
        $sql = "SELECT cg.id AS group_id, COUNT(*) calls, SUM(c.duration) duration, SUM(c.billsec) billsec, SUM(c.cost_api) cost_api, SUM(c.amount_charged) revenue, (SUM(c.amount_charged)-SUM(c.cost_api)) profit
                FROM calls c
                LEFT JOIN groups cg ON (cg.id=c.group_id OR cg.api_group_id=c.group_id)
                WHERE $summaryWhere";
        $sql .= ' GROUP BY cg.id ORDER BY profit DESC';
        $stmt = $db->prepare($sql);
        $stmt->bind_param($summaryTypes, ...$summaryParams);
        $stmt->execute();
        $summary = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Map group names
        $groups = [];
        $res = $db->query('SELECT id, name FROM groups');
        while($row=$res->fetch_assoc()){ $groups[(int)$row['id']] = $row['name']; }

        // Daily trend (local aggregated)
        $trendWhere = $baseWhere;
        $trendTypes = $baseTypes;
        $trendParams = $baseParams;
        if ($groupFilter && !$this->isGroupMember()) {
            $trendWhere .= ' AND cg.id=?';
            $trendTypes .= 'i';
            $trendParams[] = $groupFilter;
        }
        $sql2 = "SELECT DATE(c.start) d, SUM(c.cost_api) cost, SUM(c.amount_charged) revenue
                 FROM calls c
                 LEFT JOIN groups cg ON (cg.id=c.group_id OR cg.api_group_id=c.group_id)
                 WHERE $trendWhere";
        $sql2 .= ' GROUP BY DATE(c.start) ORDER BY d';
        $stmt = $db->prepare($sql2);
        $stmt->bind_param($trendTypes, ...$trendParams);
        $stmt->execute();
        $trend = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Agent stats (Call Plane-like) from DB: join users by exten=src
        $agentWhere = $baseWhere;
        $agentTypes = $baseTypes;
        $agentParams = $baseParams;
        if ($groupFilter && !$this->isGroupMember()) {
            $agentWhere .= ' AND cg.id=?';
            $agentTypes .= 'i';
            $agentParams[] = $groupFilter;
        }
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
                   WHERE $agentWhere";
        $sql3 .= ' GROUP BY u.login, cg.name, u.exten ORDER BY cost DESC';
        $stmt = $db->prepare($sql3);

        // Debug: Check parameter counts
        error_log("Agent Stats - Types: '$agentTypes' (" . strlen($agentTypes) . " chars), Params: " . count($agentParams));
        error_log("User role: " . ($_SESSION['user']['role'] ?? 'unknown'));
        error_log("Is groupmember: " . ($this->isGroupMember() ? 'yes' : 'no'));

        $stmt->bind_param($agentTypes, ...$agentParams);
        $stmt->execute();
        $agentStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Group agents by group name and filter for group admin
        $agentsByGroup = [];
        $userGroupName = '';
        if (!$this->isSuper() && !$this->isGroupMember()) {
            $stmt = $db->prepare('SELECT name FROM groups WHERE id=?');
            $stmt->bind_param('i', $groupFilter);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($r) $userGroupName = $r['name'];
        }
        foreach ($agentStats as $agent) {
            $group = $agent['group_name'];
            if ($this->isSuper() || (!$this->isGroupMember() && $group === $userGroupName)) {
                $agentsByGroup[$group][] = $agent;
            } elseif ($this->isGroupMember()) {
                // Groupmember sadece kendi agent'ını görür
                $agentsByGroup['Kendi Agentınız'][] = $agent;
            }
        }

        // Disposition distribution for chart
        $dispWhere = $baseWhere;
        $dispTypes = $baseTypes;
        $dispParams = $baseParams;
        if ($groupFilter && !$this->isGroupMember()) {
            $dispWhere .= ' AND cg.id=?';
            $dispTypes .= 'i';
            $dispParams[] = $groupFilter;
        }
        $sql4 = "SELECT UPPER(c.disposition) d, COUNT(*) n
                   FROM calls c
                   LEFT JOIN groups cg ON (cg.id=c.group_id OR cg.api_group_id=c.group_id)
                   WHERE $dispWhere";
        $sql4 .= ' GROUP BY UPPER(c.disposition)';
        $stmt = $db->prepare($sql4);
        $stmt->bind_param($dispTypes, ...$dispParams);
        $stmt->execute();
        $dispRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Group admin KPIs: spent and remaining balance
        $spent = 0.0; $callsCount = 0; $answerCount = 0; $noAnswerCount = 0; $balance = null;
        if (!$this->isSuper() && !$this->isGroupMember() && $groupFilter) {
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
