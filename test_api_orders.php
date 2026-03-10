<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$_GET['user_id'] = 1;
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once 'api/orders.php';
?>
