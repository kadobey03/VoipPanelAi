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

    // Run idempotent lightweight migrations for schema updates without reinstall
    public static function migrate(): void {
        $db = self::conn();
        // Helper to check column existence (avoid prepared SHOW)
        $hasCol = function(string $table, string $col) use ($db): bool {
            $sql = "SHOW COLUMNS FROM `$table` LIKE '".$db->real_escape_string($col)."'";
            $res = $db->query($sql);
            if ($res === false) { return false; }
            $exists = $res->num_rows > 0;
            $res->free();
            return $exists;
        };

        // groups.api_group_id, groups.api_group_name
        if (!$hasCol('groups', 'api_group_id')) {
            $db->query('ALTER TABLE `groups` ADD COLUMN `api_group_id` INT NULL AFTER `balance`');
        }
        if (!$hasCol('groups', 'api_group_name')) {
            $db->query('ALTER TABLE `groups` ADD COLUMN `api_group_name` VARCHAR(100) NULL AFTER `api_group_id`');
        }
    }
}
