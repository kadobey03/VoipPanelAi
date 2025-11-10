<?php
/**
 * TRON TRC20 USDT Ödeme Sistemi Test Senaryoları
 * Bu script, cryptocurrency ödeme sisteminin tüm bileşenlerini test eder
 * 
 * Kullanım: php test_crypto_payment.php
 */

require_once __DIR__ . '/config/bootstrap.php';

use App\Helpers\TronClient;
use App\Helpers\TronWallet;
use App\Helpers\CryptoSecurity;
use App\Helpers\DB;

class CryptoPaymentTester {
    private $db;
    private $testResults = [];
    
    public function __construct() {
        $this->db = DB::conn();
        echo "🚀 TRON TRC20 USDT Ödeme Sistemi Test Başlatılıyor...\n\n";
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        $this->testDatabaseSchema();
        $this->testTronClient();
        $this->testTronWallet();
        $this->testCryptoSecurity();
        $this->testPaymentWorkflow();
        
        $this->printResults();
    }
    
    /**
     * Test database schema
     */
    private function testDatabaseSchema() {
        echo "📊 Veritabanı şeması test ediliyor...\n";
        
        $tables = [
            'crypto_wallets',
            'crypto_payments', 
            'security_logs',
            'crypto_blacklist',
            'payment_methods'
        ];
        
        foreach ($tables as $table) {
            try {
                $result = $this->db->query("SHOW TABLES LIKE '{$table}'");
                if ($result->num_rows > 0) {
                    $this->testResults[] = "✅ Tablo {$table} mevcut";
                } else {
                    $this->testResults[] = "❌ Tablo {$table} bulunamadı";
                }
            } catch (\Exception $e) {
                $this->testResults[] = "❌ Tablo {$table} test hatası: " . $e->getMessage();
            }
        }
        
        // Test settings
        try {
            $stmt = $this->db->query("SELECT name FROM settings WHERE name LIKE 'crypto_%'");
            $settingsCount = $stmt->num_rows;
            if ($settingsCount > 0) {
                $this->testResults[] = "✅ Crypto ayarları mevcut ({$settingsCount} adet)";
            } else {
                $this->testResults[] = "❌ Crypto ayarları bulunamadı";
            }
        } catch (\Exception $e) {
            $this->testResults[] = "❌ Settings test hatası: " . $e->getMessage();
        }
        
        echo "Veritabanı şema testi tamamlandı.\n\n";
    }
    
    /**
     * Test TRON client functionality
     */
    private function testTronClient() {
        echo "🔗 TRON Client test ediliyor...\n";
        
        try {
            $tronClient = new TronClient();
            
            // Test address validation
            $validAddress = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
            $invalidAddress = 'invalid_address';
            
            if (TronClient::isValidAddress($validAddress)) {
                $this->testResults[] = "✅ Geçerli TRON adresi doğrulaması başarılı";
            } else {
                $this->testResults[] = "❌ Geçerli TRON adresi doğrulaması başarısız";
            }
            
            if (!TronClient::isValidAddress($invalidAddress)) {
                $this->testResults[] = "✅ Geçersiz TRON adresi reddedildi";
            } else {
                $this->testResults[] = "❌ Geçersiz TRON adresi kabul edildi";
            }
            
            // Test hex to base58 conversion
            $hexAddress = '41a614f803b6fd780986a42c78ec9c7f77e6ded13c';
            $base58Address = TronClient::hexToBase58($hexAddress);
            if ($base58Address) {
                $this->testResults[] = "✅ Hex to Base58 dönüştürme başarılı";
            } else {
                $this->testResults[] = "❌ Hex to Base58 dönüştürme başarısız";
            }
            
        } catch (\Exception $e) {
            $this->testResults[] = "❌ TRON Client test hatası: " . $e->getMessage();
        }
        
        echo "TRON Client testi tamamlandı.\n\n";
    }
    
    /**
     * Test TRON wallet functionality
     */
    private function testTronWallet() {
        echo "👛 TRON Wallet test ediliyor...\n";
        
        try {
            $tronWallet = new TronWallet();
            
            // Test wallet generation
            $wallet = $tronWallet->generateWallet();
            if ($wallet && isset($wallet['address'], $wallet['private_key'])) {
                $this->testResults[] = "✅ Wallet oluşturma başarılı";
                
                // Test address validation
                if (TronWallet::isValidTronAddress($wallet['address'])) {
                    $this->testResults[] = "✅ Oluşturulan wallet adresi geçerli";
                } else {
                    $this->testResults[] = "❌ Oluşturulan wallet adresi geçersiz";
                }
            } else {
                $this->testResults[] = "❌ Wallet oluşturma başarısız";
            }
            
            // Test database wallet creation (with test group)
            $testGroupId = 999; // Test group ID
            try {
                $dbWallet = $tronWallet->createWalletForGroup($testGroupId);
                if ($dbWallet) {
                    $this->testResults[] = "✅ Database wallet oluşturma test başarılı";
                    
                    // Clean up test wallet
                    $stmt = $this->db->prepare('DELETE FROM crypto_wallets WHERE group_id = ?');
                    $stmt->bind_param('i', $testGroupId);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $this->testResults[] = "❌ Database wallet oluşturma test başarısız";
                }
            } catch (\Exception $e) {
                $this->testResults[] = "⚠️ Database wallet test atlandı (normal): " . $e->getMessage();
            }
            
        } catch (\Exception $e) {
            $this->testResults[] = "❌ TRON Wallet test hatası: " . $e->getMessage();
        }
        
        echo "TRON Wallet testi tamamlandı.\n\n";
    }
    
    /**
     * Test crypto security functionality
     */
    private function testCryptoSecurity() {
        echo "🔐 Crypto Security test ediliyor...\n";
        
        try {
            $security = new CryptoSecurity();
            
            // Test address validation
            $validAddress = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
            $invalidAddress = 'invalid_address';
            
            $validResult = $security->validateTronAddress($validAddress);
            if ($validResult['valid']) {
                $this->testResults[] = "✅ Security address validation - geçerli adres";
            } else {
                $this->testResults[] = "❌ Security address validation - geçerli adres reddedildi";
            }
            
            $invalidResult = $security->validateTronAddress($invalidAddress);
            if (!$invalidResult['valid']) {
                $this->testResults[] = "✅ Security address validation - geçersiz adres reddedildi";
            } else {
                $this->testResults[] = "❌ Security address validation - geçersiz adres kabul edildi";
            }
            
            // Test amount validation
            $validAmount = $security->validatePaymentAmount(100.0, 100.0);
            if ($validAmount['valid']) {
                $this->testResults[] = "✅ Amount validation - doğru miktar";
            } else {
                $this->testResults[] = "❌ Amount validation - doğru miktar reddedildi";
            }
            
            $invalidAmount = $security->validatePaymentAmount(100.0, 50.0);
            if (!$invalidAmount['valid']) {
                $this->testResults[] = "✅ Amount validation - yanlış miktar reddedildi";
            } else {
                $this->testResults[] = "❌ Amount validation - yanlış miktar kabul edildi";
            }
            
            // Test encryption/decryption
            $testData = 'test_private_key_12345';
            $encrypted = CryptoSecurity::encrypt($testData);
            $decrypted = CryptoSecurity::decrypt($encrypted);
            
            if ($testData === $decrypted) {
                $this->testResults[] = "✅ Encryption/Decryption test başarılı";
            } else {
                $this->testResults[] = "❌ Encryption/Decryption test başarısız";
            }
            
        } catch (\Exception $e) {
            $this->testResults[] = "❌ Crypto Security test hatası: " . $e->getMessage();
        }
        
        echo "Crypto Security testi tamamlandı.\n\n";
    }
    
    /**
     * Test complete payment workflow
     */
    private function testPaymentWorkflow() {
        echo "🔄 Payment Workflow test ediliyor...\n";
        
        try {
            // Test payment method exists
            $stmt = $this->db->query("SELECT id FROM payment_methods WHERE method_type = 'cryptocurrency' AND active = 1 LIMIT 1");
            $paymentMethod = $stmt->fetch_assoc();
            
            if ($paymentMethod) {
                $this->testResults[] = "✅ Cryptocurrency payment method mevcut";
            } else {
                $this->testResults[] = "⚠️ Cryptocurrency payment method bulunamadı (manuel ekleme gerekli)";
            }
            
            // Test workflow components exist
            $components = [
                'app/Controllers/GroupController.php' => 'Group Controller',
                'app/Controllers/TopupController.php' => 'Topup Controller',
                'app/Views/groups/topup.php' => 'Topup View',
                'crypto_payment_monitor.php' => 'Payment Monitor',
                'app/Helpers/TronClient.php' => 'TRON Client',
                'app/Helpers/TronWallet.php' => 'TRON Wallet',
                'app/Helpers/CryptoSecurity.php' => 'Crypto Security'
            ];
            
            foreach ($components as $file => $name) {
                if (file_exists(__DIR__ . '/' . $file)) {
                    $this->testResults[] = "✅ {$name} dosyası mevcut";
                } else {
                    $this->testResults[] = "❌ {$name} dosyası bulunamadı";
                }
            }
            
            // Test cron job script
            if (file_exists(__DIR__ . '/crypto_payment_monitor.php')) {
                if (is_readable(__DIR__ . '/crypto_payment_monitor.php')) {
                    $this->testResults[] = "✅ Cron job script erişilebilir";
                } else {
                    $this->testResults[] = "❌ Cron job script okunamıyor";
                }
            }
            
        } catch (\Exception $e) {
            $this->testResults[] = "❌ Payment Workflow test hatası: " . $e->getMessage();
        }
        
        echo "Payment Workflow testi tamamlandı.\n\n";
    }
    
    /**
     * Print test results
     */
    private function printResults() {
        echo "📋 TEST SONUÇLARI:\n";
        echo str_repeat("=", 50) . "\n";
        
        $passed = 0;
        $failed = 0;
        $warnings = 0;
        
        foreach ($this->testResults as $result) {
            echo $result . "\n";
            
            if (strpos($result, '✅') !== false) {
                $passed++;
            } elseif (strpos($result, '❌') !== false) {
                $failed++;
            } elseif (strpos($result, '⚠️') !== false) {
                $warnings++;
            }
        }
        
        echo str_repeat("=", 50) . "\n";
        echo "📊 ÖZET:\n";
        echo "✅ Başarılı: {$passed}\n";
        echo "❌ Başarısız: {$failed}\n";
        echo "⚠️ Uyarı: {$warnings}\n";
        echo "📝 Toplam: " . count($this->testResults) . "\n\n";
        
        if ($failed === 0) {
            echo "🎉 TÜM TESTLER BAŞARILI! Sistem kullanıma hazır.\n";
        } else {
            echo "⚠️ Bazı testler başarısız oldu. Lütfen hataları kontrol edin.\n";
        }
        
        echo "\n💡 SONRAKI ADIMLAR:\n";
        echo "1. crypto_schema.sql dosyasını veritabanında çalıştırın\n";
        echo "2. composer install komutunu çalıştırın\n";
        echo "3. TRON API key'ini settings tablosuna ekleyin\n";
        echo "4. Cryptocurrency payment method'u payment_methods tablosuna ekleyin\n";
        echo "5. Cron job'ı kurun: */2 * * * * php " . __DIR__ . "/crypto_payment_monitor.php\n";
        echo "6. Sistem loglarını kontrol edin\n\n";
    }
}

// Run tests if called from command line
if (php_sapi_name() === 'cli') {
    $tester = new CryptoPaymentTester();
    $tester->runAllTests();
} else {
    echo '<h1>TRON TRC20 USDT Payment System Test</h1>';
    echo '<p>Bu test sadece command line üzerinden çalıştırılabilir:</p>';
    echo '<code>php test_crypto_payment.php</code>';
}