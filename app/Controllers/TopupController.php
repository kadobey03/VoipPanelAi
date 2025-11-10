<?php
namespace App\Controllers;

use App\Helpers\DB;
use App\Helpers\TronClient;

class TopupController {
    private function start(){ if (session_status()===PHP_SESSION_NONE) session_start(); if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    public function index(){
        $this->start();
        $db = DB::conn();
        if ($this->isSuper()) {
            $res = $db->query('
                SELECT tr.*, g.name AS group_name, u.login AS user_login,
                       cp.currency, cp.blockchain, cp.network, cp.wallet_address,
                       cp.transaction_hash, cp.amount_received, cp.status as crypto_status,
                       cp.confirmations, cp.expired_at as crypto_expired_at
                FROM topup_requests tr
                LEFT JOIN groups g ON g.id=tr.group_id
                LEFT JOIN users u ON u.id=tr.user_id
                LEFT JOIN crypto_payments cp ON cp.id=tr.crypto_payment_id
                ORDER BY tr.id DESC
            ');
        } else {
            $gid = (int)($_SESSION['user']['group_id'] ?? 0);
            $stmt=$db->prepare('
                SELECT tr.*, g.name AS group_name, u.login AS user_login,
                       cp.currency, cp.blockchain, cp.network, cp.wallet_address,
                       cp.transaction_hash, cp.amount_received, cp.status as crypto_status,
                       cp.confirmations, cp.expired_at as crypto_expired_at
                FROM topup_requests tr
                LEFT JOIN groups g ON g.id=tr.group_id
                LEFT JOIN users u ON u.id=tr.user_id
                LEFT JOIN crypto_payments cp ON cp.id=tr.crypto_payment_id
                WHERE tr.group_id=?
                ORDER BY tr.id DESC
            ');
            $stmt->bind_param('i', $gid); $stmt->execute(); $res=$stmt->get_result(); $stmt->close();
        }
        $items=[]; while($row=$res->fetch_assoc()){$items[]=$row;}
        require __DIR__.'/../Views/topups/index.php';
    }

    public function approve(){
        $this->start(); if (!$this->isSuper()) { http_response_code(403); echo 'Yetkisiz'; return; }
        $id = (int)($_POST['id'] ?? 0);
        $db = DB::conn();
        // fetch request
        $stmt=$db->prepare('SELECT * FROM topup_requests WHERE id=? AND status="pending"');
        $stmt->bind_param('i',$id); $stmt->execute(); $req=$stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$req) { \App\Helpers\Url::redirect('/topups'); }
        $db->begin_transaction();
        try{
            // update group balance
            $stmt=$db->prepare('UPDATE groups SET balance=balance+? WHERE id=?');
            $stmt->bind_param('di', $req['amount'], $req['group_id']); $stmt->execute(); $stmt->close();
            // insert transaction
            $desc = 'Topup method: '.($req['method'] ?? ''); $type='topup'; $ref = 'req#'.$req['id'];
            $stmt=$db->prepare('INSERT INTO transactions (group_id, type, amount, reference, description) VALUES (?,?,?,?,?)');
            $amt = (float)$req['amount']; $stmt->bind_param('isdss', $req['group_id'], $type, $amt, $ref, $desc); $stmt->execute(); $stmt->close();
            // mark approved
            $now = date('Y-m-d H:i:s'); $adminId = (int)($_SESSION['user']['id'] ?? 0);
            $stmt=$db->prepare('UPDATE topup_requests SET status="approved", approved_at=?, approved_by=? WHERE id=?');
            $stmt->bind_param('sii', $now, $adminId, $id); $stmt->execute(); $stmt->close();
            $db->commit();
        } catch (\Throwable $e) { $db->rollback(); }
        \App\Helpers\Url::redirect('/topups');
    }

    public function reject(){
        $this->start(); if (!$this->isSuper()) { http_response_code(403); echo 'Yetkisiz'; return; }
        $id = (int)($_POST['id'] ?? 0);
        $db = DB::conn(); $now = date('Y-m-d H:i:s'); $adminId = (int)($_SESSION['user']['id'] ?? 0);
        $stmt=$db->prepare('UPDATE topup_requests SET status="rejected", approved_at=?, approved_by=? WHERE id=? AND status="pending"');
        $stmt->bind_param('sii', $now, $adminId, $id); $stmt->execute(); $stmt->close();
        \App\Helpers\Url::redirect('/topups');
    }

    public function receipt(){
        $this->start();
        $id = (int)($_GET['id'] ?? 0);
        $db = DB::conn();
        $stmt=$db->prepare('SELECT tr.receipt_path, tr.group_id FROM topup_requests tr WHERE tr.id=?');
        $stmt->bind_param('i',$id); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close();
        if(!$row || empty($row['receipt_path'])){ http_response_code(404); echo 'Bulunamadı'; return; }
        // Permission: super admin or same group
        if (!($this->isSuper() || ((int)($_SESSION['user']['group_id'] ?? 0) === (int)$row['group_id']))) { http_response_code(403); echo 'Yetkisiz'; return; }
        $path = __DIR__.'/../../'.ltrim($row['receipt_path'],'/');
        if (!is_file($path)) { http_response_code(404); echo 'Dosya yok'; return; }
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        header('Content-Type: '.$mime);
        header('Content-Length: '.filesize($path));
        readfile($path);
    }
    
    /**
     * Check cryptocurrency payment status via AJAX
     */
    public function checkCryptoStatus() {
        $this->start();
        header('Content-Type: application/json');
        
        $paymentId = (int)($_GET['payment_id'] ?? 0);
        if (!$paymentId) {
            echo json_encode(['error' => 'Invalid payment ID']);
            return;
        }
        
        $db = DB::conn();
        
        // Get crypto payment details
        $stmt = $db->prepare(
            'SELECT cp.*, tr.group_id
             FROM crypto_payments cp
             JOIN topup_requests tr ON tr.crypto_payment_id = cp.id
             WHERE cp.id = ?'
        );
        $stmt->bind_param('i', $paymentId);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$payment) {
            echo json_encode(['error' => 'Payment not found']);
            return;
        }
        
        // Check permission
        if (!$this->isSuper() && (int)($_SESSION['user']['group_id'] ?? 0) !== (int)$payment['group_id']) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        // Get required confirmations
        $requiredConfirmations = $this->getSetting('crypto_required_confirmations') ?: 19;
        
        echo json_encode([
            'status' => $payment['status'],
            'amount_received' => $payment['amount_received'],
            'transaction_hash' => $payment['transaction_hash'],
            'confirmations' => $payment['confirmations'],
            'required_confirmations' => $requiredConfirmations,
            'expired_at' => $payment['expired_at']
        ]);
    }
    
    /**
     * Get TRON transaction details
     */
    public function getCryptoTransactionDetails() {
        $this->start();
        header('Content-Type: application/json');
        
        $txHash = $_GET['tx_hash'] ?? '';
        if (!$txHash) {
            echo json_encode(['error' => 'Invalid transaction hash']);
            return;
        }
        
        try {
            $tronClient = new TronClient($this->getSetting('crypto_tron_api_key'));
            
            $txInfo = $tronClient->getTransactionInfo($txHash);
            $txDetails = $tronClient->getTransactionById($txHash);
            
            if ($txInfo && $txDetails) {
                echo json_encode([
                    'success' => true,
                    'transaction' => [
                        'hash' => $txHash,
                        'block_number' => $txInfo['blockNumber'] ?? null,
                        'block_timestamp' => $txInfo['blockTimeStamp'] ?? null,
                        'fee' => ($txInfo['fee'] ?? 0) / 1000000, // Convert from SUN to TRX
                        'result' => $txInfo['result'] ?? 'UNKNOWN',
                        'confirmations' => $this->getTransactionConfirmations($txHash, $tronClient)
                    ]
                ]);
            } else {
                echo json_encode(['error' => 'Transaction not found']);
            }
        } catch (\Exception $e) {
            echo json_encode(['error' => 'Error fetching transaction: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Manual cryptocurrency approval (for super admin)
     */
    public function approveCrypto() {
        $this->start();
        if (!$this->isSuper()) {
            http_response_code(403);
            echo 'Yetkisiz';
            return;
        }
        
        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $txHash = trim($_POST['transaction_hash'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        
        if (!$paymentId || !$txHash || $amount <= 0) {
            \App\Helpers\Url::redirect('/topups?error=invalid_data');
            return;
        }
        
        $db = DB::conn();
        $db->begin_transaction();
        
        try {
            // Get payment details
            $stmt = $db->prepare(
                'SELECT cp.*, tr.group_id, tr.user_id
                 FROM crypto_payments cp
                 JOIN topup_requests tr ON tr.crypto_payment_id = cp.id
                 WHERE cp.id = ? AND cp.status IN (?, ?)'
            );
            $statusPending = 'pending';
            $statusConfirming = 'confirming';
            $stmt->bind_param('iss', $paymentId, $statusPending, $statusConfirming);
            $stmt->execute();
            $payment = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$payment) {
                throw new \Exception('Payment not found or already processed');
            }
            
            // Update crypto payment
            $stmt = $db->prepare(
                'UPDATE crypto_payments
                 SET status = ?, amount_received = ?, transaction_hash = ?, confirmed_at = NOW()
                 WHERE id = ?'
            );
            $status = 'completed';
            $stmt->bind_param('sdsi', $status, $amount, $txHash, $paymentId);
            $stmt->execute();
            $stmt->close();
            
            // Update group balance
            $stmt = $db->prepare('UPDATE groups SET balance = balance + ? WHERE id = ?');
            $stmt->bind_param('di', $amount, $payment['group_id']);
            $stmt->execute();
            $stmt->close();
            
            // Create transaction record
            $stmt = $db->prepare(
                'INSERT INTO transactions (group_id, type, amount, reference, description)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $type = 'topup';
            $reference = 'crypto#' . $paymentId;
            $description = 'USDT TRC20 manual approval - TX: ' . substr($txHash, 0, 10) . '...';
            
            $stmt->bind_param('isdss', $payment['group_id'], $type, $amount, $reference, $description);
            $stmt->execute();
            $stmt->close();
            
            // Update topup request
            $adminId = (int)($_SESSION['user']['id'] ?? 0);
            $now = date('Y-m-d H:i:s');
            $stmt = $db->prepare(
                'UPDATE topup_requests
                 SET status = ?, approved_at = ?, approved_by = ?, crypto_transaction_hash = ?
                 WHERE crypto_payment_id = ?'
            );
            $requestStatus = 'approved';
            $stmt->bind_param('ssisi', $requestStatus, $now, $adminId, $txHash, $paymentId);
            $stmt->execute();
            $stmt->close();
            
            $db->commit();
            \App\Helpers\Url::redirect('/topups?success=crypto_approved');
            
        } catch (\Exception $e) {
            $db->rollback();
            error_log('CryptoApproval error: ' . $e->getMessage());
            \App\Helpers\Url::redirect('/topups?error=' . urlencode($e->getMessage()));
        }
    }
    
    /**
     * Get transaction confirmation count
     */
    private function getTransactionConfirmations($txHash, $tronClient) {
        try {
            $txInfo = $tronClient->getTransactionInfo($txHash);
            
            if ($txInfo && isset($txInfo['blockNumber'])) {
                $currentBlock = $tronClient->getCurrentBlock();
                if ($currentBlock && isset($currentBlock['block_header']['raw_data']['number'])) {
                    return $currentBlock['block_header']['raw_data']['number'] - $txInfo['blockNumber'];
                }
            }
            
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get setting value
     */
    private function getSetting($name) {
        try {
            $db = DB::conn();
            $stmt = $db->prepare('SELECT value FROM settings WHERE name = ?');
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
