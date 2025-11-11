<?php
/**
 * Cron: Daily balance & stats Telegram notifier
 * Her gruba kendi dilinde günlük bakiye ve istatistik raporu gönderir
 * Cron ayarı: Her gün sabah 9:00'da çalıştır
 * 0 9 * * * /usr/bin/php /path/to/project/cron_balance.php
 */

require_once __DIR__ . '/config/bootstrap.php';

use App\Helpers\DB;
use App\Helpers\TelegramNotifier;

// Output buffering başlat
ob_start();

echo "=== Günlük Bakiye Raporu Cron Job - " . date('Y-m-d H:i:s') . " ===\n";

// Zaman dilimi
date_default_timezone_set('Europe/Kiev');

// Config
const LOW_BALANCE_USD = 10.0;
const DAY_START = 8;   // inclusive
const DAY_END = 22;    // inclusive

try {
    // Veritabanı bağlantısını test et
    $db = DB::conn();
    echo "✓ Veritabanı bağlantısı başarılı\n";
    
    // Çalışma saatleri kontrolü
    $currentHour = (int)date('H');
    $isDaytime = ($currentHour >= DAY_START && $currentHour <= DAY_END);
    
    if (!$isDaytime) {
        echo "Gece saatleri, rapor gönderilmiyor.\n";
        exit(0);
    }
    
    // Selamlama mesajını belirle
    if ($currentHour >= 8 && $currentHour < 12) {
        $greeting = "Günaydın! ☀️";
        $greetingEn = "Good morning! ☀️";
        $greetingRu = "Доброе утро! ☀️";
    } elseif ($currentHour >= 12 && $currentHour < 17) {
        $greeting = "İyi günler! 🌞";
        $greetingEn = "Good afternoon! 🌞";
        $greetingRu = "Добрый день! 🌞";
    } else {
        $greeting = "İyi akşamlar! 🌙";
        $greetingEn = "Good evening! 🌙";
        $greetingRu = "Добрый вечер! 🌙";
    }
    
    $today = date('Y-m-d');
    $sendOkCount = 0;
    $sendFailCount = 0;
    $lowBalanceGroups = 0;
    
    // Telegram aktif grupları al
    $stmt = $db->prepare('
        SELECT id, name, balance, telegram_chat_id, telegram_language, telegram_enabled 
        FROM groups 
        WHERE telegram_enabled = 1 AND telegram_chat_id IS NOT NULL AND telegram_chat_id != ""
    ');
    $stmt->execute();
    $activeGroups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    echo "Aktif telegram grubu sayısı: " . count($activeGroups) . "\n";
    
    // Her grup için rapor gönder
    foreach ($activeGroups as $group) {
        try {
            $groupId = (int)$group['id'];
            $groupName = $group['name'];
            $balance = (float)$group['balance'];
            $language = $group['telegram_language'] ?: 'TR';
            
            if ($balance <= LOW_BALANCE_USD) {
                $lowBalanceGroups++;
            }
            
            // Bu grup için günlük istatistikleri çek
            $statsStmt = $db->prepare("
                SELECT 
                    COUNT(*) AS total_calls,
                    SUM(CASE WHEN disposition = 'ANSWERED' THEN 1 ELSE 0 END) AS answered_calls,
                    SUM(COALESCE(billsec,0)) AS total_billsec,
                    SUM(COALESCE(amount_charged,0)) AS total_spending
                FROM calls
                WHERE group_id = ? AND DATE(created_at) = ?
            ");
            $statsStmt->bind_param('is', $groupId, $today);
            $statsStmt->execute();
            $statsResult = $statsStmt->get_result()->fetch_assoc();
            $statsStmt->close();
            
            $totalCalls = (int)($statsResult['total_calls'] ?? 0);
            $answeredCalls = (int)($statsResult['answered_calls'] ?? 0);
            $totalBillsec = (int)($statsResult['total_billsec'] ?? 0);
            $totalSpending = (float)($statsResult['total_spending'] ?? 0);
            
            $talkedMinutes = round($totalBillsec / 60, 1);
            
            // Telegram bildirimi gönder
            $notifier = new TelegramNotifier(null, $group['telegram_chat_id'], $language);
            
            // Dil bazında mesaj hazırla
            if ($language === 'EN') {
                $message = "{$greetingEn}\n\n";
                $message .= "📊 *Daily Balance Summary*\n\n";
                $message .= "Group: *" . $groupName . "*\n";
                $message .= "Current Balance: *" . number_format($balance, 2) . " USD*\n\n";
                $message .= "📈 *Today's Statistics:*\n";
                $message .= "Total Calls: *{$totalCalls}*\n";
                $message .= "Answered Calls: *{$answeredCalls}*\n";
                $message .= "Talk Time: *{$talkedMinutes} min*\n";
                $message .= "Amount Spent: *" . number_format($totalSpending, 2) . " USD*\n\n";
                $message .= "🌐 You can make online payments through our website to automatically top up your balance and purchase new numbers.\n\n";
                $message .= "Thank you for choosing us! 🙏";
                
                // Düşük bakiye uyarısı
                if ($balance <= LOW_BALANCE_USD) {
                    $warnMessage = "⚠️ *Low Balance Warning!*\n\n";
                    $warnMessage .= "Group: *{$groupName}*\n";
                    $warnMessage .= "Current Balance: *" . number_format($balance, 2) . " USD*\n\n";
                    $warnMessage .= "Please top up your balance as soon as possible. 💳\n\n";
                    $warnMessage .= "[💰 Top Up Balance](https://crm.akkocbilisim.com/VoipPanelAi/balance/topup)\n\n";
                    $warnMessage .= "🌐 You can make online payments through our website to automatically top up your balance and purchase new numbers.\n\n";
                    $warnMessage .= "Thank you for choosing us! 🙏";
                }
                
            } elseif ($language === 'RU') {
                $message = "{$greetingRu}\n\n";
                $message .= "📊 *Ежедневная сводка баланса*\n\n";
                $message .= "Группа: *" . $groupName . "*\n";
                $message .= "Текущий баланс: *" . number_format($balance, 2) . " USD*\n\n";
                $message .= "📈 *Статистика за сегодня:*\n";
                $message .= "Всего звонков: *{$totalCalls}*\n";
                $message .= "Отвеченных звонков: *{$answeredCalls}*\n";
                $message .= "Время разговора: *{$talkedMinutes} мин*\n";
                $message .= "Потрачено: *" . number_format($totalSpending, 2) . " USD*\n\n";
                $message .= "🌐 Вы можете совершать онлайн-платежи через наш сайт, чтобы автоматически пополнить баланс и купить новые номера.\n\n";
                $message .= "Спасибо, что выбрали нас! 🙏";
                
                // Предупреждение о низком балансе
                if ($balance <= LOW_BALANCE_USD) {
                    $warnMessage = "⚠️ *Предупреждение о низком балансе!*\n\n";
                    $warnMessage .= "Группа: *{$groupName}*\n";
                    $warnMessage .= "Текущий баланс: *" . number_format($balance, 2) . " USD*\n\n";
                    $warnMessage .= "Пожалуйста, пополните баланс как можно скорее. 💳\n\n";
                    $warnMessage .= "[💰 Пополнить Баланс](https://crm.akkocbilisim.com/VoipPanelAi/balance/topup)\n\n";
                    $warnMessage .= "🌐 Вы можете совершать онлайн-платежи через наш сайт, чтобы автоматически пополнить баланс и купить новые номера.\n\n";
                    $warnMessage .= "Спасибо, что выбрали нас! 🙏";
                }
                
            } else { // TR
                $message = "{$greeting}\n\n";
                $message .= "📊 *Günlük Bakiye Özeti*\n\n";
                $message .= "Grup: *" . $groupName . "*\n";
                $message .= "Kalan Bakiye: *" . number_format($balance, 2) . " USD*\n\n";
                $message .= "📈 *Bugünkü İstatistikler:*\n";
                $message .= "Toplam Arama: *{$totalCalls}*\n";
                $message .= "Cevaplanan Arama: *{$answeredCalls}*\n";
                $message .= "Konuşma Süresi: *{$talkedMinutes} dk*\n";
                $message .= "Harcanan Tutar: *" . number_format($totalSpending, 2) . " USD*\n\n";
                $message .= "🌐 Sitemiz üzerinden Online Ödeme Yaparak Otomatik bakiye yükleyebilir, Yeni Numara Satın alabilirsiniz.\n\n";
                $message .= "Bizi tercih ettiğiniz için teşekkürler! 🙏";
                
                // Düşük bakiye uyarısı
                if ($balance <= LOW_BALANCE_USD) {
                    $warnMessage = "⚠️ *Bakiyeniz Azaldı!*\n\n";
                    $warnMessage .= "Grup: *{$groupName}*\n";
                    $warnMessage .= "Kalan Bakiye: *" . number_format($balance, 2) . " USD*\n\n";
                    $warnMessage .= "Lütfen en kısa sürede bakiye yükleyin. 💳\n\n";
                    $warnMessage .= "[💰 Bakiye Yükle](https://crm.akkocbilisim.com/VoipPanelAi/balance/topup)\n\n";
                    $warnMessage .= "🌐 Sitemiz üzerinden Online Ödeme Yaparak Otomatik bakiye yükleyebilir, Yeni Numara Satın alabilirsiniz.\n\n";
                    $warnMessage .= "Bizi tercih ettiğiniz için teşekkürler! 🙏";
                }
            }
            
            // Mesajları gönder
            if ($notifier->sendMessage($message)) {
                $sendOkCount++;
                echo "✓ Grup '{$groupName}' için rapor gönderildi ({$language})\n";
                
                // Düşük bakiye uyarısı gönder
                if ($balance <= LOW_BALANCE_USD && isset($warnMessage)) {
                    if ($notifier->sendMessage($warnMessage)) {
                        echo "⚠ Grup '{$groupName}' için düşük bakiye uyarısı gönderildi\n";
                    }
                }
            } else {
                $sendFailCount++;
                echo "✗ Grup '{$groupName}' için rapor gönderilemedi\n";
            }
            
        } catch (Exception $e) {
            $sendFailCount++;
            echo "⚠ Grup '{$group['name']}' için hata: " . $e->getMessage() . "\n";
        }
    }
    
    // Admin raporu için genel istatistikler
    if (count($activeGroups) > 0) {
        try {
            // Toplam istatistikler
            $totalStatsStmt = $db->prepare("
                SELECT 
                    COUNT(*) AS total_calls_all,
                    SUM(COALESCE(cost_api,0)) AS total_cost,
                    SUM(COALESCE(amount_charged,0)) AS total_sales
                FROM calls
                WHERE DATE(created_at) = ?
            ");
            $totalStatsStmt->bind_param('s', $today);
            $totalStatsStmt->execute();
            $totalStatsResult = $totalStatsStmt->get_result()->fetch_assoc();
            $totalStatsStmt->close();
            
            $totalCallsAll = (int)($totalStatsResult['total_calls_all'] ?? 0);
            $totalCost = (float)($totalStatsResult['total_cost'] ?? 0);
            $totalSales = (float)($totalStatsResult['total_sales'] ?? 0);
            $totalProfit = $totalSales - $totalCost;
            
            // Toplam bakiye
            $totalBalanceStmt = $db->prepare('SELECT SUM(COALESCE(balance,0)) AS total_balance FROM groups');
            $totalBalanceStmt->execute();
            $totalBalanceResult = $totalBalanceStmt->get_result()->fetch_assoc();
            $totalBalanceStmt->close();
            $totalBalanceAll = (float)($totalBalanceResult['total_balance'] ?? 0);
            
            // Admin mesajı (varsayılan telegram kanalına)
            $adminNotifier = new TelegramNotifier(); // Varsayılan ayarları kullan
            
            $adminMessage = "{$greeting}\n\n";
            $adminMessage .= "📈 *Günlük Genel Bakiye Raporu*\n\n";
            $adminMessage .= "📅 *Tarih:* " . date('d.m.Y') . "\n";
            $adminMessage .= "📊 *Toplam Arama:* {$totalCallsAll}\n";
            $adminMessage .= "💸 *API Maliyeti:* " . number_format($totalCost, 2) . " USD\n";
            $adminMessage .= "💰 *Toplam Satış:* " . number_format($totalSales, 2) . " USD\n";
            $adminMessage .= "🎯 *Net Kâr:* " . number_format($totalProfit, 2) . " USD\n";
            $adminMessage .= "🏦 *Toplam Bakiye:* " . number_format($totalBalanceAll, 2) . " USD\n";
            $adminMessage .= "⚠️ *Düşük Bakiye Grubu:* {$lowBalanceGroups}\n";
            $adminMessage .= "✉️ *Mesaj Durumu:* OK {$sendOkCount} / FAIL {$sendFailCount}\n\n";
            
            // Her grubun bakiyesini ekle
            $adminMessage .= "💼 *Grup Bakiyeleri:*\n";
            foreach ($activeGroups as $group) {
                $groupBalance = number_format((float)$group['balance'], 2);
                $lowBadge = ((float)$group['balance'] <= LOW_BALANCE_USD) ? " ⚠️" : "";
                $adminMessage .= "• {$group['name']}: {$groupBalance} USD{$lowBadge}\n";
            }
            $adminMessage .= "\n⏰ *Rapor Zamanı:* " . date('H:i:s') . "\n";
            
            $adminNotifier->sendMessage($adminMessage);
            echo "✓ Admin raporu gönderildi\n";
            
        } catch (Exception $e) {
            echo "⚠ Admin raporu gönderilemedi: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Balance Cron başarıyla tamamlandı ===\n";
    echo "Gönderim sonucu: OK {$sendOkCount} / FAIL {$sendFailCount}\n";
    
} catch (Exception $e) {
    echo "❌ HATA: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    
    // Hata durumunda admin bildirimi gönder
    try {
        // Her gruba kendi dilinde hata bildirimi gönder
        $stmt = $db->prepare('SELECT id, name, telegram_chat_id, telegram_language, telegram_enabled FROM groups WHERE telegram_enabled = 1 AND telegram_chat_id IS NOT NULL');
        $stmt->execute();
        $activeGroups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        foreach ($activeGroups as $group) {
            try {
                $notifier = new TelegramNotifier(null, $group['telegram_chat_id'], $group['telegram_language'] ?: 'TR');
                
                if ($group['telegram_language'] === 'EN') {
                    $errorMsg = "🚨 *BALANCE CRON JOB ERROR*\n\n";
                    $errorMsg .= "📅 *Date:* " . date('d.m.Y H:i') . "\n";
                    $errorMsg .= "❌ *Error:* " . $e->getMessage();
                } elseif ($group['telegram_language'] === 'RU') {
                    $errorMsg = "🚨 *ОШИБКА CRON JOB БАЛАНСА*\n\n";
                    $errorMsg .= "📅 *Дата:* " . date('d.m.Y H:i') . "\n";
                    $errorMsg .= "❌ *Ошибка:* " . $e->getMessage();
                } else { // TR
                    $errorMsg = "🚨 *BAKİYE CRON JOB HATASI*\n\n";
                    $errorMsg .= "📅 *Tarih:* " . date('d.m.Y H:i') . "\n";
                    $errorMsg .= "❌ *Hata:* " . $e->getMessage();
                }
                
                $notifier->sendMessage($errorMsg);
                
            } catch (Exception $telegramError) {
                echo "Grup '{$group['name']}' için hata bildirimi gönderilemedi\n";
            }
        }
        
        // Eğer hiç aktif grup yoksa varsayılan gönder
        if (empty($activeGroups)) {
            $notifier = new TelegramNotifier();
            $errorMsg = "🚨 *BAKİYE CRON JOB HATASI*\n\n";
            $errorMsg .= "📅 *Tarih:* " . date('d.m.Y H:i') . "\n";
            $errorMsg .= "❌ *Hata:* " . $e->getMessage();
            $notifier->sendMessage($errorMsg);
        }
        
    } catch (Exception $telegramError) {
        echo "Telegram hata bildirimi gönderilemedi: " . $telegramError->getMessage() . "\n";
    }
    
    exit(1);
}

// Output'u al ve logla
$output = ob_get_clean();
echo $output;

// Log dosyasına kaydet
$logFile = __DIR__ . '/logs/balance_cron_' . date('Y-m') . '.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

file_put_contents($logFile, date('Y-m-d H:i:s') . " - Balance Cron Run\n" . $output . "\n\n", FILE_APPEND | LOCK_EX);

exit(0);