<?php
$page_title = 'Order Details - Staff';
$current_page = 'orders';

require_once 'staff_middleware.php';

// Get order ID
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    $_SESSION['error'] = 'Invalid order ID.';
    redirect('orders.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize_input($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Order status updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update order status.';
    }
}

// Fetch order details
$query = "SELECT o.*, u.full_name, u.email as user_email, u.phone as user_phone 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE o.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error'] = 'Order not found.';
    redirect('orders.php');
}

// Fetch order items
$items_query = "SELECT oi.*, p.image_url 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();

require_once 'header.php';
require_once 'sidebar.php';
?>

<!-- Main Content -->
<main class="admin-main">
    <div class="admin-container animate-fade-up">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <div class="breadcrumb">
                    <a href="orders.php">Orders</a> / <span>Order #<?php echo htmlspecialchars($order['order_number']); ?></span>
                </div>
                <h1>Order Details</h1>
            </div>
            <div class="page-actions">
                <button class="btn btn-secondary" onclick="window.print()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Print Order
                </button>
            </div>
        </div>

        <div class="order-details-grid">
            <!-- Left Column: Order Items and Summary -->
            <div class="order-content">
                <!-- Items Card -->
                <div class="detail-card">
                    <div class="card-header">
                        <h2>Order Items</h2>
                        <span class="item-count"><?php echo $items->num_rows; ?> Items</span>
                    </div>
                    <div class="items-list">
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <div class="order-item" id="item-<?php echo $item['id']; ?>">
                                <label class="item-checkbox-wrapper">
                                    <input type="checkbox" class="item-check" onchange="toggleItem(this)">
                                    <div class="checkmark">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </div>
                                </label>
                                <div class="item-img">
                                    <img src="../<?php echo htmlspecialchars($item['image_url'] ?: 'images/placeholder.jpg'); ?>" alt="">
                                </div>
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                    <p class="text-secondary text-sm">Qty: <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="item-price">
                                    ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($order['subtotal'], 2); ?></span>
                        </div>
                        <?php if ($order['discount'] > 0): ?>
                        <div class="summary-row text-success">
                            <span>Discount</span>
                            <span>-₹<?php echo number_format($order['discount'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>₹<?php echo number_format($order['total'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Status and Customer Info -->
            <div class="order-sidebar">
                <!-- Status Card -->
                <div class="detail-card animate-slide-in stagger-1">
                    <div class="card-header">
                        <h2>Update Status</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="statusForm" onsubmit="return handleStatusUpdate(event)">
                            <input type="hidden" name="update_status" value="1">
                            <div class="form-group">
                                <label class="form-label">Current Status</label>
                                <select name="status" id="statusSelect" class="form-input">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                        </form>
                    </div>
                </div>

                <!-- Pickup Information -->
                <div class="detail-card animate-slide-in stagger-2">
                    <div class="card-header">
                        <h2>Pickup Details</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-group">
                            <label>Pickup Time</label>
                            <p><strong><?php echo date('M d, Y', strtotime($order['pickup_date'])); ?></strong> at <strong><?php echo date('h:i A', strtotime($order['pickup_time'])); ?></strong></p>
                        </div>
                        <div class="info-group">
                            <label>Contact Name</label>
                            <p><?php echo htmlspecialchars($order['contact_name'] ?: $order['full_name']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Payment Method</label>
                            <p class="text-capitalize"><?php echo htmlspecialchars($order['payment_method']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="detail-card animate-slide-in stagger-3">
                    <div class="card-header">
                        <h2>Customer Profile</h2>
                    </div>
                    <div class="card-body">
                        <div class="customer-profile-mini">
                            <div class="customer-avatar">
                                <?php echo strtoupper(substr($order['full_name'], 0, 1)); ?>
                            </div>
                            <div class="customer-info">
                                <h3><?php echo htmlspecialchars($order['full_name']); ?></h3>
                                <p class="text-secondary text-sm"><?php echo htmlspecialchars($order['user_email']); ?></p>
                                <p class="text-secondary text-sm"><?php echo htmlspecialchars($order['user_phone'] ?: 'No phone provided'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Collection Verification Modal -->
<div id="collectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Verify Collection</h2>
            <button class="modal-close" type="button" onclick="closeCollectionModal()">&times;</button>
        </div>
        <!-- Wrap in a separate form since we can't nest forms -->
        <form method="POST" id="verificationForm">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="status" value="completed">
            
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 1.1rem; color: #1B2559; margin-bottom: 5px;">Order <?php echo htmlspecialchars($order['order_number']); ?></h3>
                    <p style="color: #A3AED0;">Customer: <span style="color: #2B3674; font-weight: 600;"><?php echo htmlspecialchars($order['full_name']); ?></span></p>
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
function handleStatusUpdate(e) {
    const statusSelect = document.getElementById('statusSelect');
    if (statusSelect.value === 'completed') {
        e.preventDefault(); // Stop normal form submission
        openCollectionModal();
        return false;
    }
    return true; // Allow other statuses to proceed
}

function openCollectionModal() {
    // Reset checkboxes
    const checkboxes = document.querySelectorAll('#collectionModal input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('collectionModal').classList.add('show');
}

function closeCollectionModal() {
    document.getElementById('collectionModal').classList.remove('show');
    // Optional: Reset select to previous value if cancelled
}

// Close on outside click
window.onclick = function(event) {
    if (event.target == document.getElementById('collectionModal')) {
        closeCollectionModal();
    }
}

// Manual Item Verification Toggle
function toggleItem(checkbox) {
    const itemRow = checkbox.closest('.order-item');
    if (checkbox.checked) {
        itemRow.classList.add('verified');
    } else {
        itemRow.classList.remove('verified');
    }
}
</script>

<style>
.breadcrumb {
    font-size: 0.875rem;
    color: #718096;
    margin-bottom: 0.5rem;
}

.breadcrumb a {
    color: #667eea;
    text-decoration: none;
}

.order-details-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 2rem;
    margin-top: 1rem;
}

.detail-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
}

.card-body {
    padding: 1.5rem;
}

.item-count {
    background: #edf2f7;
    color: #4a5568;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8125rem;
    font-weight: 600;
}

.items-list {
    padding: 1rem 1.5rem;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #f7fafc;
}

.order-item:last-child {
    border-bottom: none;
}

.item-img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    background: #f7fafc;
    overflow: hidden;
}

.item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-info {
    flex: 1;
}

.item-info h3 {
    font-size: 0.95rem;
    margin: 0 0 0.25rem 0;
}

.item-price {
    font-weight: 600;
    color: #2d3748;
}

.order-summary {
    background: #f8fafc;
    padding: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    color: #4a5568;
}

.summary-row.total {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px dashed #e2e8f0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a202c;
}

.info-group {
    margin-bottom: 1.5rem;
}

.info-group:last-child {
    margin-bottom: 0;
}

.info-group label {
    display: block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #718096;
    margin-bottom: 0.5rem;
}

.info-group p {
    margin: 0;
    color: #2d3748;
}

.customer-profile-mini {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.customer-avatar {
    width: 50px;
    height: 50px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 700;
}

.customer-info h3 {
    font-size: 1rem;
    margin: 0 0 0.25rem 0;
}

.customer-info p {
    margin: 0;
}

@media (max-width: 992px) {
    .order-details-grid {
        grid-template-columns: 1fr;
    }
}

@media print {
    .admin-sidebar, .admin-header, .page-actions, .order-sidebar .detail-card:first-child {
        display: none !important;
    }
    .admin-main {
        padding: 0;
        margin: 0;
    }
    .order-details-grid {
        display: block;
    }
    .order-content, .order-sidebar {
        width: 100%;
    }
    .detail-card {
        box-shadow: none;
        border: 1px solid #e2e8f0;
    }
}
</style>

<?php require_once 'footer.php'; ?>
