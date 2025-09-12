<?php
namespace App\Controllers;

use App\Helpers\ApiClient;

class AgentsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ header('Location: /login'); exit; } }

    public function index(){
        $this->requireAuth();
        $api = new ApiClient();
        $error=null; $agents=[];
        try { $agents = $api->getAgentsStatus(); } catch (\Throwable $e) { $error = $e->getMessage(); }
        require __DIR__.'/../Views/agents/index.php';
    }
}

