<?php
$page_title = 'My Leaves - Grab & Go';
$current_page = 'my_leaves';

require_once 'staff_middleware.php';

// Fetch current staff ID
$user_id = $_SESSION['user_id'];
// Get staff_id from staff_directory using user_id
$stmt = $conn->prepare("SELECT id FROM staff_directory WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Fallback: Try to match by name if user_id is not linked yet (should catch this in migration)
    $name = $_SESSION['full_name'];
    $stmt = $conn->prepare("SELECT id FROM staff_directory WHERE full_name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
}

$staff_data = $result->fetch_assoc();
if (!$staff_data) {
    // Self-heal: Create staff profile if missing
    $full_name = $_SESSION['full_name'];
    $stmt = $conn->prepare("INSERT INTO staff_directory (full_name, user_id, position) VALUES (?, ?, 'Staff')");
    $stmt->bind_param("si", $full_name, $user_id);
    if ($stmt->execute()) {
        $staff_id = $conn->insert_id;
        $staff_data = ['id' => $staff_id];
    } else {
        die("Error creating staff profile: " . $conn->error);
    }
}
$staff_id = $staff_data['id'];

// Fetch Leaves
$leaves = [];
$query = "SELECT * FROM leave_requests WHERE staff_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $leaves[] = $row;
}

require_once 'header.php';
require_once 'sidebar.php';
?>

<main class="admin-main">
    <div class="admin-container">
        <div class="page-header">
            <div>
                <h1>My Leave Requests</h1>
                <p class="text-secondary">Track your leave status and history</p>
            </div>
            <div>
                <a href="apply_leave.php" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Apply for Leave
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-card">
            <?php if (empty($leaves)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <h3>No leave requests found</h3>
                    <p>You haven't applied for any leave yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Duration</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaves as $leave): 
                                $start = new DateTime($leave['start_date']);
                                $end = new DateTime($leave['end_date']);
                                $diff = $start->diff($end);
                                $days = $diff->days + 1;
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($leave['leave_type']); ?></strong></td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($leave['start_date'])); ?> 
                                        <span class="text-secondary">to</span> 
                                        <?php echo date('M d, Y', strtotime($leave['end_date'])); ?>
                                    </td>
                                    <td><?php echo $days; ?> Day<?php echo $days > 1 ? 's' : ''; ?></td>
                                    <td><?php echo htmlspecialchars(substr($leave['reason'], 0, 50)) . (strlen($leave['reason']) > 50 ? '...' : ''); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($leave['status']); ?>">
                                            <?php echo ucfirst($leave['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-secondary">
                                        <?php echo htmlspecialchars($leave['admin_remarks'] ?? '-'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>
