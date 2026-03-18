<?php
$page_title = 'Categories Management - Admin';
$current_page = 'categories';

require_once 'admin_middleware.php';

// Handle add category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = sanitize_input($_POST['name']);
    $icon = sanitize_input($_POST['icon']);
    $display_order = intval($_POST['display_order']);
    
    $stmt = $conn->prepare("INSERT INTO categories (name, icon, display_order) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $name, $icon, $display_order);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Category added successfully!';
    } else {
        $_SESSION['error'] = 'Failed to add category.';
    }
    redirect('categories.php');
}

// Handle update category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = intval($_POST['id']);
    $name = sanitize_input($_POST['name']);
    $icon = sanitize_input($_POST['icon']);
    $display_order = intval($_POST['display_order']);
    
    $stmt = $conn->prepare("UPDATE categories SET name=?, icon=?, display_order=? WHERE id=?");
    $stmt->bind_param("ssii", $name, $icon, $display_order, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Category updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update category.';
    }
    redirect('categories.php');
}

// Handle delete category
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Check if category has products
    $check = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        $_SESSION['error'] = 'Cannot delete category with existing products. Please reassign or delete products first.';
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Category deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete category.';
        }
    }
    redirect('categories.php');
}

require_once 'header.php';

// Get all categories
$categories_query = "SELECT c.*, COUNT(prod.id) as product_count 
                     FROM categories c 
                     LEFT JOIN products prod ON c.id = prod.category_id 
                     GROUP BY c.id 
                     ORDER BY c.display_order";
$categories_result = $conn->query($categories_query);
?>

<?php require_once 'sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main">
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Categories</h1>
                <p class="text-secondary">Manage product categories</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add Category
                </button>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Categories Grid -->
        <div class="categories-grid">
            <?php while ($category = $categories_result->fetch_assoc()): ?>
                <div class="category-card">
                    <div class="category-icon"><?php echo htmlspecialchars($category['icon']); ?></div>
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="text-secondary"><?php echo $category['product_count']; ?> products</p>
                        <span class="category-order">Order: <?php echo $category['display_order']; ?></span>
                    </div>
                    <div class="category-actions">
                        <button 
                            class="btn-icon" 
                            onclick='openEditModal(<?php echo json_encode($category); ?>)'
                            title="Edit"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button 
                            class="btn-icon btn-icon-danger" 
                            onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', <?php echo $category['product_count']; ?>)"
                            title="Delete"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<!-- Add Category Modal -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Category</h3>
            <button class="modal-close" onclick="closeAddModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_name" class="form-label">Category Name *</label>
                    <input type="text" id="add_name" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="add_icon" class="form-label">Icon (Emoji) *</label>
                    <input type="text" id="add_icon" name="icon" class="form-input" placeholder="🥬" required>
                </div>
                <div class="form-group">
                    <label for="add_display_order" class="form-label">Display Order *</label>
                    <input type="number" id="add_display_order" name="display_order" class="form-input" value="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Category</h3>
            <button class="modal-close" onclick="closeEditModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit_id" name="id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name" class="form-label">Category Name *</label>
                    <input type="text" id="edit_name" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="edit_icon" class="form-label">Icon (Emoji) *</label>
                    <input type="text" id="edit_icon" name="icon" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="edit_display_order" class="form-label">Display Order *</label>
                    <input type="number" id="edit_display_order" name="display_order" class="form-input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</div>


<script>
function openAddModal() {
    document.getElementById('addModal').classList.add('show');
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('show');
}

function openEditModal(category) {
    document.getElementById('edit_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_icon').value = category.icon;
    document.getElementById('edit_display_order').value = category.display_order;
    document.getElementById('editModal').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
}

function deleteCategory(id, name, productCount) {
    if (productCount > 0) {
        alert(`Cannot delete "${name}" because it has ${productCount} product(s). Please reassign or delete the products first.`);
        return;
    }
    
    if (confirm(`Are you sure you want to delete "${name}"?`)) {
        window.location.href = 'categories.php?delete=' + id;
    }
}
</script>

<?php require_once 'footer.php'; ?>
