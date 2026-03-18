<?php
require_once __DIR__ . '/../config.php';
$stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
$email = 'staff@grabandgo.com';
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if($row = $res->fetch_assoc()) {
    echo "Hash: " . $row['password'] . "\n";
} else {
    echo "User not found\n";
}
?>
