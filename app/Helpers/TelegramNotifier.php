<?php

namespace App\Helpers;

class TelegramNotifier
{
    private $botToken;
    private $chatId;
    private $language;
    
    public function __construct($botToken = null, $chatId = null, $language = 'TR')
    {
        $this->botToken = $botToken ?: '8076802006:AAFi4IOMuNespxZyvvFTLZuVZxMGOucmvUk';
        $this->chatId = $chatId ?: '-4931882446';
        $this->language = $language ?: 'TR';
    }
    
    /**
     * Gruba göre telegram ayarlarını al ve notifier'ı yapılandır
     */
    public static function forGroup($groupId)
    {
        try {
            $db = \App\Helpers\DB::conn();
            $stmt = $db->prepare('SELECT telegram_chat_id, telegram_language, telegram_enabled FROM groups WHERE id = ?');
            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            $group = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($group && $group['telegram_enabled'] && $group['telegram_chat_id']) {
                return new self(null, $group['telegram_chat_id'], $group['telegram_language'] ?: 'TR');
            }
            
            // Varsayılan ayarları kullan
            return new self();
            
        } catch (\Exception $e) {
            error_log('TelegramNotifier::forGroup Error: ' . $e->getMessage());
            return new self();
        }
    }
    
    /**
     * Ödeme başarıyla onaylandığında bildirim gönder
     */
    public function sendPaymentNotification($groupName, $amount, $paymentId, $transactionId = null, $balanceBefore = null, $balanceAfter = null)
    {
        $safeGroup = $this->escape($groupName);

        $message = "🎉 *ÖDEME ONAYLANDI*\n\n";
        $message .= "👥 *Grup:* " . $safeGroup . "\n";
        $message .= "🆔 *Ödeme ID:* \#{$paymentId}\n";
        $message .= "💰 *Net Tutar:* " . number_format((float)$amount, 2) . " USDT\n";

        if ($transactionId) {
            $message .= "📝 *İşlem ID:* {$transactionId}\n";
        }

        if ($balanceBefore !== null && $balanceAfter !== null) {
            $message .= "📊 *Önceki Bakiye:* " . number_format((float)$balanceBefore, 2) . " USDT\n";
            $message .= "📈 *Yeni Bakiye:* " . number_format((float)$balanceAfter, 2) . " USDT\n";
        }

        $message .= "⏰ *Zaman:* " . $this->escape(date('d.m.Y H:i:s')) . "\n";
        $message .= "✅ *Durum:* Bakiye hesaba eklendi\n";

        return $this->sendMessage($message);
    }
    
    /**
     * Yeni ödeme talebi oluşturulduğunda bildirim gönder
     */
    public function sendPaymentRequestNotification($groupName, $totalAmount, $paymentId, $currentBalance = null, $netAmount = null, $commissionPercent = null, $commissionAmount = null)
    {
        $safeGroup = $this->escape($groupName);

        $message = "💸 *YENİ USDT ÖDEME TALEBİ*\n\n";
        $message .= "👥 *Grup:* " . $safeGroup . "\n";
        $message .= "🆔 *Ödeme ID:* \#{$paymentId}\n";

        if ($netAmount !== null && $commissionPercent !== null && $commissionAmount !== null) {
            $message .= "\n";
            $message .= "💵 *Yüklenecek Bakiye:* " . number_format((float)$netAmount, 2) . " USDT\n";
            $message .= "💸 *Komisyon (%{$commissionPercent}):* \\+" . number_format((float)$commissionAmount, 2) . " USDT\n";
            $message .= "💰 *Gönderilecek Toplam:* " . number_format((float)$totalAmount, 2) . " USDT\n";
        } else {
            $message .= "💰 *Tutar:* " . number_format((float)$totalAmount, 2) . " USDT\n";
        }

        if ($currentBalance !== null) {
            $message .= "📊 *Mevcut Bakiye:* " . number_format((float)$currentBalance, 2) . " USDT\n";
        }

        $message .= "\n";
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "🔗 *Network:* TRC20\n";
        $message .= "⏳ *Durum:* Ödeme bekleniyor\\.\\.\\.\n";

        return $this->sendMessage($message);
    }
    
    /**
     * Ödeme iptal edildiğinde bildirim gönder
     */
    public function sendPaymentCancelledNotification($groupName, $amount, $paymentId, $currentBalance = null)
    {
        $safeGroup = $this->escape($groupName);

        $message = "❌ *ÖDEME İPTAL EDİLDİ*\n\n";
        $message .= "👥 *Grup:* " . $safeGroup . "\n";
        $message .= "🆔 *Ödeme ID:* \#{$paymentId}\n";
        $message .= "💰 *Tutar:* " . number_format((float)$amount, 2) . " USDT\n";

        if ($currentBalance !== null) {
            $message .= "📊 *Mevcut Bakiye:* " . number_format((float)$currentBalance, 2) . " USDT\n";
        }

        $message .= "⏰ *Zaman:* " . $this->escape(date('d.m.Y H:i:s')) . "\n";
        $message .= "🚫 *Durum:* Kullanıcı tarafından iptal edildi\n";
        $message .= "📞 *Aksiyon:* Müşteriyi arayarak iptal sebebini öğrenin\n";

        return $this->sendMessage($message);
    }

    /**
     * Ödeme süresi dolduğunda bildirim gönder
     */
    public function sendPaymentExpiredNotification($groupName, $amount, $paymentId, $currentBalance = null)
    {
        $safeGroup = $this->escape($groupName);

        $message = "⏰ *ÖDEME SÜRESİ DOLDU*\n\n";
        $message .= "👥 *Grup:* " . $safeGroup . "\n";
        $message .= "🆔 *Ödeme ID:* \#{$paymentId}\n";
        $message .= "💰 *Tutar:* " . number_format((float)$amount, 2) . " USDT\n";

        if ($currentBalance !== null) {
            $message .= "📊 *Mevcut Bakiye:* " . number_format((float)$currentBalance, 2) . " USDT\n";
        }

        $message .= "⏰ *Zaman:* " . $this->escape(date('d.m.Y H:i:s')) . "\n";
        $message .= "⌛ *Durum:* Ödeme süresi doldu\n";
        $message .= "📞 *Aksiyon:* Müşteriyi arayarak durumu kontrol edin\n";

        return $this->sendMessage($message);
    }
    
    /**
     * Agent satın alma bildirimini gönder
     */
    public function sendAgentPurchaseNotification($userName, $userEmail, $productName, $price, $paymentType, $balanceBefore, $balanceAfter)
    {
        $message = "🤖 *YENİ AGENT SATIN ALIMI*\n\n";
        $message .= "👤 *Kullanıcı:* {$userName}\n";
        $message .= "📧 *Email:* {$userEmail}\n";
        $message .= "🎯 *Ürün:* {$productName}\n";
        $message .= "💰 *Fiyat:* \${$price}\n";
        $message .= "📋 *Ödeme Tipi:* " . ($paymentType === 'one_time' ? 'Tek Seferlik' : 'Aylık Abonelik') . "\n";
        $message .= "📊 *Önceki Bakiye:* \$" . number_format($balanceBefore, 2) . "\n";
        $message .= "📈 *Kalan Bakiye:* \$" . number_format($balanceAfter, 2) . "\n";
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "✅ *Durum:* Agent başarıyla satın alındı\n";
        
        return $this->sendMessage($message);
    }
    
    /**
     * Abonelik ödemesi başarı bildirimini gönder
     */
    public function sendSubscriptionPaymentSuccess($userName, $userEmail, $productName, $price, $balanceBefore, $balanceAfter, $nextPaymentDate)
    {
        $message = "💳 *ABONELİK ÖDEMESİ BAŞARILI*\n\n";
        $message .= "👤 *Kullanıcı:* {$userName}\n";
        $message .= "📧 *Email:* {$userEmail}\n";
        $message .= "🤖 *Agent:* {$productName}\n";
        $message .= "💰 *Ödenen Tutar:* \${$price}\n";
        $message .= "📊 *Önceki Bakiye:* \$" . number_format($balanceBefore, 2) . "\n";
        $message .= "📈 *Kalan Bakiye:* \$" . number_format($balanceAfter, 2) . "\n";
        $message .= "📅 *Sonraki Ödeme:* " . date('d.m.Y', strtotime($nextPaymentDate)) . "\n";
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "✅ *Durum:* Abonelik devam ediyor\n";
        
        return $this->sendMessage($message);
    }
    
    /**
     * Abonelik ödemesi başarısız bildirimini gönder
     */
    public function sendSubscriptionPaymentFailed($userName, $userEmail, $productName, $requiredAmount, $currentBalance, $nextRetryDate)
    {
        $message = "❌ *ABONELİK ÖDEMESİ BAŞARISIZ*\n\n";
        $message .= "👤 *Kullanıcı:* {$userName}\n";
        $message .= "📧 *Email:* {$userEmail}\n";
        $message .= "🤖 *Agent:* {$productName}\n";
        $message .= "💰 *Gerekli Tutar:* \${$requiredAmount}\n";
        $message .= "📊 *Mevcut Bakiye:* \$" . number_format($currentBalance, 2) . "\n";
        $message .= "📅 *Sonraki Deneme:* " . date('d.m.Y', strtotime($nextRetryDate)) . "\n";
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "⚠️ *Durum:* Yetersiz bakiye - Agent askıya alındı\n";
        $message .= "📞 *Aksiyon:* Müşteriyi arayarak bakiye yüklemesi için uyarın\n";
        
        return $this->sendMessage($message);
    }
    
    /**
     * Agent askıya alma bildirimini gönder
     */
    public function sendAgentSuspendedNotification($userName, $userEmail, $productName, $daysOverdue)
    {
        $message = "⏸️ *AGENT ASKIYA ALINDI*\n\n";
        $message .= "👤 *Kullanıcı:* {$userName}\n";
        $message .= "📧 *Email:* {$userEmail}\n";
        $message .= "🤖 *Agent:* {$productName}\n";
        $message .= "📅 *Geciken Gün Sayısı:* {$daysOverdue} gün\n";
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "🚫 *Durum:* Ödeme yapılmaması nedeniyle askıya alındı\n";
        $message .= "📞 *Aksiyon:* Müşteriyi arayarak ödeme yapması için uyarın\n";
        
        return $this->sendMessage($message);
    }
    
    /**
     * Agent yeniden aktifleştirme bildirimini gönder
     */
    public function sendAgentReactivatedNotification($userName, $userEmail, $productName, $balanceUsed, $balanceAfter)
    {
        $message = "🔄 *AGENT YENİDEN AKTİFLEŞTİRİLDİ*\n\n";
        $message .= "👤 *Kullanıcı:* {$userName}\n";
        $message .= "📧 *Email:* {$userEmail}\n";
        $message .= "🤖 *Agent:* {$productName}\n";
        $message .= "💰 *Kullanılan Bakiye:* \${$balanceUsed}\n";
        $message .= "📈 *Kalan Bakiye:* \$" . number_format($balanceAfter, 2) . "\n";
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "✅ *Durum:* Agent başarıyla yeniden aktifleştirildi\n";
        
        return $this->sendMessage($message);
    }
    
    /**
     * Admin abonelik yönetimi bildirimini gönder
     */
    public function sendAdminSubscriptionAction($adminName, $action, $userName, $productName, $amount = null)
    {
        $message = "👨‍💼 *ADMİN ABONELİK İŞLEMİ*\n\n";
        $message .= "👤 *Admin:* {$adminName}\n";
        $message .= "🎯 *İşlem:* {$action}\n";
        $message .= "👥 *Hedef Kullanıcı:* {$userName}\n";
        $message .= "🤖 *Agent:* {$productName}\n";
        
        if ($amount !== null) {
            $message .= "💰 *Tutar:* \${$amount}\n";
        }
        
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "✅ *Durum:* İşlem başarıyla tamamlandı\n";
        
        return $this->sendMessage($message);
    }
    
    /**
     * Günlük abonelik raporu bildirimini gönder
     */
    public function sendDailySubscriptionReport($totalProcessed, $successCount, $failedCount, $totalRevenue, $suspendedCount)
    {
        if ($this->language === 'EN') {
            $message = "📊 *DAILY SUBSCRIPTION REPORT*\n\n";
            $message .= "📈 *Total Processed:* {$totalProcessed}\n";
            $message .= "✅ *Successful Payments:* {$successCount}\n";
            $message .= "❌ *Failed Payments:* {$failedCount}\n";
            $message .= "💰 *Total Revenue:* \$" . number_format($totalRevenue, 2) . "\n";
            $message .= "⏸️ *Suspended:* {$suspendedCount}\n";
            $message .= "📅 *Date:* " . date('d.m.Y') . "\n";
            $message .= "⏰ *Report Time:* " . date('H:i:s') . "\n";
            
            if ($failedCount > 0) {
                $message .= "\n⚠️ *ATTENTION:* Contact customers for failed payments";
            }
        } elseif ($this->language === 'RU') {
            $message = "📊 *ЕЖЕДНЕВНЫЙ ОТЧЕТ ПО ПОДПИСКАМ*\n\n";
            $message .= "📈 *Всего обработано:* {$totalProcessed}\n";
            $message .= "✅ *Успешные платежи:* {$successCount}\n";
            $message .= "❌ *Неудачные платежи:* {$failedCount}\n";
            $message .= "💰 *Общий доход:* \$" . number_format($totalRevenue, 2) . "\n";
            $message .= "⏸️ *Приостановлено:* {$suspendedCount}\n";
            $message .= "📅 *Дата:* " . date('d.m.Y') . "\n";
            $message .= "⏰ *Время отчета:* " . date('H:i:s') . "\n";
            
            if ($failedCount > 0) {
                $message .= "\n⚠️ *ВНИМАНИЕ:* Свяжитесь с клиентами по неудачным платежам";
            }
        } else { // TR
            $message = "📊 *GÜNLÜK ABONELİK RAPORU*\n\n";
            $message .= "📈 *Toplam İşlem:* {$totalProcessed}\n";
            $message .= "✅ *Başarılı Ödeme:* {$successCount}\n";
            $message .= "❌ *Başarısız Ödeme:* {$failedCount}\n";
            $message .= "💰 *Toplam Gelir:* \$" . number_format($totalRevenue, 2) . "\n";
            $message .= "⏸️ *Askıya Alınan:* {$suspendedCount}\n";
            $message .= "📅 *Tarih:* " . date('d.m.Y') . "\n";
            $message .= "⏰ *Rapor Zamanı:* " . date('H:i:s') . "\n";
            
            if ($failedCount > 0) {
                $message .= "\n⚠️ *DİKKAT:* Başarısız ödemeler için müşterilerle iletişime geçin";
            }
        }
        
        return $this->sendMessage($message);
    }
    
    /**
     * Cron hata bildirimi gönder
     */
    public function sendCronErrorNotification($errorMessage)
    {
        if ($this->language === 'EN') {
            $message = "🚨 *SUBSCRIPTION CRON JOB ERROR*\n\n";
            $message .= "📅 *Date:* " . date('d.m.Y H:i') . "\n";
            $message .= "❌ *Error:* {$errorMessage}\n";
        } elseif ($this->language === 'RU') {
            $message = "🚨 *ОШИБКА CRON JOB ПОДПИСОК*\n\n";
            $message .= "📅 *Дата:* " . date('d.m.Y H:i') . "\n";
            $message .= "❌ *Ошибка:* {$errorMessage}\n";
        } else { // TR
            $message = "🚨 *ABONELİK CRON JOB HATASI*\n\n";
            $message .= "📅 *Tarih:* " . date('d.m.Y H:i') . "\n";
            $message .= "❌ *Hata:* {$errorMessage}\n";
        }
        
        return $this->sendMessage($message);
    }
    
    /**
     * Özel karakterleri MarkdownV2 için escape et
     */
    private function escape($text)
    {
        // MarkdownV2'de kaçırılması gereken karakterler
        return preg_replace('/([_\*\[\]\(\)~`>#+\-=|{}.!\\\\])/', '\\\\$1', (string)$text);
    }

    /**
     * Telegram'a mesaj gönder - PUBLIC metod
     * MarkdownV2 parse_mode kullanır; önce cURL dener, fallback olarak file_get_contents
     */
    public function sendMessage($message)
    {
        if (empty($this->chatId)) {
            error_log('Telegram notification failed: Chat ID is empty');
            return false;
        }

        if (empty($this->botToken)) {
            error_log('Telegram notification failed: Bot token is empty');
            return false;
        }

        // Önce cURL ile dene (daha güvenilir)
        $result = $this->sendMessageCurl($message);
        if ($result) {
            return true;
        }

        // cURL başarısız olduysa file_get_contents ile dene
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        $data = [
            'chat_id'                  => $this->chatId,
            'text'                     => $message,
            'parse_mode'               => 'MarkdownV2',
            'disable_web_page_preview' => true,
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'timeout' => 15,
            ]
        ];

        $context = stream_context_create($options);
        $raw = @file_get_contents($url, false, $context);

        if ($raw === false) {
            error_log("Telegram notification failed (both cURL and file_get_contents) to chat_id: {$this->chatId}");
            return false;
        }

        $response = json_decode($raw, true);
        if (!$response || !$response['ok']) {
            $errorMsg = $response['description'] ?? 'Unknown error';
            error_log("Telegram file_get_contents failed to chat_id {$this->chatId}: {$errorMsg}");
            return false;
        }

        error_log("Telegram notification sent successfully (fallback) to chat_id: {$this->chatId}");
        return true;
    }
    
    /**
     * cURL ile Telegram'a mesaj gönder (birincil yöntem)
     */
    private function sendMessageCurl($message)
    {
        if (!function_exists('curl_init')) {
            error_log('Telegram cURL: curl extension not available');
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $data = [
            'chat_id'                  => $this->chatId,
            'text'                     => $message,
            'parse_mode'               => 'MarkdownV2',
            'disable_web_page_preview' => true,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'VoipPanel-Bot/1.0');

        $result    = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false || $httpCode !== 200) {
            error_log("Telegram cURL failed to chat_id {$this->chatId} - HTTP: {$httpCode}, cURL error: {$curlError}");
            return false;
        }

        $response = json_decode($result, true);
        if (!$response || !$response['ok']) {
            $errorMsg = $response['description'] ?? 'Unknown error';
            error_log("Telegram cURL API error to chat_id {$this->chatId}: {$errorMsg} | message: " . substr($message, 0, 200));
            return false;
        }

        error_log("Telegram sent OK via cURL to chat_id: {$this->chatId}");
        return true;
    }
}