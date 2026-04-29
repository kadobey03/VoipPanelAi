<?php
namespace App\Controllers;

class LandingController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Giriş yapmışsa dashboard'a yönlendir
        if (isset($_SESSION['user'])) {
            \App\Helpers\Url::redirect('/');
        }
        require __DIR__ . '/../Views/landing.php';
    }
}