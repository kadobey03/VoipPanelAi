<?php
namespace App\Helpers;

use App\Helpers\DB;
use App\Helpers\TelegramNotifier;

class SubscriptionManager {
    
    /**
     * Vadesi gelen abonelik ödemelerini işle
     */
    public static function processSubscriptionPayments() {
        $db = DB::conn();
        $processed = 0;
        $failed = 0;
        $errors = [];
        
        try {
            // Vadesi gelen bekleyen ödemeleri bul
            $stmt = $db->prepare('
                SELECT sp.*, ua.agent_number, ap.name as product_name, ap.subscription_monthly_fee,
                       g.balance as group_balance, u.login as user_login, g.name as group_name
                FROM agent_subscription_payments sp 
                JOIN user_agents ua ON sp.user_agent_id = ua.id 
                JOIN agent_products ap ON ua.agent_product_id = ap.id 
                JOIN users u ON sp.user_id = u.id 
                JOIN groups g ON u.group_id = g.id 
                WHERE sp.status = "pending" 
                AND sp.due_date <= CURDATE() 
                AND ua.status = "active"
                ORDER BY sp.due_date ASC
                LIMIT 100
            ');
            
            $stmt->execute();
            $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            foreach ($payments as $payment) {
                try {
                    $db->begin_transaction();
                    
                    $amount = $payment['amount'];
                    $groupBalance = $payment['group_balance'];
                    
                    if ($groupBalance >= $amount) {
                        // Bakiyeden düş
                        $stmt = $db->prepare('UPDATE groups SET balance = balance - ? WHERE id = ?');
                        $stmt->bind_param('di', $amount, $payment['group_id']);
                        $stmt->execute();
                        $stmt->close();
                        
                        // Ödemeyi tamamlandı olarak işaretle
                        $stmt = $db->prepare('
                            UPDATE agent_subscription_payments 
                            SET status = "paid", payment_date = NOW(), payment_method = "balance" 
                            WHERE id = ?
                        ');
                        $stmt->bind_param('i', $payment['id']);
                        $stmt->execute();
                        $stmt->close();
                        
                        // Sonraki ödemeyi planla
                        $nextDueDate = date('Y-m-d', strtotime($payment['due_date'] . ' +1 month'));
                        $stmt = $db->prepare('
                            INSERT INTO agent_subscription_payments 
                            (user_agent_id, user_id, amount, due_date, status) 
                            VALUES (?, ?, ?, ?, "pending")
                        ');
                        $stmt->bind_param('iids', $payment['user_agent_id'], $payment['user_id'], $amount, $nextDueDate);
                        $stmt->execute();
                        $stmt->close();
                        
                        // User agent'ın sonraki ödeme tarihini güncelle
                        $stmt = $db->prepare('UPDATE user_agents SET next_subscription_due = ? WHERE id = ?');
                        $stmt->bind_param('si', $nextDueDate, $payment['user_agent_id']);
                        $stmt->execute();
                        $stmt->close();
                        
                        // Transaction kaydı oluştur
                        $stmt = $db->prepare('
                            INSERT INTO transactions (group_id, type, amount, reference, description) 
                            VALUES (?, "agent_subscription", ?, ?, ?)
                        ');
                        $reference = "AGS-" . $payment['id'];
                        $description = "Agent abonelik ödemesi: " . $payment['product_name'] . " (#" . $payment['agent_number'] . ")";
                        $stmt->bind_param('idss', $payment['group_id'], $amount, $reference, $description);
                        $stmt->execute();
                        $stmt->close();
                        
                        $db->commit();
                        $processed++;
                        
                        // Başarılı ödeme bildirimi gönder
                        self::sendSubscriptionPaymentNotification($payment, 'success', $groupBalance - $amount);
                        
                    } else {
                        // Yetersiz bakiye - ödemeyi başarısız olarak işaretle ve yeniden deneme ayarla
                        $failureCount = $payment['failure_count'] + 1;
                        $nextRetryDate = date('Y-m-d H:i:s', strtotime('+1 day'));
                        $status = $failureCount >= 3 ? 'failed' : 'pending';
                        
                        $stmt = $db->prepare('
                            UPDATE agent_subscription_payments 
                            SET failure_count = ?, next_retry_date = ?, status = ?, updated_at = NOW() 
                            WHERE id = ?
                        ');
                        $stmt->bind_param('issi', $failureCount, $nextRetryDate, $status, $payment['id']);
                        $stmt->execute();
                        $stmt->close();
                        
                        // Eğer 3 başarısızlık oldu ise agent'ı askıya al
                        if ($failureCount >= 3) {
                            $stmt = $db->prepare('UPDATE user_agents SET status = "suspended" WHERE id = ?');
                            $stmt->bind_param('i', $payment['user_agent_id']);
                            $stmt->execute();
                            $stmt->close();
                            
                            // Askıya alma bildirimi gönder
                            self::sendSubscriptionPaymentNotification($payment, 'suspended', $groupBalance);
                        } else {
                            // Yetersiz bakiye bildirimi gönder
                            self::sendSubscriptionPaymentNotification($payment, 'insufficient_balance', $groupBalance);
                        }
                        
                        $db->commit();
                        $failed++;
                    }
                    
                } catch (Exception $e) {
                    $db->rollback();
                    $errors[] = "Payment ID {$payment['id']}: " . $e->getMessage();
                    $failed++;
                }
            }
            
        } catch (Exception $e) {
            $errors[] = "General error: " . $e->getMessage();
        }
        
        return [
            'processed' => $processed,
            'failed' => $failed,
            'total_checked' => count($payments),
            'errors' => $errors
        ];
    }
    
    /**
     * Abonelik ödeme bildirimi gönder
     */
    private static function sendSubscriptionPaymentNotification($payment, $status, $remainingBalance) {
        try {
            $notifier = new TelegramNotifier();
            $message = "";
            
            switch ($status) {
                case 'success':
                    $message = "✅ *Abonelik Ödemesi Başarılı*\n\n";
                    $message .= "👤 *Kullanıcı:* " . $payment['user_login'] . "\n";
                    $message .= "🏢 *Grup:* " . ($payment['group_name'] ?: 'Bilinmeyen') . "\n";
                    $message .= "📱 *Agent:* " . $payment['product_name'] . " (#" . $payment['agent_number'] . ")\n";
                    $message .= "💰 *Ödenen Tutar:* $" . number_format($payment['amount'], 2) . "\n";
                    $message .= "💳 *Kalan Bakiye:* $" . number_format($remainingBalance, 2) . "\n";
                    $message .= "📅 *Sonraki Ödeme:* " . date('d.m.Y', strtotime($payment['due_date'] . ' +1 month')) . "\n";
                    break;
                    
                case 'insufficient_balance':
                    $message = "⚠️ *Abonelik Ödemesi Başarısız - Yetersiz Bakiye*\n\n";
                    $message .= "👤 *Kullanıcı:* " . $payment['user_login'] . "\n";
                    $message .= "🏢 *Grup:* " . ($payment['group_name'] ?: 'Bilinmeyen') . "\n";
                    $message .= "📱 *Agent:* " . $payment['product_name'] . " (#" . $payment['agent_number'] . ")\n";
                    $message .= "💰 *Gereken Tutar:* $" . number_format($payment['amount'], 2) . "\n";
                    $message .= "💳 *Mevcut Bakiye:* $" . number_format($remainingBalance, 2) . "\n";
                    $message .= "🔄 *Yeniden Deneme:* " . ($payment['failure_count'] + 1) . "/3\n";
                    $message .= "\n*Not:* 24 saat sonra tekrar denenecek.";
                    break;
                    
                case 'suspended':
                    $message = "🚫 *Agent Askıya Alındı - 3 Başarısız Ödeme*\n\n";
                    $message .= "👤 *Kullanıcı:* " . $payment['user_login'] . "\n";
                    $message .= "🏢 *Grup:* " . ($payment['group_name'] ?: 'Bilinmeyen') . "\n";
                    $message .= "📱 *Agent:* " . $payment['product_name'] . " (#" . $payment['agent_number'] . ")\n";
                    $message .= "💰 *Gereken Tutar:* $" . number_format($payment['amount'], 2) . "\n";
                    $message .= "💳 *Mevcut Bakiye:* $" . number_format($remainingBalance, 2) . "\n";
                    $message .= "\n*Durum:* Agent hizmeti askıya alınmıştır.";
                    break;
            }
            
            if ($message) {
                $notifier->sendToAdmins($message);
            }
            
        } catch (Exception $e) {
            error_log('Subscription notification error: ' . $e->getMessage());
        }
    }
    
    /**
     * Askıya alınan agentleri yeniden aktifleştir (ödeme yapıldıktan sonra)
     */
    public static function reactivateSuspendedAgents($userId, $groupId) {
        $db = DB::conn();
        
        try {
            // Kullanıcının askıya alınmış agentlerini bul
            $stmt = $db->prepare('
                SELECT ua.id, ua.agent_number, ap.name as product_name, ap.subscription_monthly_fee
                FROM user_agents ua 
                JOIN agent_products ap ON ua.agent_product_id = ap.id 
                WHERE ua.user_id = ? AND ua.status = "suspended" AND ap.is_subscription = 1
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $suspendedAgents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            if (empty($suspendedAgents)) {
                return 0;
            }
            
            // Grup bakiyesini kontrol et
            $stmt = $db->prepare('SELECT balance FROM groups WHERE id = ?');
            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $balance = $result ? $result['balance'] : 0;
            $stmt->close();
            
            $reactivated = 0;
            
            foreach ($suspendedAgents as $agent) {
                if ($balance >= $agent['subscription_monthly_fee']) {
                    // Agent'ı yeniden aktifleştir
                    $stmt = $db->prepare('UPDATE user_agents SET status = "active" WHERE id = ?');
                    $stmt->bind_param('i', $agent['id']);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Bekleyen ödemelerini sıfırla
                    $stmt = $db->prepare('
                        UPDATE agent_subscription_payments 
                        SET status = "pending", failure_count = 0, next_retry_date = NULL 
                        WHERE user_agent_id = ? AND status = "failed"
                    ');
                    $stmt->bind_param('i', $agent['id']);
                    $stmt->execute();
                    $stmt->close();
                    
                    $reactivated++;
                }
            }
            
            if ($reactivated > 0) {
                // Yeniden aktivasyon bildirimi gönder
                $notifier = new TelegramNotifier();
                $message = "🔄 *Agent Yeniden Aktivasyonu*\n\n";
                $message .= "👤 *Kullanıcı ID:* " . $userId . "\n";
                $message .= "🏢 *Grup ID:* " . $groupId . "\n";
                $message .= "📱 *Yeniden Aktifleştirilen Agent:* " . $reactivated . "\n";
                $message .= "💳 *Mevcut Bakiye:* $" . number_format($balance, 2) . "\n";
                $notifier->sendToAdmins($message);
            }
            
            return $reactivated;
            
        } catch (Exception $e) {
            error_log('Agent reactivation error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Vadesi geçmiş ödemeleri rapor et
     */
    public static function getOverduePayments() {
        $db = DB::conn();
        
        try {
            $stmt = $db->prepare('
                SELECT sp.*, ua.agent_number, ap.name as product_name, 
                       u.login as user_login, g.name as group_name, g.balance as group_balance,
                       DATEDIFF(CURDATE(), sp.due_date) as days_overdue
                FROM agent_subscription_payments sp 
                JOIN user_agents ua ON sp.user_agent_id = ua.id 
                JOIN agent_products ap ON ua.agent_product_id = ap.id 
                JOIN users u ON sp.user_id = u.id 
                JOIN groups g ON u.group_id = g.id 
                WHERE sp.status IN ("pending", "overdue") 
                AND sp.due_date < CURDATE()
                ORDER BY sp.due_date ASC
            ');
            
            $stmt->execute();
            $overduePayments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $overduePayments;
            
        } catch (Exception $e) {
            error_log('Overdue payments check error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Abonelik istatistikleri
     */
    public static function getSubscriptionStats() {
        $db = DB::conn();
        
        try {
            $stats = [];
            
            // Toplam aktif abonelikler
            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM user_agents ua 
                JOIN agent_products ap ON ua.agent_product_id = ap.id 
                WHERE ua.status = "active" AND ap.is_subscription = 1
            ');
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stats['active_subscriptions'] = $result['count'];
            $stmt->close();
            
            // Bu ay toplam abonelik geliri
            $stmt = $db->prepare('
                SELECT COALESCE(SUM(amount), 0) as total FROM agent_subscription_payments 
                WHERE status = "paid" 
                AND YEAR(payment_date) = YEAR(CURDATE()) 
                AND MONTH(payment_date) = MONTH(CURDATE())
            ');
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stats['monthly_revenue'] = $result['total'];
            $stmt->close();
            
            // Vadesi geçmiş ödemeler
            $stmt = $db->prepare('
                SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM agent_subscription_payments 
                WHERE status IN ("pending", "overdue") 
                AND due_date < CURDATE()
            ');
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stats['overdue_count'] = $result['count'];
            $stats['overdue_amount'] = $result['total'];
            $stmt->close();
            
            // Askıya alınan agentler
            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM user_agents ua 
                JOIN agent_products ap ON ua.agent_product_id = ap.id 
                WHERE ua.status = "suspended" AND ap.is_subscription = 1
            ');
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stats['suspended_count'] = $result['count'];
            $stmt->close();
            
            return $stats;
            
        } catch (Exception $e) {
            error_log('Subscription stats error: ' . $e->getMessage());
            return [];
        }
    }
}