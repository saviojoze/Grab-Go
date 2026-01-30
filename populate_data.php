<?php
$conn = new mysqli('localhost', 'root', '', 'grab_and_go');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// 1. Remove unwanted empty categories (except Juice, Cake, Soft Drinks)
$unused_ids = [6, 8, 9, 10, 11]; // Meat & Seafood, Snacks, Milk, Cheese, Bread
foreach($unused_ids as $id) {
    $conn->query("DELETE FROM categories WHERE id = $id AND (SELECT COUNT(*) FROM products WHERE category_id = $id) = 0");
}

// 2. Add products for Juices (ID 14)
$juices = [
    ['Fresh Apple Juice', 14, 4.50, 5.00, 40, 'Bottle (1L)', 'images/products/apple-juice.jpg', 'Organic 100% pure apple juice', 'Vegan,Organic'],
    ['Mango Nectar', 14, 5.25, NULL, 30, 'Bottle (1L)', 'images/products/mango-juice.jpg', 'Sweet and delicious mango nectar', 'Vegetarian']
];

// 3. Add products for Cakes (ID 12)
$cakes = [
    ['Chocolate Lava Cake', 12, 15.00, 18.00, 15, 'Piece', 'images/products/choco-cake.jpg', 'Rich and decadent chocolate lava cake', 'Vegetarian'],
    ['Red Velvet Slice', 12, 12.50, NULL, 20, 'Slice', 'images/products/red-velvet.jpg', 'Classic red velvet cake with cream cheese frosting', 'Vegetarian']
];

// 4. Add products for Soft Drinks (ID 13)
$soft_drinks = [
    ['Classic Cola 2L', 13, 3.50, 4.00, 100, 'Bottle', 'images/products/cola.jpg', 'Classic refreshing cola drink', 'Vegan'],
    ['Lemon-Lime Soda', 13, 2.75, NULL, 80, 'Bottle (1.5L)', 'images/products/lemon-soda.jpg', 'Zesty lemon-lime flavored soda', 'Vegan']
];

$stmt = $conn->prepare("INSERT INTO products (name, category_id, price, original_price, stock, unit, image_url, description, dietary_tags, is_sale) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach (array_merge($juices, $cakes, $soft_drinks) as $p) {
    $is_sale = $p[3] ? 1 : 0;
    $stmt->bind_param("siddississ", $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $is_sale);
    $stmt->execute();
}

echo "Cleanup done and products added!";
?>
