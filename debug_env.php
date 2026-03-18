<?php
require_once 'config.php';

echo "<h1>🔍 Grab & Go Production Debug</h1>";
echo "<pre>";

// Basic check
echo "PROTOCOL detected: " . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? "https" : "http") . "\n";
echo "HOST detected: " . $_SERVER['HTTP_HOST'] . "\n\n";

// Google credentials
echo "GOOGLE_CLIENT_ID: [" . (defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : 'NOT_DEFINED') . "]\n";
echo "GOOGLE_CLIENT_SECRET: [" . (defined('GOOGLE_CLIENT_SECRET') && GOOGLE_CLIENT_SECRET ? "****HIDDEN****" : "EMPTY") . "]\n";
echo "GOOGLE_REDIRECT_URI: [" . (defined('GOOGLE_REDIRECT_URI') ? GOOGLE_REDIRECT_URI : 'NOT_DEFINED') . "]\n\n";

// Database
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_PORT: " . DB_PORT . "\n";

// Test DB Connection
$test_conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
if ($test_conn->connect_error) {
    echo "❌ DB Connection FAILED: " . $test_conn->connect_error . "\n";
} else {
    echo "✅ DB Connection SUCCESS!\n";
    $res = $test_conn->query("SELECT COUNT(*) FROM products");
    echo "Products found: " . ($res ? $res->fetch_row()[0] : "ERROR") . "\n";
}

echo "</pre>";
echo "<hr><p><b>Note:</b> If GOOGLE_CLIENT_ID has double quotes (\") around it in the brackets above, remove them from your Railway dashboard.</p>";
?>
