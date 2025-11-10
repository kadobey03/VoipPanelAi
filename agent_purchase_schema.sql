-- Agent Satın Alma Sistemi SQL Schema

-- Agent ürün tanımları tablosu (admin tarafından yönetilir)
CREATE TABLE agent_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    phone_prefix VARCHAR(10) DEFAULT '0905',
    per_minute_cost DECIMAL(6,4) DEFAULT 0.4500,
    is_single_user TINYINT(1) DEFAULT 1,
    is_callback_enabled TINYINT(1) DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    is_subscription TINYINT(1) DEFAULT 0,
    subscription_monthly_fee DECIMAL(10,2) DEFAULT 0.00,
    setup_fee DECIMAL(10,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Kullanıcıların satın aldığı agentler
CREATE TABLE user_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    agent_product_id INT NOT NULL,
    agent_number VARCHAR(20) NOT NULL,
    custom_price DECIMAL(10,2) NULL,
    custom_monthly_fee DECIMAL(10,2) NULL,
    status ENUM('active', 'suspended', 'expired') DEFAULT 'active',
    purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATETIME NULL,
    last_subscription_payment DATETIME NULL,
    next_subscription_due DATETIME NULL,
    is_lifetime TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_product_id) REFERENCES agent_products(id),
    UNIQUE KEY unique_agent_number (agent_number)
);

-- Agent satın alma geçmişi ve ödemeler
CREATE TABLE agent_purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    user_agent_id INT NOT NULL,
    agent_product_id INT NOT NULL,
    purchase_type ENUM('initial', 'subscription', 'renewal') DEFAULT 'initial',
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('balance', 'crypto', 'manual') DEFAULT 'balance',
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    transaction_reference VARCHAR(100) NULL,
    purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    notes TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_agent_id) REFERENCES user_agents(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_product_id) REFERENCES agent_products(id),
    INDEX idx_user_purchase (user_id, purchase_date),
    INDEX idx_status_date (status, purchase_date)
);

-- Abonelik ödemesi takibi
CREATE TABLE agent_subscription_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_agent_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    payment_date DATETIME NULL,
    status ENUM('pending', 'paid', 'overdue', 'failed') DEFAULT 'pending',
    payment_method ENUM('balance', 'manual') DEFAULT 'balance',
    failure_count TINYINT DEFAULT 0,
    next_retry_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_agent_id) REFERENCES user_agents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_due_date_status (due_date, status),
    INDEX idx_user_due (user_id, due_date)
);

-- Örnek ürün kayıtları
INSERT INTO agent_products (name, description, phone_prefix, per_minute_cost, is_single_user, is_callback_enabled, price, is_subscription, subscription_monthly_fee, setup_fee) VALUES
('0905 Li Numara', '0905 li numara tek kullanıcı için. Dakika başı 0.45$ ücret.', '0905', 0.4500, 1, 0, 250.00, 0, 0.00, 0.00),
('0905 Li Sabit Geri Aranabilen Numara', '0905 li sabit geri aranabilen numara. Dakika başı 0.45$ ücret. Kurulum ödemesi sonrasında aylık 100$.', '0905', 0.4500, 1, 1, 350.00, 1, 100.00, 350.00);

-- Transactions tablosuna yeni tip ekle
ALTER TABLE transactions 
MODIFY COLUMN type ENUM('topup','debit_call','adjust','agent_purchase','agent_subscription') NOT NULL;