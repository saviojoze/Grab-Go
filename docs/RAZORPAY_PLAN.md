# Implementation Plan - Razorpay Integration

To enable online payments via Razorpay for the "Grab & Go" application.

## 1. Database Updates
We need to track payment status and Razorpay details in the `orders` table.

- **Add Column**: `payment_status` (enum: 'pending', 'paid', 'failed') DEFAULT 'pending'
- **Add Column**: `razorpay_order_id` (varchar)
- **Add Column**: `transaction_id` (varchar) - maps to `razorpay_payment_id`

## 2. Configuration Setup
Store strictly sensitive keys in `.env` and expose them via `config.php`.

- **Action**: Update `.env` with `RAZORPAY_KEY_ID` and `RAZORPAY_KEY_SECRET`.
- **Action**: Update `config.php` to define `RAZORPAY_KEY_ID` and `RAZORPAY_KEY_SECRET`.

## 3. Backend Logic (RazorpayHelper)
Since we are avoiding Composer for simplicity in this specific environment, we will create a lightweight helper class to interact with Razorpay's API.

- **File**: `includes/RazorpayHelper.php`
- **Capabilities**:
    - `createOrder($amount, $receiptId)`: Calls Razorpay API to generate an Order ID.
    - `verifySignature($attributes)`: Verifies the webhook/callback signature.

## 4. Checkout Logic Update
Modify the order processing flow to handle "Pay Online" differently from "Cash".

- **File**: `checkout/process-order.php`
- **Logic**:
    - IF `payment_method` is 'online':
        - Create the order in DB with status 'pending' (or 'awaiting_payment').
        - Redirect to a new page: `checkout/pay-online.php?order_id=...`
    - ELSE (Cash/Card):
        - Process normally and redirect to `confirmation.php`.

## 5. Payment Page (`checkout/pay-online.php`)
A completely new page dedicated to the payment step. This ensures that even if payment fails or the window is closed, the order is saved and the user can retry.

- **Features**:
    - Displays Order Summary (Total Amount).
    - Initializes Razorpay Checkout via Javascript.
    - "Pay Now" button triggers the Razorpay modal.
    - On Success: POSTs data to `checkout/verify-payment.php`.

## 6. Payment Verification (`checkout/verify-payment.php`)
Validates the payment on the server side.

- **Logic**:
    - Receives `razorpay_payment_id`, `razorpay_order_id`, `razorpay_signature`.
    - Uses `RazorpayHelper` to verify signature.
    - IF Valid:
        - Update Order `payment_status` = 'paid'
        - Update Order `transaction_id` = `razorpay_payment_id`
        - Redirect to `orders/confirmation.php`.
    - IF Invalid:
        - Show error message.

## 7. Confirmation Page Update
- **File**: `orders/confirmation.php`
- **Logic**: Show "Payment Status: Paid" if applicable.
