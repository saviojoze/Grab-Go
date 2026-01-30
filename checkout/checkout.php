<?php
$page_title = 'Checkout - Grab & Go';
$extra_css = 'css/checkout.css';

require_once __DIR__ . '/../config.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$user_id = get_user_id();

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.image_url, p.unit 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = $user_id";
$cart_result = $conn->query($cart_query);

// Calculate totals
$subtotal = 0;
$cart_items = [];
while ($item = $cart_result->fetch_assoc()) {
    $item['total'] = $item['price'] * $item['quantity'];
    $subtotal += $item['total'];
    $cart_items[] = $item;
}

// Redirect if cart is empty
if (count($cart_items) === 0) {
    redirect('../cart/cart.php');
}

$discount = 0;
$delivery = 0;
$total = $subtotal - $discount + $delivery;

// Get user info
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="checkout-container">
    <div class="container">
        <!-- Header -->
        <div class="checkout-header">
            <div class="breadcrumb">
                <a href="<?php echo BASE_URL; ?>products/listing.php">Home</a>
                <span>/</span>
                <a href="<?php echo BASE_URL; ?>cart/cart.php">Cart</a>
                <span>/</span>
                <span>Checkout</span>
            </div>
            <h1>Checkout</h1>
            <p class="text-secondary">Complete your order. Please check all details.</p>
        </div>
        
        <div class="checkout-content">
            <!-- Checkout Form -->
            <form action="process-order.php" method="POST" class="checkout-form" data-validate>
                <!-- Order Method -->
                <div class="checkout-section">
                    <div class="checkout-section-header">
                        <div class="section-number">1</div>
                        <h2>Order Method</h2>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 4px;">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                Pickup Date
                            </label>
                            <input 
                                type="date" 
                                name="pickup_date" 
                                class="form-input" 
                                required
                                min="<?php echo date('Y-m-d'); ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 4px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                Time Slot
                            </label>
                            <select name="pickup_time" class="form-select" required>
                                <option value="">Select time</option>
                                <option value="09:00:00">9:00 AM - 10:00 AM</option>
                                <option value="10:00:00">10:00 AM - 11:00 AM</option>
                                <option value="11:00:00">11:00 AM - 12:00 PM</option>
                                <option value="12:00:00">12:00 PM - 1:00 PM</option>
                                <option value="13:00:00">1:00 PM - 2:00 PM</option>
                                <option value="14:00:00">2:00 PM - 3:00 PM</option>
                                <option value="15:00:00">3:00 PM - 4:00 PM</option>
                                <option value="16:00:00">4:00 PM - 5:00 PM</option>
                                <option value="17:00:00">5:00 PM - 6:00 PM</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="checkout-section">
                    <div class="checkout-section-header">
                        <div class="section-number">2</div>
                        <h2>Contact Information</h2>
                    </div>
                    
                    <div class="form-row single">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input 
                                type="text" 
                                name="contact_name" 
                                class="form-input" 
                                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input 
                                type="email" 
                                name="contact_email" 
                                class="form-input" 
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input 
                                type="tel" 
                                name="contact_phone" 
                                class="form-input" 
                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                required
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="checkout-section">
                    <div class="checkout-section-header">
                        <div class="section-number">3</div>
                        <h2>Payment Method</h2>
                    </div>
                    
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="cash" value="cash" checked>
                            <label for="cash">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                                <span>Cash</span>
                            </label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="card" value="card">
                            <label for="card">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                                <span>Card</span>
                            </label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="online" value="online">
                            <label for="online">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                                <span>Pay Online</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Place an Order →
                </button>
            </form>
            
            <!-- Order Summary -->
            <div class="checkout-summary">
                <h2>Order Summary</h2>
                
                <!-- Items -->
                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <div class="summary-item-image">
                            <img src="<?php echo BASE_URL . ($item['image_url'] ?? 'images/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 onerror="this.src='<?php echo BASE_URL; ?>images/placeholder.jpg'">
                        </div>
                        <div class="summary-item-details">
                            <div class="summary-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="summary-item-quantity">Qty: <?php echo $item['quantity']; ?> <?php echo htmlspecialchars($item['unit'] ?? 'units'); ?></div>
                        </div>
                        <div class="summary-item-price">₹<?php echo number_format($item['total'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Totals -->
                <div class="summary-totals">
                    <div class="summary-row">
                        <span class="label">Subtotal</span>
                        <span class="value">₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="label">Discount</span>
                        <span class="value">-₹<?php echo number_format($discount, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="label">Delivery Fee</span>
                        <span class="value">₹<?php echo number_format($delivery, 2); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span class="label">Total</span>
                        <span class="value">₹<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
