<?php

namespace App\Helpers;

class TelegramNotifier
{
    private $botToken;
    private $chatId;
    
    public function __construct($botToken = null, $chatId = null)
    {
        $this->botToken = $botToken ?: '8076802006:AAFi4IOMuNespxZyvvFTLZuVZxMGOucmvUk';
        $this->chatId = $chatId ?: '-4931882446';
    }
    
    /**
     * Ödeme başarıyla onaylandığında bildirim gönder
     */
    public function sendPaymentNotification($groupName, $amount, $paymentId, $transactionId = null, $balanceBefore = null, $balanceAfter = null)
    {
        $message = "🎉 *ÖDEME ONAYLANDI*\n\n";
        $message .= "💰 *Tutar:* {$amount} USDT\n";
        $message .= "👥 *Grup:* {$groupName}\n";
        $message .= "🆔 *Payment ID:* {$paymentId}\n";
        
        if ($transactionId) {
            $message .= "📝 *Transaction ID:* {$transactionId}\n";
        }
        
        if ($balanceBefore !== null && $balanceAfter !== null) {
            $message .= "📊 *Önceki Bakiye:* " . number_format($balanceBefore, 2) . " USDT\n";
            $message .= "📈 *Sonraki Bakiye:* " . number_format($balanceAfter, 2) . " USDT\n";
        }
        
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "🚀 *Durum:* Bakiye hesaba eklendi\n";
        
        return $this->sendMessage($message);
    }
    
    /**
     * Yeni ödeme talebi oluşturulduğunda bildirim gönder
     */
    public function sendPaymentRequestNotification($groupName, $amount, $paymentId, $currentBalance = null)
    {
        $message = "💸 *YENİ ÖDEME TALEBİ*\n\n";
        $message .= "💰 *Tutar:* {$amount} USDT\n";
        $message .= "👥 *Grup:* {$groupName}\n";
        $message .= "🆔 *Payment ID:* {$paymentId}\n";
        
        if ($currentBalance !== null) {
            $message .= "📊 *Mevcut Bakiye:* " . number_format($currentBalance, 2) . " USDT\n";
        }
        
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "⏳ *Durum:* Ödeme bekleniyor...\n";
        
        return $this->sendMessage($message);
    }
    
    /**
     * Ödeme iptal edildiğinde bildirim gönder
     */
    public function sendPaymentCancelledNotification($groupName, $amount, $paymentId, $currentBalance = null)
    {
        $message = "❌ *ÖDEME İPTAL EDİLDİ*\n\n";
        $message .= "💰 *Tutar:* {$amount} USDT\n";
        $message .= "👥 *Grup:* {$groupName}\n";
        $message .= "🆔 *Payment ID:* {$paymentId}\n";
        
        if ($currentBalance !== null) {
            $message .= "📊 *Mevcut Bakiye:* " . number_format($currentBalance, 2) . " USDT\n";
        }
        
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "🚫 *Durum:* Kullanıcı tarafından iptal edildi\n";
        $message .= "📞 *Aksiyon:* Müşteriyi arayarak iptal sebebini öğrenin\n";
        
        return $this->sendMessage($message);
    }
    
    /**
     * Ödeme süresi dolduğunda bildirim gönder
     */
    public function sendPaymentExpiredNotification($groupName, $amount, $paymentId, $currentBalance = null)
    {
        $message = "⏰ *ÖDEME SÜRESİ DOLDU*\n\n";
        $message .= "💰 *Tutar:* {$amount} USDT\n";
        $message .= "👥 *Grup:* {$groupName}\n";
        $message .= "🆔 *Payment ID:* {$paymentId}\n";
        
        if ($currentBalance !== null) {
            $message .= "📊 *Mevcut Bakiye:* " . number_format($currentBalance, 2) . " USDT\n";
        }
        
        $message .= "⏰ *Zaman:* " . date('d.m.Y H:i:s') . "\n";
        $message .= "⌛ *Durum:* Ödeme süresi doldu (10 dakika)\n";
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
        
        return $this->sendMessage($message);
    }
    
    /**
     * Telegram'a mesaj gönder
     */
    private function sendMessage($message)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        
        $data = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === false) {
            error_log('Telegram notification failed - connection error');
            return false;
        }
        
        $response = json_decode($result, true);
        
        if (!$response || !$response['ok']) {
            error_log('Telegram notification failed: ' . $result);
            return false;
        }
        
        error_log('Telegram notification sent successfully');
        return true;
    }
    
    /**
     * cURL ile Telegram'a mesaj gönder (fallback)
     */
    private function sendMessageCurl($message)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        
        $data = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($result === false || $httpCode !== 200) {
            error_log('Telegram cURL notification failed');
            return false;
        }
        
        $response = json_decode($result, true);
        
        if (!$response || !$response['ok']) {
            error_log('Telegram notification failed: ' . $result);
            return false;
        }
        
        error_log('Telegram notification sent successfully via cURL');
        return true;
    }
}