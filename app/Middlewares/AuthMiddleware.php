<?php
namespace App\Middlewares;

use App\Helpers\Url;

class AuthMiddleware {
    public static function handle() {
        session_start();
        if (!isset($_SESSION['user'])) {
            Url::redirect('/login');
        }
    }
}
