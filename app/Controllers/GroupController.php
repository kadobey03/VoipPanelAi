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
            
            // Create TRON wallet for this payment
            $tronWallet = new TronWallet();
            $wallet = $tronWallet->getOrCreateWalletForGroup($groupId);
            
            if (!$wallet) {
                return ['success' => false, 'error' => 'TRON wallet oluşturulamadı'];
            }
            
            // Validate wallet address
            $addressCheck = $security->validateTronAddress($wallet['address']);
            if (!$addressCheck['valid']) {
                return ['success' => false, 'error' => 'Wallet address validation failed'];
            }
            
            $db = DB::conn();
            $db->begin_transaction();
            
            // Create crypto payment record
            $stmt = $db->prepare(
                'INSERT INTO crypto_payments (group_id, user_id, wallet_id, amount_requested, currency, blockchain, network, wallet_address, status, expired_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))'
            );
            
            $currency = 'USDT';
            $blockchain = 'TRON';
            $network = 'TRC20';
            $status = 'pending';
            
            $stmt->bind_param('iiidsssss', $groupId, $userId, $wallet['id'], $amount, $currency, $blockchain, $network, $wallet['address'], $status);
            
            if ($stmt->execute()) {
                $paymentId = $stmt->insert_id;
                $stmt->close();
                
                // Create traditional topup request with crypto reference
                $stmt = $db->prepare(
                    'INSERT INTO topup_requests (group_id, user_id, amount, method, status, crypto_payment_id, crypto_wallet_address)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                
                $requestStatus = 'pending';
                $stmt->bind_param('iidssss', $groupId, $userId, $amount, $method, $requestStatus, $paymentId, $wallet['address']);
                $stmt->execute();
                $stmt->close();
                
                $db->commit();
                
                // Mark wallet as used
                $tronWallet->markWalletAsUsed($wallet['id']);
                
                return [
                    'success' => true,
                    'data' => [
                        'payment_id' => $paymentId,
                        'wallet_address' => $wallet['address'],
                        'amount' => $amount,
                        'currency' => $currency,
                        'network' => $network,
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes'))
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
}
