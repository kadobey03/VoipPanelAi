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
        
        // Pagination parameters
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 10))); // Default 10, max 100
        $offset = ($page - 1) * $limit;
        
        // Build base WHERE clause
        $whereClause = '';
        $params = [];
        $paramTypes = '';
        
        if ($this->isSuper()) {
            if (isset($_GET['group_id']) && $_GET['group_id']!=='') {
                $groupId=(int)$_GET['group_id'];
                $whereClause = 'WHERE t.group_id = ?';
                $params[] = $groupId;
                $paramTypes .= 'i';
            }
        } else {
            $groupId = (int)($_SESSION['user']['group_id'] ?? 0);
            $whereClause = 'WHERE t.group_id = ?';
            $params[] = $groupId;
            $paramTypes .= 'i';
        }
        
        // Get total count
        $countQuery = "
            SELECT COUNT(*) as total
            FROM transactions t
            LEFT JOIN groups g ON g.id = t.group_id
            $whereClause
        ";
        
        if ($params) {
            $countStmt = $db->prepare($countQuery);
            $countStmt->bind_param($paramTypes, ...$params);
            $countStmt->execute();
            $totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
            $countStmt->close();
        } else {
            $totalRecords = $db->query($countQuery)->fetch_assoc()['total'];
        }
        
        $totalPages = ceil($totalRecords / $limit);
        
        // Get paginated data
        $dataQuery = "
            SELECT t.*, g.name AS group_name,
                   cp.currency, cp.blockchain, cp.network,
                   cp.transaction_hash as crypto_tx_hash,
                   cp.wallet_address, cp.confirmations,
                   CASE
                       WHEN t.reference LIKE 'crypto#%' THEN 'cryptocurrency'
                       ELSE 'traditional'
                   END as payment_type
            FROM transactions t
            LEFT JOIN groups g ON g.id = t.group_id
            LEFT JOIN crypto_payments cp ON cp.id = CAST(SUBSTRING(t.reference, 8) AS UNSIGNED) AND t.reference LIKE 'crypto#%'
            $whereClause
            ORDER BY t.id DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        $paramTypes .= 'ii';
        
        if ($params) {
            $stmt = $db->prepare($dataQuery);
            $stmt->bind_param($paramTypes, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $dataQuery .= " LIMIT $limit OFFSET $offset";
            $res = $db->query($dataQuery);
        }
        
        while($row=$res->fetch_assoc()){$items[]=$row;}
        if (isset($stmt)) $stmt->close();
        
        // Pagination data for view
        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'limit' => $limit,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'prev_page' => max(1, $page - 1),
            'next_page' => min($totalPages, $page + 1)
        ];
        
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

