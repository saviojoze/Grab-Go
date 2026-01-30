<?php
/**
 * Setup Attendance System
 * Creates necessary database tables and ensures staff role exists
 */

require_once __DIR__ . '/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Setting up Staff Attendance System...</h1>";

// 1. Create table
$sql = "CREATE TABLE IF NOT EXISTS staff_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late', 'Half Day') DEFAULT 'Absent',
    check_in_time TIME NULL,
    check_out_time TIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (user_id, date)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'staff_attendance' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 2. Ensure we have a staff member for testing
// Check if any user has role='staff'
$result = $conn->query("SELECT id FROM users WHERE role = 'staff' LIMIT 1");
if ($result && $result->num_rows == 0) {
    // Promote a user or create one?
    // Let's create a dummy staff user
    $password_hash = password_hash('staff123', PASSWORD_DEFAULT);
    $email = 'staff@grabgo.com';
    
    // Check if email exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES ('Staff Member', ?, ?, 'staff')");
        $stmt->bind_param("ss", $email, $password_hash);
        if ($stmt->execute()) {
            echo "Created demo staff account: $email / staff123<br>";
        }
    }
} else {
    echo "Staff members exist in the database.<br>";
}

echo "<h2>Setup Complete!</h2>";
echo "<a href='admin/attendance.php'>Go to Attendance Dashboard</a>";
?>
