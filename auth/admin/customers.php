<?php
$page_title = 'Customers - Admin';
$current_page = 'customers';

require_once 'admin_middleware.php';
require_once 'header.php';

// Get search parameter
$search = $_GET['search'] ?? '';

// Build query for customers
$query = "SELECT u.*, COUNT(o.id) as total_orders, MAX(o.created_at) as last_order
          FROM users u
          LEFT JOIN orders o ON u.id = o.user_id
          WHERE u.role = 'customer'";

if ($search) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (u.full_name LIKE '%$search_term%' OR u.email LIKE '%$search_term%' OR u.phone LIKE '%$search_term%')";
}

$query .= " GROUP BY u.id ORDER BY u.created_at DESC";
$customers = $conn->query($query);
?>

<?php require_once 'sidebar.php'; ?>

<main class="admin-main">
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Customers</h1>
                <p class="text-secondary">Manage and view your customer base</p>
            </div>
            <!-- Optional Actions -->
        </div>

        <!-- Filters -->
         <div class="filters-bar">
            <form method="GET" class="filters-form">
                <div class="filter-group" style="max-width: 400px;">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search customers by name, email or phone..." 
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="filter-input"
                    >
                </div>
                <button type="submit" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Search
                </button>
                <?php if ($search): ?>
                    <a href="customers.php" class="btn btn-text">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Customers Table -->
        <div class="dashboard-card"> <!-- Using dashboard-card to match staff design -->
            <div class="card-header">
                <h2>All Customers</h2>
                <span class="badge badge-neutral"><?php echo $customers->num_rows; ?> found</span>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Contact Info</th>
                            <th>Joined Date</th>
                            <th>Orders</th>
                            <th>Last Active</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($customers && $customers->num_rows > 0): ?>
                            <?php while ($customer = $customers->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #F4F7FE; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--color-primary);">
                                                <?php echo strtoupper(substr($customer['full_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h4 style="margin: 0; font-size: 0.95rem;"><?php echo htmlspecialchars($customer['full_name']); ?></h4>
                                                <span class="text-secondary" style="font-size: 0.8rem;">ID: #<?php echo $customer['id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <span style="font-size: 0.9rem;"><?php echo htmlspecialchars($customer['email']); ?></span>
                                            <?php if($customer['phone']): ?>
                                                <span class="text-secondary" style="font-size: 0.8rem;"><?php echo htmlspecialchars($customer['phone']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-neutral"><?php echo $customer['total_orders']; ?> orders</span>
                                    </td>
                                    <td>
                                        <?php if($customer['last_order']): ?>
                                            <?php echo date('M d, Y', strtotime($customer['last_order'])); ?>
                                        <?php else: ?>
                                            <span class="text-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($customer['is_blocked']): ?>
                                            <span class="badge badge-danger">Blocked</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($customer['is_blocked']): ?>
                                            <button 
                                                class="btn-icon text-success" 
                                                title="Unblock User"
                                                onclick="toggleUserStatus(<?php echo $customer['id']; ?>, 'unblock', '<?php echo addslashes($customer['full_name']); ?>')"
                                            >
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                                </svg>
                                            </button>
                                        <?php else: ?>
                                            <button 
                                                class="btn-icon text-danger" 
                                                title="Block User"
                                                onclick="toggleUserStatus(<?php echo $customer['id']; ?>, 'block', '<?php echo addslashes($customer['full_name']); ?>')"
                                            >
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: var(--color-text-secondary);">
                                    No customers found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
function toggleUserStatus(userId, status, userName) {
    const action = status === 'block' ? 'Block' : 'Unblock';
    
    if (confirm(`Are you sure you want to ${action.toLowerCase()} ${userName}?`)) {
        fetch('toggle_customer_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload to show changes
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request');
        });
    }
}
</script>

<?php require_once 'footer.php'; ?>
