<?php
/**
 * Database Migration - Add Password Reset Fields
 * Run this file once to add reset token columns
 */

require_once __DIR__ . '/../config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Migration - Grab & Go</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 15px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 15px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        h1 { color: #00D563; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔧 Database Migration</h1>
    <p>Adding password reset fields to users table...</p>";

// Check if columns already exist
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'reset_token'");
if ($check->num_rows > 0) {
    echo "<div class='success'>✅ <strong>Columns already exist!</strong><br>No migration needed.</div>";
} else {
    // Add reset_token column
    $sql1 = "ALTER TABLE users ADD COLUMN reset_token VARCHAR(100) NULL AFTER password";
    if ($conn->query($sql1)) {
        echo "<div class='success'>✅ Added <code>reset_token</code> column</div>";
    } else {
        echo "<div class='error'>❌ Error adding reset_token: " . $conn->error . "</div>";
    }
    
    // Add reset_token_expires column
    $sql2 = "ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL AFTER reset_token";
    if ($conn->query($sql2)) {
        echo "<div class='success'>✅ Added <code>reset_token_expires</code> column</div>";
    } else {
        echo "<div class='error'>❌ Error adding reset_token_expires: " . $conn->error . "</div>";
    }
    
    echo "<div class='success'><strong>🎉 Migration Complete!</strong><br>
    Password reset feature is now ready to use.<br><br>
    <a href='../auth/login.php' style='background: #00D563; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login →</a>
    </div>";
}

echo "<p style='margin-top: 30px; color: #666;'><small>You can delete this file after running it once.</small></p>";
echo "</body></html>";
?>
