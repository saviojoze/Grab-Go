<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/RazorpayHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}

$razorpay_payment_id = $_POST['razorpay_payment_id'];
$razorpay_order_id = $_POST['razorpay_order_id'];
$razorpay_signature = $_POST['razorpay_signature'];
$internal_order_number = $_POST['internal_order_id'];

$helper = new RazorpayHelper();
$attributes = [
    'razorpay_order_id' => $razorpay_order_id,
    'razorpay_payment_id' => $razorpay_payment_id,
    'razorpay_signature' => $razorpay_signature
];

if ($helper->verifySignature($attributes)) {
    // Determine status based on signature valid
    $payment_status = 'paid';
    
    // Update Order
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, transaction_id = ? WHERE order_number = ?");
    $stmt->bind_param("sss", $payment_status, $razorpay_payment_id, $internal_order_number);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Payment Verified Successfully!";
        redirect('../orders/confirmation.php?order=' . $internal_order_number);
    } else {
        $_SESSION['error'] = "Database update failed. Please contact support.";
        redirect('pay-online.php?order=' . $internal_order_number);
    }

} else {
    // Signature verification failed
    $_SESSION['error'] = "Payment verification failed! Please try again.";
    
    // Mark order as failed payment (optional, or just leave pending)
    $fail_status = 'failed';
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE order_number = ?");
    $stmt->bind_param("ss", $fail_status, $internal_order_number);
    $stmt->execute();
    
    redirect('pay-online.php?order=' . $internal_order_number);
}
?>
