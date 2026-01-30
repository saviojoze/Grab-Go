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
// Get order items with product details
$stmt = $conn->prepare("
    SELECT oi.*, p.image_url, p.unit, p.description 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$items_result = $stmt->get_result();

$delivery_otp = $order['delivery_otp'] ?? '------';

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
        </div>
        
        <div class="confirmation-content">
                <h1>Order Confirmed!</h1>
                <p class="text-secondary">Thank you for your order. We have received it.</p>
                
                <?php if($order['payment_method'] === 'online'): ?>
                    <div style="margin-top: 15px;">
                        <?php if($order['payment_status'] === 'paid'): ?>
                            <span class="badge badge-success">Payment Successful</span>
                        <?php elseif($order['payment_status'] === 'failed'): ?>
                            <span class="badge badge-danger">Payment Failed</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Payment Pending</span>
                            <div style="margin-top: 10px;">
                                <a href="../checkout/pay-online.php?order=<?php echo $order_number; ?>" class="btn btn-primary btn-sm">Complete Payment</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <!-- Pickup Information -->
            <div class="pickup-info">
                <h2>Pickup Time</h2>
                
                <!-- QR Code & PIN -->
                <div class="qr-code-section" style="text-align: center;">
                    <div class="qr-code" style="margin-bottom: 5px;">
                        <?php 
                            $qr_data = json_encode([
                                'order_id' => $order_number,
                                'otp' => $delivery_otp,
                                'user' => $_SESSION['full_name'] ?? 'Customer'
                            ]);
                            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($qr_data);
                        ?>
                        <img src="<?php echo $qr_url; ?>" alt="Order QR" style="border-radius: var(--radius-md); border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                    </div>
                    
                    <div class="verification-pin-box" style="background: #e3f2fd; border: 2px dashed #1565c0; padding: 10px; border-radius: 8px; margin: 15px auto; max-width: 200px;">
                        <p class="text-xs text-secondary" style="margin-bottom: 4px; font-weight: 600; text-transform: uppercase;">Delivery OTP</p>
                        <div style="font-size: 24px; font-weight: 800; letter-spacing: 5px; color: #1565c0; line-height: 1;">
                            <?php echo htmlspecialchars($delivery_otp); ?>
                        </div>
                    </div>
                    
                    <p class="text-sm text-secondary">Present this QR code or PIN at pickup</p>
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
                <!-- Items -->
                <div class="product-cards-container" style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 25px;">
                    <?php while ($item = $items_result->fetch_assoc()): 
                        $imgUrl = $item['image_url'] ?? 'images/placeholder.jpg';
                        if (strpos($imgUrl, 'http') !== 0) {
                            $imgUrl = BASE_URL . $imgUrl;
                        }
                    ?>
                        <div class="product-card" style="display: flex; background: #fff; border: 1px solid #e0e0e0; border-radius: 4px; padding: 12px; gap: 16px; align-items: start;">
                            <!-- Image -->
                            <div style="width: 80px; height: 80px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 1px solid #f0f0f0; border-radius: 4px;">
                                <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            
                            <!-- Middle Content -->
                            <div style="flex: 1; padding-top: 4px;">
                                <h3 style="margin: 0 0 4px 0; font-size: 15px; color: #212121; font-weight: 500; line-height: 1.3;">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </h3>
                                <div style="font-size: 12px; color: #878787; margin-bottom: 6px; line-height: 1.4;">
                                    <?php 
                                        $desc = strip_tags($item['description']);
                                        if (strlen($desc) > 80) $desc = substr($desc, 0, 80) . '...';
                                        echo htmlspecialchars($desc);
                                    ?>
                                </div>
                                <div style="font-size: 13px; color: #212121;">
                                    Qty: <b><?php echo $item['quantity']; ?></b>
                                </div>
                            </div>
                            
                            <!-- Right Content: Price & Button -->
                            <div style="text-align: right; min-width: 100px; padding-top: 4px;">
                                <div style="font-size: 18px; font-weight: 600; color: #212121; margin-bottom: 8px;">
                                    ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                                
                                <a href="../products/details.php?id=<?php echo $item['product_id']; ?>" 
                                   style="display: inline-block; background: #2874f0; color: white; padding: 8px 16px; text-decoration: none; font-size: 12px; font-weight: 600; border-radius: 2px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
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
