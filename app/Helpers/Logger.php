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
}
