<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/bootstrap.php';

use App\Helpers\DB;
use App\Helpers\TronClient;

// Check if user is authenticated
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$paymentId = (int)($input['payment_id'] ?? 0);
$walletAddress = $input['wallet_address'] ?? '';

if (!$paymentId || !$walletAddress) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    $db = DB::conn();
    
    // Get payment info
    $stmt = $db->prepare(
        'SELECT cp.*, cw.group_id 
         FROM crypto_payments cp 
         JOIN crypto_wallets cw ON cp.wallet_id = cw.id 
         WHERE cp.id = ? AND cp.wallet_address = ?'
    );
    $stmt->bind_param('is', $paymentId, $walletAddress);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$payment) {
        echo json_encode(['error' => 'Payment not found']);
        exit;
    }
    
    // Check if payment is already confirmed
    if ($payment['status'] === 'confirmed') {
        echo json_encode([
            'status' => 'confirmed',
            'confirmations' => 999,
            'message' => 'Ödeme onaylandı'
        ]);
        exit;
    }
    
    // Initialize TRON client for blockchain check
    $tronClient = new TronClient();
    
    // Get USDT TRC20 balance for the wallet
    $balance = $tronClient->getTRC20Balance($walletAddress);
    $expectedAmount = (float)$payment['amount_requested'];
    
    // Check if payment received
    if ($balance >= $expectedAmount) {
        // Get recent transactions to find the specific payment
        $transactions = $tronClient->getTRC20Transactions($walletAddress, TronClient::USDT_CONTRACT, 20);
        
        $confirmations = 0;
        $foundPayment = false;
        
        foreach ($transactions as $tx) {
            $txAmount = (float)($tx['value'] ?? 0) / 1000000; // USDT has 6 decimals
            $txTime = ($tx['block_timestamp'] ?? 0) / 1000; // Convert to seconds
            $paymentTime = strtotime($payment['created_at']);
            
            // Check if this transaction matches our payment
            if ($txAmount >= $expectedAmount && $txTime >= $paymentTime) {
                $foundPayment = true;
                
                // Get transaction confirmations (simplified)
                $currentBlock = $tronClient->getCurrentBlock();
                if ($currentBlock && isset($currentBlock['block_header']['raw_data']['number'])) {
                    $currentBlockNumber = $currentBlock['block_header']['raw_data']['number'];
                    $txBlockNumber = $tx['block'] ?? $currentBlockNumber;
                    $confirmations = max(0, $currentBlockNumber - $txBlockNumber);
                }
                break;
            }
        }
        
        if ($foundPayment) {
            if ($confirmations >= 19) {
                // Payment confirmed! Update database
                $db->begin_transaction();
                try {
                    // Update crypto payment status
                    $stmt = $db->prepare('UPDATE crypto_payments SET status = ?, confirmed_at = NOW() WHERE id = ?');
                    $confirmedStatus = 'confirmed';
                    $stmt->bind_param('si', $confirmedStatus, $paymentId);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Update group balance
                    $stmt = $db->prepare('UPDATE groups SET balance = balance + ? WHERE id = ?');
                    $stmt->bind_param('di', $expectedAmount, $payment['group_id']);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Create transaction record
                    $stmt = $db->prepare(
                        'INSERT INTO transactions (group_id, type, amount, reference, description) 
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    $type = 'topup';
                    $reference = 'crypto_payment_' . $paymentId;
                    $description = 'USDT TRC20 cryptocurrency payment';
                    $stmt->bind_param('isdss', $payment['group_id'], $type, $expectedAmount, $reference, $description);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Update topup request status
                    $stmt = $db->prepare('UPDATE topup_requests SET status = ? WHERE crypto_payment_id = ?');
                    $approvedStatus = 'approved';
                    $stmt->bind_param('si', $approvedStatus, $paymentId);
                    $stmt->execute();
                    $stmt->close();
                    
                    $db->commit();
                    
                    echo json_encode([
                        'status' => 'confirmed',
                        'confirmations' => $confirmations,
                        'amount' => $expectedAmount,
                        'message' => 'Ödeme başarıyla onaylandı!'
                    ]);
                    
                } catch (\Exception $e) {
                    $db->rollback();
                    echo json_encode([
                        'status' => 'pending',
                        'confirmations' => $confirmations,
                        'error' => 'Database update failed'
                    ]);
                }
            } else {
                // Payment detected but waiting for confirmations
                echo json_encode([
                    'status' => 'pending',
                    'confirmations' => $confirmations,
                    'message' => "Transfer tespit edildi, onay bekleniyor ($confirmations/19)"
                ]);
            }
        } else {
            // Balance detected but no matching transaction found
            echo json_encode([
                'status' => 'detected',
                'confirmations' => 0,
                'message' => 'Bakiye tespit edildi, işlem kontrol ediliyor...'
            ]);
        }
    } else {
        // No payment detected yet
        echo json_encode([
            'status' => 'waiting',
            'confirmations' => 0,
            'balance' => $balance,
            'expected' => $expectedAmount,
            'message' => 'Ödeme bekleniyor...'
        ]);
    }
    
} catch (\Exception $e) {
    error_log('Payment status check error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Sistem hatası, lütfen tekrar deneyin'
    ]);
}
?>