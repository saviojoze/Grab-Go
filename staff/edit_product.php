<?php
$page_title = 'Edit Product - Staff';
$current_page = 'products';

require_once 'staff_middleware.php';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    redirect('products.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
    $stock = intval($_POST['stock']);
    $unit = sanitize_input($_POST['unit'] ?? 'units');
    $description = sanitize_input($_POST['description']);
    $dietary_tags = sanitize_input($_POST['dietary_tags']);
    $is_sale = isset($_POST['is_sale']) ? 1 : 0;
    
    // Get current image
    $current_image = $_POST['current_image'];
    $image_url = $current_image;
    
    // Handle image upload - only if a file was actually selected
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK && $_FILES['image']['size'] > 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Check if upload directory exists, create if not
            $upload_dir = '../images/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists and is not a placeholder
                if ($current_image && $current_image != '' && file_exists('../' . $current_image)) {
                    @unlink('../' . $current_image);
                }
                $image_url = 'images/products/' . $new_filename;
            } else {
                $_SESSION['error'] = 'Failed to upload image. Please try again.';
            }
        } else {
            $_SESSION['error'] = 'Invalid image format. Only JPG, PNG, and WebP are allowed.';
        }
    }
    
    // Update product
    $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, price=?, original_price=?, stock=?, unit=?, image_url=?, description=?, dietary_tags=?, is_sale=? WHERE id=?");
    $stmt->bind_param("siddisssiii", $name, $category_id, $price, $original_price, $stock, $unit, $image_url, $description, $dietary_tags, $is_sale, $product_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Product updated successfully!';
        redirect('products.php');
    } else {
        $_SESSION['error'] = 'Failed to update product. Please try again.';
    }
}

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    redirect('products.php');
}

require_once 'header.php';

// Get categories for selection
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY display_order");
?>

<?php require_once 'sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main">
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Edit Product</h1>
                <p class="text-secondary">Update product information</p>
            </div>
            <div class="page-actions">
                <a href="products.php" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to Products
                </a>
            </div>
        </div>
        
        <!-- Product Form -->
        <div class="form-card">
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                
                <div class="form-grid">
                    <!-- Product Name -->
                    <div class="form-group form-group-full">
                        <label for="name" class="form-label">Product Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input" 
                            required
                            value="<?php echo htmlspecialchars($product['name']); ?>"
                        >
                    </div>
                    
                    <!-- Category -->
                    <div class="form-group">
                        <label for="category_id" class="form-label">Category *</label>
                        <select id="category_id" name="category_id" class="form-input" required>
                            <option value="">Select a category</option>
                            <?php while ($category = $categories_result->fetch_assoc()): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Price -->
                    <div class="form-group">
                        <label for="price" class="form-label">Price (₹) *</label>
                        <input 
                            type="number" 
                            id="price" 
                            name="price" 
                            class="form-input" 
                            step="0.01" 
                            min="0"
                            required
                            value="<?php echo $product['price']; ?>"
                        >
                    </div>
                    
                    <!-- Original Price -->
                    <div class="form-group">
                        <label for="original_price" class="form-label">Original Price (₹)</label>
                        <input 
                            type="number" 
                            id="original_price" 
                            name="original_price" 
                            class="form-input" 
                            step="0.01" 
                            min="0"
                            value="<?php echo $product['original_price']; ?>"
                        >
                        <small class="form-hint">Leave empty if not on sale</small>
                    </div>
                    
                    <!-- Stock -->
                    <div class="form-group">
                        <label for="stock" class="form-label">Stock Quantity *</label>
                        <input 
                            type="number" 
                            id="stock" 
                            name="stock" 
                            class="form-input" 
                            min="0"
                            required
                            value="<?php echo $product['stock']; ?>"
                        >
                    </div>

                    <!-- Unit -->
                    <div class="form-group">
                        <label for="unit" class="form-label">Unit *</label>
                        <select id="unit" name="unit" class="form-input" required>
                            <option value="units" <?php echo ($product['unit'] ?? 'units') == 'units' ? 'selected' : ''; ?>>units (items)</option>
                            <option value="kg" <?php echo ($product['unit'] ?? '') == 'kg' ? 'selected' : ''; ?>>kg (kilograms)</option>
                            <option value="g" <?php echo ($product['unit'] ?? '') == 'g' ? 'selected' : ''; ?>>g (grams)</option>
                            <option value="L" <?php echo ($product['unit'] ?? '') == 'L' ? 'selected' : ''; ?>>L (liters)</option>
                            <option value="ml" <?php echo ($product['unit'] ?? '') == 'ml' ? 'selected' : ''; ?>>ml (milliliters)</option>
                            <option value="pack" <?php echo ($product['unit'] ?? '') == 'pack' ? 'selected' : ''; ?>>pack</option>
                            <option value="loaf" <?php echo ($product['unit'] ?? '') == 'loaf' ? 'selected' : ''; ?>>loaf</option>
                            <option value="bunch" <?php echo ($product['unit'] ?? '') == 'bunch' ? 'selected' : ''; ?>>bunch</option>
                        </select>
                        <small class="form-hint">Unit measurement for stock</small>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group form-group-full">
                        <label for="description" class="form-label">Description</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-input" 
                            rows="4"
                        ><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <!-- Dietary Tags -->
                    <div class="form-group">
                        <label for="dietary_tags" class="form-label">Dietary Tags</label>
                        <input 
                            type="text" 
                            id="dietary_tags" 
                            name="dietary_tags" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($product['dietary_tags']); ?>"
                        >
                        <small class="form-hint">Separate multiple tags with commas</small>
                    </div>
                    
                    <!-- Product Image -->
                    <div class="form-group">
                        <label for="image" class="form-label">Product Image</label>
                        <input 
                            type="file" 
                            id="image" 
                            name="image" 
                            class="form-input" 
                            accept="image/jpeg,image/png,image/webp"
                            onchange="previewImage(this)"
                        >
                        <small class="form-hint">Max 5MB. JPG, PNG, or WebP</small>
                    </div>
                    
                    <!-- Current Image Preview -->
                    <div class="form-group form-group-full">
                        <label class="form-label">Current Image</label>
                        <div class="image-preview" id="imagePreview">
                            <?php if (!empty($product['image_url'])): ?>
                                <img 
                                    id="previewImg" 
                                    src="../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                    alt="Product Image"
                                    onerror="this.onerror=null; this.src='../images/placeholder.jpg'"
                                >
                            <?php else: ?>
                                <img 
                                    id="previewImg" 
                                    src="../images/placeholder.jpg" 
                                    alt="No Image"
                                >
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Is Sale -->
                    <div class="form-group form-group-full">
                        <label class="checkbox-label">
                            <input type="checkbox" id="is_sale" name="is_sale" <?php echo $product['is_sale'] ? 'checked' : ''; ?>>
                            <span>Mark as on sale</span>
                        </label>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function previewImage(input) {
    const previewImg = document.getElementById('previewImg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'footer.php'; ?>
