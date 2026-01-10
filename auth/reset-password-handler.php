<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$token = sanitize_input($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate inputs
if (empty($token) || empty($password) || empty($confirm_password)) {
    redirect('reset-password.php?token=' . $token . '&error=required');
}

if ($password !== $confirm_password) {
    redirect('reset-password.php?token=' . $token . '&error=mismatch');
}

if (strlen($password) < 6) {
    redirect('reset-password.php?token=' . $token . '&error=short');
}

// Verify token is still valid
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('reset-password.php?token=' . $token . '&error=invalid');
}

$user = $result->fetch_assoc();

// Hash new password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Update password and clear reset token
$stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
$stmt->bind_param("si", $hashed_password, $user['id']);
$stmt->execute();

// Redirect to login with success message
redirect('login.php?success=password_reset');
?>
