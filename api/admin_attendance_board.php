<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$admin_id = $_GET['admin_id'] ?? null;
if (!$admin_id) send_error('Admin ID required', 401);

// Identify user role
$role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$role_stmt->bind_param("i", $admin_id);
$role_stmt->execute();
$role = $role_stmt->get_result()->fetch_assoc()['role'] ?? '';

if ($role !== 'admin' && $role !== 'staff') {
    send_error('Unauthorized', 401);
}

if ($method === 'GET') {
    $date = $_GET['date'] ?? date('Y-m-d');
    
    // Fetch all active staff
    $staff_list = [];
    $res = $conn->query("SELECT id, full_name, position FROM staff_directory WHERE is_active = 1 ORDER BY full_name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $staff_list[$row['id']] = $row;
        }
    }
    
    // Fetch existing attendance for the date
    $attendance_map = [];
    $att_query = $conn->prepare("SELECT * FROM staff_attendance WHERE date = ?");
    if ($att_query) {
        $att_query->bind_param("s", $date);
        $att_query->execute();
        $res_att = $att_query->get_result();
        while ($row = $res_att->fetch_assoc()) {
            if (!empty($row['employee_id'])) {
                $attendance_map[$row['employee_id']] = $row;
            }
        }
    }
    
    // Format response
    $merged = [];
    foreach ($staff_list as $eid => $staff) {
        $rec = $attendance_map[$eid] ?? null;
        $status = $rec['status'] ?? 'Absent';
        $check_in = $rec['check_in_time'] ?? '';
        if (!$check_in && $status === 'Present') $check_in = '09:00';
        $check_out = $rec['check_out_time'] ?? '';
        
        $merged[] = [
            'employee_id' => $eid,
            'staff_name' => $staff['full_name'],
            'position' => $staff['position'] ?? '',
            'status' => $status,
            'check_in_time' => $check_in,
            'check_out_time' => $check_out
        ];
    }
    
    send_success(['date' => $date, 'attendance' => $merged]);
} elseif ($method === 'POST') {
    $input = get_json_input();
    $date = $input['date'] ?? null;
    $attendance_data = $input['attendance'] ?? [];
    
    if (!$date) send_error('Date required');
    
    $updates = 0;
    
    foreach ($attendance_data as $data) {
        $emp_id = intval($data['employee_id']);
        $status = trim($data['status']);
        $check_in = !empty($data['check_in_time']) ? trim($data['check_in_time']) : null;
        $check_out = !empty($data['check_out_time']) ? trim($data['check_out_time']) : null;
        $staff_name = trim($data['staff_name']);
        
        try {
            $stmt = $conn->prepare("INSERT INTO staff_attendance (employee_id, staff_name, date, status, check_in_time, check_out_time) 
                                VALUES (?, ?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE 
                                status = VALUES(status), 
                                check_in_time = VALUES(check_in_time), 
                                check_out_time = VALUES(check_out_time),
                                staff_name = VALUES(staff_name)");
            if ($stmt) {
                $stmt->bind_param("isssss", $emp_id, $staff_name, $date, $status, $check_in, $check_out);
                if ($stmt->execute()) {
                    $updates++;
                }
            }
        } catch (Exception $e) {
            error_log("App Attendance Update Error: " . $e->getMessage());
        }
    }
    
    send_success(null, "Attendance updated for $updates employees");
} else {
    send_error('Method not allowed', 405);
}
?>
