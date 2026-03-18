<?php
require 'config.php';
$res = $conn->query("SELECT id, email, role, password FROM users WHERE role = 'customer'");
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | Email: {$row['email']} | Pass Hash: " . substr($row['password'], 0, 10) . "\n";
}
?>
