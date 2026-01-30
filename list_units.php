<?php
$conn = new mysqli('localhost', 'root', '', 'grab_and_go');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$res = $conn->query("SELECT id, name, unit FROM products");
while ($row = $res->fetch_assoc()) {
    echo "{$row['id']} | {$row['name']} | Unit: {$row['unit']}\n";
}
?>
