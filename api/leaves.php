<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_GET['user_id'] ?? null;

if (!$user_id && $method === 'GET') {
    send_error('User ID is required');
}

// Get staff_id from staff_directory
$stmt_staff = $conn->prepare("SELECT id FROM staff_directory WHERE user_id = ?");
$stmt_staff->bind_param("i", $user_id);
$stmt_staff->execute();
$staff_res = $stmt_staff->get_result();
$staff_id = ($staff_res->num_rows > 0) ? $staff_res->fetch_assoc()['id'] : null;

if (!$staff_id && $method === 'GET') {
    send_success([], 'No staff record found for this user');
}

switch ($method) {
    case 'GET':
        $query = "SELECT * FROM leave_requests WHERE staff_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $leaves = [];
        while ($row = $result->fetch_assoc()) {
            $leaves[] = $row;
        }
        send_success($leaves);
        break;

    case 'POST':
        $input = get_json_input();
        $u_id = $input['user_id'] ?? null;
        
        // Resolve staff_id again for POST
        $stmt_s = $conn->prepare("SELECT id FROM staff_directory WHERE user_id = ?");
        $stmt_s->bind_param("i", $u_id);
        $stmt_s->execute();
        $s_res = $stmt_s->get_result();
        $sid = ($s_res->num_rows > 0) ? $s_res->fetch_assoc()['id'] : null;

        if (!$sid) {
            send_error('Staff account not properly linked to staff directory. Please contact admin.', 403);
        }

        $start_date = $input['start_date'] ?? null;
        $end_date = $input['end_date'] ?? null;
        $leave_type = $input['leave_type'] ?? 'Sick Leave';
        $reason = $input['reason'] ?? '';

        if (!$start_date || !$end_date || !$reason) {
            send_error('Start date, end date and reason are required');
        }

        $stmt = $conn->prepare("INSERT INTO leave_requests (staff_id, start_date, end_date, leave_type, reason, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("issss", $sid, $start_date, $end_date, $leave_type, $reason);
        
        if ($stmt->execute()) {
            send_success(['id' => $conn->insert_id], 'Leave application submitted');
        } else {
            send_error('Failed to submit leave application');
        }
        break;

    default:
        send_error('Method not allowed', 405);
}
?>
