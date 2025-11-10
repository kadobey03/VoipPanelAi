<?php
namespace App\Helpers;

class TronClientCurl {
    private $baseUrl = 'https://api.trongrid.io';
    private $apiKey;
    
    // USDT TRC20 Contract Address
    const USDT_CONTRACT = 'TWyDPcChQdvx78Zu3AbQr4zTtVWogSnug9';
    
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey;
    }
    
    /**
     * Make HTTP request using cURL
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'VoipPanel/1.0');
        
        // Headers
        $headers = [
            'Content-Type: application/json'
        ];
        if ($this->apiKey) {
            $headers[] = 'TRON-PRO-API-KEY: ' . $this->apiKey;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Method specific options
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("TronClientCurl cURL error: $error");
            return null;
        }
        
        if ($httpCode !== 200) {
            error_log("TronClientCurl HTTP error: $httpCode - Response: $response");
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get account info including TRX balance
     */
    public function getAccountInfo($address) {
        try {
            $data = $this->makeRequest('/v1/accounts/' . $address, 'GET');
            return $data['data'][0] ?? null;
        } catch (\Exception $e) {
            error_log('TronClientCurl::getAccountInfo Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get TRC20 token balance (USDT)
     */
    public function getTRC20Balance($address, $contractAddress = self::USDT_CONTRACT) {
        try {
            // Correct TRON API endpoint for TRC20 balance
            $endpoint = "/v1/accounts/{$address}/trc20/{$contractAddress}";
            $data = $this->makeRequest($endpoint, 'GET');
            
            if (isset($data['data'][0]['balance'])) {
                // USDT has 6 decimal places
                return (float)($data['data'][0]['balance'] / 1000000);
            }
            return 0.0;
        } catch (\Exception $e) {
            error_log('TronClientCurl::getTRC20Balance Error: ' . $e->getMessage());
            return 0.0;
        }
    }
    
    /**
     * Get TRC20 token transactions for an address
     */
    public function getTRC20Transactions($address, $contractAddress = self::USDT_CONTRACT, $limit = 50) {
        try {
            $query = http_build_query([
                'limit' => $limit,
                'contract_address' => $contractAddress,
                'order_by' => 'block_timestamp,desc'
            ]);
            
            $data = $this->makeRequest('/v1/accounts/' . $address . '/transactions/trc20?' . $query, 'GET');
            return $data['data'] ?? [];
        } catch (\Exception $e) {
            error_log('TronClientCurl::getTRC20Transactions Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get transaction info by hash
     */
    public function getTransactionInfo($txHash) {
        try {
            $data = $this->makeRequest('/wallet/gettransactioninfobyid', 'POST', ['value' => $txHash]);
            return $data ?? null;
        } catch (\Exception $e) {
            error_log('TronClientCurl::getTransactionInfo Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get transaction by ID
     */
    public function getTransactionById($txHash) {
        try {
            $data = $this->makeRequest('/wallet/gettransactionbyid', 'POST', ['value' => $txHash]);
            return $data ?? null;
        } catch (\Exception $e) {
            error_log('TronClientCurl::getTransactionById Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get current block info
     */
    public function getCurrentBlock() {
        try {
            $data = $this->makeRequest('/wallet/getnowblock', 'POST');
            return $data ?? null;
        } catch (\Exception $e) {
            error_log('TronClientCurl::getCurrentBlock Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate TRON address format
     */
    public static function isValidAddress($address) {
        // TRON addresses start with 'T' and are 34 characters long
        return preg_match('/^T[A-Za-z0-9]{33}$/', $address);
    }
}