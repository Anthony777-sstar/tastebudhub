<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getConnection();

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
}

// Get all contact messages
$contacts = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC");

$page_title = 'Contact Messages - Admin';
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
                <li><a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                <li><a href="foods.php"><i class="fas fa-utensils"></i> Manage Foods</a></li>
                <li><a href="orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="contacts.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Contact Messages</h1>
            </div>
            
            <!-- Messages List -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>All Messages</h2>
                </div>
                
                <?php if ($contacts->num_rows > 0): ?>
                    <div class="messages-grid">
                        <?php while ($contact = $contacts->fetch_assoc()): ?>
                            <div class="message-card">
                                <div class="message-header">
                                    <div class="sender-info">
                                        <h4><?php echo htmlspecialchars($contact['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($contact['email']); ?></p>
                                    </div>
                                    <div class="message-date">
                                        <?php echo date('M j, Y g:i A', strtotime($contact['created_at'])); ?>
                                    </div>
                                </div>
                                
                                <div class="message-content">
                                    <p><?php echo nl2br(htmlspecialchars($contact['message'])); ?></p>
                                </div>
                                
                                <div class="message-actions">
                                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-reply"></i> Reply
                                    </a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                        <input type="hidden" name="delete_id" value="<?php echo $contact['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-envelope"></i>
                        <h3>No messages yet</h3>
                        <p>Contact messages from customers will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <style>
    .messages-grid {
        display: grid;
        gap: 1.5rem;
        padding: 1.5rem;
    }
    
    .message-card {
        background: white;
        border-radius: 15px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: var(--transition);
    }
    
    .message-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 1.5rem 1.5rem 1rem;
        background: var(--bg-light);
        border-bottom: 1px solid var(--border-color);
    }
    
    .sender-info h4 {
        margin: 0 0 0.25rem 0;
        color: var(--text-primary);
    }
    
    .sender-info p {
        margin: 0;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }
    
    .message-date {
        color: var(--text-secondary);
        font-size: 0.875rem;
        text-align: right;
    }
    
    .message-content {
        padding: 1.5rem;
    }
    
    .message-content p {
        margin: 0;
        color: var(--text-primary);
        line-height: 1.6;
    }
    
    .message-actions {
        display: flex;
        gap: 0.5rem;
        padding: 1rem 1.5rem;
        background: var(--bg-light);
        border-top: 1px solid var(--border-color);
    }
    
    @media (max-width: 768px) {
        .message-header {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .message-date {
            text-align: left;
        }
        
        .message-actions {
            flex-direction: column;
        }
    }
    </style>
</body>
</html>

<?php $conn->close(); ?>