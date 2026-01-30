<?php
/**
 * Admin Middleware
 * Ensures only authenticated admin users can access admin pages
 */

require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error'] = 'Please login to access the admin panel';
    redirect('../auth/login.php');
}

// Check if user is admin
if (!is_admin()) {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    redirect('../products/listing.php');
}

// Admin is authenticated, continue
