<?php
$page_title = 'Attendance Board - Grab & Go';
$current_page = 'attendance';

require_once 'admin_middleware.php';

// Handle Bulk Save
// Placed before header.php to allow for header redirection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all'])) {
    $date = $_POST['date'];
    $updates = 0;

    if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
        foreach ($_POST['attendance'] as $emp_id => $data) {
            $status = sanitize_input($data['status']); // Present, Absent, etc.
            $check_in = !empty($data['check_in']) ? $data['check_in'] : null;
            $check_out = !empty($data['check_out']) ? $data['check_out'] : null;
            $staff_name = sanitize_input($data['staff_name']);
            
            // Using a safer parameter binding approach or at least wrapping in try-catch
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
                    $stmt->execute();
                    $updates++;
                    $stmt->close();
                }
            } catch (Exception $e) {
                // Silently fail or log error, but continue processing others
                error_log("Attendance Update Error: " . $e->getMessage());
            }
        }
    }

    if ($updates > 0) {
        $_SESSION['success'] = "Attendance updated for $updates employees.";
    }
    
    // Redirect to self to prevent form resubmission and ensure page reload
    header("Location: attendance.php?date=" . urlencode($date));
    exit;
}


require_once 'header.php';

// SELF-DIAGNOSIS: Check if staff_directory exists
$table_check = $conn->query("SHOW TABLES LIKE 'staff_directory'");
if (!$table_check || $table_check->num_rows == 0) {
     echo "<div class='alert alert-warning' style='margin: 20px;'>
            <h3>Setup Required</h3>
            <p>To use the new Attendance System, we need to create the Staff Directory.</p>
            <a href='../setup_staff_master.php' class='btn btn-primary'>Run Setup Script</a>
          </div>";
    require_once 'footer.php';
    exit;
}

// Get selected date
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$pretty_date = date('l, F j, Y', strtotime($selected_date));

// Statistics
$stats = ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0];

// 1. Fetch ALL Active Staff
$staff_list = [];
$res = $conn->query("SELECT * FROM staff_directory WHERE is_active = 1 ORDER BY full_name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $staff_list[$row['id']] = $row;
    }
}
$stats['total'] = count($staff_list);

// 2. Fetch Attendance for Date
$attendance_map = [];
$att_query = $conn->prepare("SELECT * FROM staff_attendance WHERE date = ?");
if ($att_query) {
    $att_query->bind_param("s", $selected_date);
    $att_query->execute();
    $res_att = $att_query->get_result();
    while ($row = $res_att->fetch_assoc()) {
        if (!empty($row['employee_id'])) {
            $attendance_map[$row['employee_id']] = $row;
        }
    }
    $att_query->close();
}

// Calculate Stats (Initial PHP Calculation)
foreach ($staff_list as $eid => $staff) {
    $rec = $attendance_map[$eid] ?? null;
    $status = $rec['status'] ?? 'Absent'; 
    $key = strtolower(str_replace(' ', '_', $status));
    if (isset($stats[$key])) $stats[$key]++;
}

?>

<?php require_once 'sidebar.php'; ?>

<style>
    /* Custom Attendance Styles */
    .attendance-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .stat-mini-card {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .stat-mini-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    
    .attendance-table-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    /* Status Pills */
    .status-group {
        display: inline-flex;
        background: #F4F7FE;
        padding: 4px;
        border-radius: 30px;
    }

    .status-option {
        position: relative;
    }

    .status-option input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .status-label {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: block;
        color: var(--color-text-secondary);
    }

    /* Checked States */
    .status-option input[value="Present"]:checked + .status-label {
        background: #fff;
        color: #05CD99;
        box-shadow: 0 2px 6px rgba(5, 205, 153, 0.2);
    }
    
    .status-option input[value="Late"]:checked + .status-label {
        background: #fff;
        color: #FFB547;
        box-shadow: 0 2px 6px rgba(255, 181, 71, 0.2);
    }
    
    .status-option input[value="Absent"]:checked + .status-label {
        background: #fff;
        color: #EE5D50;
        box-shadow: 0 2px 6px rgba(238, 93, 80, 0.2);
    }

    /* Time Inputs */
    .time-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .time-input {
        background: #F4F7FE;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 8px 12px;
        font-family: inherit;
        color: var(--color-text-primary);
        font-weight: 500;
        transition: all 0.2s;
        width: 130px;
        cursor: pointer;
    }
    
    .time-input:focus {
        background: #fff;
        border-color: var(--color-primary);
        outline: none;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .attendance-stats {
            grid-template-columns: 1fr;
        }
    }
    
    .date-nav {
        background: #fff;
        padding: 10px 20px;
        border-radius: 30px;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .nav-btn {
        background: #F4F7FE;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--color-primary);
        transition: all 0.2s;
    }
    
    .nav-btn:hover {
        background: var(--color-primary);
        color: #fff;
    }
</style>

<main class="admin-main">
    <div class="admin-container">
        
        <!-- Header -->
        <div class="page-header">
            <div>
                <h1>Attendance Board</h1>
                <p class="text-secondary">Track daily check-ins and check-outs</p>
            </div>
            <div>
                <a href="attendance_records.php" class="btn btn-secondary" style="display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    History Log
                </a>
            </div>
        </div>
        
        <!-- Stats Overview -->
        <!-- Added IDs for JS updates -->
        <div class="attendance-stats">
            <div class="stat-mini-card">
                <div class="stat-mini-icon" style="background: rgba(5, 205, 153, 0.1); color: #05CD99;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div>
                    <h3 id="stat-present" style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--color-text-primary);"><?php echo $stats['present']; ?></h3>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--color-text-secondary);">Present Today</p>
                </div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-icon" style="background: rgba(255, 181, 71, 0.1); color: #FFB547;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div>
                    <h3 id="stat-late" style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--color-text-primary);"><?php echo $stats['late']; ?></h3>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--color-text-secondary);">Late Arrivals</p>
                </div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-icon" style="background: rgba(238, 93, 80, 0.1); color: #EE5D50;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
                <div>
                    <h3 id="stat-absent" style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--color-text-primary);"><?php echo $stats['absent']; ?></h3>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--color-text-secondary);">Absent</p>
                </div>
            </div>
        </div>

        <!-- Date Filter & Actions -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div class="date-nav">
                <?php 
                    $prev_date = date('Y-m-d', strtotime($selected_date . ' -1 day'));
                    $next_date = date('Y-m-d', strtotime($selected_date . ' +1 day'));
                ?>
                <a href="?date=<?php echo $prev_date; ?>" class="nav-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </a>
                
                <form method="GET" style="display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-text-secondary);"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <input type="date" name="date" value="<?php echo $selected_date; ?>" onchange="this.form.submit()" style="border: none; font-size: 1rem; font-weight: 600; color: var(--color-text-primary); outline: none; background: transparent; font-family: inherit;">
                </form>
                
                <a href="?date=<?php echo $next_date; ?>" class="nav-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="markAllPresent()" style="background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: none;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Mark All Present
                </button>
                
                <button type="button" onclick="document.getElementById('attendanceForm').submit();" class="btn btn-primary" style="padding: 10px 24px; font-size: 0.95rem; border-radius: 30px; box-shadow: 0 4px 12px rgba(5, 205, 153, 0.2); display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Save Changes
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" style="padding: 15px; margin-bottom: 24px; border-radius: 12px; background: #E6FFFA; color: #05CD99; border: 1px solid rgba(5,205,153,0.2); display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <?php if (empty($staff_list)): ?>
            <div class="attendance-table-card">
                <div class="empty-state" style="padding: 60px 20px;">
                    <div class="empty-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <h3>No staff found</h3>
                    <p>Go to <a href="manage_staff.php" class="text-link">Staff Directory</a> to add employees.</p>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" id="attendanceForm">
                <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                <input type="hidden" name="save_all" value="1">
                
                <div class="attendance-table-card">
                    <table class="data-table">
                        <thead>
                            <tr style="background: #FAFCFE;">
                                <th style="padding-left: 24px;">Employee</th>
                                <th>Status</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staff_list as $eid => $staff): 
                                $rec = $attendance_map[$eid] ?? null;
                                $status = $rec['status'] ?? 'Absent'; 
                                $check_in = $rec['check_in_time'] ?? '';
                                if (!$check_in && $status == 'Present') $check_in = '09:00'; 
                                $check_out = $rec['check_out_time'] ?? '';
                                
                                // Initials
                                $initials = strtoupper(substr($staff['full_name'], 0, 1));
                            ?>
                            <tr>
                                <td style="padding-left: 24px;">
                                    <div class="product-cell">
                                        <div class="staff-avatar-mini" style="width: 48px; height: 48px; border-radius: 50%; background: #F4F7FE; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div>
                                            <h4 style="font-size: 1rem; margin: 0;"><?php echo htmlspecialchars($staff['full_name']); ?></h4>
                                            <span style="font-size: 0.85rem; color: var(--color-text-secondary);"><?php echo htmlspecialchars($staff['position']); ?></span>
                                            <input type="hidden" name="attendance[<?php echo $eid; ?>][staff_name]" value="<?php echo htmlspecialchars($staff['full_name']); ?>">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="status-group">
                                        <!-- Added IDs for easier lookup if needed, plus handleStatusChange wrapper -->
                                        <label class="status-option">
                                            <input type="radio" class="status-radio" name="attendance[<?php echo $eid; ?>][status]" value="Present" <?php echo ($status=='Present')?'checked':''; ?> onclick="handleStatusChange(<?php echo $eid; ?>, 'Present')">
                                            <span class="status-label">Present</span>
                                        </label>
                                        <label class="status-option">
                                            <input type="radio" class="status-radio" name="attendance[<?php echo $eid; ?>][status]" value="Late" <?php echo ($status=='Late')?'checked':''; ?> onclick="handleStatusChange(<?php echo $eid; ?>, 'Late')">
                                            <span class="status-label">Late</span>
                                        </label>
                                        <label class="status-option">
                                            <input type="radio" class="status-radio" name="attendance[<?php echo $eid; ?>][status]" value="Absent" <?php echo ($status=='Absent')?'checked':''; ?> onclick="handleStatusChange(<?php echo $eid; ?>, 'Absent')">
                                            <span class="status-label">Absent</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="time-input-group">
                                        <input type="time" id="in_<?php echo $eid; ?>" name="attendance[<?php echo $eid; ?>][check_in]" class="time-input" value="<?php echo $check_in; ?>">
                                    </div>
                                </td>
                                <td>
                                    <div class="time-input-group">
                                        <input type="time" id="out_<?php echo $eid; ?>" name="attendance[<?php echo $eid; ?>][check_out]" class="time-input" value="<?php echo $check_out; ?>">
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
        
    </div>
</main>

<script>
// Initial count update
document.addEventListener('DOMContentLoaded', updateStats);

function handleStatusChange(id, status) {
    let inInput = document.getElementById('in_' + id);
    let outInput = document.getElementById('out_' + id);

    if (status === 'Present' || status === 'Late') {
        if (!inInput.value) inInput.value = '09:00';
    } else if (status === 'Absent') {
        inInput.value = '';
        outInput.value = '';
    }
    updateStats();
}

function markAllPresent() {
    document.querySelectorAll('input[value="Present"]').forEach(radio => {
        radio.checked = true;
        let name = radio.name; 
        let id_part = name.match(/\[(\d+)\]/)[1];
        let inInput = document.getElementById('in_' + id_part);
        if(inInput && !inInput.value) inInput.value = '09:00';
    });
    updateStats();
}

function updateStats() {
    let present = 0;
    let late = 0;
    let absent = 0;
    
    document.querySelectorAll('.status-radio:checked').forEach(radio => {
        if (radio.value === 'Present') present++;
        else if (radio.value === 'Late') late++;
        else if (radio.value === 'Absent') absent++;
    });
    
    // Update DOM
    if(document.getElementById('stat-present')) document.getElementById('stat-present').innerText = present;
    if(document.getElementById('stat-late')) document.getElementById('stat-late').innerText = late;
    if(document.getElementById('stat-absent')) document.getElementById('stat-absent').innerText = absent;
}
</script>

<?php require_once 'footer.php'; ?>
