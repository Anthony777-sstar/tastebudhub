<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getConnection();
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = htmlspecialchars($_POST['name']);
                $image_url = htmlspecialchars($_POST['image_url']);
                $description = htmlspecialchars($_POST['description']);
                $price = floatval($_POST['price']);
                $rating = floatval($_POST['rating']);
                $tags = htmlspecialchars($_POST['tags']);
                $category = htmlspecialchars($_POST['category']);
                
                $stmt = $conn->prepare("INSERT INTO foods (name, image_url, description, price, rating, tags, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssdsss", $name, $image_url, $description, $price, $rating, $tags, $category);
                
                if ($stmt->execute()) {
                    $message = "Food item added successfully!";
                } else {
                    $message = "Error adding food item.";
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $name = htmlspecialchars($_POST['name']);
                $image_url = htmlspecialchars($_POST['image_url']);
                $description = htmlspecialchars($_POST['description']);
                $price = floatval($_POST['price']);
                $rating = floatval($_POST['rating']);
                $tags = htmlspecialchars($_POST['tags']);
                $category = htmlspecialchars($_POST['category']);
                
                $stmt = $conn->prepare("UPDATE foods SET name=?, image_url=?, description=?, price=?, rating=?, tags=?, category=? WHERE id=?");
                $stmt->bind_param("sssdssi", $name, $image_url, $description, $price, $rating, $tags, $category, $id);
                
                if ($stmt->execute()) {
                    $message = "Food item updated successfully!";
                } else {
                    $message = "Error updating food item.";
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                $stmt = $conn->prepare("DELETE FROM foods WHERE id=?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = "Food item deleted successfully!";
                } else {
                    $message = "Error deleting food item.";
                }
                break;
        }
    }
}

// Get all foods
$foods = $conn->query("SELECT * FROM foods ORDER BY category, name");
$categories = $conn->query("SELECT DISTINCT category FROM foods ORDER BY category");

$page_title = 'Manage Foods - Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-page">
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <h2>🍽️ Admin Panel</h2>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                <li><a href="foods.php" class="active"><i class="fas fa-utensils"></i> Manage Foods</a></li>
                <li><a href="orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="contacts.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Manage Foods</h1>
                <button class="btn btn-primary" onclick="showAddForm()">
                    <i class="fas fa-plus"></i> Add New Food
                </button>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Add/Edit Form -->
            <div class="admin-section" id="food-form" style="display: none;">
                <div class="section-header">
                    <h2 id="form-title">Add New Food</h2>
                    <button class="btn btn-outline" onclick="hideForm()">Cancel</button>
                </div>
                
                <form class="admin-form" method="POST" id="foodForm">
                    <input type="hidden" name="action" id="form-action" value="add">
                    <input type="hidden" name="id" id="food-id">
                    
                    <div class="form-group">
                        <label for="name">Food Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="url" id="image_url" name="image_url" required>
                        <small>Use Pexels or other stock photo URLs</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rating">Rating (1-5)</label>
                        <input type="number" id="rating" name="rating" step="0.1" min="1" max="5" value="4.5" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tags">Tags (comma-separated)</label>
                        <input type="text" id="tags" name="tags" placeholder="e.g., vegetarian, spicy, italian">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Pizza">Pizza</option>
                            <option value="Burger">Burger</option>
                            <option value="Salad">Salad</option>
                            <option value="Cake">Cake</option>
                            <option value="Sushi">Sushi</option>
                            <option value="Pasta">Pasta</option>
                            <option value="Asian">Asian</option>
                            <option value="Mexican">Mexican</option>
                            <option value="Dessert">Dessert</option>
                            <option value="BBQ">BBQ</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Food
                        </button>
                        <button type="button" class="btn btn-outline" onclick="hideForm()">Cancel</button>
                    </div>
                </form>
            </div>
            
            <!-- Foods List -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>All Foods</h2>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($food = $foods->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($food['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($food['name']); ?>" 
                                             class="food-image-preview">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($food['name']); ?></strong>
                                        <br>
                                        <small><?php echo htmlspecialchars(substr($food['description'], 0, 50)) . '...'; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($food['category']); ?></td>
                                    <td>$<?php echo number_format($food['price'], 2); ?></td>
                                    <td>
                                        <i class="fas fa-star" style="color: var(--primary-color);"></i>
                                        <?php echo $food['rating']; ?>
                                    </td>
                                    <td>
                                        <div class="admin-actions">
                                            <button class="btn btn-sm btn-warning" onclick="editFood(<?php echo htmlspecialchars(json_encode($food)); ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this food item?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $food['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    function showAddForm() {
        document.getElementById('food-form').style.display = 'block';
        document.getElementById('form-title').textContent = 'Add New Food';
        document.getElementById('form-action').value = 'add';
        document.getElementById('foodForm').reset();
        document.getElementById('food-form').scrollIntoView({ behavior: 'smooth' });
    }
    
    function hideForm() {
        document.getElementById('food-form').style.display = 'none';
    }
    
    function editFood(food) {
        document.getElementById('food-form').style.display = 'block';
        document.getElementById('form-title').textContent = 'Edit Food';
        document.getElementById('form-action').value = 'edit';
        document.getElementById('food-id').value = food.id;
        document.getElementById('name').value = food.name;
        document.getElementById('image_url').value = food.image_url;
        document.getElementById('description').value = food.description;
        document.getElementById('price').value = food.price;
        document.getElementById('rating').value = food.rating;
        document.getElementById('tags').value = food.tags;
        document.getElementById('category').value = food.category;
        document.getElementById('food-form').scrollIntoView({ behavior: 'smooth' });
    }
    </script>
</body>
</html>

<?php $conn->close(); ?>