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

        $isSuper = $this->isSuper();
        $userGroupName = '';
        if (!$isSuper) {
            $userGroupName = $_SESSION['user']['group_name'] ?? '';
        }
        $api = new ApiClient();
        $error = null;
        $agents = [];
        try { $agents = $api->getAgentsStatus(); } catch (\Throwable $e) { $error = $e->getMessage(); }

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
        header('Location: /agents');
    }
}
