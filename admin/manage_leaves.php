<?php
$page_title = 'Manage Leaves - Grab & Go';
$current_page = 'manage_leaves';

require_once 'admin_middleware.php';

// Handle Actions (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    $remarks = sanitize_input($_POST['remarks']);
    
    if ($status === 'Approved' || $status === 'Rejected') {
        $stmt = $conn->prepare("UPDATE leave_requests SET status = ?, admin_remarks = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $remarks, $request_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Leave request marked as $status.";
        } else {
            $_SESSION['error'] = "Error updating status: " . $stmt->error;
        }
    }
    // Redirect to avoid resubmission
    header("Location: manage_leaves.php");
    exit;
}

// Fetch Pending Requests
$pending_query = "SELECT l.*, s.full_name, s.position 
                  FROM leave_requests l 
                  JOIN staff_directory s ON l.staff_id = s.id 
                  WHERE l.status = 'Pending' 
                  ORDER BY l.created_at ASC";
$pending_res = $conn->query($pending_query);

// Fetch History (Last 50)
$history_query = "SELECT l.*, s.full_name, s.position 
                  FROM leave_requests l 
                  JOIN staff_directory s ON l.staff_id = s.id 
                  WHERE l.status != 'Pending' 
                  ORDER BY l.created_at DESC 
                  LIMIT 50";
$history_res = $conn->query($history_query);

require_once 'header.php';
require_once 'sidebar.php';
?>

<main class="admin-main">
    <div class="admin-container">
        <div class="page-header">
            <div>
                <h1>Manage Leave Requests</h1>
                <p class="text-secondary">Approve or reject staff leave applications</p>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Pending Requests -->
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-text-primary); margin-bottom: 16px;">Pending Applications</h2>
        
        <?php if ($pending_res->num_rows > 0): ?>
            <div class="dashboard-card" style="margin-bottom: 40px;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Staff Member</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pending_res->fetch_assoc()): 
                                $start = new DateTime($row['start_date']);
                                $end = new DateTime($row['end_date']);
                                $diff = $start->diff($end);
                                $days = $diff->days + 1;
                            ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--color-text-primary);"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                        <div style="font-size: 0.85rem; color: var(--color-text-secondary);"><?php echo htmlspecialchars($row['position']); ?></div>
                                    </td>
                                    <td>
                                        <span style="background: #F4F7FE; color: var(--color-primary); padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                            <?php echo htmlspecialchars($row['leave_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500;">
                                            <?php echo date('M d', strtotime($row['start_date'])); ?> - <?php echo date('M d, Y', strtotime($row['end_date'])); ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--color-text-secondary);"><?php echo $days; ?> Day<?php echo $days > 1 ? 's' : ''; ?></div>
                                    </td>
                                    <td style="max-width: 300px;">
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--color-text-secondary); line-height: 1.5;">
                                            <?php echo htmlspecialchars($row['reason']); ?>
                                        </p>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <button onclick="openModal('approve', <?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['full_name']); ?>')" class="btn btn-sm" style="background: #E6FFFA; color: #05CD99; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                                                Approve
                                            </button>
                                            <button onclick="openModal('reject', <?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['full_name']); ?>')" class="btn btn-sm" style="background: #FFEFEF; color: #EE5D50; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                                                Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="dashboard-card" style="padding: 40px; text-align: center; margin-bottom: 40px;">
                <p style="color: var(--color-text-secondary); margin: 0;">No pending leave requests.</p>
            </div>
        <?php endif; ?>

        <!-- History -->
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-text-primary); margin-bottom: 16px;">Request History</h2>
        
        <div class="dashboard-card">
            <?php if ($history_res->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Staff Member</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $history_res->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                                    <td>
                                        <?php echo date('M d', strtotime($row['start_date'])); ?> - <?php echo date('M d', strtotime($row['end_date'])); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($row['admin_remarks'] ?? '-'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                 <div class="empty-state">
                    <p>No history found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Action Modal -->
<div id="actionModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: #fff; padding: 24px; border-radius: 16px; width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <h3 id="modalTitle" style="margin-top: 0;">Confirm Action</h3>
        <p id="modalText" style="color: var(--color-text-secondary);">Are you sure?</p>
        
        <form method="POST">
            <input type="hidden" name="request_id" id="modalRequestId">
            <input type="hidden" name="status" id="modalStatus">
            
            <div class="form-group">
                <label class="form-label">Remarks (Optional)</label>
                <textarea name="remarks" class="form-input" rows="3" placeholder="Add a note..."></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="document.getElementById('actionModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" id="modalConfirmBtn">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(action, id, name) {
    const modal = document.getElementById('actionModal');
    const title = document.getElementById('modalTitle');
    const text = document.getElementById('modalText');
    const statusInput = document.getElementById('modalStatus');
    const idInput = document.getElementById('modalRequestId');
    const btn = document.getElementById('modalConfirmBtn');
    
    idInput.value = id;
    
    if (action === 'approve') {
        title.innerText = 'Approve Leave';
        text.innerText = `Approve leave request for ${name}?`;
        statusInput.value = 'Approved';
        btn.innerText = 'Approve';
        btn.style.background = '#05CD99';
    } else {
        title.innerText = 'Reject Leave';
        text.innerText = `Reject leave request for ${name}?`;
        statusInput.value = 'Rejected';
        btn.innerText = 'Reject';
        btn.style.background = '#EE5D50';
    }
    
    modal.style.display = 'flex';
}

// Close on outside click
window.onclick = function(event) {
    const modal = document.getElementById('actionModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<?php require_once 'footer.php'; ?>
