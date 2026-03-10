<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

// Allow file uploads
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Method not allowed', 405);
}

$admin_id = $_POST['admin_id'] ?? null;
if (!$admin_id) send_error('Admin ID required', 401);

// Verify admin or staff
$role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$role_stmt->bind_param("i", $admin_id);
$role_stmt->execute();
$role = $role_stmt->get_result()->fetch_assoc()['role'] ?? '';

if ($role !== 'admin' && $role !== 'staff') {
    send_error('Unauthorized', 401);
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $err = $_FILES['image']['error'] ?? 'No file uploaded';
    send_error('Image upload failed: ' . $err);
}

$file = $_FILES['image'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed_types)) {
    send_error('Invalid file type. Only JPG, PNG, GIF and WebP are allowed.');
}

if ($file['size'] > 5 * 1024 * 1024) {
    send_error('File too large. Maximum size is 5MB.');
}

$ext = match($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
    default      => 'jpg',
};

$filename  = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$upload_dir = __DIR__ . '/../images/products/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$dest = $upload_dir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    send_error('Failed to save image to server.');
}

$relative_path = 'images/products/' . $filename;

send_success([
    'image_url'   => $relative_path,
    'full_url'    => 'http://' . $_SERVER['HTTP_HOST'] . '/' . basename(dirname(__DIR__)) . '/' . $relative_path,
], 'Image uploaded successfully');
?>
