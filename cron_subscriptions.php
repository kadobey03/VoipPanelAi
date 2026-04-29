<?php
/**
 * Agent Abonelik Ödemelerini İşleyen Cron Job
 * Her gün çalıştırılmalıdır: 0 6 * * * /usr/bin/php /path/to/project/cron_subscriptions.php
 */

require_once __DIR__ . '/config/bootstrap.php';

use App\Helpers\DB;
use App\Helpers\SubscriptionManager;
use App\Helpers\TelegramNotifier;

// Output buffering başlat
ob_start();

echo "=== Agent Abonelik Ödemelerini İşleme - " . date('Y-m-d H:i:s') . " ===\n";

try {
    // Veritabanı bağlantısını test et
    $db = DB::conn();
    echo "✓ Veritabanı bağlantısı başarılı\n";
    
    // Abonelik ödemelerini işle
    echo "Abonelik ödemelerini işleme başlıyor...\n";
    
    $result = SubscriptionManager::processSubscriptionPayments();
    
    echo "✓ İşlem tamamlandı:\n";
    echo "  - Kontrol edilen ödeme: {$result['total_checked']}\n";
    echo "  - Başarılı ödeme: {$result['processed']}\n";
    echo "  - Başarısız ödeme: {$result['failed']}\n";
    
    if (!empty($result['errors'])) {
        echo "⚠ Hatalar:\n";
        foreach ($result['errors'] as $error) {
            echo "  - " . $error . "\n";
        }
    }
    
    // Abonelik istatistiklerini al
    $stats = SubscriptionManager::getSubscriptionStats();
    echo "\n=== Abonelik İstatistikleri ===\n";
    echo "Aktif abonelikler: {$stats['active_subscriptions']}\n";
    echo "Bu ay gelir: $" . number_format($stats['monthly_revenue'], 2) . "\n";
    echo "Vadesi geçmiş ödeme sayısı: {$stats['overdue_count']}\n";
    echo "Vadesi geçmiş tutar: $" . number_format($stats['overdue_amount'], 2) . "\n";
    echo "Askıya alınan agent: {$stats['suspended_count']}\n";
    
    // Vadesi geçmiş ödemeleri raporla
    $overduePayments = SubscriptionManager::getOverduePayments();
    if (!empty($overduePayments)) {
        echo "\n=== Vadesi Geçmiş Ödemeler ===\n";
        foreach ($overduePayments as $payment) {
            echo "- User: {$payment['user_login']}, Agent: {$payment['agent_number']}, ";
            echo "Tutar: $" . number_format($payment['amount'], 2) . ", ";
            echo "Gecikme: {$payment['days_overdue']} gün\n";
        }
    }
    
    // Eğer işlenecek ödeme varsa admin bildirimi gönder
    if ($result['total_checked'] > 0 || !empty($overduePayments)) {
        try {
            $notifier = new TelegramNotifier();
            $date = date('d\.m\.Y'); // MarkdownV2 escape
            $message = "📊 *Günlük Abonelik Raporu* \- {$date}\n\n";
            $message .= "✅ *Başarılı Ödeme:* {$result['processed']}\n";
            $message .= "❌ *Başarısız Ödeme:* {$result['failed']}\n";
            $message .= "📈 *Aktif Abonelik:* {$stats['active_subscriptions']}\n";
            $message .= "💰 *Aylık Gelir:* " . number_format($stats['monthly_revenue'], 2) . " USD\n";

            if ($stats['overdue_count'] > 0) {
                $message .= "\n⚠️ *Vadesi Geçmiş:* {$stats['overdue_count']} ödeme \(" . number_format($stats['overdue_amount'], 2) . " USD\)\n";
            }

            if ($stats['suspended_count'] > 0) {
                $message .= "🚫 *Askıya Alınan:* {$stats['suspended_count']} agent\n";
            }

            if (!empty($result['errors'])) {
                $message .= "\n🔧 *Hatalar:* " . count($result['errors']) . " adet\n";
            }

            $message .= "\n⏰ *Zaman:* " . date('H:i:s') . "\n";

            // sendToAdmins yerine sendMessage kullan (default bot/chat'e gönderir)
            if ($notifier->sendMessage($message)) {
                echo "✓ Abonelik raporu Telegram'a gönderildi\n";
            } else {
                echo "✗ Abonelik raporu Telegram'a gönderilemedi\n";
            }

        } catch (Exception $e) {
            echo "Telegram bildirimi gönderilirken hata: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== İşlem başarıyla tamamlandı ===\n";
    
} catch (Exception $e) {
    echo "❌ HATA: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    
    // Hata durumunda admin bildirimi gönder
    try {
        $notifier = new TelegramNotifier();
        $safeErr  = preg_replace('/([_\*\[\]\(\)~`>#+\-=|{}.!\\\\])/', '\\\\$1', $e->getMessage());
        $message  = "🚨 *Abonelik Cron Job Hatası*\n\n";
        $message .= "📅 *Tarih:* " . date('d\.m\.Y H:i') . "\n";
        $message .= "❌ *Hata:* " . $safeErr . "\n";
        $notifier->sendMessage($message);
    } catch (Exception $telegramError) {
        echo "Telegram hata bildirimi gönderilemedi: " . $telegramError->getMessage() . "\n";
    }
    
    exit(1);
}

// Output'u al ve logla
$output = ob_get_clean();
echo $output;

// Log dosyasına kaydet
$logFile = __DIR__ . '/logs/subscription_cron_' . date('Y-m') . '.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

file_put_contents($logFile, date('Y-m-d H:i:s') . " - Subscription Cron Run\n" . $output . "\n\n", FILE_APPEND | LOCK_EX);

exit(0);