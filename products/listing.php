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
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 100;
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

// Apply price filter
$query .= " AND p.price <= $max_price";

// Apply dietary filter
if (!empty($selected_dietary)) {
    foreach ($selected_dietary as $diet) {
        $diet = $conn->real_escape_string($diet);
        $query .= " AND p.dietary_tags LIKE '%$diet%'";
    }
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

<div class="listing-container">
    <div class="container">
        <!-- Header Banner -->
        <div class="listing-header">
            <h1>Order by 2 PM for <span style="color: var(--color-primary);">same-day pickup</span> at<br>Student Union.</h1>
            <p>Browse our fresh produce and quality groceries. Order now for pickup today.</p>
            <a href="#products" class="btn btn-primary">View These Items →</a>
        </div>
        
        <!-- Main Content -->
        <div class="listing-content">
            <!-- Sidebar Filters -->
            <aside class="filters-sidebar">
                <!-- Categories -->
                <div class="filter-section">
                    <h3>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Categories
                    </h3>
                    <div class="filter-options">
                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                            <div class="filter-option">
                                <input 
                                    type="checkbox" 
                                    id="cat_<?php echo $category['id']; ?>" 
                                    value="<?php echo $category['id']; ?>"
                                    class="category-filter"
                                    <?php echo in_array($category['id'], $selected_categories) ? 'checked' : ''; ?>
                                >
                                <label for="cat_<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </label>
                                <span class="count"><?php echo $category_counts[$category['id']] ?? 0; ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <!-- Price Range -->
                <div class="filter-section">
                    <h3>Price Range</h3>
                    <div class="price-range-container">
                        <input 
                            type="range" 
                            class="price-range" 
                            min="0" 
                            max="100" 
                            value="<?php echo $max_price; ?>"
                            id="priceRange"
                        >
                        <div class="price-labels">
                            <span>Min: ₹0</span>
                            <span>Max: ₹<span id="maxPriceValue"><?php echo $max_price; ?></span></span>
                        </div>
                    </div>
                </div>
                
                <!-- Dietary Needs -->
                <div class="filter-section">
                    <h3>Dietary Needs</h3>
                    <div class="filter-options">
                        <div class="filter-option">
                            <input 
                                type="checkbox" 
                                id="diet_vegan" 
                                value="Vegan"
                                class="dietary-filter"
                                <?php echo in_array('Vegan', $selected_dietary) ? 'checked' : ''; ?>
                            >
                            <label for="diet_vegan">Vegan</label>
                        </div>
                        <div class="filter-option">
                            <input 
                                type="checkbox" 
                                id="diet_vegetarian" 
                                value="Vegetarian"
                                class="dietary-filter"
                                <?php echo in_array('Vegetarian', $selected_dietary) ? 'checked' : ''; ?>
                            >
                            <label for="diet_vegetarian">Vegetarian</label>
                        </div>
                        <div class="filter-option">
                            <input 
                                type="checkbox" 
                                id="diet_organic" 
                                value="Organic"
                                class="dietary-filter"
                                <?php echo in_array('Organic', $selected_dietary) ? 'checked' : ''; ?>
                            >
                            <label for="diet_organic">Organic</label>
                        </div>
                    </div>
                </div>
            </aside>
            
            <!-- Products Grid -->
            <section class="products-section" id="products">
                <div class="products-header">
                    <h2>Fresh Produce</h2>
                    <div class="sort-dropdown">
                        <label for="sortBy" class="text-sm text-secondary">Sort by:</label>
                        <select id="sortBy" onchange="window.location.href='?sort='+this.value">
                            <option value="name" <?php echo $sort_by == 'name' ? 'selected' : ''; ?>>Popularity</option>
                            <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        </select>
                    </div>
                </div>
                
                <div class="products-grid">
                    <?php if ($products_result && $products_result->num_rows > 0): ?>
                        <?php while ($product = $products_result->fetch_assoc()): ?>
                            <div class="product-card">
                                <?php if ($product['is_sale']): ?>
                                    <span class="product-card-badge badge-sale">Sale 20%</span>
                                <?php endif; ?>
                                
                                <div class="product-card-image">
                                    <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         onerror="this.onerror=null; this.src='../images/placeholder.jpg'">
                                </div>
                                
                                <div class="product-card-content">
                                    <div class="product-card-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                    <h3 class="product-card-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    
                                    <div class="product-card-footer">
                                        <div class="product-card-price">
                                            <span class="price-current">₹<?php echo number_format($product['price'], 2); ?></span>
                                            <?php if ($product['original_price']): ?>
                                                <span class="price-original">₹<?php echo number_format($product['original_price'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <button 
                                            class="btn-icon add-to-cart-btn" 
                                            data-product-id="<?php echo $product['id']; ?>"
                                            title="Add to cart"
                                        >
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: var(--spacing-3xl);">
                            <p class="text-secondary">No products found matching your filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <button class="pagination-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <button class="pagination-btn active">1</button>
                    <button class="pagination-btn">2</button>
                    <button class="pagination-btn">3</button>
                    <button class="pagination-btn">12</button>
                    <button class="pagination-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
    // Update price range display
    const priceRange = document.getElementById('priceRange');
    const maxPriceValue = document.getElementById('maxPriceValue');
    
    function updateSliderFill(slider) {
        if (!slider) return;
        const val = slider.value;
        const min = slider.min ? parseFloat(slider.min) : 0;
        const max = slider.max ? parseFloat(slider.max) : 100;
        const percentage = ((val - min) / (max - min)) * 100;
        
        slider.style.background = `linear-gradient(to right, var(--color-primary) 0%, var(--color-primary) ${percentage}%, var(--color-border) ${percentage}%, var(--color-border) 100%)`;
    }

    if (priceRange) {
        // Initial fill update
        updateSliderFill(priceRange);
        
        priceRange.addEventListener('input', function() {
            maxPriceValue.textContent = this.value;
            updateSliderFill(this);
        });
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
