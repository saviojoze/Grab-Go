<?php
$page_title = 'Manage Staff - Grab & Go';
$current_page = 'staff_list';

require_once 'admin_middleware.php';

// Handle Add Staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_staff']) || isset($_POST['action']) && $_POST['action'] === 'add_staff')) {
    try {
        $name = trim(sanitize_input($_POST['full_name']));
        $position = trim(sanitize_input($_POST['position']));
        $email = trim(sanitize_input($_POST['email']));
        
        if (empty($position)) $position = "Staff"; // Default

        if (!empty($name) && !empty($email)) {
            // 1. Check if email already exists
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $_SESSION['error'] = "Error: Email '$email' is already registered!";
            } else {
                // 2. Create User Account (role='staff')
                $conn->begin_transaction();
                
                $random_pass = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
                $stmt_user = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'staff')");
                $stmt_user->bind_param("sss", $name, $email, $random_pass);
                
                if ($stmt_user->execute()) {
                    // 3. Add to Staff Directory
                    $stmt = $conn->prepare("INSERT INTO staff_directory (full_name, position) VALUES (?, ?)");
                    $stmt->bind_param("ss", $name, $position);
                    
                    if ($stmt->execute()) {
                        $conn->commit();
                        $_SESSION['success'] = "Added staff: $name. They can now login with $email";
                    } else {
                        $conn->rollback();
                        $_SESSION['error'] = "Failed to add to directory: " . $stmt->error;
                    }
                } else {
                    $conn->rollback();
                    // Check for duplicate entry error specifically
                    if ($conn->errno == 1062) {
                        $_SESSION['error'] = "Error: Email '$email' is already registered (Duplicate Entry).";
                    } else {
                        $_SESSION['error'] = "Error creating account: " . $stmt_user->error;
                    }
                }
            }
        } else {
            $_SESSION['error'] = "Please fill in all required fields.";
        }
    } catch (Exception $e) {
        if (isset($conn)) $conn->rollback();
        $_SESSION['error'] = "System Error: " . $e->getMessage();
    }
    
    // PRG Pattern: Redirect to avoid resubmission
    header("Location: manage_staff.php");
    exit;
}

// Handle Delete (Soft Delete)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("UPDATE staff_directory SET is_active = 0 WHERE id = $id");
    $_SESSION['success'] = "Staff member removed.";
    header("Location: manage_staff.php");
    exit;
}

require_once 'header.php';



// Fetch Staff
$staff_list = [];
$res = $conn->query("SELECT * FROM staff_directory WHERE is_active = 1 ORDER BY full_name ASC");
while ($row = $res->fetch_assoc()) {
    $staff_list[] = $row;
}
?>

<?php require_once 'sidebar.php'; ?>

<style>
    /* Page Specific Styles */
    .staff-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 24px;
        margin-top: 24px;
    }

    .staff-card {
        background: #fff;
        border-radius: 16px;
        padding: 30px 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); /* Softer shadow */
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid transparent;
        position: relative;
    }

    .staff-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-color: var(--color-primary);
    }

    .staff-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #E0E7FF 0%, #F4F7FE 100%);
        color: var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 16px;
        border: 4px solid #fff;
        box-shadow: 0 4px 12px rgba(67, 24, 255, 0.15);
    }

    .staff-name {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--color-text-primary);
        margin: 0 0 6px 0;
    }

    .staff-position {
        font-size: 0.9rem;
        color: var(--color-text-secondary);
        background: #F4F7FE;
        padding: 4px 12px;
        border-radius: 20px;
        display: inline-block;
        margin-bottom: 24px;
        font-weight: 600;
    }

    .staff-actions {
        width: 100%;
        border-top: 1px solid #F4F7FE;
        padding-top: 20px;
        display: flex;
        justify-content: center;
    }

    .btn-remove {
        color: #EE5D50;
        background: rgba(238, 93, 80, 0.05);
        border: none;
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-remove:hover {
        background: #EE5D50;
        color: #fff;
    }

    /* Add Form Refinements */
    .add-staff-wrapper {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        padding: 30px;
        margin-bottom: 32px;
    }
    
    .add-staff-form {
        display: grid;
        grid-template-columns: 1.5fr 1.5fr 1fr auto;
        gap: 20px;
        align-items: flex-end;
        margin-top: 20px;
    }

    @media (max-width: 768px) {
        .add-staff-form {
            grid-template-columns: 1fr;
        }
        .btn-add {
            width: 100%;
        }
    }
</style>

<main class="admin-main">
    <div class="admin-container">
        
        <div class="page-header">
            <div>
                <h1>Staff Directory</h1>
                <p class="text-secondary">Manage your team members and roles</p>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background: #E6FFFA; color: #05CD99; border: 1px solid rgba(5,205,153,0.2); display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background: #FFEFEF; color: #EE5D50; border: 1px solid rgba(238,93,80,0.2);">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Add Staff Section -->
        <div class="add-staff-wrapper">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-text-primary); margin: 0;">Add New Employee</h2>
                <p style="color: var(--color-text-secondary); font-size: 0.9rem; margin-top: 4px;">Enter details to register a new staff member.</p>
            </div>
            
            <form method="POST" class="add-staff-form">
                <input type="hidden" name="action" value="add_staff">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.9rem;">Full Name</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 12px; color: #A3AED0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </span>
                        <input type="text" name="full_name" class="form-input" placeholder="e.g. John Doe" style="padding-left: 48px; height: 48px;" required>
                    </div>
                </div>
                <!-- Email Field -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.9rem;">Email Address</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 12px; color: #A3AED0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        </span>
                        <input type="email" name="email" class="form-input" placeholder="staff@example.com" style="padding-left: 48px; height: 48px;" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.9rem;">Position</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 12px; color: #A3AED0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        </span>
                        <input type="text" name="position" class="form-input" placeholder="e.g. Cashier" style="padding-left: 48px; height: 48px;">
                    </div>
                </div>
                <button type="submit" name="add_staff" class="btn btn-primary btn-add" style="height: 48px; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 0 24px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Staff
                </button>
            </form>
        </div>

        <!-- Staff List Grid -->
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-text-primary); margin-top: 40px; margin-bottom: 16px;">Current Staff Members</h2>
        
        <?php if (empty($staff_list)): ?>
            <div class="empty-state" style="background: #fff; border-radius: 16px; padding: 60px 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="empty-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <h3>No staff members found</h3>
                <p>Get started by adding a new employee above.</p>
            </div>
        <?php else: ?>
            <div class="staff-grid">
                <?php foreach ($staff_list as $staff): 
                    // Generate initials
                    $initials = strtoupper(substr($staff['full_name'], 0, 1));
                    if (strpos($staff['full_name'], ' ') !== false) {
                        $parts = explode(' ', $staff['full_name']);
                        if (isset($parts[1])) $initials .= strtoupper(substr($parts[1], 0, 1));
                    }
                ?>
                    <div class="staff-card">
                        <div class="staff-avatar">
                            <?php echo $initials; ?>
                        </div>
                        <h3 class="staff-name"><?php echo htmlspecialchars($staff['full_name']); ?></h3>
                        <span class="staff-position"><?php echo htmlspecialchars($staff['position']); ?></span>
                        
                        <div class="staff-actions">
                            <a href="manage_staff.php?delete=<?php echo $staff['id']; ?>" class="btn-remove" onclick="return confirm('Are you sure you want to remove this staff member?');">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                Remove
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    </div>
</main>
<?php require_once 'footer.php'; ?>
