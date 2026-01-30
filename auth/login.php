<?php 
require_once __DIR__ . '/../config.php'; 

// Detect role for styling
$role = $_GET['role'] ?? 'customer';
$theme_class = 'theme-' . $role;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Back - Grab & Go</title>
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/auth.css">
    
    <style>
        /* Shared variables */
        :root {
            --theme-primary: #2874f0; /* Default Flipkart Blue */
        }
        
        .theme-admin { --theme-primary: #1a73e8; } /* Admin Blue */
        .theme-staff { --theme-primary: #00d563; } /* Staff Green */
        .theme-customer { --theme-primary: #2874f0; } /* Flipkart Blue */

        /* Apply theme overrides */
        .auth-hero { background: var(--theme-primary) !important; }
        .btn-primary { background: var(--theme-primary) !important; border-color: var(--theme-primary) !important; }
        .form-input:focus { border-color: var(--theme-primary) !important; box-shadow: 0 0 0 3px rgba(var(--theme-primary), 0.1); }
        .forgot-password { color: var(--theme-primary); }
    </style>
    
    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>
</head>
<body>
    <div class="auth-container <?php echo $theme_class; ?>">
        <!-- Left Side - Hero Section -->
        <div class="auth-hero">
            <div class="auth-hero-content">
                <span class="auth-hero-badge">LIMITED OFFER 25% OFF</span>
                <h1>Skip the Line.<br>Save Your Time.</h1>
                <p>Order online, skip the checkout lines, and manage pre-orders and purchase pickups seamlessly.</p>
                
                <div class="auth-hero-image">
                    <img src="../images/grocery-bag.jpg" alt="Grocery Shopping" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <!-- Header -->
                <div class="auth-form-header">
                    <h2>Welcome Back!</h2>
                    <p>Please enter your credentials to continue.</p>
                </div>
                
                <!-- Login Form -->
                <form id="loginForm" class="auth-form">
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
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 4px;">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            Password
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="loginPassword"
                                name="password" 
                                class="form-input" 
                                placeholder="••••••••"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                    </div>
                    
                    <div id="errorMessage" class="alert alert-error" style="display: none;"></div>
                    <div id="successMessage" class="alert alert-success" style="display: none;"></div>
                    
                    <button type="submit" id="submitBtn" class="btn btn-primary btn-block btn-lg">
                        Sign In
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="auth-divider">
                    <span>OR</span>
                </div>
                
                <!-- Google Sign-In -->
                <button id="googleLoginBtn" onclick="loginWithGoogle()" class="btn btn-secondary btn-block btn-lg" style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continue with Google
                </button>
                
                <!-- Footer -->
                <div class="auth-footer">
                    <p>New to Grab & Go? <a href="register.php">Create an Account</a></p>
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
        
        function togglePassword() {
            const passwordInput = document.getElementById('loginPassword');
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        }
        
        // Handle email/password login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('loginPassword').value;
            const submitBtn = document.getElementById('submitBtn');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            
            // Hide messages
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';
            
            // Disable button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Signing In...';
            
            // Sign in with Firebase
            auth.signInWithEmailAndPassword(email, password)
                .then((userCredential) => {
                    const user = userCredential.user;
                    
                    // Get ID token
                    return user.getIdToken().then((idToken) => {
                        // Send to backend for session creation
                        return fetch('firebase-auth-handler.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                idToken: idToken,
                                email: user.email,
                                displayName: user.displayName,
                                photoURL: user.photoURL,
                                uid: user.uid
                            })
                        });
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect based on role
                        window.location.href = data.redirect;
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch((error) => {
                    let errorMsg = 'Login failed. Please try again.';
                    
                    if (error.code === 'auth/user-not-found') {
                        errorMsg = 'No account found with this email. Please register first.';
                    } else if (error.code === 'auth/wrong-password') {
                        errorMsg = 'Incorrect password. Please try again.';
                    } else if (error.code === 'auth/invalid-email') {
                        errorMsg = 'Invalid email address.';
                    } else if (error.code === 'auth/user-disabled') {
                        errorMsg = 'This account has been disabled.';
                    }
                    
                    errorMessage.textContent = errorMsg;
                    errorMessage.style.display = 'block';
                    
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign In';
                });
        });
        
        function loginWithGoogle() {
            const googleBtn = document.getElementById('googleLoginBtn');
            googleBtn.disabled = true;
            googleBtn.innerHTML = 'Connecting...';

            const provider = new firebase.auth.GoogleAuthProvider();
            provider.addScope('email');
            provider.addScope('profile');
            
            // Sign in with popup
            auth.signInWithPopup(provider)
                .then((result) => {
                    // Get user info
                    const user = result.user;
                    const email = user.email;
                    const displayName = user.displayName;
                    const photoURL = user.photoURL;
                    const uid = user.uid;
                    
                    // Get Firebase ID token
                    return user.getIdToken().then((idToken) => {
                        // Send to backend for session creation
                        return fetch('firebase-auth-handler.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                idToken: idToken,
                                email: email,
                                displayName: displayName,
                                photoURL: photoURL,
                                uid: uid
                            })
                        });
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect based on role
                        window.location.href = data.redirect;
                    } else {
                        alert('Authentication failed: ' + data.message);
                        googleBtn.disabled = false;
                        googleBtn.innerHTML = `
                            <svg width="20" height="20" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Continue with Google`;
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    // alert('Sign-in failed: ' + error.message);
                    
                    // Don't alert for cancelled popups, just reset button
                    if (error.code !== 'auth/popup-closed-by-user' && error.code !== 'auth/cancelled-popup-request') {
                         alert('Sign-in failed: ' + error.message);
                    }
                    
                    googleBtn.disabled = false;
                    googleBtn.innerHTML = `
                            <svg width="20" height="20" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Continue with Google`;
                });
        }
    </script>
</body>
</html>
