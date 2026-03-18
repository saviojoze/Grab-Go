<?php
require_once __DIR__ . '/../config.php';

$sql = "SELECT id, full_name, email, role, password FROM users LIMIT 10";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - Name: " . $row["full_name"]. " - Email: " . $row["email"]. " - Role: " . $row["role"]. " - Hash: " . $row["password"] . "\n";
    }
} else {
    echo "0 results";
}
?>
