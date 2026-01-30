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
    redirect('listing.php'); // Or show 404
}

$product = $result->fetch_assoc();
$page_title = htmlspecialchars($product['name']) . ' - Grab & Go';
$current_page = 'shop';

// Process images (simulated gallery)
$imgUrl = $product['image_url'] ?? 'images/placeholder.jpg';
if (strpos($imgUrl, 'http') !== 0) {
    $base = defined('BASE_URL') ? BASE_URL : '../';
    $imgUrl = $base . $imgUrl;
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* Flipkart-style Specific CSS */
.fp-container { background-color: #F1F3F6; min-height: 100vh; padding-top: 20px; padding-bottom: 40px; }
.fp-card { background: #fff; border-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); display: flex; overflow: hidden; }
.fp-left-col { width: 40%; padding: 16px; position: relative; border-right: 1px solid #f0f0f0; min-height: 500px; display: flex; flex-direction: column; }
.fp-right-col { width: 60%; padding: 24px; padding-left: 24px; }
.fp-gallery { display: flex; height: 100%; gap: 10px; }
.fp-thumbnails { width: 64px; display: flex; flex-direction: column; gap: 8px; }
.fp-thumb { width: 64px; height: 64px; border: 1px solid #f0f0f0; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px; }
.fp-thumb.active { border: 2px solid #2874f0; }
.fp-thumb img { max-width: 100%; max-height: 100%; }
.fp-main-img-box { flex: 1; display: flex; align-items: center; justify-content: center; position: relative; border: 1px solid #f0f0f0; height: 416px; }
.fp-main-img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.2s ease; cursor: crosshair; }
.fp-main-img:hover { transform: scale(1.1); } 

.fp-buttons { display: flex; gap: 12px; margin-top: 20px; }
.fp-btn { flex: 1; padding: 16px 8px; font-weight: 700; font-size: 16px; color: #fff; text-transform: uppercase; border: none; border-radius: 2px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.2); }
.fp-btn-cart { background: #FF9F00; }
.fp-btn-buy { background: #FB641B; }
.fp-btn svg { width: 18px; height: 18px; fill: white; }

.fp-title { font-size: 18px; color: #212121; line-height: 1.4; margin-bottom: 8px; }
.fp-rating-badge { background: #388E3C; color: #fff; padding: 2px 6px; font-size: 12px; font-weight: 700; border-radius: 3px; display: inline-flex; align-items: center; gap: 2px; margin-right: 8px; vertical-align: middle; }
.fp-rating-text { color: #878787; font-weight: 500; font-size: 14px; margin-left: 8px; }
.fp-price-row { display: flex; align-items: baseline; gap: 12px; margin: 12px 0; }
.fp-price { font-size: 28px; font-weight: 500; color: #212121; }
.fp-original-price { font-size: 16px; color: #878787; text-decoration: line-through; }
.fp-discount { color: #388E3C; font-weight: 700; font-size: 16px; }

.fp-section-title { font-size: 16px; font-weight: 500; color: #212121; margin-bottom: 12px; margin-top: 24px; }
.fp-offers-list { list-style: none; padding: 0; margin-bottom: 24px; }
.fp-offer-item { display: flex; gap: 10px; font-size: 14px; color: #212121; margin-bottom: 8px; align-items: flex-start; }
.fp-tag-icon { color: #388E3C; font-weight: 700; font-size: 12px; margin-top: 2px; }

.fp-pincode-box { display: flex; border-bottom: 2px solid #2874f0; width: 250px; padding: 4px 0; margin-bottom: 8px; }
.fp-pincode-input { border: none; outline: none; font-size: 14px; font-weight: 500; flex: 1; padding: 4px; }
.fp-check-btn { color: #2874f0; font-weight: 700; font-size: 13px; cursor: pointer; background: none; border: none; }

.fp-specs-table { border: 1px solid #E0E0E0; border-radius: 2px; width: 100%; max-width: 600px; border-collapse: collapse; margin-top: 10px; }
.fp-specs-table td { padding: 12px; font-size: 14px; border-bottom: 1px solid #F0F0F0; }
.fp-specs-label { color: #878787; width: 30%; }
.fp-specs-value { color: #212121; }

.fp-desc { color: #212121; font-size: 14px; line-height: 1.5; margin-top: 8px; }

/* Responsive */
@media (max-width: 768px) {
    .fp-card { flex-direction: column; }
    .fp-left-col, .fp-right-col { width: 100%; border-right: none; }
    .fp-main-img-box { height: 300px; }
}
</style>

<div class="fp-container">
    <div class="container">
        <!-- Breadcrumbs -->
        <div style="font-size: 12px; color: #878787; margin-bottom: 10px;">
            <a href="../index.php" style="color: #878787; text-decoration: none;">Home</a>  
            <span style="margin: 0 5px;">›</span>
            <a href="listing.php" style="color: #878787; text-decoration: none;">Shop</a>
            <span style="margin: 0 5px;">›</span>
            <span><?php echo htmlspecialchars($product['category_name']); ?></span>
            <span style="margin: 0 5px;">›</span>
            <span style="color: #212121;"><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <div class="fp-card">
            <!-- Left Column: Gallery & Buttons -->
            <div class="fp-left-col">
                <div class="fp-gallery">
                    <div class="fp-thumbnails">
                        <div class="fp-thumb active" onmouseover="changeImage('<?php echo htmlspecialchars($imgUrl); ?>')">
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>">
                        </div>
                        <!-- Simulated Thumbnails -->
                        <div class="fp-thumb" onmouseover="changeImage('<?php echo htmlspecialchars($imgUrl); ?>')">
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" style="filter: hue-rotate(45deg);">
                        </div>
                         <div class="fp-thumb" onmouseover="changeImage('<?php echo htmlspecialchars($imgUrl); ?>')">
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" style="filter: hue-rotate(90deg);">
                        </div>
                    </div>
                    <div class="fp-main-img-box">
                        <div style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                             <button style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #f0f0f0; background: #fff; cursor: pointer; color: #c2c2c2; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                             </button>
                        </div>
                        <img id="mainProductImg" src="<?php echo htmlspecialchars($imgUrl); ?>" class="fp-main-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                </div>

                <div class="fp-buttons">
                    <button class="fp-btn fp-btn-cart add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                        <svg viewBox="0 0 16 16"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>
                        Add to Cart
                    </button>
                    <button class="fp-btn fp-btn-buy buy-now-btn" data-product-id="<?php echo $product['id']; ?>">
                        <svg viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/></svg>
                        Buy Now
                    </button>
                </div>
            </div>

            <!-- Right Column: Details -->
            <div class="fp-right-col">
                <h1 class="fp-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div style="margin-bottom: 12px;">
                    <span class="fp-rating-badge">4.5 ★</span>
                    <span style="color: #878787; font-size: 14px;">(<?php echo rand(100, 5000); ?> Ratings & <?php echo rand(10, 500); ?> Reviews)</span>
                </div>

                <div class="fp-price-row">
                    <span class="fp-price">₹<?php echo number_format($product['price'], 0); ?></span>
                    <?php if ($product['original_price'] > $product['price']): ?>
                        <span class="fp-original-price">₹<?php echo number_format($product['original_price'], 0); ?></span>
                        <span class="fp-discount">
                            <?php echo round((($product['original_price'] - $product['price']) / $product['original_price']) * 100); ?>% off
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Offers -->
                <div class="fp-section-title">Available offers</div>
                <ul class="fp-offers-list">
                    <li class="fp-offer-item">
                        <span class="fp-tag-icon">🏷️</span>
                        <span><b>Bank Offer</b> 5% Cashback on Axis Bank Card</span>
                    </li>
                    <li class="fp-offer-item">
                        <span class="fp-tag-icon">🏷️</span>
                        <span><b>Special Price</b> Get extra 10% off (price inclusive of cashback/coupon)</span>
                    </li>
                    <li class="fp-offer-item">
                        <span class="fp-tag-icon">🏷️</span>
                        <span><b>Partner Offer</b> Sign up for Grab&Go Pay Later and get ₹50 Gift Card</span>
                    </li>
                </ul>

                <!-- Delivery Check -->
                <div style="display: flex; gap: 40px; margin-bottom: 24px;">
                    <div style="color: #878787; font-size: 14px; font-weight: 500; width: 100px;">Delivery</div>
                    <div>
                        <div class="fp-pincode-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#2874f0" style="margin-right: 8px; margin-top: 4px;"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            <input type="text" class="fp-pincode-input" placeholder="Enter Delivery Pincode">
                            <button class="fp-check-btn">Check</button>
                        </div>
                        <div style="font-size: 14px; font-weight: 700; color: #212121;">
                            Delivery by <?php echo date('d M, l', strtotime('+2 days')); ?> 
                            <span style="color: #388E3C; border-left: 1px solid #e0e0e0; padding-left: 8px; margin-left: 8px;">FREE</span>
                        </div>
                    </div>
                </div>

                <!-- Highlights / Specs -->
                <div style="display: flex; gap: 40px; margin-top: 24px;">
                    <div style="color: #878787; font-size: 14px; font-weight: 500; width: 100px;">Highlights</div>
                    <div style="font-size: 14px; color: #212121;">
                         <ul style="padding-left: 16px; margin: 0; line-height: 24px;">
                            <li>Genuine <?php echo htmlspecialchars($product['category_name']); ?> Product</li>
                            <li><?php echo !empty($product['stock']) ? 'In Stock' : 'Out of Stock'; ?></li>
                            <?php if (!empty($product['dietary_tags'])): ?>
                                <li>Tags: <?php echo htmlspecialchars($product['dietary_tags']); ?></li>
                            <?php endif; ?>
                            <li>Standard Warranty Applicable</li>
                         </ul>
                    </div>
                </div>

                <!-- Full Description -->
                 <div style="display: flex; gap: 40px; margin-top: 24px;">
                    <div style="color: #878787; font-size: 14px; font-weight: 500; width: 100px;">Description</div>
                    <div class="fp-desc">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                </div>

                <!-- Specifications Table -->
                <div class="fp-section-title" style="margin-top: 40px; border: 1px solid #e0e0e0; padding: 16px; border-bottom: none; font-size: 20px; font-weight: 600;">Specifications</div>
                <table class="fp-specs-table">
                    <tr>
                        <td><span class="fp-specs-label">In The Box</span></td>
                        <td><span class="fp-specs-value">1 x <?php echo htmlspecialchars($product['name']); ?></span></td>
                    </tr>
                    <tr>
                        <td><span class="fp-specs-label">Model Number</span></td>
                        <td><span class="fp-specs-value">GG-<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></span></td>
                    </tr>
                    <tr>
                        <td><span class="fp-specs-label">Brand</span></td>
                        <td><span class="fp-specs-value">Grab & Go Exclusive</span></td>
                    </tr>
                     <tr>
                        <td><span class="fp-specs-label">Category</span></td>
                        <td><span class="fp-specs-value"><?php echo htmlspecialchars($product['category_name']); ?></span></td>
                    </tr>
                     <?php if (stripos($product['category_name'], 'Electronics') !== false || stripos($product['name'], 'Laptop') !== false): ?>
                        <tr><td colspan="2" style="background:#f0f0f0; font-weight:600;">Processor Features</td></tr>
                        <tr><td><span class="fp-specs-label">Processor Brand</span></td><td><span class="fp-specs-value">Intel</span></td></tr>
                        <tr><td><span class="fp-specs-label">Processor Name</span></td><td><span class="fp-specs-value">Core i5</span></td></tr>
                        <tr><td><span class="fp-specs-label">SSD</span></td><td><span class="fp-specs-value">Yes</span></td></tr>
                        <tr><td><span class="fp-specs-label">RAM</span></td><td><span class="fp-specs-value">16 GB</span></td></tr>
                    <?php endif; ?>
                </table>

            </div>
        </div>
    </div>
</div>

<script>
    function changeImage(src) {
        document.getElementById('mainProductImg').src = src;
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
