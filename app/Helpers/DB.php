<?php
namespace App\Helpers;

class DB {
    private static $conn = null;

    public static function conn(): \mysqli {
        if (self::$conn instanceof \mysqli) {
            return self::$conn;
        }
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USERNAME') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';
        $db   = getenv('DB_DATABASE') ?: '';

        $mysqli = @new \mysqli($host, $user, $pass, $db);
        if ($mysqli->connect_errno) {
            throw new \RuntimeException('DB connection failed: '.$mysqli->connect_error);
        }
        $mysqli->set_charset('utf8mb4');
        self::$conn = $mysqli;
        return self::$conn;
    }

    public static function close(): void {
        if (self::$conn instanceof \mysqli) {
            self::$conn->close();
        }
        self::$conn = null;
    }
}

