<?php
namespace App\Controllers;

use App\Helpers\ApiClient;
use App\Helpers\DB;

class DashboardController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); }

        $user    = $_SESSION['user'];
        $isSuper = ($user['role'] ?? '') === 'superadmin';
        $isGroupMember = ($user['role'] ?? '') === 'groupmember';
        $gid     = (int)($user['group_id'] ?? 0);

        // ── API Balance (superadmin only) ────────────────────────────────
        $api = new ApiClient();
        $balanceValue = null;
        if ($isSuper) {
            try {
                $balanceData  = $api->getBalance();
                $balanceValue = is_array($balanceData) && isset($balanceData['balance']) ? (float)$balanceData['balance'] : null;
            } catch (\Throwable $e) { $balanceValue = null; }
        }

        $db = DB::conn();

        // ── Groups total balance ─────────────────────────────────────────
        $groupsTotal = 0.0;
        if ($res = $db->query('SELECT COALESCE(SUM(balance),0) AS total FROM groups')) {
            $groupsTotal = (float)$res->fetch_assoc()['total'];
        }

        // ── Own group balance (non-super) ────────────────────────────────
        $ownGroupBalance = null; $ownGroupName = null;
        if (!$isSuper && $gid) {
            $stmt = $db->prepare('SELECT balance, name FROM groups WHERE id=?');
            $stmt->bind_param('i', $gid); $stmt->execute(); $rs = $stmt->get_result();
            if ($g = $rs->fetch_assoc()) { $ownGroupBalance = (float)$g['balance']; $ownGroupName = $g['name']; }
            $stmt->close();
        }

        // ── Today stats ──────────────────────────────────────────────────
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd   = date('Y-m-d') . ' 23:59:59';
        if ($isSuper) {
            $stmt = $db->prepare('SELECT COUNT(*) total, SUM(IF(UPPER(disposition)="ANSWERED",1,0)) answered, COALESCE(SUM(billsec),0) billsec, COALESCE(SUM(amount_charged),0) charged, COALESCE(SUM(cost_api),0) cost FROM calls WHERE start BETWEEN ? AND ?');
            $stmt->bind_param('ss', $todayStart, $todayEnd);
        } else {
            $stmt = $db->prepare('SELECT COUNT(*) total, SUM(IF(UPPER(disposition)="ANSWERED",1,0)) answered, COALESCE(SUM(billsec),0) billsec, COALESCE(SUM(amount_charged),0) charged, COALESCE(SUM(cost_api),0) cost FROM calls WHERE start BETWEEN ? AND ? AND group_id=?');
            $stmt->bind_param('ssi', $todayStart, $todayEnd, $gid);
        }
        $stmt->execute(); $todayRow = $stmt->get_result()->fetch_assoc(); $stmt->close();
        $todayTotal    = (int)($todayRow['total']    ?? 0);
        $todayAnswered = (int)($todayRow['answered'] ?? 0);
        $todayBillsec  = (int)($todayRow['billsec']  ?? 0);
        $todayCharged  = (float)($todayRow['charged'] ?? 0);
        $todayCost     = (float)($todayRow['cost']    ?? 0);
        $todayAnswerRate = $todayTotal > 0 ? round($todayAnswered / $todayTotal * 100, 1) : 0;

        // ── Weekly stats ─────────────────────────────────────────────────
        $from = date('Y-m-d H:i:s', time() - 7*24*3600);
        if ($isSuper) {
            $stmt = $db->prepare('SELECT COALESCE(SUM(amount_charged),0) rev, COALESCE(SUM(cost_api),0) cost FROM calls WHERE start >= ?');
            $stmt->bind_param('s', $from);
        } else {
            $stmt = $db->prepare('SELECT COALESCE(SUM(amount_charged),0) rev, COALESCE(SUM(cost_api),0) cost FROM calls WHERE start >= ? AND group_id=?');
            $stmt->bind_param('si', $from, $gid);
        }
        $stmt->execute(); $profitRow = $stmt->get_result()->fetch_assoc(); $stmt->close();
        $weeklyRevenue = (float)($profitRow['rev']  ?? 0);
        $weeklyCost    = (float)($profitRow['cost'] ?? 0);
        $weeklyProfit  = $weeklyRevenue - $weeklyCost;

        // ── 7-day chart data ─────────────────────────────────────────────
        $chartLabels = []; $chartRevenue = []; $chartCost = []; $chartCalls = [];
        if ($isSuper) {
            $stmt = $db->prepare('SELECT DATE(start) d, SUM(amount_charged) rev, SUM(cost_api) cost, COUNT(*) c FROM calls WHERE start >= ? GROUP BY DATE(start) ORDER BY d');
            $stmt->bind_param('s', $from);
        } else {
            $stmt = $db->prepare('SELECT DATE(start) d, SUM(amount_charged) rev, SUM(cost_api) cost, COUNT(*) c FROM calls WHERE start >= ? AND group_id=? GROUP BY DATE(start) ORDER BY d');
            $stmt->bind_param('si', $from, $gid);
        }
        $stmt->execute(); $rs = $stmt->get_result();
        while ($row = $rs->fetch_assoc()) {
            $chartLabels[]  = date('d.m', strtotime($row['d']));
            $chartRevenue[] = (float)$row['rev'];
            $chartCost[]    = (float)$row['cost'];
            $chartCalls[]   = (int)$row['c'];
        }
        $stmt->close();

        // ── Disposition donut data (today) ───────────────────────────────
        if ($isSuper) {
            $stmt = $db->prepare('SELECT disposition, COUNT(*) c FROM calls WHERE start BETWEEN ? AND ? GROUP BY disposition');
            $stmt->bind_param('ss', $todayStart, $todayEnd);
        } else {
            $stmt = $db->prepare('SELECT disposition, COUNT(*) c FROM calls WHERE start BETWEEN ? AND ? AND group_id=? GROUP BY disposition');
            $stmt->bind_param('ssi', $todayStart, $todayEnd, $gid);
        }
        $stmt->execute(); $rs = $stmt->get_result();
        $dispositionData = ['ANSWERED' => 0, 'NO ANSWER' => 0, 'BUSY' => 0, 'FAILED' => 0];
        while ($row = $rs->fetch_assoc()) {
            $d = strtoupper($row['disposition']);
            if ($d === 'NO_ANSWER') $d = 'NO ANSWER';
            if (isset($dispositionData[$d])) $dispositionData[$d] = (int)$row['c'];
            else $dispositionData['NO ANSWER'] += (int)$row['c'];
        }
        $stmt->close();

        // ── Active agents count ──────────────────────────────────────────
        $activeAgents = 0; $totalAgents = 0;
        if ($isSuper) {
            if ($r = $db->query('SELECT COUNT(*) c FROM agents WHERE active=1')) $activeAgents = (int)$r->fetch_assoc()['c'];
            if ($r = $db->query('SELECT COUNT(*) c FROM agents'))               $totalAgents  = (int)$r->fetch_assoc()['c'];
        } else {
            // agents tablosunda group_id yok, group_name ile filtre yapıyoruz
            $groupName = '';
            $stmtG = $db->prepare('SELECT name FROM groups WHERE id=?');
            $stmtG->bind_param('i', $gid); $stmtG->execute();
            $rowG = $stmtG->get_result()->fetch_assoc(); $stmtG->close();
            if ($rowG) $groupName = $rowG['name'];

            if (!empty($groupName)) {
                $stmt = $db->prepare('SELECT COUNT(*) c FROM agents WHERE active=1 AND group_name=?'); $stmt->bind_param('s', $groupName); $stmt->execute(); $activeAgents = (int)$stmt->get_result()->fetch_assoc()['c']; $stmt->close();
                $stmt = $db->prepare('SELECT COUNT(*) c FROM agents WHERE group_name=?');              $stmt->bind_param('s', $groupName); $stmt->execute(); $totalAgents  = (int)$stmt->get_result()->fetch_assoc()['c']; $stmt->close();
            }
        }

        // ── Last 10 calls ────────────────────────────────────────────────
        if ($isSuper) {
            $res = $db->query('SELECT c.call_id, c.src, c.dst, c.start, c.duration, c.billsec, c.disposition, c.amount_charged, g.name group_name FROM calls c LEFT JOIN groups g ON g.id=c.group_id ORDER BY c.start DESC LIMIT 10');
        } else {
            $res = null;
            $stmt = $db->prepare('SELECT c.call_id, c.src, c.dst, c.start, c.duration, c.billsec, c.disposition, c.amount_charged, g.name group_name FROM calls c LEFT JOIN groups g ON g.id=c.group_id WHERE c.group_id=? ORDER BY c.start DESC LIMIT 10');
            $stmt->bind_param('i', $gid); $stmt->execute(); $res = $stmt->get_result(); $stmt->close();
        }
        $recentCalls = [];
        if ($res) { while ($row = $res->fetch_assoc()) { $recentCalls[] = $row; } }

        // ── User/group counts (superadmin) ───────────────────────────────
        $totalUsers = 0; $totalGroups = 0;
        if ($isSuper) {
            if ($r = $db->query('SELECT COUNT(*) c FROM users'))  $totalUsers  = (int)$r->fetch_assoc()['c'];
            if ($r = $db->query('SELECT COUNT(*) c FROM groups')) $totalGroups = (int)$r->fetch_assoc()['c'];
        }

        $diff = $isSuper && $balanceValue !== null ? $balanceValue - $groupsTotal : null;

        require __DIR__.'/../Views/dashboard.php';
    }
}
