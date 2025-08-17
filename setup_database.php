<?php
require 'db.php';

// Create pending_codes table if it doesn't exist
$create_pending_codes = "
CREATE TABLE IF NOT EXISTS pending_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL UNIQUE,
    status ENUM('active', 'used', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($create_pending_codes)) {
    echo "Pending codes table created successfully!<br>";
} else {
    echo "Error creating pending codes table: " . $conn->error . "<br>";
}

// Update transactions table to ensure it has the status column
$alter_transactions = "
ALTER TABLE transactions 
MODIFY COLUMN status ENUM('pending', 'completed', 'expired') DEFAULT 'pending'";

if ($conn->query($alter_transactions)) {
    echo "Transactions table updated successfully!<br>";
} else {
    echo "Error updating transactions table: " . $conn->error . "<br>";
}

echo "Database setup completed! You can now use the payment system.";
?> 