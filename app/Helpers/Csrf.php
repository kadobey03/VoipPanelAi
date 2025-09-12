<?php
namespace App\Helpers;
class Csrf {
    public static function generate() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
    public static function check($token) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
