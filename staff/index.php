<?php
$page_title = 'Staff Dashboard - Grab & Go';
$current_page = 'dashboard';

require_once 'staff_middleware.php';

// Get order statistics
$today_stats = [];
$today_stats['total'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$today_stats['pending'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$today_stats['ready'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'ready'")->fetch_assoc()['count'];
$today_stats['completed'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'")->fetch_assoc()['count'];

// Get recent orders
$recent_orders_query = "SELECT o.*, u.full_name 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 5";
$recent_orders = $conn->query($recent_orders_query);

require_once 'header.php';
require_once 'sidebar.php';
?>

<!-- Main Content -->
<main class="admin-main">
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                <p class="text-secondary">Here's what's happening at your store today.</p>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card stagger-1">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?php echo $today_stats['total']; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card stagger-2">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?php echo $today_stats['pending']; ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>
            
            <div class="stat-card stagger-3">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?php echo $today_stats['ready']; ?></h3>
                    <p>Ready for Pickup</p>
                </div>
            </div>
            
            <div class="stat-card stagger-4">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6L9 17l-5-5"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?php echo $today_stats['completed']; ?></h3>
                    <p>Completed Orders</p>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="dashboard-card animate-slide-in stagger-4">
            <div class="card-header">
                <h2>Recent Orders</h2>
                <a href="orders.php" class="btn btn-secondary">
                    View All Orders
                </a>
            </div>
            
            <?php if ($recent_orders->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                    <td>
                                        <div>
                                            <div style="font-weight: 700; color: #1B2559;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                                            <!-- <div class="text-secondary text-sm"><?php echo htmlspecialchars($order['email']); ?></div> -->
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #A3AED0; line-height: 1.4;">
                                            <?php echo date('M d,', strtotime($order['created_at'])); ?><br>
                                            <?php echo date('Y', strtotime($order['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td><strong>₹<?php echo number_format($order['total'], 2); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-icon">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No orders yet</h3>
                    <p>New orders will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>
