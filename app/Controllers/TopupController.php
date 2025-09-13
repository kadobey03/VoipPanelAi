<?php
namespace App\Controllers;

use App\Helpers\DB;

class TopupController {
    private function start(){ if (session_status()===PHP_SESSION_NONE) session_start(); if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    public function index(){
        $this->start();
        $db = DB::conn();
        if ($this->isSuper()) {
            $res = $db->query('SELECT tr.*, g.name AS group_name, u.login AS user_login FROM topup_requests tr LEFT JOIN groups g ON g.id=tr.group_id LEFT JOIN users u ON u.id=tr.user_id ORDER BY tr.id DESC');
        } else {
            $gid = (int)($_SESSION['user']['group_id'] ?? 0);
            $stmt=$db->prepare('SELECT tr.*, g.name AS group_name, u.login AS user_login FROM topup_requests tr LEFT JOIN groups g ON g.id=tr.group_id LEFT JOIN users u ON u.id=tr.user_id WHERE tr.group_id=? ORDER BY tr.id DESC');
            $stmt->bind_param('i', $gid); $stmt->execute(); $res=$stmt->get_result(); $stmt->close();
        }
        $items=[]; while($row=$res->fetch_assoc()){$items[]=$row;}
        require __DIR__.'/../Views/topups/index.php';
    }

    public function approve(){
        $this->start(); if (!$this->isSuper()) { http_response_code(403); echo 'Yetkisiz'; return; }
        $id = (int)($_POST['id'] ?? 0);
        $db = DB::conn();
        // fetch request
        $stmt=$db->prepare('SELECT * FROM topup_requests WHERE id=? AND status="pending"');
        $stmt->bind_param('i',$id); $stmt->execute(); $req=$stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$req) { \App\Helpers\Url::redirect('/topups'); }
        $db->begin_transaction();
        try{
            // update group balance
            $stmt=$db->prepare('UPDATE groups SET balance=balance+? WHERE id=?');
            $stmt->bind_param('di', $req['amount'], $req['group_id']); $stmt->execute(); $stmt->close();
            // insert transaction
            $desc = 'Topup method: '.($req['method'] ?? ''); $type='topup'; $ref = 'req#'.$req['id'];
            $stmt=$db->prepare('INSERT INTO transactions (group_id, type, amount, reference, description) VALUES (?,?,?,?,?)');
            $amt = (float)$req['amount']; $stmt->bind_param('isdss', $req['group_id'], $type, $amt, $ref, $desc); $stmt->execute(); $stmt->close();
            // mark approved
            $now = date('Y-m-d H:i:s'); $adminId = (int)($_SESSION['user']['id'] ?? 0);
            $stmt=$db->prepare('UPDATE topup_requests SET status="approved", approved_at=?, approved_by=? WHERE id=?');
            $stmt->bind_param('sii', $now, $adminId, $id); $stmt->execute(); $stmt->close();
            $db->commit();
        } catch (\Throwable $e) { $db->rollback(); }
        \App\Helpers\Url::redirect('/topups');
    }

    public function reject(){
        $this->start(); if (!$this->isSuper()) { http_response_code(403); echo 'Yetkisiz'; return; }
        $id = (int)($_POST['id'] ?? 0);
        $db = DB::conn(); $now = date('Y-m-d H:i:s'); $adminId = (int)($_SESSION['user']['id'] ?? 0);
        $stmt=$db->prepare('UPDATE topup_requests SET status="rejected", approved_at=?, approved_by=? WHERE id=? AND status="pending"');
        $stmt->bind_param('sii', $now, $adminId, $id); $stmt->execute(); $stmt->close();
        \App\Helpers\Url::redirect('/topups');
    }

    public function receipt(){
        $this->start();
        $id = (int)($_GET['id'] ?? 0);
        $db = DB::conn();
        $stmt=$db->prepare('SELECT tr.receipt_path, tr.group_id FROM topup_requests tr WHERE tr.id=?');
        $stmt->bind_param('i',$id); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close();
        if(!$row || empty($row['receipt_path'])){ http_response_code(404); echo 'Bulunamadı'; return; }
        // Permission: super admin or same group
        if (!($this->isSuper() || ((int)($_SESSION['user']['group_id'] ?? 0) === (int)$row['group_id']))) { http_response_code(403); echo 'Yetkisiz'; return; }
        $path = __DIR__.'/../../'.ltrim($row['receipt_path'],'/');
        if (!is_file($path)) { http_response_code(404); echo 'Dosya yok'; return; }
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        header('Content-Type: '.$mime);
        header('Content-Length: '.filesize($path));
        readfile($path);
    }
}
