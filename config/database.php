<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'taste_bud_hub');

// Create connection using mysqli (raw SQL only)
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset for proper handling of special characters
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Test connection function
function testConnection() {
    $conn = getConnection();
    if ($conn) {
        echo "✅ Database connection successful!<br>";
        echo "Database: " . DB_NAME . "<br>";
        echo "Host: " . DB_HOST . "<br>";
        $conn->close();
        return true;
    }
    return false;
}

// Get user by ID
function getUserById($user_id) {
    $conn = getConnection();
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

// Update user profile picture
function updateUserProfilePicture($user_id, $profile_pic) {
    $conn = getConnection();
    $sql = "UPDATE users SET profile_pic = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $profile_pic, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

// Get user orders
function getUserOrders($user_id, $limit = null) {
    $conn = getConnection();
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
    }
    $stmt = $conn->prepare($sql);
    if ($limit) {
        $stmt->bind_param("ii", $user_id, $limit);
    } else {
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $orders;
}

// Get ongoing orders (not delivered)
function getOngoingOrders($user_id) {
    $conn = getConnection();
    $sql = "SELECT * FROM orders WHERE user_id = ? AND status != 'Delivered' ORDER BY order_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $orders;
}

// Get order history (delivered orders)
function getOrderHistory($user_id) {
    $conn = getConnection();
    $sql = "SELECT * FROM orders WHERE user_id = ? AND status = 'Delivered' ORDER BY order_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $orders;
}

// Get all foods (excluding removed items)
function getAllFoods() {
    $conn = getConnection();
    $sql = "SELECT * FROM foods 
            WHERE is_available = 1 
            AND name NOT IN ('BBQ Ribs', 'Beef Burger Deluxe', 'Pepperoni Pizza', 'Chicken Pizza')
            ORDER BY rating DESC";
    $result = $conn->query($sql);
    $foods = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $foods[] = $row;
        }
    }
    
    $conn->close();
    return $foods;
}

// Get foods by category (excluding removed items)
function getFoodsByCategory($category) {
    $conn = getConnection();
    $sql = "SELECT * FROM foods 
            WHERE category = ? 
            AND is_available = 1 
            AND name NOT IN ('BBQ Ribs', 'Beef Burger Deluxe', 'Pepperoni Pizza', 'Chicken Pizza')
            ORDER BY rating DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $foods = [];
    
    while($row = $result->fetch_assoc()) {
        $foods[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $foods;
}

// Search foods (excluding removed items)
function searchFoods($search_term) {
    $conn = getConnection();
    $search_param = "%$search_term%";
    $sql = "SELECT * FROM foods 
            WHERE (name LIKE ? OR description LIKE ? OR tags LIKE ?) 
            AND is_available = 1 
            AND name NOT IN ('BBQ Ribs', 'Beef Burger Deluxe', 'Pepperoni Pizza', 'Chicken Pizza')
            ORDER BY rating DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $foods = [];
    
    while($row = $result->fetch_assoc()) {
        $foods[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $foods;
}

// Get featured foods for homepage (excluding removed items)
function getFeaturedFoods($limit = 6) {
    $conn = getConnection();
    $sql = "SELECT * FROM foods 
            WHERE is_available = 1 
            AND name NOT IN ('BBQ Ribs', 'Beef Burger Deluxe', 'Pepperoni Pizza', 'Chicken Pizza')
            ORDER BY rating DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $foods = [];
    
    while($row = $result->fetch_assoc()) {
        $foods[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $foods;
}

// Create new order with payment method
function createOrder($user_id, $customer_name, $customer_phone, $customer_address, $total_price, $payment_method = null) {
    $conn = getConnection();
    $sql = "INSERT INTO orders (user_id, customer_name, customer_phone, customer_address, total_price, payment_method) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssds", $user_id, $customer_name, $customer_phone, $customer_address, $total_price, $payment_method);
    
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        return $order_id;
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

// Add order item
function addOrderItem($order_id, $food_id, $quantity, $item_price) {
    $conn = getConnection();
    $sql = "INSERT INTO order_items (order_id, food_id, quantity, item_price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiid", $order_id, $food_id, $quantity, $item_price);
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

// Get order with items
function getOrderWithItems($order_id) {
    $conn = getConnection();
    
    // Get order details
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$order) {
        $conn->close();
        return null;
    }
    
    // Get order items
    $sql = "SELECT oi.*, f.name, f.image_url 
            FROM order_items oi 
            JOIN foods f ON oi.food_id = f.id 
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    
    while($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    $order['items'] = $items;
    $stmt->close();
    $conn->close();
    return $order;
}

// Update order status
function updateOrderStatus($order_id, $status) {
    $conn = getConnection();
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

// Get all categories (excluding categories with only removed items)
function getCategories() {
    $conn = getConnection();
    $sql = "SELECT DISTINCT category FROM foods 
            WHERE is_available = 1 
            AND name NOT IN ('BBQ Ribs', 'Beef Burger Deluxe', 'Pepperoni Pizza', 'Chicken Pizza')
            ORDER BY category";
    $result = $conn->query($sql);
    $categories = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
    }
    
    $conn->close();
    return $categories;
}

// Verify admin login
function verifyAdmin($username, $password) {
    $conn = getConnection();
    $sql = "SELECT id, username, password FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($admin = $result->fetch_assoc()) {
        if (password_verify($password, $admin['password'])) {
            $stmt->close();
            $conn->close();
            return $admin;
        }
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

// Verify user login
function verifyUser($email, $password) {
    $conn = getConnection();
    $sql = "SELECT id, name, email, password, profile_pic FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $stmt->close();
            $conn->close();
            return $user;
        }
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

// Create new user
function createUser($name, $email, $password) {
    $conn = getConnection();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $hashed_password);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        return $user_id;
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

// Save contact message
function saveContactMessage($name, $email, $message) {
    $conn = getConnection();
    $sql = "INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $message);
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

// Initialize database and create tables if they don't exist
function initializeDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->query($sql);
    
    $conn->select_db(DB_NAME);
    
    // Create tables if they don't exist
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            address TEXT DEFAULT NULL,
            profile_pic VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username)
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS foods (
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
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            customer_name VARCHAR(100) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_address TEXT NOT NULL,
            order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            total_price DECIMAL(10,2) NOT NULL,
            status ENUM('Pending', 'Preparing', 'Delivered') DEFAULT 'Pending',
            payment_method VARCHAR(50) DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status),
            INDEX idx_order_date (order_date)
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            food_id INT NOT NULL,
            quantity INT NOT NULL CHECK (quantity > 0),
            item_price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE RESTRICT,
            INDEX idx_order_id (order_id),
            INDEX idx_food_id (food_id)
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB"
    ];
    
    foreach ($tables as $table) {
        $conn->query($table);
    }
    
    // Add profile_pic column if it doesn't exist
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_pic'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL");
    }
    
    // Add payment_method column if it doesn't exist
    $result = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL");
    }
    
    // Insert sample admin if not exists
    $admin_check = $conn->query("SELECT * FROM admins WHERE username = 'admin'");
    if ($admin_check->num_rows == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO admins (username, password) VALUES ('admin', '$password')");
    }
    
    // Insert sample foods if table is empty (excluding removed items)
    $food_check = $conn->query("SELECT COUNT(*) as count FROM foods");
    $row = $food_check->fetch_assoc();
    if ($row['count'] < 5) {
        $sample_foods = [
            ['Margherita Pizza', 'https://images.pexels.com/photos/315755/pexels-photo-315755.jpeg?auto=compress&cs=tinysrgb&w=400', 'Classic pizza with fresh tomatoes, mozzarella, and basil', 12.99, 4.8, 'vegetarian,italian', 'Pizza'],
            ['Caesar Salad', 'https://images.pexels.com/photos/257816/pexels-photo-257816.jpeg?auto=compress&cs=tinysrgb&w=400', 'Fresh romaine lettuce with parmesan and croutons', 9.99, 4.5, 'vegetarian,healthy', 'Salad'],
            ['Chocolate Cake', 'https://images.pexels.com/photos/291528/pexels-photo-291528.jpeg?auto=compress&cs=tinysrgb&w=400', 'Rich chocolate cake with smooth frosting', 6.99, 4.9, 'dessert,chocolate', 'Cake'],
            ['Salmon Sushi', 'https://images.pexels.com/photos/357756/pexels-photo-357756.jpeg?auto=compress&cs=tinysrgb&w=400', 'Fresh salmon sushi with perfect rice', 18.99, 4.8, 'fish,japanese', 'Sushi'],
            ['Chicken Pasta', 'https://images.pexels.com/photos/1279330/pexels-photo-1279330.jpeg?auto=compress&cs=tinysrgb&w=400', 'Creamy pasta with grilled chicken and herbs', 14.99, 4.6, 'chicken,italian', 'Pasta'],
            ['Vegetable Stir Fry', 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg?auto=compress&cs=tinysrgb&w=400', 'Fresh vegetables stir-fried with aromatic spices', 11.99, 4.4, 'vegetarian,healthy,asian', 'Asian'],
            ['Fish Tacos', 'https://images.pexels.com/photos/461198/pexels-photo-461198.jpeg?auto=compress&cs=tinysrgb&w=400', 'Grilled fish tacos with fresh salsa', 13.99, 4.7, 'fish,mexican', 'Mexican'],
            ['Vanilla Ice Cream', 'https://images.pexels.com/photos/1352278/pexels-photo-1352278.jpeg?auto=compress&cs=tinysrgb&w=400', 'Creamy vanilla ice cream with toppings', 4.99, 4.3, 'dessert,cold', 'Dessert']
        ];
        
        foreach ($sample_foods as $food) {
            $stmt = $conn->prepare("INSERT INTO foods (name, image_url, description, price, rating, tags, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdsss", $food[0], $food[1], $food[2], $food[3], $food[4], $food[5], $food[6]);
            $stmt->execute();
        }
    }
    
    $conn->close();
}

// Initialize database on first run
initializeDatabase();
?>