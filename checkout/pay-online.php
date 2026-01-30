<?php
$page_title = 'Pay Online - Grab & Go';
require_once __DIR__ . '/../config.php';

if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$order_number = $_GET['order'] ?? '';
$user_id = get_user_id();

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->bind_param("si", $order_number, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order || $order['payment_method'] !== 'online') {
    redirect('../orders/my-orders.php');
}

if ($order['payment_status'] === 'paid') {
    redirect('../orders/confirmation.php?order=' . $order_number);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-xl">
    <div style="max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center;">
        
        <div style="margin-bottom: 20px;">
            <img src="https://cdn.razorpay.com/logo.png" alt="Razorpay" style="height: 48px; margin-bottom: 15px;">
            <h2>Complete Your Payment</h2>
            <p class="text-secondary">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
        </div>

        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
            <p class="text-sm text-secondary uppercase tracking-wider font-bold">Total Amount</p>
            <div style="font-size: 2.5rem; font-weight: 800; color: #1565c0;">
                ₹<?php echo number_format($order['total'], 2); ?>
            </div>
        </div>

        <button id="rzp-button1" class="btn btn-primary btn-block btn-lg" style="width: 100%; padding: 15px; font-size: 1.1rem;">
            Pay Now with Razorpay
        </button>

        <p style="margin-top: 20px; font-size: 0.9rem; color: #718096;">
            Secure payment powered by Razorpay
        </p>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?php echo RAZORPAY_KEY_ID; ?>", 
    "amount": "<?php echo $order['total'] * 100; ?>", 
    "currency": "INR",
    "name": "Grab & Go",
    "description": "Payment for Order #<?php echo $order['order_number']; ?>",
    "image": "<?php echo BASE_URL; ?>logo.png",
    "order_id": "<?php echo $order['razorpay_order_id']; ?>", 
    "handler": function (response){
        // Submit response to verify-payment.php
        var form = document.createElement("form");
        form.method = "POST";
        form.action = "verify-payment.php";

        var field1 = document.createElement("input");
        field1.type = "hidden";
        field1.name = "razorpay_payment_id";
        field1.value = response.razorpay_payment_id;
        form.appendChild(field1);

        var field2 = document.createElement("input");
        field2.type = "hidden";
        field2.name = "razorpay_order_id";
        field2.value = response.razorpay_order_id;
        form.appendChild(field2);

        var field3 = document.createElement("input");
        field3.type = "hidden";
        field3.name = "razorpay_signature";
        field3.value = response.razorpay_signature;
        form.appendChild(field3);
        
        var field4 = document.createElement("input");
        field4.type = "hidden";
        field4.name = "internal_order_id";
        field4.value = "<?php echo $order['order_number']; ?>";
        form.appendChild(field4);

        document.body.appendChild(form);
        form.submit();
    },
    "prefill": {
        "name": "<?php echo htmlspecialchars($order['contact_name']); ?>",
        "email": "<?php echo htmlspecialchars($order['contact_email']); ?>",
        "contact": "<?php echo htmlspecialchars($order['contact_phone']); ?>"
    },
    "theme": {
        "color": "#1565c0"
    }
};
var rzp1 = new Razorpay(options);
document.getElementById('rzp-button1').onclick = function(e){
    rzp1.open();
    e.preventDefault();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
