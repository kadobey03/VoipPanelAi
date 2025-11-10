<?php
namespace App\Helpers;

class BinanceClient {
    private $baseUrl = 'https://api.binance.com';
    private $apiKey;
    private $apiSecret;
    
    public function __construct($apiKey = null, $apiSecret = null) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }
    
    /**
     * Make HTTP request using cURL
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null, $signed = false) {
        $url = $this->baseUrl . $endpoint;
        
        if ($signed && $this->apiSecret) {
            $data['timestamp'] = time() * 1000;
            $queryString = http_build_query($data);
            $signature = hash_hmac('sha256', $queryString, $this->apiSecret);
            $data['signature'] = $signature;
        }
        
        if ($method === 'GET' && $data) {
            $url .= '?' . http_build_query($data);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'VoipPanel/1.0');
        
        // Headers
        $headers = ['Content-Type: application/json'];
        if ($this->apiKey) {
            $headers[] = 'X-MBX-APIKEY: ' . $this->apiKey;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Method specific options
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data && $method === 'POST') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("BinanceClient cURL error: $error");
            return null;
        }
        
        if ($httpCode !== 200) {
            error_log("BinanceClient HTTP error: $httpCode - Response: $response");
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get USDT price (public endpoint)
     */
    public function getUSDTPrice() {
        try {
            $data = $this->makeRequest('/api/v3/ticker/price', 'GET', ['symbol' => 'USDTTRY']);
            return $data['price'] ?? 0;
        } catch (\Exception $e) {
            error_log('BinanceClient::getUSDTPrice Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get account info (requires API keys)
     */
    public function getAccountInfo() {
        if (!$this->apiKey || !$this->apiSecret) {
            error_log('BinanceClient: API keys required for account info');
            return null;
        }
        
        try {
            $data = $this->makeRequest('/api/v3/account', 'GET', [], true);
            return $data;
        } catch (\Exception $e) {
            error_log('BinanceClient::getAccountInfo Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get USDT balance from account
     */
    public function getUSDTBalance() {
        $accountInfo = $this->getAccountInfo();
        if (!$accountInfo || !isset($accountInfo['balances'])) {
            return 0.0;
        }
        
        foreach ($accountInfo['balances'] as $balance) {
            if ($balance['asset'] === 'USDT') {
                return (float)$balance['free'];
            }
        }
        
        return 0.0;
    }
    
    /**
     * Get deposit history (requires API keys)
     */
    public function getDepositHistory($coin = 'USDT', $limit = 50) {
        if (!$this->apiKey || !$this->apiSecret) {
            error_log('BinanceClient: API keys required for deposit history');
            return [];
        }
        
        try {
            $params = [
                'coin' => $coin,
                'limit' => $limit
            ];
            $data = $this->makeRequest('/sapi/v1/capital/deposit/hisrec', 'GET', $params, true);
            return $data ?? [];
        } catch (\Exception $e) {
            error_log('BinanceClient::getDepositHistory Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check recent deposits for specific amount and address
     */
    public function checkRecentDeposits($expectedAmount, $network = 'TRX', $paymentCreatedTime = null) {
        $deposits = $this->getDepositHistory('USDT', 20);
        
        if (!is_array($deposits)) {
            error_log('BinanceClient: getDepositHistory returned non-array');
            return false;
        }
        
        // Eğer payment creation time verilmişse, sadece ondan sonraki depolarına bak
        $cutoffTime = $paymentCreatedTime ? strtotime($paymentCreatedTime) : (time() - 6000); // Varsayılan 30 dakika
        
        error_log("BinanceClient: Checking deposits after " . date('Y-m-d H:i:s', $cutoffTime));
        error_log("BinanceClient: Looking for amount $expectedAmount on network $network");
        
        foreach ($deposits as $deposit) {
            $depositTime = $deposit['insertTime'] / 1000; // ms to seconds
            $depositAmount = (float)$deposit['amount'];
            $depositNetwork = $deposit['network'] ?? '';
            
            error_log("BinanceClient: Deposit - Time: " . date('Y-m-d H:i:s', $depositTime) . ", Amount: $depositAmount, Network: $depositNetwork");
            
            // Zaman, miktar ve network kontrolü (daha sıkı)
            if ($depositTime >= $cutoffTime &&
                abs($depositAmount - $expectedAmount) < 0.01 &&
                $depositNetwork === $network) {
                
                error_log("BinanceClient: MATCH FOUND!");
                return [
                    'found' => true,
                    'amount' => $depositAmount,
                    'txId' => $deposit['txId'] ?? '',
                    'confirmTimes' => $deposit['confirmTimes'] ?? 0,
                    'status' => $deposit['status'] ?? 0,
                    'depositTime' => $depositTime
                ];
            }
        }
        
        error_log("BinanceClient: No matching deposit found");
        return false;
    }
}