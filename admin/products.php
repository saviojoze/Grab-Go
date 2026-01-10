<?php
$page_title = 'Products Management - Admin';
$current_page = 'products';

require_once 'admin_middleware.php';
require_once 'header.php';

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$stock_filter = $_GET['stock'] ?? '';

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";

if ($search) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (p.name LIKE '%$search_term%' OR p.description LIKE '%$search_term%')";
}

if ($category_filter) {
    $category_id = intval($category_filter);
    $query .= " AND p.category_id = $category_id";
}

if ($stock_filter == 'low') {
    $query .= " AND p.stock < 10";
} elseif ($stock_filter == 'out') {
    $query .= " AND p.stock = 0";
}

$query .= " ORDER BY p.created_at DESC";

$products = $conn->query($query);

// Get categories for filter
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<?php require_once 'sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main">
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Products</h1>
                <p class="text-secondary">Manage your product inventory</p>
            </div>
            <div class="page-actions">
                <a href="add_product.php" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add Product
                </a>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-bar">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search products..." 
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="filter-input"
                    >
                </div>
                
                <div class="filter-group">
                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="stock" class="filter-select">
                        <option value="">All Stock Levels</option>
                        <option value="low" <?php echo $stock_filter == 'low' ? 'selected' : ''; ?>>Low Stock</option>
                        <option value="out" <?php echo $stock_filter == 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Filter
                </button>
                
                <?php if ($search || $category_filter || $stock_filter): ?>
                    <a href="products.php" class="btn btn-text">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Products Table -->
        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products && $products->num_rows > 0): ?>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <img 
                                            src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.jpg'); ?>" 
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            class="product-thumb"
                                            onerror="this.onerror=null; this.src='../images/placeholder.jpg'"
                                        >
                                        <div>
                                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <p class="text-secondary text-sm">
                                                <?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 50)); ?>
                                                <?php echo strlen($product['description'] ?? '') > 50 ? '...' : ''; ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="category-badge">
                                        <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="price-cell">
                                        <span class="price-current">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <?php if ($product['original_price']): ?>
                                            <span class="price-original">₹<?php echo number_format($product['original_price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $stock = $product['stock'];
                                    $stock_class = $stock == 0 ? 'stock-out' : ($stock < 10 ? 'stock-low' : 'stock-good');
                                    ?>
                                    <span class="stock-badge <?php echo $stock_class; ?>">
                                        <?php echo $stock; ?> units
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product['is_sale']): ?>
                                        <span class="badge badge-success">On Sale</span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">Regular</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-icon" title="Edit">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </a>
                                        <button 
                                            class="btn-icon btn-icon-danger delete-product-btn" 
                                            data-id="<?php echo $product['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                            title="Delete"
                                        >
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary">
                                No products found. <a href="add_product.php">Add your first product</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete Product</h3>
            <button class="modal-close" onclick="closeDeleteModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete <strong id="deleteProductName"></strong>?</p>
            <p class="text-secondary">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn btn-danger" id="confirmDeleteBtn">Delete Product</button>
        </div>
    </div>
</div>

<script>
let deleteProductId = null;

// Delete product handlers
document.querySelectorAll('.delete-product-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        deleteProductId = this.dataset.id;
        document.getElementById('deleteProductName').textContent = this.dataset.name;
        document.getElementById('deleteModal').classList.add('show');
    });
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteProductId) {
        window.location.href = 'delete_product.php?id=' + deleteProductId;
    }
});

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    deleteProductId = null;
}
</script>

<?php require_once 'footer.php'; ?>
