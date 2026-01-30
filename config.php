<?php
/**
 * Database Configuration
 * Grab & Go - Smart Supermarket System
 */

// Load environment variables
require_once __DIR__ . '/includes/EnvLoader.php';
EnvLoader::load(__DIR__ . '/.env');

// Database credentials
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?? '');
define('DB_NAME', getenv('DB_NAME') ?: 'grab_and_go');

// Google OAuth Configuration
// TO GET YOUR CLIENT ID:
// 1. Go to https://console.cloud.google.com/
// 2. Create a new project or select existing
// 3. Enable Google+ API
// 4. Go to Credentials > Create Credentials > OAuth 2.0 Client ID
// 5. Set Authorized JavaScript origins: http://localhost
// 6. Set Authorized redirect URIs: http://localhost/Mini%20Project/auth/google-callback.php
// 7. Copy the Client ID below
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET'));
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI'));

// Email Configuration (for password reset)
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME'));
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL'));
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Grab & Go');
// Dynamic Base URL Calculation
$script_dir = str_replace('\\', '/', __DIR__);
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$base_url = str_replace($doc_root, '', $script_dir);
define('BASE_URL', rtrim($base_url, '/') . '/');

// Firebase Configuration
define('FIREBASE_API_KEY', getenv('FIREBASE_API_KEY'));
define('FIREBASE_AUTH_DOMAIN', getenv('FIREBASE_AUTH_DOMAIN'));
define('FIREBASE_PROJECT_ID', getenv('FIREBASE_PROJECT_ID'));
define('FIREBASE_STORAGE_BUCKET', getenv('FIREBASE_STORAGE_BUCKET'));
define('FIREBASE_MESSAGING_SENDER_ID', getenv('FIREBASE_MESSAGING_SENDER_ID'));
define('FIREBASE_APP_ID', getenv('FIREBASE_APP_ID'));

// Razorpay Configuration
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID'));
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET'));



// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (Exception $e) {
    // Check for specific error codes if needed, but for now catch all
    die("Connection failed: " . $e->getMessage() . " (Suggestion: Check if your MySQL server is running in XAMPP)");
}

// Check connection (keep this for fallback if exceptions aren't thrown)
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Helper function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper function to check if user is staff
function is_staff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}

// Helper function to get current user ID
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

?>
