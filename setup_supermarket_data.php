<?php
/**
 * Setup Supermarket Data
 * Populate the database with new categories and products
 * Generates STATIC SVG files for 100% reliability
 */

require_once __DIR__ . '/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fixing Supermarket Images (Static SVGs)...</h1>";

// Ensure directory exists
if (!file_exists(__DIR__ . '/images/products')) {
    mkdir(__DIR__ . '/images/products', 0777, true);
}

// Function to create static SVG
function create_static_svg($text, $filename) {
    $cleanText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
    <rect width="100%" height="100%" fill="#f8f9fa"/>
    <rect x="0" y="0" width="400" height="400" fill="none" stroke="#dee2e6" stroke-width="2"/>
    <text x="50%" y="50%" font-family="Arial, Helvetica, sans-serif" font-size="24" fill="#495057" text-anchor="middle" dominant-baseline="middle">
        ' . $cleanText . '
    </text>
</svg>';
    
    // Write using absolute path
    $path = __DIR__ . '/images/products/' . $filename;
    file_put_contents($path, $svg);
    return 'images/products/' . $filename;
}

$category_map = [];

// Helper to get cat IDs
$cats = ['Electronics', 'Home Appliances', 'Beauty & Personal Care', 'Home & Kitchen', 'Automotive', 'Pet Supplies', 'Toys & Games'];
foreach ($cats as $name) {
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $category_map[$name] = $row['id'];
    }
}

// Products
$new_products = [
    // Electronics
    ['cat' => 'Electronics', 'name' => 'Wireless Bluetooth Headphones', 'price' => 2999.00, 'stock' => 50, 'desc' => 'Noise cancelling over-ear headphones.'],
    ['cat' => 'Electronics', 'name' => 'Smart LED TV 43 Inch', 'price' => 24999.00, 'stock' => 20, 'desc' => 'Full HD Smart TV with apps.'],
    ['cat' => 'Electronics', 'name' => 'Power Bank 20000mAh', 'price' => 1499.00, 'stock' => 100, 'desc' => 'Fast charging portable battery.'],
    ['cat' => 'Electronics', 'name' => 'Smartphone 5G 128GB', 'price' => 19999.00, 'stock' => 30, 'desc' => 'Latest 5G smartphone with dual camera.'],
    ['cat' => 'Electronics', 'name' => 'Laptop i5 16GB RAM', 'price' => 45000.00, 'stock' => 10, 'desc' => 'Work and gaming laptop.'],
    ['cat' => 'Electronics', 'name' => 'Smart Watch Series 5', 'price' => 2499.00, 'stock' => 50, 'desc' => 'Fitness tracker and smart notifications.'],
    ['cat' => 'Electronics', 'name' => 'Wireless Mouse', 'price' => 599.00, 'stock' => 200, 'desc' => 'Ergonomic optical mouse.'],
    ['cat' => 'Electronics', 'name' => 'Gaming Keyboard', 'price' => 1599.00, 'stock' => 40, 'desc' => 'RGB mechanical feel keyboard.'],
    ['cat' => 'Electronics', 'name' => 'USB-C Fast Charger', 'price' => 499.00, 'stock' => 150, 'desc' => '20W adapter for phones.'],
    ['cat' => 'Electronics', 'name' => 'Tablet 10 Inch', 'price' => 12999.00, 'stock' => 25, 'desc' => 'Perfect for movies and reading.'],

    // Home Appliances
    ['cat' => 'Home Appliances', 'name' => 'Microwave Oven 20L', 'price' => 5999.00, 'stock' => 30, 'desc' => 'Solo microwave for reheating/cooking.'],
    ['cat' => 'Home Appliances', 'name' => 'Electric Kettle 1.5L', 'price' => 899.00, 'stock' => 60, 'desc' => 'Boil water in minutes.'],
    ['cat' => 'Home Appliances', 'name' => 'Mixer Grinder 750W', 'price' => 2999.00, 'stock' => 40, 'desc' => 'Powerful mixer with 3 jars.'],
    ['cat' => 'Home Appliances', 'name' => 'Air Fryer 4L', 'price' => 5999.00, 'stock' => 20, 'desc' => 'Healthy cooking with less oil.'],
    ['cat' => 'Home Appliances', 'name' => 'Vacuum Cleaner', 'price' => 4999.00, 'stock' => 15, 'desc' => 'Powerful suction for home cleaning.'],
    ['cat' => 'Home Appliances', 'name' => 'Steam Iron', 'price' => 1299.00, 'stock' => 50, 'desc' => 'Non-stick soleplate steam iron.'],
    ['cat' => 'Home Appliances', 'name' => 'Water Purifier RO+UV', 'price' => 8999.00, 'stock' => 10, 'desc' => 'Advanced 7-stage purification.'],
    ['cat' => 'Home Appliances', 'name' => 'Sandwich Maker', 'price' => 1499.00, 'stock' => 45, 'desc' => 'Grill toaster for breakfast.'],

    // Home & Kitchen
    ['cat' => 'Home & Kitchen', 'name' => 'Non-Stick Cookware Set', 'price' => 3499.00, 'stock' => 25, 'desc' => 'Frying pan, kadhai, and tawa.'],
    ['cat' => 'Home & Kitchen', 'name' => 'Double Bedsheet Cotton', 'price' => 999.00, 'stock' => 45, 'desc' => 'Soft cotton with 2 pillow covers.'],
    ['cat' => 'Home & Kitchen', 'name' => 'Water Bottle Set (4pc)', 'price' => 499.00, 'stock' => 100, 'desc' => 'BPA-free fridge bottles.'],
    ['cat' => 'Home & Kitchen', 'name' => 'Stainless Steel Knife Set', 'price' => 699.00, 'stock' => 60, 'desc' => 'Chef knives with wooden block.'],
    ['cat' => 'Home & Kitchen', 'name' => 'Dinner Set 32 Pcs', 'price' => 2499.00, 'stock' => 20, 'desc' => 'Melamine dinnerware set.'],
    ['cat' => 'Home & Kitchen', 'name' => 'Storage Containers (6pc)', 'price' => 899.00, 'stock' => 80, 'desc' => 'Airtight kitchen organizers.'],

    // Beauty
    ['cat' => 'Beauty & Personal Care', 'name' => 'Daily Face Wash', 'price' => 199.00, 'stock' => 150, 'desc' => 'Neem and turmeric face wash.'],
    ['cat' => 'Beauty & Personal Care', 'name' => 'Sunscreen SPF 50', 'price' => 450.00, 'stock' => 100, 'desc' => 'Matte finish UV protection.'],
    ['cat' => 'Beauty & Personal Care', 'name' => 'Herbal Shampoo 1L', 'price' => 699.00, 'stock' => 80, 'desc' => 'For strong and shiny hair.'],
    ['cat' => 'Beauty & Personal Care', 'name' => 'Body Lotion 400ml', 'price' => 399.00, 'stock' => 120, 'desc' => 'Cocoa butter deep moisture.'],
    ['cat' => 'Beauty & Personal Care', 'name' => 'Perfume 100ml', 'price' => 1299.00, 'stock' => 50, 'desc' => 'Long lasting luxury fragrance.'],
    
    // Automotive
    ['cat' => 'Automotive', 'name' => 'Car Air Freshener', 'price' => 299.00, 'stock' => 100, 'desc' => 'Lemon scent car perfume.'],
    ['cat' => 'Automotive', 'name' => 'Microfiber Cleaning Cloth', 'price' => 199.00, 'stock' => 200, 'desc' => 'Lint-free cloth for car wash.'],
];

$updated_count = 0;
$added_count = 0;

foreach ($new_products as $i => $prod) {
    if (empty($prod['name'])) continue; // Safety

    // Get Cat ID
    $cat_id = $category_map[$prod['cat']] ?? null;
    if (!$cat_id) continue;

    // GENERATE STATIC FILE
    // Filename: slugified name .svg
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $prod['name']));
    $svgFilename = $slug . '.svg';
    
    $relativePath = create_static_svg($prod['name'], $svgFilename); // "images/products/foo.svg"

    // Update DB to use this relative path
    $image_url = $relativePath;

    // Check existence
    $stmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
    $stmt->bind_param("s", $prod['name']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // UPDATE existing product with new image
        $row = $result->fetch_assoc();
        $pid = $row['id'];
        
        $upd = $conn->prepare("UPDATE products SET image_url = ?, price = ?, description = ? WHERE id = ?");
        $upd->bind_param("sdsi", $image_url, $prod['price'], $prod['desc'], $pid);
        if ($upd->execute()) {
            $updated_count++;
        }
    } else {
        // INSERT new product
        $ins = $conn->prepare("INSERT INTO products (name, category_id, price, original_price, stock, unit, image_url, description, dietary_tags, is_sale) VALUES (?, ?, ?, NULL, ?, 'units', ?, ?, '', 0)");
        $ins->bind_param("sidiss", 
            $prod['name'], 
            $cat_id, 
            $prod['price'], 
            $prod['stock'], 
            $image_url,
            $prod['desc']
        );
        if ($ins->execute()) {
            $added_count++;
        }
    }
}

echo "<h2>Static Images Generated!</h2>";
echo "<p>Generated and linked static SVG files for $updated_count products.</p>";
echo "<a href='products/listing.php' style='background:black;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to Shop</a>";
?>
