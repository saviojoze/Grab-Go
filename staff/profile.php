<?php
$page_title = 'My Profile - Staff Portal';
$current_page = 'profile';

require_once 'staff_middleware.php';

$user_id = $_SESSION['user_id'];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_profile') {
        $full_name = sanitize_input($_POST['full_name']);
        $phone = sanitize_input($_POST['phone']);
        
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $phone, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name; // Update session
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
                
                $new_filename = 'staff_' . $user_id . '_' . time() . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                    // Update DB
                    $db_path = 'images/profiles/' . $new_filename;
                    $conn->query("UPDATE users SET profile_picture = '$db_path' WHERE id = $user_id");
                    $_SESSION['success'] = "Profile picture updated!";
                }
            }
        }
    }
    redirect('profile.php');
}

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

require_once 'header.php';
require_once 'sidebar.php';
?>

<main class="admin-main">
    <div class="admin-container animate-fade-up">
        <div class="page-header">
            <div>
                <h1>My Profile</h1>
                <p class="text-secondary">Manage your personal information</p>
            </div>
        </div>

        <div class="dashboard-grid profile-grid">
            <!-- Profile Column -->
            <div class="dashboard-card animate-slide-in stagger-1">
                <div class="card-header">
                    <h2>Public Profile</h2>
                </div>
                <!-- Changed to card-body for strictly identical padding -->
                <div class="card-body" style="text-align: center;">
                    <div class="profile-avatar-wrapper">
                        <?php if ($user['profile_picture']): ?>
                            <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="dpForm" method="POST" enctype="multipart/form-data" class="dp-upload-form">
                            <input type="hidden" name="action" value="upload_dp">
                            <label for="profile_pic" class="upload-btn" title="Change Profile Picture">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </label>
                            <input type="file" id="profile_pic" name="profile_pic" hidden onchange="document.getElementById('dpForm').submit()">
                        </form>
                    </div>
                    
                    <h2 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <div class="profile-badge">
                        <span class="status-badge status-ready">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Edit Details Column -->
            <div class="dashboard-card animate-slide-in stagger-2">
                <div class="card-header">
                    <div>
                        <h2>Edit Details</h2>
                        <p class="text-secondary text-sm">Update your account information</p>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+91 9876543210">
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                                <small class="text-secondary" style="margin-top: 6px; display: block; font-size: 0.8rem;">Email cannot be changed.</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-input" value="<?php echo date('M d, Y', strtotime($user['created_at'])); ?>" readonly disabled>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
/* Page Specific Styles */
.profile-grid {
    grid-template-columns: 320px 1fr !important;
    gap: 32px;
    align-items: start;
}

.profile-avatar-wrapper {
    position: relative;
    width: 140px;
    height: 140px;
    margin: 0 auto 24px;
}

.profile-avatar-wrapper img, 
.avatar-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #FFF;
    box-shadow: 0 10px 25px rgba(67, 24, 255, 0.15);
}

.avatar-placeholder {
    background: linear-gradient(135deg, #868CFF 0%, #4318FF 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3.5rem;
    font-weight: 700;
}

.dp-upload-form {
    position: absolute;
    bottom: 0;
    right: 0;
}

.upload-btn {
    background: #4318FF;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(67, 24, 255, 0.4);
    border: 3px solid #FFF;
    transition: all 0.2s ease;
}

.upload-btn:hover {
    transform: scale(1.1);
    background: #2B3674;
}

.profile-name {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1B2559;
    margin-bottom: 4px;
}

.profile-email {
    color: #A3AED0;
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 16px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid rgba(163, 174, 208, 0.1);
}

@media (max-width: 992px) {
    .profile-grid {
        grid-template-columns: 1fr !important;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once 'footer.php'; ?>
