<?php
namespace App\Helpers;
class Security {
    public static function sanitize($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    public static function hash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }
}
