<?php
require_once 'config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$conn = getConnection();

try {
    // Get form data
    $user_id = $_SESSION['user_id'];
    $customer_name = htmlspecialchars($_POST['customer_name']);
    $customer_address = htmlspecialchars($_POST['customer_address']);
    $customer_phone = htmlspecialchars($_POST['customer_phone']);
    $payment_method = htmlspecialchars($_POST['payment_method'] ?? 'Card');
    $cart_data = json_decode($_POST['cart_data'], true);

    if (empty($cart_data)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit();
    }

    // Calculate total
    $subtotal = 0;
    foreach ($cart_data as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $delivery_fee = 3.99;
    $tax = $subtotal * 0.08;
    $total_price = $subtotal + $delivery_fee + $tax;

    $conn->begin_transaction();
    
    // Create order
    $order_id = createOrder($user_id, $customer_name, $customer_phone, $customer_address, $total_price, $payment_method);
    
    if (!$order_id) {
        throw new Exception('Failed to create order');
    }
    
    // Add order items
    foreach ($cart_data as $item) {
        if (!addOrderItem($order_id, $item['id'], $item['quantity'], $item['price'])) {
            throw new Exception('Failed to add order item');
        }
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'order_id' => $order_id,
        'message' => 'Order placed successfully'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Order processing failed: ' . $e->getMessage()
    ]);
}

$conn->close();
?>