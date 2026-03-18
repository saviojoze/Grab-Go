<?php
require 'config.php';
$res = $conn->query("SELECT * FROM staff_directory LIMIT 1");
print_r($res->fetch_assoc());
?>
