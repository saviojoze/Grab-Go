<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/RazorpayHelper.php';
require_once __DIR__ . '/api_helper.php';

$input = get_json_input();

if (!isset($input['razorpay_payment_id']) || !isset($input['razorpay_order_id']) || !isset($input['razorpay_signature']) || !isset($input['order_number'])) {
    send_error('Missing required payment verification details');
}

$razorpay_payment_id = $input['razorpay_payment_id'];
$razorpay_order_id = $input['razorpay_order_id'];
$razorpay_signature = $input['razorpay_signature'];
$order_number = $input['order_number'];

$helper = new RazorpayHelper();
$attributes = [
    'razorpay_order_id' => $razorpay_order_id,
    'razorpay_payment_id' => $razorpay_payment_id,
    'razorpay_signature' => $razorpay_signature
];

if ($helper->verifySignature($attributes)) {
    $payment_status = 'paid';
    
    // Update Order
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, transaction_id = ? WHERE order_number = ?");
    $stmt->bind_param("sss", $payment_status, $razorpay_payment_id, $order_number);
    
    if ($stmt->execute()) {
        send_success(['status' => 'paid'], 'Payment Verified Successfully!');
    } else {
        send_error('Payment verified but failed to update database');
    }
} else {
    $fail_status = 'failed';
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE order_number = ?");
    $stmt->bind_param("ss", $fail_status, $order_number);
    $stmt->execute();
    
    send_error('Payment verification failed! Invalid signature.');
}
?>
