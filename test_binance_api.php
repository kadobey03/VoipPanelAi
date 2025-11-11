<?php
require_once 'config/bootstrap.php';

echo "BINANCE API Test\n";
echo "================\n\n";

try {
    // Binance API credentials (settings tablosundan alınacak)
    $db = \App\Helpers\DB::conn();
    
    $apiKey = null;
    $apiSecret = null;
    
    // Settings'ten API credentials çekmeye çalış
    try {
        $stmt = $db->prepare('SELECT name, value FROM settings WHERE name IN (?, ?)');
        $keyName = 'binance_api_key';
        $secretName = 'binance_api_secret';
        $stmt->bind_param('ss', $keyName, $secretName);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if ($row['name'] === 'binance_api_key') $apiKey = $row['value'];
            if ($row['name'] === 'binance_api_secret') $apiSecret = $row['value'];
        }
        $stmt->close();
    } catch (\Exception $e) {
        echo "Settings'ten API credentials alınamadı: " . $e->getMessage() . "\n";
    }
    
    if (!$apiKey || !$apiSecret) {
        echo "⚠️  UYARI: Binance API credentials bulunamadı!\n";
        echo "Settings tablosuna şunları ekleyin:\n";
        echo "INSERT INTO settings (name, value) VALUES ('binance_api_key', 'YOUR_API_KEY');\n";
        echo "INSERT INTO settings (name, value) VALUES ('binance_api_secret', 'YOUR_API_SECRET');\n\n";
        
        echo "Test için public endpoint'ler kullanılacak...\n\n";
    }
    
    // BinanceClient oluştur
    $client = new \App\Helpers\BinanceClient($apiKey, $apiSecret);
    
    echo "1. USDT Fiyat Kontrolü (Public):\n";
    $usdtPrice = $client->getUSDTPrice();
    echo "USDT/USD Fiyatı: $" . $usdtPrice . "\n\n";
    
    if ($apiKey && $apiSecret) {
        echo "2. Hesap Bilgileri:\n";
        $accountInfo = $client->getAccountInfo();
        if ($accountInfo) {
            echo "Hesap tipi: " . ($accountInfo['accountType'] ?? 'N/A') . "\n";
            echo "Trading açık: " . ($accountInfo['canTrade'] ? 'Evet' : 'Hayır') . "\n\n";
            
            echo "3. USDT Bakiyesi:\n";
            $usdtBalance = $client->getUSDTBalance();
            echo "USDT Balance: $usdtBalance USDT\n\n";
            
            echo "4. Son Deposit Geçmişi:\n";
            $deposits = $client->getDepositHistory('USDT', 5);
            if (is_array($deposits) && !empty($deposits)) {
                foreach ($deposits as $i => $deposit) {
                    echo "Deposit " . ($i+1) . ":\n";
                    echo "- Miktar: " . ($deposit['amount'] ?? 0) . " USDT\n";
                    echo "- Network: " . ($deposit['network'] ?? 'N/A') . "\n";
                    echo "- Status: " . ($deposit['status'] ?? 'N/A') . "\n";
                    echo "- Tarih: " . date('Y-m-d H:i:s', ($deposit['insertTime'] ?? 0) / 1000) . "\n";
                    echo "- TX ID: " . substr(($deposit['txId'] ?? 'N/A'), 0, 20) . "...\n\n";
                }
            } else {
                echo "Deposit geçmişi bulunamadı veya hata oluştu\n\n";
            }
            
            echo "5. Özel Miktar Kontrolü (5 USDT örneği):\n";
            $testAmount = 5.0;
            $recentDeposit = $client->checkRecentDeposits($testAmount, 'TRX', 3600);
            if ($recentDeposit && $recentDeposit['found']) {
                echo "✅ $testAmount USDT deposit bulundu!\n";
                echo "- Miktar: " . $recentDeposit['amount'] . " USDT\n";
                echo "- Status: " . $recentDeposit['status'] . "\n";
                echo "- Confirmations: " . $recentDeposit['confirmTimes'] . "\n";
            } else {
                echo "❌ Son 1 saatte $testAmount USDT deposit bulunamadı\n";
            }
            
        } else {
            echo "❌ Hesap bilgileri alınamadı. API credentials kontrol edin.\n";
        }
    } else {
        echo "API credentials bulunamadı, sadece public endpoints test edildi.\n";
    }
    
} catch (\Exception $e) {
    echo "HATA: " . $e->getMessage() . "\n";
}

echo "\n=============================\n";
echo "MANUEL CURL TEST KOMUTLARI:\n";
echo "=============================\n\n";

echo "Public - USDT Fiyat:\n";
echo "curl -X GET \"https://api.binance.com/api/v3/ticker/price?symbol=USDTUSDT\" -H \"Content-Type: application/json\"\n\n";

if ($apiKey) {
    echo "Private - Account Info (API KEY gerekli):\n";
    echo "curl -X GET \"https://api.binance.com/api/v3/account?timestamp=$(date +%s000)&signature=SIGNATURE\" -H \"X-MBX-APIKEY: $apiKey\"\n\n";
}

echo "Not: Private endpoint'ler için HMAC-SHA256 signature gerekli\n";