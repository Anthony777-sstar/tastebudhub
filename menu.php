<?php
require_once 'config/database.php';
$page_title = 'Menu - Taste Bud Hub';
include 'includes/header.php';

$conn = getConnection();

// Get categories for filter
$categories = $conn->query("SELECT DISTINCT category FROM foods ORDER BY category");

// Get search and category filters
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Build SQL query
$sql = "SELECT * FROM foods WHERE 1=1";
$params = [];
$types = "";

if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR tags LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

$sql .= " ORDER BY rating DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$foods = $stmt->get_result();
?>

<main class="menu-page">
    <div class="container">
        <!-- Menu Header -->
        <div class="menu-header">
            <h1>Our Menu</h1>
            <p>Explore our delicious selection of dishes</p>
        </div>
        
        <!-- Filters -->
        <div class="menu-filters">
            <div class="category-pills">
                <button class="pill <?php echo empty($category_filter) ? 'active' : ''; ?>" onclick="filterCategory('')">All</button>
                <?php 
                $categories->data_seek(0);
                while ($category = $categories->fetch_assoc()): ?>
                    <button class="pill <?php echo $category_filter === $category['category'] ? 'active' : ''; ?>" 
                            onclick="filterCategory('<?php echo $category['category']; ?>')">
                        <?php echo htmlspecialchars($category['category']); ?>
                    </button>
                <?php endwhile; ?>
            </div>
            
            <div class="search-bar">
                <input type="text" id="menu-search" placeholder="Search dishes..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <button onclick="searchMenu()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <!-- Menu Grid -->
        <div class="menu-grid">
            <?php if ($foods->num_rows > 0): ?>
                <?php while ($food = $foods->fetch_assoc()): ?>
                    <div class="menu-item" data-category="<?php echo $food['category']; ?>">
                        <div class="item-image">
                            <img src="<?php echo htmlspecialchars($food['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($food['name']); ?>">
                        </div>
                        
                        <div class="item-info">
                            <div class="item-header">
                                <h3><?php echo htmlspecialchars($food['name']); ?></h3>
                                <div class="item-rating">
                                     <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                                    <span><?php echo $food['rating']; ?></span>
                                </div>
                            </div>
                            
                            <p class="item-description"><?php echo htmlspecialchars($food['description']); ?></p>
                            
                            <div class="item-tags">
                                <?php 
                                $tags = explode(',', $food['tags']);
                                foreach (array_slice($tags, 0, 3) as $tag): ?>
                                    <span class="showcase-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="item-footer">
                                <div class="item-price">$<?php echo number_format($food['price'], 2); ?></div>
                                <span class="item-category"><?php echo htmlspecialchars($food['category']); ?></span>
                            </div>
                            
                            <button class="btn-add-to-cart" 
                                    onclick="addToCartWithFeedback(this, <?php echo $food['id']; ?>, '<?php echo htmlspecialchars($food['name']); ?>', <?php echo $food['price']; ?>, '<?php echo htmlspecialchars($food['image_url']); ?>')">
                                <i class="fas fa-plus"></i>
                                Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No dishes found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Login Modal -->
<div id="loginModal" class="login-modal">
    <div class="login-modal-content">
        <h3>Sign In Required</h3>
        <p>Please sign in or register before proceeding to checkout.</p>
        <div class="login-modal-actions">
            <a href="auth/login.php" class="btn btn-primary">Sign In</a>
            <a href="auth/register.php" class="btn btn-outline">Register</a>
            <button class="btn btn-outline" onclick="closeLoginModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
function filterCategory(category) {
    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    window.location.href = url.toString();
}

function searchMenu() {
    const searchTerm = document.getElementById('menu-search').value;
    const url = new URL(window.location);
    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    } else {
        url.searchParams.delete('search');
    }
    window.location.href = url.toString();
}

function addToCartWithFeedback(button, id, name, price, image) {
    // Add visual feedback
    button.classList.add('added');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Added!';
    
    // Add to cart
    addToCart(id, name, price, image);
    
    // Reset button after animation
    setTimeout(() => {
        button.classList.remove('added');
        button.innerHTML = originalText;
    }, 1000);
}

function showLoginModal() {
    document.getElementById('loginModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLoginModal() {
    document.getElementById('loginModal').classList.remove('active');
    document.body.style.overflow = '';
}

// Override cart icon click to check authentication
document.addEventListener('DOMContentLoaded', function() {
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon) {
        cartIcon.onclick = function() {
            // Check if user is logged in
            fetch('auth/check_login.php')
                .then(response => response.json())
                .then(data => {
                    if (data.logged_in) {
                        window.location.href = 'cart.php';
                    } else {
                        showLoginModal();
                    }
                })
                .catch(() => {
                    showLoginModal();
                });
        };
    }
});

document.getElementById('menu-search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchMenu();
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target === modal) {
        closeLoginModal();
    }
}
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>