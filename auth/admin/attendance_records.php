<?php
$page_title = 'Attendance Logs - Grab & Go';
$current_page = 'attendance_records';

require_once 'admin_middleware.php';
require_once 'header.php';

// Filters
$start_date = $_GET['start'] ?? date('Y-m-01');
$end_date = $_GET['end'] ?? date('Y-m-d');
$staff_filter = $_GET['staff_id'] ?? '';

// Query
$sql = "SELECT sa.*, sd.full_name as emp_name, sd.position 
        FROM staff_attendance sa 
        LEFT JOIN staff_directory sd ON sa.employee_id = sd.id 
        WHERE sa.date BETWEEN ? AND ?";
$params = [$start_date, $end_date];
$types = "ss";

if (!empty($staff_filter)) {
    $sql .= " AND sa.employee_id = ?";
    $params[] = $staff_filter;
    $types .= "i";
}
$sql .= " ORDER BY sa.date DESC, sd.full_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// Get Staff for Filter
$staff_q = $conn->query("SELECT id, full_name FROM staff_directory ORDER BY full_name ASC");
$staff_options = [];
while($s = $staff_q->fetch_assoc()) {
    $staff_options[] = $s;
}
?>

<?php require_once 'sidebar.php'; ?>

<style>
    .logs-filter-card {
        background: #fff;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }
    
    .filter-form {
        display: flex;
        gap: 16px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--color-text-secondary);
        font-size: 0.85rem;
    }
    
    .logs-table-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .status-present {
        background: rgba(5, 205, 153, 0.1);
        color: #05CD99;
    }
    
    .status-late {
        background: rgba(255, 181, 71, 0.1);
        color: #FFB547;
    }
    
    .status-absent {
        background: rgba(238, 93, 80, 0.1);
        color: #EE5D50;
    }
    
    .date-badge {
        background: #F4F7FE;
        padding: 6px 12px; 
        border-radius: 8px;
        font-weight: 600;
        color: var(--color-text-primary);
        font-size: 0.9rem;
    }
    
    /* Responsive Table Styles */
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<main class="admin-main">
    <div class="admin-container">
        
        <div class="page-header">
            <div>
                <h1>Attendance Logs</h1>
                <p class="text-secondary">View historical attendance records</p>
            </div>
            <div>
                <a href="attendance.php" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"></path><path d="M12 19l-7-7 7-7"></path></svg>
                    Back to Board
                </a>
            </div>
        </div>
        
        <div class="logs-filter-card">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label class="filter-label">Start Date</label>
                    <input type="date" name="start" value="<?php echo $start_date; ?>" class="form-input" style="height: 48px;">
                </div>
                <div class="filter-group">
                    <label class="filter-label">End Date</label>
                    <input type="date" name="end" value="<?php echo $end_date; ?>" class="form-input" style="height: 48px;">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Filter by Staff</label>
                    <div style="position: relative;">
                        <select name="staff_id" class="form-input" style="height: 48px; appearance: none;">
                            <option value="">All Staff Members</option>
                            <?php foreach ($staff_options as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php if($staff_filter == $s['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($s['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i style="position: absolute; right: 16px; top: 16px; pointer-events: none;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"></path></svg>
                        </i>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="height: 48px; padding: 0 32px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                    Filter Logs
                </button>
            </form>
        </div>
        
        <?php if ($res->num_rows == 0): ?>
            <div class="logs-table-card">
                <div class="empty-state" style="padding: 60px 20px;">
                    <div class="empty-icon" style="background: #F4F7FE; color: var(--color-text-secondary);">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <h3>No records found</h3>
                    <p>Try matching different dates or check if attendance has been marked.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="logs-table-card">
                <table class="data-table">
                    <thead>
                        <tr style="background: #FAFCFE;">
                            <th style="padding-left: 24px;">Date</th>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $res->fetch_assoc()): 
                            $status_class = 'status-absent';
                            if ($row['status'] == 'Present') $status_class = 'status-present';
                            if ($row['status'] == 'Late') $status_class = 'status-late';
                            
                            $initials = strtoupper(substr($row['emp_name'] ?: $row['staff_name'], 0, 1));
                            
                            $check_in = $row['check_in_time'] ? date('h:i A', strtotime($row['check_in_time'])) : '--:--';
                            $check_out = $row['check_out_time'] ? date('h:i A', strtotime($row['check_out_time'])) : '--:--';
                        ?>
                        <tr>
                            <td style="padding-left: 24px;">
                                <span class="date-badge">
                                    <?php echo date('M d, Y', strtotime($row['date'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="product-cell">
                                    <div class="staff-avatar-mini" style="width: 40px; height: 40px; border-radius: 50%; background: #F4F7FE; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem;">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 0.95rem; margin: 0;"><?php echo htmlspecialchars($row['emp_name'] ?: $row['staff_name']); ?></h4>
                                        <span style="font-size: 0.8rem; color: var(--color-text-secondary);"><?php echo htmlspecialchars($row['position']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php if($row['status'] == 'Present'): ?>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <?php elseif($row['status'] == 'Late'): ?>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <?php else: ?>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td style="font-family: monospace; font-size: 0.95rem; font-weight: 600; color: var(--color-text-primary);">
                                <?php echo $check_in; ?>
                            </td>
                            <td style="font-family: monospace; font-size: 0.95rem; font-weight: 600; color: var(--color-text-primary);">
                                <?php echo $check_out; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php require_once 'footer.php'; ?>
