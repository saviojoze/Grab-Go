<?php
// Function to simulate get_json_input()
function get_json_input() {
    return ['email' => 'staff@grabandgo.com', 'password' => 'password'];
}

// Function to simulate sending responses
function send_response($success, $message, $data, $code) {
    echo "[$code] " . ($success ? "SUCCESS" : "ERROR") . ": $message" . PHP_EOL;
    if ($data) print_r($data);
    exit;
}
function send_error($m, $c=400) { send_response(false, $m, null, $c); }
function send_success($d, $m='Success') { send_response(true, $m, $d, 200); }

require_once 'config.php';
// Inlining the logic from mobile_login.php for test
$input = get_json_input();
$email = sanitize_input($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    send_error('Email and password are required');
}

$stmt = $conn->prepare("SELECT id, password, full_name, email, role, is_blocked, phone, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        if ($user['is_blocked']) {
            send_error('Your account has been suspended. Please contact support.', 403);
        }
        unset($user['password']);
        send_success($user, 'Login successful');
    } else {
        send_error('Invalid email or password');
    }
} else {
    send_error('Invalid email or password');
}
?>
