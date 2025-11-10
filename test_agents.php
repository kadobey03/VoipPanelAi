<?php
require_once __DIR__.'/app/Helpers/ApiClient.php';

echo "=== Agent Güncellemesi Test ===\n";
echo "Tarih: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $api = new App\Helpers\ApiClient();
    echo "API Client oluşturuldu ✅\n";
    
    echo "Agent durumları çekiliyor...\n";
    $agents = $api->getAgentsStatus();
    
    echo "API Response:\n";
    echo json_encode($agents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "Toplam Agent Sayısı: " . count($agents) . "\n";
    
    if (count($agents) > 0) {
        echo "\n=== Agent Detayları ===\n";
        foreach ($agents as $agent) {
            echo sprintf("Exten: %s | Login: %s | Status: %s | Group: %s\n", 
                $agent['exten'] ?? 'N/A',
                $agent['user_login'] ?? 'N/A', 
                $agent['status'] ?? 'N/A',
                $agent['group'] ?? 'N/A'
            );
        }
    }
    
    echo "\n✅ Test başarılı! API ile agent bilgileri alınabilir.\n";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}