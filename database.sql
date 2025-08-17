-- Create database
CREATE DATABASE IF NOT EXISTS login_system;
USE login_system;

-- Users table (if not exists)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    api_key VARCHAR(64) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    recipient_id INT,
    amount DECIMAL(10,2) NOT NULL,
    transaction_code VARCHAR(5) NOT NULL UNIQUE,
    status ENUM('pending', 'completed', 'expired', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (recipient_id) REFERENCES users(id)
);

-- Pending codes table for generated codes
CREATE TABLE IF NOT EXISTS pending_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL UNIQUE,
    status ENUM('active', 'used', 'expired', 'cancelled') DEFAULT 'active',
    cancel_code VARCHAR(6) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    fixed_amount FLOAT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Add sample users with specified credentials
INSERT INTO users (username, password, balance) VALUES 
('Akankshi', '$2y$10$Y49QULJv9YRSEgnpTkoRkOyUVYXZTZ47spNrKS.IGqZEhIpgzlOnq', 1000000.00),
('Akhil', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1000000.00);

-- Note: The password hash above corresponds to 'password' - you may want to change these
-- For Akankshi with password '2020' and Akhil with password '2012', you would need to generate proper hashes 