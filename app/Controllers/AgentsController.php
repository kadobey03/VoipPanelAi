<?php
namespace App\Controllers;

use App\Helpers\ApiClient;

class AgentsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    public function index(){
        $this->requireAuth();
        $isSuper = $this->isSuper();
        $userGroupName = '';
        if (!$isSuper) {
            $userGroupName = $_SESSION['user']['group_name'] ?? '';
        }
        $api = new ApiClient();
        $error = null;
        $agents = [];
        try { $agents = $api->getAgentsStatus(); } catch (\Throwable $e) { $error = $e->getMessage(); }
        $agentsByGroup = [];
        foreach ($agents as $agent) {
            $group = $agent['group'] ?? '';
            if ($isSuper || $group === $userGroupName) {
                $agentsByGroup[$group][] = $agent;
            }
        }
        require __DIR__.'/../Views/agents/index.php';
    }
}
