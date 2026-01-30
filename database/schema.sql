-- ============================================
-- GRAB & GO - Database Schema
-- Smart Supermarket Online Pre-Order & Pickup System
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS grab_and_go;
USE grab_and_go;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    profile_picture VARCHAR(255),
    role ENUM('customer', 'admin', 'staff') DEFAULT 'customer',
    is_blocked TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Categories Table
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Products Table
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    stock INT DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'units',
    image_url VARCHAR(255),
    description TEXT,
    dietary_tags VARCHAR(255),
    is_sale BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_price (price),
    INDEX idx_sale (is_sale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Cart Table
-- ============================================
CREATE TABLE IF NOT EXISTS cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Orders Table
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    pickup_time TIME NOT NULL,
    contact_name VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(20),
    payment_method ENUM('cash', 'card', 'online') DEFAULT 'cash',
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    delivery_fee DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    qr_code_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_order_number (order_number),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_pickup_date (pickup_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Order Items Table
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Insert Default Admin User
-- Password: admin123 (hashed)
-- ============================================
INSERT INTO users (email, password, full_name, role) VALUES
('admin@grabandgo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin'),
('staff@grabandgo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff User', 'staff');

-- ============================================
-- Insert Categories
-- ============================================
INSERT INTO categories (name, icon, display_order) VALUES
('Fresh Produce', '🥬', 1),
('Fruits', '🍎', 2),
('Vegetables', '🥕', 3),
('Dairy', '🥛', 4),
('Bakery', '🍞', 5),
('Meat & Seafood', '🥩', 6),
('Beverages', '🥤', 7),
('Snacks', '🍿', 8);

-- ============================================
-- Insert Sample Products
-- ============================================
INSERT INTO products (name, category_id, price, original_price, stock, unit, image_url, description, dietary_tags, is_sale) VALUES
-- Fresh Produce
('Green Cabbage', 1, 5.29, NULL, 50, 'kg', 'images/products/cabbage.jpg', 'Fresh organic green cabbage', 'Vegan,Organic', 0),
('Organic Bananas', 2, 9.60, NULL, 100, 'kg', 'images/products/bananas.jpg', 'Fresh organic bananas', 'Vegan,Organic', 0),
('Vine Ripe Tomatoes', 3, 2.49, NULL, 75, 'kg', 'images/products/tomatoes.jpg', 'Fresh vine ripe tomatoes', 'Vegan,Organic', 0),
('Hass Avocado', 1, 5.90, NULL, 60, 'kg', 'images/products/avocado.jpg', 'Fresh Hass avocados', 'Vegan,Organic', 0),
('Large Lemons', 2, 0.79, NULL, 80, 'kg', 'images/products/lemons.jpg', 'Fresh large lemons', 'Vegan,Organic', 0),
('Gala Apples', 2, 1.99, 2.49, 90, 'kg', 'images/products/apples.jpg', 'Fresh Gala apples', 'Vegan,Organic', 1),

-- Dairy
('Fresh Milk 1L', 4, 2.99, NULL, 40, 'L', 'images/products/milk.jpg', 'Fresh whole milk', 'Vegetarian', 0),
('Cheddar Cheese', 4, 4.99, NULL, 30, 'units', 'images/products/cheese.jpg', 'Aged cheddar cheese', 'Vegetarian', 0),

-- Bakery
('Whole Wheat Bread', 5, 3.49, NULL, 25, 'loaf', 'images/products/bread.jpg', 'Fresh whole wheat bread', 'Vegetarian', 0),
('Croissants 6pk', 5, 5.99, NULL, 20, 'pack', 'images/products/croissants.jpg', 'Butter croissants', 'Vegetarian', 0),

-- Beverages
('Orange Juice 1L', 7, 3.99, NULL, 50, 'L', 'images/products/orange-juice.jpg', 'Fresh orange juice', 'Vegan', 0),
('Sparkling Water 6pk', 7, 4.49, NULL, 60, 'pack', 'images/products/water.jpg', 'Sparkling mineral water', 'Vegan', 0);
