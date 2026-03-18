# Admin Login Troubleshooting Guide

## Issue
Admin login is failing with "Login failed. Please try again." error.

## Root Cause
The system uses **Firebase Authentication** for login. The admin account needs to exist in **both**:
1. Firebase Authentication (for login)
2. MySQL Database (for role and user data)

## Solution Options

### Option 1: Create Admin Account in Firebase (Recommended)

Since you're using Firebase Authentication, you need to create the admin user in Firebase:

1. **Go to Firebase Console**: https://console.firebase.google.com/
2. **Select your project**: "grab-and-go-1ed06"
3. **Navigate to Authentication** → Users
4. **Add User** with:
   - Email: `admin@grabandgo.com`
   - Password: `admin123` (or your preferred password)

### Option 2: Use Existing Account and Update Role

If you already have a Firebase account, you can make it an admin:

1. Log in with your existing Firebase account
2. Find your user ID in the database
3. Run this SQL query:
   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
   ```

### Option 3: Add PHP Fallback Authentication

Add a fallback to allow direct PHP authentication for admin users (bypasses Firebase).

## Current Setup

- **Database Password Hash**: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`
- **Plaintext Password**: `admin123`
- **Email**: `admin@grabandgo.com`

## Recommended Action

**Create the admin account in Firebase** with the same credentials that exist in your database:
- Email: `admin@grabandgo.com`  
- Password: `admin123`

This will allow the Firebase authentication to work properly and redirect you to the admin dashboard.

## Alternative: Hybrid Authentication

I can create a hybrid system that:
1. First tries Firebase authentication
2. Falls back to PHP/MySQL authentication if Firebase fails
3. This allows admin login even without Firebase account

Would you like me to implement the hybrid authentication system?
