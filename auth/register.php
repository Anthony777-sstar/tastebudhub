<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../dashboard.php';
    header("Location: $redirect");
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!empty($name) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            if (strlen($password) >= 6) {
                $user_id = createUser($name, $email, $password);
                
                if ($user_id) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    $redirect = isset($_GET['redirect']) ? '../' . $_GET['redirect'] : '../dashboard.php';
                    header("Location: $redirect");
                    exit();
                } else {
                    $error_message = 'An account with this email already exists or registration failed.';
                }
            } else {
                $error_message = 'Password must be at least 6 characters long.';
            }
        } else {
            $error_message = 'Passwords do not match.';
        }
    } else {
        $error_message = 'Please fill in all fields.';
    }
}

$page_title = 'Register - Taste Bud Hub';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="../index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
                <h1>Join Taste Bud Hub!</h1>
                <p>Create your account to access your personal dashboard</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small>Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-user-plus"></i>
                    Create Account & Access Dashboard
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . $_GET['redirect'] : ''; ?>">Sign in here</a></p>
            </div>
        </div>
        
        <div class="auth-visual">
            <div class="visual-content">
                <h2>Start Your Food Journey!</h2>
                <p>Get access to your personal dashboard with order tracking, history, and more!</p>
                <div class="visual-features">
                    <div class="feature">
                        <i class="fas fa-user-circle"></i>
                        <span>Personal Profile</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Smart Cart</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-mobile-alt"></i>
                        <span>Mobile Payments</span>
                    </div>
                    <div class="feature">
                         <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                        <span>Order Tracking</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>