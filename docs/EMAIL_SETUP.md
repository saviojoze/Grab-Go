# Email Setup Guide for Password Reset

## Overview
The forgot password feature sends verification emails using Gmail's SMTP server. You need to configure your Gmail account to allow the application to send emails.

## Step 1: Update Email Configuration

Open `config.php` and update these lines with your Gmail address:

```php
define('SMTP_USERNAME', 'YOUR_EMAIL@gmail.com'); // Replace with your Gmail
define('SMTP_FROM_EMAIL', 'YOUR_EMAIL@gmail.com'); // Replace with your Gmail
```

**Example:**
```php
define('SMTP_USERNAME', 'saviojoze@gmail.com');
define('SMTP_FROM_EMAIL', 'saviojoze@gmail.com');
```

The app password is already configured: `YOUR_APP_PASSWORD`

## Step 2: Update Database Schema

Run this SQL to add password reset fields to the users table:

1. Go to phpMyAdmin: `http://localhost/phpmyadmin`
2. Select `grab_and_go` database
3. Click **SQL** tab
4. Copy and paste this:

```sql
ALTER TABLE users ADD COLUMN reset_token VARCHAR(100) NULL AFTER password;
ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL AFTER reset_token;
```

5. Click **Go**

Or import the file: `database/add_reset_tokens.sql`

## Step 3: Test the Flow

### 1. Request Password Reset
- Go to: `http://localhost/Mini%20Project/auth/login.php`
- Click **"Forgot Password?"**
- Enter your email address
- Click **"Send Reset Link"**

### 2. Check Your Email
- Open your Gmail inbox
- Look for email from "Grab & Go"
- Subject: "Password Reset Request - Grab & Go"

### 3. Click Reset Link
- Click the button in the email
- Or copy/paste the link into browser
- You'll be taken to the reset password page

### 4. Set New Password
- Enter your new password
- Confirm the password
- Click **"Reset Password"**

### 5. Login
- You'll be redirected to login page
- See success message
- Login with your new password

## How It Works

```
User Flow:
1. User clicks "Forgot Password?"
2. Enters email address
3. System generates unique reset token
4. Token stored in database with 1-hour expiry
5. Email sent with reset link containing token
6. User clicks link in email
7. System verifies token is valid and not expired
8. User enters new password
9. Password updated, token cleared
10. User redirected to login
```

## Security Features

✅ **Token Expiry**: Reset links expire after 1 hour
✅ **One-Time Use**: Token is cleared after password reset
✅ **Secure Hashing**: Passwords are hashed with bcrypt
✅ **Token Validation**: Checks token exists and hasn't expired
✅ **HTTPS Ready**: Works with SSL in production

## Troubleshooting

### Email Not Sending

**Problem**: "Failed to send reset email"

**Solutions**:
1. Make sure you've updated `SMTP_USERNAME` and `SMTP_FROM_EMAIL` in `config.php`
2. Verify the app password is correct: `YOUR_APP_PASSWORD`
3. Check Gmail allows "Less secure app access" (if needed)
4. Make sure XAMPP's PHP mail configuration is correct

### Token Invalid

**Problem**: "Invalid or Expired Link"

**Causes**:
- Link is older than 1 hour
- Token was already used
- Database wasn't updated with reset token fields

**Solution**: Request a new reset link

### Email Goes to Spam

**Problem**: Reset email in spam folder

**Solution**: 
- Check spam/junk folder
- Mark as "Not Spam"
- Add sender to contacts

## Production Deployment

For production, you should:

1. **Use Environment Variables**:
   ```php
   define('SMTP_USERNAME', getenv('SMTP_USERNAME'));
   define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
   ```

2. **Use HTTPS**:
   - Update reset link to use `https://`
   - Ensure SSL certificate is installed

3. **Use Professional Email Service**:
   - SendGrid
   - Mailgun
   - Amazon SES
   - More reliable than Gmail SMTP

4. **Add Rate Limiting**:
   - Limit reset requests per email
   - Prevent spam/abuse

## Files Created

- ✅ `auth/forgot-password.php` - Request reset page
- ✅ `auth/forgot-password-handler.php` - Process reset request
- ✅ `auth/reset-password.php` - New password form
- ✅ `auth/reset-password-handler.php` - Update password
- ✅ `database/add_reset_tokens.sql` - Database migration

## Email Template

The reset email includes:
- Professional HTML design
- Clear call-to-action button
- Plain text link as backup
- 1-hour expiry notice
- Security reminder
- Branded with Grab & Go colors

---

**Your forgot password system is ready!** 🎉

Just update your email in `config.php` and run the database migration.
