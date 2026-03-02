<?php
require_once __DIR__ . '/../config.php';

// Get user info and cart count
$cart_count = 0;
$user_data = null;
if (is_logged_in()) {
    $user_id = get_user_id();
    
    // User data
    $user_query = "SELECT full_name, profile_picture FROM users WHERE id = $user_id";
    $user_result = $conn->query($user_query);
    if ($user_result) {
        $user_data = $user_result->fetch_assoc();
    }
    
    // Cart count
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/design-system.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/components.css?v=<?php echo time(); ?>">
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL . $extra_css; ?>?v=<?php echo time(); ?>">
    <?php endif; ?>
    <script>
        window.GRAB_AND_GO_BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
</head>
<body>
    <header class="header">
        <div class="container-fluid">
            <div class="header-main flex items-center justify-between">
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>products/listing.php" class="logo">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span>GRAB & GO</span>
                </a>
                
                <!-- Right Side Icons -->
                <div class="header-actions flex items-center gap-lg">
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
                        
                        <div class="flex items-center gap-md">
                            <a href="<?php echo BASE_URL; ?>profile/view.php" class="user-profile-link">
                                <div class="user-avatar-small">
                                    <?php if ($user_data && $user_data['profile_picture']): ?>
                                        <img src="<?php echo BASE_URL . $user_data['profile_picture']; ?>" alt="Profile" class="avatar-img">
                                    <?php else: ?>
                                        <div class="avatar-letter">
                                            <?php echo strtoupper(substr($user_data['full_name'] ?? 'U', 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                            
                            <a href="<?php echo BASE_URL; ?>auth/logout.php" class="logout-link" title="Logout">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white opacity-70 hover:opacity-100">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-primary">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Secondary Navigation Bar -->
    <div class="secondary-nav">
        <div class="container-fluid">
            <nav class="flex justify-end">
                <ul class="nav-menu">
                    <li><a href="<?php echo BASE_URL; ?>products/listing.php" class="nav-link-secondary <?php echo ($current_page ?? '') == 'shop' ? 'active' : ''; ?>">Shop</a></li>
                    <li><a href="<?php echo BASE_URL; ?>orders/my-orders.php" class="nav-link-secondary <?php echo ($current_page ?? '') == 'my-orders' ? 'active' : ''; ?>">My Orders</a></li>
                    <li><a href="<?php echo BASE_URL; ?>locations.php" class="nav-link-secondary <?php echo ($current_page ?? '') == 'locations' ? 'active' : ''; ?>">Locations</a></li>
                </ul>
            </nav>
        </div>
    </div>
    
    <main>
