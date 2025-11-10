<?php
namespace App\Controllers;

use App\Helpers\DB;

class SettingsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ \App\Helpers\Url::redirect('/login'); } }
    private function requireSuper(){ $this->requireAuth(); if(!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'superadmin'){ die('Yetkisiz'); } }

    public function index(){
        $this->requireSuper();
        $db = DB::conn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $key => $value) {
                if ($key !== 'submit') {
                    $stmt = $db->prepare('INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value=?');
                    $stmt->bind_param('sss', $key, $value, $value);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            header('Location: ' . \App\Helpers\Url::to('/settings'));
            exit;
        }

        $settings = [];
        $result = $db->query('SELECT name, value FROM settings');
        while ($row = $result->fetch_assoc()) {
            $settings[$row['name']] = $row['value'];
        }

        require __DIR__.'/../Views/settings/index.php';
    }

    public function changeLang() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $lang = $_POST['lang'] ?? 'tr';
        if (in_array($lang, ['tr', 'en'])) {
            \App\Helpers\Lang::set($lang);
        }
        header('Location: ' . (\App\Helpers\Url::to('/')));
        exit;
    }
}