<?php
namespace App\Controllers;

use App\Helpers\DB;

class TransactionsController {
    private function start(){ if (session_status()===PHP_SESSION_NONE) session_start(); if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    public function index(){
        $this->start();
        $db = DB::conn();
        $items=[]; $groupId=null;
        if ($this->isSuper()) {
            if (isset($_GET['group_id']) && $_GET['group_id']!=='') { $groupId=(int)$_GET['group_id']; }
            if ($groupId) {
                $stmt=$db->prepare('SELECT t.*, g.name AS group_name FROM transactions t LEFT JOIN groups g ON g.id=t.group_id WHERE t.group_id=? ORDER BY t.id DESC');
                $stmt->bind_param('i',$groupId); $stmt->execute(); $res=$stmt->get_result();
            } else {
                $res = $db->query('SELECT t.*, g.name AS group_name FROM transactions t LEFT JOIN groups g ON g.id=t.group_id ORDER BY t.id DESC');
            }
        } else {
            $groupId = (int)($_SESSION['user']['group_id'] ?? 0);
            $stmt=$db->prepare('SELECT t.*, g.name AS group_name FROM transactions t LEFT JOIN groups g ON g.id=t.group_id WHERE t.group_id=? ORDER BY t.id DESC');
            $stmt->bind_param('i',$groupId); $stmt->execute(); $res=$stmt->get_result();
        }
        while($row=$res->fetch_assoc()){$items[]=$row;}
        if (isset($stmt)) $stmt->close();
        require __DIR__.'/../Views/transactions/index.php';
    }
}

