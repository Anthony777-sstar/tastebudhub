<?php
require_once 'config/database.php';
$page_title = 'Order Confirmed - Taste Bud Hub';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$conn = getConnection();
$order_id = (int)$_GET['order_id'];

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit();
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, f.name, f.image_url 
    FROM order_items oi 
    JOIN foods f ON oi.food_id = f.id 
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();
?>

<main class="order-success-page">
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>Order Confirmed!</h1>
            <p>Thank you for your order. We're preparing your delicious meal!</p>
            
            <div class="order-details">
                <div class="order-header">
                    <h3>Order #<?php echo $order_id; ?></h3>
                    <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                        <?php echo $order['status']; ?>
                    </span>
                </div>
                
                <div class="order-info">
                    <div class="info-section">
                        <h4>Delivery Information</h4>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                    </div>
                    
                    <div class="info-section">
                        <h4>Order Summary</h4>
                        <div class="order-items-list">
                            <?php while ($item = $order_items->fetch_assoc()): ?>
                                <div class="order-item">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div class="item-details">
                                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                                    </div>
                                    <span class="item-total">$<?php echo number_format($item['item_price'] * $item['quantity'], 2); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="order-total">
                            <strong>Total: $<?php echo number_format($order['total_price'], 2); ?></strong>
                        </div>
                    </div>
                </div>
                
                <div class="estimated-delivery">
                    <i class="fas fa-clock"></i>
                    <span>Estimated delivery: 25-35 minutes</span>
                </div>
            </div>
            
            <div class="success-actions">
                <a href="menu.php" class="btn btn-outline">Order More</a>
                <a href="index.php" class="btn btn-primary">Back to Home</a>
            </div>
        </div>
    </div>
</main>

<script>
// Clear cart from localStorage
localStorage.removeItem('cart');
updateCartCount();
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>