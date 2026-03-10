<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$admin_id = $_GET['admin_id'] ?? ($_POST['admin_id'] ?? null);

if (!$admin_id) {
    send_error('Admin ID is required');
}

// Verify admin role
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if (!$admin || $admin['role'] !== 'admin') {
    send_error('Unauthorized. Admin only.', 403);
}

switch ($method) {
    case 'GET':
        $res = $conn->query("SELECT id, full_name, email, role, phone, is_blocked, created_at FROM users ORDER BY created_at DESC");
        $users = [];
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
        send_success($users);
        break;

    case 'PUT':
        $input = get_json_input();
        $target_user_id = $input['user_id'] ?? null;
        $role = $input['role'] ?? null;
        $is_blocked = isset($input['is_blocked']) ? (int)$input['is_blocked'] : null;
        $full_name = trim($input['full_name'] ?? '');
        $phone = trim($input['phone'] ?? '');

        if (!$target_user_id) {
            send_error('Target user ID is required');
        }

        $updates = [];
        $params = [];
        $types = "";

        if ($role) {
            $updates[] = "role = ?";
            $params[] = $role;
            $types .= "s";
        }
        if ($is_blocked !== null) {
            $updates[] = "is_blocked = ?";
            $params[] = $is_blocked;
            $types .= "i";
        }
        if ($full_name) {
            $updates[] = "full_name = ?";
            $params[] = $full_name;
            $types .= "s";
        }
        if ($phone) {
            $updates[] = "phone = ?";
            $params[] = $phone;
            $types .= "s";
        }

        if (empty($updates)) {
            send_error('No updates provided');
        }

        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $target_user_id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            send_success(null, 'User updated successfully');
        } else {
            send_error('Failed to update user: ' . $conn->error);
        }
        break;

    default:
        send_error('Method not allowed', 405);
        break;
}
?>
