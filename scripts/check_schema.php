<?php
require_once 'config.php';
$res = $conn->query("SHOW COLUMNS FROM users");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ') ' . ($row['Null']=='YES'?'NULL':'NOT NULL') . ' ' . ($row['Default']===null?'NO DEFAULT':$row['Default']) . "\n";
}
?>
