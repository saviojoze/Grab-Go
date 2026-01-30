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

// Low stock products (for Chart data)
$stock_out = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock = 0")->fetch_assoc()['count'];
$stock_low = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock > 0 AND stock <= 10")->fetch_assoc()['count'];
$stock_available = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock > 10")->fetch_assoc()['count'];

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
            <div class="stat-card">
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
            <!-- Stock Status Chart -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Stock Health</h2>
                    <span class="badge badge-neutral">Real-time</span>
                </div>
                <div class="card-content">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 32px; margin-bottom: 24px;">
                        <div style="width: 180px; height: 180px; position: relative;">
                            <canvas id="stockChart"></canvas>
                            <!-- Center Text -->
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                                <span style="display: block; font-size: 2rem; font-weight: 800; color: var(--color-text-primary); line-height: 1;"><?php echo $stock_available + $stock_low + $stock_out; ?></span>
                                <span style="display: block; font-size: 0.75rem; color: var(--color-text-secondary); margin-top: 4px;">Total Items</span>
                            </div>
                        </div>
                        
                        <!-- Custom Legend -->
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #05CD99;"></span>
                                <div>
                                    <span class="legend-label">Available</span>
                                    <span class="legend-value"><?php echo $stock_available; ?></span>
                                </div>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #FFB547;"></span>
                                <div>
                                    <span class="legend-label">Limited Stock</span>
                                    <span class="legend-value"><?php echo $stock_low; ?></span>
                                </div>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: #EE5D50;"></span>
                                <div>
                                    <span class="legend-label">Out of Stock</span>
                                    <span class="legend-value"><?php echo $stock_out; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actionable Stock List -->
                    <div class="stock-alerts-section">
                        <h4 style="margin: 0 0 16px 0; font-size: 1rem; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">Items Needing Attention</h4>
                        
                        <?php 
                        // Fetch the actual products that are low/out
                        $alert_products = $conn->query("SELECT * FROM products WHERE stock <= 10 ORDER BY stock ASC LIMIT 5");
                        if ($alert_products->num_rows > 0): 
                        ?>
                            <div class="stock-list">
                                <?php while ($product = $alert_products->fetch_assoc()): ?>
                                    <div class="stock-item">
                                        <div class="stock-item-info">
                                            <h4 style="margin: 0; font-size: 0.95rem;"><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <?php if($product['stock'] == 0): ?>
                                                <span class="badge badge-danger">Out of Stock</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><?php echo $product['stock']; ?> left</span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-sm btn-secondary" style="text-decoration: none;">Restock</a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state-small" style="text-align: center; padding: 20px; color: var(--color-text-secondary); background: #F4F7FE; border-radius: 8px;">
                                <p style="margin: 0; font-size: 0.9rem;">✨ All products are well stocked!</p>
                            </div>
                        <?php endif; ?>
                    </div>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('stockChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Limited Stock', 'Out of Stock'],
            datasets: [{
                data: [<?php echo $stock_available; ?>, <?php echo $stock_low; ?>, <?php echo $stock_out; ?>],
                backgroundColor: [
                    '#05CD99', // Green for Available
                    '#FFB547', // Orange for Limited
                    '#EE5D50'  // Red for Out
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '75%', // Thinner ring for modern look
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // Using custom legend
                },
                tooltip: {
                    backgroundColor: '#111c44',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { family: 'DM Sans', size: 13 },
                    bodyFont: { family: 'DM Sans', size: 13 }
                }
            }
        }
    });

    // Modal Functions
    function updateStatus(orderId, currentStatus) {
        document.getElementById('modal_order_id').value = orderId;
        document.getElementById('modal_status').value = currentStatus;
        document.getElementById('statusModal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('statusModal').classList.remove('show');
    }

    // Close modal on outside click
    document.getElementById('statusModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>

<?php require_once 'footer.php'; ?>
