<?php
/**
 * Staff Middleware
 * Ensures only authenticated staff users can access staff pages
 */

require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error'] = 'Please login to access the staff panel';
    redirect('../auth/login.php');
}

// Check if user is staff or admin (admins can access staff portal but staff cannot access admin portal)
if (!is_staff() && !is_admin()) {
    $_SESSION['error'] = 'Access denied. Staff privileges required.';
    redirect('../products/listing.php');
}

// Staff is authenticated, continue
?>
