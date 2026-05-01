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

            // DEBUG BAŞLANGICI
            $debugInfo = [];
            $debugInfo[] = 'METHOD: ' . $_SERVER['REQUEST_METHOD'];
            $debugInfo[] = 'POST login: ' . htmlspecialchars($login);
            $debugInfo[] = 'POST password length: ' . strlen($password);

            try {
                $mysqli = DB::conn();
                $debugInfo[] = 'DB bağlantısı: OK';

                $stmt = $mysqli->prepare('SELECT id, login, password, role, group_id FROM users WHERE login=? LIMIT 1');
                $stmt->bind_param('s', $login);
                $stmt->execute();
                $res = $stmt->get_result();
                $user = $res->fetch_assoc();
                $stmt->close();

                if ($user) {
                    $debugInfo[] = 'Kullanıcı bulundu: ID=' . $user['id'] . ', role=' . $user['role'];
                    $debugInfo[] = 'Hash preview: ' . substr($user['password'], 0, 10) . '...';
                    $verify = Security::verify($password, $user['password']);
                    $debugInfo[] = 'password_verify sonucu: ' . ($verify ? 'TRUE ✅' : 'FALSE ❌');
                } else {
                    $debugInfo[] = 'Kullanıcı BULUNAMADI ❌ (login: ' . htmlspecialchars($login) . ')';
                }

                if ($user && Security::verify($password, $user['password'])) {
                    $debugInfo[] = 'Giriş başarılı, redirect ediliyor...';
                    // DEBUG çıktısını logla
                    \App\Helpers\Logger::log('LOGIN DEBUG: ' . implode(' | ', $debugInfo));
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => (int)$user['id'],
                        'login' => $user['login'],
                        'role' => $user['role'],
                        'group_id' => $user['group_id'] ? (int)$user['group_id'] : null,
                    ];
                    Url::redirect('/');
                } else {
                    $error = Lang::get('invalid_credentials');
                    $debugInfo[] = 'GİRİŞ BAŞARISIZ ❌';
                    // DEBUG çıktısını sayfada göster
                    $error .= '<br><br><div style="background:#1e293b;color:#94a3b8;padding:12px;border-radius:8px;font-size:12px;font-family:monospace;text-align:left"><b style="color:#f87171">DEBUG:</b><br>' . implode('<br>', $debugInfo) . '</div>';
                    \App\Helpers\Logger::log('LOGIN DEBUG FAIL: ' . implode(' | ', $debugInfo));
                }
            } catch (\Throwable $e) {
                $debugInfo[] = 'EXCEPTION: ' . $e->getMessage();
                $error = Lang::get('login_error') . $e->getMessage();
                $error .= '<br><div style="background:#1e293b;color:#94a3b8;padding:12px;border-radius:8px;font-size:12px;font-family:monospace;text-align:left"><b style="color:#f87171">DEBUG:</b><br>' . implode('<br>', $debugInfo) . '</div>';
            }
            // DEBUG SONU
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

    public function register() {
        $this->startSession();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $email = trim($_POST['email'] ?? '');

            // Validation
            $errors = [];
            if (empty($login) || strlen($login) < 3) {
                $errors[] = Lang::get('username_min_length');
            }
            if (empty($password) || strlen($password) < 6) {
                $errors[] = Lang::get('password_min_length');
            }
            if ($password !== $confirmPassword) {
                $errors[] = Lang::get('password_mismatch');
            }
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = Lang::get('invalid_email');
            }

            try {
                $mysqli = DB::conn();
                
                // Check if username already exists
                $stmt = $mysqli->prepare('SELECT id FROM users WHERE login=? LIMIT 1');
                $stmt->bind_param('s', $login);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->fetch_assoc()) {
                    $errors[] = Lang::get('username_exists');
                }
                $stmt->close();

                if (empty($errors)) {
                    // Hash password and create user
                    $hashedPassword = Security::hash($password);
                    $stmt = $mysqli->prepare('INSERT INTO users (login, password, role, created_at) VALUES (?, ?, "groupmember", NOW())');
                    $stmt->bind_param('ss', $login, $hashedPassword);
                    
                    if ($stmt->execute()) {
                        $userId = $mysqli->insert_id;
                        $stmt->close();
                        
                        // Log the user in automatically
                        $_SESSION['user'] = [
                            'id' => $userId,
                            'login' => $login,
                            'role' => 'groupmember',
                            'group_id' => null,
                        ];
                        
                        $success = Lang::get('registration_success');
                        Url::redirect('/?registered=1');
                    } else {
                        $errors[] = Lang::get('registration_error');
                        $stmt->close();
                    }
                }
            } catch (\Throwable $e) {
                $errors[] = Lang::get('registration_error') . ': ' . $e->getMessage();
            }
        }
        require __DIR__.'/../Views/auth/register.php';
    }
}
