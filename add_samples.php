<?php
$conn = new mysqli('shuttle.proxy.rlwy.net', 'root', 'VmabgDADhVihmKpeEsHVEJyQJMJqenNU', 'railway', 22618);
if ($conn->connect_error) die("Conn fail");

echo "Adding improved sample data...\n";

// Add sample categories
$conn->query("INSERT IGNORE INTO categories (id, name, icon) VALUES 
(101, '🔥 Hot Deals', '⚡'),
(102, '💻 Electronics', '📱'),
(103, '🥤 Beverages', '🥤')");

// Add sample products (using correct column name 'stock')
$conn->query("INSERT IGNORE INTO products (name, category_id, price, stock, unit, description) VALUES 
('Sample Smartphone', 102, 15999.00, 25, 'units', 'Latest model with 8GB RAM'),
('Sample Energy Drink', 103, 99.00, 100, 'Bottle', 'Instant energy boost'),
('Sample Laptop', 102, 45999.00, 10, 'units', 'High performance laptop for work')");

echo "✅ Sample categories and products added to Railway!\n";
?>
