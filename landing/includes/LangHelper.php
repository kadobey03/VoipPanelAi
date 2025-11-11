<?php
/**
 * Simple Language Helper for Landing Page
 */
class LangHelper {
    private static $currentLang = 'tr';
    private static $translations = [];
    private static $loaded = false;
    
    /**
     * Set current language
     */
    public static function setLang($lang) {
        if (in_array($lang, ['tr', 'en', 'ru'])) {
            self::$currentLang = $lang;
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['lang'] = $lang;
            self::loadTranslations();
        }
    }
    
    /**
     * Get current language
     */
    public static function getCurrentLang() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['lang'])) {
            self::$currentLang = $_SESSION['lang'];
        } elseif (isset($_GET['lang']) && in_array($_GET['lang'], ['tr', 'en', 'ru'])) {
            self::$currentLang = $_GET['lang'];
            $_SESSION['lang'] = self::$currentLang;
        }
        
        return self::$currentLang;
    }
    
    /**
     * Load translations for current language
     */
    private static function loadTranslations() {
        if (self::$loaded) return;
        
        $langFile = __DIR__ . '/../lang/' . self::getCurrentLang() . '.php';
        
        if (file_exists($langFile)) {
            self::$translations = include $langFile;
        } else {
            // Fallback to Turkish
            $fallbackFile = __DIR__ . '/../lang/tr.php';
            if (file_exists($fallbackFile)) {
                self::$translations = include $fallbackFile;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get translation for given key
     */
    public static function get($key, $default = null) {
        self::loadTranslations();
        
        if (isset(self::$translations[$key])) {
            return self::$translations[$key];
        }
        
        return $default ?: $key;
    }
    
    /**
     * Check if translation exists
     */
    public static function has($key) {
        self::loadTranslations();
        return isset(self::$translations[$key]);
    }
}

// Helper function for easier access
function __($key, $default = null) {
    return LangHelper::get($key, $default);
}
?>