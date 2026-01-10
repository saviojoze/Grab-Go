<?php
require_once __DIR__ . '/config.php';

// Redirect based on login status
if (is_logged_in()) {
    redirect('products/listing.php');
} else {
    redirect('auth/login.php');
}
?>
