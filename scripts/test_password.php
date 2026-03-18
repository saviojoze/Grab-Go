<?php
require_once __DIR__ . '/../config.php';

$email = 'staff@grabandgo.com';
$sql = "SELECT id, full_name, email, role, password FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "Email: " . $row["email"] . "\n";
    echo "Hash: " . $row["password"] . "\n";
    
    // Test some passwords
    $passwords = ['password', 'staff123', 'admin123', '12345678', 'password123'];
    foreach ($passwords as $p) {
        if (password_verify($p, $row["password"])) {
            echo "VERIFIED: Password is [$p]\n";
        }
    }
} else {
    echo "No staff account found for $email.\n";
}
?>
