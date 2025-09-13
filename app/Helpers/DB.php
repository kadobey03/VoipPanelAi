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
        // Optionally align MySQL session timezone (e.g., '+03:00')
        $dbTz = getenv('DB_TIMEZONE') ?: '';
        if ($dbTz !== '') {
            @$mysqli->query("SET time_zone='".$mysqli->real_escape_string($dbTz)."'");
        }
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
                `type` ENUM('topup','debit_call','debit_call_stat','adjust') NOT NULL,
                `amount` DECIMAL(12,4) NOT NULL,
                `reference` VARCHAR(64) NULL,
                `description` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            // Alter enum to include debit_call_stat
            $db->query("ALTER TABLE `transactions` MODIFY COLUMN `type` ENUM('topup','debit_call','debit_call_stat','adjust') NOT NULL");
        }

        // call_stats table
        $hasCallStats = false;
        $res = $db->query("SHOW TABLES LIKE 'call_stats'");
        if ($res) { $hasCallStats = $res->num_rows > 0; $res->free(); }
        if (!$hasCallStats) {
            $db->query("CREATE TABLE IF NOT EXISTS `call_stats` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_login` VARCHAR(50),
                `group_name` VARCHAR(100),
                `calls` INT DEFAULT 0,
                `answer` INT DEFAULT 0,
                `unique_numbers` INT DEFAULT 0,
                `duration` INT DEFAULT 0,
                `billsec` INT DEFAULT 0,
                `talk_percent` DECIMAL(5,2) DEFAULT 0.00,
                `jackpot` INT DEFAULT 0,
                `unique_jackpot` INT DEFAULT 0,
                `spy_calls` INT DEFAULT 0,
                `spy_duration` INT DEFAULT 0,
                `promt_calls` INT DEFAULT 0,
                `promt_duration` INT DEFAULT 0,
                `echo_calls` INT DEFAULT 0,
                `echo_duration` INT DEFAULT 0,
                `cost` DECIMAL(12,6) DEFAULT 0.000000,
                `margin_cost` DECIMAL(12,6) DEFAULT 0.000000,
                `voip_exten` VARCHAR(20),
                `date_from` DATETIME,
                `date_to` DATETIME,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_stat` (`user_login`, `date_from`, `date_to`)
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
                `fee_percent` DECIMAL(5,2) DEFAULT 0.00,
                `fee_fixed` DECIMAL(12,4) DEFAULT 0.0000,
                `active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else { if($res) $res->free();
            // add fee columns if missing
            $hasCol = function(string $table, string $col) use ($db): bool { $r=$db->query("SHOW COLUMNS FROM `$table` LIKE '".$db->real_escape_string($col)."'"); if(!$r) return false; $e=$r->num_rows>0; $r->free(); return $e; };
            if(!$hasCol('payment_methods','fee_percent')){ $db->query('ALTER TABLE `payment_methods` ADD COLUMN `fee_percent` DECIMAL(5,2) DEFAULT 0.00 AFTER `details`'); }
            if(!$hasCol('payment_methods','fee_fixed')){ $db->query('ALTER TABLE `payment_methods` ADD COLUMN `fee_fixed` DECIMAL(12,4) DEFAULT 0.0000 AFTER `fee_percent`'); }
        }

        // topup_requests table
        $res = $db->query("SHOW TABLES LIKE 'topup_requests'");
        if (!$res || $res->num_rows === 0) {
            $db->query("CREATE TABLE IF NOT EXISTS `topup_requests` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `group_id` INT NOT NULL,
                `user_id` INT NOT NULL,
                `amount` DECIMAL(12,4) NOT NULL,
                `method` VARCHAR(100) NOT NULL,
                `method_id` INT NULL,
                `fee_percent` DECIMAL(5,2) DEFAULT 0.00,
                `fee_fixed` DECIMAL(12,4) DEFAULT 0.0000,
                `charge_total` DECIMAL(12,4) DEFAULT 0.0000,
                `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
                `note` VARCHAR(255) NULL,
                `reference` VARCHAR(64) NULL,
                `receipt_path` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `approved_at` DATETIME NULL,
                `approved_by` INT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else { if ($res) $res->free();
            // add missing columns
            $hasCol = function(string $table, string $col) use ($db): bool { $r=$db->query("SHOW COLUMNS FROM `$table` LIKE '".$db->real_escape_string($col)."'"); if(!$r) return false; $e=$r->num_rows>0; $r->free(); return $e; };
            if (!$hasCol('topup_requests','method_id')) { $db->query('ALTER TABLE `topup_requests` ADD COLUMN `method_id` INT NULL AFTER `method`'); }
            if (!$hasCol('topup_requests','fee_percent')) { $db->query('ALTER TABLE `topup_requests` ADD COLUMN `fee_percent` DECIMAL(5,2) DEFAULT 0.00 AFTER `method_id`'); }
            if (!$hasCol('topup_requests','fee_fixed')) { $db->query('ALTER TABLE `topup_requests` ADD COLUMN `fee_fixed` DECIMAL(12,4) DEFAULT 0.0000 AFTER `fee_percent`'); }
            if (!$hasCol('topup_requests','charge_total')) { $db->query('ALTER TABLE `topup_requests` ADD COLUMN `charge_total` DECIMAL(12,4) DEFAULT 0.0000 AFTER `fee_fixed`'); }
            if (!$hasCol('topup_requests','receipt_path')) { $db->query('ALTER TABLE `topup_requests` ADD COLUMN `receipt_path` VARCHAR(255) NULL AFTER `reference`'); }

        }

        // users table migrations
        if (!$hasCol('users', 'agent_id')) {
            $db->query('ALTER TABLE `users` ADD COLUMN `agent_id` INT NULL AFTER `group_id`');
        }
        // Update role enum to include groupmember
        $db->query('ALTER TABLE `users` MODIFY COLUMN `role` ENUM(\'superadmin\',\'groupadmin\',\'user\',\'groupmember\') DEFAULT \'groupmember\'');
        // Update empty or null roles to groupmember
        $db->query('UPDATE users SET role=\'groupmember\' WHERE role=\'\' OR role IS NULL');
    }
}
