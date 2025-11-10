<?php
namespace App\Controllers;

use App\Helpers\DB;
use App\Helpers\Security;
use App\Helpers\Url;

class AuthController {
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function login() {
        $this->startSession();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = $_POST['login'] ?? '';
            $password = $_POST['password'] ?? '';

            // DEBUG: Log attempt
            error_log("Login attempt - User: $login, Password length: " . strlen($password));

            try {
                $mysqli = DB::conn();
                $stmt = $mysqli->prepare('SELECT id, login, password, role, group_id FROM users WHERE login=? LIMIT 1');
                $stmt->bind_param('s', $login);
                $stmt->execute();
                $res = $stmt->get_result();
                $user = $res->fetch_assoc();
                $stmt->close();

                // DEBUG: Log database result
                error_log("Database result: " . ($user ? "User found - ID: {$user['id']}, Login: {$user['login']}, Role: {$user['role']}" : "User not found"));
                
                if ($user) {
                    error_log("Password verification: " . (Security::verify($password, $user['password']) ? "SUCCESS" : "FAILED"));
                }

                if ($user && Security::verify($password, $user['password'])) {
                    $_SESSION['user'] = [
                        'id' => (int)$user['id'],
                        'login' => $user['login'],
                        'role' => $user['role'],
                        'group_id' => $user['group_id'] ? (int)$user['group_id'] : null,
                    ];
                    error_log("Login successful for user: $login");
                    Url::redirect('/VoipPanelAi/');
                } else {
                    $error = 'Geçersiz kullanıcı adı veya şifre';
                    error_log("Login failed for user: $login");
                }
            } catch (\Throwable $e) {
                $error = 'Giriş sırasında hata: '.$e->getMessage();
                error_log("Login error: " . $e->getMessage());
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
        Url::redirect('/VoipPanelAi/login');
    }
}
