<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$input = get_json_input();

$user_id = $input['user_id'] ?? null;
$full_name = trim($input['full_name'] ?? '');
$phone = trim($input['phone'] ?? '');

if (!$user_id) {
    send_error('User ID is required');
}

// 1. Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    send_error('User not found');
}

// 2. Update user
$stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
$stmt->bind_param("ssi", $full_name, $phone, $user_id);

if ($stmt->execute()) {
    // Return updated user data
    $stmt = $conn->prepare("SELECT id, full_name, email, role, phone, is_blocked, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $updated_user = $stmt->get_result()->fetch_assoc();
    
    send_success($updated_user, 'Profile updated successfully');
} else {
    send_error('Failed to update profile: ' . $conn->error);
}
?>
