<?php
namespace App\Controllers;

use App\Helpers\DB;

class BalanceMenuController {
    private function start(){ if (session_status()===PHP_SESSION_NONE) session_start(); if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    public function topupSelect(){
        $this->start();
        if ($this->isSuper()) {
            $db = DB::conn(); $res=$db->query('SELECT id,name FROM groups ORDER BY name'); $groups=[]; while($r=$res->fetch_assoc()){$groups[]=$r;}
            require __DIR__.'/../Views/balance/topup_select.php';
        } else {
            $gid = (int)($_SESSION['user']['group_id'] ?? 0); if ($gid) { \App\Helpers\Url::redirect('/groups/topup?id='.$gid); } else { echo 'Grup bulunamadı'; }
        }
    }
}

