<?php
namespace App\Controllers;

use App\Helpers\DB;
use App\Helpers\Security;

class ProfileController {
    private function start(){ if (session_status()===PHP_SESSION_NONE) session_start(); if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); } }

    public function index(){
        $this->start();
        $db = DB::conn();
        $uid = (int)$_SESSION['user']['id'];
        $ok=null; $error=null;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $login = trim($_POST['login'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            if ($login==='') { $error='Kullanıcı adı gerekli'; }
            else {
                $sql='UPDATE users SET login=?'; $types='s'; $params=[$login];
                if ($password!=='') { $sql.=', password=?'; $types.='s'; $params[] = Security::hash($password); }
                $sql.=' WHERE id=?'; $types.='i'; $params[]=$uid;
                $stmt=$db->prepare($sql); $stmt->bind_param($types, ...$params); if ($stmt->execute()) { $ok='Profil güncellendi'; $_SESSION['user']['login']=$login; } else { $error='Güncellenemedi'; } $stmt->close();
            }
        }
        // fetch current
        $stmt=$db->prepare('SELECT login FROM users WHERE id=?'); $stmt->bind_param('i',$uid); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close();
        $login = $row['login'] ?? '';
        require __DIR__.'/../Views/profile/index.php';
    }
}

