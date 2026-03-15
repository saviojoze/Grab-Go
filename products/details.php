<?php
$page_title = 'Product Details - Grab & Go';
require_once __DIR__ . '/../config.php';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if invalid ID
if ($product_id <= 0) {
    redirect('listing.php');
}

// Fetch product details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('listing.php');
}

$product = $result->fetch_assoc();
$page_title = htmlspecialchars($product['name']) . ' - Grab & Go';
$current_page = 'shop';

// Process images
$imgUrl = $product['image_url'] ?? 'images/placeholder.jpg';
if (strpos($imgUrl, 'http') !== 0) {
    $base = defined('BASE_URL') ? BASE_URL : '../';
    $imgUrl = $base . $imgUrl;
}

// Fetch related products
$category_id = (int)$product['category_id'];
$related_stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$related_stmt->bind_param("ii", $category_id, $product_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();

require_once __DIR__ . '/../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #1877F2;
    --secondary: #00D563;
    --accent-red: #E21E26;
    --text-main: #2D3134;
    --text-muted: #7E7E7E;
    --border-light: #EEEEEE;
    --bg-light: #F8F9FA;
}

.details-page {
    font-family: 'Outfit', sans-serif;
    background: #fff;
    padding-bottom: 60px;
}

.breadcrumb-nav {
    padding: 20px 0;
    font-size: 14px;
    color: var(--text-muted);
}
.breadcrumb-nav a {
    color: var(--text-muted);
    text-decoration: none;
}
.breadcrumb-nav a:hover { color: var(--primary); }
.breadcrumb-nav span { margin: 0 8px; color: #ccc; }
.breadcrumb-nav .active { color: var(--accent-red); font-weight: 500; }

.product-main-info {
    display: flex;
    gap: 50px;
    margin-bottom: 50px;
}

/* Image Section */
.product-gallery {
    flex: 0 0 45%;
}
.main-img-wrap {
    background: #fff;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    position: relative;
}
.main-img-wrap img {
    max-width: 100%;
    max-height: 400px;
    transition: transform 0.3s ease;
}
.thumb-row {
    display: flex;
    gap: 12px;
}
.thumb-box {
    width: 80px;
    height: 80px;
    border: 1px solid var(--border-light);
    border-radius: 4px;
    padding: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.2s;
}
.thumb-box:hover, .thumb-box.active {
    border-color: var(--primary);
}
.thumb-box img {
    max-width: 100%;
    max-height: 100%;
}

/* Info Section */
.product-info-col {
    flex: 1;
}
.product-category-tree {
    font-size: 12px;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
}
.product-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--text-main);
    margin: 0 0 15px 0;
}
.product-rating {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
.star-rating { color: #FFC107; font-size: 14px; }
.review-count { color: var(--text-muted); font-size: 13px; }

.product-price {
    font-size: 28px;
    font-weight: 700;
    color: var(--accent-red);
    margin-bottom: 25px;
}

.product-short-desc {
    color: #666;
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 30px;
}

.atc-form {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid var(--border-light);
}
.qty-input-wrap {
    display: flex;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}
.qty-btn {
    background: #f4f4f4;
    border: none;
    width: 36px;
    height: 44px;
    cursor: pointer;
    font-size: 18px;
    color: #666;
}
.qty-val {
    width: 50px;
    height: 44px;
    border: none;
    border-left: 1px solid #ddd;
    border-right: 1px solid #ddd;
    text-align: center;
    font-weight: 600;
}
.btn-atc {
    background: #333;
    color: #fff;
    border: none;
    padding: 0 35px;
    height: 44px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 14px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-atc:hover { background: var(--primary); }

.wishlist-compare {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}
.wc-link {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #333;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}
.wc-link:hover { color: var(--primary); }

.meta-info {
    font-size: 14px;
    color: var(--text-muted);
}
.meta-row { margin-bottom: 8px; }
.meta-label { color: var(--text-main); font-weight: 600; min-width: 80px; display: inline-block; }

/* Tabs */
.tabs-section {
    margin-top: 40px;
    border-top: 1px solid var(--border-light);
}
.tab-headers {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: -1px;
}
.tab-btn {
    padding: 15px 10px;
    font-weight: 600;
    font-size: 18px;
    color: var(--text-muted);
    background: none;
    border: none;
    border-top: 3px solid transparent;
    cursor: pointer;
}
.tab-btn.active {
    color: var(--accent-red);
    border-top-color: var(--accent-red);
}
.tab-content {
    padding: 40px 0;
    color: #666;
    line-height: 1.7;
}

/* Related */
.related-section {
    margin-top: 60px;
}
.section-title-center {
    text-align: center;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 40px;
    position: relative;
}
.section-title-center::after {
    content: '';
    display: block;
    width: 40px;
    height: 2px;
    background: var(--accent-red);
    margin: 15px auto 0;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
}
.rel-card {
    border: 1px solid var(--border-light);
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.3s;
}
.rel-card:hover { box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
.rel-img {
    height: 200px;
    background: #f9f9f9;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.rel-img img { max-width: 100%; max-height: 100%; transition: transform 0.3s; }
.rel-card:hover .rel-img img { transform: scale(1.05); }
.rel-body { padding: 15px; text-align: center; }
.rel-cat { color: #999; font-size: 12px; text-transform: uppercase; display: block; margin-bottom: 5px; }
.rel-name { font-weight: 600; font-size: 15px; color: #333; margin: 0 0 8px 0; text-decoration: none; display: block; }
.rel-price { color: var(--accent-red); font-weight: 700; }

@media (max-width: 992px) {
    .product-main-info { flex-direction: column; gap: 30px; }
    .product-gallery { flex: 1; }
    .related-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="details-page">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb-nav">
            <a href="../index.php">Home</a>
            <span>/</span>
            <a href="listing.php">Shop</a>
            <span>/</span>
            <a href="listing.php?categories=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
            <span>/</span>
            <span class="active"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <!-- Product Main Container -->
        <div class="product-main-info">
            <!-- Gallery -->
            <div class="product-gallery">
                <div class="main-img-wrap">
                    <img id="main-product-image" src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="thumb-row">
                    <div class="thumb-box active" onclick="updateMainImg('<?php echo htmlspecialchars($imgUrl); ?>', this)">
                        <img src="<?php echo htmlspecialchars($imgUrl); ?>">
                    </div>
                    <!-- Dummy thumbs -->
                    <div class="thumb-box" onclick="updateMainImg('<?php echo htmlspecialchars($imgUrl); ?>', this)">
                        <img src="<?php echo htmlspecialchars($imgUrl); ?>" style="filter: brightness(0.9) contrast(1.1);">
                    </div>
                    <div class="thumb-box" onclick="updateMainImg('<?php echo htmlspecialchars($imgUrl); ?>', this)">
                        <img src="<?php echo htmlspecialchars($imgUrl); ?>" style="filter: sepia(0.3);">
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="product-info-col">
                <div class="product-category-tree">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </div>
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-rating">
                    <div class="star-rating">
                        ★★★★★
                    </div>
                    <div class="review-count">(1 customer review)</div>
                </div>

                <div class="product-price">
                    ₹<?php echo number_format($product['price'], 2); ?>
                </div>

                <div class="product-short-desc">
                    <?php 
                        $short_desc = !empty($product['description']) ? mb_strimwidth($product['description'], 0, 200, '...') : 'Experience premium quality with this selection from Grab & Go. Carefully curated for our customers.';
                        echo nl2br(htmlspecialchars($short_desc)); 
                    ?>
                </div>

                <!-- ATC Form -->
                <div class="atc-form">
                    <div class="qty-input-wrap">
                        <button class="qty-btn" type="button" onclick="changeQty(-1)">-</button>
                        <input type="text" id="product-qty" class="qty-val" value="1" readonly>
                        <button class="qty-btn" type="button" onclick="changeQty(1)">+</button>
                    </div>
                    <button class="btn-atc add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                        Add to Cart
                    </button>
                </div>

                <div class="wishlist-compare">
                    <a href="#" class="wc-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        Browse Wishlist
                    </a>
                    <a href="#" class="wc-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 3h5v5M8 21H3v-5M21 3l-7 7M3 21l7-7"/></svg>
                        Add to compare
                    </a>
                </div>

                <div class="meta-info">
                    <div class="meta-row">
                        <span class="meta-label">Categories:</span>
                        <span><?php echo htmlspecialchars($product['category_name']); ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Tag:</span>
                        <span><?php echo htmlspecialchars($product['dietary_tags'] ?? 'premium'); ?></span>
                    </div>
                    <div class="meta-row" style="margin-top: 20px;">
                        <span class="meta-label" style="margin-bottom: 10px; display: block;">Share this product:</span>
                        <div style="display: flex; gap: 15px;">
                            <a href="#" style="color: #666;"><svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                            <a href="#" style="color: #666;"><svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg></a>
                            <a href="#" style="color: #666;"><svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs-section">
            <div class="tab-headers">
                <button class="tab-btn active" onclick="switchTab('desc', this)">Description</button>
                <button class="tab-btn" onclick="switchTab('reviews', this)">Reviews (1)</button>
            </div>
            <div id="tab-desc" class="tab-content">
                <?php echo nl2br(htmlspecialchars($product['description'] ?: 'No detailed description available for this product.')); ?>
                <br><br>
                This product is part of our quality-checked inventory, ensuring you get the freshest and best items every time you shop at Grab & Go.
            </div>
            <div id="tab-reviews" class="tab-content" style="display: none;">
                <div style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
                    <strong style="color: #333;">Savio Jose</strong> <span style="color: #999; font-size: 12px; margin-left: 10px;">March 11, 2026</span>
                    <div style="color: #FFC107; margin: 5px 0;">★★★★★</div>
                    <p style="margin: 0;">Excellent product! The quality is outstanding and it was ready for pickup very quickly. Highly recommend Grab & Go service.</p>
                </div>
                <p>Add your review below (Coming Soon)</p>
            </div>
        </div>

        <!-- Related -->
        <?php if ($related_result->num_rows > 0): ?>
        <div class="related-section">
            <h2 class="section-title-center">Related products</h2>
            <div class="related-grid">
                <?php while($rp = $related_result->fetch_assoc()): 
                    $rpImg = $rp['image_url'] ?? 'images/placeholder.jpg';
                    if (strpos($rpImg, 'http') !== 0) {
                        $rpImg = (defined('BASE_URL') ? BASE_URL : '../') . $rpImg;
                    }
                ?>
                <div class="rel-card">
                    <a href="details.php?id=<?php echo $rp['id']; ?>" class="rel-img">
                        <img src="<?php echo htmlspecialchars($rpImg); ?>" alt="<?php echo htmlspecialchars($rp['name']); ?>">
                    </a>
                    <div class="rel-body">
                        <span class="rel-cat"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        <a href="details.php?id=<?php echo $rp['id']; ?>" class="rel-name"><?php echo htmlspecialchars($rp['name']); ?></a>
                        <div class="rel-price">₹<?php echo number_format($rp['price'], 2); ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function updateMainImg(src, el) {
        document.getElementById('main-product-image').src = src;
        document.querySelectorAll('.thumb-box').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
    }

    function changeQty(delta) {
        const input = document.getElementById('product-qty');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        input.value = val;
    }

    function switchTab(tab, el) {
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + tab).style.display = 'block';
        el.classList.add('active');
    }

    // ATC script bridge
    document.querySelector('.btn-atc').addEventListener('click', function() {
        const qty = parseInt(document.getElementById('product-qty').value);
        const pid = this.dataset.productId;
        
        // Use existing cart system
        addToCartAjax(pid, this, false, qty);
    });

    /**
     * MODIFIED addToCartAjax to support quantity
     */
    async function addToCartAjax(productId, btn, redirect = false, qty = 1) {
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Adding…';

        try {
            const res = await fetch('../cart/cart-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', product_id: parseInt(productId), quantity: qty })
            });
            const data = await res.json();

            if (data.success) {
                if (typeof updateCartBadge === 'function') updateCartBadge(data.cart_count);
                if (redirect) {
                    window.location.href = '../cart/cart.php';
                    return;
                }
                btn.innerHTML = '✓ Added to Cart!';
                btn.style.background = '#00D563';
                setTimeout(() => { 
                    btn.innerHTML = origText; 
                    btn.disabled = false;
                    btn.style.background = '#333';
                }, 2000);
            } else {
                alert(data.message || 'Error adding to cart');
                btn.innerHTML = origText;
                btn.disabled = false;
            }
        } catch (e) {
            btn.innerHTML = origText;
            btn.disabled = false;
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
