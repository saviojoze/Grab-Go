<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard - Grab & Go'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time() + 5; ?>">
    <link rel="stylesheet" href="../css/admin-theme.css?v=<?php echo time(); ?>">
    
    <script>
        // Init Theme
        (function() {
            const savedTheme = localStorage.getItem('adminToTheme');
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="../images/logo.svg">
</head>
<body class="admin-body">
    
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-header-left">
            <button class="sidebar-toggle" id="sidebarToggle">
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
                <span>Grab & Go Admin</span>
            </div>
        </div>
        
        <div class="admin-header-right">
            <!-- Notifications -->
            <button class="icon-btn" title="Notifications">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span class="notification-badge">3</span>
            </button>
            
            <!-- User Profile -->
            <div class="user-profile-dropdown">
                <button class="user-profile-btn" id="userProfileBtn">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)); ?>
                    </div>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <a href="../products/listing.php" class="dropdown-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        </svg>
                        View Store
                    </a>
                    <a href="#" class="dropdown-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Profile Settings
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
