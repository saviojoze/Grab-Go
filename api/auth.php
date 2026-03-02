<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

// Get JSON input
$input = get_json_input();

$idToken = $input['idToken'] ?? '';
$email = $input['email'] ?? '';
$displayName = $input['displayName'] ?? '';
$photoURL = $input['photoURL'] ?? '';
$uid = $input['uid'] ?? '';

if (empty($email) || empty($uid)) {
    send_error('Invalid request. Email and UID are required.');
}

// Check if user exists
$stmt = $conn->prepare("SELECT id, full_name, email, role, is_blocked, phone, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists
    $user = $result->fetch_assoc();

    if ($user['is_blocked']) {
        send_error('Your account has been suspended. Please contact support.', 403);
    }

    // Update profile picture if provided and different
    if (!empty($photoURL) && $photoURL != $user['profile_picture']) {
        $update_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $update_stmt->bind_param("si", $photoURL, $user['id']);
        $update_stmt->execute();
        $user['profile_picture'] = $photoURL;
    }

    send_success($user, 'Login successful');
} else {
    // New user - create account
    // For mobile, we might not have a password, so create a random one
    $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, profile_picture) VALUES (?, ?, ?, 'customer', ?)");
    $stmt->bind_param("ssss", $displayName, $email, $random_password, $photoURL);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        $new_user = [
            'id' => $user_id,
            'full_name' => $displayName,
            'email' => $email,
            'role' => 'customer',
            'is_blocked' => 0,
            'phone' => null,
            'profile_picture' => $photoURL
        ];
        
        send_success($new_user, 'Account created successfully');
    } else {
        send_error('Failed to create account in database.');
    }
}
?>
