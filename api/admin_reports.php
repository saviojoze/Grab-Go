<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/api_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$admin_id = $_GET['admin_id'] ?? null;
if (!$admin_id) send_error('Admin ID required', 401);

// Identify user role
$role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$role_stmt->bind_param("i", $admin_id);
$role_stmt->execute();
$role = $role_stmt->get_result()->fetch_assoc()['role'] ?? '';

if ($role !== 'admin') {
    send_error('Only admins can view reports', 403);
}

if ($method === 'GET') {
    // Get date range from query params or default to last 30 days
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    // Set time explicitly so that end_date includes the entire day if needed for timestamps
    $end_date_time = $end_date . ' 23:59:59';
    $start_date_time = $start_date . ' 00:00:00';

    // 1. Sales Overview
    $sales_query = "SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total), 0) as total_revenue,
        COALESCE(AVG(total), 0) as avg_order_value,
        COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as completed_revenue
        FROM orders 
        WHERE created_at BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sales_query);
    $stmt->bind_param("ss", $start_date_time, $end_date_time);
    $stmt->execute();
    $sales_data = $stmt->get_result()->fetch_assoc();

    // 2. Top Selling Products
    $top_products_query = "SELECT 
        p.id,
        p.name,
        p.image_url,
        SUM(oi.quantity) as total_sold,
        SUM(oi.quantity * oi.price) as revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.created_at BETWEEN ? AND ?
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT 5";
    
    $stmt = $conn->prepare($top_products_query);
    $stmt->bind_param("ss", $start_date_time, $end_date_time);
    $stmt->execute();
    $top_products_res = $stmt->get_result();
    $top_products = [];
    while ($row = $top_products_res->fetch_assoc()) {
        $top_products[] = $row;
    }

    // 3. Revenue by Category
    $category_revenue_query = "SELECT 
        c.id,
        c.name,
        c.icon,
        COUNT(DISTINCT oi.order_id) as order_count,
        SUM(oi.quantity * oi.price) as revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.created_at BETWEEN ? AND ?
        GROUP BY c.id
        ORDER BY revenue DESC";
    
    $stmt = $conn->prepare($category_revenue_query);
    $stmt->bind_param("ss", $start_date_time, $end_date_time);
    $stmt->execute();
    $category_revenue_res = $stmt->get_result();
    $category_revenue = [];
    $total_category_revenue = 0;
    while ($row = $category_revenue_res->fetch_assoc()) {
        $total_category_revenue += floatval($row['revenue']);
        $category_revenue[] = $row;
    }
    
    // Add percentage
    foreach ($category_revenue as &$cat) {
        $cat['percentage'] = $total_category_revenue > 0 ? (floatval($cat['revenue']) / $total_category_revenue) * 100 : 0;
    }

    // 4. Low Stock Products
    $low_stock_query = "SELECT id, name, stock, price, image_url FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 10";
    $low_stock_res = $conn->query($low_stock_query);
    $low_stock_products = [];
    while ($row = $low_stock_res->fetch_assoc()) {
        $low_stock_products[] = $row;
    }

    $response_data = [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'overview' => [
            'total_orders' => intval($sales_data['total_orders']),
            'total_revenue' => floatval($sales_data['total_revenue']),
            'avg_order_value' => floatval($sales_data['avg_order_value']),
            'completed_revenue' => floatval($sales_data['completed_revenue']),
        ],
        'top_products' => $top_products,
        'category_revenue' => $category_revenue,
        'low_stock' => $low_stock_products
    ];

    send_success($response_data);
} else {
    send_error('Method not allowed', 405);
}
?>
