<?php
/**
 * Setup Audit Logs
 * Creates table for tracking system changes
 */

require_once __DIR__ . '/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Setting up Audit Logs...</h1>";

$sql = "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, 
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'audit_logs' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

echo "<h2>Setup Complete!</h2>";
?>
