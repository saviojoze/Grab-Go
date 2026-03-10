<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Staff Portal - Grab & Go'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-premium.css?v=<?php echo time(); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="../images/logo.svg">
</head>
<body class="admin-body">
    
    <!-- Staff Header -->
    <header class="admin-header">
        <div class="admin-header-left">
            <button type="button" class="sidebar-toggle" id="sidebarToggle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="admin-logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span>Grab & Go Staff</span>
            </div>
        </div>
        
        <div class="admin-header-right staff-header-right">
            <!-- Notifications -->
            <?php
                $s_notif_pending  = (int)$conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")->fetch_assoc()['c'];
                $s_notif_ready    = (int)$conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'ready'")->fetch_assoc()['c'];
                $s_notif_lowstock = (int)$conn->query("SELECT COUNT(*) as c FROM products WHERE stock > 0 AND stock <= 5")->fetch_assoc()['c'];
                $s_notif_total    = $s_notif_pending + ($s_notif_ready > 0 ? 1 : 0) + ($s_notif_lowstock > 0 ? 1 : 0);
                $s_recent_pending = $conn->query("SELECT o.id, u.full_name, o.total, o.created_at FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.status = 'pending' ORDER BY o.created_at DESC LIMIT 4");
            ?>
            <div class="notif-wrap" id="notifWrap">
                <button type="button" class="icon-btn" id="notifBtn" title="Notifications">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <?php if ($s_notif_total > 0): ?>
                    <span class="notification-badge"><?php echo $s_notif_total; ?></span>
                    <?php endif; ?>
                </button>

                <div class="notif-panel" id="notifPanel">
                    <div class="notif-hd">
                        <span class="notif-hd-title">Notifications</span>
                        <?php if ($s_notif_total > 0): ?>
                        <span class="notif-hd-chip"><?php echo $s_notif_total; ?> new</span>
                        <?php endif; ?>
                    </div>
                    <div class="notif-list">
                        <?php if ($s_notif_pending > 0 && $s_recent_pending): while ($no = $s_recent_pending->fetch_assoc()): ?>
                        <a href="orders.php" class="notif-item">
                            <div class="notif-ico notif-ico-amber">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            </div>
                            <div class="notif-body">
                                <p class="notif-msg">New order from <strong><?php echo htmlspecialchars($no['full_name'] ?? 'Guest'); ?></strong></p>
                                <span class="notif-sub">&#8377;<?php echo number_format($no['total'], 2); ?> &middot; <?php echo date('d M, g:i A', strtotime($no['created_at'])); ?></span>
                            </div>
                            <span class="notif-dot"></span>
                        </a>
                        <?php endwhile; endif; ?>

                        <?php if ($s_notif_ready > 0): ?>
                        <a href="orders.php" class="notif-item">
                            <div class="notif-ico notif-ico-blue">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <div class="notif-body">
                                <p class="notif-msg"><strong><?php echo $s_notif_ready; ?> order<?php echo $s_notif_ready > 1 ? 's' : ''; ?></strong> ready for pickup</p>
                                <span class="notif-sub">Awaiting customer collection</span>
                            </div>
                            <span class="notif-dot"></span>
                        </a>
                        <?php endif; ?>

                        <?php if ($s_notif_lowstock > 0): ?>
                        <a href="products.php" class="notif-item">
                            <div class="notif-ico notif-ico-orange">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            </div>
                            <div class="notif-body">
                                <p class="notif-msg"><strong><?php echo $s_notif_lowstock; ?> product<?php echo $s_notif_lowstock > 1 ? 's' : ''; ?></strong> running low on stock</p>
                                <span class="notif-sub">Click to review inventory</span>
                            </div>
                            <span class="notif-dot"></span>
                        </a>
                        <?php endif; ?>

                        <?php if ($s_notif_total === 0): ?>
                        <div class="notif-empty">
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            <p>All caught up!</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="notif-ft">
                        <a href="orders.php">View all orders &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- User Profile (Admin Style) -->
            <div class="user-profile-dropdown">
                <button type="button" class="user-profile-btn" id="userProfileBtn">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1)); ?>
                    </div>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <a href="profile.php" class="dropdown-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        My Profile
                    </a>
                    <a href="my_leaves.php" class="dropdown-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        My Leaves
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="../auth/logout.php" class="dropdown-item text-danger">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>

        </div>
    </header>
    
    <div class="admin-layout">

<!-- Notification Dropdown Styles & Script -->
<style>
.notif-wrap { position: relative; }
.notif-panel {
    display: none;
    position: absolute; top: calc(100% + 14px); right: 0;
    width: 340px; background: #fff;
    border: 1px solid #e8eaf0; border-radius: 16px;
    box-shadow: 0 8px 40px rgba(15,23,42,.16), 0 2px 10px rgba(15,23,42,.08);
    z-index: 9999; overflow: hidden;
}
.notif-panel.open { display: block; animation: notifIn .18s cubic-bezier(.4,0,.2,1); }
@keyframes notifIn { from { opacity:0; transform: translateY(-8px) scale(.97); } to { opacity:1; transform: translateY(0) scale(1); } }
.notif-hd { display: flex; align-items: center; justify-content: space-between; padding: 16px 18px 12px; border-bottom: 1px solid #e8eaf0; }
.notif-hd-title { font-size: 0.88rem; font-weight: 700; color: #0f172a; }
.notif-hd-chip { font-size: 0.68rem; font-weight: 700; background: #4318FF; color: #fff; padding: 2px 9px; border-radius: 20px; }
.notif-list { max-height: 320px; overflow-y: auto; }
.notif-item { display: flex; align-items: flex-start; gap: 12px; padding: 13px 18px; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background .15s; }
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #f8f9fc; }
.notif-ico { width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.notif-ico-amber  { background: #fffbeb; color: #d97706; }
.notif-ico-blue   { background: #eff6ff; color: #2563eb; }
.notif-ico-orange { background: #fff7ed; color: #ea580c; }
.notif-ico-red    { background: #fef2f2; color: #dc2626; }
.notif-body { flex: 1; min-width: 0; }
.notif-msg { font-size: 0.82rem; color: #0f172a; margin: 0 0 3px; line-height: 1.4; }
.notif-msg strong { font-weight: 700; }
.notif-sub { font-size: 0.73rem; color: #94a3b8; }
.notif-dot { width: 7px; height: 7px; border-radius: 50%; background: #4318FF; flex-shrink: 0; margin-top: 6px; }
.notif-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 32px 18px; color: #94a3b8; }
.notif-empty p { margin: 0; font-size: 0.85rem; font-weight: 500; }
.notif-ft { padding: 12px 18px; border-top: 1px solid #e8eaf0; text-align: center; }
.notif-ft a { font-size: 0.78rem; font-weight: 600; color: #4318FF; text-decoration: none; }
.notif-ft a:hover { text-decoration: underline; }
</style>
<script>
(function(){
    const btn     = document.getElementById('notifBtn');
    const panel   = document.getElementById('notifPanel');
    const wrap    = document.getElementById('notifWrap');
    const userBtn = document.getElementById('userProfileBtn');
    const userMenu= document.getElementById('userDropdownMenu');
    if (!btn || !panel) return;
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        panel.classList.toggle('open');
        if (userMenu) userMenu.classList.remove('show');
    });
    document.addEventListener('click', function(e) {
        if (!wrap.contains(e.target)) panel.classList.remove('open');
    });
    if (userBtn && userMenu) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenu.classList.toggle('show');
            panel.classList.remove('open');
        });
        document.addEventListener('click', function() {
            userMenu.classList.remove('show');
        });
    }
})();
</script>
