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

        // Auto-migrate: add agent_id to users if not exists
        try {
            $db->query('SELECT agent_id FROM users LIMIT 1');
        } catch (\Throwable $e) {
            $db->query('ALTER TABLE users ADD COLUMN agent_id INT AFTER group_id');
            $db->query('ALTER TABLE users MODIFY COLUMN role ENUM(\'superadmin\',\'groupadmin\',\'user\') DEFAULT \'groupadmin\'');
        }

        $isSuper = $this->isSuper();
        $isUser = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'user';
        $isGroupMember = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'groupmember';

        // Groupmember için session'da agent_id yoksa veritabanından çek
        if ($isGroupMember && !isset($_SESSION['user']['agent_id'])) {
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
        $userGroupName = '';
        $userAgentId = 0;
        if (!$isSuper) {
            $groupId = (int)($_SESSION['user']['group_id'] ?? 0);
            $stmt = $db->prepare('SELECT name FROM groups WHERE id=?');
            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($r) $userGroupName = $r['name'];
            if ($isGroupMember) {
                $userAgentId = isset($_SESSION['user']['agent_id']) ? (int)$_SESSION['user']['agent_id'] : 0;
            }
        }
        $api = new ApiClient();
        $error = null;
        // Get all agents from DB
        $agentsDb = $db->query('SELECT exten, user_login, group_name, active FROM agents')->fetch_all(MYSQLI_ASSOC);

        // If no agents in DB, try to sync from API
        if (empty($agentsDb)) {
            $api = new ApiClient();
            $agentsApi = [];
            try { $agentsApi = $api->getAgentsStatus(); } catch (\Throwable $e) { $error = $e->getMessage(); }
            foreach ($agentsApi as $agent) {
                $exten = $agent['exten'];
                $login = $agent['user_login'] ?? '';
                $apiGroup = $agent['group'] ?? '';
                $localGroup = '';
                if ($apiGroup) {
                    $stmt = $db->prepare('SELECT name FROM groups WHERE api_group_name=?');
                    $stmt->bind_param('s', $apiGroup);
                    $stmt->execute();
                    $r = $stmt->get_result()->fetch_assoc();
                    if ($r) $localGroup = $r['name'];
                    $stmt->close();
                }
                $stmt = $db->prepare('INSERT INTO agents (exten, user_login, group_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE user_login=VALUES(user_login), group_name=VALUES(group_name), updated_at=NOW()');
                $stmt->bind_param('sss', $exten, $login, $localGroup);
                $stmt->execute();
                $stmt->close();
            }
            // Refresh agentsDb
            $agentsDb = $db->query('SELECT exten, user_login, group_name, active FROM agents')->fetch_all(MYSQLI_ASSOC);
        }

        // Get status from API
        $agentsApi = [];
        try { $agentsApi = $api->getAgentsStatus(); } catch (\Throwable $e) { $error = $e->getMessage(); }

        // Merge status
        $statusMap = [];
        foreach ($agentsApi as $a) {
            $statusMap[$a['exten']] = $a;
        }
        // Filter for group admin or member
        if (!$isSuper) {
            if ($isUser) {
                $agentsDb = array_filter($agentsDb, function($a) use ($userAgentId) {
                    return ($a['active'] ?? 1) == 1 && $a['id'] == $userAgentId;
                });
            } elseif ($isGroupMember) {
                // Groupmember sadece kendi agent_id'sine ait agenti görür
                $agentsDb = array_filter($agentsDb, function($a) use ($userAgentId) {
                    return ($a['active'] ?? 1) == 1 && $a['id'] == $userAgentId;
                });
            } else {
                $agentsDb = array_filter($agentsDb, function($a) use ($userGroupName) {
                    return ($a['active'] ?? 1) == 1 && strtolower($a['group_name']) === strtolower($userGroupName);
                });
            }
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

        // Case-insensitive gruplama ve fallback
        $agentsByGroup = [];
        foreach ($agents as $agent) {
            $group = $agent['group'] ?? $agent['group_name'] ?? ''; // API yoksa DB'den fallback
            // Case-insensitive karşılaştırma
            if ($isSuper || (!$isGroupMember && strtolower($group) === strtolower($userGroupName))) {
                if (!isset($agentsByGroup[$group])) {
                    $agentsByGroup[$group] = [];
                }
                $agentsByGroup[$group][] = $agent;
            } elseif ($isGroupMember) {
                // Groupmember sadece kendi agent'ını görür
                $group = 'Kendi Agentınız';
                if (!isset($agentsByGroup[$group])) {
                    $agentsByGroup[$group] = [];
                }
                $agentsByGroup[$group][] = $agent;
            }
        }

        // DEBUGGING İÇİN (production'da kaldırın):
        // error_log("UserGroupName: " . $userGroupName);
        // error_log("Agents count: " . count($agents));
        // error_log("AgentsByGroup keys: " . implode(', ', array_keys($agentsByGroup)));

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
            $apiGroup = $agent['group'] ?? '';
            $localGroup = '';
            if ($apiGroup) {
                $stmt = $db->prepare('SELECT name FROM groups WHERE api_group_name=?');
                $stmt->bind_param('s', $apiGroup);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_assoc();
                if ($r) $localGroup = $r['name'];
                $stmt->close();
            }
            $stmt = $db->prepare('INSERT INTO agents (exten, user_login, group_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE user_login=VALUES(user_login), group_name=VALUES(group_name), updated_at=NOW()');
            $stmt->bind_param('sss', $exten, $login, $localGroup);
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