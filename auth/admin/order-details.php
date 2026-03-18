<?php
$page_title = 'Order Details - Admin';
$current_page = 'orders';

require_once 'admin_middleware.php';

// Get order ID
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    redirect('orders.php');
}

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize_input($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Order status updated successfully!';
        
        // Optional: Send email notification to customer here
        
    } else {
        $_SESSION['error'] = 'Failed to update order status.';
    }
    redirect("order-details.php?id=$order_id");
}

// Fetch Order Details
// Fetch Order Details
$query = "SELECT o.*, u.full_name as user_name, u.email as user_email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE o.id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    $_SESSION['error'] = 'Order not found.';
    redirect('orders.php');
}

// Fetch Order Items
$items_query = "SELECT oi.*, p.name, p.image_url, p.category_id 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
                
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();

require_once 'header.php';
?>

<?php require_once 'sidebar.php'; ?>

<main class="admin-main">
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <div class="breadcrumb">
                    <a href="orders.php" class="text-secondary">Orders</a>
                    <span class="mx-2">/</span>
                    <span class="text-primary">Order #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <h1>Order Details</h1>
            </div>
            <div class="page-actions">
                <button onclick="window.print()" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Print Order
                </button>
                <button id="updateStatusBtn" class="btn btn-primary">
                    Update Status
                </button>
            </div>
        </div>

        <div class="order-grid">
            <!-- Order Information -->
            <div class="info-card">
                <h3>Order Information</h3>
                <div class="info-row">
                    <span class="label">Order ID:</span>
                    <span class="value">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Date Placed:</span>
                    <span class="value"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="value">
                        <?php
                        $status_class = '';
                        switch($order['status']) {
                            case 'pending': $status_class = 'badge-warning'; break;
                            case 'ready': $status_class = 'badge-info'; break;
                            case 'completed': $status_class = 'badge-success'; break;
                            case 'cancelled': $status_class = 'badge-danger'; break;
                            default: $status_class = 'badge-neutral';
                        }
                        ?>
                        <span class="badge <?php echo $status_class; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Pickup Time:</span>
                    <span class="value"><?php echo date('M d, Y', strtotime($order['pickup_date'])) . ' at ' . date('h:i A', strtotime($order['pickup_time'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Payment:</span>
                    <span class="value" style="text-transform: capitalize;"><?php echo $order['payment_method']; ?></span>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="info-card">
                <h3>Customer Details</h3>
                <div class="customer-profile">
                    <div class="customer-avatar">
                        <?php echo strtoupper(substr($order['contact_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h4><?php echo htmlspecialchars($order['contact_name']); ?></h4>
                        <p class="text-secondary"><?php echo htmlspecialchars($order['contact_email']); ?></p>
                        <?php if (!empty($order['contact_phone'])): ?>
                            <p class="text-secondary"><?php echo htmlspecialchars($order['contact_phone']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="mailto:<?php echo htmlspecialchars($order['contact_email']); ?>" class="btn btn-sm btn-secondary w-100">
                        Contact Customer
                    </a>
                </div>
            </div>

            <!-- Order Items -->
            <div class="items-card full-width">
                <h3>Order Items</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Price</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $order_items->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img 
                                            src="../<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.jpg'); ?>" 
                                            alt="<?php echo htmlspecialchars($item['name']); ?>"
                                            class="item-thumb"
                                            onerror="this.src='../images/placeholder.jpg'"
                                        >
                                        <div>
                                            <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                            <!-- Valid categories usually 1-5, just a failsafe -->
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">₹<?php echo number_format($item['price'], 2); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-right">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right label-total">Subtotal</td>
                            <td class="text-right">₹<?php echo number_format($order['total'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right label-total">Tax (0%)</td>
                            <td class="text-right">₹0.00</td>
                        </tr>
                        <tr class="grand-total">
                            <td colspan="3" class="text-right">Total</td>
                            <td class="text-right text-primary">₹<?php echo number_format($order['total'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Status Update Modal -->
<div class="modal" id="statusModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Order Status</h3>
            <button class="modal-close" id="closeStatusBtn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="update_status" value="1">
                <div class="form-group">
                    <label class="form-label">Select Status</label>
                    <select name="status" class="form-input">
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="ready" <?php echo $order['status'] == 'ready' ? 'selected' : ''; ?>>Ready for Pickup</option>
                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="alert alert-info">
                    Changing status to "Ready" or "Completed" will help track order progress.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelStatusBtn">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Page Specific Styles */
.order-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-top: 24px;
}

.full-width {
    grid-column: 1 / -1;
}

.info-card, .items-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.info-card h3, .items-card h3 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.1rem;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 12px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    align-items: center;
}

.info-row .label {
    color: var(--text-secondary);
    font-weight: 500;
}

.info-row .value {
    color: var(--text-primary);
    font-weight: 600;
}

.customer-profile {
    display: flex;
    align-items: center;
    gap: 16px;
}

.customer-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--color-primary-light);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 600;
}

.customer-profile h4 {
    margin: 0;
    font-size: 1rem;
}

.customer-profile p {
    margin: 2px 0 0;
    font-size: 0.9rem;
}

/* Items Table */
.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th {
    text-align: left;
    padding: 12px;
    color: var(--text-secondary);
    font-weight: 500;
    border-bottom: 1px solid var(--border-color);
}

.items-table td {
    padding: 16px 12px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.items-table tfoot td {
    border-bottom: none;
    padding: 8px 12px;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.item-thumb {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
    background: #f3f4f6;
}

.item-name {
    font-weight: 500;
    color: var(--text-primary);
}

.text-right { text-align: right; }
.text-center { text-align: center; }

.label-total {
    font-weight: 500;
    color: var(--text-secondary);
}

.grand-total td {
    border-top: 2px solid var(--border-color);
    padding-top: 16px;
    font-weight: 700;
    font-size: 1.1rem;
}

.breadcrumb {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    margin-bottom: 8px;
}

/* Print Styles */
@media print {
    .sidebar, .page-header .page-actions, .breadcrumb {
        display: none;
    }
    
    .admin-main {
        margin-left: 0;
        padding: 0;
    }
    
    .admin-container {
        padding: 0;
        max-width: 100%;
    }
    
    .info-card, .items-card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusModal = document.getElementById('statusModal');
    const updateStatusBtn = document.getElementById('updateStatusBtn');
    const closeStatusBtn = document.getElementById('closeStatusBtn');
    const cancelStatusBtn = document.getElementById('cancelStatusBtn');

    function openModal() {
        if (statusModal) {
            statusModal.classList.add('active');
            statusModal.classList.add('show'); // Support both conventions
            console.log('Opening status modal');
        }
    }

    function closeModal() {
        if (statusModal) {
            statusModal.classList.remove('active');
            statusModal.classList.remove('show');
        }
    }

    if (updateStatusBtn) {
        updateStatusBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    if (closeStatusBtn) {
        closeStatusBtn.addEventListener('click', closeModal);
    }
    
    if (cancelStatusBtn) {
        cancelStatusBtn.addEventListener('click', closeModal);
    }

    // Close on outside click
    window.addEventListener('click', function(e) {
        if (e.target === statusModal) {
            closeModal();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && statusModal && statusModal.classList.contains('active')) {
            closeModal();
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>
