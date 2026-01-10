<?php
$page_title = 'Your Shopping Cart - Grab & Go';
$extra_css = 'css/cart.css';

require_once __DIR__ . '/../config.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$user_id = get_user_id();

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.image_url, p.stock, cat.name as category_name 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               LEFT JOIN categories cat ON p.category_id = cat.id
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

$discount = 0;
$delivery = 0;
$total = $subtotal - $discount + $delivery;

// Get recommendations
$recommendations_query = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
$recommendations_result = $conn->query($recommendations_query);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="cart-container">
    <div class="container">
        <!-- Header -->
        <div class="cart-header">
            <div class="breadcrumb">
                <a href="<?php echo BASE_URL; ?>products/listing.php">Home</a>
                <span>/</span>
                <a href="<?php echo BASE_URL; ?>products/listing.php">Shop</a>
                <span>/</span>
                <span>Shopping Cart</span>
            </div>
            <h1>Your Shopping Cart</h1>
        </div>
        
        <?php if (count($cart_items) > 0): ?>
            <div class="cart-content">
                <!-- Cart Items -->
                <div>
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <img src="<?php echo BASE_URL . ($item['image_url'] ?? 'images/placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         onerror="this.src='<?php echo BASE_URL; ?>images/placeholder.jpg'">
                                </div>
                                
                                <div class="cart-item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <div class="cart-item-category"><?php echo htmlspecialchars($item['category_name']); ?></div>
                                    <div class="cart-item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                                </div>
                                
                                <div class="quantity-control" data-cart-id="<?php echo $item['id']; ?>">
                                    <button class="quantity-btn quantity-minus">-</button>
                                    <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                                    <button class="quantity-btn quantity-plus">+</button>
                                </div>
                                
                                <button 
                                    class="cart-item-remove" 
                                    onclick="Cart.removeItem(<?php echo $item['id']; ?>)"
                                    title="Remove item"
                                >
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Recommendations -->
                    <div class="recommendations">
                        <h2>You might also need</h2>
                        <div class="recommendations-grid">
                            <?php while ($rec = $recommendations_result->fetch_assoc()): ?>
                                <div class="recommendation-card">
                                    <div class="recommendation-card-image">
                                        <img src="<?php echo BASE_URL . ($rec['image_url'] ?? 'images/placeholder.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($rec['name']); ?>"
                                             onerror="this.src='<?php echo BASE_URL; ?>images/placeholder.jpg'">
                                        <button 
                                            class="recommendation-card-add add-to-cart-btn" 
                                            data-product-id="<?php echo $rec['id']; ?>"
                                        >
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="recommendation-card-content">
                                        <div class="recommendation-card-title"><?php echo htmlspecialchars($rec['name']); ?></div>
                                        <div class="recommendation-card-price">₹<?php echo number_format($rec['price'], 2); ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    
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
                    
                    <a href="<?php echo BASE_URL; ?>checkout/checkout.php" class="btn btn-primary btn-block btn-lg" style="margin-top: var(--spacing-lg);">
                        Proceed to checkout →
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>products/listing.php" class="return-shopping">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Return to shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <h2>Your cart is empty</h2>
                <p>Add some products to get started!</p>
                <a href="<?php echo BASE_URL; ?>products/listing.php" class="btn btn-primary btn-lg">
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
