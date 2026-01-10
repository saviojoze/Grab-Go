<?php
require_once __DIR__ . '/../config.php';

// Check if we have an authorization code
if (!isset($_GET['code'])) {
    redirect('login.php?error=oauth_failed');
}

$auth_code = $_GET['code'];

// Exchange authorization code for access token
$token_url = 'https://oauth2.googleapis.com/token';
$token_data = [
    'code' => $auth_code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$token_response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($token_response, true);

if (!isset($token_data['access_token'])) {
    redirect('login.php?error=oauth_failed');
}

$access_token = $token_data['access_token'];

// Get user info from Google
$userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userinfo_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userinfo_response = curl_exec($ch);
curl_close($ch);

$user_data = json_decode($userinfo_response, true);

if (!isset($user_data['email'])) {
    redirect('login.php?error=oauth_failed');
}

// Extract user information
$email = $user_data['email'];
$full_name = $user_data['name'] ?? '';
$google_id = $user_data['id'];

// Check if user exists
$stmt = $conn->prepare("SELECT id, full_name, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists, log them in
    $user = $result->fetch_assoc();
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $user['role'];
    
    // Redirect based on role
    if ($user['role'] === 'admin') {
        redirect('../admin/dashboard.php');
    } else if ($user['role'] === 'staff') {
        redirect('../staff/orders.php');
    } else {
        redirect('../products/listing.php');
    }
} else {
    // Create new user account
    // Generate a random password (won't be used for Google login)
    $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'customer')");
    $stmt->bind_param("sss", $full_name, $email, $random_password);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Log the user in
        $_SESSION['user_id'] = $user_id;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'customer';
        
        redirect('../products/listing.php');
    } else {
        redirect('login.php?error=registration_failed');
    }
}
?>
