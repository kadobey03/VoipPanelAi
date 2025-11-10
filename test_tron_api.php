<?php
require_once 'config/bootstrap.php';

// Test wallet address - Gerçek Binance USDT Hot Wallet
$testWalletAddress = 'TQn9Y2khEsLJW1ChVWFMSMeRDow5KcbLSE'; // Binance Hot Wallet

echo "TRON API Test\n";
echo "=============\n";
echo "Test wallet: $testWalletAddress\n\n";

try {
    // TronClientCurl kullanarak test
    $client = new \App\Helpers\TronClientCurl();
    
    echo "1. USDT Balance Kontrolü:\n";
    $balance = $client->getTRC20Balance($testWalletAddress);
    echo "Balance: $balance USDT\n\n";
    
    echo "2. Son İşlemler:\n";
    $transactions = $client->getTRC20Transactions($testWalletAddress, \App\Helpers\TronClientCurl::USDT_CONTRACT, 10);
    echo "Toplam işlem sayısı: " . count($transactions) . "\n";
    
    if (!empty($transactions)) {
        foreach (array_slice($transactions, 0, 3) as $i => $tx) {
            echo "İşlem " . ($i+1) . ":\n";
            echo "- Miktar: " . (($tx['value'] ?? 0) / 1000000) . " USDT\n";
            echo "- Tarih: " . date('Y-m-d H:i:s', ($tx['block_timestamp'] ?? 0) / 1000) . "\n";
            echo "- TX Hash: " . ($tx['transaction_id'] ?? 'N/A') . "\n\n";
        }
    }
    
    echo "3. Güncel Blok:\n";
    $currentBlock = $client->getCurrentBlock();
    if ($currentBlock && isset($currentBlock['block_header']['raw_data']['number'])) {
        echo "Güncel blok numarası: " . $currentBlock['block_header']['raw_data']['number'] . "\n";
    } else {
        echo "Blok bilgisi alınamadı\n";
    }
    
} catch (\Exception $e) {
    echo "HATA: " . $e->getMessage() . "\n";
}

echo "\n=============================\n";
echo "MANUEL CURL TEST KOMUTLARI:\n";
echo "=============================\n\n";

echo "Balance kontrolü:\n";
echo "curl -X GET \"https://api.trongrid.io/v1/accounts/$testWalletAddress/trc20/TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t\" -H \"Content-Type: application/json\"\n\n";

echo "Son işlemler:\n";
echo "curl -X GET \"https://api.trongrid.io/v1/accounts/$testWalletAddress/transactions/trc20?limit=10&contract_address=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t\" -H \"Content-Type: application/json\"\n\n";