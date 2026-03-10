<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$admin_id = $_GET['admin_id'] ?? null;
if (!$admin_id) send_error('Admin ID required', 401);

$role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$role_stmt->bind_param("i", $admin_id);
$role_stmt->execute();
$role = $role_stmt->get_result()->fetch_assoc()['role'] ?? '';

if ($role !== 'admin' && $role !== 'staff') {
    send_error('Unauthorized', 401);
}

$query = "SELECT * FROM staff_attendance ORDER BY date DESC, created_at DESC LIMIT 100";
$result = $conn->query($query);
$attendance = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $attendance[] = $row;
    }
}

send_success($attendance);
?>
