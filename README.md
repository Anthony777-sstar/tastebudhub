# Taste Bud Hub - Food Ordering Platform

A complete food ordering website built with **raw PHP, MySQL, HTML, CSS, and JavaScript** - no frameworks or libraries.

## 🚀 Quick Setup

### 1. Database Setup
1. Open **phpMyAdmin**
2. Go to **SQL** tab
3. Copy and paste the entire content from `database.sql`
4. Click **Go** to execute

### 2. Configuration
Update database credentials in `config/database.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'taste_bud_hub');
```

### 3. Access the Site
- **Frontend:** `http://localhost/your-project/`
- **Admin Panel:** `http://localhost/taste-bud-hub/admin/`

## 🔑 Default Login Credentials

### Admin Panel
- **Username:** `admin`
- **Password:** `password`

### Sample Users
- **Email:** `john@example.com`
- **Password:** `password`

## 📁 Project Structure

```
taste-bud-hub/
├── index.php              # Homepage
├── menu.php               # Food menu page
├── cart.php               # Shopping cart
├── about.php              # About page
├── contact.php            # Contact page
├── order_success.php      # Order confirmation
├── process_order.php      # Order processing
├── database.sql           # Database setup script
├── config/
│   └── database.php       # Database configuration
├── includes/
│   ├── header.php         # Site header
│   └── footer.php         # Site footer
├── auth/
│   ├── login.php          # User login
│   ├── register.php       # User registration
│   ├── logout.php         # Logout
│   └── check_login.php    # Login status check
├── admin/
│   ├── index.php          # Admin dashboard
│   ├── login.php          # Admin login
│   ├── foods.php          # Manage foods
│   ├── orders.php         # Manage orders
│   └── contacts.php       # View messages
└── assets/
    ├── css/
    │   ├── style.css      # Main styles
    │   └── admin.css      # Admin styles
    └── js/
        ├── main.js        # Main JavaScript
        └── cart.js        # Cart functionality
```

## 🗄️ Database Schema

### Core Tables
- **users** - Customer accounts
- **admins** - Administrator accounts  
- **foods** - Food items catalog
- **orders** - Customer orders
- **order_items** - Order line items
- **contacts** - Contact form messages
- **reviews** - Customer reviews (optional)

### Key Features
- ✅ **Raw SQL queries only** - No ORM or frameworks
- ✅ **Prepared statements** - SQL injection protection
- ✅ **Foreign key constraints** - Data integrity
- ✅ **Proper indexing** - Optimized performance
- ✅ **Password hashing** - Secure authentication

## 🛠️ Raw SQL Examples

### Get All Foods
```sql
SELECT * FROM foods WHERE is_available = 1 ORDER BY rating DESC;
```

### Search Foods
```sql
SELECT * FROM foods 
WHERE (name LIKE '%search%' OR description LIKE '%search%') 
AND is_available = 1;
```

### Create Order
```sql
INSERT INTO orders (user_id, customer_name, customer_phone, customer_address, total_price) 
VALUES (1, 'John Doe', '+1234567890', '123 Main St', 25.99);
```

### Get Order with Items
```sql
SELECT o.*, oi.quantity, oi.item_price, f.name as food_name
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
JOIN foods f ON oi.food_id = f.id
WHERE o.id = 1;
```

## 🎨 Features

### Customer Features
- Browse food menu by category
- Search foods by name/description
- Add items to cart (localStorage)
- User registration and login
- Place orders with delivery info
- Order confirmation page
- Contact form

### Admin Features
- Admin dashboard with statistics
- Manage food items (CRUD)
- View and update order status
- View customer messages
- Order management system

### Technical Features
- **Responsive design** - Mobile-friendly
- **Cart system** - JavaScript + localStorage
- **Session management** - PHP sessions
- **Form validation** - Client and server-side
- **Security** - Prepared statements, password hashing
- **Clean URLs** - SEO-friendly structure

## 🔧 Customization

### Adding New Foods
```sql
INSERT INTO foods (name, image_url, description, price, rating, tags, category) 
VALUES ('New Dish', 'image-url.jpg', 'Description', 15.99, 4.5, 'tag1,tag2', 'Category');
```

### Adding New Admin
```sql
INSERT INTO admins (username, password, email) 
VALUES ('newadmin', '$2y$10$hashedpassword', 'admin@example.com');
```

### Updating Order Status
```sql
UPDATE orders SET status = 'Delivered' WHERE id = 1;
```

## 🚀 Deployment

1. Upload files to web server
2. Import `database.sql` in hosting phpMyAdmin
3. Update database credentials in `config/database.php`
4. Set proper file permissions
5. Test functionality

## 📝 Notes

- **No frameworks** - Pure PHP, HTML, CSS, JavaScript
- **Raw SQL only** - Direct mysqli queries
- **Security focused** - Prepared statements, input validation
- **Mobile responsive** - Works on all devices
- **Production ready** - Proper error handling

## 🆘 Troubleshooting

### Database Connection Issues
1. Check credentials in `config/database.php`
2. Ensure MySQL server is running
3. Verify database exists
4. Check user permissions

### Common Errors
- **500 Error:** Check PHP error logs
- **Database Error:** Verify SQL syntax
- **Login Issues:** Check password hashing
- **Cart Issues:** Enable JavaScript

## 📞 Support

For issues or questions:
1. Check the code comments
2. Review error logs
3. Verify database setup
4. Test with sample data

---

**Built with using raw PHP, MySQL, HTML, CSS & JavaScript**