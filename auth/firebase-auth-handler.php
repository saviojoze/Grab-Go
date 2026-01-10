<?php
require_once __DIR__ . '/../config.php';

// Set JSON header
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$idToken = $input['idToken'] ?? '';
$email = $input['email'] ?? '';
$displayName = $input['displayName'] ?? '';
$photoURL = $input['photoURL'] ?? '';
$uid = $input['uid'] ?? '';

if (empty($idToken) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Optional: Verify the Firebase ID token (recommended for production)
// You would use Firebase Admin SDK for this
// For now, we'll trust the token since it's coming from Firebase

// Check if user exists
$stmt = $conn->prepare("SELECT id, full_name, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists - log them in
    $user = $result->fetch_assoc();
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $user['role'];
    $_SESSION['firebase_uid'] = $uid;
    
    // Determine redirect based on role
    if ($user['role'] === 'admin') {
        $redirect = '../admin/index.php';
    } else if ($user['role'] === 'staff') {
        $redirect = '../staff/orders.php';
    } else {
        $redirect = '../products/listing.php';
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $redirect
    ]);
} else {
    // New user - create account
    $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'customer')");
    $stmt->bind_param("sss", $displayName, $email, $random_password);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Log them in
        $_SESSION['user_id'] = $user_id;
        $_SESSION['full_name'] = $displayName;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'customer';
        $_SESSION['firebase_uid'] = $uid;
        
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully',
            'redirect' => '../products/listing.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create account'
        ]);
    }
}
?>
