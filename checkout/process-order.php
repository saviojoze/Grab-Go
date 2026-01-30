<?php
require_once __DIR__ . '/../config.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    redirect('../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

$user_id = get_user_id();

// Get form data
$pickup_date = sanitize_input($_POST['pickup_date'] ?? '');
$pickup_time = sanitize_input($_POST['pickup_time'] ?? '');
$contact_name = sanitize_input($_POST['contact_name'] ?? '');
$contact_email = sanitize_input($_POST['contact_email'] ?? '');
$contact_phone = sanitize_input($_POST['contact_phone'] ?? '');
$payment_method = sanitize_input($_POST['payment_method'] ?? 'cash');

// Validate required fields
if (empty($pickup_date) || empty($pickup_time) || empty($contact_name) || empty($contact_email) || empty($contact_phone)) {
    redirect('checkout.php?error=required');
}

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = $user_id";
$cart_result = $conn->query($cart_query);

if ($cart_result->num_rows === 0) {
    redirect('../cart/cart.php');
}

// Calculate totals
$subtotal = 0;
$cart_items = [];
while ($item = $cart_result->fetch_assoc()) {
    $item['total'] = $item['price'] * $item['quantity'];
    $subtotal += $item['total'];
    $cart_items[] = $item;
}

$discount = 0;
$delivery = 0;
$total = $subtotal - $discount + $delivery;

// Generate unique order number
$order_number = 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 8));

// Start transaction
$conn->begin_transaction();

try {
    // Generate unique OTP for this order
    $delivery_otp = sprintf("%06d", mt_rand(100000, 999999));
    
    // Payment Status Logic
    $payment_status = 'pending';
    $razorpay_order_id = null;
    
    // If paying online, generate Razorpay Order ID
    if ($payment_method === 'online') {
        require_once __DIR__ . '/../includes/RazorpayHelper.php';
        $razorpay = new RazorpayHelper();
        
        $rzp_order = $razorpay->createOrder($total, $order_number);
        
        if ($rzp_order && isset($rzp_order['id'])) {
            $razorpay_order_id = $rzp_order['id'];
        } else {
            throw new Exception("Failed to create Razorpay order");
        }
    }

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, pickup_date, pickup_time, contact_name, contact_email, contact_phone, payment_method, subtotal, discount, delivery_fee, total, delivery_otp, payment_status, razorpay_order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissssssddddsss", $order_number, $user_id, $pickup_date, $pickup_time, $contact_name, $contact_email, $contact_phone, $payment_method, $subtotal, $discount, $delivery, $total, $delivery_otp, $payment_status, $razorpay_order_id);
    $stmt->execute();
    
    $order_id = $conn->insert_id;
    
    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($cart_items as $item) {
        $stmt->bind_param("iisid", $order_id, $item['product_id'], $item['name'], $item['quantity'], $item['price']);
        $stmt->execute();
    }
    
    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Redirect based on payment method
    if ($payment_method === 'online') {
        redirect('pay-online.php?order=' . $order_number);
    } else {
        redirect('../orders/confirmation.php?order=' . $order_number);
    }
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    redirect('checkout.php?error=processing');
}
?>
