<?php
namespace App\Controllers;

use App\Helpers\DB;
use App\Helpers\ApiClient;
use App\Helpers\TronWallet;
use App\Helpers\TronClient;
use App\Helpers\CryptoSecurity;

class GroupController {
    private function startSession() { if (session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireSuperOrGroupAdmin() {
        $this->startSession();
        if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); }
    }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }
    private function currentGroupId(): ?int { return $_SESSION['user']['group_id'] ?? null; }

    public function index() {
        $this->requireSuperOrGroupAdmin();
        $db = DB::conn();
        if ($this->isSuper()) {
            try {
                $res = $db->query('SELECT id, name, margin, balance, api_group_id, api_group_name FROM groups ORDER BY id DESC');
            } catch (\Throwable $e) {
                $res = $db->query('SELECT id, name, margin, balance FROM groups ORDER BY id DESC');
            }
        } else {
            $gid = (int)$this->currentGroupId();
            try {
                $stmt = $db->prepare('SELECT id, name, margin, balance, api_group_id, api_group_name FROM groups WHERE id=?');
                $stmt->bind_param('i', $gid);
                $stmt->execute();
                $res = $stmt->get_result();
                $stmt->close();
            } catch (\Throwable $e) {
                $stmt = $db->prepare('SELECT id, name, margin, balance FROM groups WHERE id=?');
                $stmt->bind_param('i', $gid);
                $stmt->execute();
                $res = $stmt->get_result();
                $stmt->close();
            }
        }
        $groups = [];
        while ($row = $res->fetch_assoc()) { $groups[] = $row; }
        require __DIR__.'/../Views/groups/index.php';
    }

    public function edit() {
        $this->requireSuperOrGroupAdmin();
        $db = DB::conn();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { \App\Helpers\Url::redirect('/groups'); }
        if (!$this->isSuper()) { http_response_code(403); echo 'Yetkisiz'; return; }
        $error = null; $ok = null;
        // Fetch API groups for mapping
        $apiGroups = [];
        try { $apiGroups = (new ApiClient())->getGroups() ?? []; } catch (\Throwable $e) { $apiGroups = []; }
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') { $error='İsim gerekli'; }
            else {
                $margin = (float)($_POST['margin'] ?? 0);
                $api_group_id = isset($_POST['api_group_id']) && $_POST['api_group_id']!=='' ? (int)$_POST['api_group_id'] : null;
                $api_group_name = null;
                if ($api_group_id) {
                    foreach ($apiGroups as $ag) { if ((int)($ag['id'] ?? 0) === $api_group_id) { $api_group_name = (string)($ag['name'] ?? ''); break; } }
                } else {
                    foreach ($apiGroups as $ag) { if (strcasecmp((string)($ag['name'] ?? ''), $name) === 0) { $api_group_id = (int)($ag['id'] ?? 0); $api_group_name = (string)($ag['name'] ?? ''); break; } }
                }
                $stmt = $db->prepare('UPDATE groups SET name=?, margin=?, api_group_id=?, api_group_name=? WHERE id=?');
                $stmt->bind_param('sdisi', $name, $margin, $api_group_id, $api_group_name, $id);
                $ok = $stmt->execute() ? 'Güncellendi' : 'Güncelleme hatası';
                $stmt->close();
            }
        }
        try {
            $stmt = $db->prepare('SELECT id, name, margin, balance, api_group_id, api_group_name FROM groups WHERE id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $group = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } catch (\Throwable $e) {
            $stmt = $db->prepare('SELECT id, name, margin, balance FROM groups WHERE id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $group = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $group['api_group_id'] = null; $group['api_group_name'] = null;
        }
        require __DIR__.'/../Views/groups/edit.php';
    }

    public function create() {
        $this->requireSuperOrGroupAdmin();
        if (!$this->isSuper()) { http_response_code(403); echo 'Sadece süper admin'; return; }
        $db = DB::conn();
        $error=null; $ok=null;
        $apiGroups = [];
        try { $apiGroups = (new ApiClient())->getGroups() ?? []; } catch (\Throwable $e) { $apiGroups = []; }
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $name = trim($_POST['name'] ?? '');
            $margin = (float)($_POST['margin'] ?? 0);
            $balance = (float)($_POST['balance'] ?? 0);
            $api_group_id = isset($_POST['api_group_id']) && $_POST['api_group_id']!=='' ? (int)$_POST['api_group_id'] : null;
            $api_group_name = null;
            if ($api_group_id) {
                foreach ($apiGroups as $ag) { if ((int)($ag['id'] ?? 0) === $api_group_id) { $api_group_name = (string)($ag['name'] ?? ''); break; } }
            } else {
                foreach ($apiGroups as $ag) { if (strcasecmp((string)($ag['name'] ?? ''), $name) === 0) { $api_group_id = (int)($ag['id'] ?? 0); $api_group_name = (string)($ag['name'] ?? ''); break; } }
            }
            if ($name!=='') {
                $stmt=$db->prepare('INSERT INTO groups (name, margin, balance, api_group_id, api_group_name) VALUES (?,?,?,?,?)');
                $stmt->bind_param('sddis', $name, $margin, $balance, $api_group_id, $api_group_name);
                if ($stmt->execute()) { $ok='Grup oluşturuldu'; } else { $error='Oluşturma hatası'; }
                $stmt->close();
            } else { $error='İsim gerekli'; }
        }
        require __DIR__.'/../Views/groups/create.php';
    }

    public function topup() {
        $this->requireSuperOrGroupAdmin();
        $db = DB::conn();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: /groups'); exit; }
        if (!$this->isSuper() && $this->currentGroupId() !== $id) { http_response_code(403); echo 'Yetkisiz'; return; }
        
        $error=null; $ok=null; $cryptoPaymentData = null;
        
        // Check if continuing existing crypto payment
        $continuePayment = (int)($_GET['continue_payment'] ?? 0);
        if ($continuePayment) {
            $cryptoPaymentData = $this->loadExistingCryptoPayment($continuePayment, $id);
            if (!$cryptoPaymentData) {
                $error = 'Ödeme bulunamadı veya süresi geçmiş';
            }
        }
        
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $amount = (float)($_POST['amount'] ?? 0);
            $method = $_POST['method'] ?? ($this->isSuper() ? 'manual' : 'unknown');
            $methodId = (int)($_POST['method_id'] ?? 0);
            
            if ($amount>0) {
                // Check if this is a cryptocurrency payment
                $isCrypto = $this->isCryptocurrencyMethod($methodId);
                
                if ($this->isSuper()) {
                    $db->begin_transaction();
                    try {
                        $stmt = $db->prepare('UPDATE groups SET balance = balance + ? WHERE id=?');
                        $stmt->bind_param('di', $amount, $id);
                        $stmt->execute();
                        $stmt->close();

                        $type = 'topup'; $desc = 'Topup method: '.$method; $ref = null;
                        $stmt = $db->prepare('INSERT INTO transactions (group_id, type, amount, reference, description) VALUES (?,?,?,?,?)');
                        $stmt->bind_param('isdss', $id, $type, $amount, $ref, $desc);
                        $stmt->execute();
                        $stmt->close();
                        $db->commit();
                        $ok = 'Bakiye eklendi';
                    } catch (\Throwable $e) { $db->rollback(); $error = 'Hata: '.$e->getMessage(); }
                } else {
                    if ($isCrypto) {
                        // Create cryptocurrency payment
                        $cryptoResult = $this->createCryptocurrencyPayment($id, (int)($_SESSION['user']['id'] ?? 0), $amount, $method);
                        if ($cryptoResult['success']) {
                            $cryptoPaymentData = $cryptoResult['data'];
                            $ok = 'Cryptocurrency ödeme sayfası hazırlandı. Lütfen gösterilen adrese ödeme yapın.';
                        } else {
                            $error = $cryptoResult['error'];
                        }
                    } else {
                        // Group admin: create pending request (traditional method)
                        $uid = (int)($_SESSION['user']['id'] ?? 0);
                        $stmt = $db->prepare('INSERT INTO topup_requests (group_id, user_id, amount, method, status) VALUES (?,?,?,?,"pending")');
                        $stmt->bind_param('iids', $id, $uid, $amount, $method);
                        if ($stmt->execute()) { $ok='Yükleme talebiniz alındı. Onay bekliyor.'; } else { $error='Talep oluşturulamadı'; }
                        $stmt->close();
                    }
                }
            } else { $error='Geçerli tutar girin'; }
        }
        
        // fetch group
        $stmt = $db->prepare('SELECT id, name, margin, balance, api_group_id, api_group_name FROM groups WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $group = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        require __DIR__.'/../Views/groups/topup.php';
    }
    
    /**
     * Check if payment method is cryptocurrency
     */
    private function isCryptocurrencyMethod($methodId) {
        if (!$methodId) return false;
        
        try {
            $db = DB::conn();
            $stmt = $db->prepare('SELECT method_type FROM payment_methods WHERE id = ? AND active = 1');
            $stmt->bind_param('i', $methodId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            return $result && $result['method_type'] === 'cryptocurrency';
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Create cryptocurrency payment request
     */
    private function createCryptocurrencyPayment($groupId, $userId, $amount, $method) {
        try {
            // Security checks
            $security = new CryptoSecurity();
            
            // Check if user can create payment
            $securityCheck = $security->canCreatePayment($userId, $groupId, $amount);
            if (!$securityCheck['allowed']) {
                $security->logSecurityEvent('payment_blocked', $userId, $groupId, [
                    'reason' => $securityCheck['reason'],
                    'amount' => $amount
                ]);
                return ['success' => false, 'error' => $securityCheck['reason']];
            }
            
            // Log payment creation attempt
            $security->logSecurityEvent('crypto_payment_created', $userId, $groupId, [
                'amount' => $amount,
                'method' => $method
            ]);
            
            // Get wallet address from settings
            $walletAddress = $this->getSetting('crypto_usdt_wallet');
            if (!$walletAddress) {
                return ['success' => false, 'error' => 'Crypto wallet adresi ayarlanmamış. Lütfen admin ile iletişime geçin.'];
            }
            
            // Validate wallet address
            $addressCheck = $security->validateTronAddress($walletAddress);
            if (!$addressCheck['valid']) {
                return ['success' => false, 'error' => 'Geçersiz wallet adresi ayarlanmış'];
            }
            
            $db = DB::conn();
            $db->begin_transaction();
            
            // Get timeout from settings (default 10 minutes)
            $timeout = $this->getSetting('crypto_payment_timeout') ?: 10;
            
            // Get dummy wallet ID for central wallet system
            $dummyWalletId = $this->ensureDummyWalletExists($db, $walletAddress);
            
            // Create crypto payment record
            $stmt = $db->prepare(
                'INSERT INTO crypto_payments (group_id, user_id, wallet_id, amount_requested, currency, blockchain, network, wallet_address, status, expired_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))'
            );
            
            $currency = 'USDT';
            $blockchain = 'TRON';
            $network = 'TRC20';
            $status = 'pending';
            
            $stmt->bind_param('iiidsssssi', $groupId, $userId, $dummyWalletId, $amount, $currency, $blockchain, $network, $walletAddress, $status, $timeout);
            
            if ($stmt->execute()) {
                $paymentId = $stmt->insert_id;
                $stmt->close();
                
                // Create traditional topup request with crypto reference
                $stmt = $db->prepare(
                    'INSERT INTO topup_requests (group_id, user_id, amount, method, status, crypto_payment_id, crypto_wallet_address)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                
                $requestStatus = 'pending';
                $stmt->bind_param('iidssss', $groupId, $userId, $amount, $method, $requestStatus, $paymentId, $walletAddress);
                $stmt->execute();
                $stmt->close();
                
                $db->commit();
                
                return [
                    'success' => true,
                    'data' => [
                        'payment_id' => $paymentId,
                        'wallet_address' => $walletAddress,
                        'amount' => $amount,
                        'currency' => $currency,
                        'network' => $network,
                        'expires_at' => date('Y-m-d H:i:s', strtotime("+{$timeout} minutes"))
                    ]
                ];
            }
            
            $stmt->close();
            $db->rollback();
            return ['success' => false, 'error' => 'Ödeme kaydı oluşturulamadı'];
            
        } catch (\Exception $e) {
            if (isset($db)) $db->rollback();
            error_log('GroupController::createCryptocurrencyPayment Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Sistem hatası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Load existing crypto payment data
     */
    private function loadExistingCryptoPayment($paymentId, $groupId) {
        try {
            $db = DB::conn();
            
            // Get crypto payment with permission check
            $stmt = $db->prepare('
                SELECT cp.*, tr.group_id, tr.user_id
                FROM crypto_payments cp
                JOIN topup_requests tr ON tr.crypto_payment_id = cp.id
                WHERE cp.id = ? AND tr.group_id = ?
            ');
            $stmt->bind_param('ii', $paymentId, $groupId);
            $stmt->execute();
            $payment = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$payment) {
                return null;
            }
            
            // Check if payment is still valid (10 minutes)
            $expiredAt = strtotime($payment['expired_at']);
            $now = time();
            
            if ($now > $expiredAt || $payment['status'] !== 'pending') {
                // Mark as expired if still pending
                if ($payment['status'] === 'pending') {
                    $stmt = $db->prepare('UPDATE crypto_payments SET status = ? WHERE id = ?');
                    $expiredStatus = 'expired';
                    $stmt->bind_param('si', $expiredStatus, $paymentId);
                    $stmt->execute();
                    $stmt->close();
                }
                return null;
            }
            
            return [
                'payment_id' => $payment['id'],
                'wallet_address' => $payment['wallet_address'],
                'amount' => $payment['amount_requested'],
                'currency' => $payment['currency'],
                'network' => $payment['network'],
                'expires_at' => $payment['expired_at']
            ];
            
        } catch (\Exception $e) {
            error_log('GroupController::loadExistingCryptoPayment Error: ' . $e->getMessage());
            return null;
        }
    }

    public function show() {
        $this->requireSuperOrGroupAdmin();
        $db = DB::conn();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: /groups'); exit; }
        if (!$this->isSuper() && $this->currentGroupId() !== $id) { http_response_code(403); echo 'Yetkisiz'; return; }
        $stmt = $db->prepare('SELECT id, name, margin, balance FROM groups WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $group = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        // last 20 transactions
        $stmt = $db->prepare('SELECT id, type, amount, reference, description, created_at FROM transactions WHERE group_id=? ORDER BY id DESC LIMIT 20');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        require __DIR__.'/../Views/groups/show.php';
    }
    
    /**
     * Get setting value from database
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
    
    /**
     * Ensure dummy wallet record exists and return its ID
     */
    private function ensureDummyWalletExists($db, $walletAddress) {
        try {
            // First check if dummy wallet already exists for this address
            $stmt = $db->prepare('SELECT id FROM crypto_wallets WHERE address = ? AND group_id = 0');
            $stmt->bind_param('s', $walletAddress);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($result && isset($result['id'])) {
                return (int)$result['id'];
            }
            
            // Create new dummy wallet record
            $stmt = $db->prepare('INSERT INTO crypto_wallets (address, private_key_encrypted, group_id, created_at) VALUES (?, ?, 0, NOW())');
            $dummyPrivateKey = 'central_wallet_dummy';
            $stmt->bind_param('ss', $walletAddress, $dummyPrivateKey);
            $stmt->execute();
            $walletId = $stmt->insert_id;
            $stmt->close();
            
            if ($walletId > 0) {
                return (int)$walletId;
            }
            
            throw new \Exception('Failed to create dummy wallet record');
            
        } catch (\Exception $e) {
            error_log('Dummy wallet creation error: ' . $e->getMessage());
            throw new \Exception('Unable to create wallet record for crypto payment');
        }
    }
    
}
