# Google OAuth Setup Guide for Grab & Go

## Overview
This guide will help you set up Google OAuth authentication for your Grab & Go application, allowing users to sign in and sign up with their Google accounts.

## Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click **"Select a project"** at the top
3. Click **"New Project"**
4. Enter project name: `Grab and Go`
5. Click **"Create"**

## Step 2: Enable Google+ API

1. In your project, go to **"APIs & Services"** > **"Library"**
2. Search for **"Google+ API"**
3. Click on it and click **"Enable"**

## Step 3: Create OAuth 2.0 Credentials

1. Go to **"APIs & Services"** > **"Credentials"**
2. Click **"Create Credentials"** > **"OAuth client ID"**
3. If prompted, configure the OAuth consent screen:
   - User Type: **External**
   - App name: `Grab & Go`
   - User support email: Your email
   - Developer contact: Your email
   - Click **"Save and Continue"**
   - Scopes: Click **"Save and Continue"** (default scopes are fine)
   - Test users: Add your email if needed
   - Click **"Save and Continue"**

4. Back to Create OAuth client ID:
   - Application type: **Web application**
   - Name: `Grab & Go Web Client`
   
5. **Authorized JavaScript origins:**
   ```
   http://localhost
   ```

6. **Authorized redirect URIs:**
   ```
   http://localhost/Mini%20Project/auth/google-callback.php
   ```

7. Click **"Create"**

## Step 4: Copy Your Credentials

After creating, you'll see a popup with:
- **Client ID** (looks like: `123456789-abcdefg.apps.googleusercontent.com`)
- **Client Secret** (looks like: `GOCSPX-abc123def456`)

**IMPORTANT:** Copy both of these!

## Step 5: Update config.php

1. Open `C:\xampp\htdocs\Mini Project\config.php`
2. Find these lines:
   ```php
   define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
   define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
   ```

3. Replace with your actual credentials:
   ```php
   define('GOOGLE_CLIENT_ID', '123456789-abcdefg.apps.googleusercontent.com');
   define('GOOGLE_CLIENT_SECRET', 'GOCSPX-abc123def456');
   ```

4. Save the file

## Step 6: Test Google Sign-In

1. Go to `http://localhost/Mini%20Project/auth/login.php`
2. Click **"Continue with Google"** button
3. You should be redirected to Google's sign-in page
4. Sign in with your Google account
5. Grant permissions
6. You'll be redirected back and logged in automatically!

## How It Works

### Login Flow:
1. User clicks "Continue with Google"
2. Redirected to Google's OAuth consent screen
3. User approves access
4. Google redirects to `google-callback.php` with auth code
5. Backend exchanges code for access token
6. Backend fetches user info (email, name)
7. If user exists: Log them in
8. If new user: Create account and log them in

### Registration Flow:
1. User clicks "Sign up with Google"
2. Same OAuth flow as login
3. New account is automatically created with Google info
4. User is logged in immediately

## Troubleshooting

### "Google OAuth is not configured" Alert
- You haven't updated `config.php` with your Client ID
- Make sure you replaced `YOUR_GOOGLE_CLIENT_ID_HERE`

### "Redirect URI mismatch" Error
- Check that your redirect URI in Google Console exactly matches:
  ```
  http://localhost/Mini%20Project/auth/google-callback.php
  ```
- Note: URL encoding (`%20` for space) matters!

### "Access blocked: This app's request is invalid"
- Make sure you've enabled Google+ API
- Check OAuth consent screen is configured

### cURL Error
- Make sure PHP cURL extension is enabled in XAMPP
- Check `php.ini` and uncomment: `extension=curl`

## Security Notes

⚠️ **Important for Production:**

1. **Never commit credentials to Git:**
   - Add `config.php` to `.gitignore`
   - Use environment variables instead

2. **Use HTTPS in production:**
   - Change redirect URI to `https://yourdomain.com/...`
   - Update in both Google Console and `config.php`

3. **Restrict API key:**
   - In Google Console, add application restrictions
   - Limit to your domain only

## Features Enabled

✅ **Login Page:**
- "Continue with Google" button
- Automatic account creation for new users
- Seamless login for existing users

✅ **Registration Page:**
- "Sign up with Google" button
- Instant account creation
- No password required

✅ **Backend:**
- OAuth 2.0 flow implementation
- Secure token exchange
- User info retrieval
- Automatic account creation/login

## Next Steps

After setting up Google OAuth:
1. Test with multiple Google accounts
2. Verify new users are created in database
3. Check existing users can log in
4. Test on different browsers

## Support

If you encounter issues:
1. Check browser console for errors
2. Verify all credentials are correct
3. Ensure redirect URI matches exactly
4. Check PHP error logs in XAMPP

---

**Congratulations!** Your users can now sign in with Google! 🎉
