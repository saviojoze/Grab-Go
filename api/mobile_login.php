<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

// Get JSON input
$input = get_json_input();

if (!$input) {
    // If JSON parsing fails, maybe it was sent as form data
    $input = $_POST;
}

$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

// DEBUG LOGGING
$log = "[" . date('Y-m-d H:i:s') . "] Login attempt: $email\n";
file_put_contents(__DIR__ . '/login_debug.log', $log, FILE_APPEND);

if (empty($email) || empty($password)) {
    file_put_contents(__DIR__ . '/login_debug.log', "ERROR: Missing fields\n", FILE_APPEND);
    send_error('Email and password are required');
}

// Check user credentials
$stmt = $conn->prepare("SELECT id, password, full_name, email, role, is_blocked, phone, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
file_put_contents(__DIR__ . '/login_debug.log', "Result count: " . $result->num_rows . "\n", FILE_APPEND);

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    file_put_contents(__DIR__ . '/login_debug.log', "User found: " . $user['id'] . " | Role: " . $user['role'] . "\n", FILE_APPEND);
    
    if ($user['is_blocked']) {
        send_error('Your account has been suspended. Please contact support.', 403);
    }
    
    $login_success = false;

    // 1. Try local password verification (standard for staff/manual accounts)
    if (password_verify($password, $user['password'])) {
        $login_success = true;
    } 
    // 2. Fallback: Try Firebase REST API (if local verification fails, maybe it's a web-registered user)
    else {
        $firebase_api_key = getenv('FIREBASE_API_KEY') ?: 'AIzaSyAxNsQWe2L5bhMpr5VfEJhctgciZE7ARso'; // Fallback if getenv fails
        $url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=" . $firebase_api_key;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $res_data = json_decode($response, true);
        curl_close($ch);

        if (isset($res_data['idToken'])) {
            $login_success = true;
            // Optionally update the local hash if it was a random one
            if (strlen($user['password']) < 20 || !password_get_info($user['password'])['algo']) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->bind_param("si", $new_hash, $user['id']);
                $upd->execute();
            }
        }
    }

    if ($login_success) {
        // Remove password from response
        unset($user['password']);
        file_put_contents(__DIR__ . '/login_debug.log', "Login Success\n", FILE_APPEND);
        send_success($user, 'Login successful');
    } else {
        file_put_contents(__DIR__ . '/login_debug.log', "Login Failed: Invalid password\n", FILE_APPEND);
        send_error('Invalid email or password');
    }
} else {
    file_put_contents(__DIR__ . '/login_debug.log', "Login Failed: User not found or multiple found\n", FILE_APPEND);
    send_error('Invalid email or password');
}
?>
