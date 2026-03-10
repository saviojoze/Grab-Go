<?php
require_once 'config.php';
$email = 'staff@grabandgo.com';
$res = $conn->query("SELECT id, email, role, password FROM users WHERE email = '$email'");
if ($row = $res->fetch_assoc()) {
    echo "User exists: " . $row['email'] . " (Role: " . $row['role'] . ")" . PHP_EOL;
    // Don't echo the actual password hash but check if it's set
    if (!empty($row['password'])) {
        echo "Password is set." . PHP_EOL;
    }
} else {
    echo "User not found!" . PHP_EOL;
}
