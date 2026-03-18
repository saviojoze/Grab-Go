<?php
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h2>🧪 Testing Leave System Logic</h2>";

// 1. Find a test staff member
$res = $conn->query("SELECT id, full_name, user_id FROM staff_directory LIMIT 1");
if ($res->num_rows == 0) die("No staff found to test with.");
$staff = $res->fetch_assoc();

echo "<p>👤 Testing with Staff: <strong>{$staff['full_name']}</strong> (ID: {$staff['id']})</p>";

// 2. Simulate Applying for Leave
$start = date('Y-m-d', strtotime('+1 day'));
$end = date('Y-m-d', strtotime('+3 days'));
$reason = "Automated Test Leave Request";

$stmt = $conn->prepare("INSERT INTO leave_requests (staff_id, start_date, end_date, leave_type, reason, status) VALUES (?, ?, ?, 'Test', ?, 'Pending')");
$stmt->bind_param("isss", $staff['id'], $start, $end, $reason);

if ($stmt->execute()) {
    $request_id = $conn->insert_id;
    echo "<p style='color:green'>✅ Step 1: Leave Request Created (ID: $request_id)</p>";
} else {
    die("<p style='color:red'>❌ Step 1 Failed: " . $stmt->error . "</p>");
}

// 3. Simulate Admin Approving
$admin_remark = "Approved by Test Script";
$stmt_app = $conn->prepare("UPDATE leave_requests SET status = 'Approved', admin_remarks = ? WHERE id = ?");
$stmt_app->bind_param("si", $admin_remark, $request_id);

if ($stmt_app->execute()) {
    echo "<p style='color:green'>✅ Step 2: Admin Approved Request</p>";
} else {
    die("<p style='color:red'>❌ Step 2 Failed: " . $stmt_app->error . "</p>");
}

// 4. Verify Final State
$check = $conn->query("SELECT * FROM leave_requests WHERE id = $request_id");
$final = $check->fetch_assoc();

if ($final['status'] == 'Approved' && $final['admin_remarks'] == $admin_remark) {
    echo "<p style='color:green'>✅ Step 3: Verification Successful!</p>";
    echo "<div style='background:#e6fffa; padding:10px; border:1px solid #05cd99; display:inline-block;'>";
    echo "Status: <strong>{$final['status']}</strong><br>";
    echo "Remarks: {$final['admin_remarks']}";
    echo "</div>";
    
    // Cleanup (Optional - keep it so user can see it in history)
    // $conn->query("DELETE FROM leave_requests WHERE id = $request_id");
} else {
    echo "<p style='color:red'>❌ Step 3 Failed: State mismatch.</p>";
    print_r($final);
}

echo "<br><br><a href='admin/manage_leaves.php'>Go to Admin Panel to see this request</a>";
?>
