<?php
namespace App\Controllers;

use App\Helpers\ApiClient;
use App\Helpers\DB;
use App\Helpers\SubscriptionManager;

class AgentsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    /**
     * Grup adlarını akıllıca karşılaştır
     */
    private function isGroupMatch($agentGroupName, $userGroupName, $userApiGroupName) {
        if (empty($agentGroupName) || (empty($userGroupName) && empty($userApiGroupName))) {
            error_log("isGroupMatch FALSE - Empty params: agentGroup='$agentGroupName', userGroup='$userGroupName', apiGroup='$userApiGroupName'");
            return false;
        }
        
        // Exact match first
        if (strcasecmp($agentGroupName, $userGroupName) === 0) {
            error_log("isGroupMatch TRUE - Exact match with userGroupName: '$agentGroupName' === '$userGroupName'");
            return true;
        }
        if ($userApiGroupName && strcasecmp($agentGroupName, $userApiGroupName) === 0) {
            error_log("isGroupMatch TRUE - Exact match with userApiGroupName: '$agentGroupName' === '$userApiGroupName'");
            return true;
        }
        
        // Normalize function - case, underscore, spaces, numbers
        $normalize = function($name) {
            $name = strtolower(trim($name));
            $name = str_replace(['_', '-'], ' ', $name); // Replace underscores and dashes with spaces
            $name = preg_replace('/\s+/', ' ', $name); // Multiple spaces to single space
            $name = preg_replace('/\s*\d+$/', '', $name); // Remove trailing numbers (like "_2")
            return trim($name);
        };
        
        $normalizedAgent = $normalize($agentGroupName);
        
        // Normalized match
        $normalizedUser = $normalize($userGroupName);
        if ($normalizedAgent === $normalizedUser) {
            error_log("isGroupMatch TRUE - Normalized match: '$normalizedAgent' === '$normalizedUser'");
            return true;
        }
        
        if ($userApiGroupName) {
            $normalizedApi = $normalize($userApiGroupName);
            if ($normalizedAgent === $normalizedApi) {
                error_log("isGroupMatch TRUE - Normalized API match: '$normalizedAgent' === '$normalizedApi'");
                return true;
            }
        }
        
        // Partial match (contains)
        if (!empty($normalizedAgent) && !empty($normalizedUser)) {
            if (strpos($normalizedAgent, $normalizedUser) !== false || strpos($normalizedUser, $normalizedAgent) !== false) {
                error_log("isGroupMatch TRUE - Partial match: '$normalizedAgent' contains '$normalizedUser'");
                return true;
            }
        }
        
        error_log("isGroupMatch FALSE - No match found for: agentGroup='$agentGroupName', userGroup='$userGroupName', apiGroup='$userApiGroupName'");
        return false;
    }

    public function index(){
        $this->requireAuth();

        // Auto-migrate: add hidden column if not exists
        $db = DB::conn();
        try {
            $db->query('SELECT hidden FROM users LIMIT 1');
        } catch (\Throwable $e) {
            $db->query('ALTER TABLE users ADD COLUMN hidden TINYINT(1) DEFAULT 0');
        }
        // Auto-migrate: create agents table if not exists
        $db->query('CREATE TABLE IF NOT EXISTS agents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            exten VARCHAR(20) NOT NULL UNIQUE,
            user_login VARCHAR(50),
            group_name VARCHAR(100),
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )');

        // Auto-migrate: add agent_id to users if not exists
        try {
            $db->query('SELECT agent_id FROM users LIMIT 1');
        } catch (\Throwable $e) {
            $db->query('ALTER TABLE users ADD COLUMN agent_id INT AFTER group_id');
            $db->query('ALTER TABLE users MODIFY COLUMN role ENUM(\'superadmin\',\'groupadmin\',\'user\') DEFAULT \'groupadmin\'');
        }

        $isSuper = $this->isSuper();
        $isGroupAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'groupadmin';
        $isUser = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'user';
        $isGroupMember = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'groupmember';

        // Groupmember için session'da agent_id yoksa veritabanından çek
        if ($isGroupMember && !isset($_SESSION['user']['agent_id'])) {
            $userId = (int)$_SESSION['user']['id'];
            $stmt = $db->prepare('SELECT agent_id FROM users WHERE id=?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            if ($r && $r['agent_id']) {
                $_SESSION['user']['agent_id'] = $r['agent_id'];
            }
            $stmt->close();
        }
        $userGroupName = '';
        $userApiGroupName = '';
        $userAgentId = 0;
        $userExten = '';
        if (!$isSuper) {
            $groupId = (int)($_SESSION['user']['group_id'] ?? 0);
            try {
                $stmt = $db->prepare('SELECT name, api_group_name FROM groups WHERE id=?');
                $stmt->bind_param('i', $groupId);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($r) {
                    $userGroupName = $r['name'] ?? '';
                    $userApiGroupName = $r['api_group_name'] ?? '';
                }
            } catch (\Throwable $e) {
                $stmt = $db->prepare('SELECT name FROM groups WHERE id=?');
                $stmt->bind_param('i', $groupId);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($r) $userGroupName = $r['name'];
            }

            if ($isUser) {
                // User için agent_id çek
                $userId = (int)$_SESSION['user']['id'];
                $stmt = $db->prepare('SELECT agent_id FROM users WHERE id=?');
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($r && $r['agent_id']) {
                    $userAgentId = (int)$r['agent_id'];
                    $_SESSION['user']['agent_id'] = $userAgentId;
                }
            } elseif ($isGroupMember) {
                // Groupmember için exten çek
                $userExten = $_SESSION['user']['exten'] ?? '';
            }
        }
        $api = new ApiClient();
        $error = null;
        // Get all agents from DB
        $agentsDb = $db->query('SELECT exten, user_login, group_name, active FROM agents')->fetch_all(MYSQLI_ASSOC);

        // If no agents in DB, try to sync from API
        if (empty($agentsDb)) {
            $api = new ApiClient();
            $agentsApi = [];
            try { $agentsApi = $api->getAgentsStatus(); } catch (\Throwable $e) { $error = $e->getMessage(); }
            foreach ($agentsApi as $agent) {
                $exten = $agent['exten'];
                $login = $agent['user_login'] ?? '';
                $apiGroup = $agent['group'] ?? '';
                $localGroup = '';
                if ($apiGroup) {
                    $stmt = $db->prepare('SELECT name FROM groups WHERE api_group_name=?');
                    $stmt->bind_param('s', $apiGroup);
                    $stmt->execute();
                    $r = $stmt->get_result()->fetch_assoc();
                    if ($r) $localGroup = $r['name'];
                    $stmt->close();
                    // Eğer local eşleşme yoksa, API'deki adı kullan
                    if (!$localGroup) {
                        $localGroup = $apiGroup;
                    }
                }
                $stmt = $db->prepare('INSERT INTO agents (exten, user_login, group_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE user_login=VALUES(user_login), group_name=VALUES(group_name), updated_at=NOW()');
                $stmt->bind_param('sss', $exten, $login, $localGroup);
                $stmt->execute();
                $stmt->close();
            }
            // Refresh agentsDb
            $agentsDb = $db->query('SELECT exten, user_login, group_name, active FROM agents')->fetch_all(MYSQLI_ASSOC);
        }

        // Get status from API
        $agentsApi = [];
        try { $agentsApi = $api->getAgentsStatus(); } catch (\Throwable $e) { $error = $e->getMessage(); }

        // Merge status
        $statusMap = [];
        foreach ($agentsApi as $a) {
            $statusMap[$a['exten']] = $a;
        }
        // Filter for group admin or member
        if (!$isSuper) {
            if ($isUser) {
                // User için agent_id'den agent bilgilerini çek
                $userAgentExten = '';
                if ($userAgentId) {
                    $stmt = $db->prepare('SELECT exten FROM agents WHERE id=?');
                    $stmt->bind_param('i', $userAgentId);
                    $stmt->execute();
                    $r = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    if ($r) $userAgentExten = $r['exten'];
                }
                $agentsDb = array_filter($agentsDb, function($a) use ($userAgentExten) {
                    return ($a['active'] ?? 1) == 1 && $a['exten'] == $userAgentExten;
                });
            } elseif ($isGroupMember) {
                // Groupmember sadece kendi exten'ine ait agenti görür
                $agentsDb = array_filter($agentsDb, function($a) use ($userExten) {
                    return ($a['active'] ?? 1) == 1 && $a['exten'] == $userExten;
                });
            } else {
                // Grup admini kendi grubu agentlerini görür
                $agentsDb = array_filter($agentsDb, function($a) use ($userGroupName, $userApiGroupName) {
                    if (($a['active'] ?? 1) != 1) return false;
                    $agentGroupName = $a['group_name'] ?? '';
                    return $this->isGroupMatch($agentGroupName, $userGroupName, $userApiGroupName);
                });
            }
        }

        $agents = [];
        foreach ($agentsDb as $agent) {
            $exten = $agent['exten'];
            if (isset($statusMap[$exten])) {
                $agents[] = array_merge($agent, $statusMap[$exten]);
            } else {
                // No status, but keep
                $agents[] = $agent;
            }
        }

        // Case-insensitive gruplama ve fallback
        $agentsByGroup = [];
        if ($isSuper) {
            // Süper admin tüm agent'ları görür
            foreach ($agents as $agent) {
                $group = $agent['group'] ?? $agent['group_name'] ?? 'Genel';
                if (!isset($agentsByGroup[$group])) {
                    $agentsByGroup[$group] = ['groupName' => $group, 'agents' => []];
                }
                $agentsByGroup[$group]['agents'][] = $agent;
            }
        } else {
            // Diğer kullanıcılar için gruplama
            if ($isUser) {
                // User sadece kendi agent'ını görür
                $group = 'Kendi Agentınız';
                if (!isset($agentsByGroup[$group])) {
                    $agentsByGroup[$group] = ['groupName' => $group, 'agents' => []];
                }
                foreach ($agents as $agent) {
                    $agentsByGroup[$group]['agents'][] = $agent;
                }
            } elseif ($isGroupMember) {
                // Groupmember sadece kendi agent'ını görür
                $group = 'Kendi Agentınız';
                if (!isset($agentsByGroup[$group])) {
                    $agentsByGroup[$group] = ['groupName' => $group, 'agents' => []];
                }
                foreach ($agents as $agent) {
                    $agentsByGroup[$group]['agents'][] = $agent;
                }
            } else {
                // Grup admini - zaten filtrelenmiş agentları direkt gruplara ekle
                $displayGroupName = $userGroupName ?: 'Kendi Grubunuz';
                if (!isset($agentsByGroup[$displayGroupName])) {
                    $agentsByGroup[$displayGroupName] = ['groupName' => $displayGroupName, 'agents' => []];
                }
                foreach ($agents as $agent) {
                    $agentsByGroup[$displayGroupName]['agents'][] = $agent;
                }
            }
        }


        require __DIR__.'/../Views/agents/index.php';
    }

    public function toggleHidden() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            die('Yetkisiz');
        }
        $exten = $_POST['exten'] ?? '';
        if (!$exten) {
            die('Geçersiz');
        }
        $db = DB::conn();
        $stmt = $db->prepare('UPDATE agents SET active = 1 - active WHERE exten=?');
        $stmt->bind_param('s', $exten);
        $stmt->execute();
        $stmt->close();
        if (!headers_sent()) {
            header('Location: ' . \App\Helpers\Url::to('/agents'));
        } else {
            echo '<script>window.location.href="' . \App\Helpers\Url::to('/agents') . '";</script>';
        }
        exit;
    }

    public function syncAgents() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            die('Yetkisiz');
        }
        $api = new ApiClient();
        $agentsApi = $api->getAgentsStatus();
        $db = DB::conn();
        foreach ($agentsApi as $agent) {
            $exten = $agent['exten'];
            $login = $agent['user_login'] ?? '';
            $apiGroup = $agent['group'] ?? '';
            $localGroup = '';
            if ($apiGroup) {
                $stmt = $db->prepare('SELECT name FROM groups WHERE api_group_name=?');
                $stmt->bind_param('s', $apiGroup);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_assoc();
                if ($r) $localGroup = $r['name'];
                $stmt->close();
                // Eğer local eşleşme yoksa, API'deki adı kullan
                if (!$localGroup) {
                    $localGroup = $apiGroup;
                }
            }
            $stmt = $db->prepare('INSERT INTO agents (exten, user_login, group_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE user_login=VALUES(user_login), group_name=VALUES(group_name), updated_at=NOW()');
            $stmt->bind_param('sss', $exten, $login, $localGroup);
            $stmt->execute();
            $stmt->close();
        }
        if (!headers_sent()) {
            header('Location: ' . \App\Helpers\Url::to('/agents'));
        } else {
            echo '<script>window.location.href="' . \App\Helpers\Url::to('/agents') . '";</script>';
        }
        exit;
    }

    public function toggleActive() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            die('Yetkisiz');
        }
        $exten = $_POST['exten'] ?? '';
        if (!$exten) {
            die('Geçersiz');
        }
        $db = DB::conn();
        $stmt = $db->prepare('UPDATE agents SET active = 1 - active WHERE exten=?');
        $stmt->bind_param('s', $exten);
        $stmt->execute();
        $stmt->close();
        if (!headers_sent()) {
            header('Location: ' . \App\Helpers\Url::to('/agents'));
        } else {
            echo '<script>window.location.href="' . \App\Helpers\Url::to('/agents') . '";</script>';
        }
        exit;
    }

    /**
     * Agent adını güncelle
     */
    public function updateAgentName() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            $_SESSION['error'] = 'Yetkisiz işlem';
            header('Location: ' . \App\Helpers\Url::to('/agents'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die('Geçersiz istek');
        }

        $exten = $_POST['exten'] ?? '';
        $newName = trim($_POST['new_name'] ?? '');

        if (!$exten || !$newName) {
            $_SESSION['error'] = 'Eksik parametreler';
            header('Location: ' . \App\Helpers\Url::to('/agents'));
            exit;
        }

        $db = DB::conn();
        
        try {
            // Agent'ın mevcut olup olmadığını kontrol et
            $stmt = $db->prepare('SELECT user_login FROM agents WHERE exten = ?');
            $stmt->bind_param('s', $exten);
            $stmt->execute();
            $agent = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$agent) {
                $_SESSION['error'] = 'Agent bulunamadı';
                header('Location: ' . \App\Helpers\Url::to('/agents'));
                exit;
            }

            // Agent adını güncelle (sadece lokal veritabanında, API'yi etkilemez)
            $stmt = $db->prepare('UPDATE agents SET user_login = ?, updated_at = NOW() WHERE exten = ?');
            $stmt->bind_param('ss', $newName, $exten);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success'] = 'Agent adı başarıyla güncellendi: ' . htmlspecialchars($newName);

        } catch (Exception $e) {
            error_log('Agent name update error: ' . $e->getMessage());
            $_SESSION['error'] = 'Agent adı güncellenirken hata oluştu';
        }

        header('Location: ' . \App\Helpers\Url::to('/agents'));
        exit;
    }

    /**
     * Agent'a abonelik ekle
     */
    public function addSubscription() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            $_SESSION['error'] = 'Yetkisiz işlem';
            header('Location: ' . \App\Helpers\Url::to('/agents'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die('Geçersiz istek');
        }

        $exten = $_POST['agent_exten'] ?? '';
        $productId = (int)($_POST['agent_product_id'] ?? 0);
        $agentNumber = $_POST['agent_number'] ?? '';
        $subscriptionStartDate = $_POST['subscription_start_date'] ?? '';
        $subscriptionPaid = isset($_POST['subscription_paid']) ? 1 : 0;

        if (!$exten || !$productId) {
            $_SESSION['error'] = 'Eksik parametreler - Extension ve Ürün seçimi gerekli';
            header('Location: ' . \App\Helpers\Url::to('/agents'));
            exit;
        }

        // Başlangıç tarihi kontrolü ve düzenleme
        if (empty($subscriptionStartDate)) {
            $subscriptionStartDate = date('Y-m-d');
        } else {
            // Geçerli tarih formatında olduğundan emin ol
            $dateCheck = \DateTime::createFromFormat('Y-m-d', $subscriptionStartDate);
            if (!$dateCheck || $dateCheck->format('Y-m-d') !== $subscriptionStartDate) {
                $_SESSION['error'] = 'Geçersiz başlangıç tarihi formatı';
                header('Location: ' . \App\Helpers\Url::to('/agents'));
                exit;
            }
        }

        $db = DB::conn();
        $this->createAgentPurchaseTables();

        try {
            $db->begin_transaction();

            // Agent'ı bul
            $stmt = $db->prepare('SELECT * FROM agents WHERE exten = ?');
            $stmt->bind_param('s', $exten);
            $stmt->execute();
            $agent = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$agent) {
                throw new \Exception('Agent bulunamadı');
            }

            // Ürün bilgilerini al
            $stmt = $db->prepare('SELECT * FROM agent_products WHERE id = ? AND is_active = 1');
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$product) {
                throw new \Exception('Ürün bulunamadı');
            }

            // Agent'ın grubunu bul ve o grubun adminini al
            $groupId = 0;
            $userId = 0;
            
            if ($agent['group_name']) {
                // Grup ID'sini bul - daha esnek arama
                $stmt = $db->prepare('SELECT id FROM groups WHERE name = ? OR api_group_name = ? OR name LIKE ? OR api_group_name LIKE ? LIMIT 1');
                $groupPattern = '%' . $agent['group_name'] . '%';
                $stmt->bind_param('ssss', $agent['group_name'], $agent['group_name'], $groupPattern, $groupPattern);
                $stmt->execute();
                $groupResult = $stmt->get_result()->fetch_assoc();
                if ($groupResult) $groupId = $groupResult['id'];
                $stmt->close();
                
                // Bu grubun adminini bul
                if ($groupId) {
                    $stmt = $db->prepare('SELECT id, login FROM users WHERE group_id = ? AND role = "groupadmin" LIMIT 1');
                    $stmt->bind_param('i', $groupId);
                    $stmt->execute();
                    $adminResult = $stmt->get_result()->fetch_assoc();
                    if ($adminResult) $userId = $adminResult['id'];
                    $stmt->close();
                }
                
                // Eğer hala admin bulunamazsa, superadmin kullan
                if (!$userId) {
                    $stmt = $db->prepare('SELECT id, group_id FROM users WHERE role = "superadmin" LIMIT 1');
                    $stmt->execute();
                    $superAdminResult = $stmt->get_result()->fetch_assoc();
                    if ($superAdminResult) {
                        $userId = $superAdminResult['id'];
                        $groupId = $superAdminResult['group_id'] ?? null;
                        
                        // Eğer superadmin'in group_id'si yok veya geçersizse, mevcut bir grup bul
                        if (!$groupId) {
                            $stmt2 = $db->prepare('SELECT id FROM groups ORDER BY id ASC LIMIT 1');
                            $stmt2->execute();
                            $firstGroupResult = $stmt2->get_result()->fetch_assoc();
                            if ($firstGroupResult) {
                                $groupId = $firstGroupResult['id'];
                            }
                            $stmt2->close();
                        }
                    }
                    $stmt->close();
                }
            }
            
            // group_id doğrulaması yap
            if ($groupId) {
                $stmt = $db->prepare('SELECT id FROM groups WHERE id = ?');
                $stmt->bind_param('i', $groupId);
                $stmt->execute();
                $groupCheck = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                if (!$groupCheck) {
                    // Geçersiz group_id, ilk mevcut grubu al
                    $stmt = $db->prepare('SELECT id FROM groups ORDER BY id ASC LIMIT 1');
                    $stmt->execute();
                    $firstGroupResult = $stmt->get_result()->fetch_assoc();
                    $groupId = $firstGroupResult ? $firstGroupResult['id'] : null;
                    $stmt->close();
                }
            }
            
            if (!$userId) {
                throw new \Exception('Abonelik sahibi bulunamadı. Agent grubu: ' . ($agent['group_name'] ?? 'bilinmeyen') . '. Sistem adminini kontrol edin.');
            }

            // Agent numarası kullanıcıdan gelen veya otomatik oluştur
            if (!$agentNumber) {
                $agentNumber = $this->generateAgentNumber($product['phone_prefix']);
            }
            
            // Agent numarası benzersizlik kontrolü
            $stmt = $db->prepare('SELECT COUNT(*) as count FROM user_agents WHERE agent_number = ?');
            $stmt->bind_param('s', $agentNumber);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($result['count'] > 0) {
                throw new \Exception('Bu agent numarası zaten kullanımda');
            }

            // Başlangıç tarihine göre sonraki ödeme tarihini hesapla
            $nextDue = null;
            if ($product['is_subscription']) {
                $startDate = new \DateTime($subscriptionStartDate);
                $nextDue = $startDate->add(new \DateInterval('P1M'))->format('Y-m-d H:i:s');
            }

            // User agent oluştur - created_at başlangıç tarihi olacak
            $stmt = $db->prepare('INSERT INTO user_agents (user_id, group_id, agent_product_id, agent_number, status, is_lifetime, next_subscription_due, created_at) VALUES (?, ?, ?, ?, "active", ?, ?, ?)');
            $isLifetime = $product['is_subscription'] ? 0 : 1;
            $createdAt = $subscriptionStartDate . ' ' . date('H:i:s');
            $stmt->bind_param('iiisisis', $userId, $groupId, $productId, $agentNumber, $isLifetime, $nextDue, $createdAt);
            $stmt->execute();
            $userAgentId = $db->insert_id;
            $stmt->close();

            // İlk ödeme işlemi
            if ($product['is_subscription'] && $product['subscription_monthly_fee'] > 0) {
                if ($subscriptionPaid) {
                    // Manuel ödeme - ödendi olarak işaretle
                    $stmt = $db->prepare('INSERT INTO agent_subscription_payments (user_agent_id, user_id, amount, due_date, status, payment_date, payment_method) VALUES (?, ?, ?, ?, "paid", NOW(), "manual")');
                    $nextPaymentDate = (new DateTime($subscriptionStartDate))->add(new DateInterval('P1M'))->format('Y-m-d');
                    $stmt->bind_param('iids', $userAgentId, $userId, $product['subscription_monthly_fee'], $nextPaymentDate);
                    $stmt->execute();
                    $stmt->close();

                    // Sonraki ay için ödemeyi planla
                    $nextMonthDate = (new \DateTime($subscriptionStartDate))->add(new \DateInterval('P2M'))->format('Y-m-d');
                    $stmt = $db->prepare('INSERT INTO agent_subscription_payments (user_agent_id, user_id, amount, due_date, status) VALUES (?, ?, ?, ?, "pending")');
                    $stmt->bind_param('iids', $userAgentId, $userId, $product['subscription_monthly_fee'], $nextMonthDate);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Otomatik bakiyeden düş - kurulum ücreti
                    $stmt = $db->prepare('SELECT balance FROM groups WHERE id = ?');
                    $stmt->bind_param('i', $groupId);
                    $stmt->execute();
                    $groupResult = $stmt->get_result()->fetch_assoc();
                    $currentBalance = $groupResult ? $groupResult['balance'] : 0;
                    $stmt->close();

                    if ($currentBalance < $product['price']) {
                        throw new \Exception('Yetersiz bakiye. Kurulum ücreti için $' . number_format($product['price'], 2) . ' gerekli, mevcut: $' . number_format($currentBalance, 2));
                    }

                    // Bakiyeden kurulum ücretini düş
                    $stmt = $db->prepare('UPDATE groups SET balance = balance - ? WHERE id = ?');
                    $stmt->bind_param('di', $product['price'], $groupId);
                    $stmt->execute();
                    $stmt->close();

                    // Kurulum ödemesi kaydı
                    $stmt = $db->prepare('INSERT INTO agent_subscription_payments (user_agent_id, user_id, amount, due_date, status, payment_date, payment_method) VALUES (?, ?, ?, ?, "paid", NOW(), "balance")');
                    $setupDueDate = $subscriptionStartDate;
                    $stmt->bind_param('iids', $userAgentId, $userId, $product['price'], $setupDueDate);
                    $stmt->execute();
                    $stmt->close();

                    // İlk aylık ödemeyi planla
                    $firstPaymentDate = (new \DateTime($subscriptionStartDate))->add(new \DateInterval('P1M'))->format('Y-m-d');
                    $stmt = $db->prepare('INSERT INTO agent_subscription_payments (user_agent_id, user_id, amount, due_date, status) VALUES (?, ?, ?, ?, "pending")');
                    $stmt->bind_param('iids', $userAgentId, $userId, $product['subscription_monthly_fee'], $firstPaymentDate);
                    $stmt->execute();
                    $stmt->close();

                    // Transaction kaydı oluştur
                    $stmt = $db->prepare('INSERT INTO transactions (group_id, type, amount, reference, description) VALUES (?, "agent_setup", ?, ?, ?)');
                    $reference = "AGS-" . $userAgentId;
                    $description = "Agent kurulum ücreti: " . $product['name'] . " (#" . $agentNumber . ")";
                    $stmt->bind_param('idss', $groupId, $product['price'], $reference, $description);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $db->commit();
            
            $successMessage = 'Agent\'a başarıyla abonelik eklendi. Agent Numarası: ' . $agentNumber;
            $successMessage .= ' Başlangıç Tarihi: ' . date('d.m.Y', strtotime($subscriptionStartDate));
            
            if ($subscriptionPaid) {
                $successMessage .= ' (Manuel ödeme kaydedildi)';
            } else if (!$product['is_subscription']) {
                $successMessage .= ' (Lifetime ürün)';
            } else {
                $successMessage .= ' (Kurulum ücreti bakiyeden düşüldü)';
            }
            
            $_SESSION['success'] = $successMessage;

        } catch (\Exception $e) {
            $db->rollback();
            error_log('Add subscription error: ' . $e->getMessage());
            $_SESSION['error'] = 'Abonelik eklenirken hata oluştu: ' . $e->getMessage();
        }

        header('Location: ' . \App\Helpers\Url::to('/agents'));
        exit;
    }

    /**
     * Agent aboneliğini güncelle
     */
    public function updateSubscription() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            $_SESSION['error'] = 'Yetkisiz işlem';
            header('Location: ' . \App\Helpers\Url::to('/agents'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die('Geçersiz istek');
        }

        $userAgentId = (int)($_POST['user_agent_id'] ?? 0);
        $subscriptionStartDate = $_POST['subscription_start_date'] ?? '';
        $nextPaymentDate = $_POST['next_payment_date'] ?? '';
        $subscriptionStatus = $_POST['subscription_status'] ?? 'active';
        $markPaid = isset($_POST['mark_paid']) ? 1 : 0;

        if (!$userAgentId) {
            $_SESSION['error'] = 'Eksik parametreler';
            header('Location: ' . \App\Helpers\Url::to('/agents'));
            exit;
        }

        $db = DB::conn();

        try {
            $db->begin_transaction();

            // Mevcut abonelik bilgilerini al
            $stmt = $db->prepare('SELECT ua.*, ap.name as product_name, ap.subscription_monthly_fee FROM user_agents ua JOIN agent_products ap ON ua.agent_product_id = ap.id WHERE ua.id = ?');
            $stmt->bind_param('i', $userAgentId);
            $stmt->execute();
            $userAgent = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$userAgent) {
                throw new \Exception('Abonelik bulunamadı');
            }

            // Başlangıç tarihi kontrolü
            if (!empty($subscriptionStartDate)) {
                $dateCheck = \DateTime::createFromFormat('Y-m-d', $subscriptionStartDate);
                if (!$dateCheck) {
                    throw new \Exception('Geçersiz başlangıç tarihi formatı');
                }
                
                // created_at'ı güncelle
                $createdAt = $subscriptionStartDate . ' ' . date('H:i:s', strtotime($userAgent['created_at']));
                $stmt = $db->prepare('UPDATE user_agents SET created_at = ? WHERE id = ?');
                $stmt->bind_param('si', $createdAt, $userAgentId);
                $stmt->execute();
                $stmt->close();
            }

            // Sonraki ödeme tarihi kontrolü
            if (!empty($nextPaymentDate)) {
                $dateCheck = \DateTime::createFromFormat('Y-m-d', $nextPaymentDate);
                if (!$dateCheck) {
                    throw new \Exception('Geçersiz ödeme tarihi formatı');
                }
                
                $nextDue = $nextPaymentDate . ' ' . date('H:i:s');
                $stmt = $db->prepare('UPDATE user_agents SET next_subscription_due = ? WHERE id = ?');
                $stmt->bind_param('si', $nextDue, $userAgentId);
                $stmt->execute();
                $stmt->close();
            }

            // Abonelik durumunu güncelle
            $stmt = $db->prepare('UPDATE user_agents SET status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->bind_param('si', $subscriptionStatus, $userAgentId);
            $stmt->execute();
            $stmt->close();

            // Manuel ödeme işaretle
            if ($markPaid && $userAgent['subscription_monthly_fee'] > 0) {
                // Mevcut pending ödemeyi bul
                $stmt = $db->prepare('SELECT id, due_date FROM agent_subscription_payments WHERE user_agent_id = ? AND status = "pending" ORDER BY due_date ASC LIMIT 1');
                $stmt->bind_param('i', $userAgentId);
                $stmt->execute();
                $pendingPayment = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($pendingPayment) {
                    // Mevcut ödemeyi paid yap
                    $stmt = $db->prepare('UPDATE agent_subscription_payments SET status = "paid", payment_date = NOW(), payment_method = "manual" WHERE id = ?');
                    $stmt->bind_param('i', $pendingPayment['id']);
                    $stmt->execute();
                    $stmt->close();

                    // Sonraki ay için yeni ödeme planla
                    $nextMonth = date('Y-m-d', strtotime($pendingPayment['due_date'] . ' +1 month'));
                    $stmt = $db->prepare('INSERT INTO agent_subscription_payments (user_agent_id, user_id, amount, due_date, status) VALUES (?, ?, ?, ?, "pending")');
                    $stmt->bind_param('iids', $userAgentId, $userAgent['user_id'], $userAgent['subscription_monthly_fee'], $nextMonth);
                    $stmt->execute();
                    $stmt->close();

                    // user_agents tablosundaki sonraki ödeme tarihini de güncelle
                    $stmt = $db->prepare('UPDATE user_agents SET next_subscription_due = ? WHERE id = ?');
                    $nextDueTime = $nextMonth . ' ' . date('H:i:s');
                    $stmt->bind_param('si', $nextDueTime, $userAgentId);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $db->commit();
            
            $successMessage = 'Abonelik başarıyla güncellendi: ' . htmlspecialchars($userAgent['product_name']);
            if ($markPaid) {
                $successMessage .= ' (Manuel ödeme kaydedildi)';
            }
            $_SESSION['success'] = $successMessage;

        } catch (\Exception $e) {
            $db->rollback();
            error_log('Update subscription error: ' . $e->getMessage());
            $_SESSION['error'] = 'Abonelik güncellenirken hata oluştu: ' . $e->getMessage();
        }

        header('Location: ' . \App\Helpers\Url::to('/agents'));
        exit;
    }

    /**
     * Agent aboneliğini sil/iptal et
     */
    public function removeSubscription() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            $_SESSION['error'] = 'Yetkisiz işlem';
            header('Location: ' . \App\Helpers\Url::to('/agents'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die('Geçersiz istek');
        }

        $userAgentId = (int)($_POST['user_agent_id'] ?? 0);

        if (!$userAgentId) {
            $_SESSION['error'] = 'Eksik parametreler';
            header('Location: ' . \App\Helpers\Url::to('/agents'));
            exit;
        }

        $db = DB::conn();

        try {
            $db->begin_transaction();

            // User agent'ı bul
            $stmt = $db->prepare('SELECT ua.*, ap.name as product_name FROM user_agents ua JOIN agent_products ap ON ua.agent_product_id = ap.id WHERE ua.id = ?');
            $stmt->bind_param('i', $userAgentId);
            $stmt->execute();
            $userAgent = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$userAgent) {
                throw new Exception('Abonelik bulunamadı');
            }

            // Agent'ı pasif yap
            $stmt = $db->prepare('UPDATE user_agents SET status = "cancelled", updated_at = NOW() WHERE id = ?');
            $stmt->bind_param('i', $userAgentId);
            $stmt->execute();
            $stmt->close();

            // Bekleyen abonelik ödemelerini iptal et
            $stmt = $db->prepare('UPDATE agent_subscription_payments SET status = "cancelled" WHERE user_agent_id = ? AND status = "pending"');
            $stmt->bind_param('i', $userAgentId);
            $stmt->execute();
            $stmt->close();

            $db->commit();
            $_SESSION['success'] = 'Agent aboneliği başarıyla iptal edildi: ' . htmlspecialchars($userAgent['product_name']);

        } catch (Exception $e) {
            $db->rollback();
            error_log('Remove subscription error: ' . $e->getMessage());
            $_SESSION['error'] = 'Abonelik iptal edilirken hata oluştu: ' . $e->getMessage();
        }

        header('Location: ' . \App\Helpers\Url::to('/agents'));
        exit;
    }

    public function syncCron() {
        $token = $_GET['token'] ?? ($_POST['token'] ?? '');
        $expected = getenv('CRON_TOKEN') ?: '';
        if (!$expected || $token !== $expected) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $api = new ApiClient();
        $agentsApi = [];
        $imported = 0;
        $updated = 0;
        $errors = [];

        try {
            $agentsApi = $api->getAgentsStatus();
            $db = DB::conn();
            
            foreach ($agentsApi as $agent) {
                $exten = $agent['exten'];
                $login = $agent['user_login'] ?? '';
                $apiGroup = $agent['group'] ?? '';
                $localGroup = '';
                
                if ($apiGroup) {
                    $stmt = $db->prepare('SELECT name FROM groups WHERE api_group_name=?');
                    $stmt->bind_param('s', $apiGroup);
                    $stmt->execute();
                    $r = $stmt->get_result()->fetch_assoc();
                    if ($r) $localGroup = $r['name'];
                    $stmt->close();
                    // Eğer local eşleşme yoksa, API'deki adı kullan
                    if (!$localGroup) {
                        $localGroup = $apiGroup;
                    }
                }

                // Check if agent exists
                $stmt = $db->prepare('SELECT id FROM agents WHERE exten=?');
                $stmt->bind_param('s', $exten);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    // Update existing
                    $stmt->close();
                    $stmt = $db->prepare('UPDATE agents SET user_login=?, group_name=?, updated_at=NOW() WHERE exten=?');
                    $stmt->bind_param('sss', $login, $localGroup, $exten);
                    $stmt->execute();
                    $stmt->close();
                    $updated++;
                } else {
                    // Insert new
                    $stmt->close();
                    $stmt = $db->prepare('INSERT INTO agents (exten, user_login, group_name) VALUES (?, ?, ?)');
                    $stmt->bind_param('sss', $exten, $login, $localGroup);
                    $stmt->execute();
                    $stmt->close();
                    $imported++;
                }
            }
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'agents_imported' => $imported,
            'agents_updated' => $updated,
            'total_agents' => count($agentsApi),
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Agent satın alma sayfası
     */
    public function purchase() {
        $this->requireAuth();
        $db = DB::conn();
        
        // Auto-migrate: Agent satın alma tablolarını oluştur
        $this->createAgentPurchaseTables();
        
        // Aktif ürünleri getir
        $stmt = $db->prepare('SELECT * FROM agent_products WHERE is_active = 1 ORDER BY price ASC');
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Kullanıcının mevcut agentlerini getir
        $userId = $_SESSION['user']['id'];
        $groupId = $_SESSION['user']['group_id'] ?? 0;
        
        $stmt = $db->prepare('SELECT ua.*, ap.name as product_name, ap.phone_prefix
                             FROM user_agents ua
                             JOIN agent_products ap ON ua.agent_product_id = ap.id
                             WHERE ua.user_id = ? AND ua.group_id = ? AND ua.status = "active"');
        $stmt->bind_param('ii', $userId, $groupId);
        $stmt->execute();
        $userAgents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Grup bakiyesini getir
        $stmt = $db->prepare('SELECT balance FROM groups WHERE id = ?');
        $stmt->bind_param('i', $groupId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $balance = $result ? $result['balance'] : 0;
        $stmt->close();
        
        require __DIR__.'/../Views/agents/purchase.php';
    }

    /**
     * Agent satın alma işlemi
     */
    public function processPurchase() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die('Geçersiz istek');
        }
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $userId = $_SESSION['user']['id'];
        $groupId = $_SESSION['user']['group_id'] ?? 0;
        
        if (!$productId) {
            $_SESSION['error'] = 'Geçerli bir ürün seçiniz';
            header('Location: ' . \App\Helpers\Url::to('/agents/purchase'));
            exit;
        }
        
        $db = DB::conn();
        
        // Ürün bilgilerini getir
        $stmt = $db->prepare('SELECT * FROM agent_products WHERE id = ? AND is_active = 1');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$product) {
            $_SESSION['error'] = 'Ürün bulunamadı';
            header('Location: ' . \App\Helpers\Url::to('/agents/purchase'));
            exit;
        }
        
        // Grup bakiyesini kontrol et
        $stmt = $db->prepare('SELECT balance FROM groups WHERE id = ?');
        $stmt->bind_param('i', $groupId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $currentBalance = $result ? $result['balance'] : 0;
        $stmt->close();
        
        $totalCost = $product['price'];
        if ($currentBalance < $totalCost) {
            $_SESSION['error'] = 'Yetersiz bakiye. Mevcut: $' . number_format($currentBalance, 2) . ', Gereken: $' . number_format($totalCost, 2);
            header('Location: ' . \App\Helpers\Url::to('/agents/purchase'));
            exit;
        }
        
        $db->begin_transaction();
        
        try {
            // Agent numarası oluştur
            $agentNumber = $this->generateAgentNumber($product['phone_prefix']);
            
            // Kullanıcı agenti oluştur
            $stmt = $db->prepare('INSERT INTO user_agents (user_id, group_id, agent_product_id, agent_number, status, is_lifetime, next_subscription_due) VALUES (?, ?, ?, ?, "active", ?, ?)');
            $isLifetime = $product['is_subscription'] ? 0 : 1;
            $nextDue = $product['is_subscription'] ? date('Y-m-d H:i:s', strtotime('+1 month')) : null;
            $stmt->bind_param('iiisos', $userId, $groupId, $productId, $agentNumber, $isLifetime, $nextDue);
            $stmt->execute();
            $userAgentId = $db->insert_id;
            $stmt->close();
            
            // Satın alma kaydı oluştur
            $stmt = $db->prepare('INSERT INTO agent_purchases (user_id, group_id, user_agent_id, agent_product_id, purchase_type, amount, status, processed_at) VALUES (?, ?, ?, ?, "initial", ?, "completed", NOW())');
            $stmt->bind_param('iiiid', $userId, $groupId, $userAgentId, $productId, $totalCost);
            $stmt->execute();
            $purchaseId = $db->insert_id;
            $stmt->close();
            
            // Bakiyeden düş
            $stmt = $db->prepare('UPDATE groups SET balance = balance - ? WHERE id = ?');
            $stmt->bind_param('di', $totalCost, $groupId);
            $stmt->execute();
            $stmt->close();
            
            // Transaction kaydı oluştur
            $stmt = $db->prepare('INSERT INTO transactions (group_id, type, amount, reference, description) VALUES (?, "agent_purchase", ?, ?, ?)');
            $reference = "AGP-" . $purchaseId;
            $description = "Agent satın alma: " . $product['name'] . " (#" . $agentNumber . ")";
            $stmt->bind_param('idss', $groupId, $totalCost, $reference, $description);
            $stmt->execute();
            $stmt->close();
            
            // Abonelik ödemesi planla (eğer abonelik ürünse)
            if ($product['is_subscription'] && $product['subscription_monthly_fee'] > 0) {
                $stmt = $db->prepare('INSERT INTO agent_subscription_payments (user_agent_id, user_id, amount, due_date, status) VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 1 MONTH), "pending")');
                $stmt->bind_param('iid', $userAgentId, $userId, $product['subscription_monthly_fee']);
                $stmt->execute();
                $stmt->close();
            }
            
            $db->commit();
            
            // Telegram bildirimi gönder
            $this->sendPurchaseNotification($userId, $product, $agentNumber, $totalCost, $currentBalance - $totalCost);
            
            // Bakiye güncellendikten sonra askıya alınan agentleri yeniden aktifleştir
            $reactivatedCount = SubscriptionManager::reactivateSuspendedAgents($userId, $groupId);
            if ($reactivatedCount > 0) {
                $_SESSION['success'] = 'Agent başarıyla satın alındı! Agent Numarası: ' . $agentNumber . ' Ayrıca ' . $reactivatedCount . ' askıya alınmış agent yeniden aktifleştirildi.';
            } else {
                $_SESSION['success'] = 'Agent başarıyla satın alındı! Agent Numarası: ' . $agentNumber;
            }
            
        } catch (Exception $e) {
            $db->rollback();
            error_log('Agent purchase error: ' . $e->getMessage());
            $_SESSION['error'] = 'Satın alma işlemi başarısız. Lütfen tekrar deneyiniz.';
        }
        
        header('Location: ' . \App\Helpers\Url::to('/agents/purchase'));
        exit;
    }

    /**
     * Admin agent ürün yönetimi
     */
    public function manageProducts() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            die('Yetkisiz erişim');
        }
        
        $db = DB::conn();
        $this->createAgentPurchaseTables();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'create') {
                $this->createProduct();
                return;
            } elseif ($action === 'update') {
                $this->updateProduct();
                return;
            } elseif ($action === 'delete') {
                $this->deleteProduct();
                return;
            }
        }
        
        // Ürünleri listele
        $stmt = $db->prepare('SELECT * FROM agent_products ORDER BY created_at DESC');
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        require __DIR__.'/../Views/agents/manage-products.php';
    }

    /**
     * Agent numarası oluşturucu
     */
    private function generateAgentNumber($prefix = '0905') {
        $db = DB::conn();
        
        do {
            $number = $prefix . str_pad(mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
            
            // Benzersizlik kontrolü
            $stmt = $db->prepare('SELECT COUNT(*) as count FROM user_agents WHERE agent_number = ?');
            $stmt->bind_param('s', $number);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
        } while ($result['count'] > 0);
        
        return $number;
    }

    /**
     * Telegram satın alma bildirimi
     */
    private function sendPurchaseNotification($userId, $product, $agentNumber, $amount, $remainingBalance) {
        try {
            $db = DB::conn();
            
            // Kullanıcı bilgilerini al
            $stmt = $db->prepare('SELECT u.login, g.name as group_name FROM users u LEFT JOIN groups g ON u.group_id = g.id WHERE u.id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$user) return;
            
            $message = "🛒 *Agent Satın Alma Bildirimi*\n\n";
            $message .= "👤 *Kullanıcı:* " . $user['login'] . "\n";
            $message .= "🏢 *Grup:* " . ($user['group_name'] ?: 'Bilinmeyen') . "\n";
            $message .= "📱 *Ürün:* " . $product['name'] . "\n";
            $message .= "📞 *Agent Numarası:* " . $agentNumber . "\n";
            $message .= "💰 *Tutar:* $" . number_format($amount, 2) . "\n";
            $message .= "💳 *Kalan Bakiye:* $" . number_format($remainingBalance, 2) . "\n";
            $message .= "📅 *Tarih:* " . date('d.m.Y H:i') . "\n";
            
            if ($product['is_subscription']) {
                $message .= "\n⚠️ *Not:* Aylık $" . number_format($product['subscription_monthly_fee'], 2) . " abonelik ücreti otomatik düşülecek.";
            }
            
            // Telegram bildirimi gönder
            $notifier = new \App\Helpers\TelegramNotifier();
            $notifier->sendToAdmins($message);
            
        } catch (Exception $e) {
            error_log('Telegram notification error: ' . $e->getMessage());
        }
    }

    /**
     * Agent satın alma tablolarını oluştur
     */
    private function createAgentPurchaseTables() {
        $db = DB::conn();
        
        try {
            // Agent products tablosunu kontrol et ve oluştur
            $db->query('SELECT 1 FROM agent_products LIMIT 1');
        } catch (Exception $e) {
            // Tabloları oluştur
            $sql = file_get_contents(__DIR__ . '/../../agent_purchase_schema.sql');
            $db->multi_query($sql);
            
            // Multi-query sonuçlarını temizle
            while ($db->next_result()) {
                if ($result = $db->store_result()) {
                    $result->free();
                }
            }
        }
    }

    /**
     * Ürün oluştur
     */
    private function createProduct() {
        $db = DB::conn();
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $phonePrefix = trim($_POST['phone_prefix'] ?? '0905');
        $perMinuteCost = (float)($_POST['per_minute_cost'] ?? 0.45);
        $isSingleUser = isset($_POST['is_single_user']) ? 1 : 0;
        $isCallbackEnabled = isset($_POST['is_callback_enabled']) ? 1 : 0;
        $price = (float)($_POST['price'] ?? 0);
        $isSubscription = isset($_POST['is_subscription']) ? 1 : 0;
        $subscriptionMonthlyFee = (float)($_POST['subscription_monthly_fee'] ?? 0);
        $setupFee = (float)($_POST['setup_fee'] ?? 0);
        
        if (empty($name) || $price <= 0) {
            $_SESSION['error'] = 'Ürün adı ve fiyat gerekli';
            return;
        }
        
        $stmt = $db->prepare('INSERT INTO agent_products (name, description, phone_prefix, per_minute_cost, is_single_user, is_callback_enabled, price, is_subscription, subscription_monthly_fee, setup_fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssdiiiddd', $name, $description, $phonePrefix, $perMinuteCost, $isSingleUser, $isCallbackEnabled, $price, $isSubscription, $subscriptionMonthlyFee, $setupFee);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Ürün başarıyla oluşturuldu';
        } else {
            $_SESSION['error'] = 'Ürün oluşturulurken hata oluştu';
        }
        
        $stmt->close();
        header('Location: ' . \App\Helpers\Url::to('/agents/manage-products'));
        exit;
    }

    /**
     * Ürün güncelle
     */
    private function updateProduct() {
        $db = DB::conn();
        
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $phonePrefix = trim($_POST['phone_prefix'] ?? '0905');
        $perMinuteCost = (float)($_POST['per_minute_cost'] ?? 0.45);
        $isSingleUser = isset($_POST['is_single_user']) ? 1 : 0;
        $isCallbackEnabled = isset($_POST['is_callback_enabled']) ? 1 : 0;
        $price = (float)($_POST['price'] ?? 0);
        $isSubscription = isset($_POST['is_subscription']) ? 1 : 0;
        $subscriptionMonthlyFee = (float)($_POST['subscription_monthly_fee'] ?? 0);
        $setupFee = (float)($_POST['setup_fee'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (!$id || empty($name) || $price <= 0) {
            $_SESSION['error'] = 'Geçersiz veriler';
            return;
        }
        
        $stmt = $db->prepare('UPDATE agent_products SET name=?, description=?, phone_prefix=?, per_minute_cost=?, is_single_user=?, is_callback_enabled=?, price=?, is_subscription=?, subscription_monthly_fee=?, setup_fee=?, is_active=?, updated_at=NOW() WHERE id=?');
        $stmt->bind_param('sssdiiidddii', $name, $description, $phonePrefix, $perMinuteCost, $isSingleUser, $isCallbackEnabled, $price, $isSubscription, $subscriptionMonthlyFee, $setupFee, $isActive, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Ürün başarıyla güncellendi';
        } else {
            $_SESSION['error'] = 'Ürün güncellenirken hata oluştu';
        }
        
        $stmt->close();
        header('Location: ' . \App\Helpers\Url::to('/agents/manage-products'));
        exit;
    }

    /**
     * Ürün sil
     */
    private function deleteProduct() {
        $db = DB::conn();
        $id = (int)($_POST['id'] ?? 0);
        
        if (!$id) {
            $_SESSION['error'] = 'Geçersiz ürün ID';
            return;
        }
        
        // Aktif satın alımları kontrol et
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM user_agents WHERE agent_product_id = ? AND status = "active"');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = 'Bu ürünün aktif satın alımları var. Silemezsiniz.';
            return;
        }
        
        $stmt = $db->prepare('DELETE FROM agent_products WHERE id = ?');
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Ürün başarıyla silindi';
        } else {
            $_SESSION['error'] = 'Ürün silinirken hata oluştu';
        }
        
        $stmt->close();
        header('Location: ' . \App\Helpers\Url::to('/agents/manage-products'));
        exit;
    }

    /**
     * Admin abonelik yönetimi dashboard'ı
     */
    public function subscriptions() {
        $this->requireAuth();
        $isSuper = $this->isSuper();
        $isGroupAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'groupadmin';
        
        if (!$isSuper && !$isGroupAdmin) {
            die('Yetkisiz erişim');
        }

        $db = DB::conn();
        $this->createAgentPurchaseTables();

        // Abonelik istatistikleri al
        $stats = SubscriptionManager::getSubscriptionStats();

        // Vadesi geçmiş ödemeler
        $overduePayments = SubscriptionManager::getOverduePayments();

        // Son 50 abonelik ödemesi
        $stmt = $db->prepare('
            SELECT sp.*, ua.agent_number, ap.name as product_name,
                   u.login as user_login, g.name as group_name, g.balance as group_balance
            FROM agent_subscription_payments sp
            JOIN user_agents ua ON sp.user_agent_id = ua.id
            JOIN agent_products ap ON ua.agent_product_id = ap.id
            JOIN users u ON sp.user_id = u.id
            JOIN groups g ON u.group_id = g.id
            ORDER BY sp.created_at DESC
            LIMIT 50
        ');
        $stmt->execute();
        $recentPayments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Aktif abonelikler (sadece aylık olanlar)
        if ($isSuper) {
            // Super admin tüm abonelikleri görür
            $stmt = $db->prepare('
                SELECT ua.*, ap.name as product_name, ap.subscription_monthly_fee, ap.is_subscription,
                       u.login as user_login, g.name as group_name, ua.created_at as subscription_start,
                       ua.next_subscription_due as subscription_end
                FROM user_agents ua
                JOIN agent_products ap ON ua.agent_product_id = ap.id
                JOIN users u ON ua.user_id = u.id
                JOIN groups g ON u.group_id = g.id
                WHERE ua.status = "active" AND ap.is_subscription = 1
                ORDER BY ua.next_subscription_due ASC
            ');
        } else {
            // Grup admin sadece kendi grubunun aboneliklerini görür
            $userGroupId = $_SESSION['user']['group_id'];
            $stmt = $db->prepare('
                SELECT ua.*, ap.name as product_name, ap.subscription_monthly_fee, ap.is_subscription,
                       u.login as user_login, g.name as group_name, ua.created_at as subscription_start,
                       ua.next_subscription_due as subscription_end
                FROM user_agents ua
                JOIN agent_products ap ON ua.agent_product_id = ap.id
                JOIN users u ON ua.user_id = u.id
                JOIN groups g ON u.group_id = g.id
                WHERE ua.status = "active" AND ap.is_subscription = 1 AND u.group_id = ?
                ORDER BY ua.next_subscription_due ASC
            ');
            $stmt->bind_param('i', $userGroupId);
        }
        $stmt->execute();
        $activeSubscriptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require __DIR__.'/../Views/agents/subscriptions.php';
    }

    /**
     * Manuel abonelik ödemesi işleme
     */
    public function processManualSubscription() {
        $this->requireAuth();
        if (!$this->isSuper()) {
            die('Yetkisiz erişim');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die('Geçersiz istek');
        }

        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if (!$paymentId || !in_array($action, ['approve', 'reject'])) {
            $_SESSION['error'] = 'Geçersiz parametreler';
            header('Location: ' . \App\Helpers\Url::to('/agents/subscriptions'));
            exit;
        }

        $db = DB::conn();

        try {
            // Ödeme bilgilerini al
            $stmt = $db->prepare('
                SELECT sp.*, ua.agent_number, ap.name as product_name, ap.subscription_monthly_fee,
                       u.login as user_login, g.name as group_name, g.balance as group_balance
                FROM agent_subscription_payments sp
                JOIN user_agents ua ON sp.user_agent_id = ua.id
                JOIN agent_products ap ON ua.agent_product_id = ap.id
                JOIN users u ON sp.user_id = u.id
                JOIN groups g ON u.group_id = g.id
                WHERE sp.id = ?
            ');
            $stmt->bind_param('i', $paymentId);
            $stmt->execute();
            $payment = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$payment) {
                $_SESSION['error'] = 'Ödeme bulunamadı';
                header('Location: ' . \App\Helpers\Url::to('/agents/subscriptions'));
                exit;
            }

            $db->begin_transaction();

            if ($action === 'approve') {
                // Ödemeyi onaylı olarak işaretle
                $stmt = $db->prepare('
                    UPDATE agent_subscription_payments
                    SET status = "paid", payment_date = NOW(), payment_method = "manual"
                    WHERE id = ?
                ');
                $stmt->bind_param('i', $paymentId);
                $stmt->execute();
                $stmt->close();

                // Sonraki ödemeyi planla
                $nextDueDate = date('Y-m-d', strtotime($payment['due_date'] . ' +1 month'));
                $stmt = $db->prepare('
                    INSERT INTO agent_subscription_payments
                    (user_agent_id, user_id, amount, due_date, status)
                    VALUES (?, ?, ?, ?, "pending")
                ');
                $stmt->bind_param('iids', $payment['user_agent_id'], $payment['user_id'], $payment['amount'], $nextDueDate);
                $stmt->execute();
                $stmt->close();

                // User agent'ın sonraki ödeme tarihini güncelle
                $stmt = $db->prepare('UPDATE user_agents SET next_subscription_due = ? WHERE id = ?');
                $stmt->bind_param('si', $nextDueDate, $payment['user_agent_id']);
                $stmt->execute();
                $stmt->close();

                // Eğer agent askıya alınmışsa aktif yap
                $stmt = $db->prepare('UPDATE user_agents SET status = "active" WHERE id = ?');
                $stmt->bind_param('i', $payment['user_agent_id']);
                $stmt->execute();
                $stmt->close();

                // Transaction kaydı oluştur
                $stmt = $db->prepare('
                    INSERT INTO transactions (group_id, type, amount, reference, description)
                    VALUES (?, "agent_subscription", ?, ?, ?)
                ');
                $groupId = (int)$payment['group_id'];
                $reference = "AGS-" . $paymentId . "-M";
                $description = "Manuel abonelik ödemesi: " . $payment['product_name'] . " (#" . $payment['agent_number'] . ")";
                $stmt->bind_param('idss', $groupId, $payment['amount'], $reference, $description);
                $stmt->execute();
                $stmt->close();

                $_SESSION['success'] = 'Abonelik ödemesi manuel olarak onaylandı';

            } else { // reject
                // Ödemeyi reddedildi olarak işaretle
                $stmt = $db->prepare('UPDATE agent_subscription_payments SET status = "failed" WHERE id = ?');
                $stmt->bind_param('i', $paymentId);
                $stmt->execute();
                $stmt->close();

                $_SESSION['success'] = 'Abonelik ödemesi reddedildi';
            }

            $db->commit();

        } catch (Exception $e) {
            $db->rollback();
            error_log('Manual subscription processing error: ' . $e->getMessage());
            $_SESSION['error'] = 'İşlem başarısız: ' . $e->getMessage();
        }

        header('Location: ' . \App\Helpers\Url::to('/agents/subscriptions'));
        exit;
    }

    /**
     * Abonelik cron job endpoint'i
     */
    public function subscriptionCron() {
        $token = $_GET['token'] ?? ($_POST['token'] ?? '');
        $expected = getenv('CRON_TOKEN') ?: '';
        if (!$expected || $token !== $expected) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        header('Content-Type: application/json');

        try {
            $result = SubscriptionManager::processSubscriptionPayments();

            echo json_encode([
                'success' => true,
                'processed' => $result['processed'],
                'failed' => $result['failed'],
                'total_checked' => $result['total_checked'],
                'errors' => $result['errors'],
                'timestamp' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}