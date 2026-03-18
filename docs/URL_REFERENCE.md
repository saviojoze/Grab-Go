# Grab & Go - URL Reference Guide

## Main Application URLs

### Public Access
- **Home/Login**: `http://localhost/Mini%20Project/` or `http://localhost/Mini%20Project/index.php`
- **Login Page**: `http://localhost/Mini%20Project/auth/login.php`
- **Register**: `http://localhost/Mini%20Project/auth/register.php`
- **Forgot Password**: `http://localhost/Mini%20Project/auth/forgot-password.php`

### Customer Area (After Login)
- **Product Listing**: `http://localhost/Mini%20Project/products/listing.php`
- **Shopping Cart**: `http://localhost/Mini%20Project/cart/cart.php`
- **Checkout**: `http://localhost/Mini%20Project/checkout/checkout.php`
- **My Orders**: `http://localhost/Mini%20Project/orders/my-orders.php`

### Admin Area (Admin Login Required)
- **Admin Dashboard**: `http://localhost/Mini%20Project/admin/index.php`
- **Products Management**: `http://localhost/Mini%20Project/admin/products.php`
- **Add Product**: `http://localhost/Mini%20Project/admin/add_product.php`
- **Edit Product**: `http://localhost/Mini%20Project/admin/edit_product.php?id=1`
- **Categories**: `http://localhost/Mini%20Project/admin/categories.php`
- **Orders Management**: `http://localhost/Mini%20Project/admin/orders.php`
- **Reports & Analytics**: `http://localhost/Mini%20Project/admin/reports.php`

## Common 404 Errors & Solutions

### Error: "Not Found" when accessing admin
**Problem**: Trying to access `/admin/` without `index.php`  
**Solution**: Use `http://localhost/Mini%20Project/admin/index.php`

### Error: "Not Found" after login
**Problem**: Redirect path might be incorrect  
**Solution**: Check that you're logging in with correct credentials and role

### Error: Space in URL (%20)
**Problem**: Folder name has spaces  
**Solution**: Use `%20` in place of spaces or rename folder to `Mini-Project`

## Quick Access Links

### For Testing
```
Customer Login: http://localhost/Mini%20Project/auth/login.php
Admin Login: http://localhost/Mini%20Project/auth/login.php
  (Use: admin@grabandgo.com / admin123)
```

### After Login Redirects
- **Customer** → `products/listing.php`
- **Admin** → `admin/index.php`
- **Staff** → `staff/orders.php`

## Troubleshooting

If you get 404 errors:
1. Check XAMPP is running (Apache + MySQL)
2. Verify the URL has `%20` instead of spaces
3. Make sure you're accessing the correct file path
4. Check that the file actually exists in the directory
5. Clear browser cache and try again

## File Structure Reference
```
Mini Project/
├── index.php (redirects to login or products)
├── auth/
│   ├── login.php
│   ├── register.php
│   └── forgot-password.php
├── admin/
│   ├── index.php (dashboard)
│   ├── products.php
│   ├── orders.php
│   └── reports.php
├── products/
│   └── listing.php
├── cart/
│   └── cart.php
└── orders/
    └── my-orders.php
```
