<?php
require_once 'config.php';
$user_id = 1;
$query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($query);
if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " orders for user_id $user_id.\n";
    while ($row = $result->fetch_assoc()) {
        echo "Order: " . $row['order_number'] . " Status: " . $row['status'] . "\n";
    }
} else {
    echo "No orders found for user_id $user_id.\n";
}
?>
