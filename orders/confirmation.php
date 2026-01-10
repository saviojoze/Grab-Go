<?php
$page_title = 'Order Confirmed - Grab & Go';
$extra_css = 'css/confirmation.css';

require_once __DIR__ . '/../config.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$order_number = $_GET['order'] ?? '';

if (empty($order_number)) {
    redirect('../products/listing.php');
}

$user_id = get_user_id();

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->bind_param("si", $order_number, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    redirect('../products/listing.php');
}

$order = $order_result->fetch_assoc();

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$items_result = $stmt->get_result();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="confirmation-container">
    <div class="container">
        <!-- Success Message -->
        <div class="confirmation-success">
            <div class="success-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h1>Order Confirmed!</h1>
            <p>Thank you for choosing our service! Your order <span class="order-number">#<?php echo htmlspecialchars($order_number); ?></span><br>
            will be <strong>READY</strong> for your next visit immediately.</p>
        </div>
        
        <div class="confirmation-content">
            <!-- Pickup Information -->
            <div class="pickup-info">
                <h2>Pickup Time</h2>
                
                <!-- QR Code -->
                <div class="qr-code-section">
                    <div class="qr-code">
                        <div class="qr-code-placeholder"></div>
                    </div>
                    <p class="text-sm text-secondary">Scan this QR code at pickup</p>
                </div>
                
                <!-- Date & Time -->
                <div class="pickup-datetime">
                    <div class="datetime-item">
                        <div class="datetime-label">Date</div>
                        <div class="datetime-value">
                            <?php 
                                $date = new DateTime($order['pickup_date']);
                                echo $date->format('M d, Y');
                            ?>
                        </div>
                    </div>
                    <div class="datetime-item">
                        <div class="datetime-label">Time</div>
                        <div class="datetime-value">
                            <?php 
                                $time = new DateTime($order['pickup_time']);
                                echo $time->format('g:i A');
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="window.print()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        Print Proof
                    </button>
                    <a href="../orders/my-orders.php" class="btn btn-secondary">
                        See Order
                    </a>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary-section">
                <h2>Order Summary</h2>
                
                <!-- Items -->
                <?php while ($item = $items_result->fetch_assoc()): ?>
                    <div class="summary-item">
                        <div class="summary-item-details">
                            <div class="summary-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <div class="summary-item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div class="summary-item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                <?php endwhile; ?>
                
                <!-- Totals -->
                <div class="summary-totals">
                    <div class="summary-row">
                        <span class="label">Subtotal</span>
                        <span class="value">₹<?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="label">Discount</span>
                        <span class="value">-₹<?php echo number_format($order['discount'], 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="label">Delivery Fee</span>
                        <span class="value">₹<?php echo number_format($order['delivery_fee'], 2); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span class="label">Total</span>
                        <span class="value">₹<?php echo number_format($order['total'], 2); ?></span>
                    </div>
                </div>
                
                <a href="#" class="download-receipt">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download Receipt
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
