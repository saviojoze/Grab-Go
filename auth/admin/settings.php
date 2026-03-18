<?php
$page_title = 'Settings - Grab & Go';
$current_page = 'settings';

require_once 'admin_middleware.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user_id'] ?? 0;

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error_msg = "All fields are required.";
    } elseif ($new_pass !== $confirm_pass) {
        $error_msg = "New passwords do not match.";
    } elseif (strlen($new_pass) < 6) {
        $error_msg = "New password must be at least 6 characters long.";
    } else {
        // Verify current password
        if ($stmt = $conn->prepare("SELECT password FROM users WHERE id = ?")) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($res->num_rows === 1) {
                $row = $res->fetch_assoc();
                if (password_verify($current_pass, $row['password'])) {
                    // Update password
                    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                    if ($update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?")) {
                        $update_stmt->bind_param("si", $new_hash, $user_id);
                        if ($update_stmt->execute()) {
                            $success_msg = "Password updated successfully.";
                        } else {
                            $error_msg = "Database Error: " . $conn->error;
                        }
                        $update_stmt->close();
                    } else {
                        $error_msg = "Prepare Error: " . $conn->error;
                    }
                } else {
                    $error_msg = "Incorrect current password. Please try again.";
                }
            } else {
                $error_msg = "User account not found (ID: $user_id).";
            }
            $stmt->close();
        } else {
            $error_msg = "System Error: " . $conn->error;
        }
    }
}

require_once 'header.php';
?>

<?php require_once 'sidebar.php'; ?>

<main class="admin-main">
    <div class="admin-container">
        
        <div class="page-header">
            <div>
                <h1>Settings</h1>
                <p class="text-secondary">Manage your dashboard preferences</p>
            </div>
        </div>
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success" style="padding: 15px; margin-bottom: 24px; border-radius: 12px; background: #E6FFFA; color: #05CD99; border: 1px solid rgba(5,205,153,0.2);">
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger" style="padding: 15px; margin-bottom: 24px; border-radius: 12px; background: #FFF5F5; color: #E53E3E; border: 1px solid rgba(229, 62, 62, 0.2);">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 24px;">
            
            <!-- Appearance Settings -->
            <div class="card" style="padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #F4F7FE; color: var(--color-primary); display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.1rem;">Appearance</h3>
                        <p class="text-secondary" style="margin: 0; font-size: 0.85rem;">Customize how the dashboard looks</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: block; margin-bottom: 12px; font-weight: 600;">Theme Preference</label>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <label class="theme-option" style="cursor: pointer;">
                            <input type="radio" name="theme" value="light" onchange="changeTheme('light')" checked style="display: none;">
                            <div class="theme-card" style="border: 2px solid var(--color-border); border-radius: 12px; padding: 16px; text-align: center; transition: all 0.2s;">
                                <div style="margin-bottom: 12px;">☀️</div>
                                <span style="font-weight: 500;">Light Mode</span>
                            </div>
                        </label>
                        
                        <label class="theme-option" style="cursor: pointer;">
                            <input type="radio" name="theme" value="dark" onchange="changeTheme('dark')" style="display: none;">
                            <div class="theme-card" style="border: 2px solid var(--color-border); border-radius: 12px; padding: 16px; text-align: center; transition: all 0.2s;">
                                <div style="margin-bottom: 12px;">🌙</div>
                                <span style="font-weight: 500;">Dark Mode</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Account Settings / Change Password -->
            <div class="card" style="padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #F4F7FE; color: var(--color-primary); display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.1rem;">Security</h3>
                        <p class="text-secondary" style="margin: 0; font-size: 0.85rem;">Update your password</p>
                    </div>
                </div>
                
                <form method="POST" action="settings.php">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem;">Current Password</label>
                        <input type="password" name="current_password" class="form-input" required style="width: 100%; padding: 10px 16px; border: 1px solid var(--color-border); border-radius: 8px; font-family: inherit;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem;">New Password</label>
                        <input type="password" name="new_password" class="form-input" required style="width: 100%; padding: 10px 16px; border: 1px solid var(--color-border); border-radius: 8px; font-family: inherit;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem;">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input" required style="width: 100%; padding: 10px 16px; border: 1px solid var(--color-border); border-radius: 8px; font-family: inherit;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; border-radius: 12px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        Update Password
                    </button>
                </form>
            </div>
            
        </div>
        
    </div>
</main>

<style>
    /* Selected State Styling */
    input[name="theme"]:checked + .theme-card {
        border-color: var(--color-primary);
        background: rgba(67, 24, 255, 0.05);
        color: var(--color-primary);
    }
</style>

<script>
    // Initialize Radio State
    document.addEventListener('DOMContentLoaded', () => {
        const currentTheme = localStorage.getItem('adminToTheme') || 'light';
        const radio = document.querySelector(`input[name="theme"][value="${currentTheme}"]`);
        if (radio) radio.checked = true;
    });

    function changeTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }
        localStorage.setItem('adminToTheme', theme);
    }
</script>

<?php require_once 'footer.php'; ?>
