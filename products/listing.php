<?php
$page_title = 'Fresh Produce - Grab & Go';
$current_page = 'shop';
$extra_css = 'css/products.css';

require_once __DIR__ . '/../config.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    redirect('../auth/login.php');
}

// Get filter parameters
$selected_categories = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];
$selected_dietary = isset($_GET['dietary']) ? explode(',', $_GET['dietary']) : [];
$sort_by = $_GET['sort'] ?? 'name';
$sort_by = $_GET['sort'] ?? 'name';

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";

// Apply category filter
if (!empty($selected_categories)) {
    $cat_ids = implode(',', array_map('intval', $selected_categories));
    $query .= " AND p.category_id IN ($cat_ids)";
}

// Apply dietary filter
if (!empty($selected_dietary)) {
    foreach ($selected_dietary as $diet) {
        $diet = $conn->real_escape_string($diet);
        $query .= " AND p.dietary_tags LIKE '%$diet%'";
    }
}

// Apply search filter
if (!empty($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $query .= " AND (p.name LIKE '%$search_term%' OR p.description LIKE '%$search_term%')";
}

// Apply sorting
switch ($sort_by) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name':
    default:
        $query .= " ORDER BY p.name ASC";
        break;
}

$products_result = $conn->query($query);

// Get all categories for filter
$categories_result = $conn->query("SELECT * FROM categories ORDER BY display_order");

// Get product counts per category
$counts_query = "SELECT category_id, COUNT(*) as count FROM products GROUP BY category_id";
$counts_result = $conn->query($counts_query);
$category_counts = [];
if ($counts_result) {
    while ($row = $counts_result->fetch_assoc()) {
        $category_counts[$row['category_id']] = $row['count'];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="shop-container">
    <!-- Hero Section -->
    <div class="shop-hero">
        <div class="shop-hero-bg">
            <!-- decorative background -->
        </div>
        <div class="shop-hero-content container">
            <h1 class="shop-title">GRAB & GO</h1>
           
            
            <form class="shop-search" action="" method="GET">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="search" placeholder="Search " value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>
    </div>

    <style>
        /* Force sidebar styles */
        .shop-sidebar .category-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .shop-sidebar .category-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #555;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s ease;
            width: 100%;
            box-sizing: border-box; /* Ensure padding doesn't affect width */
        }
        .shop-sidebar .category-link:hover {
            background-color: #FAFAFA;
            color: #000;
        }
        .shop-sidebar .category-link.active {
            background-color: #F5F5F7;
            color: #000;
            font-weight: 600;
        }
        .shop-sidebar .cat-icon-box {
            width: 28px; /* Fixed width for alignment */
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.25rem;
            flex-shrink: 0; /* Prevent shrinking */
            line-height: 1;
        }
        .shop-sidebar .category-name {
             flex: 1; /* Take remaining space */
        }
        .shop-sidebar .badge-count {
            background: transparent;
            color: #666;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .shop-sidebar .category-link.active .badge-count {
            background: #000;
            color: #fff;
        }
    </style>

    <div class="container shop-layout">
        <!-- Sidebar -->
        <aside class="shop-sidebar">
            <div class="sidebar-section">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; padding-left: 8px; color: #1c1c1c;">Category</h3>
                <div class="category-nav">
                    <a href="listing.php" class="category-link <?php echo empty($selected_categories) ? 'active' : ''; ?>">
                        <div class="cat-icon-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        </div>
                        <span class="category-name">All Products</span>
                        <span class="badge-count"><?php echo array_sum($category_counts); ?></span>
                    </a>
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                        <?php 
                        $isActive = in_array($category['id'], $selected_categories);
                        $linkUrl = $isActive ? 'listing.php' : 'listing.php?categories=' . $category['id'];
                        ?>
                        <a href="<?php echo $linkUrl; ?>" class="category-link <?php echo $isActive ? 'active' : ''; ?>">
                            <div class="cat-icon-box">
                                <span style="color: #4A90E2; font-size: 14px;">◆</span>
                            </div>
                            <span class="category-name"><?php echo htmlspecialchars($category['name']); ?></span>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </aside>

        <!-- Product Grid -->
        <main class="shop-main">
            <div class="shop-header-row">
                <div class="breadcrumbs">
                    <span>Home</span> <span class="divider">/</span> <span>Shop</span>
                </div>
                <div class="sort-wrapper">
                    <select id="sortBy" onchange="window.location.href='?sort='+this.value">
                        <option value="name">Sort by: Popularity</option>
                        <option value="price_low">Sort by: Price Low</option>
                        <option value="price_high">Sort by: Price High</option>
                    </select>
                </div>
            </div>

            <div class="shop-grid">
                <?php if ($products_result && $products_result->num_rows > 0): ?>
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                        <div class="shop-card">
                            <div class="card-header">
                                <span class="category-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                <?php if ($product['is_sale']): ?>
                                    <span class="sale-badge">Sale</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-image">
                                <?php 
                                $imgUrl = $product['image_url'] ?? 'images/placeholder.jpg';
                                if (strpos($imgUrl, 'http') === 0) {
                                    $displayImg = $imgUrl;
                                } else {
                                    // Use BASE_URL if defined, otherwise relative
                                    $base = defined('BASE_URL') ? BASE_URL : '../';
                                    $displayImg = $base . $imgUrl;
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($displayImg); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     onerror="this.onerror=null; this.src='<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>images/placeholder.jpg'">
                            </div>

                            <div class="card-body">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="rating">
                                    <span class="stars">★★★★★</span>
                                    <span class="reviews">(<?php echo rand(10, 150); ?> Reviews)</span>
                                </div>
                                <div class="price">₹<?php echo number_format($product['price'], 2); ?></div>
                            </div>

                            <div class="card-footer">
                                <button class="btn-shop btn-ghost add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                    Add to Cart
                                </button>
                                <button class="btn-shop btn-dark">
                                    Buy Now
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-results">
                        <p>No products found matching your criteria.</p>
                        <a href="listing.php" class="btn-link">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
