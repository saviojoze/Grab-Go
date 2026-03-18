<?php
// Simulate mobile_login.php
function get_json_input() {
    return ['email' => 'testcustomer@gmail.com', 'password' => 'password123'];
}
function send_success($data, $message) {
    echo "SUCCESS: $message\n";
    print_r($data);
    exit;
}
function send_error($message, $code = 400) {
    echo "ERROR: $message ($code)\n";
    exit;
}

require 'config.php';
// Logic from mobile_login.php
$input = get_json_input();
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

$stmt = $conn->prepare("SELECT id, password, full_name, email, role, is_blocked, phone, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if ($user['is_blocked']) send_error('Blocked', 403);
    
    if (password_verify($password, $user['password'])) {
        unset($user['password']);
        send_success($user, 'Login successful');
    } else {
        echo "Password verify failed locally\n";
        // Fallback simulate
        echo "Trying Firebase fallback...\n";
        send_error('Invalid email or password');
    }
} else {
    send_error('User not found');
}
?>
