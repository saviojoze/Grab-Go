<?php
require_once 'config.php';
$res = $conn->query('DESCRIBE orders');
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . PHP_EOL;
}
