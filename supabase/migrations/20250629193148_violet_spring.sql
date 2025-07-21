-- Taste Bud Hub Database Setup
-- Run this directly in phpMyAdmin SQL tab

-- Create database
CREATE DATABASE IF NOT EXISTS taste_bud_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taste_bud_hub;

-- Drop existing tables if they exist (in correct order due to foreign keys)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS foods;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Create admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB;

-- Create foods table
CREATE TABLE foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    rating FLOAT DEFAULT 4.5 CHECK (rating >= 0 AND rating <= 5),
    tags VARCHAR(255) DEFAULT NULL,
    category VARCHAR(50) NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_rating (rating),
    INDEX idx_available (is_available)
) ENGINE=InnoDB;

-- Create orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Preparing', 'Delivered') DEFAULT 'Pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB;

-- Create order_items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    item_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_food_id (food_id)
) ENGINE=InnoDB;

-- Create contacts table
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create reviews table (optional)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE,
    INDEX idx_food_id (food_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB;

-- Insert default admin user
INSERT INTO admins (username, password, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@tastebudhub.com');
-- Password is: password

-- Insert sample food categories and items
INSERT INTO foods (name, image_url, description, price, rating, tags, category) VALUES
('Margherita Pizza', 'https://images.pexels.com/photos/315755/pexels-photo-315755.jpeg?auto=compress&cs=tinysrgb&w=400', 'Classic pizza with fresh tomatoes, mozzarella cheese, and basil', 12.99, 4.8, 'vegetarian,italian,classic', 'Pizza'),
('Beef Burger Deluxe', 'https://images.pexels.com/photos/1639557/pexels-photo-1639557.jpeg?auto=compress&cs=tinysrgb&w=400', 'Juicy beef patty with lettuce, tomato, and special sauce', 15.99, 4.7, 'beef,american,classic', 'Burger'),
('Caesar Salad', 'https://images.pexels.com/photos/257816/pexels-photo-257816.jpeg?auto=compress&cs=tinysrgb&w=400', 'Fresh romaine lettuce with parmesan cheese and croutons', 9.99, 4.5, 'vegetarian,healthy,fresh', 'Salad'),
('Chocolate Cake', 'https://images.pexels.com/photos/291528/pexels-photo-291528.jpeg?auto=compress&cs=tinysrgb&w=400', 'Rich chocolate cake with smooth frosting', 6.99, 4.9, 'dessert,chocolate,sweet', 'Cake'),
('Salmon Sushi', 'https://images.pexels.com/photos/357756/pexels-photo-357756.jpeg?auto=compress&cs=tinysrgb&w=400', 'Fresh salmon sushi with perfect rice', 18.99, 4.8, 'fish,japanese,healthy', 'Sushi'),
('Chicken Pasta', 'https://images.pexels.com/photos/1279330/pexels-photo-1279330.jpeg?auto=compress&cs=tinysrgb&w=400', 'Creamy pasta with grilled chicken and herbs', 14.99, 4.6, 'chicken,italian,creamy', 'Pasta'),
('Vegetable Stir Fry', 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg?auto=compress&cs=tinysrgb&w=400', 'Fresh vegetables stir-fried with aromatic spices', 11.99, 4.4, 'vegetarian,healthy,asian', 'Asian'),
('Fish Tacos', 'https://images.pexels.com/photos/461198/pexels-photo-461198.jpeg?auto=compress&cs=tinysrgb&w=400', 'Grilled fish tacos with fresh salsa', 13.99, 4.7, 'fish,mexican,fresh', 'Mexican'),
('Vanilla Ice Cream', 'https://images.pexels.com/photos/1352278/pexels-photo-1352278.jpeg?auto=compress&cs=tinysrgb&w=400', 'Creamy vanilla ice cream with toppings', 4.99, 4.3, 'dessert,cold,sweet', 'Dessert'),
('BBQ Ribs', 'https://images.pexels.com/photos/410648/pexels-photo-410648.jpeg?auto=compress&cs=tinysrgb&w=400', 'Smoky BBQ ribs with special sauce', 19.99, 4.8, 'pork,bbq,american', 'BBQ'),
('Pepperoni Pizza', 'https://images.pexels.com/photos/2147491/pexels-photo-2147491.jpeg?auto=compress&cs=tinysrgb&w=400', 'Classic pepperoni pizza with mozzarella', 14.99, 4.6, 'pepperoni,italian,classic', 'Pizza'),
('Greek Salad', 'https://images.pexels.com/photos/1211887/pexels-photo-1211887.jpeg?auto=compress&cs=tinysrgb&w=400', 'Traditional Greek salad with feta cheese', 10.99, 4.4, 'vegetarian,mediterranean,healthy', 'Salad');

-- Insert sample users
INSERT INTO users (name, email, password, phone, address) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890', '123 Main St, City, State'),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567891', '456 Oak Ave, City, State'),
('Mike Johnson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567892', '789 Pine Rd, City, State');
-- All passwords are: password

-- Insert sample orders
INSERT INTO orders (user_id, customer_name, customer_phone, customer_address, total_price, status) VALUES
(1, 'John Doe', '+1234567890', '123 Main St, City, State', 32.05, 'Delivered'),
(2, 'Jane Smith', '+1234567891', '456 Oak Ave, City, State', 24.50, 'Preparing');

-- Insert sample order items
INSERT INTO order_items (order_id, food_id, quantity, item_price) VALUES
(1, 1, 1, 12.99),
(1, 2, 1, 15.99),
(2, 5, 1, 18.99);

-- Show completion message
SELECT 'Database setup completed successfully!' as Status;
SELECT 'Admin login: username = admin, password = password' as Credentials;
SELECT 'Sample users: password = password for all' as UserInfo;