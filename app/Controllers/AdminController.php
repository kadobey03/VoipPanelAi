<?php
namespace App\Controllers;

use App\Helpers\DB;

class AdminController {
    private function start(){ if (session_status()===PHP_SESSION_NONE) session_start(); if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    public function impersonate(){
        $this->start(); if (!$this->isSuper()) { http_response_code(403); echo 'Yetkisiz'; return; }
        $id = (int)($_GET['id'] ?? 0); if(!$id){ \App\Helpers\Url::redirect('/users'); }
        $db=DB::conn(); $stmt=$db->prepare('SELECT id, login, role, group_id FROM users WHERE id=?'); $stmt->bind_param('i',$id); $stmt->execute(); $u=$stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($u) { $_SESSION['impersonator'] = $_SESSION['user']; $_SESSION['user']=['id'=>(int)$u['id'],'login'=>$u['login'],'role'=>$u['role'],'group_id'=>$u['group_id']? (int)$u['group_id']:null]; }
        \App\Helpers\Url::redirect('/');
    }

    public function stopImpersonate(){
        $this->start(); if (!isset($_SESSION['impersonator'])) { \App\Helpers\Url::redirect('/'); }
        $_SESSION['user'] = $_SESSION['impersonator']; unset($_SESSION['impersonator']);
        \App\Helpers\Url::redirect('/');
    }
}

