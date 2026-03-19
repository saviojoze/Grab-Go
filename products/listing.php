<?php
$page_title = 'Shop - Grab & Go Supermarket';
$current_page = 'shop';
$extra_css = 'css/products.css';

require_once __DIR__ . '/../config.php';

if (!is_logged_in()) {
    redirect('../auth/login.php');
}

// ── Filters ──────────────────────────────────
$selected_categories = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];
$sort_by             = $_GET['sort'] ?? 'default';
$search_term         = trim($_GET['search'] ?? '');
$avail_filter        = $_GET['avail'] ?? ''; // 'in' | 'out'
$promo_filter        = $_GET['promo'] ?? ''; // 'sale' | 'bestseller'
$min_price           = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price           = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 500;

// ── Query ─────────────────────────────────────
$query = "SELECT p.*, c.name as category_name
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE 1=1";

if (!empty($selected_categories)) {
    $cat_ids = implode(',', array_map('intval', $selected_categories));
    $query .= " AND p.category_id IN ($cat_ids)";
}
if ($avail_filter === 'in')  $query .= " AND p.stock > 0";
if ($avail_filter === 'out') $query .= " AND p.stock = 0";
if ($promo_filter === 'sale') $query .= " AND p.original_price > p.price";

if ($max_price > 0 && $max_price < 500) {
    if ($min_price > 0) $query .= " AND p.price BETWEEN $min_price AND $max_price";
    else $query .= " AND p.price <= $max_price";
} elseif ($min_price > 0) {
    $query .= " AND p.price >= $min_price";
}

if ($search_term !== '') {
    $st = $conn->real_escape_string($search_term);
    $query .= " AND (p.name LIKE '%$st%' OR p.description LIKE '%$st%')";
}

switch ($sort_by) {
    case 'price_low':  $query .= " ORDER BY p.price ASC"; break;
    case 'price_high': $query .= " ORDER BY p.price DESC"; break;
    default:           $query .= " ORDER BY p.name ASC";
}

$products_result   = $conn->query($query);
$total_found       = $products_result ? $products_result->num_rows : 0;
$categories_result = $conn->query("SELECT * FROM categories ORDER BY display_order");

// stats for sidebar
$total_all   = (int)$conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$in_stock_n  = (int)$conn->query("SELECT COUNT(*) as c FROM products WHERE stock > 0")->fetch_assoc()['c'];
$out_stock_n = (int)$conn->query("SELECT COUNT(*) as c FROM products WHERE stock = 0")->fetch_assoc()['c'];

// category counts
$cat_counts = [];
$cr = $conn->query("SELECT category_id, COUNT(*) as cnt FROM products GROUP BY category_id");
if ($cr) while ($r = $cr->fetch_assoc()) $cat_counts[$r['category_id']] = $r['cnt'];

// active cat name
$active_cat_name = '';
if (!empty($selected_categories)) {
    $r = $conn->query("SELECT name FROM categories WHERE id = " . intval($selected_categories[0]));
    if ($r) $active_cat_name = $r->fetch_assoc()['name'] ?? '';
}

require_once __DIR__ . '/../includes/header.php';

$cat_icons = [
    'Fruits'=>'🍎','Vegetables'=>'🥕','Dairy'=>'🥛','Bakery'=>'🍞',
    'Meat'=>'🥩','Snacks'=>'🍿','Beverages'=>'☕','Drinks'=>'🧃',
    'Frozen'=>'🧊','Cleaning'=>'🧹','Health'=>'💊','Organic'=>'🌿',
    'Fresh Produce'=>'🥬','Produce'=>'🥬','Juices'=>'🍹','Cakes'=>'🍰',
];
?>

<div class="ref-shop">



    <!-- ── PROMO BANNER ─────────────────────── -->
    <div class="bnr-wrap">
        <div class="bnr-slider" id="bnrSlider">

            <!-- Slide 1 — Browse & Pick Up -->
            <div class="bnr-slide bnr-s1">
                <div class="bnr-inner">
                    <div class="bnr-text-col">
                        <span class="bnr-tag">🛒 Smart Shopping</span>
                        <h2 class="bnr-headline">Browse Online,<br><em>Pick Up In Store.</em></h2>
                        <p class="bnr-desc">Shop from our full range online, then collect your order at the counter — no waiting in long checkout queues.</p>
                        <a href="#products-main" class="bnr-cta">Start Shopping →</a>
                    </div>
                    <div class="bnr-visual-col">
                        <div class="bnr-emoji-stack">
                            <span class="bnr-e bnr-e1">🏪</span>
                            <span class="bnr-e bnr-e2">🛒</span>
                            <span class="bnr-e bnr-e3">📱</span>
                            <span class="bnr-e bnr-e4">✅</span>
                        </div>
                        <div class="bnr-badge-float">QUICK<br><strong>PICKUP</strong></div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 — Skip the Queue -->
            <div class="bnr-slide bnr-s2">
                <div class="bnr-inner">
                    <div class="bnr-text-col">
                        <span class="bnr-tag">⚡ Skip the Line</span>
                        <h2 class="bnr-headline">Order Ahead,<br><em>Skip the Queue.</em></h2>
                        <p class="bnr-desc">Place your order in advance, walk straight to the counter, and collect your items — zero wait time.</p>
                        <a href="#products-main" class="bnr-cta bnr-cta-alt">Order Now →</a>
                    </div>
                    <div class="bnr-visual-col">
                        <div class="bnr-emoji-stack">
                            <span class="bnr-e bnr-e1">⏱️</span>
                            <span class="bnr-e bnr-e2">🏃</span>
                            <span class="bnr-e bnr-e3">🎫</span>
                            <span class="bnr-e bnr-e4">🛍️</span>
                        </div>
                        <div class="bnr-badge-float bnr-badge-orange">NO<br><strong>QUEUES</strong></div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 — Weekend Special -->
            <div class="bnr-slide bnr-s3">
                <div class="bnr-inner">
                    <div class="bnr-text-col">
                        <span class="bnr-tag">🎉 Weekend Deals</span>
                        <h2 class="bnr-headline">Fill Your Cart<br><em>&amp; Save Big.</em></h2>
                        <p class="bnr-desc">Grab more, pay less! Enjoy up to 25% off on your in-store pickup orders this weekend.</p>
                        <a href="listing.php?promo=sale" class="bnr-cta bnr-cta-gold">View Offers →</a>
                    </div>
                    <div class="bnr-visual-col">
                        <div class="bnr-emoji-stack">
                            <span class="bnr-e bnr-e1">🧃</span>
                            <span class="bnr-e bnr-e2">🍿</span>
                            <span class="bnr-e bnr-e3">🥛</span>
                            <span class="bnr-e bnr-e4">🍞</span>
                        </div>
                        <div class="bnr-badge-float bnr-badge-gold">UP TO<br><strong>25% OFF</strong></div>
                    </div>
                </div>
            </div>

        </div><!-- /slider -->

        <!-- Controls -->
        <button class="bnr-arrow bnr-prev" type="button" onclick="bnrPrev()" aria-label="Previous">‹</button>
        <button class="bnr-arrow bnr-next" type="button" onclick="bnrNext()" aria-label="Next">›</button>

        <!-- Dots -->
        <div class="bnr-dots">
            <button class="bnr-dot bnr-dot-active" type="button" onclick="bnrGoManual(0)" id="bnrDot0"></button>
            <button class="bnr-dot" type="button" onclick="bnrGoManual(1)" id="bnrDot1"></button>
            <button class="bnr-dot" type="button" onclick="bnrGoManual(2)" id="bnrDot2"></button>
        </div>
    </div>

    <!-- ── BODY: SIDEBAR + PRODUCTS ─────── -->
    <div class="ref-body container" id="products-main">

        <!-- ═══════════════ SIDEBAR ═══════════════ -->
        <aside class="ref-sidebar">
            <h2 class="ref-sidebar-title">Filter Options</h2>

            <!-- By Categories -->
            <div class="ref-fblock">
                <h3 class="ref-fblock-title">By Categories</h3>
                <ul class="ref-flist">
                    <li>
                        <a href="listing.php" class="ref-flink <?php echo empty($selected_categories) && !$search_term ? 'ref-flink-active' : ''; ?>">
                            <span class="ref-cat-label">
                                <span class="ref-cat-icon">🏪</span>
                                <span class="ref-cat-text">All Products</span>
                            </span>
                            <span class="ref-fcnt">(<?php echo $total_all; ?>)</span>
                        </a>
                    </li>
                    <?php
                    $categories_result->data_seek(0);
                    while ($cat = $categories_result->fetch_assoc()):
                        $isActive = in_array($cat['id'], $selected_categories);
                        $href = $isActive
                            ? 'listing.php'
                            : 'listing.php?categories=' . $cat['id'];
                        $cnt = $cat_counts[$cat['id']] ?? 0;
                        
                        $cname = trim($cat['name']);
                        $icon = '🏷️';
                        if (preg_match('/^([^\p{L}\p{N}\s]+)\s*(.*)$/u', $cname, $matches) && !empty($matches[1])) {
                            $icon = $matches[1];
                            $cname = $matches[2] ? $matches[2] : $cname;
                        } else {
                            if (isset($cat_icons[$cname])) {
                                $icon = $cat_icons[$cname];
                            } else {
                                $lc = strtolower($cname);
                                if (strpos($lc, 'deal') !== false || strpos($lc, 'hot') !== false) $icon = '🔥';
                                else if (strpos($lc, 'electronic') !== false || strpos($lc, 'tech') !== false) $icon = '💻';
                                else if (strpos($lc, 'appliance') !== false || strpos($lc, 'home') !== false) $icon = '📺';
                                else if (strpos($lc, 'drink') !== false || strpos($lc, 'beverage') !== false || strpos($lc, 'soda') !== false) $icon = '🥤';
                                else if (strpos($lc, 'veg') !== false || strpos($lc, 'produce') !== false) $icon = '🥬';
                                else if (strpos($lc, 'fruit') !== false) $icon = '🍎';
                                else if (strpos($lc, 'cake') !== false || strpos($lc, 'bakery') !== false) $icon = '🍰';
                                else if (strpos($lc, 'dairy') !== false || strpos($lc, 'milk') !== false) $icon = '🥛';
                                else if (strpos($lc, 'juice') !== false) $icon = '🍹';
                                else if (strpos($lc, 'meat') !== false) $icon = '🥩';
                                else if (strpos($lc, 'snack') !== false) $icon = '🍿';
                                else if (strpos($lc, 'clean') !== false) $icon = '🧹';
                                else if (strpos($lc, 'frozen') !== false) $icon = '🧊';
                            }
                        }
                    ?>
                    <li>
                        <a href="<?php echo $href; ?>" class="ref-flink <?php echo $isActive ? 'ref-flink-active' : ''; ?>">
                            <span class="ref-cat-label">
                                <span class="ref-cat-icon"><?php echo $icon; ?></span>
                                <span class="ref-cat-text"><?php echo htmlspecialchars($cname); ?></span>
                            </span>
                            <span class="ref-fcnt">(<?php echo $cnt; ?>)</span>
                        </a>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <!-- Price Filter -->
            <div class="ref-fblock">
                <h3 class="ref-fblock-title">Price</h3>
                <div class="ref-price-slider-wrap" id="priceFilterWrapper">
                    <div class="ref-price-track">
                        <div class="ref-price-fill" id="rangeFill"></div>
                        <input type="range" class="rn-min" id="minPriceInput" min="0" max="500" value="<?php echo $min_price; ?>" step="10">
                        <input type="range" class="rn-max" id="maxPriceInput" min="0" max="500" value="<?php echo $max_price; ?>" step="10">
                    </div>
                    <div class="ref-price-labels" style="margin-top: 20px;">
                        <span id="minPriceLabel">₹<?php echo $min_price; ?></span>
                        <span id="maxPriceLabel"><?php echo $max_price >= 500 ? '₹500+' : '₹'.$max_price; ?></span>
                    </div>
                    <button class="btn-apply-price" onclick="applyPriceFilter()">Apply Filter</button>
                    
                    <div class="ref-price-sort-links" style="margin-top: 15px;">
                        <?php $pBase = !empty($selected_categories) ? '?categories='.implode(',', $selected_categories).'&' : '?'; ?>
                        <a href="listing.php<?php echo $pBase; ?>sort=price_low" class="ref-price-sort-btn <?php echo $sort_by==='price_low' ? 'ref-price-sort-active' : ''; ?>">Low → High</a>
                        <a href="listing.php<?php echo $pBase; ?>sort=price_high" class="ref-price-sort-btn <?php echo $sort_by==='price_high' ? 'ref-price-sort-active' : ''; ?>">High → Low</a>
                    </div>
                </div>
            </div>

            <!-- By Promotions -->
            <div class="ref-fblock">
                <h3 class="ref-fblock-title">By Promotions</h3>
                <div class="ref-radio-group">
                    <?php $promoBase = !empty($selected_categories) ? 'listing.php?categories='.implode(',', $selected_categories).'&promo=' : 'listing.php?promo='; ?>
                    <a href="listing.php<?php echo !empty($selected_categories) ? '?categories='.implode(',', $selected_categories) : ''; ?>"
                       class="ref-radio-item <?php echo !$promo_filter ? 'ref-radio-active' : ''; ?>">
                        <span class="ref-radio-dot <?php echo !$promo_filter ? 'ref-radio-dot-on' : ''; ?>"></span>
                        All Products
                    </a>
                    <a href="<?php echo $promoBase; ?>sale" class="ref-radio-item <?php echo $promo_filter==='sale' ? 'ref-radio-active' : ''; ?>">
                        <span class="ref-radio-dot <?php echo $promo_filter==='sale' ? 'ref-radio-dot-on' : ''; ?>"></span>
                        On Sale
                    </a>
                    <a href="<?php echo $promoBase; ?>bestseller" class="ref-radio-item <?php echo $promo_filter==='bestseller' ? 'ref-radio-active' : ''; ?>">
                        <span class="ref-radio-dot <?php echo $promo_filter==='bestseller' ? 'ref-radio-dot-on' : ''; ?>"></span>
                        Best Sellers
                    </a>
                </div>
            </div>

            <!-- Availability -->
            <div class="ref-fblock">
                <h3 class="ref-fblock-title">Availability</h3>
                <div class="ref-radio-group">
                    <?php $availBase = !empty($selected_categories) ? 'listing.php?categories='.implode(',', $selected_categories).'&avail=' : 'listing.php?avail='; ?>
                    <a href="<?php echo $availBase; ?>in" class="ref-radio-item <?php echo $avail_filter==='in' ? 'ref-radio-active' : ''; ?>">
                        <span class="ref-radio-dot <?php echo $avail_filter==='in' ? 'ref-radio-dot-on' : ''; ?>"></span>
                        In Stock
                        <span class="ref-fcnt">(<?php echo $in_stock_n; ?>)</span>
                    </a>
                    <a href="<?php echo $availBase; ?>out" class="ref-radio-item <?php echo $avail_filter==='out' ? 'ref-radio-active' : ''; ?>">
                        <span class="ref-radio-dot <?php echo $avail_filter==='out' ? 'ref-radio-dot-on' : ''; ?>"></span>
                        Out of Stocks
                        <span class="ref-fcnt">(<?php echo $out_stock_n; ?>)</span>
                    </a>
                </div>
            </div>

        </aside>

        <!-- ═══════════════ PRODUCT AREA ═══════════════ -->
        <div class="ref-product-area">

            <!-- Toolbar -->
            <div class="ref-toolbar">
                <p class="ref-showing">
                    Showing <strong>1–<?php echo $total_found; ?></strong> of <strong><?php echo $total_all; ?></strong> results
                </p>
                <div class="ref-toolbar-right">

                    <!-- Active filter chips -->
                    <?php if ($active_cat_name): ?>
                    <div class="ref-chip-row">
                        <span class="ref-chip">
                            <?php echo htmlspecialchars($active_cat_name); ?>
                            <a href="listing.php" class="ref-chip-x">✕</a>
                        </span>
                        <?php if ($avail_filter === 'in'): ?>
                        <span class="ref-chip">
                            In Stock
                            <a href="listing.php?categories=<?php echo implode(',', $selected_categories); ?>" class="ref-chip-x">✕</a>
                        </span>
                        <?php endif; ?>
                        <a href="listing.php" class="ref-chip-clear">Clear All</a>
                    </div>
                    <?php endif; ?>

                    <div class="ref-sort-wrap">
                        <span class="ref-sort-lbl">Sort by :</span>
                        <select class="ref-sort-sel" onchange="applySort(this.value)">
                            <option value="default"    <?php echo $sort_by==='default'    ? 'selected':'' ?>>Default Sorting</option>
                            <option value="price_low"  <?php echo $sort_by==='price_low'  ? 'selected':'' ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort_by==='price_high' ? 'selected':'' ?>>Price: High to Low</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ── Product Grid ─────────────────── -->
            <?php if ($products_result && $total_found > 0): $products_result->data_seek(0); ?>
            <div class="ref-grid">
                <?php while ($p = $products_result->fetch_assoc()):
                    $imgUrl = $p['image_url'] ?? '';
                    $img    = (strpos($imgUrl,'http')===0)
                                ? $imgUrl
                                : (defined('BASE_URL') ? BASE_URL : '../') . $imgUrl;
                    $hasDisc = ($p['original_price'] ?? 0) > $p['price'];
                    $disc    = $hasDisc ? round((($p['original_price']-$p['price'])/$p['original_price'])*100) : 0;
                    $isOOS   = $p['stock'] == 0;
                    $isLow   = !$isOOS && $p['stock'] < 5;
                    $stars   = 4 + (rand(0,1) * 0.5); // 4.0 or 4.5 display
                    $ratingN = number_format(4 + (mt_rand(0,9)/10), 1);
                ?>
                <div class="ref-card">

                    <!-- Image zone -->
                    <div class="ref-card-img-zone">
                        <a href="details.php?id=<?php echo $p['id']; ?>">
                            <img
                                src="<?php echo htmlspecialchars($img); ?>"
                                alt="<?php echo htmlspecialchars($p['name']); ?>"
                                class="ref-card-img"
                                onerror="this.src='<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>images/placeholder.jpg'"
                                loading="lazy"
                            >
                        </a>

                        <!-- Discount ribbon -->
                        <?php if ($hasDisc && $disc > 0): ?>
                        <span class="ref-badge"><?php echo $disc; ?>% off</span>
                        <?php elseif ($p['is_sale'] ?? 0): ?>
                        <span class="ref-badge">Sale</span>
                        <?php endif; ?>

                        <!-- OOS -->
                        <?php if ($isOOS): ?>
                        <div class="ref-oos-veil"><span>Out of Stock</span></div>
                        <?php endif; ?>

                        <!-- Action icons (hover) -->
                        <div class="ref-card-actions">
                            <button class="ref-act-btn add-to-cart-btn" data-product-id="<?php echo $p['id']; ?>" title="Add to cart" <?php echo $isOOS ? 'disabled' : ''; ?>>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            </button>
                            <a href="details.php?id=<?php echo $p['id']; ?>" class="ref-act-btn" title="Quick view">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Info zone -->
                    <div class="ref-card-info">
                        <div class="ref-card-meta">
                            <span class="ref-card-cat"><?php echo htmlspecialchars($p['category_name']); ?></span>
                            <span class="ref-card-rating">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="#f59e0b" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <?php echo $ratingN; ?>
                            </span>
                        </div>
                        <a href="details.php?id=<?php echo $p['id']; ?>" class="ref-card-name-a">
                            <h3 class="ref-card-name"><?php echo htmlspecialchars($p['name']); ?></h3>
                        </a>
                        <div class="ref-card-price-row">
                            <span class="ref-card-price">₹<?php echo number_format($p['price'], 2); ?></span>
                            <?php if ($hasDisc): ?>
                            <span class="ref-card-orig">₹<?php echo number_format($p['original_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($isLow): ?>
                        <p class="ref-low">Only <?php echo $p['stock']; ?> left</p>
                        <?php endif; ?>
                    </div>

                    <!-- Add to Cart row -->
                    <div class="ref-card-footer">
                        <button class="ref-atc-btn add-to-cart-btn" data-product-id="<?php echo $p['id']; ?>" <?php echo $isOOS ? 'disabled' : ''; ?>>
                            <?php if ($isOOS): ?>
                                Unavailable
                            <?php else: ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Add to Cart
                            <?php endif; ?>
                        </button>
                        <?php if (!$isOOS): ?>
                        <button class="ref-buy-btn buy-now-btn" data-product-id="<?php echo $p['id']; ?>">Buy Now</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <?php else: ?>
            <div class="ref-empty">
                <div class="ref-empty-ico">🛒</div>
                <h3>No products found</h3>
                <p>Try adjusting your filters or browse all categories.</p>
                <a href="listing.php" class="ref-empty-cta">Browse All</a>
            </div>
            <?php endif; ?>

        </div><!-- /product-area -->
    </div><!-- /ref-body -->

    <!-- ── TRUST STRIP ──────────────────── -->
    <div class="ref-trust">
        <div class="container ref-trust-inner">

            <div class="ref-trust-item">
                <div class="ref-trust-icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div>
                    <strong>Flexible Payment</strong>
                    <span>Multiple secure payment options</span>
                </div>
            </div>
            <div class="ref-trust-item">
                <div class="ref-trust-icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.33h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </div>
                <div>
                    <strong>24×7 Support</strong>
                    <span>We support online all days</span>
                </div>
            </div>
        </div>
    </div>

</div><!-- /ref-shop -->

<script>
function applySort(v) {
    const p = new URLSearchParams(window.location.search);
    p.set('sort', v);
    window.location.href = '?' + p.toString();
}

/* ── Banner slider ── */
let bnrCurrent = 0;
const bnrTotal = 3;
let bnrTimer;

function bnrGo(n) {
    bnrCurrent = (n + bnrTotal) % bnrTotal;
    const slider = document.getElementById('bnrSlider');
    if (slider) {
        slider.style.transform = `translateX(-${bnrCurrent * 100}%)`;
        // Enable pointer-events only on the active slide
        slider.querySelectorAll('.bnr-slide').forEach((s, i) => {
            s.style.pointerEvents = (i === bnrCurrent) ? 'auto' : 'none';
        });
    }
    document.querySelectorAll('.bnr-dot').forEach((d, i) => {
        d.classList.toggle('bnr-dot-active', i === bnrCurrent);
    });
}
function bnrNext() { bnrStopAuto(); bnrGo(bnrCurrent + 1); bnrStartAuto(); }
function bnrPrev() { bnrStopAuto(); bnrGo(bnrCurrent - 1); bnrStartAuto(); }
function bnrGoManual(n) { bnrStopAuto(); bnrGo(n); bnrStartAuto(); }

function bnrStartAuto() { bnrTimer = setInterval(bnrNext, 4000); }
function bnrStopAuto()  { clearInterval(bnrTimer); }

/* ── Toast notification ── */
function showToast(msg, type = 'success') {
    let t = document.getElementById('listing-toast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'listing-toast';
        t.style.cssText = `
            position:fixed; bottom:24px; left:50%; transform:translateX(-50%) translateY(60px);
            background:#18181b; color:#fff; padding:12px 22px; border-radius:30px;
            font-size:0.85rem; font-weight:600; z-index:9999; opacity:0;
            transition:all .3s cubic-bezier(.4,0,.2,1); white-space:nowrap;
            box-shadow:0 4px 20px rgba(0,0,0,.3);
        `;
        document.body.appendChild(t);
    }
    if (type === 'error') t.style.background = '#dc2626';
    else if (type === 'success') t.style.background = '#16a34a';
    t.textContent = msg;
    t.style.opacity = '1';
    t.style.transform = 'translateX(-50%) translateY(0)';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translateX(-50%) translateY(60px)';
    }, 2800);
}

/* ── Update header cart badge ── */
function updateCartBadge(count) {
    const badges = document.querySelectorAll('.cart-count, .cart-badge, [data-cart-count]');
    badges.forEach(b => { b.textContent = count; b.style.display = count > 0 ? '' : 'none'; });
}

/* ── Add to cart via AJAX ── */
async function addToCartAjax(productId, btn, redirect = false) {
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = redirect
        ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Please wait…'
        : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Adding…';

    try {
        const res  = await fetch('../cart/cart-api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', product_id: parseInt(productId), quantity: 1 })
        });
        const data = await res.json();

        if (data.success) {
            updateCartBadge(data.cart_count);
            if (redirect) {
                window.location.href = '../cart/cart.php';
                return;
            }
            showToast('✓ Added to cart!', 'success');
            btn.innerHTML = '✓ Added!';
            setTimeout(() => { btn.innerHTML = origText; btn.disabled = false; }, 1800);
        } else {
            showToast(data.message || 'Could not add to cart', 'error');
            btn.innerHTML = origText;
            btn.disabled = false;
        }
    } catch (e) {
        showToast('Connection error. Please try again.', 'error');
        btn.innerHTML = origText;
        btn.disabled = false;
    }
}

function applyPriceFilter() {
    const min = document.getElementById('minPriceInput').value;
    const max = document.getElementById('maxPriceInput').value;
    const p = new URLSearchParams(window.location.search);
    if(parseInt(min) > 0) p.set('min_price', min); else p.delete('min_price');
    if(parseInt(max) < 500) p.set('max_price', max); else p.delete('max_price');
    window.location.href = '?' + p.toString();
}

function initPriceSlider() {
    const minInput = document.getElementById('minPriceInput');
    const maxInput = document.getElementById('maxPriceInput');
    const fill = document.getElementById('rangeFill');
    const minLabel = document.getElementById('minPriceLabel');
    const maxLabel = document.getElementById('maxPriceLabel');
    
    if(!minInput || !maxInput) return;

    function updateView() {
        let minV = parseInt(minInput.value);
        let maxV = parseInt(maxInput.value);
        if (minV > maxV) { let tmp = minV; minV = maxV; maxV = tmp; }
        
        const minPercent = (minV / minInput.max) * 100;
        const maxPercent = (maxV / maxInput.max) * 100;
        
        fill.style.left = minPercent + '%';
        fill.style.width = (maxPercent - minPercent) + '%';
        
        minLabel.textContent = '₹' + minV;
        maxLabel.textContent = maxV >= 500 ? '₹500+' : '₹' + maxV;
    }
    
    minInput.addEventListener('input', () => {
        if(parseInt(minInput.value) > parseInt(maxInput.value) - 10) minInput.value = maxInput.value - 10;
        updateView();
    });
    
    maxInput.addEventListener('input', () => {
        if(parseInt(maxInput.value) < parseInt(minInput.value) + 10) maxInput.value = parseInt(minInput.value) + 10;
        updateView();
    });
    
    updateView();
}

document.addEventListener('DOMContentLoaded', () => {
    initPriceSlider();

    /* Banner init — sets pointer-events correctly from the start */
    bnrGo(0);
    bnrStartAuto();
    const wrap = document.querySelector('.bnr-wrap');
    if (wrap) {
        wrap.addEventListener('mouseenter', bnrStopAuto);
        wrap.addEventListener('mouseleave', bnrStartAuto);
    }

    /* Add to Cart buttons */
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const pid = btn.dataset.productId;
            if (!pid) return;
            addToCartAjax(pid, btn, false);
        });
    });

    /* Buy Now buttons */
    document.querySelectorAll('.buy-now-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const pid = btn.dataset.productId;
            if (!pid) return;
            addToCartAjax(pid, btn, true);
        });
    });
});
</script>

<!-- ═══════════════════ STYLES ═══════════════════ -->
<style>

/* ══════════════════════════════════════════════════
   BANNER
══════════════════════════════════════════════════ */
.bnr-wrap {
    position: relative;
    overflow: hidden;
    background: #fff;
    border-bottom: 1px solid #e4e4e7;
}
.bnr-slider {
    display: flex;
    transition: transform .55s cubic-bezier(.4,0,.2,1);
    will-change: transform;
}
.bnr-slide {
    min-width: 100%;
    padding: 0;
    pointer-events: none;
}
.bnr-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    padding: 36px 48px;
    min-height: 200px;
    position: relative;
}

/* Slide backgrounds */
.bnr-s1 { background: linear-gradient(135deg, #1a4d1e 0%, #2d7a32 50%, #1a6b1e 100%); }
.bnr-s2 { background: linear-gradient(135deg, #1e3a5c 0%, #1877f2 50%, #0d5ccc 100%); }
.bnr-s3 { background: linear-gradient(135deg, #7c2d12 0%, #ea580c 50%, #c2410c 100%); }



/* Text side */
.bnr-text-col {
    flex: 1;
    position: relative; z-index: 2;
    max-width: 520px;
}
.bnr-tag {
    display: inline-block;
    background: rgba(255,255,255,.18);
    color: rgba(255,255,255,.95);
    font-size: 0.72rem; font-weight: 800;
    letter-spacing: 0.08em; text-transform: uppercase;
    padding: 5px 14px; border-radius: 20px;
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.25);
    margin-bottom: 14px;
}
.bnr-headline {
    font-size: 2.4rem; font-weight: 900;
    color: #fff; margin: 0 0 12px;
    letter-spacing: -0.04em; line-height: 1.12;
    text-shadow: 0 2px 12px rgba(0,0,0,.2);
}
.bnr-headline em {
    font-style: normal;
    color: rgba(255,255,255,.75);
}
.bnr-desc {
    font-size: 0.9rem; color: rgba(255,255,255,.78);
    margin: 0 0 22px; line-height: 1.6; max-width: 360px;
}
.bnr-cta {
    display: inline-block;
    background: #fff; color: #1a4d1e;
    font-size: 0.85rem; font-weight: 800;
    padding: 11px 26px; border-radius: 30px;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(0,0,0,.2);
    transition: transform .18s, box-shadow .18s;
    position: relative;
    z-index: 5;
    cursor: pointer;
}
.bnr-cta:hover { transform: translateY(-2px); box-shadow: 0 6px 22px rgba(0,0,0,.28); }
.bnr-cta-alt  { color: #1877f2; }
.bnr-cta-gold { color: #c2410c; }

/* Visual side */
.bnr-visual-col {
    position: relative;
    width: 220px; height: 160px;
    flex-shrink: 0;
    z-index: 2;
}
.bnr-emoji-stack { position: relative; width: 100%; height: 100%; }
.bnr-e {
    position: absolute;
    font-size: 2.5rem;
    filter: drop-shadow(0 4px 12px rgba(0,0,0,.25));
    animation: bnrFloat 3s ease-in-out infinite;
}
.bnr-e1 { top:  0;   left:  50%; animation-delay: 0s;    font-size: 3.4rem; }
.bnr-e2 { top: 10px; left:  0;   animation-delay: .5s;   font-size: 2.4rem; }
.bnr-e3 { bottom:0;  right: 10px;animation-delay: 1s;    font-size: 2.8rem; }
.bnr-e4 { bottom:0;  left:  30%; animation-delay: 1.5s;  font-size: 2rem;   }

@keyframes bnrFloat {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-10px); }
}

.bnr-badge-float {
    position: absolute; top: -10px; right: -10px;
    background: #fff;
    color: #1a4d1e;
    width: 80px; height: 80px; border-radius: 50%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    font-size: 0.6rem; font-weight: 700;
    text-align: center; line-height: 1.2;
    text-transform: uppercase; letter-spacing: 0.04em;
    box-shadow: 0 6px 20px rgba(0,0,0,.2);
    animation: bnrSpin 6s linear infinite;
}
.bnr-badge-float strong { font-size: 0.9rem; font-weight: 900; display: block; }
.bnr-badge-orange { color: #c2410c; }
.bnr-badge-gold   { color: #92400e; }

@keyframes bnrSpin {
    0%,100% { transform: rotate(-6deg) scale(1); }
    50%      { transform: rotate(6deg) scale(1.05); }
}

/* Arrows */
.bnr-arrow {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(255,255,255,0.25); border: 1.5px solid rgba(255,255,255,0.5);
    color: #fff; font-size: 1.6rem; line-height: 1;
    cursor: pointer; z-index: 10;
    transition: all .18s;
    display: flex; align-items: center; justify-content: center;
    text-shadow: 0 1px 4px rgba(0,0,0,.3);
}
.bnr-arrow:hover { background: rgba(255,255,255,0.45); transform: translateY(-50%) scale(1.08); }
.bnr-prev { left: 18px; }
.bnr-next { right: 18px; }

/* Dots */
.bnr-dots {
    position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%);
    display: flex; gap: 7px; z-index: 10;
}
.bnr-dot {
    width: 8px; height: 8px; border-radius: 20px;
    background: rgba(255,255,255,.4); border: none;
    cursor: pointer; transition: all .25s;
    padding: 0;
}
.bnr-dot-active {
    background: #fff; width: 24px;
}

/* Responsive banner */
@media (max-width: 768px) {
    .bnr-inner { padding: 28px 24px; min-height: 160px; }
    .bnr-headline { font-size: 1.7rem; }
    .bnr-visual-col { width: 130px; height: 120px; }
    .bnr-e { font-size: 2rem; }
    .bnr-e1 { font-size: 2.5rem; }
    .bnr-badge-float { width: 64px; height: 64px; right: -5px; top: -5px; font-size: 0.5rem; }
    .bnr-badge-float strong { font-size: 0.75rem; }
    .bnr-prev { left: 8px; }
    .bnr-next { right: 8px; }
}
@media (max-width: 520px) {
    .bnr-visual-col { display: none; }
    .bnr-headline { font-size: 1.5rem; }
    .bnr-desc { font-size: 0.82rem; }
}

</style>

<!-- ═══════════════════ STYLES ═══════════════════ -->
<style>
/* === ROOT TOKENS ======================================== */
.ref-shop {
    --g:           #2a7d2e;
    --gd:          #1b5e20;
    --gs:          #f0faf0;
    --gm:          #a8d5aa;
    --cream:       #fdf6ee;
    --cream-d:     #f5e9d8;
    --card-border: #e6dbd0;
    --t1:          #18181b;
    --t2:          #52525b;
    --t3:          #a1a1aa;
    --border:      #e4e4e7;
    --bg:          #f6f3ef;
    --white:       #ffffff;
    --star:        #f59e0b;
    --red:         #dc2626;
    --radius:      16px;
    --radius-sm:   10px;
    --shadow-sm:   0 1px 3px rgba(0,0,0,.06), 0 2px 8px rgba(0,0,0,.04);
    --shadow-card: 0 1px 4px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.07);
    --shadow-hover:0 8px 40px rgba(0,0,0,.14), 0 4px 16px rgba(0,0,0,.08);
    --green-glow:  0 0 0 3px rgba(42,125,46,.14);

    background: var(--bg);
    font-family: 'DM Sans', 'Inter', system-ui, sans-serif;
    scroll-behavior: smooth;
}



/* === LAYOUT ============================================= */
.ref-body {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 28px;
    padding-top: 36px;
    padding-bottom: 56px;
    align-items: start;
}

/* === SIDEBAR ============================================ */
.ref-sidebar {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 0;
    box-shadow: var(--shadow-card);
    position: sticky;
    top: 80px;
    overflow: hidden;
}
.ref-sidebar-title {
    font-size: 0.78rem; font-weight: 900;
    text-transform: uppercase; letter-spacing: 0.1em;
    color: var(--white);
    background: linear-gradient(135deg, var(--g) 0%, var(--gd) 100%);
    margin: 0; padding: 16px 20px;
}
.ref-fblock {
    margin: 0;
    padding: 16px 20px;
    border-bottom: 1px solid #f0ece8;
}
.ref-fblock:last-child { border-bottom: none; }
.ref-fblock-title {
    font-size: 0.72rem; font-weight: 900;
    text-transform: uppercase; letter-spacing: 0.09em;
    color: var(--t1); margin: 0 0 11px;
    display: flex; align-items: center; gap: 8px;
}
.ref-fblock-title::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(to right, var(--border), transparent);
}
.ref-flist {
    list-style: none; margin: 0; padding: 0;
    display: flex; flex-direction: column; gap: 2px;
}
.ref-flink {
    display: flex; align-items: center; justify-content: space-between;
    font-size: 0.83rem; color: var(--t2); font-weight: 500;
    text-decoration: none;
    padding: 7px 10px; border-radius: var(--radius-sm);
    border-left: 3px solid transparent;
    transition: all .18s;
}
.ref-flink:hover { color: var(--g); background: var(--gs); border-left-color: var(--gm); }
.ref-flink-active { color: var(--g); font-weight: 700; background: var(--gs); border-left-color: var(--g); }
.ref-fcnt {
    font-size: 0.68rem; color: var(--t3);
    background: #f4f4f5; border-radius: 20px;
    padding: 1px 7px; font-weight: 600;
}
.ref-flink-active .ref-fcnt { background: var(--gm); color: var(--gd); }

.ref-cat-label { display: flex; align-items: center; gap: 8px; }
.ref-cat-icon { font-size: 1.15rem; transition: transform 0.2s cubic-bezier(.34,1.56,.64,1); display: inline-block; filter: drop-shadow(0 2px 3px rgba(0,0,0,0.12)); }
.ref-flink:hover .ref-cat-icon { transform: scale(1.3) rotate(-8deg); }
.ref-cat-text { transition: color 0.15s; }

/* Price slider visual */
.ref-price-track {
    position: relative; height: 6px;
    background: #e9e9eb; border-radius: 10px;
    margin: 12px 0;
}
.ref-price-fill {
    position: absolute; left: 0; width: 100%;
    height: 100%;
    background: linear-gradient(to right, var(--g), #4caf50);
    border-radius: 10px;
    z-index: 1;
}
.ref-price-track input[type=range] {
    position: absolute;
    top: -6px; left: -2px; width: calc(100% + 4px);
    pointer-events: none;
    -webkit-appearance: none;
    appearance: none;
    background: transparent;
    z-index: 2;
}
.ref-price-track input[type=range]::-webkit-slider-thumb {
    pointer-events: auto;
    -webkit-appearance: none;
    appearance: none;
    width: 18px; height: 18px; border-radius: 50%;
    background: var(--white); border: 2.5px solid var(--g);
    box-shadow: var(--shadow-sm), var(--green-glow);
    cursor: pointer;
}
.ref-price-track input[type=range]::-moz-range-thumb {
    pointer-events: auto;
    width: 18px; height: 18px; border-radius: 50%;
    background: var(--white); border: 2.5px solid var(--g);
    box-shadow: var(--shadow-sm), var(--green-glow);
    cursor: pointer;
}
.ref-price-labels {
    display: flex; justify-content: space-between;
    font-size: 0.75rem; color: var(--t1); font-weight: 700;
    margin-top: 8px;
}
.btn-apply-price {
    display: block; width: 100%; text-align: center; font-size: 0.75rem;
    font-weight: 700; color: #fff; background: var(--g);
    padding: 6px; border-radius: 6px; cursor: pointer; border: none;
    transition: all .2s; margin-top: 10px;
}
.btn-apply-price:hover { filter: brightness(1.1); box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.ref-price-sort-links { display: flex; gap: 6px; margin-top: 12px; }
.ref-price-sort-btn {
    font-size: 0.72rem; font-weight: 700; color: var(--t2);
    text-decoration: none; padding: 5px 12px;
    border: 1.5px solid var(--border); border-radius: 20px;
    background: var(--white); transition: all .18s;
}
.ref-price-sort-btn:hover { background: var(--gs); color: var(--g); border-color: var(--gm); }
.ref-price-sort-active { background: var(--g) !important; color: #fff !important; border-color: var(--g) !important; }

/* Radio items */
.ref-radio-group { display: flex; flex-direction: column; gap: 4px; }
.ref-radio-item {
    display: flex; align-items: center; gap: 10px;
    font-size: 0.83rem; color: var(--t2); font-weight: 500;
    text-decoration: none;
    padding: 7px 10px; border-radius: var(--radius-sm);
    transition: all .18s;
}
.ref-radio-item:hover { color: var(--g); background: var(--gs); }
.ref-radio-active { color: var(--g); font-weight: 700; background: var(--gs); }
.ref-radio-dot {
    width: 16px; height: 16px; border-radius: 50%;
    border: 2px solid #d4d4d8;
    background: var(--white); flex-shrink: 0;
    transition: all .18s;
    box-shadow: inset 0 1px 3px rgba(0,0,0,.06);
}
.ref-radio-dot-on {
    background: var(--g); border-color: var(--g);
    box-shadow: inset 0 0 0 4px #fff, var(--green-glow);
}

/* === PRODUCT AREA ======================================= */
.ref-product-area { min-width: 0; }

/* Toolbar */
.ref-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 10px;
    margin-bottom: 18px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 12px 18px;
    box-shadow: var(--shadow-sm);
}
.ref-showing { font-size: 0.84rem; color: var(--t2); margin: 0; }
.ref-showing strong { color: var(--t1); font-weight: 800; }
.ref-toolbar-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

/* Active filter chips */
.ref-chip-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.ref-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--gs); border: 1.5px solid var(--gm);
    color: var(--gd); font-size: 0.74rem; font-weight: 700;
    padding: 4px 12px; border-radius: 20px;
    box-shadow: 0 1px 4px rgba(42,125,46,.1);
}
.ref-chip-x { color: var(--g); text-decoration: none; margin-left: 2px; font-size: 0.72rem; opacity: .7; transition: opacity .15s; }
.ref-chip-x:hover { opacity: 1; }
.ref-chip-clear {
    font-size: 0.74rem; font-weight: 700; color: var(--red);
    text-decoration: none; padding: 4px 12px;
    border: 1.5px solid rgba(220,38,38,.25); border-radius: 20px;
    background: rgba(220,38,38,.04); transition: all .15s;
}
.ref-chip-clear:hover { background: rgba(220,38,38,.1); border-color: rgba(220,38,38,.5); }

.ref-sort-wrap { display: flex; align-items: center; gap: 7px; }
.ref-sort-lbl { font-size: 0.8rem; color: var(--t3); font-weight: 600; white-space: nowrap; }
.ref-sort-sel {
    border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    background: var(--white); padding: 7px 14px;
    font-size: 0.82rem; font-weight: 700; color: var(--t1);
    cursor: pointer; font-family: inherit; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.ref-sort-sel:focus { border-color: var(--g); box-shadow: var(--green-glow); }

/* === PRODUCT GRID ======================================= */
.ref-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

/* === PRODUCT CARD ======================================= */
.ref-card {
    background: var(--white);
    border: 1px solid var(--card-border);
    border-radius: var(--radius);
    overflow: hidden;
    display: flex; flex-direction: column;
    box-shadow: var(--shadow-card);
    transition: box-shadow .28s cubic-bezier(.4,0,.2,1),
                transform .28s cubic-bezier(.4,0,.2,1),
                border-color .28s;
}
.ref-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-6px);
    border-color: #d4c8bc;
}

/* Image zone */
.ref-card-img-zone {
    position: relative;
    background: linear-gradient(155deg, var(--cream) 0%, var(--cream-d) 100%);
    height: 220px;
    overflow: hidden;
}
.ref-card-img-zone > a { display: block; width: 100%; height: 100%; }
.ref-card-img {
    width: 100%; height: 100%;
    object-fit: contain;
    mix-blend-mode: multiply;
    padding: 20px;
    transition: transform .4s cubic-bezier(.4,0,.2,1);
}
.ref-card:hover .ref-card-img { transform: scale(1.08); }

/* shimmer sweep on hover */
.ref-card-img-zone::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.45) 50%, transparent 60%);
    transform: translateX(-100%);
    transition: transform .6s ease;
    pointer-events: none;
}
.ref-card:hover .ref-card-img-zone::after { transform: translateX(150%); }

/* Green discount badge — top left */
.ref-badge {
    position: absolute; top: 12px; left: 12px;
    background: linear-gradient(135deg, #2a9134 0%, #1b7a24 100%);
    color: #fff;
    font-size: 0.68rem; font-weight: 900;
    padding: 5px 12px; border-radius: 20px;
    letter-spacing: 0.05em; text-transform: uppercase;
    box-shadow: 0 3px 10px rgba(27,90,36,.35);
}

/* OOS veil */
.ref-oos-veil {
    position: absolute; inset: 0;
    background: rgba(253,248,243,.82);
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(3px);
}
.ref-oos-veil span {
    background: var(--white); border: 1.5px solid var(--border);
    font-size: 0.7rem; font-weight: 800; color: var(--t2);
    padding: 6px 16px; border-radius: 20px;
    text-transform: uppercase; letter-spacing: 0.07em;
    box-shadow: var(--shadow-sm);
}

/* Action icons — slide in top-right on hover */
.ref-card-actions {
    position: absolute; top: 12px; right: 12px;
    display: flex; flex-direction: column; gap: 7px;
    opacity: 0; transform: translateX(10px);
    transition: opacity .22s, transform .22s;
}
.ref-card:hover .ref-card-actions { opacity: 1; transform: translateX(0); }
.ref-act-btn {
    width: 36px; height: 36px; border-radius: 50%;
    background: rgba(255,255,255,.95);
    border: 1px solid rgba(228,220,210,.8);
    display: flex; align-items: center; justify-content: center;
    color: var(--t2); cursor: pointer; text-decoration: none;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
    transition: all .18s cubic-bezier(.4,0,.2,1);
    backdrop-filter: blur(4px);
}
.ref-act-btn:hover {
    background: var(--g); color: #fff; border-color: var(--g);
    transform: scale(1.1);
    box-shadow: 0 4px 14px rgba(42,125,46,.4);
}
.ref-act-btn:disabled { opacity: .35; cursor: not-allowed; }

/* Info zone */
.ref-card-info { padding: 16px 18px 10px; flex: 1; }
.ref-card-meta {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 8px;
}
.ref-card-cat {
    font-size: 0.65rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.09em;
    color: var(--g);
    background: var(--gs); padding: 2px 9px; border-radius: 20px;
    border: 1px solid var(--gm);
}
.ref-card-rating {
    display: flex; align-items: center; gap: 4px;
    font-size: 0.76rem; font-weight: 800; color: var(--t1);
    background: #fffbeb; border: 1px solid #fde68a;
    padding: 2px 8px; border-radius: 20px;
}
.ref-card-name-a { text-decoration: none; }
.ref-card-name {
    font-size: 0.93rem; font-weight: 700;
    color: var(--t1); margin: 0 0 10px; line-height: 1.4;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
    transition: color .15s;
}
.ref-card-name-a:hover .ref-card-name { color: var(--g); }
.ref-card-price-row { display: flex; align-items: baseline; gap: 8px; }
.ref-card-price { font-size: 1.1rem; font-weight: 900; color: var(--g); letter-spacing: -0.02em; }
.ref-card-orig { font-size: 0.8rem; color: var(--t3); text-decoration: line-through; }
.ref-low {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 0.7rem; font-weight: 700; color: var(--red);
    background: #fff1f1; border: 1px solid #fecaca;
    padding: 2px 8px; border-radius: 20px; margin: 6px 0 0;
}

/* Card footer */
.ref-card-footer {
    display: flex; gap: 8px;
    padding: 10px 16px 16px;
    border-top: 1px solid #f4ede6;
    margin-top: 6px;
}
.ref-atc-btn {
    flex: 1; padding: 10px 10px;
    background: linear-gradient(135deg, var(--g) 0%, var(--gd) 100%);
    color: #fff; border: none; border-radius: var(--radius-sm);
    font-size: 0.78rem; font-weight: 800;
    cursor: pointer; font-family: inherit;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    box-shadow: 0 3px 10px rgba(42,125,46,.3);
    transition: all .18s cubic-bezier(.4,0,.2,1);
}
.ref-atc-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(42,125,46,.4);
}
.ref-atc-btn:active:not(:disabled) { transform: translateY(0); }
.ref-atc-btn:disabled { background: #e4e4e7; color: var(--t3); cursor: not-allowed; box-shadow: none; }
.ref-buy-btn {
    flex: 1; padding: 10px 10px;
    background: var(--white); color: var(--g);
    border: 2px solid var(--g);
    border-radius: var(--radius-sm); font-size: 0.78rem; font-weight: 800;
    cursor: pointer; font-family: inherit;
    transition: all .18s cubic-bezier(.4,0,.2,1);
}
.ref-buy-btn:hover { background: var(--gs); transform: translateY(-1px); }

/* === EMPTY =============================================  */
.ref-empty {
    text-align: center; padding: 80px 24px;
    grid-column: 1/-1;
}
.ref-empty-ico { font-size: 3rem; margin-bottom: 14px; }
.ref-empty h3 { font-size: 1.2rem; font-weight: 800; color: var(--t1); margin: 0 0 8px; }
.ref-empty p  { font-size: 0.9rem; color: var(--t3); margin: 0 0 20px; }
.ref-empty-cta {
    display: inline-block; background: var(--g); color: #fff;
    padding: 11px 28px; border-radius: 9px;
    text-decoration: none; font-size: 0.875rem; font-weight: 700;
}
.ref-empty-cta:hover { background: var(--gd); }

/* === TRUST STRIP ======================================== */
.ref-trust {
    background: var(--white);
    border-top: 1px solid var(--border);
    padding: 44px 0;
    margin-top: 20px;
    position: relative;
    overflow: hidden;
}
.ref-trust::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(to right, var(--g), #4caf50, var(--g));
}
.ref-trust-inner {
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
}
.ref-trust-item {
    display: flex; align-items: center; gap: 20px;
    padding: 0 40px;
    border-right: 1px solid var(--border);
}
.ref-trust-item:last-child { border-right: none; }
.ref-trust-icon-box {
    width: 62px; height: 62px; border-radius: 16px;
    background: linear-gradient(135deg, var(--gs) 0%, #d4edda 100%);
    border: 1.5px solid var(--gm);
    display: flex; align-items: center; justify-content: center;
    color: var(--g); flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(42,125,46,.12);
}
.ref-trust-item > div:not(.ref-trust-icon-box) { display: flex; flex-direction: column; gap: 3px; }
.ref-trust-item strong { font-size: 0.95rem; font-weight: 800; color: var(--t1); }
.ref-trust-item span { font-size: 0.78rem; color: var(--t3); line-height: 1.4; }

/* === RESPONSIVE ========================================= */

/* ── Category Icon Strip ───────────────────── */
.cis-section {
    background: #fff;
    border-bottom: 1px solid #ececec;
    padding: 22px 0 18px;
}
.cis-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px;
}
.cis-title {
    font-size: 0.95rem; font-weight: 800; color: #18181b;
    letter-spacing: -0.02em;
}
.cis-see-all {
    font-size: 0.78rem; font-weight: 700; color: #2a7d2e;
    text-decoration: none; transition: opacity .15s;
}
.cis-see-all:hover { opacity: .7; }
.cis-row {
    display: flex; gap: 12px; flex-wrap: nowrap;
    overflow-x: auto; padding-bottom: 6px;
    scrollbar-width: none;
}
.cis-row::-webkit-scrollbar { display: none; }
.cis-tile {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    text-decoration: none; flex-shrink: 0;
    transition: transform .2s;
}
.cis-tile:hover { transform: translateY(-4px); }
.cis-tile span {
    font-size: 0.72rem; font-weight: 700; color: #3f3f3f;
    white-space: nowrap; text-align: center;
}
.cis-icon-box {
    width: 72px; height: 72px; border-radius: 18px;
    background: var(--cic, #e8f5e9);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.05);
    transition: box-shadow .2s;
}
.cis-tile:hover .cis-icon-box {
    box-shadow: 0 6px 18px rgba(0,0,0,.12);
}

/* ── Offer Banner Cards ─────────────────────── */
.obc-section {
    padding: 20px 0 24px;
    background: #f6f3ef;
}
.obc-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.obc-card {
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    transition: transform .22s, box-shadow .22s;
}
.obc-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(0,0,0,.14); }
.obc-c1 { background: linear-gradient(135deg, #1a4d1e 0%, #2a9134 100%); }
.obc-c2 { background: linear-gradient(135deg, #7b3f00 0%, #e65c00 100%); }
.obc-c3 { background: linear-gradient(135deg, #1a237e 0%, #1877f2 100%); }
.obc-card-inner {
    display: flex; align-items: center; justify-content: space-between;
    padding: 24px 22px;
    position: relative;
    min-height: 140px;
}
.obc-text { z-index: 2; }
.obc-sub {
    display: block;
    font-size: 0.68rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: rgba(255,255,255,.7);
    margin-bottom: 6px;
}
.obc-title {
    font-size: 1.25rem; font-weight: 900;
    color: #fff; margin: 0 0 14px;
    line-height: 1.2; letter-spacing: -0.03em;
}
.obc-btn {
    display: inline-block;
    background: #fff;
    color: #1a4d1e;
    font-size: 0.75rem; font-weight: 800;
    padding: 8px 18px; border-radius: 30px;
    text-decoration: none;
    box-shadow: 0 3px 10px rgba(0,0,0,.18);
    transition: transform .15s, box-shadow .15s;
}
.obc-btn:hover { transform: translateY(-1px); box-shadow: 0 5px 14px rgba(0,0,0,.24); }
.obc-btn-dark { color: #7b3f00; }
.obc-btn-white { color: #1a237e; }
.obc-visual {
    position: relative;
    width: 100px; height: 100px;
    flex-shrink: 0;
}
.obc-big-emoji {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%,-50%);
    font-size: 3.6rem;
    filter: drop-shadow(0 4px 10px rgba(0,0,0,.25));
    animation: bnrFloat 3s ease-in-out infinite;
}
.obc-sm-emoji {
    position: absolute;
    font-size: 1.6rem;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,.2));
    animation: bnrFloat 3s ease-in-out infinite;
}
.obc-sm1 { top: 0; right: 0; animation-delay: .7s; }
.obc-sm2 { bottom: 0; left: 0; animation-delay: 1.4s; }

@media (max-width: 860px) {
    .obc-grid { grid-template-columns: 1fr; }
    .cis-icon-box { width: 58px; height: 58px; font-size: 1.7rem; border-radius: 14px; }
}
@media (max-width: 520px) {
    .obc-grid { grid-template-columns: 1fr 1fr; }
    .obc-card-inner { padding: 16px 14px; min-height: 120px; }
    .obc-title { font-size: 1rem; }
    .obc-visual { width: 70px; height: 70px; }
    .obc-big-emoji { font-size: 2.4rem; }
}

/* === RESPONSIVE ========================================= */
@media (max-width: 1100px) {
    .ref-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 860px) {
    .ref-body { grid-template-columns: 1fr; }
    .ref-sidebar { position: static; }
    .ref-grid { grid-template-columns: repeat(2, 1fr); }
    .ref-trust-inner { flex-direction: column; align-items: center; gap: 30px; }
    .ref-trust-item { border-right: none; padding: 0 20px; }
}
@media (max-width: 520px) {
    .ref-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .ref-card-img-zone { height: 160px; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
