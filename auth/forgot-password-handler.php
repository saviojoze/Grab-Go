<?php
require_once __DIR__ . '/../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('forgot-password.php');
}

// Use raw email for Firebase API — do NOT sanitize with htmlspecialchars
$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('forgot-password.php?error=required');
}

// Check if user exists in our DB
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('forgot-password.php?error=not_found');
}

$user = $result->fetch_assoc();

// Ensure columns exist (automatic migration)
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) DEFAULT NULL");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token_expires DATETIME DEFAULT NULL");

// ── Method 1: Firebase REST API (no SMTP needed) ──────────────────────
$firebase_api_key = FIREBASE_API_KEY;
$firebase_url = "https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=" . $firebase_api_key;

$post_data = json_encode([
    'requestType' => 'PASSWORD_RESET',
    'email'       => $email
]);

$ch = curl_init($firebase_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for common XAMPP SSL issues
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response      = curl_exec($ch);
$http_code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error    = curl_error($ch);
curl_close($ch);

// Log full response for debugging
$log_msg = date('[Y-m-d H:i:s] ') . "Reset for $email: HTTP $http_code | Resp: " . ($response ?: 'EMPTY') . "\n";
file_put_contents('auth_debug.log', $log_msg, FILE_APPEND);

if ($curl_error) {
    $firebase_error = 'cURL error: ' . $curl_error;
} elseif ($http_code === 200) {
    $firebase_success = true;
} else {
    $resp_data      = json_decode($response, true);
    $firebase_error = $resp_data['error']['message'] ?? 'Unknown Firebase error (HTTP ' . $http_code . ')';
}

// ── Method 2: PHPMailer fallback (only if SMTP is configured) ─────────
$smtp_success = false;
$smtp_configured = (
    defined('SMTP_PASSWORD') &&
    SMTP_PASSWORD !== 'YOUR_APP_PASSWORD_HERE' &&
    !empty(SMTP_PASSWORD)
);

if (!$firebase_success && $smtp_configured) {

    require_once __DIR__ . '/../phpmailserver/PHPMailer-master/PHPMailer-master/src/Exception.php';
    require_once __DIR__ . '/../phpmailserver/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
    require_once __DIR__ . '/../phpmailserver/PHPMailer-master/PHPMailer-master/src/SMTP.php';

    // Generate DB token for custom reset page
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $stmt2   = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
    $stmt2->bind_param("ssi", $token, $expires, $user['id']);
    $stmt2->execute();
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $reset_link = "$protocol://$host/auth/reset-password.php?token=" . $token;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) SMTP_PORT;
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $user['full_name']);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - Grab & Go';
        $mail->Body    = '<p>Hi ' . htmlspecialchars($user['full_name']) . ',</p><p><a href="' . $reset_link . '">Click here to reset your password</a> (link expires in 1 hour).</p>';
        $mail->AltBody = "Reset your password: " . $reset_link;
        $mail->send();
        $smtp_success = true;
    } catch (Exception $e) {
        $log_dir = __DIR__ . '/../logs/';
        if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
        file_put_contents($log_dir . 'mail_errors.log',
            date('[Y-m-d H:i:s] ') . "SMTP Error: " . $mail->ErrorInfo . "\n", FILE_APPEND);
    }
}

// ALWAYS generate a DB token as a fallback, so the direct reset link works if email fails
$token_local   = bin2hex(random_bytes(32));
$expires_local = date('Y-m-d H:i:s', strtotime('+1 hour'));
$stmt_local = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
$stmt_local->bind_param("ssi", $token_local, $expires_local, $user['id']);
$stmt_local->execute();

// ── Redirect based on result ──────────────────────────────────────────
if ($firebase_success || $smtp_success) {
    redirect('forgot-password.php?success=sent&email=' . urlencode($email) . '&token=' . $token_local);
} else {
    // Dev fallback — show reset link on screen
    redirect('forgot-password.php?success=dev&token=' . $token_local . '&email=' . urlencode($email));
}
?>
