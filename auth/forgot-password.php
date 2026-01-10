<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Grab & Go</title>
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/auth.css">
    
    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Hero Section -->
        <div class="auth-hero">
            <div class="auth-hero-content">
                <span class="auth-hero-badge">PASSWORD RECOVERY</span>
                <h1>Reset Your Password</h1>
                <p>Enter your email address and we'll send you a link to reset your password.</p>
            </div>
        </div>
        
        <!-- Right Side - Forgot Password Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <!-- Header -->
                <div class="auth-form-header">
                    <h2>Forgot Password?</h2>
                    <p>No worries, we'll send you reset instructions.</p>
                </div>
                
                <!-- Forgot Password Form -->
                <form id="forgotPasswordForm" class="auth-form">
                    <div class="form-group">
                        <label class="form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 4px;">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            Email Address
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="name@example.com"
                            required
                            autofocus
                        >
                    </div>
                    
                    <div id="errorMessage" class="alert alert-error" style="display: none;"></div>
                    <div id="successMessage" class="alert alert-success" style="display: none;"></div>
                    
                    <button type="submit" id="submitBtn" class="btn btn-primary btn-block btn-lg">
                        Send Reset Link
                    </button>
                </form>
                
                <!-- Footer -->
                <div class="auth-footer">
                    <p><a href="login.php">← Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Firebase Configuration
        const firebaseConfig = {
            apiKey: "<?php echo FIREBASE_API_KEY; ?>",
            authDomain: "<?php echo FIREBASE_AUTH_DOMAIN; ?>",
            projectId: "<?php echo FIREBASE_PROJECT_ID; ?>",
            storageBucket: "<?php echo FIREBASE_STORAGE_BUCKET; ?>",
            messagingSenderId: "<?php echo FIREBASE_MESSAGING_SENDER_ID; ?>",
            appId: "<?php echo FIREBASE_APP_ID; ?>"
        };
        
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();
        
        // Handle form submission
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const submitBtn = document.getElementById('submitBtn');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            
            // Hide previous messages
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';
            
            // Disable button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            // Send password reset email via Firebase
            auth.sendPasswordResetEmail(email)
                .then(() => {
                    // Email sent successfully
                    successMessage.innerHTML = '<strong>Success!</strong> Password reset email sent to ' + email + '. Check your inbox and spam folder.';
                    successMessage.style.display = 'block';
                    
                    // Clear form
                    document.getElementById('email').value = '';
                    
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send Reset Link';
                })
                .catch((error) => {
                    // Handle errors
                    let errorMsg = 'An error occurred. Please try again.';
                    
                    if (error.code === 'auth/user-not-found') {
                        errorMsg = 'No account found with this email address.';
                    } else if (error.code === 'auth/invalid-email') {
                        errorMsg = 'Invalid email address.';
                    } else if (error.code === 'auth/too-many-requests') {
                        errorMsg = 'Too many requests. Please try again later.';
                    }
                    
                    errorMessage.textContent = errorMsg;
                    errorMessage.style.display = 'block';
                    
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send Reset Link';
                });
        });
    </script>
</body>
</html>

<body>
    <div class="auth-container">
        <!-- Left Side - Hero Section -->
        <div class="auth-hero">
            <div class="auth-hero-content">
                <span class="auth-hero-badge">PASSWORD RECOVERY</span>
                <h1>Reset Your Password</h1>
                <p>Enter your email address and we'll send you a link to reset your password.</p>
            </div>
        </div>
        
        <!-- Right Side - Forgot Password Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <!-- Header -->
                <div class="auth-form-header">
                    <h2>Forgot Password?</h2>
                    <p>No worries, we'll send you reset instructions.</p>
                </div>
                
                <!-- Forgot Password Form -->
                <form action="forgot-password-handler.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label class="form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 4px;">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            Email Address
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="name@example.com"
                            required
                            autofocus
                        >
                    </div>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-error">
                            <?php 
                                if ($_GET['error'] == 'not_found') {
                                    echo 'No account found with that email address';
                                } else if ($_GET['error'] == 'email_failed') {
                                    echo 'Failed to send reset email. Please try again.';
                                }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            <?php if ($_GET['success'] == 'dev'): ?>
                                <strong>Development Mode:</strong> Email sending is not configured.<br><br>
                                <strong>Click this link to reset your password:</strong><br>
                                <a href="reset-password.php?token=<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>" 
                                   style="color: #00D563; word-break: break-all;">
                                    Reset Password Now →
                                </a>
                            <?php else: ?>
                                Reset link sent! Check your email inbox.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Send Reset Link
                    </button>
                </form>
                
                <!-- Footer -->
                <div class="auth-footer">
                    <p><a href="login.php">← Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
