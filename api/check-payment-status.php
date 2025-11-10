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
$rawInput = file_get_contents('php://input');
error_log("Debug - Raw input: $rawInput");

$input = json_decode($rawInput, true);
error_log("Debug - Decoded input: " . json_encode($input));

$paymentId = (int)($input['payment_id'] ?? 0);
$walletAddress = $input['wallet_address'] ?? '';

error_log("Debug - Payment ID: $paymentId, Wallet Address: $walletAddress");

if (!$paymentId || !$walletAddress) {
    echo json_encode([
        'error' => 'Invalid parameters',
        'debug' => [
            'payment_id' => $paymentId,
            'wallet_address' => $walletAddress,
            'raw_input' => $rawInput
        ]
    ]);
    exit;
}

try {
    // Try to get DB connection first
    try {
        $db = DB::conn();
    } catch (\Exception $e) {
        error_log('DB connection error in check-payment-status: ' . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Veritabanı bağlantı hatası'
        ]);
        exit;
    }
    
    // Check if crypto_payments table exists first
    try {
        $result = $db->query("SHOW TABLES LIKE 'crypto_payments'");
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Crypto ödeme sistemi kurulu değil'
            ]);
            exit;
        }
    } catch (\Exception $e) {
        error_log('Table check error: ' . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Sistem kontrolü başarısız'
        ]);
        exit;
    }
    
    // Get payment info with better error handling
    try {
        // Debug log the input parameters
        error_log("Debug - Payment ID: $paymentId, Wallet Address: $walletAddress");
        
        // Try with simpler query first (without JOIN)
        $stmt = $db->prepare(
            'SELECT * FROM crypto_payments WHERE id = ? AND wallet_address = ?'
        );
        $stmt->bind_param('is', $paymentId, $walletAddress);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Debug log the result
        error_log("Debug - Payment found: " . ($payment ? 'YES' : 'NO'));
        if ($payment) {
            error_log("Debug - Payment data: " . json_encode($payment));
        }
        
        // If payment found, get group_id from topup_requests
        if ($payment) {
            try {
                $stmt = $db->prepare('SELECT group_id FROM topup_requests WHERE crypto_payment_id = ?');
                $stmt->bind_param('i', $paymentId);
                $stmt->execute();
                $topupResult = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                error_log("Debug - Topup result: " . ($topupResult ? json_encode($topupResult) : 'NO'));
                
                if ($topupResult) {
                    $payment['group_id'] = $topupResult['group_id'];
                } else {
                    // Fallback: could not find group_id, set to 0 or get from session
                    $payment['group_id'] = 0;
                }
            } catch (\Exception $e) {
                error_log('Group ID fetch error: ' . $e->getMessage());
                $payment['group_id'] = 0;
            }
        }
    } catch (\Exception $e) {
        error_log('DB query error in check-payment-status: ' . $e->getMessage() . ' - SQL Error: ' . $db->error);
        echo json_encode([
            'status' => 'error',
            'message' => 'Ödeme bilgisi alınamadı',
            'debug' => $e->getMessage(),
            'sql_error' => $db->error
        ]);
        exit;
    }
    
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
    
    // Initialize TRON client for blockchain check with error handling
    try {
        // Check if GuzzleHttp is available
        if (!class_exists('GuzzleHttp\Client')) {
            error_log('GuzzleHttp\Client class not found - composer dependencies missing');
            echo json_encode([
                'status' => 'waiting',
                'confirmations' => 0,
                'message' => 'Blockchain servisi başlatılamıyor. Lütfen composer install çalıştırın.'
            ]);
            exit;
        }
        
        $tronClient = new TronClient();
    } catch (\Exception $e) {
        error_log('TronClient initialization error: ' . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Blockchain bağlantı hatası'
        ]);
        exit;
    } catch (\Error $e) {
        error_log('TronClient fatal error: ' . $e->getMessage());
        echo json_encode([
            'status' => 'waiting',
            'confirmations' => 0,
            'message' => 'Sistem bağımlılıkları eksik. Composer install gerekli.'
        ]);
        exit;
    }
    
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
    error_log('Payment status check error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'status' => 'error',
        'message' => 'Sistem hatası, lütfen tekrar deneyin',
        'debug' => 'Error: ' . $e->getMessage()
    ]);
} catch (\Error $e) {
    error_log('Payment status check fatal error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'status' => 'error',
        'message' => 'Kritik sistem hatası',
        'debug' => 'Fatal Error: ' . $e->getMessage()
    ]);
}
?>