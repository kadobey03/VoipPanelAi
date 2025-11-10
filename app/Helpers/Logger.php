<?php
namespace App\Helpers;
class Logger {
    private static $logFile = __DIR__ . '/../../storage/logs/app.log';
    
    private static function ensurePath(){
        $dir = dirname(self::$logFile);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
    }
    
    public static function log($message) {
        self::ensurePath();
        $date = date('Y-m-d H:i:s');
        file_put_contents(self::$logFile, "[$date] $message\n", FILE_APPEND);
    }
    
    public static function info($message) {
        self::logWithLevel('INFO', $message);
    }
    
    public static function error($message) {
        self::logWithLevel('ERROR', $message);
    }
    
    public static function warning($message) {
        self::logWithLevel('WARNING', $message);
    }
    
    public static function debug($message) {
        self::logWithLevel('DEBUG', $message);
    }
    
    private static function logWithLevel($level, $message) {
        self::ensurePath();
        $date = date('Y-m-d H:i:s');
        file_put_contents(self::$logFile, "[$date] [$level] $message\n", FILE_APPEND);
    }
}
