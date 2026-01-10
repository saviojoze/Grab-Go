<?php
$page_title = 'Admin Dashboard - Grab & Go';
$current_page = 'dashboard';

require_once 'admin_middleware.php';

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
    
    redirect('index.php');
}

require_once 'header.php';

// Get statistics
$stats = [];

// Total products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $result->fetch_assoc()['count'];

// Total categories
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
$stats['categories'] = $result->fetch_assoc()['count'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT COALESCE(SUM(total), 0) as revenue FROM orders WHERE status != 'cancelled'");
$stats['revenue'] = $result->fetch_assoc()['revenue'];

// Low stock products
$low_stock = $conn->query("SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");

// Recent orders
$recent_orders = $conn->query("SELECT o.*, u.full_name FROM orders o 
                               LEFT JOIN users u ON o.user_id = u.id 
                               ORDER BY o.created_at DESC LIMIT 5");
?>

<?php require_once 'sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main">
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Dashboard</h1>
                <p class="text-secondary">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 👋</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-secondary" onclick="window.location.reload()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['products']); ?></h3>
                    <p>Total Products</p>
                </div>
                <div class="stat-trend stat-trend-up">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                    <span>Active Catalog</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['categories']); ?></h3>
                    <p>Categories</p>
                </div>
                <div class="stat-trend stat-trend-neutral">
                    <span>Organized</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['orders']); ?></h3>
                    <p>Total Orders</p>
                </div>
                <div class="stat-trend stat-trend-up">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                    <span>Lifetime</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>₹<?php echo number_format($stats['revenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
                <div class="stat-trend stat-trend-up">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                    <span>Growing</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="quick-actions-grid">
                <a href="add_product.php" class="quick-action-card">
                    <div class="quick-action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </div>
                    <h3>Add Product</h3>
                    <p>Add new product to inventory</p>
                </a>
                
                <a href="categories.php" class="quick-action-card">
                    <div class="quick-action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </div>
                    <h3>Manage Categories</h3>
                    <p>Organize product categories</p>
                </a>
                
                <a href="products.php" class="quick-action-card">
                    <div class="quick-action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                        </svg>
                    </div>
                    <h3>View Products</h3>
                    <p>Browse all products</p>
                </a>
                
                <a href="reports.php" class="quick-action-card">
                    <div class="quick-action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <h3>View Reports</h3>
                    <p>Sales and analytics</p>
                </a>
            </div>
        </div>
        
        <!-- Content Grid -->
        <div class="dashboard-grid">
            <!-- Low Stock Alert -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Low Stock Alert</h2>
                    <span class="badge badge-warning"><?php echo $low_stock->num_rows; ?> items</span>
                </div>
                <div class="card-content">
                    <?php if ($low_stock->num_rows > 0): ?>
                        <div class="stock-list">
                            <?php while ($product = $low_stock->fetch_assoc()): ?>
                                <div class="stock-item">
                                    <div class="stock-item-info">
                                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <span class="stock-badge stock-badge-low">
                                            <?php echo $product['stock']; ?> units left
                                        </span>
                                    </div>
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary">Restock</a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-secondary text-center">All products are well stocked! 🎉</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Recent Orders</h2>
                    <a href="orders.php" class="text-link">View all</a>
                </div>
                <div class="card-content">
                    <?php if ($recent_orders->num_rows > 0): ?>
                        <div class="orders-list">
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <?php
                                // Fetch items for this order to display on dashboard
                                $items_q = $conn->prepare("SELECT oi.quantity, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ? LIMIT 3");
                                $items_q->bind_param("i", $order['id']);
                                $items_q->execute();
                                $items_res = $items_q->get_result();
                                $items_str = [];
                                while($itm = $items_res->fetch_assoc()) {
                                    $items_str[] = $itm['quantity'] . "x " . $itm['name'];
                                }
                                $display_items = implode(", ", $items_str);
                                ?>
                                <div class="order-item">
                                    <div class="order-item-info">
                                        <h4>#<?php echo htmlspecialchars($order['id']); ?> - <?php echo htmlspecialchars($order['full_name']); ?></h4>
                                        <p class="text-secondary" style="font-size: 0.85rem; margin-top: 4px;">
                                            <?php echo htmlspecialchars($display_items); ?>
                                            <?php if ($items_res->num_rows >= 3) echo "..."; ?>
                                        </p>
                                    </div>
                                    <div class="order-item-meta">
                                        <span class="order-amount">₹<?php echo number_format($order['total'], 2); ?></span>
                                        <button 
                                            class="status-badge status-<?php echo $order['status']; ?>"
                                            style="border: none; cursor: pointer; margin-top: 4px;"
                                            onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')"
                                            title="Click to update status"
                                        >
                                            <?php echo ucfirst($order['status']); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-secondary text-center">No orders yet</p>
                    <?php endif; ?>
                </div>
            </div>
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
                        <option value="cancelled">Cancelled</option>
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

<script>
function updateStatus(orderId, currentStatus) {
    document.getElementById('modal_order_id').value = orderId;
    document.getElementById('modal_status').value = currentStatus;
    document.getElementById('statusModal').classList.add('active');
}

function closeModal() {
    document.getElementById('statusModal').classList.remove('active');
}

// Close modal on outside click
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once 'footer.php'; ?>
