<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    send_error('User ID is required');
}

switch ($method) {
    case 'GET':
        $query = "SELECT c.*, p.name, p.price, p.image_url, p.unit 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_items = [];
        while ($row = $result->fetch_assoc()) {
            $cart_items[] = $row;
        }
        send_success($cart_items);
        break;

    case 'POST':
        $input = get_json_input();
        $product_id = $input['product_id'] ?? null;
        $quantity = $input['quantity'] ?? 1;

        if (!$product_id) send_error('Product ID is required');

        // Check if item already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            $new_quantity = $item['quantity'] + $quantity;
            $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_quantity, $item['id']);
            $update_stmt->execute();
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
            $insert_stmt->execute();
        }
        send_success(null, 'Item added to cart');
        break;

    case 'DELETE':
        $product_id = $_GET['product_id'] ?? null;
        if ($product_id) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
        } else {
            // Clear entire cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
        }
        $stmt->execute();
        send_success(null, 'Item removed from cart');
        break;

    case 'PUT': // Using PUT for quantity updates
        $input = get_json_input();
        $product_id = $input['product_id'] ?? null;
        $quantity = $input['quantity'] ?? null;

        if (!$product_id || $quantity === null) send_error('Product ID and quantity are required');

        if ($quantity <= 0) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
        } else {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        }
        $stmt->execute();
        send_success(null, 'Cart updated');
        break;

    default:
        send_error('Method not allowed', 405);
}
?>
