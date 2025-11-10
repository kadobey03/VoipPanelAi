<?php
namespace App\Controllers;

use App\Helpers\DB;

class TransactionsController {
    private function start(){ if (session_status()===PHP_SESSION_NONE) session_start(); if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    public function index(){
        $this->start();
        $db = DB::conn();
        $items=[]; $groupId=null;
        
        if ($this->isSuper()) {
            if (isset($_GET['group_id']) && $_GET['group_id']!=='') { $groupId=(int)$_GET['group_id']; }
            if ($groupId) {
                $stmt=$db->prepare('
                    SELECT t.*, g.name AS group_name,
                           cp.currency, cp.blockchain, cp.network,
                           cp.transaction_hash as crypto_tx_hash,
                           cp.wallet_address, cp.confirmations,
                           CASE
                               WHEN t.reference LIKE "crypto#%" THEN "cryptocurrency"
                               ELSE "traditional"
                           END as payment_type
                    FROM transactions t
                    LEFT JOIN groups g ON g.id = t.group_id
                    LEFT JOIN crypto_payments cp ON cp.id = CAST(SUBSTRING(t.reference, 8) AS UNSIGNED) AND t.reference LIKE "crypto#%"
                    WHERE t.group_id = ?
                    ORDER BY t.id DESC
                ');
                $stmt->bind_param('i',$groupId); $stmt->execute(); $res=$stmt->get_result();
            } else {
                $res = $db->query('
                    SELECT t.*, g.name AS group_name,
                           cp.currency, cp.blockchain, cp.network,
                           cp.transaction_hash as crypto_tx_hash,
                           cp.wallet_address, cp.confirmations,
                           CASE
                               WHEN t.reference LIKE "crypto#%" THEN "cryptocurrency"
                               ELSE "traditional"
                           END as payment_type
                    FROM transactions t
                    LEFT JOIN groups g ON g.id = t.group_id
                    LEFT JOIN crypto_payments cp ON cp.id = CAST(SUBSTRING(t.reference, 8) AS UNSIGNED) AND t.reference LIKE "crypto#%"
                    ORDER BY t.id DESC
                ');
            }
        } else {
            $groupId = (int)($_SESSION['user']['group_id'] ?? 0);
            $stmt=$db->prepare('
                SELECT t.*, g.name AS group_name,
                       cp.currency, cp.blockchain, cp.network,
                       cp.transaction_hash as crypto_tx_hash,
                       cp.wallet_address, cp.confirmations,
                       CASE
                           WHEN t.reference LIKE "crypto#%" THEN "cryptocurrency"
                           ELSE "traditional"
                       END as payment_type
                FROM transactions t
                LEFT JOIN groups g ON g.id = t.group_id
                LEFT JOIN crypto_payments cp ON cp.id = CAST(SUBSTRING(t.reference, 8) AS UNSIGNED) AND t.reference LIKE "crypto#%"
                WHERE t.group_id = ?
                ORDER BY t.id DESC
            ');
            $stmt->bind_param('i',$groupId); $stmt->execute(); $res=$stmt->get_result();
        }
        
        while($row=$res->fetch_assoc()){$items[]=$row;}
        if (isset($stmt)) $stmt->close();
        require __DIR__.'/../Views/transactions/index.php';
    }
    
    /**
     * Get cryptocurrency transaction details
     */
    public function cryptoDetails() {
        $this->start();
        $transactionId = (int)($_GET['id'] ?? 0);
        
        if (!$transactionId) {
            http_response_code(404);
            echo 'Transaction not found';
            return;
        }
        
        $db = DB::conn();
        
        // Get transaction with crypto details
        $stmt = $db->prepare('
            SELECT t.*, g.name AS group_name, u.login as user_login,
                   cp.currency, cp.blockchain, cp.network,
                   cp.transaction_hash, cp.wallet_address,
                   cp.confirmations, cp.amount_received,
                   cp.created_at as crypto_created_at,
                   cp.confirmed_at as crypto_confirmed_at
            FROM transactions t
            LEFT JOIN groups g ON g.id = t.group_id
            LEFT JOIN crypto_payments cp ON cp.id = CAST(SUBSTRING(t.reference, 8) AS UNSIGNED) AND t.reference LIKE "crypto#%"
            LEFT JOIN users u ON u.group_id = t.group_id
            WHERE t.id = ? AND t.reference LIKE "crypto#%"
        ');
        
        $stmt->bind_param('i', $transactionId);
        $stmt->execute();
        $transaction = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$transaction) {
            http_response_code(404);
            echo 'Crypto transaction not found';
            return;
        }
        
        // Check permission
        if (!$this->isSuper() && (int)($_SESSION['user']['group_id'] ?? 0) !== (int)$transaction['group_id']) {
            http_response_code(403);
            echo 'Unauthorized';
            return;
        }
        
        require __DIR__.'/../Views/transactions/crypto_details.php';
    }
    
    /**
     * Export transactions to CSV
     */
    public function export() {
        $this->start();
        
        $db = DB::conn();
        $groupId = null;
        
        if ($this->isSuper()) {
            if (isset($_GET['group_id']) && $_GET['group_id'] !== '') {
                $groupId = (int)$_GET['group_id'];
            }
        } else {
            $groupId = (int)($_SESSION['user']['group_id'] ?? 0);
        }
        
        // Prepare query based on permissions
        if ($this->isSuper() && !$groupId) {
            $stmt = $db->query('
                SELECT t.*, g.name AS group_name,
                       cp.currency, cp.blockchain, cp.transaction_hash as crypto_tx_hash
                FROM transactions t
                LEFT JOIN groups g ON g.id = t.group_id
                LEFT JOIN crypto_payments cp ON cp.id = CAST(SUBSTRING(t.reference, 8) AS UNSIGNED) AND t.reference LIKE "crypto#%"
                ORDER BY t.created_at DESC
            ');
            $transactions = $stmt->fetch_all(MYSQLI_ASSOC);
        } else {
            $stmt = $db->prepare('
                SELECT t.*, g.name AS group_name,
                       cp.currency, cp.blockchain, cp.transaction_hash as crypto_tx_hash
                FROM transactions t
                LEFT JOIN groups g ON g.id = t.group_id
                LEFT JOIN crypto_payments cp ON cp.id = CAST(SUBSTRING(t.reference, 8) AS UNSIGNED) AND t.reference LIKE "crypto#%"
                WHERE t.group_id = ?
                ORDER BY t.created_at DESC
            ');
            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=transactions_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        $headers = [
            'ID', 'Group', 'Type', 'Amount', 'Reference', 'Description',
            'Payment Type', 'Currency', 'Blockchain', 'Crypto TX Hash', 'Date'
        ];
        fputcsv($output, $headers);
        
        // CSV data
        foreach ($transactions as $transaction) {
            $paymentType = strpos($transaction['reference'], 'crypto#') === 0 ? 'Cryptocurrency' : 'Traditional';
            
            $row = [
                $transaction['id'],
                $transaction['group_name'],
                $transaction['type'],
                $transaction['amount'],
                $transaction['reference'],
                $transaction['description'],
                $paymentType,
                $transaction['currency'] ?? '',
                $transaction['blockchain'] ?? '',
                $transaction['crypto_tx_hash'] ?? '',
                $transaction['created_at']
            ];
            
            fputcsv($output, $row);
        }
        
        fclose($output);
    }
}

