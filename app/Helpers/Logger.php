<?php
namespace App\Helpers;
class Logger {
    private static $logFile = __DIR__ . '/../../storage/logs/app.log';
    public static function log($message) {
        $date = date('Y-m-d H:i:s');
        file_put_contents(self::$logFile, "[$date] $message\n", FILE_APPEND);
    }
}
