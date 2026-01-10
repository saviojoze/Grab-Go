<?php
$page_title = 'Reports & Analytics - Admin';
$current_page = 'reports';

require_once 'admin_middleware.php';

// Get date range from query params or default to last 30 days
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Sales Overview
$sales_query = "SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total), 0) as total_revenue,
    COALESCE(AVG(total), 0) as avg_order_value,
    COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as completed_revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?";

$stmt = $conn->prepare($sales_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$sales_data = $stmt->get_result()->fetch_assoc();

// Top Selling Products
$top_products_query = "SELECT 
    p.name,
    p.image_url,
    SUM(oi.quantity) as total_sold,
    SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5";

$stmt = $conn->prepare($top_products_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$top_products = $stmt->get_result();

// Revenue by Category
$category_revenue_query = "SELECT 
    c.name,
    c.icon,
    COUNT(DISTINCT oi.order_id) as order_count,
    SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY c.id
    ORDER BY revenue DESC";

$stmt = $conn->prepare($category_revenue_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$category_revenue = $stmt->get_result();

// Daily Sales Trend (last 7 days)
$daily_sales_query = "SELECT 
    DATE(created_at) as sale_date,
    COUNT(*) as orders,
    COALESCE(SUM(total), 0) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN DATE_SUB(?, INTERVAL 7 DAY) AND ?
    GROUP BY DATE(created_at)
    ORDER BY sale_date ASC";

$stmt = $conn->prepare($daily_sales_query);
$stmt->bind_param("ss", $end_date, $end_date);
$stmt->execute();
$daily_sales = $stmt->get_result();

// Low Stock Products
$low_stock_query = "SELECT name, stock, price FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 10";
$low_stock_products = $conn->query($low_stock_query);

require_once 'header.php';
?>

<?php require_once 'sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main">
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Reports & Analytics</h1>
                <p class="text-secondary">Business insights and performance tracking</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-secondary" onclick="exportReport()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Export Report
                </button>
            </div>
        </div>
        
        <!-- Date Range Filter -->
        <div class="filter-card">
            <form method="GET" class="date-range-form">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input 
                        type="date" 
                        name="start_date" 
                        class="form-input"
                        value="<?php echo htmlspecialchars($start_date); ?>"
                    >
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input 
                        type="date" 
                        name="end_date" 
                        class="form-input"
                        value="<?php echo htmlspecialchars($end_date); ?>"
                    >
                </div>
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </form>
        </div>
        
        <!-- Sales Overview -->
        <div class="stats-grid">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>₹<?php echo number_format($sales_data['total_revenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($sales_data['total_orders']); ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>₹<?php echo number_format($sales_data['avg_order_value'], 2); ?></h3>
                    <p>Avg Order Value</p>
                </div>
            </div>
            
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>₹<?php echo number_format($sales_data['completed_revenue'], 2); ?></h3>
                    <p>Completed Revenue</p>
                </div>
            </div>
        </div>
        
        <!-- Charts and Tables -->
        <div class="reports-grid">
            <!-- Top Selling Products -->
            <div class="report-card">
                <div class="card-header">
                    <h2>Top Selling Products</h2>
                    <span class="badge badge-primary">Top 5</span>
                </div>
                <div class="card-content">
                    <?php if ($top_products->num_rows > 0): ?>
                        <div class="product-list">
                            <?php while ($product = $top_products->fetch_assoc()): ?>
                                <div class="product-item">
                                    <div class="product-info">
                                        <?php if ($product['image_url']): ?>
                                            <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product">
                                        <?php else: ?>
                                            <div class="product-placeholder">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <p class="text-secondary"><?php echo number_format($product['total_sold']); ?> units sold</p>
                                        </div>
                                    </div>
                                    <div class="product-revenue">
                                        ₹<?php echo number_format($product['revenue'], 2); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-secondary text-center">No sales data available</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Revenue by Category -->
            <div class="report-card">
                <div class="card-header">
                    <h2>Revenue by Category</h2>
                </div>
                <div class="card-content">
                    <?php if ($category_revenue->num_rows > 0): ?>
                        <div class="category-list">
                            <?php 
                            $total_revenue = 0;
                            $categories = [];
                            while ($cat = $category_revenue->fetch_assoc()) {
                                $total_revenue += $cat['revenue'];
                                $categories[] = $cat;
                            }
                            
                            foreach ($categories as $cat):
                                $percentage = $total_revenue > 0 ? ($cat['revenue'] / $total_revenue) * 100 : 0;
                            ?>
                                <div class="category-item">
                                    <div class="category-header">
                                        <span class="category-icon"><?php echo $cat['icon']; ?></span>
                                        <span class="category-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                                        <span class="category-revenue">₹<?php echo number_format($cat['revenue'], 2); ?></span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <div class="category-stats">
                                        <span><?php echo number_format($percentage, 1); ?>% of total</span>
                                        <span><?php echo $cat['order_count']; ?> orders</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-secondary text-center">No category data available</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            <div class="report-card">
                <div class="card-header">
                    <h2>Low Stock Alert</h2>
                    <span class="badge badge-warning"><?php echo $low_stock_products->num_rows; ?> items</span>
                </div>
                <div class="card-content">
                    <?php if ($low_stock_products->num_rows > 0): ?>
                        <div class="stock-alert-list">
                            <?php while ($product = $low_stock_products->fetch_assoc()): ?>
                                <div class="stock-alert-item">
                                    <div>
                                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="text-secondary">₹<?php echo number_format($product['price'], 2); ?></p>
                                    </div>
                                    <span class="stock-badge stock-badge-<?php echo $product['stock'] < 5 ? 'critical' : 'low'; ?>">
                                        <?php echo $product['stock']; ?> left
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-secondary text-center">All products are well stocked! 🎉</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.filter-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.date-range-form {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
}

.date-range-form .form-group {
    flex: 1;
    min-width: 200px;
    margin-bottom: 0;
}

.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.report-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-primary {
    background: #dbeafe;
    color: #1e40af;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.card-content {
    padding: 1.5rem;
}

.product-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f7fafc;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.product-item:hover {
    background: #edf2f7;
    transform: translateX(4px);
}

.product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-info img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.product-placeholder {
    width: 50px;
    height: 50px;
    background: #e2e8f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-info h4 {
    margin: 0;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #2d3748;
}

.product-revenue {
    font-size: 1.125rem;
    font-weight: 700;
    color: #10b981;
}

.category-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.category-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.category-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.category-icon {
    font-size: 1.5rem;
}

.category-name {
    flex: 1;
    font-weight: 600;
    color: #2d3748;
}

.category-revenue {
    font-weight: 700;
    color: #667eea;
}

.progress-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
}

.category-stats {
    display: flex;
    justify-content: space-between;
    font-size: 0.8125rem;
    color: #718096;
}

.stock-alert-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.stock-alert-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f7fafc;
    border-radius: 8px;
}

.stock-alert-item h4 {
    margin: 0 0 0.25rem 0;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #2d3748;
}

.stock-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8125rem;
    font-weight: 600;
}

.stock-badge-low {
    background: #fef3c7;
    color: #92400e;
}

.stock-badge-critical {
    background: #fee2e2;
    color: #991b1b;
}

@media (max-width: 768px) {
    .reports-grid {
        grid-template-columns: 1fr;
    }
    
    .date-range-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .date-range-form .form-group {
        width: 100%;
    }
}
</style>

<script>
function exportReport() {
    alert('Export functionality will be implemented with PDF/Excel export');
}
</script>

<?php require_once 'footer.php'; ?>
