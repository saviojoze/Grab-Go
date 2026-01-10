<?php
/**
 * Database Setup Checker
 * This page helps diagnose database issues
 */

// Database credentials
require_once __DIR__ . '/includes/EnvLoader.php';
EnvLoader::load(__DIR__ . '/.env');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?? '');
define('DB_NAME', getenv('DB_NAME') ?: 'grab_and_go');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Setup Checker - Grab & Go</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; padding: 10px; background: #fff3cd; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0; }
        h1 { color: #00D563; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #00D563; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>🔍 Grab & Go - Database Setup Checker</h1>";

// Step 1: Check MySQL connection
echo "<div class='step'><h2>Step 1: MySQL Connection</h2>";
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    echo "<div class='error'>❌ <strong>MySQL Connection Failed!</strong><br>";
    echo "Error: " . $conn->connect_error . "</div>";
    echo "<div class='info'><strong>Solution:</strong><br>";
    echo "1. Open XAMPP Control Panel<br>";
    echo "2. Click <strong>Start</strong> next to MySQL<br>";
    echo "3. Wait for it to show 'Running' status<br>";
    echo "4. Refresh this page</div>";
    echo "</div></body></html>";
    exit;
} else {
    echo "<div class='success'>✅ MySQL connection successful!</div>";
}
echo "</div>";

// Step 2: Check if database exists
echo "<div class='step'><h2>Step 2: Database Existence</h2>";
$db_check = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
if ($db_check->num_rows == 0) {
    echo "<div class='error'>❌ <strong>Database '" . DB_NAME . "' does not exist!</strong></div>";
    echo "<div class='info'><strong>Solution:</strong><br>";
    echo "1. Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a><br>";
    echo "2. Click <strong>New</strong> in the left sidebar<br>";
    echo "3. Database name: <code>grab_and_go</code><br>";
    echo "4. Click <strong>Create</strong><br>";
    echo "5. Then import the schema (see Step 3 below)</div>";
    echo "</div></body></html>";
    exit;
} else {
    echo "<div class='success'>✅ Database 'grab_and_go' exists!</div>";
}
echo "</div>";

// Step 3: Check if tables exist
echo "<div class='step'><h2>Step 3: Database Tables</h2>";
$conn->select_db(DB_NAME);
$tables = ['users', 'categories', 'products', 'cart', 'orders', 'order_items'];
$missing_tables = [];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    echo "<div class='error'>❌ <strong>Missing tables:</strong> " . implode(', ', $missing_tables) . "</div>";
    echo "<div class='info'><strong>Solution:</strong><br>";
    echo "1. Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a><br>";
    echo "2. Click on <code>grab_and_go</code> database in left sidebar<br>";
    echo "3. Click <strong>Import</strong> tab at the top<br>";
    echo "4. Click <strong>Choose File</strong><br>";
    echo "5. Navigate to: <code>C:\\xampp\\htdocs\\Mini Project\\database\\schema.sql</code><br>";
    echo "6. Click <strong>Go</strong> at the bottom<br>";
    echo "7. Wait for success message<br>";
    echo "8. Refresh this page</div>";
} else {
    echo "<div class='success'>✅ All required tables exist!</div>";
    
    // Check if there's data
    $user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    $product_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
    
    echo "<div class='info'><strong>Database Stats:</strong><br>";
    echo "Users: $user_count<br>";
    echo "Products: $product_count</div>";
    
    if ($product_count == 0) {
        echo "<div class='warning'>⚠️ <strong>No products in database!</strong><br>";
        echo "The database exists but has no products. You need to import the schema.sql file.</div>";
    }
}
echo "</div>";

// Step 4: Final status
echo "<div class='step'><h2>Step 4: Final Status</h2>";
if (empty($missing_tables) && $product_count > 0) {
    echo "<div class='success'><strong>🎉 Everything is set up correctly!</strong><br>";
    echo "Your database is ready to use.<br><br>";
    echo "<a href='/Mini%20Project/products/listing.php' style='background: #00D563; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Products Page →</a>";
    echo "</div>";
} else {
    echo "<div class='warning'><strong>⚠️ Setup incomplete</strong><br>";
    echo "Please follow the solutions above to complete the setup.</div>";
}
echo "</div>";

echo "</body></html>";
?>
