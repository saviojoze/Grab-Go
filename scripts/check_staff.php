<?php
require_once __DIR__ . '/../config.php';

$sql = "SELECT id, full_name, email, role FROM users WHERE role = 'staff'";
$result = $conn->query($sql);

echo "Staff Accounts:\n";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Email: [" . $row["email"] . "] Name: [" . $row["full_name"] . "]\n";
    }
} else {
    echo "No staff accounts found.\n";
}
?>
