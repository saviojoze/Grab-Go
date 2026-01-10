<?php
require_once __DIR__ . '/../config.php';

// Destroy session
session_destroy();

// Redirect to login
redirect('../auth/login.php');
?>
