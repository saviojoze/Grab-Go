# GRAB & GO - QUICK SETUP GUIDE

## Step 1: Start XAMPP

1. Open **XAMPP Control Panel**
2. Click **Start** for **Apache**
3. Click **Start** for **MySQL**
4. Wait for both to show green "Running" status

## Step 2: Create Database

1. Open your browser
2. Go to: `http://localhost/phpmyadmin`
3. Click **New** in the left sidebar
4. Database name: `grab_and_go`
5. Click **Create**

## Step 3: Import Database Schema

1. Click on `grab_and_go` database in the left sidebar
2. Click **Import** tab at the top
3. Click **Choose File**
4. Navigate to: `C:\xampp\htdocs\Mini Project\database\schema.sql`
5. Click **Go** at the bottom
6. Wait for "Import has been successfully finished" message

## Step 4: Access the Application

1. Open your browser
2. Go to: `http://localhost/Mini%20Project/`
3. You will be redirected to the login page

## Step 5: Test the System

### Option A: Create New Account
1. Click **"Create an Account"**
2. Fill in your details
3. Click **"Create Account"**
4. Login with your credentials

### Option B: Use Admin Account
- **Email:** `admin@grabandgo.com`
- **Password:** `admin123`

## Step 6: Test User Flow

1. **Browse Products**
   - Use filters on the left sidebar
   - Try category checkboxes
   - Adjust price range slider
   - Select dietary needs

2. **Add to Cart**
   - Click the green + button on any product
   - Watch for "Item added to cart!" notification
   - See cart badge update in header

3. **View Cart**
   - Click cart icon in header
   - Update quantities with +/- buttons
   - Try removing items
   - Add recommended products

4. **Checkout**
   - Click "Proceed to checkout"
   - Select pickup date (today or future)
   - Choose time slot
   - Verify contact info
   - Select payment method
   - Click "Place an Order"

5. **View Confirmation**
   - See order number
   - View QR code placeholder
   - Check pickup date/time
   - Review order summary

## Troubleshooting

### Database Connection Error
- Check XAMPP MySQL is running
- Verify database name is `grab_and_go`
- Check `config.php` settings

### Page Not Found
- Verify URL: `http://localhost/Mini%20Project/`
- Check Apache is running in XAMPP
- Ensure files are in `C:\xampp\htdocs\Mini Project\`

### Login Not Working
- Make sure database is imported
- Check if user exists in database
- Try creating new account

### Images Not Showing
- Product images are in `images/products/` folder
- Placeholder will show if image missing
- This is normal for demo

## Default Accounts

**Admin:**
- Email: `admin@grabandgo.com`
- Password: `admin123`

**Staff:**
- Email: `staff@grabandgo.com`
- Password: `admin123`

## Next Steps

After testing the basic flow:
- Create multiple orders
- View order history at "My Orders"
- Test different payment methods
- Try different pickup times
- Test with multiple products

## Need Help?

Check the full documentation in `README.md`

---

**Enjoy your Grab & Go experience!** 🛒✨
