<?php
$page_title = 'Add Product - Admin';
$current_page = 'products';

require_once 'admin_middleware.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
    $stock = intval($_POST['stock']);
    $description = sanitize_input($_POST['description']);
    $dietary_tags = sanitize_input($_POST['dietary_tags']);
    $is_sale = isset($_POST['is_sale']) ? 1 : 0;
    
    // Handle image upload
    $image_url = null;
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
                $image_url = 'images/products/' . $new_filename;
            } else {
                $_SESSION['error'] = 'Failed to upload image. Please try again.';
            }
        } else {
            $_SESSION['error'] = 'Invalid image format. Only JPG, PNG, and WebP are allowed.';
        }
    }
    
    // Insert product
    $stmt = $conn->prepare("INSERT INTO products (name, category_id, price, original_price, stock, image_url, description, dietary_tags, is_sale) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siddisssi", $name, $category_id, $price, $original_price, $stock, $image_url, $description, $dietary_tags, $is_sale);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Product added to inventory';
        redirect('products.php');
    } else {
        $_SESSION['error'] = 'Failed to add product. Please try again.';
    }
}

require_once 'header.php';

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<?php require_once 'sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main">
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Add Product</h1>
                <p class="text-secondary">Add a new product to your inventory</p>
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
                            placeholder="e.g., Organic Bananas"
                        >
                    </div>
                    
                    <!-- Category -->
                    <div class="form-group">
                        <label for="category_id" class="form-label">Category *</label>
                        <select id="category_id" name="category_id" class="form-input" required>
                            <option value="">Select a category</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
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
                            placeholder="9.99"
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
                            placeholder="12.99 (optional)"
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
                            placeholder="100"
                        >
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group form-group-full">
                        <label for="description" class="form-label">Description</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-input" 
                            rows="4"
                            placeholder="Enter product description..."
                        ></textarea>
                    </div>
                    
                    <!-- Dietary Tags -->
                    <div class="form-group">
                        <label for="dietary_tags" class="form-label">Dietary Tags</label>
                        <input 
                            type="text" 
                            id="dietary_tags" 
                            name="dietary_tags" 
                            class="form-input" 
                            placeholder="e.g., Vegan, Organic, Gluten-Free"
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
                    
                    <!-- Image Preview -->
                    <div class="form-group form-group-full">
                        <div id="imagePreview" class="image-preview" style="display: none;">
                            <img id="previewImg" src="" alt="Preview">
                        </div>
                    </div>
                    
                    <!-- Is Sale -->
                    <div class="form-group form-group-full">
                        <label class="checkbox-label">
                            <input type="checkbox" id="is_sale" name="is_sale">
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
                        Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'footer.php'; ?>
