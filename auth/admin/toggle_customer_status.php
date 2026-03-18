<?php
require_once 'admin_middleware.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;
$status = isset($input['status']) ? $input['status'] : ''; // 'block' or 'unblock'

if (!$user_id || !in_array($status, ['block', 'unblock'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Don't allow blocking admins or staff from this endpoint just to be safe, though this file is in admin/
// The customers page only lists customers, but good to double check
$check_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$user = $check_result->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

if ($user['role'] !== 'customer') {
    echo json_encode(['success' => false, 'message' => 'Can only manage customer status']);
    exit;
}

$is_blocked = ($status === 'block') ? 1 : 0;

$stmt = $conn->prepare("UPDATE users SET is_blocked = ? WHERE id = ?");
$stmt->bind_param("ii", $is_blocked, $user_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'User ' . ($status === 'block' ? 'blocked' : 'unblocked') . ' successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
