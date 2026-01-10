<?php
require_once __DIR__ . '/../config.php';

$token = $_GET['token'] ?? '';
$error = '';
$token_valid = false;

if (empty($token)) {
    redirect('login.php');
}

// Verify token
$stmt = $conn->prepare("SELECT id, email, full_name FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error = 'invalid_token';
} else {
    $token_valid = true;
    $user = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Grab & Go</title>
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Hero Section -->
        <div class="auth-hero">
            <div class="auth-hero-content">
                <span class="auth-hero-badge">SECURE RESET</span>
                <h1>Create New Password</h1>
                <p>Choose a strong password to protect your account.</p>
            </div>
        </div>
        
        <!-- Right Side - Reset Password Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <?php if ($error == 'invalid_token'): ?>
                    <!-- Invalid Token -->
                    <div class="auth-form-header">
                        <h2>Invalid or Expired Link</h2>
                        <p>This password reset link is invalid or has expired.</p>
                    </div>
                    
                    <div class="alert alert-error">
                        The reset link you used is either invalid or has expired. Password reset links are only valid for 1 hour.
                    </div>
                    
                    <a href="forgot-password.php" class="btn btn-primary btn-block btn-lg">
                        Request New Reset Link
                    </a>
                    
                    <div class="auth-footer">
                        <p><a href="login.php">← Back to Login</a></p>
                    </div>
                    
                <?php elseif ($token_valid): ?>
                    <!-- Reset Password Form -->
                    <div class="auth-form-header">
                        <h2>Reset Your Password</h2>
                        <p>Enter your new password below.</p>
                    </div>
                    
                    <form action="reset-password-handler.php" method="POST" class="auth-form">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <div class="password-input-wrapper">
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="password" 
                                    class="form-input" 
                                    placeholder="••••••••"
                                    required
                                    minlength="6"
                                    autofocus
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <div class="password-input-wrapper">
                                <input 
                                    type="password" 
                                    name="confirm_password" 
                                    id="confirm_password" 
                                    class="form-input" 
                                    placeholder="••••••••"
                                    required
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-error">
                                <?php 
                                    if ($_GET['error'] == 'mismatch') {
                                        echo 'Passwords do not match';
                                    } else if ($_GET['error'] == 'short') {
                                        echo 'Password must be at least 6 characters';
                                    }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            Reset Password
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p><a href="login.php">← Back to Login</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(id) {
            const passwordInput = document.getElementById(id);
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
