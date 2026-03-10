<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$admin_id = $_GET['admin_id'] ?? null;

if (!$admin_id) send_error('Admin ID required', 401);

// Verify admin or staff
$role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$role_stmt->bind_param("i", $admin_id);
$role_stmt->execute();
$role = $role_stmt->get_result()->fetch_assoc()['role'] ?? '';

if ($role !== 'admin' && $role !== 'staff') {
    send_error('Unauthorized', 401);
}

switch ($method) {
    case 'POST':
        // Create new product
        $input = get_json_input();
        $name        = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $price       = floatval($input['price'] ?? 0);
        $stock       = intval($input['stock'] ?? 0);
        $category_id = intval($input['category_id'] ?? 0);
        $unit        = trim($input['unit'] ?? 'units');
        $image_url   = trim($input['image_url'] ?? '');

        if (!$name || $price <= 0) send_error('Name and price are required');

        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, unit, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisss", $name, $description, $price, $stock, $category_id, $unit, $image_url);

        if ($stmt->execute()) {
            send_success(['id' => $conn->insert_id], 'Product created successfully');
        } else {
            send_error('Failed to create product');
        }
        break;

    case 'PUT':
        // Update product
        $input = get_json_input();
        $id          = intval($input['id'] ?? 0);
        $name        = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $price       = floatval($input['price'] ?? 0);
        $stock       = intval($input['stock'] ?? 0);
        $category_id = intval($input['category_id'] ?? 0);
        $unit        = trim($input['unit'] ?? 'units');
        $image_url   = trim($input['image_url'] ?? '');

        if (!$id || !$name) send_error('Product ID and name required');

        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, unit=?, image_url=? WHERE id=?");
        $stmt->bind_param("ssdisssi", $name, $description, $price, $stock, $category_id, $unit, $image_url, $id);

        if ($stmt->execute()) {
            send_success(null, 'Product updated successfully');
        } else {
            send_error('Failed to update product');
        }
        break;

    case 'DELETE':
        // Delete product
        $product_id = intval($_GET['product_id'] ?? 0);
        if (!$product_id) send_error('Product ID required');

        // Only admin can delete
        if ($role !== 'admin') send_error('Only admins can delete products', 403);

        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            send_success(null, 'Product deleted successfully');
        } else {
            send_error('Failed to delete product');
        }
        break;

    default:
        send_error('Method not allowed', 405);
}
?>
