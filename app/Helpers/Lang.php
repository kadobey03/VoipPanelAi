<?php
namespace App\Helpers;

class Lang {
    private static $lang = [];

    public static function load($lang = 'tr') {
        $file = __DIR__ . '/../../lang/' . $lang . '.php';
        if (file_exists($file)) {
            self::$lang = require $file;
        }
    }

    public static function get($key) {
        return self::$lang[$key] ?? $key;
    }

    public static function getCurrentLang() {
        return $_SESSION['lang'] ?? 'tr';
    }

    public static function current() {
        return $_SESSION['lang'] ?? 'tr';
    }

    public static function set($lang) {
        $_SESSION['lang'] = $lang;
        self::load($lang);
    }
}