<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$category_id = $_GET['category_id'] ?? null;
$search = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? null;
$max_price = $_GET['max_price'] ?? null;
$limit = $_GET['limit'] ?? 20;
$offset = $_GET['offset'] ?? 0;

$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";
$params = [];
$types = "";

if ($category_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if ($min_price !== null) {
    $query .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price !== null) {
    $query .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$params[] = (int)$limit;
$params[] = (int)$offset;
$types .= "ii";

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    // Add full URL for images
    if ($row['image_url'] && !filter_var($row['image_url'], FILTER_VALIDATE_URL)) {
        // Assuming BASE_URL is defined and correct
        // But for mobile, we need the machine IP. Let's provide relative path for now
        // and handle base URL on mobile side.
    }
    $products[] = $row;
}

send_success($products);
?>
