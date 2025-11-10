-- TRON TRC20 USDT Ödeme Sistemi için Veritabanı Şeması
-- Bu dosya mevcut install.sql'e eklenecek

-- Kripto cüzdan adresleri
CREATE TABLE crypto_wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    blockchain VARCHAR(20) DEFAULT 'TRON',
    network VARCHAR(20) DEFAULT 'TRC20',
    address VARCHAR(100) NOT NULL UNIQUE,
    private_key_encrypted TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    status ENUM('active','used','expired') DEFAULT 'active',
    INDEX idx_group_blockchain (group_id, blockchain),
    INDEX idx_address (address),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

-- Kripto ödeme talepleri
CREATE TABLE crypto_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    wallet_id INT NOT NULL,
    amount_requested DECIMAL(18,6) NOT NULL,
    amount_received DECIMAL(18,6) DEFAULT 0.000000,
    currency VARCHAR(10) DEFAULT 'USDT',
    blockchain VARCHAR(20) DEFAULT 'TRON',
    network VARCHAR(20) DEFAULT 'TRC20',
    wallet_address VARCHAR(100) NOT NULL,
    transaction_hash VARCHAR(100) NULL,
    confirmations INT DEFAULT 0,
    required_confirmations INT DEFAULT 19,
    status ENUM('pending','confirming','completed','failed','expired') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    expired_at TIMESTAMP NULL,
    notes TEXT NULL,
    INDEX idx_status (status),
    INDEX idx_wallet_address (wallet_address),
    INDEX idx_transaction_hash (transaction_hash),
    INDEX idx_group_user (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (wallet_id) REFERENCES crypto_wallets(id) ON DELETE CASCADE
);

-- Ödeme yöntemlerine cryptocurrency ekle (eğer yoksa)
INSERT IGNORE INTO payment_methods (name, method_type, details, fee_percent, fee_fixed, active) 
VALUES 
('USDT TRC20', 'cryptocurrency', 'Tether USDT via TRON TRC20 Network', 0.00, 0.00, 1),
('Bitcoin', 'cryptocurrency', 'Bitcoin (BTC) Payments', 0.00, 0.00, 0);

-- Topup requests tablosuna crypto payment referansı ekle (eğer yoksa)
ALTER TABLE topup_requests 
ADD COLUMN crypto_payment_id INT NULL,
ADD COLUMN crypto_wallet_address VARCHAR(100) NULL,
ADD COLUMN crypto_transaction_hash VARCHAR(100) NULL,
ADD INDEX idx_crypto_payment (crypto_payment_id);

-- Settings tablosuna crypto ayarları ekle
INSERT IGNORE INTO settings (name, value) VALUES
('crypto_tron_api_key', ''),
('crypto_usdt_min_amount', '1.00'),
('crypto_usdt_max_amount', '10000.00'),
('crypto_payment_timeout_hours', '24'),
('crypto_required_confirmations', '19'),
('crypto_master_wallet_address', ''),
('crypto_master_wallet_private_key_encrypted', '');

-- Güvenlik logları tablosu
CREATE TABLE security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    user_id INT NULL,
    group_id INT NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_user_id (user_id),
    INDEX idx_group_id (group_id),
    INDEX idx_created_at (created_at)
);

-- Kripto adres kara liste tablosu
CREATE TABLE crypto_blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    address VARCHAR(100) NOT NULL,
    blockchain VARCHAR(20) DEFAULT 'TRON',
    reason TEXT NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_address_blockchain (address, blockchain),
    INDEX idx_active (active)
);

-- Rate limiting tablosu (isteğe bağlı - cache kullanılabilir)
CREATE TABLE rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    requests_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, action_type),
    INDEX idx_window_start (window_start)
);