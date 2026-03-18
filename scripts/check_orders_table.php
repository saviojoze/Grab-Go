<?php
require_once 'config.php';
$result = $conn->query("DESCRIBE orders");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
