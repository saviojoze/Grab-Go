<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Grab &amp; Go</title>
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/auth.css">

    <style>
        :root {
            --theme-primary: #1877F2;
            --theme-green: #00D563;
        }
        .auth-hero {
            background: var(--theme-primary) !important;
            padding: 60px !important;
        }
        .auth-hero-badge {
            background-color: var(--theme-green) !important;
            color: #ffffff !important;
            font-weight: 700;
            border-radius: 50px;
            padding: 6px 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.75rem;
            display: inline-block;
            margin-bottom: 24px;
        }
        .auth-hero h1 {
            font-size: 3.5rem !important;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1rem;
            color: #ffffff;
        }
        .auth-hero p {
            font-size: 1.1rem !important;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
            max-width: 400px;
        }
        .btn-primary {
            background: var(--theme-primary) !important;
            border-color: var(--theme-primary) !important;
            font-weight: 600;
        }
        .form-input:focus {
            border-color: var(--theme-primary) !important;
            box-shadow: 0 0 0 4px rgba(24, 119, 242, 0.15) !important;
        }
        .auth-footer a {
            color: var(--theme-green) !important;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Hero Section -->
        <div class="auth-hero">
            <div class="auth-hero-content">
                <span class="auth-hero-badge">PASSWORD RECOVERY</span>
                <h1>Reset Your<br>Password</h1>
                <p>Enter your email address and we'll send you reset instructions straight to your inbox.</p>
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

                <!-- Forgot Password Form — POSTs to PHP handler (uses PHPMailer + Gmail) -->
                <form action="forgot-password-handler.php" method="POST" class="auth-form" id="forgotForm">
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
                            id="resetEmail"
                            name="email"
                            class="form-input"
                            placeholder="name@example.com"
                            required
                            autofocus
                            value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>"
                        >
                    </div>

                    <!-- Server-side error/success messages -->
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-error">
                            <?php
                                $err = $_GET['error'];
                                if ($err === 'not_found')    echo 'No account found with that email address. Please check and try again.';
                                elseif ($err === 'required') echo 'Please enter your email address.';
                                elseif ($err === 'email_failed') echo 'Failed to send email. Please try again in a moment.';
                                else echo 'An error occurred. Please try again.';
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            <?php if ($_GET['success'] === 'sent'): ?>
                                <strong>Email Sent!</strong> A link has been sent to <strong><?php echo htmlspecialchars($_GET['email'] ?? ''); ?></strong>.<br><br>
                                📧 <strong>Check your Spam/Junk folder!</strong><br>
                                Search for an email from:<br>
                                <code style="font-size:12px; background:rgba(0,0,0,0.05); padding:2px 6px; border-radius:4px; display:inline-block; margin-top:5px;">noreply@grab-and-go-1ed06.firebaseapp.com</code>

                                <div style="margin-top: 25px; border-top: 1px solid #ddd; padding-top: 15px;">
                                    <p style="font-size: 13px; color: #666; margin-bottom: 10px;">Still can't find it?</p>
                                    <button type="button" onclick="document.getElementById('emergency-reset').style.display='block'; this.style.display='none';" 
                                            style="background: none; border: none; color: #1877F2; cursor: pointer; font-size: 13px; text-decoration: underline; padding: 0;">
                                        Show emergency reset link
                                    </button>
                                    <div id="emergency-reset" style="display: none; margin-top: 10px; background: #fffbe6; border: 1px solid #ffe58f; padding: 10px; border-radius: 6px;">
                                        <p style="font-size: 12px; color: #856404; margin-bottom: 8px;"><strong>Emergency Mode:</strong> Use this link to reset directly:</p>
                                        <a href="reset-password.php?token=<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>" 
                                           style="color: #1877F2; font-size: 13px; font-weight: 700;">Reset Password Now →</a>
                                    </div>
                                </div>
                            <?php elseif ($_GET['success'] === 'dev'): ?>
                                <div style="border: 2px dashed #ffc107; padding: 15px; border-radius: 8px; background: #fffdf5;">
                                    <strong style="color: #856404;">⚠️ Email could not be sent.</strong><br>
                                    <p style="font-size: 14px; margin: 10px 0;">SMTP is not configured yet. You can reset your password directly below:</p>
                                    <a href="reset-password.php?token=<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>" 
                                       style="display: block; background: #1877F2; color: #fff; padding: 12px; border-radius: 6px; text-decoration: none; font-weight: 700; text-align: center;">
                                        Reset Password Now →
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" id="submitBtn" class="btn btn-primary btn-block btn-lg"
                            onclick="this.disabled=true; this.textContent='Sending…'; this.form.submit();">
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
