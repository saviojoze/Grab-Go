<?php
require_once __DIR__ . '/../config.php';

// Get cart count for current user
$cart_count = 0;
if (is_logged_in()) {
    $user_id = get_user_id();
    $cart_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id";
    $cart_result = $conn->query($cart_query);
    if ($cart_result && $row = $cart_result->fetch_assoc()) {
        $cart_count = $row['total'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Grab & Go - Smart Supermarket'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/design-system.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/components.css">
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL . $extra_css; ?>">
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-main flex items-center justify-between">
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>products/listing.php" class="logo">
                    <div class="logo-icon"></div>
                    <span>GRAB & GO</span>
                </a>
                
                <!-- Navigation -->
                <nav>
                    <ul class="nav-menu">
                        <li><a href="<?php echo BASE_URL; ?>products/listing.php" class="nav-link <?php echo ($current_page ?? '') == 'shop' ? 'active' : ''; ?>">Shop</a></li>
                        <li><a href="<?php echo BASE_URL; ?>orders/my-orders.php" class="nav-link <?php echo ($current_page ?? '') == 'my-orders' ? 'active' : ''; ?>">My Orders</a></li>
                        <li><a href="#" class="nav-link">Locations</a></li>
                        <?php if (is_logged_in()): ?>
                            <li><a href="<?php echo BASE_URL; ?>auth/logout.php" class="nav-link">Logout</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <!-- Right Side Icons -->
                <div class="flex items-center gap-lg">
                    <?php if (is_logged_in()): ?>
                        <div class="cart-icon-wrapper">
                            <a href="<?php echo BASE_URL; ?>cart/cart.php">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                <?php if ($cart_count > 0): ?>
                                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        
                        <div class="user-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-primary">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main>
