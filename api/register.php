<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$input = get_json_input();

$full_name = trim($input['full_name'] ?? '');
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';
$phone = trim($input['phone'] ?? '');

if (empty($full_name) || empty($email) || empty($password)) {
    send_error('Full name, email and password are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_error('Invalid email format');
}

if (strlen($password) < 6) {
    send_error('Password must be at least 6 characters');
}

// Check if user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    send_error('Email is already registered');
}

// Create user
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, 'customer')");
$stmt->bind_param("ssss", $full_name, $email, $hashed_password, $phone);

if (!$conn) {
    send_error('Server Database Connection Error');
}

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    
    $new_user = [
        'id' => $user_id,
        'full_name' => $full_name,
        'email' => $email,
        'role' => 'customer',
        'phone' => $phone,
        'profile_picture' => null,
        'is_blocked' => 0
    ];
    
    send_success($new_user, 'Account created successfully');
} else {
    send_error('Registration failed: ' . $conn->error);
}
