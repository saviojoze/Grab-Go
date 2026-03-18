<?php
require 'config.php';
$res = $conn->query("SELECT id, email, role, password FROM users");
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | Role: {$row['role']} | Email: {$row['email']} | Pass Hash: " . substr($row['password'], 0, 10) . "...\n";
}
?>
