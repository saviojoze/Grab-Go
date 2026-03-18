<?php
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h2>Setting up Staff Directory...</h2>";

// 1. Create table
$sql = "CREATE TABLE IF NOT EXISTS staff_directory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    position VARCHAR(50) DEFAULT 'Staff',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green'>Table 'staff_directory' created/checked.</p>";
} else {
    die("Error creating table: " . $conn->error);
}

// 2. Seed Data (if empty)
$check = $conn->query("SELECT COUNT(*) as count FROM staff_directory");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    echo "<p>Seeding default staff (Alice, Bob, Charlie)...</p>";
    $stmt = $conn->prepare("INSERT INTO staff_directory (full_name, position) VALUES (?, ?)");
    $staff = [
        ['Alice Johnson', 'Cashier'],
        ['Bob Smith', 'Stocker'],
        ['Charlie Brown', 'Manager'],
        ['David Miller', 'Security']
    ];
    foreach ($staff as $s) {
        $stmt->bind_param("ss", $s[0], $s[1]);
        $stmt->execute();
    }
    echo "<p style='color:green'>Seeded 4 employees.</p>";
} else {
    echo "<p>Directory already has staff.</p>";
}

// 3. Update Attendance Table to support employee_id
$col_check = $conn->query("SHOW COLUMNS FROM staff_attendance LIKE 'employee_id'");
if ($col_check->num_rows == 0) {
    $conn->query("ALTER TABLE staff_attendance ADD COLUMN employee_id INT NULL AFTER staff_name");
    echo "<p style='color:green'>Added 'employee_id' to attendance table.</p>";
}

echo "<h3>Setup Complete!</h3>";
echo "<p><a href='admin/attendance.php'>Go to Attendance Page</a></p>";
?>
