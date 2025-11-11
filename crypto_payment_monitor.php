<?php
/**
 * TRON TRC20 USDT Payment Monitor
 * Bu script, bekleyen cryptocurrency ödemelerini izler ve onaylar
 * Cron job olarak 2 dakikada bir çalıştırılmalıdır:
 * 0,2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52,54,56,58 * * * * /usr/bin/php /path/to/crypto_payment_monitor.php
 */

require_once __DIR__ . '/config/bootstrap.php';

use App\Helpers\DB;
use App\Helpers\TronClient;
use App\Helpers\Logger;
use App\Helpers\TelegramNotifier;

class CryptoPaymentMonitor {
    private $db;
    private $tronClient;
    private $logger;
    
    public function __construct() {
        $this->db = DB::conn();
        
        // Get TRON API key from settings
        $apiKey = $this->getSetting('crypto_tron_api_key');
        $this->tronClient = new TronClient($apiKey);
        
        $this->logger = new Logger('crypto_payment_monitor');
        $this->logger->info('CryptoPaymentMonitor started');
    }
    
    /**
     * Main monitoring function
     */
    public function monitor() {
        try {
            // Get pending payments
            $pendingPayments = $this->getPendingPayments();
            $this->logger->info('Found ' . count($pendingPayments) . ' pending payments');
            
            foreach ($pendingPayments as $payment) {
                $this->processPayment($payment);
            }
            
            // Clean up expired payments
            $this->cleanupExpiredPayments();
            
        } catch (\Exception $e) {
            $this->logger->error('Monitor error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get pending cryptocurrency payments
     */
    private function getPendingPayments() {
        $stmt = $this->db->prepare(
            'SELECT cp.*, cw.address, cw.private_key_encrypted 
             FROM crypto_payments cp 
             JOIN crypto_wallets cw ON cp.wallet_id = cw.id 
             WHERE cp.status IN (?, ?) AND cp.expired_at > NOW()'
        );
        
        $statusPending = 'pending';
        $statusConfirming = 'confirming';
        $stmt->bind_param('ss', $statusPending, $statusConfirming);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Process individual payment
     */
    private function processPayment($payment) {
        try {
            $this->logger->info("Processing payment ID: {$payment['id']} for address: {$payment['address']}");
            
            // Get USDT TRC20 transactions for this address
            $transactions = $this->tronClient->getTRC20Transactions($payment['address']);
            
            foreach ($transactions as $tx) {
                if ($this->isValidPaymentTransaction($tx, $payment)) {
                    $this->confirmPayment($payment, $tx);
                    return; // Payment found and confirmed
                }
            }
            
            // If still confirming, check confirmation count
            if ($payment['status'] === 'confirming' && !empty($payment['transaction_hash'])) {
                $this->updateConfirmationCount($payment);
            }
            
        } catch (\Exception $e) {
            $this->logger->error("Error processing payment {$payment['id']}: " . $e->getMessage());
        }
    }
    
    /**
     * Check if transaction is valid for payment
     */
    private function isValidPaymentTransaction($tx, $payment) {
        // Check if transaction is to our address
        if (strcasecmp($tx['to'] ?? '', $payment['address']) !== 0) {
            return false;
        }
        
        // Check if transaction is after payment creation
        $txTimestamp = ($tx['block_timestamp'] ?? 0) / 1000; // Convert to seconds
        $paymentTimestamp = strtotime($payment['created_at']);
        
        if ($txTimestamp < $paymentTimestamp) {
            return false;
        }
        
        // Check amount (USDT has 6 decimal places)
        $receivedAmount = (float)($tx['value'] ?? 0) / 1000000;
        $expectedAmount = (float)$payment['amount_requested'];
        
        // Allow 1% tolerance for amount matching
        $tolerance = $expectedAmount * 0.01;
        if (abs($receivedAmount - $expectedAmount) > $tolerance) {
            return false;
        }
        
        // Check if transaction hash is already used
        if ($this->isTransactionHashUsed($tx['transaction_id'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Confirm payment and update balances
     */
    private function confirmPayment($payment, $transaction) {
        $this->db->begin_transaction();
        
        try {
            $receivedAmount = (float)($transaction['value'] ?? 0) / 1000000;
            $txHash = $transaction['transaction_id'];
            
            // Get group info for Telegram notification
            $groupName = 'Bilinmeyen Grup';
            $balanceBefore = 0;
            if ($payment['group_id']) {
                $stmt = $this->db->prepare('SELECT name, balance FROM groups WHERE id = ?');
                $stmt->bind_param('i', $payment['group_id']);
                $stmt->execute();
                $groupResult = $stmt->get_result()->fetch_assoc();
                if ($groupResult) {
                    $groupName = $groupResult['name'];
                    $balanceBefore = (float)$groupResult['balance'];
                }
                $stmt->close();
            }
            
            // Update crypto payment status
            $stmt = $this->db->prepare(
                'UPDATE crypto_payments
                 SET status = ?, amount_received = ?, transaction_hash = ?, confirmed_at = NOW(), confirmations = ?
                 WHERE id = ?'
            );
            
            $status = 'completed';
            $confirmations = $this->getTransactionConfirmations($txHash);
            
            $stmt->bind_param('sdsii', $status, $receivedAmount, $txHash, $confirmations, $payment['id']);
            $stmt->execute();
            $stmt->close();
            
            // Update group balance
            $stmt = $this->db->prepare('UPDATE groups SET balance = balance + ? WHERE id = ?');
            $stmt->bind_param('di', $receivedAmount, $payment['group_id']);
            $stmt->execute();
            $stmt->close();
            
            // Create transaction record
            $stmt = $this->db->prepare(
                'INSERT INTO transactions (group_id, type, amount, reference, description)
                 VALUES (?, ?, ?, ?, ?)'
            );
            
            $type = 'topup';
            $reference = 'crypto#' . $payment['id'];
            $description = 'USDT TRC20 topup - TX: ' . substr($txHash, 0, 10) . '...';
            
            $stmt->bind_param('isdss', $payment['group_id'], $type, $receivedAmount, $reference, $description);
            $stmt->execute();
            $transactionId = $this->db->insert_id;
            $stmt->close();
            
            // Update topup request status
            $stmt = $this->db->prepare(
                'UPDATE topup_requests
                 SET status = ?, crypto_transaction_hash = ?
                 WHERE crypto_payment_id = ?'
            );
            
            $requestStatus = 'approved';
            $stmt->bind_param('ssi', $requestStatus, $txHash, $payment['id']);
            $stmt->execute();
            $stmt->close();
            
            $this->db->commit();
            
            // Send Telegram notification
            try {
                $balanceAfter = $balanceBefore + $receivedAmount;
                $telegram = new TelegramNotifier();
                $telegram->sendPaymentNotification($groupName, $receivedAmount, $payment['id'], $transactionId, $balanceBefore, $balanceAfter);
                $this->logger->info("Telegram notification sent for payment ID: {$payment['id']}");
            } catch (\Exception $e) {
                $this->logger->error("Telegram notification failed for payment {$payment['id']}: " . $e->getMessage());
                // Don't fail the payment if Telegram fails
            }
            
            $this->logger->info("Payment confirmed! ID: {$payment['id']}, Amount: {$receivedAmount} USDT, TX: {$txHash}");
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->logger->error("Error confirming payment {$payment['id']}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update confirmation count for pending transaction
     */
    private function updateConfirmationCount($payment) {
        $confirmations = $this->getTransactionConfirmations($payment['transaction_hash']);
        $requiredConfirmations = (int)($this->getSetting('crypto_required_confirmations') ?: 19);
        
        if ($confirmations >= $requiredConfirmations) {
            // Move to completed status
            $stmt = $this->db->prepare(
                'UPDATE crypto_payments SET confirmations = ?, status = ? WHERE id = ?'
            );
            $status = 'completed';
            $stmt->bind_param('isi', $confirmations, $status, $payment['id']);
            $stmt->execute();
            $stmt->close();
            
            $this->logger->info("Payment {$payment['id']} reached required confirmations: {$confirmations}");
        } else {
            // Just update confirmation count
            $stmt = $this->db->prepare('UPDATE crypto_payments SET confirmations = ? WHERE id = ?');
            $stmt->bind_param('ii', $confirmations, $payment['id']);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    /**
     * Get transaction confirmation count
     */
    private function getTransactionConfirmations($txHash) {
        try {
            $txInfo = $this->tronClient->getTransactionInfo($txHash);
            
            if ($txInfo && isset($txInfo['blockNumber'])) {
                $currentBlock = $this->tronClient->getCurrentBlock();
                if ($currentBlock && isset($currentBlock['block_header']['raw_data']['number'])) {
                    return $currentBlock['block_header']['raw_data']['number'] - $txInfo['blockNumber'];
                }
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->logger->error("Error getting confirmations for TX {$txHash}: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Check if transaction hash is already used
     */
    private function isTransactionHashUsed($txHash) {
        $stmt = $this->db->prepare(
            'SELECT id FROM crypto_payments WHERE transaction_hash = ? AND status = ?'
        );
        $status = 'completed';
        $stmt->bind_param('ss', $txHash, $status);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return (bool)$result;
    }
    
    /**
     * Clean up expired payments
     */
    private function cleanupExpiredPayments() {
        // First get expired payments for Telegram notification
        $stmt = $this->db->prepare(
            'SELECT cp.id, cp.group_id, cp.amount_requested, g.name as group_name
             FROM crypto_payments cp
             LEFT JOIN topup_requests tr ON tr.crypto_payment_id = cp.id
             LEFT JOIN groups g ON g.id = tr.group_id
             WHERE cp.status = ? AND cp.expired_at < NOW()'
        );
        $statusPending = 'pending';
        $stmt->bind_param('s', $statusPending);
        $stmt->execute();
        $expiredPayments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Update expired payments
        $stmt = $this->db->prepare(
            'UPDATE crypto_payments
             SET status = ?
             WHERE status = ? AND expired_at < NOW()'
        );
        
        $statusExpired = 'expired';
        $stmt->bind_param('ss', $statusExpired, $statusPending);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($affected > 0) {
            $this->logger->info("Cleaned up {$affected} expired payments");
            
            // Send Telegram notifications for expired payments
            foreach ($expiredPayments as $payment) {
                try {
                    $telegram = new TelegramNotifier();
                    $groupName = $payment['group_name'] ?: 'Bilinmeyen Grup';
                    $telegram->sendPaymentExpiredNotification($groupName, $payment['amount_requested'], $payment['id']);
                    $this->logger->info("Expired payment notification sent for ID: {$payment['id']}");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to send expired payment notification for ID {$payment['id']}: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Get setting value
     */
    private function getSetting($name) {
        try {
            $stmt = $this->db->prepare('SELECT value FROM settings WHERE name = ?');
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            return $result ? $result['value'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

// Script entry point
if (php_sapi_name() === 'cli') {
    echo "Starting TRON TRC20 Payment Monitor...\n";
    
    $monitor = new CryptoPaymentMonitor();
    $monitor->monitor();
    
    echo "Monitor completed.\n";
} else {
    // Web access protection
    http_response_code(403);
    echo 'This script can only be run from command line.';
}