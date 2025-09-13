<?php
namespace App\Controllers;

use App\Helpers\DB;
use App\Helpers\Security;

class UserController {
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }
    private function requireAuth() {
        $this->startSession();
        if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); }
    }
    private function isSuperAdmin(): bool {
        return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'superadmin';
    }

    public function index() {
        $this->requireAuth();
        $mysqli = DB::conn();
        if ($this->isSuperAdmin()) {
            $res = $mysqli->query('SELECT id, login, role, group_id, agent_id, exten FROM users ORDER BY id DESC');
        } else {
            $gid = (int)($_SESSION['user']['group_id'] ?? 0);
            $stmt = $mysqli->prepare('SELECT id, login, role, group_id, agent_id, exten FROM users WHERE group_id=? ORDER BY id DESC');
            $stmt->bind_param('i', $gid);
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();
        }
        $users = [];
        while ($row = $res->fetch_assoc()) { $users[] = $row; }
        require __DIR__.'/../Views/users/index.php';
    }

    public function create() {
        $this->requireAuth();
        $mysqli = DB::conn();
        $error = null; $ok = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';
            $exten = trim($_POST['exten'] ?? '');
            $role = $this->isSuperAdmin() ? ($_POST['role'] ?? 'groupadmin') : 'groupadmin';
            $group_id = $this->isSuperAdmin() ? (int)($_POST['group_id'] ?? 0) : (int)($_SESSION['user']['group_id'] ?? 0);
            $agent_id = $this->isSuperAdmin() && $role === 'groupmember' ? (int)($_POST['agent_id'] ?? 0) : null;
            if ($login && $password) {
                $hash = Security::hash($password);
                if ($agent_id !== null) {
                    $stmt = $mysqli->prepare('INSERT INTO users (login, password, exten, role, group_id, agent_id) VALUES (?,?,?,?,?,?)');
                    $stmt->bind_param('ssssii', $login, $hash, $exten, $role, $group_id, $agent_id);
                } else {
                    $stmt = $mysqli->prepare('INSERT INTO users (login, password, exten, role, group_id) VALUES (?,?,?,?,?)');
                    $stmt->bind_param('ssssi', $login, $hash, $exten, $role, $group_id);
                }
                if ($stmt->execute()) {
                    $ok = 'Kullanıcı oluşturuldu';
                } else {
                    $error = 'Kullanıcı oluşturulamadı';
                }
                $stmt->close();
            } else {
                $error = 'Zorunlu alanlar eksik';
            }
        }
        // fetch groups and agents for superadmin selection
        $groups = [];
        $agents = [];
        if ($this->isSuperAdmin()) {
            $res = $mysqli->query('SELECT id, name FROM groups ORDER BY name');
            while ($row = $res->fetch_assoc()) { $groups[] = $row; }
            $res2 = $mysqli->query('SELECT id, user_login, exten FROM agents WHERE active=1 ORDER BY user_login');
            while ($row = $res2->fetch_assoc()) { $agents[] = $row; }
        }
        require __DIR__.'/../Views/users/create.php';
    }

    public function edit() {
        $this->requireAuth();
        $mysqli = DB::conn();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { \App\Helpers\Url::redirect('/users'); }
        $error = null; $ok = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';
            $exten = isset($_POST['exten']) ? trim($_POST['exten']) : null;
            $role = $this->isSuperAdmin() ? ($_POST['role'] ?? 'groupadmin') : null;
            $group_id = $this->isSuperAdmin() ? (int)($_POST['group_id'] ?? 0) : null;

            $sql = 'UPDATE users SET login=?';
            $types = 's';
            $params = [$login];
            if ($password !== '') { $sql .= ', password=?'; $types.='s'; $params[] = Security::hash($password); }
            if ($exten !== null) { $sql .= ', exten=?'; $types.='s'; $params[] = $exten; }
            if ($role !== null) { $sql .= ', role=?'; $types.='s'; $params[] = $role; }
            if ($group_id !== null) { $sql .= ', group_id=?'; $types.='i'; $params[] = $group_id; }
            $sql .= ' WHERE id=?'; $types.='i'; $params[] = $id;
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $ok = 'Kullanıcı güncellendi';
            } else { $error = 'Güncelleme başarısız'; }
            $stmt->close();
        }
        // fetch current
        $stmt = $mysqli->prepare('SELECT id, login, role, group_id, agent_id, exten FROM users WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        // fetch groups for superadmin selection
        $groups = [];
        if ($this->isSuperAdmin()) {
            $res = $mysqli->query('SELECT id, name FROM groups ORDER BY name');
            while ($row = $res->fetch_assoc()) { $groups[] = $row; }
        }
        require __DIR__.'/../Views/users/edit.php';
    }

    public function delete() {
        $this->requireAuth();
        if (!$this->isSuperAdmin()) { http_response_code(403); echo 'Yetkisiz'; return; }
        $mysqli = DB::conn();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 1) { // id 1 süper admin varsayılanı koru
            $stmt = $mysqli->prepare('DELETE FROM users WHERE id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
        \App\Helpers\Url::redirect('/users');
    }
}

