<?php
namespace App\Controllers;

use App\Helpers\ApiClient;
use App\Helpers\DB;

class AgentsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    public function index(){
        $this->requireAuth();

        // Auto-migrate: add hidden column if not exists
        $db = DB::conn();
        try {
            $db->query('SELECT hidden FROM users LIMIT 1');
        } catch (\Throwable $e) {
            $db->query('ALTER TABLE users ADD COLUMN hidden TINYINT(1) DEFAULT 0');
        }
        // Auto-migrate: create agents table if not exists
        $db->query('CREATE TABLE IF NOT EXISTS agents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            exten VARCHAR(20) NOT NULL UNIQUE,
            user_login VARCHAR(50),
            group_name VARCHAR(100),
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )');

        $isSuper = $this->isSuper();
        $userGroupName = '';
        if (!$isSuper) {
            $groupId = (int)($_SESSION['user']['group_id'] ?? 0);
            $stmt = $db->prepare('SELECT name FROM groups WHERE id=?');
            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($r) $userGroupName = $r['name'];
        }
        $api = new ApiClient();
        $error = null;
        // Get all agents from DB
        $agentsDb = $db->query('SELECT exten, user_login, group_name, active FROM agents')->fetch_all(MYSQLI_ASSOC);

        // Get status from API
        $agentsApi = [];
        try { $agentsApi = $api->getAgentsStatus(); } catch (\Throwable $e) { $error = $e->getMessage(); }

        // Merge status
        $statusMap = [];
        foreach ($agentsApi as $a) {
            $statusMap[$a['exten']] = $a;
        }
        // Filter for group admin
        if (!$isSuper) {
            $agentsDb = array_filter($agentsDb, function($a) use ($userGroupName) {
                return ($a['active'] ?? 1) == 1 && $a['group_name'] === $userGroupName;
            });
        }

        $agents = [];
        foreach ($agentsDb as $agent) {
            $exten = $agent['exten'];
            if (isset($statusMap[$exten])) {
                $agents[] = array_merge($agent, $statusMap[$exten]);
            } else {
                // No status, but keep
                $agents[] = $agent;
            }
        }

        // Get hidden status from DB
        $hiddenMap = [];
        $result = $db->query('SELECT exten, hidden FROM users');
        while ($row = $result->fetch_assoc()) {
            $hiddenMap[$row['exten']] = $row['hidden'];
        }
        foreach ($agents as &$agent) {
            $agent['hidden'] = $hiddenMap[$agent['exten']] ?? 0;
        }
        // Filter out hidden agents
        $agents = array_filter($agents, function($agent) {
            return !$agent['hidden'];
        });

        $agentsByGroup = [];
        foreach ($agents as $agent) {
            $group = $agent['group'] ?? '';
            if ($isSuper || $group === $userGroupName) {
                $agentsByGroup[$group][] = $agent;
            }
        }
        require __DIR__.'/../Views/agents/index.php';
    }

    public function toggleHidden() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            die('Yetkisiz');
        }
        $exten = $_POST['exten'] ?? '';
        if (!$exten) {
            die('Geçersiz');
        }
        $db = DB::conn();
        $stmt = $db->prepare('UPDATE users SET hidden = 1 - hidden WHERE exten=?');
        $stmt->bind_param('s', $exten);
        $stmt->execute();
        $stmt->close();
        header('Location: ' . \App\Helpers\Url::to('/agents'));
    }

    public function syncAgents() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            die('Yetkisiz');
        }
        $api = new ApiClient();
        $agentsApi = $api->getAgentsStatus();
        $db = DB::conn();
        foreach ($agentsApi as $agent) {
            $exten = $agent['exten'];
            $login = $agent['user_login'] ?? '';
            $group = $agent['group'] ?? '';
            $stmt = $db->prepare('INSERT INTO agents (exten, user_login, group_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE user_login=VALUES(user_login), group_name=VALUES(group_name), updated_at=NOW()');
            $stmt->bind_param('sss', $exten, $login, $group);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: ' . \App\Helpers\Url::to('/agents'));
    }

    public function toggleActive() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            die('Yetkisiz');
        }
        $exten = $_POST['exten'] ?? '';
        if (!$exten) {
            die('Geçersiz');
        }
        $db = DB::conn();
        $stmt = $db->prepare('UPDATE agents SET active = 1 - active WHERE exten=?');
        $stmt->bind_param('s', $exten);
        $stmt->execute();
        $stmt->close();
        header('Location: ' . \App\Helpers\Url::to('/agents'));
    }
}
