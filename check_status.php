<?php
$conn = new mysqli('shuttle.proxy.rlwy.net', 'root', 'VmabgDADhVihmKpeEsHVEJyQJMJqenNU', 'railway', 22618);
if ($conn->connect_error) die("Conn fail");

$tables = ['categories', 'products', 'users', 'staff_attendance'];
foreach ($tables as $t) {
    if ($res = $conn->query("SELECT COUNT(*) FROM $t")) {
        echo "$t: " . $res->fetch_row()[0] . "\n";
    } else {
        echo "$t: table not found or error\n";
    }
}
?>
