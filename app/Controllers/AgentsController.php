<?php
namespace App\Controllers;

use App\Helpers\ApiClient;
use App\Helpers\DB;

class AgentsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }

    /**
     * Grup adlarını akıllıca karşılaştır
     */
    private function isGroupMatch($agentGroupName, $userGroupName, $userApiGroupName) {
        if (empty($agentGroupName) || (empty($userGroupName) && empty($userApiGroupName))) {
            return false;
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
        
        // Exact match first
        if (strcasecmp($agentGroupName, $userGroupName) === 0) return true;
        if ($userApiGroupName && strcasecmp($agentGroupName, $userApiGroupName) === 0) return true;
        
        // Normalized match
        $normalizedUser = $normalize($userGroupName);
        if ($normalizedAgent === $normalizedUser) return true;
        
        if ($userApiGroupName) {
            $normalizedApi = $normalize($userApiGroupName);
            if ($normalizedAgent === $normalizedApi) return true;
        }
        
        // Partial match (contains)
        if (!empty($normalizedAgent) && !empty($normalizedUser)) {
            if (strpos($normalizedAgent, $normalizedUser) !== false || strpos($normalizedUser, $normalizedAgent) !== false) {
                return true;
            }
        }
        
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
            foreach ($agents as $agent) {
                if ($isUser) {
                    // User sadece kendi agent'ını görür
                    $group = 'Kendi Agentınız';
                    if (!isset($agentsByGroup[$group])) {
                        $agentsByGroup[$group] = ['groupName' => $group, 'agents' => []];
                    }
                    $agentsByGroup[$group]['agents'][] = $agent;
                } elseif ($isGroupMember) {
                    // Groupmember sadece kendi agent'ını görür
                    $group = 'Kendi Agentınız';
                    if (!isset($agentsByGroup[$group])) {
                        $agentsByGroup[$group] = ['groupName' => $group, 'agents' => []];
                    }
                    $agentsByGroup[$group]['agents'][] = $agent;
                } else {
                    // Grup admini kendi grubu agentlerini görür
                    $group = $agent['group'] ?? $agent['group_name'] ?? '';
                    
                    if ($this->isGroupMatch($group, $userGroupName, $userApiGroupName)) {
                        // Grup adı olarak local grup adını kullan
                        $displayGroupName = $userGroupName ?: $group;
                        if (!isset($agentsByGroup[$displayGroupName])) {
                            $agentsByGroup[$displayGroupName] = ['groupName' => $displayGroupName, 'agents' => []];
                        }
                        $agentsByGroup[$displayGroupName]['agents'][] = $agent;
                    }
                }
            }
        }

        // DEBUGGING İÇİN (production'da kaldırın):
        error_log("=== AGENTS DEBUG ===");
        error_log("UserGroupName: '" . $userGroupName . "'");
        error_log("UserApiGroupName: '" . $userApiGroupName . "'");
        error_log("GroupId: " . ($groupId ?? 'none'));
        error_log("IsSuper: " . ($isSuper ? 'true' : 'false'));
        error_log("Total agentsDb before filter: " . count($agentsDb));
        
        // Agent'ların grup isimlerini logla
        foreach ($agentsDb as $i => $agent) {
            error_log("Agent[$i]: exten=" . ($agent['exten'] ?? 'null') . ", group_name='" . ($agent['group_name'] ?? 'null') . "', active=" . ($agent['active'] ?? 'null'));
            if (!$isSuper && !$isUser && !$isGroupMember) {
                $isMatch = $this->isGroupMatch($agent['group_name'] ?? '', $userGroupName, $userApiGroupName);
                error_log("  -> isGroupMatch: " . ($isMatch ? 'YES' : 'NO'));
            }
        }
        
        error_log("Agents count after filter: " . count($agents));
        error_log("AgentsByGroup keys: " . implode(', ', array_keys($agentsByGroup)));
        error_log("=== END DEBUG ===");

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
}