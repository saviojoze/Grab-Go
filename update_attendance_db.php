<?php
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Updating Database Schema...</h2>";

// 1. Add staff_name column if it doesn't exist
$check_col = $conn->query("SHOW COLUMNS FROM staff_attendance LIKE 'staff_name'");
if ($check_col->num_rows == 0) {
    $sql = "ALTER TABLE staff_attendance ADD COLUMN staff_name VARCHAR(255) NOT NULL AFTER user_id";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>Added 'staff_name' column.</p>";
    } else {
        echo "<p style='color:red'>Error adding column: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Column 'staff_name' already exists.</p>";
}

// 2. Drop existing Unique Key on (user_id, date)
// We need to find the key name first, usually 'user_id' or 'user_id_2'
// But easiest is to DROP INDEX if exists.
// Let's try to drop the likely index names.
$indexes = ['user_id', 'date', 'user_id_date_unique']; // Guessing names usually created
$dropped = false;

// Actually, SHOW INDEX can tell us.
$result = $conn->query("SHOW INDEX FROM staff_attendance WHERE Key_name != 'PRIMARY'");
$indices_to_drop = [];
while($row = $result->fetch_assoc()) {
    // If the index creates a constraint on user_id+date, we drop it.
    // Simplifying: Drop ALL non-primary unique indexes to be safe and re-add correct one.
    if ($row['Non_unique'] == 0 && $row['Key_name'] != 'PRIMARY') {
        $indices_to_drop[] = $row['Key_name'];
    }
}
$indices_to_drop = array_unique($indices_to_drop);

foreach ($indices_to_drop as $index) {
    if ($conn->query("ALTER TABLE staff_attendance DROP INDEX `$index`") === TRUE) {
        echo "<p style='color:green'>Dropped constraint '$index'.</p>";
    }
}

// 3. Add new Unique Key (staff_name, date)
// We first ensure names are populated for existing records (if any)
$conn->query("UPDATE staff_attendance SET staff_name = CONCAT('Staff_', user_id) WHERE staff_name IS NULL OR staff_name = ''");

$sql = "ALTER TABLE staff_attendance ADD UNIQUE KEY `name_date_unique` (staff_name, date)";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green'>Added new UNIQUE constraint on (staff_name, date).</p>";
} else {
    // It might fail if duplicates exist now.
    echo "<p style='color:orange'>Notice: Could not add unique constraint (maybe duplicates exist?): " . $conn->error . "</p>";
}

// 4. Make user_id NULLABLE (since we rely on name)
$conn->query("ALTER TABLE staff_attendance MODIFY user_id INT NULL");

echo "<h3>Update Complete!</h3>";
echo "<p><a href='admin/attendance.php'>Go Back to Attendance</a></p>";
?>
