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
        $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            // Fetch items for each order
            $order_id = $row['id'];
            $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $items_stmt->bind_param("i", $order_id);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();
            $items = [];
            while ($item_row = $items_result->fetch_assoc()) {
                $items[] = $item_row;
            }
            $row['items'] = $items;
            $orders[] = $row;
        }
        send_success($orders);
        break;

    case 'POST':
        $input = get_json_input();
        
        // Validation (basic)
        if (!isset($input['pickup_date']) || !isset($input['pickup_time'])) {
            send_error('Pickup date and time are required');
        }

        $order_number = 'GBG-' . strtoupper(bin2hex(random_bytes(4)));
        $pickup_date = $input['pickup_date'];
        $pickup_time = $input['pickup_time'];
        $contact_name = $input['contact_name'] ?? '';
        $contact_email = $input['contact_email'] ?? '';
        $contact_phone = $input['contact_phone'] ?? '';
        $payment_method = $input['payment_method'] ?? 'cash';
        
        // Start transaction
        $conn->begin_transaction();

        try {
            // 1. Get cart items to calculate totals
            $cart_query = "SELECT c.*, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?";
            $cart_stmt = $conn->prepare($cart_query);
            $cart_stmt->bind_param("i", $user_id);
            $cart_stmt->execute();
            $cart_result = $cart_stmt->get_result();
            
            if ($cart_result->num_rows === 0) {
                throw new Exception('Cart is empty');
            }

            $subtotal = 0;
            $cart_items = [];
            while ($item = $cart_result->fetch_assoc()) {
                $subtotal += $item['price'] * $item['quantity'];
                $cart_items[] = $item;
            }

            $total = $subtotal; // Add tax, delivery etc if needed

            // 2. Insert Order
            $order_stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, pickup_date, pickup_time, contact_name, contact_email, contact_phone, payment_method, subtotal, total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $order_stmt->bind_param("sissssssdd", $order_number, $user_id, $pickup_date, $pickup_time, $contact_name, $contact_email, $contact_phone, $payment_method, $subtotal, $total);
            $order_stmt->execute();
            $order_id = $conn->insert_id;

            // 3. Insert Order Items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $item_stmt->bind_param("iisid", $order_id, $item['product_id'], $item['name'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }

            // 4. Clear Cart
            $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $clear_cart->bind_param("i", $user_id);
            $clear_cart->execute();

            $conn->commit();
            send_success(['order_id' => $order_id, 'order_number' => $order_number], 'Order placed successfully');

        } catch (Exception $e) {
            $conn->rollback();
            send_error('Failed to place order: ' . $e->getMessage());
        }
        break;

    default:
        send_error('Method not allowed', 405);
}
?>
