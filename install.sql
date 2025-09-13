-- PapaM VoIP Panel Kurulum SQL'i
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    exten VARCHAR(20),
    group_id INT,
    agent_id INT,
    role ENUM('superadmin','groupadmin','user') DEFAULT 'groupadmin',
    hidden TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    margin DECIMAL(5,2) DEFAULT 0.00,
    balance DECIMAL(12,2) DEFAULT 0.00,
    api_group_id INT NULL,
    api_group_name VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE calls (
    call_id VARCHAR(32) PRIMARY KEY,
    src VARCHAR(20),
    dst VARCHAR(20),
    start DATETIME,
    duration INT,
    billsec INT,
    disposition VARCHAR(50),
    group_id INT,
    user_id INT,
    cost_api DECIMAL(12,6) DEFAULT 0.000000,
    margin_percent DECIMAL(5,2) DEFAULT 0.00,
    amount_charged DECIMAL(12,6) DEFAULT 0.000000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE external_numbers (
    number VARCHAR(20) PRIMARY KEY,
    status ENUM('active','disabled','spam') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exten VARCHAR(20) NOT NULL UNIQUE,
    user_login VARCHAR(50),
    group_name VARCHAR(100),
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bakiye hareketleri
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    type ENUM('topup','debit_call','adjust') NOT NULL,
    amount DECIMAL(12,4) NOT NULL,
    reference VARCHAR(64) NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Varsayılan admin
INSERT INTO users (login, password, role) VALUES ('admin', '$2y$10$examplehash', 'superadmin');
