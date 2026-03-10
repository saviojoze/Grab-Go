<aside class="admin-sidebar" id="adminSidebar">
    <nav class="sidebar-nav">
        <a href="index.php" data-section="dashboard" class="sidebar-item <?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="products.php" data-section="products" class="sidebar-item <?php echo ($current_page === 'products') ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
            </svg>
            <span>Product Inventory</span>
        </a>
        
        <a href="orders.php" data-section="orders" class="sidebar-item <?php echo ($current_page === 'orders') ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span>Order Management</span>
        </a>

        <a href="profile.php" data-section="profile" class="sidebar-item <?php echo ($current_page === 'profile') ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>My Profile</span>
        </a>

        <a href="my_leaves.php" data-section="leaves" class="sidebar-item <?php echo ($current_page === 'my_leaves') ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>My Leaves</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <a href="../auth/logout.php" class="sidebar-item text-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Sign Out</span>
        </a>
    </div>
</aside>

<style>
/* ── Staff Sidebar: Admin-style design with unique section colors ── */

.sidebar-item.active {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #FFF !important;
    border-right: 4px solid #4318FF !important;
    border-left: none !important;
    box-shadow: none !important;
    transform: none !important;
}

.sidebar-item.active svg {
    color: #4318FF !important;
}

/* Hover effects */
.sidebar-item:not(.active):hover {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #FFF !important;
}
</style>

<script>
// Sidebar Toggle
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.toggle('active');
});

// Profile Dropdown
document.getElementById('userProfileBtn')?.addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('userDropdownMenu')?.classList.toggle('show');
});

// Close dropdown on outside click
document.addEventListener('click', function() {
    document.getElementById('userDropdownMenu')?.classList.remove('show');
});
</script>
