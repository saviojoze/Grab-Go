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
        // Check user role
        $role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $role_stmt->bind_param("i", $user_id);
        $role_stmt->execute();
        $user_role = $role_stmt->get_result()->fetch_assoc()['role'] ?? 'customer';

        if ($user_role === 'staff' || $user_role === 'admin') {
            // Staff can see all orders OR filtered by status
            $status = $_GET['status'] ?? null;
            if ($status) {
                $query = "SELECT o.*, u.full_name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.status = ? ORDER BY o.created_at DESC";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $status);
            } else {
                $query = "SELECT o.*, u.full_name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
                $stmt = $conn->prepare($query);
            }
        } else {
            // Customer can only see their own orders
            $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
        }

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
            $discount = 0;
            $delivery_fee = 0;
            $delivery_otp = sprintf("%06d", mt_rand(100000, 999999));

            // Razorpay logic for online payment
            $razorpay_order_id = null;
            $payment_status = 'pending';

            if ($payment_method === 'online') {
                require_once __DIR__ . '/../includes/RazorpayHelper.php';
                $razorpay = new RazorpayHelper();
                $rzp_order = $razorpay->createOrder($total, $order_number);
                
                if ($rzp_order && isset($rzp_order['id'])) {
                    $razorpay_order_id = $rzp_order['id'];
                } else {
                    throw new Exception('Failed to create Razorpay order. Please check Razorpay configuration.');
                }
            }

            // 2. Insert Order (Updated with more fields including delivery_otp)
            $order_stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, pickup_date, pickup_time, contact_name, contact_email, contact_phone, payment_method, subtotal, discount, delivery_fee, total, status, delivery_otp, payment_status, razorpay_order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
            $order_stmt->bind_param("sissssssddddsss", $order_number, $user_id, $pickup_date, $pickup_time, $contact_name, $contact_email, $contact_phone, $payment_method, $subtotal, $discount, $delivery_fee, $total, $delivery_otp, $payment_status, $razorpay_order_id);
            $order_stmt->execute();
            $order_id = $conn->insert_id;

            // 3. Insert Order Items and deduct stock
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
            
            foreach ($cart_items as $item) {
                $item_stmt->bind_param("iisid", $order_id, $item['product_id'], $item['name'], $item['quantity'], $item['price']);
                $item_stmt->execute();

                // Reduce stock atomically
                $update_stock->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
                $update_stock->execute();
                
                if ($update_stock->affected_rows === 0) {
                    throw new Exception("Item '" . $item['name'] . "' is out of stock!");
                }
            }

            // 4. Clear Cart
            $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $clear_cart->bind_param("i", $user_id);
            $clear_cart->execute();

            $conn->commit();
            send_success([
                'order_id' => $order_id, 
                'order_number' => $order_number,
                'delivery_otp' => $delivery_otp,
                'razorpay_order_id' => $razorpay_order_id,
                'total' => $total
            ], 'Order placed successfully');

        } catch (Exception $e) {
            $conn->rollback();
            send_error('Failed to place order: ' . $e->getMessage());
        }
        break;

    case 'PUT':
        // Update order status (Staff only)
        $input = get_json_input();
        $order_id = $input['order_id'] ?? null;
        $new_status = $input['status'] ?? null;
        $verification_pin = $input['verification_pin'] ?? null;

        if (!$order_id || !$new_status) {
            send_error('Order ID and status are required');
        }

        // Check if user is staff
        $role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $role_stmt->bind_param("i", $user_id);
        $role_stmt->execute();
        $user_role = $role_stmt->get_result()->fetch_assoc()['role'] ?? 'customer';

        if ($user_role !== 'staff' && $user_role !== 'admin') {
            send_error('Unauthorized. Staff only.');
        }

        // Verify OTP if completing
        if ($new_status === 'completed') {
            $stmt_pin = $conn->prepare("SELECT delivery_otp FROM orders WHERE id = ?");
            $stmt_pin->bind_param("i", $order_id);
            $stmt_pin->execute();
            $real_pin = $stmt_pin->get_result()->fetch_assoc()['delivery_otp'] ?? '';
            
            if ($verification_pin !== $real_pin) {
                send_error('Invalid Delivery OTP!');
            }
        }

        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        
        if ($stmt->execute()) {
            send_success(null, 'Order status updated successfully');
        } else {
            send_error('Failed to update order status');
        }
        break;

    default:
        send_error('Method not allowed', 405);
}
?>
