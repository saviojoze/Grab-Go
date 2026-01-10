<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Grab & Go</title>
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
                <span class="auth-hero-badge">LIMITED OFFER 25% OFF</span>
                <h1>Join Grab & Go Today</h1>
                <p>Create an account to enjoy seamless online shopping and quick pickup service.</p>
                
                <div class="auth-hero-image">
                    <img src="../images/grocery-bag.jpg" alt="Grocery Shopping" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
        
        <!-- Right Side - Registration Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <!-- Header -->
                <div class="auth-form-header">
                    <h2>Create Account</h2>
                    <p>Fill in your details to get started.</p>
                </div>
                
                <!-- Registration Form -->
                <form id="registerForm" class="auth-form">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input 
                            type="text" 
                            id="full_name"
                            name="full_name" 
                            class="form-input" 
                            placeholder="John Doe"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
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
                        <label class="form-label">Phone Number</label>
                        <input 
                            type="tel" 
                            id="phone"
                            name="phone" 
                            class="form-input" 
                            placeholder="+1 (555) 000-0000"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="••••••••"
                                required
                                minlength="6"
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
                        <label class="form-label">Confirm Password</label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
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
                    
                    <div id="errorMessage" class="alert alert-error" style="display: none;"></div>
                    
                    <button type="submit" id="submitBtn" class="btn btn-primary btn-block btn-lg">
                        Create Account
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="auth-divider">
                    <span>OR</span>
                </div>
                
                <!-- Google Sign-Up -->
                <button onclick="signUpWithGoogle()" class="btn btn-secondary btn-block btn-lg" style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Sign up with Google
                </button>
                
                <!-- Footer -->
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Firebase Configuration
        const firebaseConfig = {
            apiKey: "AIzaSyAxNsQWe2L5bhMpr5VfEJhctgciZE7ARso",
            authDomain: "grab-and-go-1ed06.firebaseapp.com",
            projectId: "grab-and-go-1ed06",
            storageBucket: "grab-and-go-1ed06.firebasestorage.app",
            messagingSenderId: "105107871688",
            appId: "1:105107871688:web:081dbc2bd5ae78088b92fd"
        };
        
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();
        
        function togglePassword(id) {
            const passwordInput = document.getElementById(id);
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        }
        
        // Handle email/password registration
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fullName = document.getElementById('full_name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submitBtn');
            const errorMessage = document.getElementById('errorMessage');
            
            // Hide error
            errorMessage.style.display = 'none';
            
            // Validate passwords match
            if (password !== confirmPassword) {
                errorMessage.textContent = 'Passwords do not match';
                errorMessage.style.display = 'block';
                return;
            }
            
            // Disable button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating Account...';
            
            // Create user in Firebase
            auth.createUserWithEmailAndPassword(email, password)
                .then((userCredential) => {
                    const user = userCredential.user;
                    
                    // Update profile with display name
                    return user.updateProfile({
                        displayName: fullName
                    }).then(() => {
                        // Get ID token
                        return user.getIdToken();
                    });
                })
                .then((idToken) => {
                    // Send to backend to create MySQL record
                    return fetch('firebase-auth-handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            idToken: idToken,
                            email: document.getElementById('email').value,
                            displayName: document.getElementById('full_name').value,
                            photoURL: null,
                            uid: auth.currentUser.uid,
                            phone: phone
                        })
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to products page
                        window.location.href = data.redirect;
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch((error) => {
                    let errorMsg = 'Registration failed. Please try again.';
                    
                    if (error.code === 'auth/email-already-in-use') {
                        errorMsg = 'Email already registered. Please login instead.';
                    } else if (error.code === 'auth/invalid-email') {
                        errorMsg = 'Invalid email address.';
                    } else if (error.code === 'auth/weak-password') {
                        errorMsg = 'Password should be at least 6 characters.';
                    }
                    
                    errorMessage.textContent = errorMsg;
                    errorMessage.style.display = 'block';
                    
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Account';
                });
        });
        
        // Handle Google Sign-Up
        function signUpWithGoogle() {
            const provider = new firebase.auth.GoogleAuthProvider();
            provider.addScope('email');
            provider.addScope('profile');
            
            auth.signInWithPopup(provider)
                .then((result) => {
                    const user = result.user;
                    const email = user.email;
                    const displayName = user.displayName;
                    const photoURL = user.photoURL;
                    const uid = user.uid;
                    
                    return user.getIdToken().then((idToken) => {
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
                        window.location.href = data.redirect;
                    } else {
                        alert('Registration failed: ' + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Sign-up failed: ' + error.message);
                });
        }
    </script>
</body>
</html>
