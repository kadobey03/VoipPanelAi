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

        // calls cost/margin columns
        if (!$hasCol('calls', 'cost_api')) {
            $db->query('ALTER TABLE `calls` ADD COLUMN `cost_api` DECIMAL(12,6) DEFAULT 0.000000 AFTER `user_id`');
        }
        if (!$hasCol('calls', 'margin_percent')) {
            $db->query('ALTER TABLE `calls` ADD COLUMN `margin_percent` DECIMAL(5,2) DEFAULT 0.00 AFTER `cost_api`');
        }
        if (!$hasCol('calls', 'amount_charged')) {
            $db->query('ALTER TABLE `calls` ADD COLUMN `amount_charged` DECIMAL(12,6) DEFAULT 0.000000 AFTER `margin_percent`');
        }

        // transactions table
        $hasTransactions = false;
        $res = $db->query("SHOW TABLES LIKE 'transactions'");
        if ($res) { $hasTransactions = $res->num_rows > 0; $res->free(); }
        if (!$hasTransactions) {
            $db->query("CREATE TABLE IF NOT EXISTS `transactions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `group_id` INT NOT NULL,
                `type` ENUM('topup','debit_call','adjust') NOT NULL,
                `amount` DECIMAL(12,4) NOT NULL,
                `reference` VARCHAR(64) NULL,
                `description` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        // payment_methods table
        $res = $db->query("SHOW TABLES LIKE 'payment_methods'");
        if (!$res || $res->num_rows === 0) {
            $db->query("CREATE TABLE IF NOT EXISTS `payment_methods` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `method_type` VARCHAR(50) NOT NULL,
                `details` TEXT NULL,
                `active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else { $res->free(); }

        // topup_requests table
        $res = $db->query("SHOW TABLES LIKE 'topup_requests'");
        if (!$res || $res->num_rows === 0) {
            $db->query("CREATE TABLE IF NOT EXISTS `topup_requests` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `group_id` INT NOT NULL,
                `user_id` INT NOT NULL,
                `amount` DECIMAL(12,4) NOT NULL,
                `method` VARCHAR(50) NOT NULL,
                `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
                `note` VARCHAR(255) NULL,
                `reference` VARCHAR(64) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `approved_at` DATETIME NULL,
                `approved_by` INT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else { $res->free(); }
    }
}
