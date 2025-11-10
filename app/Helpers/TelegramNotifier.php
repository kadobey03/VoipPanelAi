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