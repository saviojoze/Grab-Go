# 🚀 Railway Deployment Guide

Follow these steps to deploy your "Grab & Go" project to Railway.

## 1. Prepare your Repository
Ensure your code is pushed to a GitHub repository.

## 2. Create a Railway Project
1. Log in to [Railway.app](https://railway.app/).
2. Click **"New Project"**.
3. Select **"Deploy from GitHub repo"** and choose your repository.

## 3. Add a MySQL Database
1. In your Railway project, click **"Add Service"**.
2. Select **"Database"** → **"MySQL"**.
3. Railway will provision a new MySQL instance.

## 4. Configure Environment Variables
1. Go to your Web Service (the one deployed from GitHub).
2. Click the **"Variables"** tab.
3. Add the following variables (map them to the MySQL service variables):
   - `DB_HOST`: `${{MySQL.MYSQLHOST}}`
   - `DB_USER`: `${{MySQL.MYSQLUSER}}`
   - `DB_PASS`: `${{MySQL.MYSQLPASSWORD}}`
   - `DB_NAME`: `${{MySQL.MYSQLDATABASE}}`
   - `DB_PORT`: `${{MySQL.MYSQLPORT}}`
4. Also add any other required variables from your `.env.example`:
   - `FIREBASE_API_KEY`
   - `GOOGLE_CLIENT_ID`
   - `SMTP_HOST`, etc.

## 5. Import Database Schema
1. Once MySQL is ready, go to the **"Variables"** for the MySQL service and copy the **"TCP Connection Command"** (or use a tool like TablePlus/DBeaver).
2. Or use the Railway CLI:
   ```bash
   railway connect mysql
   # Then paste the content of database/schema.sql
   ```
3. Alternatively, you can use the `database/schema.sql` content and run it in the Railway SQL editor.

## 6. Access your Site
Railway will provide a public URL (e.g., `https://grab-and-go-production.up.railway.app`). You may need to generate a domain in the **"Settings"** tab of your web service.

---
**Note**: The provided `Dockerfile` will automatically handle the PHP and Apache setup.
