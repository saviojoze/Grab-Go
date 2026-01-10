<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('forgot-password.php');
}

$email = sanitize_input($_POST['email'] ?? '');

if (empty($email)) {
    redirect('forgot-password.php?error=required');
}

// Check if user exists
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('forgot-password.php?error=not_found');
}

$user = $result->fetch_assoc();

// Generate reset token
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Store token in database
$stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
$stmt->bind_param("ssi", $token, $expires, $user['id']);
$stmt->execute();

// Create reset link
$reset_link = "http://localhost/Mini%20Project/auth/reset-password.php?token=" . $token;

// Email content
$subject = "Password Reset Request - Grab & Go";
$message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #00D563; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; padding: 12px 30px; background: #00D563; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Password Reset Request</h1>
        </div>
        <div class='content'>
            <p>Hello " . htmlspecialchars($user['full_name']) . ",</p>
            <p>We received a request to reset your password for your Grab & Go account.</p>
            <p>Click the button below to reset your password:</p>
            <p style='text-align: center;'>
                <a href='" . $reset_link . "' class='button'>Reset Password</a>
            </p>
            <p>Or copy and paste this link into your browser:</p>
            <p style='word-break: break-all; background: white; padding: 10px; border-radius: 5px;'>" . $reset_link . "</p>
            <p><strong>This link will expire in 1 hour.</strong></p>
            <p>If you didn't request a password reset, you can safely ignore this email.</p>
        </div>
        <div class='footer'>
            <p>© 2025 Grab & Go. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
";

// Try to send email using SMTP
$success = false;

// Method 1: Try using stream socket (works on XAMPP)
try {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    
    // For development: Just save to file and show link
    // In production, use proper SMTP
    if (mail($email, $subject, $message, $headers)) {
        $success = true;
    }
} catch (Exception $e) {
    // Email failed
}

// For development: Always show success and display the link
// Remove this in production!
if (!$success) {
    // Store the reset link in session for development
    $_SESSION['dev_reset_link'] = $reset_link;
    $_SESSION['dev_reset_email'] = $email;
    redirect('forgot-password.php?success=dev&token=' . $token);
} else {
    redirect('forgot-password.php?success=1');
}
?>
