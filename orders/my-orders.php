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
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                        
                        <?php if($order['payment_method'] === 'online'): ?>
                            <?php 
                            $pay_class = 'badge-neutral';
                            if($order['payment_status'] === 'paid') $pay_class = 'badge-success';
                            if($order['payment_status'] === 'failed') $pay_class = 'badge-danger';
                            if($order['payment_status'] === 'pending') $pay_class = 'badge-warning';
                            ?>
                            <div style="margin-top: 5px; text-align: right;">
                                <span class="badge <?php echo $pay_class; ?>" style="font-size: 0.7rem;">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                                <?php if($order['payment_status'] === 'pending'): ?>
                                    <a href="../checkout/pay-online.php?order=<?php echo $order['order_number']; ?>" style="display: block; font-size: 0.75rem; color: #1565c0; margin-top: 2px;">Pay Now</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
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
                    
                    <?php if (in_array($order['status'], ['pending', 'ready'])): ?>
                    <div style="background: #f0f9ff; border: 1px dashed #0c4a6e; border-radius: 8px; padding: 12px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #0369a1; letter-spacing: 0.5px;">Delivery OTP</span>
                        </div>
                        <div style="font-family: monospace; font-size: 1.5rem; font-weight: 700; color: #0284c7; letter-spacing: 4px;">
                            <?php echo $order['delivery_otp'] ? htmlspecialchars($order['delivery_otp']) : '------'; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
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
