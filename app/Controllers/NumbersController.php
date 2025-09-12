<?php
namespace App\Controllers;

use App\Helpers\ApiClient;

class NumbersController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ header('Location: /login'); exit; } }

    public function index(){
        $this->requireAuth();
        $api = new ApiClient();
        $error=null; $numbers=[];
        try { $numbers = $api->getExternalNumbers(); } catch (\Throwable $e) { $error = $e->getMessage(); }
        require __DIR__.'/../Views/numbers/index.php';
    }

    public function setActive(){ $this->setStatus('active'); }
    public function setSpam(){ $this->setStatus('spam'); }

    private function setStatus($status){
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /numbers'); return; }
        $number = $_POST['number'] ?? '';
        $api = new ApiClient();
        try {
            if ($status==='active') $api->setNumberActive($number); else $api->setNumberSpam($number);
        } catch (\Throwable $e) { /* ignore here, reflect on UI next load */ }
        header('Location: /numbers');
    }
}

