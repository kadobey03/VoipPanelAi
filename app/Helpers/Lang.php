<?php
namespace App\Helpers;

class Lang {
    private static $lang = [];

    public static function load($lang = 'en') {
        $file = __DIR__ . '/../../lang/' . $lang . '.php';
        if (file_exists($file)) {
            self::$lang = require $file;
        }
    }

    public static function get($key) {
        return self::$lang[$key] ?? $key;
    }

    public static function current() {
        return $_SESSION['lang'] ?? 'en';
    }

    public static function set($lang) {
        $_SESSION['lang'] = $lang;
        self::load($lang);
    }

    public static function detectBrowserLanguage() {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return 'en';
        }
        
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $supportedLangs = ['en', 'tr', 'ru'];
        
        // Parse Accept-Language header
        $languages = [];
        preg_match_all('/([a-z]{2})(?:-[A-Z]{2})?\s*(?:;\s*q\s*=\s*([0-9.]+))?/i', $acceptLang, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $lang = strtolower($match[1]);
            $quality = isset($match[2]) ? (float) $match[2] : 1.0;
            $languages[] = [$lang, $quality];
        }
        
        // Sort by quality
        usort($languages, function($a, $b) {
            return $b[1] <=> $a[1];
        });
        
        // Find first supported language
        foreach ($languages as $langData) {
            if (in_array($langData[0], $supportedLangs)) {
                return $langData[0];
            }
        }
        
        return 'en'; // Default fallback
    }
}