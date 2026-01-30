<?php
$page_title = 'Order Management - Staff';
$current_page = 'orders';

require_once 'staff_middleware.php';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = sanitize_input($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Order status updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update order status.';
    }
    
    redirect('orders.php');
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT o.*, u.full_name, u.email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE 1=1";

$params = [];
$types = "";

if ($status_filter !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search)) {
    $query .= " AND (o.order_number LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

// Optimized sorting for workflow efficiency
if ($status_filter === 'pending' || $status_filter === 'ready') {
    $query .= " ORDER BY o.pickup_date ASC, o.pickup_time ASC";
} else {
    $query .= " ORDER BY o.created_at DESC";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result();

// Get order statistics for filters
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$stats['pending'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$stats['ready'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'ready'")->fetch_assoc()['count'];
$stats['completed'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'")->fetch_assoc()['count'];

require_once 'header.php';
require_once 'sidebar.php';
?>

<style>
/* PREMIUM FILTERS & SEARCH - INJECTED FOR ROBUSTNESS */
.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
    background: #FFF;
    padding: 10px 10px;
    border-radius: 50px; /* Pill shape container */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); /* Soft container shadow */
    border: 1px solid rgba(255, 255, 255, 0.5);
}

/* Filter Tabs as PILLS */
.filter-tabs {
    display: flex;
    gap: 8px; /* Space between pills */
    padding: 0;
    margin: 0 !important;
    border: none;
    background: transparent;
}

.filter-tab {
    padding: 8px 16px;
    border-radius: 30px; /* Fully rounded pills */
    color: #A3AED0;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.filter-tab:hover {
    color: #4318FF;
    background: #F4F7FE;
}

.filter-tab.active {
    background: #4318FF;
    color: #FFF;
    box-shadow: 0 4px 10px rgba(67, 24, 255, 0.25); /* Glow effect */
}

/* Modern Search Bar */
.search-form {
    margin: 0;
}

.search-input-group {
    position: relative;
    width: 280px;
}

.search-input-group input {
    width: 100%;
    padding: 10px 16px 10px 42px;
    border-radius: 30px;
    border: 1px solid #E0E5F2; /* Subtle border */
    background: #F4F7FE; /* Light background to contrast white container */
    color: #1B2559;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s;
}

.search-input-group input:focus {
    background: #FFF;
    border-color: #4318FF;
    box-shadow: 0 0 0 3px rgba(67, 24, 255, 0.1);
    outline: none;
}

.search-input-group svg {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #4318FF; /* Primary colored icon */
    pointer-events: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-controls {
        border-radius: 20px; /* Less rounded on mobile if it stacks */
        padding: 16px;
        flex-direction: column;
        align-items: stretch;
    }
    .filter-tabs {
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .search-input-group {
        width: 100%;
    }
}
</style>

<!-- Main Content -->
<main class="admin-main">
    <div class="admin-container animate-fade-up">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Order Management</h1>
                <p class="text-secondary">Process and manage customer orders</p>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="table-controls animate-slide-in stagger-1" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
            <div class="filter-tabs" style="margin-bottom: 0;">
                <a href="?status=all" class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                    All Orders (<?php echo $stats['total']; ?>)
                </a>
                <a href="?status=pending" class="filter-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                    Pending (<?php echo $stats['pending']; ?>)
                </a>
                <a href="?status=ready" class="filter-tab <?php echo $status_filter === 'ready' ? 'active' : ''; ?>">
                    Ready (<?php echo $stats['ready']; ?>)
                </a>
                <a href="?status=completed" class="filter-tab <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                    Completed (<?php echo $stats['completed']; ?>)
                </a>
            </div>
            
            <form method="GET" class="search-form">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                <div class="search-input-group">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search order #, customer..." 
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                </div>
            </form>
        </div>
        
        <!-- Orders Table -->
        <div class="table-card animate-slide-in stagger-2">
            <?php if ($orders->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Pickup Date/Time</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <?php 
                                    $is_today = date('Y-m-d') === $order['pickup_date'];
                                    $pickup_urgent = $is_today && $order['status'] !== 'completed' && $order['status'] !== 'cancelled';
                                ?>
                                <tr class="<?php echo $pickup_urgent ? 'urgent-row' : ''; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="customer-name"><?php echo htmlspecialchars($order['full_name']); ?></div>
                                            <div class="text-secondary text-sm"><?php echo htmlspecialchars($order['email']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <?php echo date('M d, Y', strtotime($order['pickup_date'])); ?>
                                            <?php if ($is_today): ?>
                                                <span class="badge" style="background:var(--color-primary); color:white; font-size:10px; margin-left:5px;">TODAY</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-secondary text-sm"><?php echo date('h:i A', strtotime($order['pickup_time'])); ?></div>
                                    </td>
                                    <td><strong>₹<?php echo number_format($order['total'], 2); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            
                                            <!-- Quick Action: Pending -> Ready -->
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="update_status" value="1">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="ready">
                                                    <button type="submit" class="btn-icon" style="background-color: var(--color-success); color: white;" title="Mark as Ready">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Quick Action: Ready -> Completed -->
                                            <?php if ($order['status'] === 'ready'): ?>
                                                <button 
                                                    type="button"
                                                    class="btn-icon" 
                                                    style="background-color: var(--color-primary); color: white;" 
                                                    title="Verify & Complete"
                                                    onclick="verifyCollection(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_number']); ?>', '<?php echo htmlspecialchars($order['full_name']); ?>')"
                                                >
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                                    </svg>
                                                </button>
                                            <?php endif; ?>

                                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="View Details">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                            <button 
                                                class="btn-icon" 
                                                onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')"
                                                title="Edit Status"
                                            >
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <h3>No orders found</h3>
                    <p>There are no orders matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Update Status Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Update Order Status</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="order_id" id="modal_order_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select New Status</label>
                    <select name="status" id="modal_status" class="form-input" required>
                        <option value="pending">Pending</option>
                        <option value="ready">Ready for Pickup</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Collection Verification Modal -->
<div id="collectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Verify Collection</h2>
            <button class="modal-close" onclick="closeCollectionModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="order_id" id="collection_order_id">
            <input type="hidden" name="status" value="completed">
            
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 1.1rem; color: #1B2559; margin-bottom: 5px;">Order <span id="verify_order_num"></span></h3>
                    <p style="color: #A3AED0;">Customer: <span id="verify_customer" style="color: #2B3674; font-weight: 600;"></span></p>
                </div>

                <div class="verification-steps">
                    <div class="verification-heading">Required Checks</div>
                    
                    <label class="verification-item">
                        <input type="checkbox" required>
                        <span>Customer Identity Verified (ID/SMS)</span>
                    </label>
                    
                    <label class="verification-item">
                        <input type="checkbox" required>
                        <span>Payment Status Confirmed</span>
                    </label>

                    <label class="verification-item">
                        <input type="checkbox" required>
                        <span>All Items Handed Over to Customer</span>
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCollectionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm & Complete</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateStatus(orderId, currentStatus) {
    document.getElementById('modal_order_id').value = orderId;
    document.getElementById('modal_status').value = currentStatus;
    document.getElementById('statusModal').classList.add('show');
}

function verifyCollection(orderId, orderNum, customerName) {
    document.getElementById('collection_order_id').value = orderId;
    document.getElementById('verify_order_num').textContent = orderNum;
    document.getElementById('verify_customer').textContent = customerName;
    
    // Reset checkboxes
    const checkboxes = document.querySelectorAll('#collectionModal input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    document.getElementById('collectionModal').classList.add('show');
}

function closeModal() {
    document.getElementById('statusModal').classList.remove('show');
}

function closeCollectionModal() {
    document.getElementById('collectionModal').classList.remove('show');
}

// Close modal on outside click
window.onclick = function(event) {
    if (event.target == document.getElementById('statusModal')) {
        closeModal();
    }
    if (event.target == document.getElementById('collectionModal')) {
        closeCollectionModal();
    }
}
</script>

<?php require_once 'footer.php'; ?>
