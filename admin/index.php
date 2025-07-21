<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getConnection();

// Get dashboard stats
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT SUM(total_price) as total FROM orders")->fetch_assoc()['total'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'")->fetch_assoc()['count'];
$preparing_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Preparing'")->fetch_assoc()['count'];

// Get recent orders
$recent_orders = $conn->query("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC 
    LIMIT 15
");

$page_title = 'Admin Dashboard - Taste Bud Hub';
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
                <li><a href="index.php" class="active"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                <li><a href="foods.php"><i class="fas fa-utensils"></i> Manage Foods</a></li>
                <li><a href="orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="contacts.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <div>
                    <h1>Dashboard Overview</h1>
                    <div class="admin-user">
                        <i class="fas fa-user-shield"></i>
                        Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($total_orders); ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($total_sales, 2); ?></h3>
                        <p>Total Sales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($pending_orders); ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($preparing_orders); ?></h3>
                        <p>Preparing</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders with Scrollable Container -->
            <div class="admin-section">
                <div class="section-header">
                    <h2><i class="fas fa-receipt"></i> Recent Orders</h2>
                    <a href="orders.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>
                        View All Orders
                    </a>
                </div>
                
                <div class="order-history-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Order ID</th>
                                <th><i class="fas fa-user"></i> Customer</th>
                                <th><i class="fas fa-calendar"></i> Date & Time</th>
                                <th><i class="fas fa-money-bill"></i> Total</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders->num_rows > 0): ?>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $order['id']; ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($order['customer_name'] ?: 'Guest'); ?></strong>
                                                <br>
                                                <small style="color: var(--text-secondary);">
                                                    <i class="fas fa-phone"></i>
                                                    <?php echo htmlspecialchars($order['customer_phone']); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo date('M j, Y', strtotime($order['order_date'])); ?></strong>
                                                <br>
                                                <small style="color: var(--text-secondary);">
                                                    <i class="fas fa-clock"></i>
                                                    <?php echo date('g:i A', strtotime($order['order_date'])); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong style="color: var(--success-color); font-size: 1.1rem;">
                                                $<?php echo number_format($order['total_price'], 2); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                                <?php echo $order['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="admin-actions">
                                                <select onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)" 
                                                        style="margin-bottom: 0.5rem;">
                                                    <option value="Pending" <?php echo $order['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Preparing" <?php echo $order['status'] === 'Preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                    <option value="Delivered" <?php echo $order['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                </select>
                                                <button class="btn btn-sm btn-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-receipt"></i>
                                        <h3>No orders yet</h3>
                                        <p>Orders will appear here when customers place them.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-receipt"></i> Order Details</h3>
                <button class="close-modal" onclick="closeOrderModal()">&times;</button>
            </div>
            <div class="modal-body" id="orderDetails">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
    
    <script>
    function updateOrderStatus(orderId, newStatus) {
        fetch('update_order_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success feedback
                showNotification('Order status updated successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error updating order status', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating order status', 'error');
        });
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
            showNotification('Error loading order details', 'error');
        });
    }
    
    function closeOrderModal() {
        document.getElementById('orderModal').style.display = 'none';
    }
    
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        `;
        
        if (type === 'success') {
            notification.style.background = 'linear-gradient(135deg, #10B981, #059669)';
            notification.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        } else {
            notification.style.background = 'linear-gradient(135deg, #EF4444, #DC2626)';
            notification.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideInRight 0.3s ease-out reverse';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('orderModal');
        if (event.target === modal) {
            closeOrderModal();
        }
    }
    
    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
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
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 2px solid var(--border-color);
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }
        
        .modal-header h3 {
            margin: 0;
            color: var(--text-primary);
            font-size: 1.25rem;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .close-modal:hover {
            background: var(--bg-light);
            color: var(--text-primary);
            transform: scale(1.1);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>

<?php $conn->close(); ?>