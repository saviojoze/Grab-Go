<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../auth/login.php');
}

$action = $_POST['action'] ?? '';

// ============================================
// LOGIN
// ============================================
if ($action === 'login') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        redirect('login.php?error=required');
    }
    
    // Check user credentials
    $stmt = $conn->prepare("SELECT id, password, full_name, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('../admin/dashboard.php');
            } else if ($user['role'] === 'staff') {
                redirect('../staff/orders.php');
            } else {
                redirect('../products/listing.php');
            }
        } else {
            redirect('login.php?error=invalid');
        }
    } else {
        redirect('login.php?error=invalid');
    }
}

// ============================================
// REGISTER
// ============================================
else if ($action === 'register') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (empty($full_name) || empty($email) || empty($password)) {
        redirect('register.php?error=required');
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        redirect('register.php?error=password_mismatch');
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        redirect('register.php?error=email_exists');
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')");
    $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed_password);
    
    if ($stmt->execute()) {
        redirect('login.php?success=registered');
    } else {
        redirect('register.php?error=database');
    }
}

else {
    redirect('../auth/login.php');
}
?>
