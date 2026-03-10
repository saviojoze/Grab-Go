<?php
$page_title = 'Staff Dashboard - Grab & Go';
$current_page = 'dashboard';

require_once 'staff_middleware.php';

// Order statistics
$today_stats = [];
$today_stats['total']     = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$today_stats['pending']   = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")->fetch_assoc()['c'];
$today_stats['ready']     = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'ready'")->fetch_assoc()['c'];
$today_stats['completed'] = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'completed'")->fetch_assoc()['c'];

// Today orders
$today_orders_count = $conn->query("SELECT COUNT(*) as c FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
$today_revenue      = $conn->query("SELECT COALESCE(SUM(total),0) as r FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")->fetch_assoc()['r'];

// Recent orders (last 6)
$recent_orders = $conn->query(
    "SELECT o.*, u.full_name
       FROM orders o
       LEFT JOIN users u ON o.user_id = u.id
      ORDER BY o.created_at DESC
      LIMIT 6"
);

require_once 'header.php';
require_once 'sidebar.php';
?>

<main class="admin-main">
<div class="admin-container" id="staff-dash-root">

    <!-- ════ PAGE HEADER ════ -->
    <div class="dh-top">
        <div class="dh-top-left">
            <p class="dh-breadcrumb">Home / <strong>Dashboard</strong></p>
            <h1 class="dh-title">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
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

    <!-- ════ METRIC CARDS ════ -->
    <div class="metric-row">

        <div class="metric-card">
            <div class="metric-label">Total Orders</div>
            <div class="metric-value"><?php echo number_format($today_stats['total']); ?></div>
            <div class="metric-footer">
                <span class="mf-tag mf-tag-blue"><?php echo $today_orders_count; ?> today</span>
                <div class="metric-icon mic-orders">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-label">Pending Orders</div>
            <div class="metric-value"><?php echo number_format($today_stats['pending']); ?></div>
            <div class="metric-footer">
                <span class="mf-tag mf-tag-amber">Needs action</span>
                <div class="metric-icon mic-today">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-label">Ready for Pickup</div>
            <div class="metric-value"><?php echo number_format($today_stats['ready']); ?></div>
            <div class="metric-footer">
                <span class="mf-tag mf-tag-green">Awaiting customer</span>
                <div class="metric-icon mic-products">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-label">Completed Orders</div>
            <div class="metric-value"><?php echo number_format($today_stats['completed']); ?></div>
            <div class="metric-footer">
                <span class="mf-tag mf-tag-green">All time</span>
                <div class="metric-icon mic-revenue">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>
        </div>

    </div>

    <!-- ════ QUICK ACTIONS ════ -->
    <section class="qa-section">
        <h2 class="section-label">Quick Actions</h2>
        <div class="qa-row">
            <a href="orders.php" class="qa-tile">
                <div class="qa-tile-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </div>
                <div class="qa-tile-body">
                    <span class="qa-tile-title">Manage Orders</span>
                    <span class="qa-tile-desc">View & process customer orders</span>
                </div>
                <svg class="qa-tile-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="products.php" class="qa-tile">
                <div class="qa-tile-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                </div>
                <div class="qa-tile-body">
                    <span class="qa-tile-title">Product Inventory</span>
                    <span class="qa-tile-desc">Check & update stock levels</span>
                </div>
                <svg class="qa-tile-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="profile.php" class="qa-tile">
                <div class="qa-tile-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="qa-tile-body">
                    <span class="qa-tile-title">My Profile</span>
                    <span class="qa-tile-desc">View & edit your details</span>
                </div>
                <svg class="qa-tile-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="my_leaves.php" class="qa-tile">
                <div class="qa-tile-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="qa-tile-body">
                    <span class="qa-tile-title">Leave Requests</span>
                    <span class="qa-tile-desc">Apply for and track leaves</span>
                </div>
                <svg class="qa-tile-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </section>

    <!-- ════ MAIN CONTENT ════ -->
    <div class="content-grid">

        <!-- Recent Orders Table -->
        <div class="panel panel-orders">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Recent Orders</h2>
                    <p class="panel-sub">Last 6 orders across all customers</p>
                </div>
                <a href="orders.php" class="panel-link">View all →</a>
            </div>

            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td><span class="oid">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></span></td>
                        <td>
                            <div class="cust">
                                <div class="cust-av"><?php echo strtoupper(substr($order['full_name'] ?? 'G', 0, 1)); ?></div>
                                <span class="cust-name"><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></span>
                            </div>
                        </td>
                        <td><span class="items-cell"><?php echo date('d M Y', strtotime($order['created_at'])); ?></span></td>
                        <td><span class="amt">₹<?php echo number_format($order['total'], 2); ?></span></td>
                        <td>
                            <span class="status-pill sp-<?php echo $order['status']; ?>">
                                <span class="sp-dot"></span>
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="row-action-btn" title="View details">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
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

        <!-- Right Column -->
        <div class="sidebar-panels">

            <!-- Order Status Summary -->
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2 class="panel-title">Order Status</h2>
                        <p class="panel-sub">Real-time order breakdown</p>
                    </div>
                    <span class="live-dot"><span class="ld-pulse"></span>Live</span>
                </div>
                <div class="summary-list">
                    <div class="sum-row">
                        <span class="sum-label">Total Orders</span>
                        <span class="sum-val"><?php echo number_format($today_stats['total']); ?></span>
                    </div>
                    <div class="sum-row">
                        <span class="sum-label">Pending</span>
                        <span class="sum-val sum-val-amber"><?php echo number_format($today_stats['pending']); ?></span>
                    </div>
                    <div class="sum-row">
                        <span class="sum-label">Ready for Pickup</span>
                        <span class="sum-val sum-val-primary"><?php echo number_format($today_stats['ready']); ?></span>
                    </div>
                    <div class="sum-row">
                        <span class="sum-label">Completed</span>
                        <span class="sum-val sum-val-green"><?php echo number_format($today_stats['completed']); ?></span>
                    </div>
                    <div class="sum-row sum-row-last">
                        <span class="sum-label">Today's Orders</span>
                        <span class="sum-val sum-val-primary"><?php echo number_format($today_orders_count); ?></span>
                    </div>
                </div>
            </div>

            <!-- Today Revenue -->
            <div class="panel panel-summary">
                <div class="panel-head">
                    <h2 class="panel-title">Today's Revenue</h2>
                </div>
                <div style="padding: 28px 24px; text-align: center;">
                    <div style="font-size: 2.4rem; font-weight: 800; color: var(--accent); letter-spacing: -0.04em; line-height: 1;">
                        ₹<?php echo number_format($today_revenue, 0); ?>
                    </div>
                    <p style="font-size: 0.78rem; color: var(--text-3); margin: 8px 0 0;">Revenue from completed orders today</p>
                </div>
            </div>

        </div>
    </div>

</div><!-- /staff-dash-root -->
</main>

<script>
// Count-up animation (same as admin)
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

<!-- ════ STAFF DASHBOARD STYLES ════ -->
<style>
/* ── Design tokens (mirrors admin) ─── */
#staff-dash-root {
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

/* ── Page header ─── */
.dh-top {
    display: flex; align-items: flex-end; justify-content: space-between;
    padding: 32px 0 28px; gap: 16px;
}
.dh-breadcrumb { font-size: 0.78rem; color: var(--text-3); margin: 0 0 6px; font-weight: 500; letter-spacing: 0.02em; }
.dh-breadcrumb strong { color: var(--text-2); }
.dh-title { font-size: 1.85rem; font-weight: 800; color: var(--text-1); margin: 0; letter-spacing: -0.04em; line-height: 1; }
.dh-top-right { display: flex; align-items: center; gap: 12px; }
.dh-date-chip {
    display: flex; align-items: center; gap: 7px;
    font-size: 0.8rem; color: var(--text-2); font-weight: 500;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 30px; padding: 8px 16px; box-shadow: var(--shadow-card);
}
.dh-refresh-btn {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.82rem; font-weight: 600; color: var(--text-1);
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 30px; padding: 9px 18px;
    cursor: pointer; font-family: inherit; box-shadow: var(--shadow-card);
    transition: box-shadow .2s, border-color .2s;
}
.dh-refresh-btn:hover { border-color: var(--accent); box-shadow: var(--shadow-hover); }

/* ── Metric cards ─── */
.metric-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 32px; }
.metric-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-card); padding: 26px 24px 22px;
    box-shadow: var(--shadow-card); transition: box-shadow .25s, transform .25s; cursor: default;
}
.metric-card:hover { box-shadow: var(--shadow-hover); transform: translateY(-3px); }
.metric-label { font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-3); margin-bottom: 12px; }
.metric-value { font-size: 2rem; font-weight: 800; color: var(--text-1); letter-spacing: -0.04em; line-height: 1; margin-bottom: 18px; }
.metric-footer { display: flex; align-items: center; justify-content: space-between; }
.mf-tag { font-size: 0.72rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; }
.mf-tag-green  { background: #f0fdf4; color: #16a34a; }
.mf-tag-blue   { background: #eff6ff; color: #2563eb; }
.mf-tag-amber  { background: #fffbeb; color: #d97706; }
.metric-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.mic-revenue  { background: var(--accent-light); color: var(--accent); }
.mic-today    { background: #eff6ff; color: #2563eb; }
.mic-orders   { background: #fffbeb; color: #d97706; }
.mic-products { background: #f0fdf4; color: #16a34a; }

/* ── Quick actions ─── */
.qa-section { margin-bottom: 32px; }
.section-label { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-3); margin: 0 0 14px; }
.qa-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.qa-tile {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-card);
    padding: 18px 20px; display: flex; align-items: center; gap: 14px;
    text-decoration: none; color: var(--text-1); box-shadow: var(--shadow-card);
    transition: box-shadow .2s, border-color .2s, transform .2s;
}
.qa-tile:hover { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(67,24,255,.08), var(--shadow-hover); transform: translateY(-2px); }
.qa-tile-icon {
    width: 40px; height: 40px; border-radius: var(--radius-sm);
    background: var(--surface-raised); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center; color: var(--text-2);
    flex-shrink: 0; transition: background .2s, color .2s;
}
.qa-tile:hover .qa-tile-icon { background: var(--accent-light); color: var(--accent); border-color: transparent; }
.qa-tile-body { flex: 1; min-width: 0; }
.qa-tile-title { display: block; font-size: 0.88rem; font-weight: 700; color: var(--text-1); margin-bottom: 2px; }
.qa-tile-desc  { display: block; font-size: 0.75rem; color: var(--text-3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.qa-tile-arrow { color: var(--text-3); flex-shrink: 0; transition: transform .2s, color .2s; }
.qa-tile:hover .qa-tile-arrow { transform: translateX(3px); color: var(--accent); }

/* ── Content grid ─── */
.content-grid { display: grid; grid-template-columns: 1fr 360px; gap: 22px; align-items: start; margin-bottom: 48px; }
.sidebar-panels { display: flex; flex-direction: column; gap: 18px; }

/* ── Panel ─── */
.panel { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-card); box-shadow: var(--shadow-card); overflow: hidden; }
.panel-head { display: flex; align-items: flex-start; justify-content: space-between; padding: 22px 24px 16px; border-bottom: 1px solid var(--border); }
.panel-title { font-size: 0.95rem; font-weight: 700; color: var(--text-1); margin: 0 0 3px; }
.panel-sub   { font-size: 0.77rem; color: var(--text-3); margin: 0; }
.panel-link  { font-size: 0.78rem; color: var(--accent); text-decoration: none; font-weight: 600; white-space: nowrap; margin-top: 2px; padding: 5px 12px; border-radius: 6px; transition: background .2s; }
.panel-link:hover { background: var(--accent-light); }
.live-dot { display: flex; align-items: center; gap: 7px; font-size: 0.75rem; font-weight: 600; color: #16a34a; margin-top: 2px; }
.ld-pulse { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; animation: ldpulse 2s ease-in-out infinite; }
@keyframes ldpulse { 0%,100% { box-shadow: 0 0 0 0 rgba(34,197,94,.4); } 50% { box-shadow: 0 0 0 5px rgba(34,197,94,0); } }

/* ── Data table ─── */
.data-table { width: 100%; border-collapse: collapse; font-size: 0.855rem; }
.data-table thead th { text-align: left; padding: 10px 14px; font-size: 0.69rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-3); font-weight: 700; background: var(--surface-raised); border-bottom: 1px solid var(--border); }
.data-table thead th:first-child { padding-left: 24px; }
.data-table thead th:last-child  { padding-right: 24px; width: 48px; }
.data-table tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
.data-table tbody tr:last-child { border-bottom: none; }
.data-table tbody tr:hover { background: var(--surface-raised); }
.data-table tbody td { padding: 14px; color: var(--text-1); vertical-align: middle; }
.data-table tbody td:first-child { padding-left: 24px; }
.data-table tbody td:last-child  { padding-right: 24px; }

.oid  { font-weight: 700; font-size: 0.8rem; color: var(--accent); }
.cust { display: flex; align-items: center; gap: 10px; }
.cust-av { width: 28px; height: 28px; border-radius: 50%; background: var(--accent-light); color: var(--accent); font-size: 0.72rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cust-name  { font-weight: 500; font-size: 0.85rem; }
.items-cell { color: var(--text-3); font-size: 0.8rem; }
.amt        { font-weight: 700; font-size: 0.88rem; color: var(--text-1); }

/* Status pills */
.status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 30px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
.sp-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.sp-pending   { background: #fffbeb; color: #b45309; }
.sp-ready     { background: #eff6ff; color: #1d4ed8; }
.sp-completed { background: #f0fdf4; color: #15803d; }
.sp-cancelled { background: #fef2f2; color: #b91c1c; }

.row-action-btn {
    width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--border);
    background: var(--surface-raised); display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--text-2); transition: all .2s; text-decoration: none;
}
.row-action-btn:hover { background: var(--accent-light); color: var(--accent); border-color: transparent; }

.empty-msg { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 48px 24px; color: var(--text-3); }
.empty-msg p { margin: 0; font-size: 0.9rem; }

/* ── Summary panel ─── */
.panel-summary .panel-head { border-bottom: 1px solid var(--border); }
.summary-list { padding: 4px 0 8px; }
.sum-row { display: flex; align-items: center; justify-content: space-between; padding: 13px 24px; border-bottom: 1px solid var(--border); }
.sum-row:last-child, .sum-row-last { border-bottom: none; }
.sum-label { font-size: 0.83rem; color: var(--text-2); font-weight: 500; }
.sum-val   { font-size: 0.9rem; font-weight: 800; color: var(--text-1); }
.sum-val-amber   { color: #d97706; }
.sum-val-red     { color: #dc2626; }
.sum-val-primary { color: var(--accent); }
.sum-val-green   { color: #16a34a; }

/* ── Responsive ─── */
@media (max-width: 1100px) { .metric-row { grid-template-columns: repeat(2, 1fr); } .qa-row { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 860px)  { .content-grid { grid-template-columns: 1fr; } }
@media (max-width: 640px)  { .metric-row { grid-template-columns: 1fr 1fr; } .dh-top { flex-direction: column; align-items: flex-start; } }
</style>

<?php require_once 'footer.php'; ?>
