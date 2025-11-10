<?php
// Agent liste güncelleme cron job'u
require_once __DIR__.'/config/bootstrap.php';

use App\Helpers\ApiClient;
use App\Helpers\DB;

$logFile = __DIR__.'/cron_agents.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
    echo "[$timestamp] $message\n";
}

try {
    logMessage("=== Agent Cron Başlatıldı ===");
    
    $api = new App\Helpers\ApiClient();
    $db = App\Helpers\DB::conn();
    
    // API'den agent listesini çek
    $agentsApi = $api->getAgentsStatus();
    logMessage("API'den " . count($agentsApi) . " agent bilgisi alındı");
    
    $imported = 0;
    $updated = 0;
    $errors = [];
    
    foreach ($agentsApi as $agent) {
        $exten = $agent['exten'];
        $login = $agent['user_login'] ?? '';
        $apiGroup = $agent['group'] ?? '';
        $localGroup = '';
        
        // Grup eşleştirmesi
        if ($apiGroup) {
            $stmt = $db->prepare('SELECT name FROM groups WHERE api_group_name=?');
            $stmt->bind_param('s', $apiGroup);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            if ($r) $localGroup = $r['name'];
            $stmt->close();
            
            // Eşleşme yoksa API adını kullan
            if (!$localGroup) {
                $localGroup = $apiGroup;
            }
        }
        
        // Agent var mı kontrol et
        $stmt = $db->prepare('SELECT id FROM agents WHERE exten=?');
        $stmt->bind_param('s', $exten);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Mevcut agent güncelle
            $stmt->close();
            $stmt = $db->prepare('UPDATE agents SET user_login=?, group_name=?, updated_at=NOW() WHERE exten=?');
            $stmt->bind_param('sss', $login, $localGroup, $exten);
            $stmt->execute();
            $stmt->close();
            $updated++;
        } else {
            // Yeni agent ekle
            $stmt->close();
            $stmt = $db->prepare('INSERT INTO agents (exten, user_login, group_name) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $exten, $login, $localGroup);
            $stmt->execute();
            $stmt->close();
            $imported++;
        }
    }
    
    logMessage("✅ İşlem tamamlandı: $imported yeni, $updated güncellendi");
    
} catch (Exception $e) {
    $errorMsg = "❌ Hata: " . $e->getMessage();
    logMessage($errorMsg);
    $errors[] = $errorMsg;
}

logMessage("=== Agent Cron Tamamlandı ===\n");
?>