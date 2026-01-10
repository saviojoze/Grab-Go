# Grab & Go - Smart Supermarket Online Pre-Order & Pickup System

A comprehensive web-based platform for online grocery shopping with pickup scheduling, built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

✅ **User Authentication**
- Secure registration and login system
- Password hashing with bcrypt
- Session management
- Role-based access (Customer, Staff, Admin)

✅ **Product Catalog**
- Browse products by category
- Filter by price range and dietary needs
- Search functionality
- Product images and details
- Sale badges and pricing

✅ **Shopping Cart**
- Add/remove items
- Quantity management
- Real-time cart updates
- Product recommendations
- Order summary with totals

✅ **Checkout Process**
- Pickup date and time selection
- Contact information form
- Multiple payment methods (Cash, Card, Online)
- Order validation

✅ **Order Management**
- Order confirmation with QR code
- Order history
- Order status tracking
- Receipt download

## Installation

### Prerequisites
- XAMPP (Apache + MySQL + PHP 7.4+)
- Web browser

### Setup Steps

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `grab_and_go`
   - Import the schema:
     ```
     File > Import > Choose file > Select database/schema.sql > Go
     ```

3. **Configure Database Connection**
   - Open `config.php`
   - Verify database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'grab_and_go');
     ```

4. **Access the Application**
   - Open browser and navigate to: `http://localhost/Mini%20Project/`
   - You will be redirected to the login page

## Default Accounts

**Admin Account:**
- Email: `admin@grabandgo.com`
- Password: `admin123`

**Staff Account:**
- Email: `staff@grabandgo.com`
- Password: `admin123`

**Customer Account:**
- Register a new account through the registration page

## Project Structure

```
Mini Project/
├── auth/                   # Authentication pages
│   ├── login.php
│   ├── register.php
│   ├── auth-handler.php
│   └── logout.php
├── products/              # Product catalog
│   └── listing.php
├── cart/                  # Shopping cart
│   ├── cart.php
│   └── cart-api.php
├── checkout/              # Checkout process
│   ├── checkout.php
│   └── process-order.php
├── orders/                # Order management
│   ├── confirmation.php
│   └── my-orders.php
├── admin/                 # Admin dashboard (future)
├── staff/                 # Staff interface (future)
├── css/                   # Stylesheets
│   ├── design-system.css
│   ├── components.css
│   ├── auth.css
│   ├── products.css
│   ├── cart.css
│   ├── checkout.css
│   └── confirmation.css
├── js/                    # JavaScript files
│   └── main.js
├── images/                # Image assets
│   └── products/
├── includes/              # Reusable components
│   ├── header.php
│   └── footer.php
├── database/              # Database files
│   └── schema.sql
├── config.php             # Database configuration
└── index.php              # Entry point
```

## Database Schema

### Tables
- **users** - User accounts (customers, staff, admin)
- **categories** - Product categories
- **products** - Product catalog
- **cart** - Shopping cart items
- **orders** - Order records
- **order_items** - Order line items

## Design System

### Colors
- Primary Green: `#00D563`
- Dark Green: `#1A4D2E`
- Text Primary: `#1A1A1A`
- Background: `#FFFFFF`, `#F5F5F5`

### Typography
- Font Family: Inter (Google Fonts)
- Sizes: 12px - 48px
- Weights: 400, 500, 600, 700

### Components
- Buttons (Primary, Secondary, Icon)
- Form inputs and controls
- Product cards
- Navigation header
- Footer
- Modals and alerts

## User Flows

### Customer Flow
1. Register/Login
2. Browse products with filters
3. Add items to cart
4. Proceed to checkout
5. Select pickup date/time
6. Complete order
7. View confirmation with QR code

### Staff Flow (Future)
1. Login
2. View pending orders
3. Mark orders as ready
4. Scan QR codes for pickup

### Admin Flow (Future)
1. Login
2. Manage products
3. View orders
4. Generate reports

## Technologies Used

- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Server:** Apache (XAMPP)
- **Design:** Custom CSS with design system

## Browser Support

- Chrome (recommended)
- Firefox
- Edge
- Safari

## Future Enhancements

- [ ] QR code generation library integration
- [ ] Admin dashboard for product management
- [ ] Staff interface for order processing
- [ ] Email notifications
- [ ] Payment gateway integration
- [ ] Mobile app
- [ ] Inventory management
- [ ] Analytics and reporting

## Credits

Designed and developed by Savio Joze
Based on Figma design specifications

## License

This project is for educational purposes.
