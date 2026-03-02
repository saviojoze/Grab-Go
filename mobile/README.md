# Grab & Go - Mobile App (React Native)

This directory contains the mobile application for the Grab & Go Smart Supermarket system.

## Setup Instructions

### 1. Prerequisite
- Make sure you have **Node.js** installed on your machine.
- Your computer and mobile device (if testing on a physical phone) must be on the **same Wi-Fi network**.

### 2. Install Dependencies
Navigate to this folder and run:
```bash
npm install
```

### 3. API Configuration
The app is configured to talk to your backend using your local IP address: `192.168.37.21`.
If your IP address changes, you must update it in:
`mobile/src/services/api.js`

### 4. Run the App
Start the Expo development server:
```bash
npx expo start
```

- **For Android**: Press `a` or scan the QR code using the **Expo Go** app.
- **For iOS**: Scan the QR code with your camera (requires Expo Go app).
- **For Web**: Press `w`.

## Features
- **Modern UI**: Clean and vibrant design matching the Grab & Go brand.
- **Dynamic Catalog**: Browse products by category with real-time data from MySQL.
- **Unified Cart**: Synchronized shopping cart between mobile and web.
- **Order History**: Track your pickup orders on the go.
- **Fast Search**: Quickly find the groceries you need.

## Troubleshooting

### 'npm' is not recognized
If you see the error `'npm' is not recognized`, it means Node.js is installed but not in your PATH. 

**For Command Prompt (CMD):**
```cmd
set PATH=%PATH%;C:\Program Files\nodejs
```

**For PowerShell:**
```powershell
$env:PATH += ";C:\Program Files\nodejs"
```

Then you can use `npm` and `npx` as usual. Alternatively, you can always use the full paths:
- **Install**: `& "C:\Program Files\nodejs\npm.cmd" install`
- **Start**: `& "C:\Program Files\nodejs\npx.cmd" expo start`
