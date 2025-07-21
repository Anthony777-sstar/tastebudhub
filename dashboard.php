<?php
require_once 'config/database.php';
$page_title = 'Dashboard - Taste Bud Hub';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php?redirect=dashboard.php');
    exit();
}

// Get user data
$user = getUserById($_SESSION['user_id']);
if (!$user) {
    header('Location: auth/logout.php');
    exit();
}

// Pagination for order history
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Get user orders with pagination
$ongoing_orders = getOngoingOrders($_SESSION['user_id']);
$order_history = getOrderHistory($_SESSION['user_id']);

// Get paginated order history
$conn = getConnection();
$total_orders_query = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ? AND status = 'Delivered'");
$total_orders_query->bind_param("i", $_SESSION['user_id']);
$total_orders_query->execute();
$total_orders = $total_orders_query->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $per_page);

$paginated_history_query = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND status = 'Delivered' ORDER BY order_date DESC LIMIT ? OFFSET ?");
$paginated_history_query->bind_param("iii", $_SESSION['user_id'], $per_page, $offset);
$paginated_history_query->execute();
$paginated_history = $paginated_history_query->get_result();

// Handle profile picture upload
$upload_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $upload_dir = 'uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file = $_FILES['profile_pic'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old profile picture if exists
                if ($user['profile_pic'] && file_exists($user['profile_pic'])) {
                    unlink($user['profile_pic']);
                }
                
                // Update database
                if (updateUserProfilePicture($_SESSION['user_id'], $upload_path)) {
                    $upload_message = 'Profile picture updated successfully!';
                    $user['profile_pic'] = $upload_path; // Update local variable
                } else {
                    $upload_message = 'Failed to update profile picture in database.';
                }
            } else {
                $upload_message = 'Failed to upload file.';
            }
        } else {
            $upload_message = 'Invalid file type or size. Please upload a JPEG, PNG, or GIF image under 5MB.';
        }
    } else {
        $upload_message = 'Upload error occurred.';
    }
}
?>

<main class="dashboard-page">
    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="user-welcome">
                <div class="user-avatar">
                    <?php if ($user['profile_pic'] && file_exists($user['profile_pic'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="welcome-text">
                    <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                    <p>Manage your orders and account settings</p>
                </div>
            </div>
            
            <div class="dashboard-actions">
                <button class="btn btn-primary" onclick="goToCart()">
                    <i class="fas fa-shopping-cart"></i>
                    View Cart (<span id="cart-count-dashboard">0</span>)
                </button>
                <a href="menu.php" class="btn btn-outline">
                    <i class="fas fa-utensils"></i>
                    Browse Menu
                </a>
            </div>
        </div>
        
        <!-- Profile Picture Upload -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Profile Picture</h2>
                <button class="btn btn-outline btn-sm" onclick="toggleProfileUpload()">
                    <i class="fas fa-camera"></i>
                    Change Picture
                </button>
            </div>
            
            <?php if ($upload_message): ?>
                <div class="alert <?php echo strpos($upload_message, 'successfully') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <i class="fas <?php echo strpos($upload_message, 'successfully') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $upload_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-upload-form" id="profile-upload-form" style="display: none;">
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="profile_pic">Choose Profile Picture</label>
                        <input type="file" id="profile_pic" name="profile_pic" accept="image/*" required>
                        <small>Maximum file size: 5MB. Supported formats: JPEG, PNG, GIF</small>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i>
                            Upload Picture
                        </button>
                        <button type="button" class="btn btn-outline" onclick="toggleProfileUpload()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Ongoing Orders -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Ongoing Orders</h2>
                    <span class="order-count"><?php echo count($ongoing_orders); ?> active</span>
                </div>
                
                <div class="orders-list">
                    <?php if (empty($ongoing_orders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <h3>No ongoing orders</h3>
                            <p>Your active orders will appear here</p>
                            <a href="menu.php" class="btn btn-primary">Start Ordering</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ongoing_orders as $order): ?>
                            <div class="order-card ongoing-order">
                                <div class="order-header">
                                    <div class="order-info">
                                        <h4>Order #<?php echo $order['id']; ?></h4>
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
                                        <div class="payment-method">
                                            <i class="fas fa-credit-card"></i>
                                            <?php echo htmlspecialchars($order['payment_method']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="order-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php 
                                            echo $order['status'] === 'Pending' ? '33%' : 
                                                ($order['status'] === 'Preparing' ? '66%' : '100%'); 
                                        ?>"></div>
                                    </div>
                                    <div class="progress-labels">
                                        <span class="<?php echo $order['status'] === 'Pending' ? 'active' : 'completed'; ?>">Pending</span>
                                        <span class="<?php echo $order['status'] === 'Preparing' ? 'active' : ($order['status'] === 'Delivered' ? 'completed' : ''); ?>">Preparing</span>
                                        <span class="<?php echo $order['status'] === 'Delivered' ? 'active' : ''; ?>">Delivered</span>
                                    </div>
                                </div>
                                
                                <div class="order-actions">
                                    <button class="btn btn-outline btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order History with Scroll -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Order History</h2>
                    <div class="history-header-actions">
                        <span class="order-count"><?php echo $total_orders; ?> completed</span>
                        <?php if ($total_orders > 5): ?>
                            <a href="order-history.php" class="btn btn-outline btn-sm">
                                <i class="fas fa-list"></i>
                                View All
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Scrollable Order History Container -->
                <div class="order-history-scroll-container">
                    <div class="orders-list">
                        <?php if ($total_orders === 0): ?>
                            <div class="empty-state">
                                <i class="fas fa-history"></i>
                                <h3>No order history</h3>
                                <p>Your completed orders will appear here</p>
                            </div>
                        <?php else: ?>
                            <?php while ($order = $paginated_history->fetch_assoc()): ?>
                                <div class="order-card history-order">
                                    <div class="order-header">
                                        <div class="order-info">
                                            <h4>Order #<?php echo $order['id']; ?></h4>
                                            <p class="order-date"><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></p>
                                        </div>
                                        <div class="order-total">
                                            <strong>$<?php echo number_format($order['total_price'], 2); ?></strong>
                                        </div>
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
                            <?php endwhile; ?>
                            
                            <!-- Pagination Controls -->
                            <?php if ($total_pages > 1): ?>
                                <div class="order-history-pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    <?php endif; ?>
                                    
                                    <span class="pagination-info">
                                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                                    </span>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($ongoing_orders) + $total_orders; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($ongoing_orders); ?></h3>
                    <p>Active Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                     <img src="assets/star.png" alt=""style="width: 25px; height: 25px;">
                </div>
                <div class="stat-content">
                    <h3>4.8</h3>
                    <p>Avg Rating</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $total_orders; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
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
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});

function toggleProfileUpload() {
    const form = document.getElementById('profile-upload-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function updateCartCount() {
    const cart = getCart();
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    const dashboardCount = document.getElementById('cart-count-dashboard');
    if (dashboardCount) {
        dashboardCount.textContent = count;
    }
}

function goToCart() {
    window.location.href = 'cart.php';
}

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
            }
            
            // Update cart count
            updateCartCount();
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
.dashboard-page {
    padding: 2rem 1rem;
    min-height: 80vh;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3rem;
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: var(--shadow-sm);
}

.user-welcome {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.user-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: var(--bg-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--text-secondary);
}

.welcome-text h1 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}

.welcome-text p {
    margin: 0;
    color: var(--text-secondary);
}

.dashboard-actions {
    display: flex;
    gap: 1rem;
}

.dashboard-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-sm);
    margin-bottom: 2rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.section-header h2 {
    margin: 0;
    color: var(--text-primary);
}

.history-header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.order-count {
    background: var(--primary-color);
    color: var(--text-primary);
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.875rem;
    font-weight: 500;
}

.profile-upload-form {
    margin-top: 1rem;
}

.upload-form {
    display: grid;
    gap: 1rem;
    max-width: 400px;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 3rem;
}

.orders-list {
    display: grid;
    gap: 1rem;
}

/* Enhanced Scrollable Order History Container */
.order-history-scroll-container {
    max-height: 450px;
    overflow-y: auto;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    background: var(--bg-light);
}

.order-history-scroll-container::-webkit-scrollbar {
    width: 8px;
}

.order-history-scroll-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.order-history-scroll-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.order-history-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.order-history-scroll-container .orders-list {
    padding: 1rem;
    gap: 1rem;
}

.order-card {
    border: 1px solid var(--border-color);
    border-radius: 15px;
    padding: 1.5rem;
    transition: var(--transition);
    background: white;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.ongoing-order {
    border-left: 4px solid var(--primary-color);
}

.history-order {
    border-left: 4px solid var(--success-color);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.order-info h4 {
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

.payment-method {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.order-progress {
    margin-bottom: 1rem;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: var(--bg-light);
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    transition: width 0.3s ease;
}

.progress-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
}

.progress-labels span {
    color: var(--text-secondary);
}

.progress-labels span.active {
    color: var(--primary-color);
    font-weight: 600;
}

.progress-labels span.completed {
    color: var(--success-color);
}

.order-actions {
    display: flex;
    gap: 0.5rem;
}

/* Enhanced Pagination */
.order-history-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: white;
    border-top: 1px solid var(--border-color);
    margin-top: 1rem;
    border-radius: 0 0 12px 12px;
}

.pagination-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--primary-color);
    color: var(--text-primary);
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: var(--transition);
    font-size: 0.875rem;
}

.pagination-btn:hover {
    background: var(--secondary-color);
    transform: translateY(-1px);
}

.pagination-info {
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
}

.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-card:nth-child(1) .stat-icon {
    background: linear-gradient(135deg, #3B82F6, #1D4ED8);
}

.stat-card:nth-child(2) .stat-icon {
    background: linear-gradient(135deg, #F59E0B, #D97706);
}

.stat-card:nth-child(3) .stat-icon {
    background: linear-gradient(135deg, #10B981, #059669);
}

.stat-card:nth-child(4) .stat-icon {
    background: linear-gradient(135deg, #EF4444, #DC2626);
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 0.25rem 0;
}

.stat-content p {
    color: var(--text-secondary);
    margin: 0;
    font-size: 0.875rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
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

/* Scroll Indicator */
.order-history-scroll-container::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 2px;
    height: 100%;
    background: linear-gradient(to bottom, var(--primary-color), transparent);
    opacity: 0.3;
    pointer-events: none;
}

/* Loading Animation for Scroll */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.order-card {
    animation: fadeInUp 0.3s ease-out;
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        gap: 1.5rem;
        text-align: center;
    }
    
    .dashboard-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
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
    
    .history-header-actions {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-end;
    }
    
    .order-history-scroll-container {
        max-height: 350px;
    }
    
    .order-history-pagination {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 1.5rem;
    }
    
    .user-welcome {
        flex-direction: column;
        text-align: center;
    }
    
    .order-history-scroll-container {
        max-height: 300px;
    }
}
</style>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>