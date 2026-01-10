<?php
$page_title = 'My Orders - Grab & Go';

require_once __DIR__ . '/../config.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$user_id = get_user_id();

// Get user's orders
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = $conn->query($orders_query);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-2xl) 0;">
    <h1 style="margin-bottom: var(--spacing-xl);">My Orders</h1>
    
    <?php if ($orders_result && $orders_result->num_rows > 0): ?>
        <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <div class="card" style="padding: var(--spacing-lg);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--spacing-md);">
                        <div>
                            <h3>Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                            <p class="text-secondary text-sm">
                                Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                        <?php
                        $status_class = 'badge-neutral';
                        switch($order['status']) {
                            case 'pending': $status_class = 'badge-warning'; break;
                            case 'ready': $status_class = 'badge-info'; break;
                            case 'completed': $status_class = 'badge-success'; break;
                            case 'cancelled': $status_class = 'badge-danger'; break;
                        }
                        ?>
                        <span class="badge <?php echo $status_class; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
                        <div>
                            <div class="text-sm text-secondary">Pickup Date</div>
                            <div class="font-semibold"><?php echo date('M d, Y', strtotime($order['pickup_date'])); ?></div>
                        </div>
                        <div>
                            <div class="text-sm text-secondary">Pickup Time</div>
                            <div class="font-semibold"><?php echo date('g:i A', strtotime($order['pickup_time'])); ?></div>
                        </div>
                        <div>
                            <div class="text-sm text-secondary">Total</div>
                            <div class="font-semibold text-green">₹<?php echo number_format($order['total'], 2); ?></div>
                        </div>
                    </div>
                    
                    <a href="confirmation.php?order=<?php echo $order['order_number']; ?>" class="btn btn-secondary btn-sm">
                        View Details
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <p>You haven't placed any orders yet.</p>
            <a href="<?php echo BASE_URL; ?>products/listing.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
