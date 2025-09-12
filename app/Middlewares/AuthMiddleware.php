<?php
namespace App\Middlewares;
class AuthMiddleware {
    public static function handle() {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }
}
