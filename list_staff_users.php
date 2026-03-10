<?php
require_once 'config.php';
$res = $conn->query("SELECT id, full_name, email, role, is_blocked, password FROM users WHERE role = 'staff'");
echo "--- STAFF USERS ---" . PHP_EOL;
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['full_name'] . " | Email: " . $row['email'] . " | Role: " . $row['role'] . " | Blocked: " . $row['is_blocked'] . PHP_EOL;
    // Check if password hash is present
    if (!empty($row['password'])) {
        echo "   Password set: YES (Hash starts with " . substr($row['password'], 0, 10) . "...)" . PHP_EOL;
    } else {
        echo "   Password set: NO" . PHP_EOL;
    }
}
?>
