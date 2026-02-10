<?php
require_once __DIR__ . '/../config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h2>Migrating Database for Leave System...</h2>";

// 1. Add user_id to staff_directory if not exists
$col_check = $conn->query("SHOW COLUMNS FROM staff_directory LIKE 'user_id'");
if ($col_check->num_rows == 0) {
    if ($conn->query("ALTER TABLE staff_directory ADD COLUMN user_id INT NULL UNIQUE AFTER id")) {
        echo "<p style='color:green'>✅ Added 'user_id' column to staff_directory.</p>";
    } else {
        echo "<p style='color:red'>❌ Error adding column: " . $conn->error . "</p>";
    }
} else {
    echo "<p>ℹ️ 'user_id' column already exists in staff_directory.</p>";
}

// 2. Create leave_requests table
$sql_leave = "CREATE TABLE IF NOT EXISTS leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    leave_type VARCHAR(50) DEFAULT 'Sick Leave',
    reason TEXT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    admin_remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff_directory(id) ON DELETE CASCADE
)";

if ($conn->query($sql_leave) === TRUE) {
    echo "<p style='color:green'>✅ Table 'leave_requests' check/creation successful.</p>";
} else {
    die("<p style='color:red'>❌ Error creating table: " . $conn->error . "</p>");
}

// 3. Backfill user_id for existing staff
echo "<h3>Linking Staff to Users...</h3>";
$staff_res = $conn->query("SELECT id, full_name FROM staff_directory WHERE user_id IS NULL");
if ($staff_res && $staff_res->num_rows > 0) {
    $updated_count = 0;
    while ($staff = $staff_res->fetch_assoc()) {
        $name = $conn->real_escape_string($staff['full_name']);
        
        // Find user by email match? No, we only have full_name here reliably if created via old flow.
        // Let's try update based on full_name matching users table.
        // NOTE: This is a best-effort link manually or if names match exactly.
        
        $user_res = $conn->query("SELECT id FROM users WHERE full_name = '$name' AND role = 'staff' LIMIT 1");
        if ($user_res && $user_res->num_rows > 0) {
            $user_id = $user_res->fetch_assoc()['id'];
            $conn->query("UPDATE staff_directory SET user_id = $user_id WHERE id = " . $staff['id']);
            echo "<p>🔗 Linked Staff '{$staff['full_name']}' to User ID: $user_id</p>";
            $updated_count++;
        } else {
            echo "<p style='color:orange'>⚠️ Could not find matching User account for Staff '{$staff['full_name']}'. They may need to be re-added or manually linked.</p>";
        }
    }
    if ($updated_count > 0) {
        echo "<p style='color:green'>✅ Linked $updated_count staff members.</p>";
    }
} else {
    echo "<p>ℹ️ All staff members are already linked or no unlinked staff found.</p>";
}

echo "<h3>Migration Complete!</h3>";
echo "<a href='../admin/manage_staff.php'>Go to Manage Staff</a>";
?>
