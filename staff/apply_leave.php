<?php
$page_title = 'Apply Leave - Grab & Go';
$current_page = 'my_leaves';

require_once 'staff_middleware.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leave_type = sanitize_input($_POST['leave_type']);
    $reason = sanitize_input($_POST['reason']);
    
    // Validation
    if (empty($start_date) || empty($end_date) || empty($reason)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } elseif ($end_date < $start_date) {
        $_SESSION['error'] = "End date cannot be before start date.";
    } else {
        // Get Staff ID
        $user_id = $_SESSION['user_id'];
        $stmt_s = $conn->prepare("SELECT id FROM staff_directory WHERE user_id = ?");
        $stmt_s->bind_param("i", $user_id);
        $stmt_s->execute();
        $res_s = $stmt_s->get_result();
        
        if ($res_s->num_rows > 0) {
            $staff_id = $res_s->fetch_assoc()['id'];
            
            $stmt = $conn->prepare("INSERT INTO leave_requests (staff_id, start_date, end_date, leave_type, reason) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $staff_id, $start_date, $end_date, $leave_type, $reason);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Leave request submitted successfully.";
                header("Location: my_leaves.php");
                exit;
            } else {
                $_SESSION['error'] = "Database error: " . $stmt->error;
            }
        } else {
             // Fallback for unlinked staff accounts
             $name = $_SESSION['full_name'];
             $stmt_n = $conn->prepare("SELECT id FROM staff_directory WHERE full_name = ?");
             $stmt_n->bind_param("s", $name);
             $stmt_n->execute();
             $res_n = $stmt_n->get_result();
             
             if ($res_n->num_rows > 0) {
                 $staff_id = $res_n->fetch_assoc()['id'];
                 // Link it now for future
                 $conn->query("UPDATE staff_directory SET user_id = $user_id WHERE id = $staff_id");
                 
                 $stmt = $conn->prepare("INSERT INTO leave_requests (staff_id, start_date, end_date, leave_type, reason) VALUES (?, ?, ?, ?, ?)");
                 $stmt->bind_param("issss", $staff_id, $start_date, $end_date, $leave_type, $reason);
                 if ($stmt->execute()) {
                    $_SESSION['success'] = "Leave request submitted successfully.";
                    header("Location: my_leaves.php");
                    exit;
                 }
             } else {
                 // Self-heal: Create new profile
                 $full_name = $_SESSION['full_name'];
                 $stmt = $conn->prepare("INSERT INTO staff_directory (full_name, user_id, position) VALUES (?, ?, 'Staff')");
                 $stmt->bind_param("si", $full_name, $user_id);
                 if ($stmt->execute()) {
                     $staff_id = $conn->insert_id;
                     $stmt = $conn->prepare("INSERT INTO leave_requests (staff_id, start_date, end_date, leave_type, reason) VALUES (?, ?, ?, ?, ?)");
                     $stmt->bind_param("issss", $staff_id, $start_date, $end_date, $leave_type, $reason);
                     if ($stmt->execute()) {
                        $_SESSION['success'] = "Leave request submitted successfully.";
                        header("Location: my_leaves.php");
                        exit;
                     }
                 } else {
                     $_SESSION['error'] = "Error creating staff profile: " . $conn->error;
                 }
             }
        }
    }
}

require_once 'header.php';
require_once 'sidebar.php';
?>

<main class="admin-main">
    <div class="admin-container">
        <div class="page-header">
            <div>
                <a href="my_leaves.php" class="text-secondary" style="text-decoration: none; display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"></path><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Back to My Leaves
                </a>
                <h1>Apply for Leave</h1>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-card" style="max-width: 600px; margin: 0 auto;">
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label class="form-label">Leave Type</label>
                    <select name="leave_type" class="form-input">
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Emergency Leave">Emergency Leave</option>
                        <option value="Vacation">Vacation</option>
                    </select>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-input" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-input" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Reason</label>
                    <textarea name="reason" class="form-input" rows="4" placeholder="Briefly explain the reason for your leave..." required></textarea>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary btn-block">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>
