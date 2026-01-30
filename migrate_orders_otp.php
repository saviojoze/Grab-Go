<?php
require_once 'config.php';

// Add delivery_otp column to orders table
$sql = "ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_otp VARCHAR(6) DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "Column 'delivery_otp' added successfully.\n";
} else {
    echo "Error adding column: " . $conn->error . "\n";
}

// Populate existing orders with random OTPs
$result = $conn->query("SELECT id FROM orders WHERE delivery_otp IS NULL");
if ($result->num_rows > 0) {
    echo "Backfilling OTPs for existing orders...\n";
    $stmt = $conn->prepare("UPDATE orders SET delivery_otp = ? WHERE id = ?");
    while($row = $result->fetch_assoc()) {
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $stmt->bind_param("si", $otp, $row['id']);
        $stmt->execute();
    }
    echo "Backfill complete.\n";
} else {
    echo "No orders needed backfilling.\n";
}
?>
