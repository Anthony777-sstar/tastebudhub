<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Taste Bud Hub'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <!-- Logo -->
            <div class="nav-logo">
                <a href="index.php">
                    <h2>🍽️ Taste Bud Hub</h2>
                </a>
            </div>
            
            <!-- Desktop Navigation Menu -->
            <div class="nav-menu" id="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="menu.php" class="nav-link">Menu</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="contact.php" class="nav-link">Contact</a>
            </div>
            
            <!-- Desktop Actions -->
            <div class="nav-actions">
                <div class="search-box">
                    <input type="text" placeholder="Search..." id="nav-search" aria-label="Search foods">
                    <i class="fas fa-search"></i>
                </div>
                
                <div class="cart-icon" onclick="toggleCart()" aria-label="Shopping cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cart-count">0</span>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-menu">
                        <?php 
                        require_once 'config/database.php';
                        $user = getUserById($_SESSION['user_id']);
                        ?>
                        <div class="user-profile clickable-profile" onclick="window.location.href='dashboard.php'" title="Go to Dashboard">
                            <div class="user-avatar-small">
                                <?php if ($user['profile_pic'] && file_exists($user['profile_pic'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile">
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                            </div>
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </div>
                        <a href="auth/logout.php" class="btn btn-outline btn-logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="logout-text">Logout</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="auth/login.php" class="btn btn-outline">Sign In</a>
                        <a href="auth/register.php" class="btn btn-primary">Register</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Hamburger Menu Button -->
            <button class="hamburger" id="hamburger" aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
        
        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>
        
        <!-- Mobile Navigation Menu -->
        <div class="mobile-menu" id="mobile-menu">
            <div class="mobile-menu-header">
                <div class="mobile-logo">
                    <h3>🍽️ Taste Bud Hub</h3>
                </div>
                <button class="mobile-menu-close" id="mobile-menu-close" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mobile-menu-content">
                <!-- Mobile Search -->
                <div class="mobile-search">
                    <div class="search-box">
                        <input type="text" placeholder="Search foods..." id="mobile-search" aria-label="Search foods">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <!-- Mobile Navigation Links -->
                <nav class="mobile-nav-links">
                    <a href="index.php" class="mobile-nav-link">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    <a href="menu.php" class="mobile-nav-link">
                        <i class="fas fa-utensils"></i>
                        <span>Menu</span>
                    </a>
                    <a href="about.php" class="mobile-nav-link">
                        <i class="fas fa-info-circle"></i>
                        <span>About</span>
                    </a>
                    <a href="contact.php" class="mobile-nav-link">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                </nav>
                
                <!-- Mobile Cart -->
                <div class="mobile-cart" onclick="toggleCart()">
                    <div class="mobile-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="mobile-cart-count" id="mobile-cart-count">0</span>
                    </div>
                    <span>View Cart</span>
                </div>
                
                <!-- Mobile Auth Actions -->
                <div class="mobile-auth-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="mobile-user-info" onclick="window.location.href='dashboard.php'">
                            <div class="user-avatar">
                                <?php if ($user['profile_pic'] && file_exists($user['profile_pic'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile">
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                            </div>
                            <div class="user-details">
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                                <span class="user-status">Tap to view dashboard</span>
                            </div>
                        </div>
                        <a href="auth/logout.php" class="mobile-auth-btn logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    <?php else: ?>
                        <a href="auth/login.php" class="mobile-auth-btn login-btn">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Sign In</span>
                        </a>
                        <a href="auth/register.php" class="mobile-auth-btn register-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Register</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>