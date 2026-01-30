<?php
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<html><body style='font-family: sans-serif; padding: 40px;'>";
echo "<h2>Setting up Demo Attendance Data...</h2>";

// 1. Ensure DB Schema is ready (Mini-Migration)
$check_col = $conn->query("SHOW COLUMNS FROM staff_attendance LIKE 'staff_name'");
if ($check_col->num_rows == 0) {
    echo "<p>Upgrading database schema...</p>";
    $conn->query("ALTER TABLE staff_attendance ADD COLUMN staff_name VARCHAR(255) NOT NULL AFTER user_id");
    
    // Drop bad indexes if any
    $result = $conn->query("SHOW INDEX FROM staff_attendance WHERE Key_name != 'PRIMARY'");
    while($row = $result->fetch_assoc()) {
        if ($row['Non_unique'] == 0 && $row['Key_name'] != 'PRIMARY') {
            $conn->query("ALTER TABLE staff_attendance DROP INDEX `{$row['Key_name']}`");
        }
    }
    // Update existing nulls
    $conn->query("UPDATE staff_attendance SET staff_name = CONCAT('Staff_', user_id) WHERE staff_name IS NULL");
    // Add new unique
    $conn->query("ALTER TABLE staff_attendance ADD UNIQUE KEY `name_date_unique` (staff_name, date)");
    $conn->query("ALTER TABLE staff_attendance MODIFY user_id INT NULL");
    echo "<p style='color:green'>Database Upgraded!</p>";
}

// 2. Insert Names
$names = [
    ['name' => 'Alice Johnson', 'status' => 'Present', 'in' => '09:00', 'out' => '17:00'],
    ['name' => 'Bob Smith', 'status' => 'Late', 'in' => '10:30', 'out' => '18:00'],
    ['name' => 'Charlie Brown', 'status' => 'Half Day', 'in' => '09:00', 'out' => '13:00']
];

$date = date('Y-m-d');
$today_str = date('F j, Y');

echo "<h3>Marking Attendance for Today ($today_str):</h3><ul>";

foreach ($names as $person) {
    $stmt = $conn->prepare("INSERT INTO staff_attendance (staff_name, date, status, check_in_time, check_out_time) 
                           VALUES (?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE 
                           status = VALUES(status), 
                           check_in_time = VALUES(check_in_time), 
                           check_out_time = VALUES(check_out_time)");
    
    $stmt->bind_param("sssss", $person['name'], $date, $person['status'], $person['in'], $person['out']);
    
    if ($stmt->execute()) {
        echo "<li>Marked <strong>{$person['name']}</strong> as <span style='color:blue'>{$person['status']}</span></li>";
        
        // Audit
        $desc = "Demo data: Marked {$person['name']}";
        $conn->query("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (1, 'DEMO_DATA', '$desc', '127.0.0.1')");
        
    } else {
        echo "<li>Error marking {$person['name']}: " . $stmt->error . "</li>";
    }
}
echo "</ul>";

echo "<p style='margin-top:20px;'><strong>Done!</strong> <a href='admin/attendance.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Attendance Board</a></p>";
echo "</body></html>";
?>
