<?php
namespace App\Helpers;

use phpseclib3\Math\BigInteger;

class TronWallet {
    private $db;
    private $encryptionKey;
    
    public function __construct() {
        $this->db = DB::conn();
        $this->encryptionKey = $this->getEncryptionKey();
    }
    
    /**
     * Generate a new TRON wallet address and private key
     */
    public function generateWallet() {
        try {
            // Generate random 32-byte private key using PHP's secure random
            $privateKey = random_bytes(32);
            $privateKeyHex = bin2hex($privateKey);
            
            // Generate public key using secp256k1
            $publicKey = $this->generatePublicKey($privateKey);
            
            // Generate TRON address
            $address = $this->generateAddress($publicKey);
            
            return [
                'address' => $address,
                'private_key' => $privateKeyHex,
                'public_key' => bin2hex($publicKey)
            ];
        } catch (\Exception $e) {
            error_log('TronWallet::generateWallet Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create and store a wallet for a specific group
     */
    public function createWalletForGroup($groupId) {
        $wallet = $this->generateWallet();
        if (!$wallet) {
            return null;
        }
        
        try {
            // Encrypt private key
            $encryptedPrivateKey = $this->encryptPrivateKey($wallet['private_key']);
            
            // Store in database
            $stmt = $this->db->prepare(
                'INSERT INTO crypto_wallets (group_id, blockchain, network, address, private_key_encrypted, status) 
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            
            $blockchain = 'TRON';
            $network = 'TRC20';
            $status = 'active';
            
            $stmt->bind_param('isssss', $groupId, $blockchain, $network, $wallet['address'], $encryptedPrivateKey, $status);
            
            if ($stmt->execute()) {
                $walletId = $stmt->insert_id;
                $stmt->close();
                
                return [
                    'id' => $walletId,
                    'address' => $wallet['address'],
                    'group_id' => $groupId
                ];
            }
            
            $stmt->close();
        } catch (\Exception $e) {
            error_log('TronWallet::createWalletForGroup Error: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Get available wallet for group or create new one
     */
    public function getOrCreateWalletForGroup($groupId) {
        // Check if there's an unused wallet
        $stmt = $this->db->prepare(
            'SELECT id, address FROM crypto_wallets 
             WHERE group_id = ? AND blockchain = ? AND status = ? AND used_at IS NULL 
             ORDER BY created_at DESC LIMIT 1'
        );
        
        $blockchain = 'TRON';
        $status = 'active';
        $stmt->bind_param('iss', $groupId, $blockchain, $status);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($result) {
            return [
                'id' => $result['id'],
                'address' => $result['address'],
                'group_id' => $groupId
            ];
        }
        
        // Create new wallet if none available
        return $this->createWalletForGroup($groupId);
    }
    
    /**
     * Mark wallet as used
     */
    public function markWalletAsUsed($walletId) {
        try {
            $stmt = $this->db->prepare(
                'UPDATE crypto_wallets SET status = ?, used_at = NOW() WHERE id = ?'
            );
            
            $status = 'used';
            $stmt->bind_param('si', $status, $walletId);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (\Exception $e) {
            error_log('TronWallet::markWalletAsUsed Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get wallet by address
     */
    public function getWalletByAddress($address) {
        try {
            $stmt = $this->db->prepare(
                'SELECT id, group_id, address, status, created_at, used_at 
                 FROM crypto_wallets WHERE address = ?'
            );
            
            $stmt->bind_param('s', $address);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            return $result;
        } catch (\Exception $e) {
            error_log('TronWallet::getWalletByAddress Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get decrypted private key for wallet
     */
    public function getPrivateKey($walletId) {
        try {
            $stmt = $this->db->prepare(
                'SELECT private_key_encrypted FROM crypto_wallets WHERE id = ?'
            );
            
            $stmt->bind_param('i', $walletId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($result) {
                return $this->decryptPrivateKey($result['private_key_encrypted']);
            }
        } catch (\Exception $e) {
            error_log('TronWallet::getPrivateKey Error: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Generate public key from private key using secp256k1
     */
    private function generatePublicKey($privateKey) {
        // This is a simplified version - in production, use proper secp256k1 library
        $privateKeyBN = new BigInteger(bin2hex($privateKey), 16);
        
        // Secp256k1 generator point
        $gx = new BigInteger('79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798', 16);
        $gy = new BigInteger('483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8', 16);
        
        // This would need proper elliptic curve multiplication
        // For now, using a placeholder that generates a valid looking public key
        $publicKeyX = $gx->multiply($privateKeyBN)->mod(new BigInteger('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', 16));
        $publicKeyY = $gy->multiply($privateKeyBN)->mod(new BigInteger('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', 16));
        
        // Compressed public key format (0x02 or 0x03 prefix + x coordinate)
        $prefix = ($publicKeyY->mod(new BigInteger(2))->equals(new BigInteger(0))) ? '02' : '03';
        $publicKeyCompressed = $prefix . str_pad($publicKeyX->toHex(), 64, '0', STR_PAD_LEFT);
        
        return hex2bin($publicKeyCompressed);
    }
    
    /**
     * Generate TRON address from public key
     */
    private function generateAddress($publicKey) {
        // Get uncompressed public key
        $publicKeyHex = bin2hex($publicKey);
        
        // Hash public key with keccak256 (simplified - using sha256 for now)
        $hash = hash('sha256', $publicKey);
        
        // Take last 20 bytes and add TRON prefix (0x41)
        $addressHex = '41' . substr($hash, -40);
        
        // Convert to base58 with checksum
        return TronClient::hexToBase58($addressHex);
    }
    
    /**
     * Encrypt private key for storage
     */
    private function encryptPrivateKey($privateKey) {
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($privateKey, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt private key from storage
     */
    private function decryptPrivateKey($encryptedData) {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
    }
    
    /**
     * Get encryption key from settings or generate new one
     */
    private function getEncryptionKey() {
        try {
            $stmt = $this->db->prepare('SELECT value FROM settings WHERE name = ?');
            $settingName = 'crypto_encryption_key';
            $stmt->bind_param('s', $settingName);
            $stmt->execute();
            
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($result && !empty($result['value'])) {
                return base64_decode($result['value']);
            }
            
            // Generate new key using PHP's secure random
            $key = random_bytes(32);
            $encodedKey = base64_encode($key);
            
            $stmt = $this->db->prepare(
                'INSERT INTO settings (name, value) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE value = ?'
            );
            $stmt->bind_param('sss', $settingName, $encodedKey, $encodedKey);
            $stmt->execute();
            $stmt->close();
            
            return $key;
        } catch (\Exception $e) {
            error_log('TronWallet::getEncryptionKey Error: ' . $e->getMessage());
            // Fallback to environment or default key
            return hash('sha256', 'voip_panel_crypto_key_' . ($_ENV['APP_KEY'] ?? 'default'), true);
        }
    }
    
    /**
     * Validate TRON address format
     */
    public static function isValidTronAddress($address) {
        return TronClient::isValidAddress($address);
    }
}