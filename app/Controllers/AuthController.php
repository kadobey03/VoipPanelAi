<?php
namespace App\Controllers;

use App\Helpers\DB;
use App\Helpers\Security;
use App\Helpers\Url;
use App\Helpers\Lang;

class AuthController {
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function login() {
        $this->startSession();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = $_POST['login'] ?? '';
            $password = $_POST['password'] ?? '';

            try {
                $mysqli = DB::conn();
                $stmt = $mysqli->prepare('SELECT id, login, password, role, group_id FROM users WHERE login=? LIMIT 1');
                $stmt->bind_param('s', $login);
                $stmt->execute();
                $res = $stmt->get_result();
                $user = $res->fetch_assoc();
                $stmt->close();

                if ($user && Security::verify($password, $user['password'])) {
                    $_SESSION['user'] = [
                        'id' => (int)$user['id'],
                        'login' => $user['login'],
                        'role' => $user['role'],
                        'group_id' => $user['group_id'] ? (int)$user['group_id'] : null,
                    ];
                    Url::redirect('/');
                } else {
                    $error = Lang::get('invalid_credentials');
                }
            } catch (\Throwable $e) {
                $error = Lang::get('login_error').$e->getMessage();
            }
        }
        require __DIR__.'/../Views/auth/login.php';
    }

    public function logout() {
        $this->startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        Url::redirect('/login');
    }
}
