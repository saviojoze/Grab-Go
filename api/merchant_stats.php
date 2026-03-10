<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    send_error('User ID is required');
}

// Verify if user is staff or admin
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || ($user['role'] !== 'staff' && $user['role'] !== 'admin')) {
    send_error('Unauthorized. Staff only.');
}

// 1. Order Statistics
$stats = [];
$stats['total_orders'] = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$stats['pending_orders'] = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")->fetch_assoc()['c'];
$stats['ready_orders'] = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'ready'")->fetch_assoc()['c'];
$stats['completed_orders'] = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'completed'")->fetch_assoc()['c'];

// 2. Today's Statistics
$stats['today_orders'] = $conn->query("SELECT COUNT(*) as c FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
$stats['today_revenue'] = $conn->query("SELECT COALESCE(SUM(total),0) as r FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")->fetch_assoc()['r'];

// 3. Recent Orders (last 5)
$recent_orders = $conn->query(
    "SELECT o.*, u.full_name as customer_name
       FROM orders o
       LEFT JOIN users u ON o.user_id = u.id
      ORDER BY o.created_at DESC
      LIMIT 5"
);

$orders_data = [];
while ($row = $recent_orders->fetch_assoc()) {
    $orders_data[] = $row;
}

$stats['recent_orders'] = $orders_data;

send_success($stats);
?>
