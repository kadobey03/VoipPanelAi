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

            $debugInfo = [];
            $debugInfo[] = 'METHOD: POST';
            $debugInfo[] = 'login: [' . htmlspecialchars($login) . '] uzunluk=' . strlen($login);
            $debugInfo[] = 'password uzunluk: ' . strlen($password);
            $debugInfo[] = 'POST keys: ' . implode(', ', array_keys($_POST));

            try {
                $mysqli = DB::conn();
                $debugInfo[] = 'DB: OK';

                $stmt = $mysqli->prepare('SELECT id, login, password, role, group_id FROM users WHERE login=? LIMIT 1');
                $stmt->bind_param('s', $login);
                $stmt->execute();
                $res = $stmt->get_result();
                $user = $res->fetch_assoc();
                $stmt->close();

                if ($user) {
                    $debugInfo[] = 'Kullanıcı BULUNDU: id=' . $user['id'] . ' role=' . $user['role'];
                    $verify = Security::verify($password, $user['password']);
                    $debugInfo[] = 'password_verify: ' . ($verify ? 'TRUE ✅' : 'FALSE ❌');
                } else {
                    $debugInfo[] = 'Kullanıcı BULUNAMADI ❌';
                    // Tüm kullanıcıları listele
                    $res2 = $mysqli->query('SELECT id, login FROM users LIMIT 5');
                    $users = [];
                    while ($row = $res2->fetch_assoc()) $users[] = $row['id'].':'.$row['login'];
                    $debugInfo[] = 'DB kullanıcılar: ' . implode(', ', $users);
                }

                if ($user && Security::verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => (int)$user['id'],
                        'login' => $user['login'],
                        'role' => $user['role'],
                        'group_id' => $user['group_id'] ? (int)$user['group_id'] : null,
                    ];
                    Url::redirect('/');
                } else {
                    $debugInfo[] = 'SONUÇ: GİRİŞ BAŞARISIZ ❌';
                    $error = Lang::get('invalid_credentials');
                    $error .= '<br><br><div style="background:#0f172a;color:#94a3b8;padding:10px;border-radius:6px;font-size:11px;font-family:monospace;text-align:left;margin-top:8px"><b style="color:#f87171">DEBUG:</b><br>' . implode('<br>', $debugInfo) . '</div>';
                }
            } catch (\Throwable $e) {
                $debugInfo[] = 'EXCEPTION: ' . $e->getMessage();
                $error = 'HATA: ' . $e->getMessage();
                $error .= '<br><div style="background:#0f172a;color:#94a3b8;padding:10px;border-radius:6px;font-size:11px;font-family:monospace;text-align:left"><b style="color:#f87171">DEBUG:</b><br>' . implode('<br>', $debugInfo) . '</div>';
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
