<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    echo 'Unauthorized';
    exit();
}

if (!isset($_GET['id'])) {
    echo 'Order ID required';
    exit();
}

$order_id = intval($_GET['id']);
$conn = getConnection();

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo 'Order not found';
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

<div class="order-details">
    <div class="order-info-grid">
        <div class="info-section">
            <h4>Order Information</h4>
            <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
            <p><strong>Date:</strong> <?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></p>
            <p><strong>Status:</strong> 
                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                    <?php echo $order['status']; ?>
                </span>
            </p>
            <p><strong>Total:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>
        </div>
        
        <div class="info-section">
            <h4>Customer Information</h4>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name'] ?: 'Guest'); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
        </div>
    </div>
    
    <div class="order-items-section">
        <h4>Order Items</h4>
        <div class="order-items-list">
            <?php while ($item = $order_items->fetch_assoc()): ?>
                <div class="order-item-detail">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div class="item-info">
                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                        <p>Price: $<?php echo number_format($item['item_price'], 2); ?> each</p>
                    </div>
                    <div class="item-total">
                        $<?php echo number_format($item['item_price'] * $item['quantity'], 2); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<style>
.order-details {
    color: var(--text-primary);
}

.order-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.info-section h4 {
    color: var(--text-primary);
    margin-bottom: 1rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.info-section p {
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
}

.order-items-section h4 {
    color: var(--text-primary);
    margin-bottom: 1rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.order-items-list {
    display: grid;
    gap: 1rem;
}

.order-item-detail {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 1rem;
    background: var(--bg-light);
    border-radius: var(--border-radius);
}

.order-item-detail img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: var(--border-radius);
}

.item-info {
    flex: 1;
}

.item-info h5 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}

.item-info p {
    margin: 0.25rem 0;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.item-total {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 1.125rem;
}

@media (max-width: 768px) {
    .order-info-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .order-item-detail {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<?php $conn->close(); ?>