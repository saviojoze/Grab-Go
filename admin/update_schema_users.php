<?php
require_once __DIR__ . '/../config.php';

// Check if is_blocked column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_blocked'");

if ($result->num_rows == 0) {
    // Column doesn't exist, add it
    if ($conn->query("ALTER TABLE users ADD COLUMN is_blocked TINYINT(1) DEFAULT 0")) {
        echo "Successfully added is_blocked column to users table.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column is_blocked already exists.\n";
}
?>
