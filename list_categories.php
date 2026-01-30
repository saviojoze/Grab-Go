<?php
$conn = new mysqli('localhost', 'root', '', 'grab_and_go');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$res = $conn->query("SELECT c.id, c.name, COUNT(p.id) as p_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id");
while ($row = $res->fetch_assoc()) {
    echo "{$row['id']} | {$row['name']} | Products: {$row['p_count']}\n";
}
?>
