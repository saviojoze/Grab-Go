<?php
require_once 'config.php';

// Add columns for Razorpay
$columns = [
    "ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending'",
    "ADD COLUMN IF NOT EXISTS razorpay_order_id VARCHAR(255) DEFAULT NULL",
    "ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(255) DEFAULT NULL"
];

foreach ($columns as $col) {
    if ($conn->query("ALTER TABLE orders $col") === TRUE) {
        echo "Executed: ALTER TABLE orders $col\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

echo "Database schema updated for Razorpay.\n";
?>
