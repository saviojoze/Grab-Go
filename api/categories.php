<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$query = "SELECT * FROM categories ORDER BY display_order ASC, name ASC";
$result = $conn->query($query);
$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

send_success($categories);
?>
