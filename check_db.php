<?php
require 'config.php';
$res = $conn->query("DESC products");
$rows = [];
while($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows, JSON_PRETTY_PRINT);
?>
