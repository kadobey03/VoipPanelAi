<?php
namespace App\Controllers;

use App\Helpers\DB;

class GroupController {
    private function startSession() { if (session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireSuperOrGroupAdmin() {
        $this->startSession();
        if (!isset($_SESSION['user'])) { header('Location: /login'); exit; }
    }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }
    private function currentGroupId(): ?int { return $_SESSION['user']['group_id'] ?? null; }

    public function index() {
        $this->requireSuperOrGroupAdmin();
        $db = DB::conn();
        if ($this->isSuper()) {
            $res = $db->query('SELECT id, name, margin, balance FROM groups ORDER BY id DESC');
        } else {
            $gid = (int)$this->currentGroupId();
            $stmt = $db->prepare('SELECT id, name, margin, balance FROM groups WHERE id=?');
            $stmt->bind_param('i', $gid);
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();
        }
        $groups = [];
        while ($row = $res->fetch_assoc()) { $groups[] = $row; }
        require __DIR__.'/../Views/groups/index.php';
    }

    public function edit() {
        $this->requireSuperOrGroupAdmin();
        $db = DB::conn();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: /groups'); exit; }
        if (!$this->isSuper() && $this->currentGroupId() !== $id) { http_response_code(403); echo 'Yetkisiz'; return; }
        $error = null; $ok = null;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $name = trim($_POST['name'] ?? '');
            $margin = (float)($_POST['margin'] ?? 0);
            if ($name !== '') {
                $stmt = $db->prepare('UPDATE groups SET name=?, margin=? WHERE id=?');
                $stmt->bind_param('sdi', $name, $margin, $id);
                if ($stmt->execute()) { $ok='Güncellendi'; } else { $error='Güncelleme hatası'; }
                $stmt->close();
            } else { $error='İsim gerekli'; }
        }
        $stmt = $db->prepare('SELECT id, name, margin, balance FROM groups WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $group = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        require __DIR__.'/../Views/groups/edit.php';
    }

    public function create() {
        $this->requireSuperOrGroupAdmin();
        if (!$this->isSuper()) { http_response_code(403); echo 'Sadece süper admin'; return; }
        $db = DB::conn();
        $error=null; $ok=null;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $name = trim($_POST['name'] ?? '');
            $margin = (float)($_POST['margin'] ?? 0);
            $balance = (float)($_POST['balance'] ?? 0);
            if ($name!=='') {
                $stmt=$db->prepare('INSERT INTO groups (name, margin, balance) VALUES (?,?,?)');
                $stmt->bind_param('sdd', $name, $margin, $balance);
                if ($stmt->execute()) { $ok='Grup oluşturuldu'; } else { $error='Oluşturma hatası'; }
                $stmt->close();
            } else { $error='İsim gerekli'; }
        }
        require __DIR__.'/../Views/groups/create.php';
    }

    public function topup() {
        $this->requireSuperOrGroupAdmin();
        $db = DB::conn();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: /groups'); exit; }
        if (!$this->isSuper() && $this->currentGroupId() !== $id) { http_response_code(403); echo 'Yetkisiz'; return; }
        $error=null; $ok=null;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $amount = (float)($_POST['amount'] ?? 0);
            $method = $_POST['method'] ?? ($this->isSuper() ? 'manual' : 'unknown');
            if ($amount>0) {
                $db->begin_transaction();
                try {
                    $stmt = $db->prepare('UPDATE groups SET balance = balance + ? WHERE id=?');
                    $stmt->bind_param('di', $amount, $id);
                    $stmt->execute();
                    $stmt->close();

                    $type = 'topup'; $desc = 'Topup method: '.$method; $ref = null;
                    $stmt = $db->prepare('INSERT INTO transactions (group_id, type, amount, reference, description) VALUES (?,?,?,?,?)');
                    $stmt->bind_param('isdss', $id, $type, $amount, $ref, $desc);
                    $stmt->execute();
                    $stmt->close();
                    $db->commit();
                    $ok = 'Bakiye eklendi';
                } catch (\Throwable $e) {
                    $db->rollback();
                    $error = 'Hata: '.$e->getMessage();
                }
            } else { $error='Geçerli tutar girin'; }
        }
        // fetch group
        $stmt = $db->prepare('SELECT id, name, margin, balance FROM groups WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $group = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        require __DIR__.'/../Views/groups/topup.php';
    }

    public function show() {
        $this->requireSuperOrGroupAdmin();
        $db = DB::conn();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: /groups'); exit; }
        if (!$this->isSuper() && $this->currentGroupId() !== $id) { http_response_code(403); echo 'Yetkisiz'; return; }
        $stmt = $db->prepare('SELECT id, name, margin, balance FROM groups WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $group = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        // last 20 transactions
        $stmt = $db->prepare('SELECT id, type, amount, reference, description, created_at FROM transactions WHERE group_id=? ORDER BY id DESC LIMIT 20');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        require __DIR__.'/../Views/groups/show.php';
    }
}

