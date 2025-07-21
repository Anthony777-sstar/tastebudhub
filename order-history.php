<?php
require_once 'config/database.php';
$page_title = 'Order History - Taste Bud Hub';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php?redirect=order-history.php');
    exit();
}

// Get all user orders
$all_orders = getUserOrders($_SESSION['user_id']);
?>

<main class="order-history-page">
    <div class="container">
        <div class="page-header">
            <div class="header-content">
                <h1>Order History</h1>
                <p>View all your past orders and track your favorites</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
                <a href="menu.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    New Order
                </a>
            </div>
        </div>
        
        <div class="orders-container">
            <?php if (empty($all_orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <h3>No orders yet</h3>
                    <p>Start ordering to see your history here</p>
                    <a href="menu.php" class="btn btn-primary">Browse Menu</a>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($all_orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3>Order #<?php echo $order['id']; ?></h3>
                                    <p class="order-date"><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></p>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="order-details">
                                <div class="order-total">
                                    <strong>Total: $<?php echo number_format($order['total_price'], 2); ?></strong>
                                </div>
                                <?php if ($order['payment_method']): ?>
                                    <div class="payment-info">
                                        <i class="fas fa-credit-card"></i>
                                        <?php echo htmlspecialchars($order['payment_method']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="order-actions">
                                <button class="btn btn-outline btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </button>
                                <button class="btn btn-primary btn-sm" onclick="reorderItems(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-redo"></i>
                                    Reorder
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Order Details Modal -->
<div id="orderModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Order Details</h3>
            <button class="close-modal" onclick="closeOrderModal()">&times;</button>
        </div>
        <div class="modal-body" id="orderDetails">
            <!-- Order details will be loaded here -->
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    fetch(`get_order_details.php?id=${orderId}`)
    .then(response => response.text())
    .then(html => {
        document.getElementById('orderDetails').innerHTML = html;
        document.getElementById('orderModal').style.display = 'flex';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading order details');
    });
}

function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}

function reorderItems(orderId) {
    fetch(`get_order_details.php?id=${orderId}&format=json`)
    .then(response => response.json())
    .then(order => {
        if (order && order.items) {
            // Clear current cart
            localStorage.removeItem('cart');
            
            // Add order items to cart
            order.items.forEach(item => {
                addToCart(item.food_id, item.name, item.item_price, item.image_url, item.quantity);
            });
            
            // Show success message
            if (typeof showToast === 'function') {
                showToast('Items added to cart!', 'success');
            } else {
                alert('Items added to cart!');
            }
            
            // Update cart count
            updateCartCount();
            
            // Redirect to cart
            window.location.href = 'cart.php';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error reordering items');
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target === modal) {
        closeOrderModal();
    }
}
</script>

<style>
.order-history-page {
    padding: 2rem 1rem;
    min-height: 80vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3rem;
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: var(--shadow-sm);
}

.header-content h1 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}

.header-content p {
    margin: 0;
    color: var(--text-secondary);
}

.header-actions {
    display: flex;
    gap: 1rem;
}

.orders-container {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-sm);
}

.orders-grid {
    display: grid;
    gap: 1.5rem;
}

.order-card {
    border: 1px solid var(--border-color);
    border-radius: 15px;
    padding: 1.5rem;
    transition: var(--transition);
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.order-info h3 {
    margin: 0 0 0.25rem 0;
    color: var(--text-primary);
}

.order-date {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.order-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.payment-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.order-actions {
    display: flex;
    gap: 0.5rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    opacity: 0.5;
}

.empty-state h3 {
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    border-radius: 15px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
    margin: 0;
    color: var(--text-primary);
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-secondary);
    padding: 0.5rem;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.close-modal:hover {
    background: var(--bg-light);
    color: var(--text-primary);
}

.modal-body {
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1.5rem;
        text-align: center;
    }
    
    .header-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .order-header {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .order-details {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .order-actions {
        flex-direction: column;
        width: 100%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>