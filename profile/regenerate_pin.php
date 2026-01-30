<?php
require_once __DIR__ . '/../config.php';

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = get_user_id();

try {
    // Generate new random 6-digit PIN using cryptographically secure method
    $new_pin = sprintf("%06d", random_int(100000, 999999));

    // Update database
    $stmt = $conn->prepare("UPDATE users SET verification_pin = ? WHERE id = ?");
    $stmt->bind_param("si", $new_pin, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'pin' => $new_pin]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update PIN']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error generating PIN']);
}
?>
