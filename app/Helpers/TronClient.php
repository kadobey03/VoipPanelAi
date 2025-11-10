<?php
namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TronClient {
    private $client;
    private $baseUrl = 'https://api.trongrid.io';
    private $apiKey;
    
    // USDT TRC20 Contract Address
    const USDT_CONTRACT = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
    
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'TRON-PRO-API-KEY' => $this->apiKey ?: ''
            ]
        ]);
    }
    
    /**
     * Get account info including TRX balance
     */
    public function getAccountInfo($address) {
        try {
            $response = $this->client->post('/v1/accounts/' . $address);
            $data = json_decode($response->getBody(), true);
            return $data['data'][0] ?? null;
        } catch (RequestException $e) {
            error_log('TronClient::getAccountInfo Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get TRC20 token balance (USDT)
     */
    public function getTRC20Balance($address, $contractAddress = self::USDT_CONTRACT) {
        try {
            $response = $this->client->post('/v1/accounts/' . $address . '/trc20/' . $contractAddress);
            $data = json_decode($response->getBody(), true);
            
            if (isset($data['data'][0]['balance'])) {
                // USDT has 6 decimal places
                return (float)($data['data'][0]['balance'] / 1000000);
            }
            return 0.0;
        } catch (RequestException $e) {
            error_log('TronClient::getTRC20Balance Error: ' . $e->getMessage());
            return 0.0;
        }
    }
    
    /**
     * Get TRC20 token transactions for an address
     */
    public function getTRC20Transactions($address, $contractAddress = self::USDT_CONTRACT, $limit = 50) {
        try {
            $response = $this->client->get('/v1/accounts/' . $address . '/transactions/trc20', [
                'query' => [
                    'limit' => $limit,
                    'contract_address' => $contractAddress,
                    'order_by' => 'block_timestamp,desc'
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            return $data['data'] ?? [];
        } catch (RequestException $e) {
            error_log('TronClient::getTRC20Transactions Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get transaction info by hash
     */
    public function getTransactionInfo($txHash) {
        try {
            $response = $this->client->post('/wallet/gettransactioninfobyid', [
                'json' => ['value' => $txHash]
            ]);
            
            $data = json_decode($response->getBody(), true);
            return $data ?? null;
        } catch (RequestException $e) {
            error_log('TronClient::getTransactionInfo Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get transaction by ID
     */
    public function getTransactionById($txHash) {
        try {
            $response = $this->client->post('/wallet/gettransactionbyid', [
                'json' => ['value' => $txHash]
            ]);
            
            $data = json_decode($response->getBody(), true);
            return $data ?? null;
        } catch (RequestException $e) {
            error_log('TronClient::getTransactionById Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get current block info
     */
    public function getCurrentBlock() {
        try {
            $response = $this->client->post('/wallet/getnowblock');
            $data = json_decode($response->getBody(), true);
            return $data ?? null;
        } catch (RequestException $e) {
            error_log('TronClient::getCurrentBlock Error: ' . $e->getMessage());
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
    
    /**
     * Convert hex address to base58 (TRON format)
     */
    public static function hexToBase58($hexAddress) {
        if (strpos($hexAddress, '0x') === 0) {
            $hexAddress = substr($hexAddress, 2);
        }
        
        // Add TRON prefix (0x41) if not present
        if (strlen($hexAddress) === 40) {
            $hexAddress = '41' . $hexAddress;
        }
        
        return self::base58CheckEncode($hexAddress);
    }
    
    /**
     * Convert base58 address to hex
     */
    public static function base58ToHex($base58Address) {
        $hex = self::base58CheckDecode($base58Address);
        // Remove TRON prefix (41)
        if (substr($hex, 0, 2) === '41') {
            $hex = substr($hex, 2);
        }
        return $hex;
    }
    
    /**
     * Base58 encoding with checksum (Bitcoin-style)
     */
    private static function base58CheckEncode($hex) {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        
        // Add checksum
        $hash = hash('sha256', hex2bin($hex));
        $hash = hash('sha256', hex2bin($hash));
        $checksum = substr($hash, 0, 8);
        $hex .= $checksum;
        
        // Convert to base58
        $decimal = gmp_init($hex, 16);
        $output = '';
        
        while (gmp_cmp($decimal, 0) > 0) {
            list($decimal, $remainder) = gmp_div_qr($decimal, 58);
            $output = $alphabet[gmp_intval($remainder)] . $output;
        }
        
        // Add leading 1s for leading 00s
        for ($i = 0; $i < strlen($hex) && substr($hex, $i, 2) === '00'; $i += 2) {
            $output = '1' . $output;
        }
        
        return $output;
    }
    
    /**
     * Base58 decoding with checksum verification
     */
    private static function base58CheckDecode($base58) {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        
        $decimal = gmp_init(0);
        $multi = gmp_init(1);
        
        for ($i = strlen($base58) - 1; $i >= 0; $i--) {
            $char = $base58[$i];
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                throw new \InvalidArgumentException('Invalid base58 character');
            }
            $decimal = gmp_add($decimal, gmp_mul($multi, $pos));
            $multi = gmp_mul($multi, 58);
        }
        
        $hex = gmp_strval($decimal, 16);
        if (strlen($hex) % 2) {
            $hex = '0' . $hex;
        }
        
        // Verify checksum
        $payload = substr($hex, 0, -8);
        $checksum = substr($hex, -8);
        
        $hash = hash('sha256', hex2bin($payload));
        $hash = hash('sha256', hex2bin($hash));
        $expectedChecksum = substr($hash, 0, 8);
        
        if ($checksum !== $expectedChecksum) {
            throw new \InvalidArgumentException('Invalid checksum');
        }
        
        return $payload;
    }
}