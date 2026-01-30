<?php
/**
 * RazorpayHelper
 * A lightweight wrapper for Razorpay API integration
 */
class RazorpayHelper {
    private $keyId;
    private $keySecret;
    private $baseUrl = 'https://api.razorpay.com/v1/';

    public function __construct() {
        $this->keyId = RAZORPAY_KEY_ID;
        $this->keySecret = RAZORPAY_KEY_SECRET;
    }

    /**
     * Create an order in Razorpay
     * @param float $amount Amount in INR
     * @param string $receiptId Internal Receipt ID
     * @return array|false Response from Razorpay or false on failure
     */
    public function createOrder($amount, $receiptId) {
        $url = $this->baseUrl . 'orders';
        
        $data = [
            'amount' => $amount * 100, // Amount in paise
            'currency' => 'INR',
            'receipt' => $receiptId,
            'payment_capture' => 1 // Auto capture
        ];

        return $this->request('POST', $url, $data);
    }

    /**
     * Verify payment signature
     * @param array $attributes [razorpay_order_id, razorpay_payment_id, razorpay_signature]
     * @return bool
     */
    public function verifySignature($attributes) {
        $expectedSignature = hash_hmac(
            'sha256', 
            $attributes['razorpay_order_id'] . '|' . $attributes['razorpay_payment_id'], 
            $this->keySecret
        );

        return hash_equals($expectedSignature, $attributes['razorpay_signature']);
    }

    /**
     * Send HTTP request to Razorpay
     */
    private function request($method, $url, $data = []) {
        $ch = curl_init();
        
        // Set basic auth
        curl_setopt($ch, CURLOPT_USERPWD, $this->keyId . ':' . $this->keySecret);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }

        return false;
    }
}
?>
