<?php
/**
 * Balance cron job testi
 * Bu script cron_balance.php'yi test eder ve debug çıktısı verir
 */

require_once __DIR__ . '/config/bootstrap.php';

use App\Helpers\DB;
use App\Helpers\TelegramNotifier;

echo "=== BALANCE CRON TEST - " . date('Y-m-d H:i:s') . " ===\n";

try {
    // Veritabanı bağlantısını test et
    $db = DB::conn();
    echo "✓ Veritabanı bağlantısı başarılı\n";
    
    // Aktif telegram gruplarını kontrol et
    $stmt = $db->prepare('
        SELECT id, name, balance, telegram_chat_id, telegram_language, telegram_enabled 
        FROM groups 
        WHERE telegram_enabled = 1 AND telegram_chat_id IS NOT NULL AND telegram_chat_id != ""
    ');
    $stmt->execute();
    $activeGroups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    echo "\n=== AKTİF TELEGRAM GRUPLARI ===\n";
    echo "Toplam grup sayısı: " . count($activeGroups) . "\n\n";
    
    if (count($activeGroups) === 0) {
        echo "❌ SORUN: Hiç aktif telegram grubu bulunamadı!\n";
        echo "Kontrol edilecekler:\n";
        echo "- telegram_enabled = 1\n";
        echo "- telegram_chat_id IS NOT NULL AND telegram_chat_id != ''\n\n";
        
        // Tüm grupları göster
        $allStmt = $db->prepare('SELECT id, name, telegram_chat_id, telegram_language, telegram_enabled FROM groups ORDER BY id');
        $allStmt->execute();
        $allGroups = $allStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $allStmt->close();
        
        echo "=== TÜM GRUPLAR ===\n";
        foreach ($allGroups as $i => $group) {
            echo ($i+1) . ". Grup: {$group['name']}\n";
            echo "   ID: {$group['id']}\n";
            echo "   Chat ID: " . ($group['telegram_chat_id'] ?: 'BOŞ') . "\n";
            echo "   Dil: " . ($group['telegram_language'] ?: 'BOŞ') . "\n";
            echo "   Aktif: {$group['telegram_enabled']}\n";
            echo "   ---\n";
        }
        exit(1);
    }
    
    $testCount = 0;
    $successCount = 0;
    
    foreach ($activeGroups as $group) {
        $testCount++;
        
        echo "{$testCount}. Test edilen grup: {$group['name']}\n";
        echo "   ID: {$group['id']}\n";
        echo "   Bakiye: {$group['balance']} USD\n";
        echo "   Chat ID: {$group['telegram_chat_id']}\n";
        echo "   Dil: " . ($group['telegram_language'] ?: 'TR') . "\n";
        
        // Chat ID kontrol et
        $chatId = trim($group['telegram_chat_id']);
        if (empty($chatId)) {
            echo "   ❌ Chat ID boş!\n";
            continue;
        }
        
        // Test mesajı gönder
        $notifier = new TelegramNotifier(null, $chatId, $group['telegram_language'] ?: 'TR');
        $testMessage = "🧪 *TEST MESAJI*\n\n";
        $testMessage .= "Grup: *{$group['name']}*\n";
        $testMessage .= "Test Zamanı: *" . date('d.m.Y H:i:s') . "*\n";
        $testMessage .= "Bu bir test mesajıdır. ✅";
        
        echo "   → Test mesajı gönderiliyor...\n";
        
        if ($notifier->sendMessage($testMessage)) {
            echo "   ✅ Test mesajı başarıyla gönderildi!\n";
            $successCount++;
        } else {
            echo "   ❌ Test mesajı gönderilemedi!\n";
        }
        
        echo "   ---\n";
        
        // Rate limiting için bekle
        if ($testCount < count($activeGroups)) {
            sleep(2);
        }
    }
    
    echo "\n=== TEST SONUCU ===\n";
    echo "Toplam Test: {$testCount}\n";
    echo "Başarılı: {$successCount}\n";
    echo "Başarısız: " . ($testCount - $successCount) . "\n";
    
    if ($successCount === $testCount) {
        echo "✅ Tüm testler başarılı! cron_balance.php düzgün çalışacak.\n";
    } else {
        echo "⚠️  Bazı gruplar için mesaj gönderilemedi. Telegram ayarlarını kontrol edin.\n";
    }
    
} catch (Exception $e) {
    echo "❌ TEST HATASI: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST TAMAMLANDI ===\n";