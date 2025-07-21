<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../dashboard.php';
    header("Location: $redirect");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (!empty($email) && !empty($password)) {
        $user = verifyUser($email, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            $redirect = isset($_GET['redirect']) ? '../' . $_GET['redirect'] : '../dashboard.php';
            header("Location: $redirect");
            exit();
        } else {
            $error_message = 'Invalid email or password.';
        }
    } else {
        $error_message = 'Please fill in all fields.';
    }
}

$page_title = 'Login - Taste Bud Hub';
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
                <h1>Welcome Back!</h1>
                <p>Sign in to access your dashboard and continue ordering</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In to Dashboard
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . $_GET['redirect'] : ''; ?>">Create one here</a></p>
            </div>
        </div>
        
        <div class="auth-visual">
            <div class="visual-content">
                <h2>Access Your Dashboard!</h2>
                <p>Manage your orders, track deliveries, and enjoy a personalized food ordering experience.</p>
                <div class="visual-features">
                    <div class="feature">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Personal Dashboard</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-history"></i>
                        <span>Order History</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-bell"></i>
                        <span>Real-time Updates</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-credit-card"></i>
                        <span>Secure Payments</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>