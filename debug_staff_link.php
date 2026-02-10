<?php
require_once 'config.php';

// Force start session if not already
if (session_status() === PHP_SESSION_NONE) session_start();

echo "<h2>🕵️ Staff Linkage Debugger</h2>";

if (!isset($_SESSION['user_id'])) {
    die("❌ Not logged in.");
}

$user_id = $_SESSION['user_id'];
echo "<p><strong>Session User ID:</strong> $user_id</p>";
echo "<p><strong>Session Name:</strong> " . ($_SESSION['full_name'] ?? 'Not Set') . "</p>";
echo "<p><strong>Session Role:</strong> " . ($_SESSION['role'] ?? 'Not Set') . "</p>";

// 1. Check User in DB
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $res->fetch_assoc();

echo "<h3>Users Table Data:</h3>";
echo "<pre>" . print_r($user, true) . "</pre>";

// 2. Check Staff Directory Link
echo "<h3>Staff Directory Lookup (by user_id):</h3>";
$res_link = $conn->query("SELECT * FROM staff_directory WHERE user_id = $user_id");
if ($res_link->num_rows > 0) {
    echo "<p style='color:green'>✅ Found linked staff profile:</p>";
    echo "<pre>" . print_r($res_link->fetch_assoc(), true) . "</pre>";
} else {
    echo "<p style='color:red'>❌ No staff profile found linked to User ID $user_id.</p>";
}

// 3. Check Staff Directory Lookup (by name)
$name = $conn->real_escape_string($user['full_name']);
echo "<h3>Staff Directory Lookup (by name '{$user['full_name']}'):</h3>";
$res_name = $conn->query("SELECT * FROM staff_directory WHERE full_name = '$name'");
if ($res_name->num_rows > 0) {
    $staff_match = $res_name->fetch_assoc();
    echo "<p style='color:orange'>⚠️ Found profile by name, but it has user_id: " . ($staff_match['user_id'] ?? 'NULL') . "</p>";
    echo "<pre>" . print_r($staff_match, true) . "</pre>";
    
    // Auto-fix option
    echo "<form method='POST'>
        <input type='hidden' name='fix_link' value='{$staff_match['id']}'>
        <button type='submit' style='background: #05CD99; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;'>🔗 Fix Link Now</button>
    </form>";
} else {
    echo "<p style='color:red'>❌ No staff profile found by name either.</p>";
    
    // Auto-create option
    echo "<form method='POST'>
        <input type='hidden' name='create_profile' value='1'>
        <button type='submit' style='background: #3B82F6; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;'>➕ Create Staff Profile</button>
    </form>";
}

// Handle Fix Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fix_link'])) {
        $staff_id = (int)$_POST['fix_link'];
        $conn->query("UPDATE staff_directory SET user_id = $user_id WHERE id = $staff_id");
        echo "<p style='color:green'>✅ Fixed! Refresh the page.</p>";
    }
    if (isset($_POST['create_profile'])) {
        $stmt = $conn->prepare("INSERT INTO staff_directory (full_name, user_id, position) VALUES (?, ?, 'Staff')");
        $stmt->bind_param("si", $user['full_name'], $user_id);
        if ($stmt->execute()) {
            echo "<p style='color:green'>✅ Created Profile! Refresh the page.</p>";
        } else {
            echo "<p style='color:red'>Error: " . $stmt->error . "</p>";
        }
    }
}
?>
