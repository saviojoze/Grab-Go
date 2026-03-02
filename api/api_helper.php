<?php
/**
 * API Helper Functions
 * Standardizes JSON responses for the mobile app
 */

header('Content-Type: application/json');

// Handle CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

/**
 * Send a JSON response
 */
function send_response($success, $message = '', $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Handle API errors
 */
function send_error($message, $code = 400) {
    send_response(false, $message, null, $code);
}

/**
 * Handle API success
 */
function send_success($data = null, $message = 'Success') {
    send_response(true, $message, $data);
}

/**
 * Get JSON input from request body
 */
function get_json_input() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

/**
 * Authenticate API request using Bearer token (if needed in future)
 * For now, we rely on the specific endpoints to handle their own auth logic
 */
function authenticate_api() {
    // This can be expanded to check for JWT or Firebase tokens
    // For simplicity, we'll start with essential endpoints
}
?>
