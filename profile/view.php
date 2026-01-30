<?php
$page_title = 'My Profile - Grab & Go';
$current_page = 'profile';
$extra_css = 'css/admin.css';

require_once __DIR__ . '/../config.php';

// Check if logged in
if (!is_logged_in()) {
    redirect('../auth/login.php');
}

$user_id = get_user_id();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_profile') {
        $full_name = sanitize_input($_POST['full_name']);
        $phone = sanitize_input($_POST['phone']);
        
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $phone, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update profile.";
        }
    } elseif ($_POST['action'] == 'upload_dp') {
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['profile_pic']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $upload_dir = '../images/profiles/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $new_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                    // Get old pic to delete
                    $res = $conn->query("SELECT profile_picture FROM users WHERE id = $user_id");
                    $old_pic = $res->fetch_assoc()['profile_picture'];
                    if ($old_pic && file_exists('../' . $old_pic)) {
                        unlink('../' . $old_pic);
                    }
                    
                    $db_path = 'images/profiles/' . $new_filename;
                    $conn->query("UPDATE users SET profile_picture = '$db_path' WHERE id = $user_id");
                    $_SESSION['success'] = "Profile picture updated!";
                } else {
                    $_SESSION['error'] = "Failed to move uploaded file.";
                }
            } else {
                $_SESSION['error'] = "Invalid file type. Allowed: " . implode(', ', $allowed);
            }
        }
    }
    redirect('view.php');
}

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Ensure customer has a verification PIN
if ($user && $user['role'] === 'customer' && empty($user['verification_pin'])) {
    $verification_pin = sprintf("%06d", mt_rand(100000, 999999));
    $user['verification_pin'] = $verification_pin;
    
    // Save to DB
    $stmt_pin = $conn->prepare("UPDATE users SET verification_pin = ? WHERE id = ?");
    $stmt_pin->bind_param("si", $verification_pin, $user_id);
    $stmt_pin->execute();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-container py-xl">
    <div class="page-header">
        <div>
            <h1>My Profile</h1>
            <p class="text-secondary">Manage your account and preferences</p>
        </div>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: 320px 1fr;">
        <!-- Profile Overview Card -->
        <div class="dashboard-card shadow-sm">
            <div class="card-content text-center py-xl">
                <div class="avatar-wrapper mb-lg" style="position: relative; width: 120px; height: 120px; margin: 0 auto;">
                    <?php if ($user['profile_picture']): ?>
                        <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid #f0f0f0;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; border-radius: 50%; background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700;">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="dpForm" method="POST" enctype="multipart/form-data" style="position: absolute; bottom: 0; right: 0;">
                        <input type="hidden" name="action" value="upload_dp">
                        <label for="profile_pic" style="background: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #eee;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                        </label>
                        <input type="file" id="profile_pic" name="profile_pic" hidden onchange="document.getElementById('dpForm').submit()">
                    </form>
                </div>
                <h3 class="h4 mt-lg mb-xs"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p class="text-secondary small mb-lg"><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="flex justify-center gap-sm">
                    <span class="badge" style="background: #e3f2fd; color: #1565c0; font-weight: 600; font-size: 0.75rem;">CUSTOMER</span>
                </div>


            </div>
            <div class="border-t p-lg">
                <a href="../orders/my-orders.php" class="sidebar-item" style="border-radius: var(--radius-md);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span>My Orders</span>
                </a>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2>Account Settings</h2>
            </div>
            <div class="card-content p-lg">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="stats-grid" style="grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($user['full_name']); ?>" required style="border-radius: var(--radius-sm);">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="e.g. +91 9876543210" style="border-radius: var(--radius-sm);">
                        </div>
                    </div>
                    
                    <div class="stats-grid mt-lg" style="grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="background: var(--color-background); border-color: #eee; cursor: not-allowed;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Created</label>
                            <input type="text" class="form-input" value="<?php echo date('M d, Y', strtotime($user['created_at'])); ?>" readonly style="background: var(--color-background); border-color: #eee; cursor: not-allowed;">
                        </div>
                    </div>
                    
                    <div class="mt-xl flex justify-end">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 40px; border-radius: var(--radius-sm);">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
async function regeneratePin() {
    const icon = document.querySelector('.refresh-icon');
    icon.style.transition = 'transform 0.5s';
    icon.style.transform = 'rotate(180deg)';
    
    try {
        const response = await fetch('regenerate_pin.php?t=' + new Date().getTime(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.querySelector('#pinDisplay').textContent = data.pin;
            // Reset rotation
            setTimeout(() => {
                icon.style.transform = 'rotate(0deg)';
            }, 500);
        } else {
            alert('Failed to regenerate PIN');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
