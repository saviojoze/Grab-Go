<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$admin_id = $_GET['admin_id'] ?? null;
if (!$admin_id) send_error('Admin ID required', 401);

$role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$role_stmt->bind_param("i", $admin_id);
$role_stmt->execute();
$role = $role_stmt->get_result()->fetch_assoc()['role'] ?? '';

if ($role !== 'admin') {
    send_error('Only admins can view system logs', 403);
}

// Generate some mock logs for demonstration
$mock_logs = [
    ['id' => 1, 'level' => 'INFO', 'message' => 'System started successfully', 'timestamp' => date('Y-m-d H:i:s', strtotime('-5 minutes'))],
    ['id' => 2, 'level' => 'WARNING', 'message' => 'High memory usage detected', 'timestamp' => date('Y-m-d H:i:s', strtotime('-15 minutes'))],
    ['id' => 3, 'level' => 'INFO', 'message' => 'New user registered', 'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
    ['id' => 4, 'level' => 'ERROR', 'message' => 'Failed payment attempt (User ID: 42)', 'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
    ['id' => 5, 'level' => 'INFO', 'message' => 'Daily system backup completed', 'timestamp' => date('Y-m-d H:i:s', strtotime('-4 hours'))],
];

send_success($mock_logs);
?>
