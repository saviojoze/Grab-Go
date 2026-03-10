<?php
$page_title = 'Admin Dashboard - Grab & Go';
$current_page = 'dashboard';

require_once 'admin_middleware.php';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = sanitize_input($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Order status updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update order status.';
    }
    
    redirect('index.php');
}

require_once 'header.php';

// Statistics
$stats = [];
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $result->fetch_assoc()['count'];
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
$stats['categories'] = $result->fetch_assoc()['count'];
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $result->fetch_assoc()['count'];
$result = $conn->query("SELECT COALESCE(SUM(total), 0) as revenue FROM orders WHERE status != 'cancelled'");
$stats['revenue'] = $result->fetch_assoc()['revenue'];
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$stats['pending'] = $result->fetch_assoc()['count'];
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
$stats['today_orders'] = $result->fetch_assoc()['count'];

// Stock data
$stock_out       = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock = 0")->fetch_assoc()['count'];
$stock_low       = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock > 0 AND stock <= 10")->fetch_assoc()['count'];
$stock_available = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock > 10")->fetch_assoc()['count'];

// Today's revenue
$result = $conn->query("SELECT COALESCE(SUM(total), 0) as revenue FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
$today_revenue = $result->fetch_assoc()['revenue'];

// Recent orders (last 6)
$recent_orders = $conn->query("SELECT o.*, u.full_name FROM orders o 
                               LEFT JOIN users u ON o.user_id = u.id 
                               ORDER BY o.created_at DESC LIMIT 6");
?>

<?php require_once 'sidebar.php'; ?>

<main class="admin-main">
<div class="admin-container" id="dash-root">

    <!-- ════ PAGE HEADER ════ -->
    <div class="dh-top">
        <div class="dh-top-left">
            <p class="dh-breadcrumb">Home / <strong>Dashboard</strong></p>
            <h1 class="dh-title">Dashboard</h1>
        </div>
        <div class="dh-top-right">
            <div class="dh-date-chip">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo date('D, d M Y'); ?>
            </div>
            <button class="dh-refresh-btn" onclick="window.location.reload()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- ════ PENDING ORDERS ALERT ════ -->
    <?php if ($stats['pending'] > 0): ?>
    <div class="dh-alert dh-alert-amber animate-fade-in" style="margin-bottom: 2rem; border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1.5rem; background: #fffbeb; border: 1px solid #fef3c7; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.05);">
        <div style="width: 54px; height: 54px; border-radius: 14px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706; flex-shrink: 0;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div style="flex: 1;">
            <h4 style="margin: 0; color: #92400e; font-size: 1.15rem; font-weight: 700;">Action Required: New Orders</h4>
            <p style="margin: 6px 0 0; color: #b45309; font-size: 0.95rem; line-height: 1.4;">There are <strong><?php echo $stats['pending']; ?></strong> pending orders that need to be processed and marked as ready for pickup.</p>
        </div>
        <a href="orders.php?status=pending" class="adh-btn" style="background: #d97706; color: white; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 600; text-decoration: none; transition: transform 0.2s; display: flex; align-items: center; gap: 8px;">
            Manage Orders
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
    </div>
    <?php endif; ?>

    <!-- ════ METRIC CARDS ════ -->
    <div class="metric-row">

        <div class="metric-card">
            <div class="metric-label">Total Revenue</div>
            <div class="metric-value">₹<?php echo number_format($stats['revenue'], 0); ?></div>
            <div class="metric-footer">
                <span class="mf-tag mf-tag-green">All time</span>
                <div class="metric-icon mic-revenue">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-label">Today's Revenue</div>
            <div class="metric-value">₹<?php echo number_format($today_revenue, 0); ?></div>
            <div class="metric-footer">
                <span class="mf-tag mf-tag-blue"><?php echo $stats['today_orders']; ?> orders today</span>
                <div class="metric-icon mic-today">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-label">Total Orders</div>
            <div class="metric-value"><?php echo number_format($stats['orders']); ?></div>
            <div class="metric-footer">
                <span class="mf-tag mf-tag-amber"><?php echo $stats['pending']; ?> pending</span>
                <div class="metric-icon mic-orders">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-label">Products</div>
            <div class="metric-value"><?php echo number_format($stats['products']); ?></div>
            <div class="metric-footer">
                <span class="mf-tag <?php echo $stock_out > 0 ? 'mf-tag-red' : 'mf-tag-green'; ?>"><?php echo $stock_out; ?> out of stock</span>
                <div class="metric-icon mic-products">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
            </div>
        </div>

    </div>

    <!-- ════ QUICK ACTIONS ════ -->
    <section class="qa-section">
        <h2 class="section-label">Quick Actions</h2>
        <div class="qa-row">
            <a href="add_product.php" class="qa-tile">
                <div class="qa-tile-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </div>
                <div class="qa-tile-body">
                    <span class="qa-tile-title">Add Product</span>
                    <span class="qa-tile-desc">List a new item to inventory</span>
                </div>
                <svg class="qa-tile-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="categories.php" class="qa-tile">
                <div class="qa-tile-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
                <div class="qa-tile-body">
                    <span class="qa-tile-title">Manage Categories</span>
                    <span class="qa-tile-desc">Organise product categories</span>
                </div>
                <svg class="qa-tile-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="orders.php" class="qa-tile">
                <div class="qa-tile-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div class="qa-tile-body">
                    <span class="qa-tile-title">All Orders</span>
                    <span class="qa-tile-desc">Process & manage orders</span>
                </div>
                <svg class="qa-tile-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="reports.php" class="qa-tile">
                <div class="qa-tile-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div class="qa-tile-body">
                    <span class="qa-tile-title">Reports</span>
                    <span class="qa-tile-desc">Sales analytics & insights</span>
                </div>
                <svg class="qa-tile-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </section>

    <!-- ════ MAIN TWO-COLUMN GRID ════ -->
    <div class="content-grid">

        <!-- Recent Orders -->
        <div class="panel panel-orders">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Recent Orders</h2>
                    <p class="panel-sub">Last 6 transactions across all customers</p>
                </div>
                <a href="orders.php" class="panel-link">View all →</a>
            </div>

            <?php if ($recent_orders->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <?php
                        $iq = $conn->prepare("SELECT oi.quantity, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ? LIMIT 2");
                        $iq->bind_param("i", $order['id']);
                        $iq->execute();
                        $ir = $iq->get_result();
                        $parts = [];
                        while($itm = $ir->fetch_assoc()) $parts[] = $itm['quantity'].'× '.$itm['name'];
                        $items_txt = implode(', ', $parts);
                    ?>
                    <tr>
                        <td><span class="oid">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></span></td>
                        <td>
                            <div class="cust">
                                <div class="cust-av"><?php echo strtoupper(substr($order['full_name'] ?? 'G', 0, 1)); ?></div>
                                <span class="cust-name"><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></span>
                            </div>
                        </td>
                        <td><span class="items-cell"><?php echo htmlspecialchars(strlen($items_txt) > 30 ? substr($items_txt, 0, 30).'…' : $items_txt); ?></span></td>
                        <td><span class="amt">₹<?php echo number_format($order['total'], 2); ?></span></td>
                        <td>
                            <span class="status-pill sp-<?php echo $order['status']; ?>">
                                <span class="sp-dot"></span>
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="row-action-btn" onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')" title="Update status">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-msg">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <p>No orders yet</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right column -->
        <div class="sidebar-panels">

            <!-- Stock Health -->
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2 class="panel-title">Stock Health</h2>
                        <p class="panel-sub">Real-time inventory status</p>
                    </div>
                    <span class="live-dot"><span class="ld-pulse"></span>Live</span>
                </div>

                <div class="stock-layout">
                    <div class="donut-wrap">
                        <canvas id="stockChart" width="140" height="140"></canvas>
                        <div class="donut-label">
                            <span class="dl-num"><?php echo $stock_available + $stock_low + $stock_out; ?></span>
                            <span class="dl-txt">total</span>
                        </div>
                    </div>
                    <div class="stock-legend">
                        <div class="sl-row">
                            <span class="sl-bar" style="background:#22c55e;"></span>
                            <span class="sl-name">Available</span>
                            <strong class="sl-val"><?php echo $stock_available; ?></strong>
                        </div>
                        <div class="sl-row">
                            <span class="sl-bar" style="background:#f59e0b;"></span>
                            <span class="sl-name">Low Stock</span>
                            <strong class="sl-val"><?php echo $stock_low; ?></strong>
                        </div>
                        <div class="sl-row">
                            <span class="sl-bar" style="background:#ef4444;"></span>
                            <span class="sl-name">Out of Stock</span>
                            <strong class="sl-val"><?php echo $stock_out; ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Restock alerts -->
                <div class="restock-wrap">
                    <p class="restock-head">Needs Attention</p>
                    <?php
                    $ap = $conn->query("SELECT * FROM products WHERE stock <= 10 ORDER BY stock ASC LIMIT 4");
                    if ($ap->num_rows > 0):
                    ?>
                    <div class="restock-list">
                        <?php while ($p = $ap->fetch_assoc()): ?>
                        <div class="restock-row">
                            <span class="restock-name"><?php echo htmlspecialchars($p['name']); ?></span>
                            <div class="restock-right">
                                <?php if($p['stock'] == 0): ?>
                                    <span class="rs-chip rs-red">Out</span>
                                <?php else: ?>
                                    <span class="rs-chip rs-amber"><?php echo $p['stock']; ?> left</span>
                                <?php endif; ?>
                                <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="rs-btn">Restock</a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="all-good-msg">✓ All products well stocked</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Store Summary -->
            <div class="panel panel-summary">
                <div class="panel-head">
                    <h2 class="panel-title">Store Summary</h2>
                </div>
                <div class="summary-list">
                    <div class="sum-row">
                        <span class="sum-label">Total Products</span>
                        <span class="sum-val"><?php echo number_format($stats['products']); ?></span>
                    </div>
                    <div class="sum-row">
                        <span class="sum-label">Categories</span>
                        <span class="sum-val"><?php echo number_format($stats['categories']); ?></span>
                    </div>
                    <div class="sum-row">
                        <span class="sum-label">Pending Orders</span>
                        <span class="sum-val sum-val-amber"><?php echo number_format($stats['pending']); ?></span>
                    </div>
                    <div class="sum-row">
                        <span class="sum-label">Out of Stock</span>
                        <span class="sum-val sum-val-red"><?php echo $stock_out; ?></span>
                    </div>
                    <div class="sum-row sum-row-last">
                        <span class="sum-label">Total Revenue</span>
                        <span class="sum-val sum-val-primary">₹<?php echo number_format($stats['revenue'], 0); ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div><!-- /admin-container -->
</main>

<!-- Status Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Update Order Status</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="order_id" id="modal_order_id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select New Status</label>
                    <select name="status" id="modal_status" class="form-input" required>
                        <option value="pending">Pending</option>
                        <option value="ready">Ready for Pickup</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Stock Doughnut
    new Chart(document.getElementById('stockChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [<?php echo $stock_available; ?>, <?php echo $stock_low; ?>, <?php echo $stock_out; ?>],
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 5
            }]
        },
        options: {
            cutout: '80%',
            responsive: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#f1f5f9',
                    padding: 10,
                    cornerRadius: 8,
                    titleFont: { family: 'DM Sans', size: 11 },
                    bodyFont: { family: 'DM Sans', size: 13, weight: '700' }
                }
            }
        }
    });

    // Modal
    function updateStatus(orderId, currentStatus) {
        document.getElementById('modal_order_id').value = orderId;
        document.getElementById('modal_status').value = currentStatus;
        document.getElementById('statusModal').classList.add('show');
    }
    function closeModal() {
        document.getElementById('statusModal').classList.remove('show');
    }
    document.getElementById('statusModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });

    // Count-up animation
    document.querySelectorAll('.metric-value').forEach(el => {
        const text = el.textContent;
        const prefix = text.match(/^[^\d]*/)?.[0] ?? '';
        const raw = text.replace(/[^\d.]/g, '');
        const target = parseFloat(raw);
        if (!target) return;
        const decimals = (raw.split('.')[1] ?? '').length;
        let n = 0, dur = 900, step = target / (dur / 16);
        const t = setInterval(() => {
            n = Math.min(n + step, target);
            el.textContent = prefix + n.toLocaleString('en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
            if (n >= target) clearInterval(t);
        }, 16);
    });
</script>

<!-- ════ DASHBOARD PREMIUM STYLES ════ -->
<style>
/* ── Design tokens ─────────────────────────────── */
#dash-root {
    --accent:        #4318FF;
    --accent-light:  #ede9ff;
    --surface:       #ffffff;
    --surface-raised:#f8f9fc;
    --border:        #e8eaf0;
    --text-1:        #0f172a;
    --text-2:        #64748b;
    --text-3:        #94a3b8;
    --radius-card:   18px;
    --radius-sm:     10px;
    --shadow-card:   0 1px 3px rgba(15,23,42,.06), 0 4px 16px rgba(15,23,42,.06);
    --shadow-hover:  0 2px 6px rgba(15,23,42,.06), 0 8px 28px rgba(15,23,42,.1);
}

/* ── Page header ─────────────────────────────── */
.dh-top {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding: 32px 0 28px;
    gap: 16px;
}
.dh-breadcrumb {
    font-size: 0.78rem;
    color: var(--text-3);
    margin: 0 0 6px;
    font-weight: 500;
    letter-spacing: 0.02em;
}
.dh-breadcrumb strong { color: var(--text-2); }
.dh-title {
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--text-1);
    margin: 0;
    letter-spacing: -0.04em;
    line-height: 1;
}
.dh-top-right { display: flex; align-items: center; gap: 12px; }
.dh-date-chip {
    display: flex; align-items: center; gap: 7px;
    font-size: 0.8rem; color: var(--text-2); font-weight: 500;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 30px; padding: 8px 16px;
    box-shadow: var(--shadow-card);
}
.dh-refresh-btn {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.82rem; font-weight: 600; color: var(--text-1);
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 30px; padding: 9px 18px;
    cursor: pointer; font-family: inherit;
    box-shadow: var(--shadow-card);
    transition: box-shadow 0.2s, border-color 0.2s;
}
.dh-refresh-btn:hover { border-color: var(--accent); box-shadow: var(--shadow-hover); }

/* ── Metric cards ─────────────────────────────── */
.metric-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 32px;
}
.metric-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    padding: 26px 24px 22px;
    box-shadow: var(--shadow-card);
    transition: box-shadow 0.25s, transform 0.25s;
    cursor: default;
}
.metric-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-3px);
}
.metric-label {
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-3);
    margin-bottom: 12px;
}
.metric-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-1);
    letter-spacing: -0.04em;
    line-height: 1;
    margin-bottom: 18px;
}
.metric-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.mf-tag {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
}
.mf-tag-green  { background: #f0fdf4; color: #16a34a; }
.mf-tag-blue   { background: #eff6ff; color: #2563eb; }
.mf-tag-amber  { background: #fffbeb; color: #d97706; }
.mf-tag-red    { background: #fef2f2; color: #dc2626; }

.metric-icon {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.mic-revenue  { background: var(--accent-light); color: var(--accent); }
.mic-today    { background: #eff6ff; color: #2563eb; }
.mic-orders   { background: #fffbeb; color: #d97706; }
.mic-products { background: #f0fdf4; color: #16a34a; }

/* ── Quick actions ─────────────────────────────── */
.qa-section { margin-bottom: 32px; }
.section-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-3);
    margin: 0 0 14px;
}
.qa-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}
.qa-tile {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    color: var(--text-1);
    box-shadow: var(--shadow-card);
    transition: box-shadow 0.2s, border-color 0.2s, transform 0.2s;
}
.qa-tile:hover {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(67,24,255,0.08), var(--shadow-hover);
    transform: translateY(-2px);
}
.qa-tile-icon {
    width: 40px; height: 40px;
    border-radius: var(--radius-sm);
    background: var(--surface-raised);
    border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-2);
    flex-shrink: 0;
    transition: background 0.2s, color 0.2s;
}
.qa-tile:hover .qa-tile-icon { background: var(--accent-light); color: var(--accent); border-color: transparent; }
.qa-tile-body { flex: 1; min-width: 0; }
.qa-tile-title { display: block; font-size: 0.88rem; font-weight: 700; color: var(--text-1); margin-bottom: 2px; }
.qa-tile-desc  { display: block; font-size: 0.75rem; color: var(--text-3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.qa-tile-arrow { color: var(--text-3); flex-shrink: 0; transition: transform 0.2s, color 0.2s; }
.qa-tile:hover .qa-tile-arrow { transform: translateX(3px); color: var(--accent); }

/* ── Content grid ─────────────────────────────── */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 22px;
    align-items: start;
    margin-bottom: 48px;
}
.sidebar-panels { display: flex; flex-direction: column; gap: 18px; }

/* ── Panel ─────────────────────────────────────── */
.panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    box-shadow: var(--shadow-card);
    overflow: hidden;
}
.panel-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 22px 24px 16px;
    border-bottom: 1px solid var(--border);
}
.panel-title { font-size: 0.95rem; font-weight: 700; color: var(--text-1); margin: 0 0 3px; }
.panel-sub   { font-size: 0.77rem; color: var(--text-3); margin: 0; }
.panel-link {
    font-size: 0.78rem; color: var(--accent); text-decoration: none;
    font-weight: 600; white-space: nowrap; margin-top: 2px;
    padding: 5px 12px; border-radius: 6px;
    transition: background 0.2s;
}
.panel-link:hover { background: var(--accent-light); }
.live-dot {
    display: flex; align-items: center; gap: 7px;
    font-size: 0.75rem; font-weight: 600; color: #16a34a;
    margin-top: 2px;
}
.ld-pulse {
    width: 8px; height: 8px; border-radius: 50%;
    background: #22c55e;
    animation: ldpulse 2s ease-in-out infinite;
    box-shadow: 0 0 0 0 rgba(34,197,94,0.5);
}
@keyframes ldpulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
    50% { box-shadow: 0 0 0 5px rgba(34,197,94,0); }
}

/* ── Data table ─────────────────────────────────── */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.855rem;
}
.data-table thead th {
    text-align: left;
    padding: 10px 14px;
    font-size: 0.69rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-3);
    font-weight: 700;
    background: var(--surface-raised);
    border-bottom: 1px solid var(--border);
}
.data-table thead th:first-child { padding-left: 24px; }
.data-table thead th:last-child  { padding-right: 24px; width: 48px; }
.data-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background 0.15s;
}
.data-table tbody tr:last-child { border-bottom: none; }
.data-table tbody tr:hover { background: var(--surface-raised); }
.data-table tbody td {
    padding: 14px 14px;
    color: var(--text-1);
    vertical-align: middle;
}
.data-table tbody td:first-child { padding-left: 24px; }
.data-table tbody td:last-child  { padding-right: 24px; }

.oid { font-weight: 700; font-size: 0.8rem; color: var(--accent); font-family: 'DM Mono', monospace; }
.cust { display: flex; align-items: center; gap: 10px; }
.cust-av {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--accent-light);
    color: var(--accent);
    font-size: 0.72rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.cust-name { font-weight: 500; font-size: 0.85rem; }
.items-cell { color: var(--text-3); font-size: 0.8rem; }
.amt { font-weight: 700; font-size: 0.88rem; color: var(--text-1); }

/* Status pills */
.status-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 10px;
    border-radius: 30px;
    font-size: 0.7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.04em;
}
.sp-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.sp-pending   { background: #fffbeb; color: #b45309; }
.sp-ready     { background: #eff6ff; color: #1d4ed8; }
.sp-completed { background: #f0fdf4; color: #15803d; }
.sp-cancelled { background: #fef2f2; color: #b91c1c; }

.row-action-btn {
    width: 30px; height: 30px;
    border-radius: 8px; border: 1px solid var(--border);
    background: var(--surface-raised);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--text-2);
    transition: all 0.2s;
}
.row-action-btn:hover { background: var(--accent-light); color: var(--accent); border-color: transparent; }

/* ── Stock panel ─────────────────────────────── */
.stock-layout {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 24px;
}
.donut-wrap {
    position: relative;
    width: 140px; height: 140px;
    flex-shrink: 0;
}
.donut-label {
    position: absolute;
    inset: 0; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    pointer-events: none;
}
.dl-num { font-size: 1.7rem; font-weight: 800; color: var(--text-1); line-height: 1; }
.dl-txt { font-size: 0.7rem; color: var(--text-3); margin-top: 2px; }
.stock-legend { display: flex; flex-direction: column; gap: 10px; flex: 1; }
.sl-row { display: flex; align-items: center; gap: 10px; }
.sl-bar { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.sl-name { flex: 1; font-size: 0.8rem; color: var(--text-2); }
.sl-val  { font-size: 0.88rem; font-weight: 700; color: var(--text-1); }

/* Restock */
.restock-wrap {
    border-top: 1px solid var(--border);
    padding: 16px 24px 20px;
}
.restock-head {
    font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.07em;
    color: var(--text-3); margin: 0 0 12px;
}
.restock-list { display: flex; flex-direction: column; gap: 8px; }
.restock-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.restock-name {
    font-size: 0.82rem; font-weight: 500; color: var(--text-1);
    flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.restock-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.rs-chip {
    font-size: 0.68rem; font-weight: 700;
    padding: 3px 8px; border-radius: 5px;
}
.rs-red   { background: #fef2f2; color: #b91c1c; }
.rs-amber { background: #fffbeb; color: #b45309; }
.rs-btn {
    font-size: 0.75rem; font-weight: 600; color: var(--accent);
    text-decoration: none; padding: 4px 10px;
    border-radius: 6px; border: 1px solid var(--border);
    background: var(--surface);
    transition: background 0.2s, border-color 0.2s;
}
.rs-btn:hover { background: var(--accent-light); border-color: transparent; }
.all-good-msg {
    font-size: 0.82rem; color: #15803d; font-weight: 600;
    background: #f0fdf4; border-radius: 8px;
    padding: 12px 16px; text-align: center;
}

/* ── Summary panel ─────────────────────────────── */
.panel-summary .panel-head { border-bottom: 1px solid var(--border); }
.summary-list { padding: 4px 0 8px; }
.sum-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 24px;
    border-bottom: 1px solid var(--border);
    transition: background 0.15s;
}
.sum-row:hover { background: var(--surface-raised); }
.sum-row-last { border-bottom: none; }
.sum-label { font-size: 0.83rem; color: var(--text-2); font-weight: 500; }
.sum-val { font-size: 0.9rem; font-weight: 700; color: var(--text-1); }
.sum-val-amber   { color: #d97706; }
.sum-val-red     { color: #dc2626; }
.sum-val-primary { color: var(--accent); }

/* ── Empty / error ─────────────────────────────── */
.empty-msg {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 12px;
    padding: 56px 24px; color: var(--text-3);
}
.empty-msg p { margin: 0; font-size: 0.9rem; }

/* ── Responsive ─────────────────────────────────── */
@media (max-width: 1280px) {
    .metric-row { grid-template-columns: repeat(2, 1fr); }
    .qa-row     { grid-template-columns: repeat(2, 1fr); }
    .content-grid { grid-template-columns: 1fr; }
    .sidebar-panels { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
}
@media (max-width: 768px) {
    .metric-row { grid-template-columns: 1fr 1fr; }
    .qa-row     { grid-template-columns: 1fr 1fr; }
    .sidebar-panels { grid-template-columns: 1fr; }
    .dh-top { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 480px) {
    .metric-row { grid-template-columns: 1fr; }
    .qa-row     { grid-template-columns: 1fr; }
}
</style>

<?php require_once 'footer.php'; ?>
