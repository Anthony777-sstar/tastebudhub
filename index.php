<?php
require_once 'config/database.php';
$page_title = 'Home - Taste Bud Hub';
include 'includes/header.php';

// Get featured foods (excluding BBQ Ribs, Beef Burger Deluxe, Pepperoni Pizza, and Chicken Pizza)
$conn = getConnection();
$featured_foods = $conn->query("
    SELECT * FROM foods 
    WHERE name NOT IN ('BBQ Ribs', 'Beef Burger Deluxe', 'Pepperoni Pizza', 'Chicken Pizza') 
    AND is_available = 1 
    ORDER BY rating DESC 
    LIMIT 6
");

// Get categories
$categories = $conn->query("SELECT DISTINCT category FROM foods WHERE is_available = 1 ORDER BY category");
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Order your favourite <span class="highlight">Foods</span></h1>
                <p>Discover delicious meals from local restaurants and have them delivered right to your doorstep in under 30 minutes.</p>
                
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">24.30</span>
                        <span class="stat-label">Total Order</span>
                    </div>
                    <div class="hero-actions">
                        <div class="quantity-selector">
                            <button class="qty-btn" onclick="updateQuantity(-1)">-</button>
                            <span class="quantity" id="hero-quantity">1</span>
                            <button class="qty-btn" onclick="updateQuantity(1)">+</button>
                        </div>
                        <button class="btn btn-dark btn-large" onclick="scrollToMenu()">
                            Order Now
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="hero-image">
                    <img src="assets/fried-rice.jpg" alt="Featured Food">
                    <div class="food-info">
                        <div class="food-rating">
                            <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                            <span>4.8</span>
                        </div>
                        <div class="delivery-time">
                            <img src="assets/clock (1).png" alt=""style="width: 25px; height: 25px;">
                            <span>25 min</span>
                        </div>
                    </div>
                </div>
                <div class="floating-elements">
                    <div class="element element-1">🌶️</div>
                    <div class="element element-2">🍃</div>
                    <div class="element element-3">🧄</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2>Browse by Category</h2>
            <div class="category-grid" id="category-grid">
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <div class="category-card" onclick="filterByCategory('<?php echo $category['category']; ?>')">
                        <div class="category-icon">
                            <?php
                            $icons = [
                                'Pizza' => '🍕',
                                'Burger' => '🍔',
                                'Salad' => '🥗',
                                'Cake' => '🍰',
                                'Sushi' => '🍣',
                                'Pasta' => '🍝',
                                'Asian' => '🥢',
                                'Mexican' => '🌮',
                                'Dessert' => '🍨',
                                'BBQ' => '🍖'
                            ];
                            echo $icons[$category['category']] ?? '🍽️';
                            ?>
                        </div>
                        <h4><?php echo htmlspecialchars($category['category']); ?></h4>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    
    <!-- Featured Foods Section -->
    <section class="featured-foods" id="menu-section">
        <div class="container">
            <h2>Popular Dishes</h2>
            <p class="section-subtitle">Discover our most loved dishes - visit our menu to order!</p>
            <div class="foods-showcase">
                <?php while ($food = $featured_foods->fetch_assoc()): ?>
                    <div class="food-showcase-item" data-id="<?php echo $food['id']; ?>">
                        <div class="showcase-image">
                            <img src="<?php echo htmlspecialchars($food['image_url']); ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
                            <div class="showcase-overlay">
                                <div class="rating-badge">
                                     <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                                    <span><?php echo $food['rating']; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="showcase-info">
                            <h3><?php echo htmlspecialchars($food['name']); ?></h3>
                            <p class="showcase-description"><?php echo htmlspecialchars($food['description']); ?></p>
                            <div class="showcase-tags">
                                <?php 
                                $tags = explode(',', $food['tags']);
                                foreach (array_slice($tags, 0, 2) as $tag): ?>
                                    <span class="showcase-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="section-footer">
                <a href="menu.php" class="btn btn-primary btn-large">
                    <i class="fas fa-utensils"></i>
                    View Full Menu & Order
                </a>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2>What Our Customers Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                    </div>
                    <p>"Amazing food quality and super fast delivery! My go-to place for ordering meals."</p>
                    <div class="testimonial-author">
                        <strong>Sarah Johnson</strong>
                        <span>Regular Customer</span>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                    </div>
                    <p>"The variety of food options is incredible. Always something new to try!"</p>
                    <div class="testimonial-author">
                        <strong>Mike Chen</strong>
                        <span>Food Enthusiast</span>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                    </div>
                    <p>"User-friendly website and excellent customer service. Highly recommended!"</p>
                    <div class="testimonial-author">
                        <strong>Emily Davis</strong>
                        <span>Happy Customer</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
$conn->close();
include 'includes/footer.php';
?>