<?php
require 'config.php';
$email = 'testcustomer@gmail.com';
$password = 'password123';
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES ('Test Customer', ?, ?, 'customer')");
$stmt->bind_param("ss", $email, $hashed);
if ($stmt->execute()) {
    echo "Customer created successfully\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
