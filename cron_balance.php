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
    
    // MarkdownV2 escape yardımcı fonksiyon
    $esc = function(string $text): string {
        return preg_replace('/([_\*\[\]\(\)~`>#+\-=|{}.!\\\\])/', '\\\\$1', $text);
    };

    // Her grup için rapor gönder
    foreach ($activeGroups as $group) {
        try {
            $groupId   = (int)$group['id'];
            $groupName = $group['name'];
            $balance   = (float)$group['balance'];
            $language  = $group['telegram_language'] ?: 'TR';
            $chatId    = trim($group['telegram_chat_id']);

            // Chat ID kontrol et - boş ise atla
            if (empty($chatId)) {
                echo "⚠ Grup '{$groupName}' için telegram_chat_id boş, atlanıyor\n";
                $sendFailCount++;
                continue;
            }

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

            $totalCalls    = (int)($statsResult['total_calls']    ?? 0);
            $answeredCalls = (int)($statsResult['answered_calls'] ?? 0);
            $totalBillsec  = (int)($statsResult['total_billsec']  ?? 0);
            $totalSpending = (float)($statsResult['total_spending'] ?? 0);
            $talkedMinutes = round($totalBillsec / 60, 1);

            // Grup adını ve sayısal değerleri escape et
            $safeName    = $esc($groupName);
            $safeBalance = $esc(number_format($balance, 2));
            $safeCalls   = $totalCalls;
            $safeAnsw    = $answeredCalls;
            $safeMin     = $esc((string)$talkedMinutes);
            $safeSpend   = $esc(number_format($totalSpending, 2));
            $safeDate    = $esc(date('d.m.Y'));
            $safeTime    = $esc(date('H:i:s'));

            echo "→ Grup '{$groupName}' için mesaj hazırlanıyor (Chat ID: {$chatId})\n";
            $notifier = new TelegramNotifier(null, $chatId, $language);

            // Dil bazında mesaj hazırla
            if ($language === 'EN') {
                $safeGreet = $esc($greetingEn);
                $message  = "{$safeGreet}\n\n";
                $message .= "📊 *Daily Balance Summary*\n\n";
                $message .= "🏢 *Group:* {$safeName}\n";
                $message .= "💰 *Balance:* {$safeBalance} USD\n\n";
                $message .= "📈 *Today's Stats:*\n";
                $message .= "📞 Total Calls: *{$safeCalls}*\n";
                $message .= "✅ Answered: *{$safeAnsw}*\n";
                $message .= "⏱ Talk Time: *{$safeMin} min*\n";
                $message .= "💸 Spent: *{$safeSpend} USD*\n\n";
                $message .= "🌐 Top up online to keep your service running\.\n\n";
                $message .= "Thank you for choosing us\! 🙏\n";
                $message .= "⏰ {$safeTime}";

                $warnMessage = null;
                if ($balance <= LOW_BALANCE_USD) {
                    $warnMessage  = "⚠️ *Low Balance Warning\!*\n\n";
                    $warnMessage .= "🏢 *Group:* {$safeName}\n";
                    $warnMessage .= "💰 *Balance:* {$safeBalance} USD\n\n";
                    $warnMessage .= "Please top up your balance as soon as possible 💳\n";
                }

            } elseif ($language === 'RU') {
                $safeGreet = $esc($greetingRu);
                $message  = "{$safeGreet}\n\n";
                $message .= "📊 *Ежедневная сводка баланса*\n\n";
                $message .= "🏢 *Группа:* {$safeName}\n";
                $message .= "💰 *Баланс:* {$safeBalance} USD\n\n";
                $message .= "📈 *Статистика за сегодня:*\n";
                $message .= "📞 Всего звонков: *{$safeCalls}*\n";
                $message .= "✅ Отвечено: *{$safeAnsw}*\n";
                $message .= "⏱ Время разговора: *{$safeMin} мин*\n";
                $message .= "💸 Потрачено: *{$safeSpend} USD*\n\n";
                $message .= "🌐 Пополните баланс онлайн для бесперебойной работы\.\n\n";
                $message .= "Спасибо, что выбрали нас\! 🙏\n";
                $message .= "⏰ {$safeTime}";

                $warnMessage = null;
                if ($balance <= LOW_BALANCE_USD) {
                    $warnMessage  = "⚠️ *Предупреждение о низком балансе\!*\n\n";
                    $warnMessage .= "🏢 *Группа:* {$safeName}\n";
                    $warnMessage .= "💰 *Баланс:* {$safeBalance} USD\n\n";
                    $warnMessage .= "Пожалуйста, пополните баланс как можно скорее 💳\n";
                }

            } else { // TR
                $safeGreet = $esc($greeting);
                $message  = "{$safeGreet}\n\n";
                $message .= "📊 *Günlük Bakiye Özeti*\n\n";
                $message .= "🏢 *Grup:* {$safeName}\n";
                $message .= "💰 *Bakiye:* {$safeBalance} USD\n\n";
                $message .= "📈 *Bugünkü İstatistikler:*\n";
                $message .= "📞 Toplam Arama: *{$safeCalls}*\n";
                $message .= "✅ Cevaplanan: *{$safeAnsw}*\n";
                $message .= "⏱ Konuşma: *{$safeMin} dk*\n";
                $message .= "💸 Harcanan: *{$safeSpend} USD*\n\n";
                $message .= "🌐 Online ödeme ile bakiye yükleyebilirsiniz\.\n\n";
                $message .= "Bizi tercih ettiğiniz için teşekkürler\! 🙏\n";
                $message .= "⏰ {$safeTime}";

                $warnMessage = null;
                if ($balance <= LOW_BALANCE_USD) {
                    $warnMessage  = "⚠️ *Bakiyeniz Azaldı\!*\n\n";
                    $warnMessage .= "🏢 *Grup:* {$safeName}\n";
                    $warnMessage .= "💰 *Bakiye:* {$safeBalance} USD\n\n";
                    $warnMessage .= "Lütfen en kısa sürede bakiye yükleyin 💳\n";
                }
            }
            
            // Mesajları gönder - Rate limiting için kısa delay
            sleep(1);
            
            if ($notifier->sendMessage($message)) {
                $sendOkCount++;
                echo "✓ Grup '{$groupName}' için rapor gönderildi ({$language}) - Chat ID: {$chatId}\n";
                
                // Düşük bakiye uyarısı gönder
                if ($warnMessage !== null) {
                    sleep(1);
                    if ($notifier->sendMessage($warnMessage)) {
                        echo "⚠ Grup '{$groupName}' için düşük bakiye uyarısı gönderildi\n";
                    } else {
                        echo "✗ Grup '{$groupName}' için düşük bakiye uyarısı gönderilemedi\n";
                    }
                }
            } else {
                $sendFailCount++;
                echo "✗ Grup '{$groupName}' için rapor gönderilemedi - Chat ID: {$chatId}\n";
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
            
            // Admin özet mesajını hazırla (MarkdownV2 safe)
            $safeDate2    = $esc(date('d.m.Y'));
            $safeTime2    = $esc(date('H:i:s'));
            $safeCostA    = $esc(number_format($totalCost, 2));
            $safeSalesA   = $esc(number_format($totalSales, 2));
            $safeProfitA  = $esc(number_format($totalProfit, 2));
            $safeBalA     = $esc(number_format($totalBalanceAll, 2));
            $safeGreetA   = $esc($greeting);

            $baseAdminMessage  = "{$safeGreetA}\n\n";
            $baseAdminMessage .= "📈 *Günlük Genel Bakiye Raporu*\n\n";
            $baseAdminMessage .= "📅 *Tarih:* {$safeDate2}\n";
            $baseAdminMessage .= "📊 *Toplam Arama:* {$totalCallsAll}\n";
            $baseAdminMessage .= "💸 *API Maliyeti:* {$safeCostA} USD\n";
            $baseAdminMessage .= "💰 *Toplam Satış:* {$safeSalesA} USD\n";
            $baseAdminMessage .= "🎯 *Net Kâr:* {$safeProfitA} USD\n";
            $baseAdminMessage .= "🏦 *Toplam Bakiye:* {$safeBalA} USD\n";
            $baseAdminMessage .= "⚠️ *Düşük Bakiye Grubu:* {$lowBalanceGroups}\n";
            $baseAdminMessage .= "✉️ *Mesaj Durumu:* OK {$sendOkCount} / FAIL {$sendFailCount}\n\n";
            $baseAdminMessage .= "💼 *Grup Bakiyeleri:*\n";
            foreach ($activeGroups as $grp) {
                $grpBal  = $esc(number_format((float)$grp['balance'], 2));
                $grpName = $esc($grp['name']);
                $lowBadge = ((float)$grp['balance'] <= LOW_BALANCE_USD) ? " ⚠️" : "";
                $baseAdminMessage .= "• {$grpName}: {$grpBal} USD{$lowBadge}\n";
            }
            $baseAdminMessage .= "\n⏰ *Rapor Zamanı:* {$safeTime2}\n";

            // Varsayılan admin kanalına gönder
            $adminNotifier = new TelegramNotifier();
            if ($adminNotifier->sendMessage($baseAdminMessage)) {
                echo "✓ Admin raporu varsayılan kanala gönderildi\n";
            } else {
                echo "✗ Admin raporu varsayılan kanala gönderilemedi\n";
            }
            
        } catch (Exception $e) {
            echo "⚠ Admin raporu gönderilemedi: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Balance Cron başarıyla tamamlandı ===\n";
    echo "Gönderim sonucu: OK {$sendOkCount} / FAIL {$sendFailCount}\n";
    
} catch (Exception $e) {
    echo "❌ HATA: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";

    // Hata durumunda sadece default admin kanalına bildir
    try {
        $escFn  = fn(string $t) => preg_replace('/([_\*\[\]\(\)~`>#+\-=|{}.!\\\\])/', '\\\\$1', $t);
        $safeErr = $escFn($e->getMessage());
        $safeTs  = $escFn(date('d.m.Y H:i'));
        $errorMsg  = "🚨 *BAKİYE CRON JOB HATASI*\n\n";
        $errorMsg .= "📅 *Tarih:* {$safeTs}\n";
        $errorMsg .= "❌ *Hata:* {$safeErr}\n";
        (new TelegramNotifier())->sendMessage($errorMsg);
    } catch (Exception $telegramError) {
        echo "Telegram hata bildirimi gönderilemedi: " . $telegramError->getMessage() . "\n";
    }

    exit(1);
}

// Output'u al ve logla
$output = ob_get_clean();
echo $output;

// Log dosyasına kaydet
$logFile = __DIR__ . '/storage/logs/balance_cron_' . date('Y-m') . '.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logEntry = str_repeat("=", 80) . "\n";
$logEntry .= date('Y-m-d H:i:s') . " - Balance Cron Run (PID: " . getmypid() . ")\n";
$logEntry .= "Başarılı: {$sendOkCount} | Başarısız: {$sendFailCount} | Aktif Grup: " . count($activeGroups ?? []) . "\n";
$logEntry .= $output . "\n";
$logEntry .= str_repeat("=", 80) . "\n\n";

file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
echo "Log kaydedildi: {$logFile}\n";

exit(0);