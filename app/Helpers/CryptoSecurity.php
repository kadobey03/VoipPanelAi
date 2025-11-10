<?php
namespace App\Helpers;

use App\Helpers\DB;
use App\Helpers\Logger;

class CryptoSecurity {
    private $db;
    private $logger;
    
    // Rate limiting settings
    const MAX_REQUESTS_PER_HOUR = 10;
    const MAX_REQUESTS_PER_DAY = 50;
    const SUSPICIOUS_AMOUNT_THRESHOLD = 1000; // USDT
    
    public function __construct() {
        $this->db = DB::conn();
        $this->logger = new Logger('crypto_security');
    }
    
    /**
     * Check if user can create new crypto payment
     */
    public function canCreatePayment($userId, $groupId, $amount) {
        try {
            // Check rate limits
            if (!$this->checkRateLimit($userId, $groupId)) {
                $this->logger->warning("Rate limit exceeded for user {$userId}, group {$groupId}");
                return ['allowed' => false, 'reason' => 'Rate limit exceeded. Too many payment requests.'];
            }
            
            // Check amount limits
            $amountCheck = $this->checkAmountLimits($amount);
            if (!$amountCheck['allowed']) {
                $this->logger->warning("Amount limit exceeded for user {$userId}: {$amount} USDT");
                return $amountCheck;
            }
            
            // Check for suspicious activity
            if ($this->detectSuspiciousActivity($userId, $groupId, $amount)) {
                $this->logger->error("Suspicious activity detected for user {$userId}, group {$groupId}, amount {$amount}");
                return ['allowed' => false, 'reason' => 'Payment blocked for security review.'];
            }
            
            return ['allowed' => true];
            
        } catch (\Exception $e) {
            $this->logger->error("Security check failed: " . $e->getMessage());
            return ['allowed' => false, 'reason' => 'Security check failed. Please try again.'];
        }
    }
    
    /**
     * Check rate limits for payment requests
     */
    private function checkRateLimit($userId, $groupId) {
        try {
            // Check hourly limit
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) as count 
                 FROM crypto_payments 
                 WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
            );
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $hourlyCount = $stmt->get_result()->fetch_assoc()['count'];
            $stmt->close();
            
            if ($hourlyCount >= self::MAX_REQUESTS_PER_HOUR) {
                return false;
            }
            
            // Check daily limit
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) as count 
                 FROM crypto_payments 
                 WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)'
            );
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $dailyCount = $stmt->get_result()->fetch_assoc()['count'];
            $stmt->close();
            
            if ($dailyCount >= self::MAX_REQUESTS_PER_DAY) {
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error("Rate limit check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check amount limits
     */
    private function checkAmountLimits($amount) {
        $minAmount = (float)$this->getSetting('crypto_usdt_min_amount', 1.0);
        $maxAmount = (float)$this->getSetting('crypto_usdt_max_amount', 10000.0);
        
        if ($amount < $minAmount) {
            return ['allowed' => false, 'reason' => "Minimum amount is {$minAmount} USDT"];
        }
        
        if ($amount > $maxAmount) {
            return ['allowed' => false, 'reason' => "Maximum amount is {$maxAmount} USDT"];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Detect suspicious activity
     */
    private function detectSuspiciousActivity($userId, $groupId, $amount) {
        try {
            // Check for unusually high amounts
            if ($amount > self::SUSPICIOUS_AMOUNT_THRESHOLD) {
                $this->logger->warning("High amount payment attempt: {$amount} USDT by user {$userId}");
                // Don't block but log for review
            }
            
            // Check for rapid successive payments
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) as count 
                 FROM crypto_payments 
                 WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)'
            );
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $recentCount = $stmt->get_result()->fetch_assoc()['count'];
            $stmt->close();
            
            if ($recentCount >= 3) {
                return true; // Block rapid requests
            }
            
            // Check for unusual patterns (multiple failed payments)
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) as count 
                 FROM crypto_payments 
                 WHERE user_id = ? AND status = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)'
            );
            $status = 'failed';
            $stmt->bind_param('is', $userId, $status);
            $stmt->execute();
            $failedCount = $stmt->get_result()->fetch_assoc()['count'];
            $stmt->close();
            
            if ($failedCount >= 5) {
                return true; // Block after multiple failures
            }
            
            return false;
            
        } catch (\Exception $e) {
            $this->logger->error("Suspicious activity check failed: " . $e->getMessage());
            return true; // Block on error for security
        }
    }
    
    /**
     * Validate TRON address format and security
     */
    public function validateTronAddress($address) {
        // Basic format validation
        if (!preg_match('/^T[A-Za-z0-9]{33}$/', $address)) {
            return ['valid' => false, 'reason' => 'Invalid TRON address format'];
        }
        
        // Check against known blacklisted addresses
        if ($this->isBlacklistedAddress($address)) {
            $this->logger->warning("Blacklisted TRON address detected: {$address}");
            return ['valid' => false, 'reason' => 'Address is blacklisted'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Check if address is blacklisted
     */
    private function isBlacklistedAddress($address) {
        try {
            $stmt = $this->db->prepare(
                'SELECT id FROM crypto_blacklist WHERE address = ? AND active = 1'
            );
            $stmt->bind_param('s', $address);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            return (bool)$result;
        } catch (\Exception $e) {
            // If blacklist table doesn't exist, assume address is clean
            return false;
        }
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent($eventType, $userId, $groupId, $details = []) {
        try {
            $this->logger->info("Security Event: {$eventType}", [
                'user_id' => $userId,
                'group_id' => $groupId,
                'details' => $details,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
            // Store in database for analysis
            $stmt = $this->db->prepare(
                'INSERT INTO security_logs (event_type, user_id, group_id, details, ip_address, user_agent) 
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            
            $detailsJson = json_encode($details);
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt->bind_param('siisss', $eventType, $userId, $groupId, $detailsJson, $ip, $userAgent);
            $stmt->execute();
            $stmt->close();
            
        } catch (\Exception $e) {
            // Don't throw error for logging failures
            error_log('Security log failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate payment amount against blockchain
     */
    public function validatePaymentAmount($expectedAmount, $receivedAmount, $tolerance = 0.01) {
        $difference = abs($expectedAmount - $receivedAmount);
        $toleranceAmount = $expectedAmount * $tolerance;
        
        if ($difference <= $toleranceAmount) {
            return ['valid' => true];
        }
        
        return [
            'valid' => false, 
            'reason' => "Amount mismatch. Expected: {$expectedAmount}, Received: {$receivedAmount}"
        ];
    }
    
    /**
     * Encrypt sensitive data
     */
    public static function encrypt($data, $key = null) {
        if (!$key) {
            $key = self::getEncryptionKey();
        }
        
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decrypt($encryptedData, $key = null) {
        if (!$key) {
            $key = self::getEncryptionKey();
        }
        
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Get encryption key
     */
    private static function getEncryptionKey() {
        return hash('sha256', 'voip_crypto_security_' . ($_ENV['APP_KEY'] ?? 'default_key'), true);
    }
    
    /**
     * Get setting value
     */
    private function getSetting($name, $default = null) {
        try {
            $stmt = $this->db->prepare('SELECT value FROM settings WHERE name = ?');
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            return $result ? $result['value'] : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}