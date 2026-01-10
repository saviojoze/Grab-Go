<?php
require_once 'admin_middleware.php';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    $_SESSION['error'] = 'Invalid product ID';
    redirect('products.php');
}

// Get product to delete image
$stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if ($product) {
    // Delete product from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        // Delete image file if exists
        if ($product['image_url'] && file_exists('../' . $product['image_url'])) {
            unlink('../' . $product['image_url']);
        }
        
        $_SESSION['success'] = 'Product deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete product. Please try again.';
    }
} else {
    $_SESSION['error'] = 'Product not found';
}

redirect('products.php');
?>
