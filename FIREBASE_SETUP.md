# Firebase Authentication Setup Guide

## Overview
This guide will help you set up Firebase Authentication for Google Sign-In in your Grab & Go application.

## Step 1: Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click **"Add project"** or **"Create a project"**
3. Enter project name: `Grab and Go`
4. Click **Continue**
5. Disable Google Analytics (optional for this project)
6. Click **Create project**
7. Wait for project to be created
8. Click **Continue**

## Step 2: Enable Google Sign-In

1. In your Firebase project, click **Authentication** in the left sidebar
2. Click **Get started**
3. Click on **Sign-in method** tab
4. Click on **Google** provider
5. Toggle **Enable** to ON
6. Enter project support email (your email)
7. Click **Save**

## Step 3: Register Your Web App

1. In Firebase Console, click the **gear icon** ⚙️ next to "Project Overview"
2. Click **Project settings**
3. Scroll down to **"Your apps"** section
4. Click the **Web icon** `</>`
5. Enter app nickname: `Grab & Go Web`
6. Check **"Also set up Firebase Hosting"** (optional)
7. Click **Register app**

## Step 4: Copy Firebase Configuration

You'll see a configuration object like this:

```javascript
const firebaseConfig = {
  apiKey: "AIzaSyC...",
  authDomain: "grab-and-go-xxxxx.firebaseapp.com",
  projectId: "grab-and-go-xxxxx",
  storageBucket: "grab-and-go-xxxxx.appspot.com",
  messagingSenderId: "123456789",
  appId: "1:123456789:web:abcdef123456"
};
```

**Copy this entire object!**

## Step 5: Update Your Code

Open `auth/login.php` and find this section:

```javascript
const firebaseConfig = {
    apiKey: "YOUR_FIREBASE_API_KEY",
    authDomain: "YOUR_PROJECT_ID.firebaseapp.com",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_PROJECT_ID.appspot.com",
    messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
    appId: "YOUR_APP_ID"
};
```

Replace it with your actual Firebase config from Step 4.

## Step 6: Add Authorized Domain

1. In Firebase Console, go to **Authentication** > **Settings** tab
2. Scroll to **Authorized domains**
3. Add: `localhost`
4. Click **Add domain**

## Step 7: Test the Integration

1. Go to: `http://localhost/Mini%20Project/auth/login.php`
2. Click **"Continue with Google"**
3. A popup will appear asking you to sign in with Google
4. Select your Google account
5. You'll be automatically logged in!

## How Firebase Authentication Works

### **Frontend Flow:**
```
User clicks "Continue with Google"
    ↓
Firebase SDK opens Google Sign-In popup
    ↓
User selects Google account
    ↓
Firebase returns user info + ID token
    ↓
JavaScript sends ID token to backend
    ↓
Backend verifies token and creates session
    ↓
User redirected to products page
```

### **What's Different from OAuth?**

**Old OAuth Method:**
- Manual token exchange
- Complex callback handling
- More code to maintain
- Need to manage tokens yourself

**New Firebase Method:**
- Firebase handles all OAuth complexity
- Simple popup-based sign-in
- Automatic token management
- Built-in security
- Easy to add more providers (Facebook, Twitter, etc.)

## Benefits of Firebase

✅ **Simpler Code**: Less code to write and maintain
✅ **More Secure**: Firebase handles token validation
✅ **Better UX**: Popup instead of redirect (faster)
✅ **Multi-Provider**: Easy to add Facebook, Twitter, etc.
✅ **Session Management**: Firebase manages user sessions
✅ **Real-time**: Can detect auth state changes
✅ **Free Tier**: Generous free usage limits

## Security Features

🔒 **ID Token Verification**: Tokens are cryptographically signed
🔒 **Automatic Token Refresh**: Firebase handles token expiry
🔒 **HTTPS Only in Production**: Enforced by Firebase
🔒 **Rate Limiting**: Built-in protection against abuse
🔒 **Secure by Default**: Best practices implemented

## Adding to Registration Page

The same setup works for `auth/register.php`:

1. Add Firebase SDK scripts
2. Copy the same `firebaseConfig`
3. Use the same `loginWithGoogle()` function (or rename to `signUpWithGoogle()`)
4. Same backend handler works for both!

## Troubleshooting

### "Firebase is not configured"
- You haven't replaced the placeholder config
- Make sure you copied your actual Firebase config

### "Popup blocked"
- Browser is blocking popups
- Allow popups for localhost
- Or use `signInWithRedirect()` instead of `signInWithPopup()`

### "Unauthorized domain"
- Add `localhost` to Authorized domains in Firebase Console
- Go to Authentication > Settings > Authorized domains

### "Invalid API key"
- Double-check your Firebase config
- Make sure you copied it correctly from Firebase Console

## Production Deployment

For production:

1. **Add Production Domain**:
   - In Firebase Console: Authentication > Settings
   - Add your production domain (e.g., `grabandgo.com`)

2. **Update Config**:
   - Use environment variables for Firebase config
   - Don't commit sensitive keys to Git

3. **Enable Token Verification**:
   - Use Firebase Admin SDK to verify ID tokens
   - Add server-side validation

## Files Modified

- ✅ `auth/login.php` - Added Firebase SDK and sign-in logic
- ✅ `auth/firebase-auth-handler.php` - Backend session handler
- ✅ `auth/register.php` - Same Firebase integration

## Next Steps

After Firebase is set up:

1. Update `auth/register.php` with same Firebase code
2. Test sign-in flow thoroughly
3. Consider adding more auth providers (Facebook, Twitter)
4. Implement sign-out functionality
5. Add profile picture from Google account

---

**Your Firebase Google Sign-In is ready!** 🎉

Just add your Firebase config and test it out!
