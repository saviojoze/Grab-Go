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
        // Create new category
        $input = get_json_input();
        $name = trim($input['name'] ?? '');
        $display_order = intval($input['display_order'] ?? 0);
        $icon = trim($input['icon'] ?? '');

        if (!$name) send_error('Name is required');

        $stmt = $conn->prepare("INSERT INTO categories (name, display_order, icon) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $name, $display_order, $icon);

        if ($stmt->execute()) {
            send_success(['id' => $conn->insert_id], 'Category created successfully');
        } else {
            send_error('Failed to create category');
        }
        break;

    case 'PUT':
        // Update category
        $input = get_json_input();
        $id = intval($input['id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $display_order = intval($input['display_order'] ?? 0);
        $icon = trim($input['icon'] ?? '');

        if (!$id || !$name) send_error('Category ID and name are required');

        $stmt = $conn->prepare("UPDATE categories SET name=?, display_order=?, icon=? WHERE id=?");
        $stmt->bind_param("sisi", $name, $display_order, $icon, $id);

        if ($stmt->execute()) {
            send_success(null, 'Category updated successfully');
        } else {
            send_error('Failed to update category');
        }
        break;

    case 'DELETE':
        // Delete category
        $category_id = intval($_GET['category_id'] ?? 0);
        if (!$category_id) send_error('Category ID required');

        // Only admin can delete
        if ($role !== 'admin') send_error('Only admins can delete categories', 403);

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);

        if ($stmt->execute()) {
            send_success(null, 'Category deleted successfully');
        } else {
            send_error('Failed to delete category');
        }
        break;

    default:
        send_error('Method not allowed', 405);
}
?>
