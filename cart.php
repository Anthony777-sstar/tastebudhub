<?php
require_once 'config/database.php';
$page_title = 'Cart - Taste Bud Hub';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php?redirect=cart.php');
    exit();
}
?>

<main class="cart-page">
    <div class="container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Your Cart</h1>
            <p>Review your order and proceed to checkout</p>
            <div class="cart-actions">
                <button class="btn btn-outline" onclick="window.location.href='menu.php'">
                    <i class="fas fa-plus"></i>
                    Add More Items
                </button>
                <button class="btn btn-danger" onclick="clearCart()" id="clear-cart-btn" style="display: none;">
                    <i class="fas fa-trash"></i>
                    Clear Cart
                </button>
            </div>
        </div>
        
        <div class="cart-layout">
            <div class="cart-items-section">
                <div class="cart-items-header">
                    <h3>Items in Your Cart</h3>
                    <span class="item-count" id="cart-item-count">0 items</span>
                </div>
                
                <div id="cart-items-list">
                    <div class="empty-cart-message">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Your cart is empty</h3>
                        <p>Add some delicious items to get started!</p>
                        <a href="menu.php" class="btn btn-primary">Browse Menu</a>
                    </div>
                </div>
            </div>
            
            <div class="order-summary">
                <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                
                <div class="summary-details">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <span id="delivery-fee">$3.99</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (8%):</span>
                        <span id="tax">$0.00</span>
                    </div>
                    <hr>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="order-total">$0.00</span>
                    </div>
                </div>
                
                <form id="checkout-form" method="POST" action="process_order.php">
                    <div class="delivery-info">
                        <h4><i class="fas fa-map-marker-alt"></i> Delivery Information</h4>
                        <div class="form-group">
                            <label for="customer_name">Full Name</label>
                            <input type="text" id="customer_name" name="customer_name" placeholder="Enter your full name" required>
                        </div>
                        <div class="form-group">
                            <label for="customer_address">Delivery Address</label>
                            <textarea id="customer_address" name="customer_address" placeholder="Enter your complete delivery address" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="customer_phone">Phone Number</label>
                            <input type="tel" id="customer_phone" name="customer_phone" placeholder="Enter your phone number" required>
                        </div>
                    </div>
                    
                    <div class="payment-section">
                        <h4><i class="fas fa-credit-card"></i> Payment Method</h4>
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" id="google-pay" name="payment_method" value="GooglePay">
                                <label for="google-pay" class="payment-label">
                                    <div class="payment-icon google-pay">
                                        <i class="fab fa-google"></i>
                                    </div>
                                    <span>Google Pay</span>
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="apple-pay" name="payment_method" value="ApplePay">
                                <label for="apple-pay" class="payment-label">
                                    <div class="payment-icon apple-pay">
                                        <i class="fab fa-apple"></i>
                                    </div>
                                    <span>Apple Pay</span>
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="card-pay" name="payment_method" value="Card" checked>
                                <label for="card-pay" class="payment-label">
                                    <div class="payment-icon card-pay">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <span>Credit/Debit Card</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Google Pay Button -->
                    <div id="google-pay-button" class="payment-button" style="display: none;"></div>
                    
                    <!-- Apple Pay Button -->
                    <div id="apple-pay-button" class="payment-button" style="display: none;">
                        <button type="button" class="apple-pay-btn">
                            <i class="fab fa-apple"></i>
                            Pay with Apple Pay
                        </button>
                    </div>
                    
                    <!-- Regular Checkout Button -->
                    <button type="submit" class="btn btn-primary btn-full" id="checkout-btn" disabled>
                        <i class="fas fa-lock"></i>
                        Place Order Securely
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<!-- Payment Success Modal -->
<div id="payment-success-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-check-circle"></i> Payment Successful!</h3>
        </div>
        <div class="modal-body">
            <div class="success-animation">
                <i class="fas fa-check-circle"></i>
            </div>
            <h4>Thank you for your order!</h4>
            <p>Your payment has been processed successfully. You will be redirected to your dashboard shortly.</p>
            <div class="success-actions">
                <button class="btn btn-primary" onclick="redirectToDashboard()">
                    <i class="fas fa-tachometer-alt"></i>
                    Go to Dashboard
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://pay.google.com/gp/p/js/pay.js"></script>
<script>
let googlePaymentsClient;
let applePaySession;

document.addEventListener('DOMContentLoaded', function() {
    displayCartItems();
    updateOrderSummary();
    initializePaymentMethods();
    setupPaymentOptionHandlers();
    updateCartItemCount();
});

function displayCartItems() {
    cart.displayCartPageItems();
    updateCartItemCount();
    toggleClearCartButton();
}

function updateCartItemCount() {
    const count = cart.getItemCount();
    const itemCountElement = document.getElementById('cart-item-count');
    if (itemCountElement) {
        itemCountElement.textContent = `${count} ${count === 1 ? 'item' : 'items'}`;
    }
}

function toggleClearCartButton() {
    const clearCartBtn = document.getElementById('clear-cart-btn');
    if (clearCartBtn) {
        clearCartBtn.style.display = cart.getItemCount() > 0 ? 'inline-flex' : 'none';
    }
}

function updateOrderSummary() {
    cart.updateOrderSummary();
    updatePaymentButtons(cart.getTotal());
}

function setupPaymentOptionHandlers() {
    const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
    const googlePayButton = document.getElementById('google-pay-button');
    const applePayButton = document.getElementById('apple-pay-button');
    const checkoutBtn = document.getElementById('checkout-btn');
    
    paymentOptions.forEach(option => {
        option.addEventListener('change', function() {
            // Hide all payment buttons
            googlePayButton.style.display = 'none';
            applePayButton.style.display = 'none';
            checkoutBtn.style.display = 'block';
            
            // Show selected payment button
            if (this.value === 'GooglePay') {
                googlePayButton.style.display = 'block';
                checkoutBtn.style.display = 'none';
            } else if (this.value === 'ApplePay') {
                applePayButton.style.display = 'block';
                checkoutBtn.style.display = 'none';
            }
        });
    });
}

function initializePaymentMethods() {
    initializeGooglePay();
    initializeApplePay();
}

function initializeGooglePay() {
    const baseRequest = {
        apiVersion: 2,
        apiVersionMinor: 0
    };
    
    const allowedCardNetworks = ["AMEX", "DISCOVER", "JCB", "MASTERCARD", "VISA"];
    const allowedCardAuthMethods = ["PAN_ONLY", "CRYPTOGRAM_3DS"];
    
    const tokenizationSpecification = {
        type: 'PAYMENT_GATEWAY',
        parameters: {
            'gateway': 'example',
            'gatewayMerchantId': 'exampleGatewayMerchantId'
        }
    };
    
    const baseCardPaymentMethod = {
        type: 'CARD',
        parameters: {
            allowedAuthMethods: allowedCardAuthMethods,
            allowedCardNetworks: allowedCardNetworks
        }
    };
    
    const cardPaymentMethod = Object.assign(
        {},
        baseCardPaymentMethod,
        {
            tokenizationSpecification: tokenizationSpecification
        }
    );
    
    let paymentsClient = new google.payments.api.PaymentsClient({environment: 'TEST'});
    
    const button = paymentsClient.createButton({
        onClick: onGooglePaymentButtonClicked,
        allowedPaymentMethods: [cardPaymentMethod]
    });
    
    document.getElementById('google-pay-button').appendChild(button);
}

function onGooglePaymentButtonClicked() {
    const subtotal = cart.getTotal();
    const deliveryFee = 3.99;
    const tax = subtotal * 0.08;
    const total = subtotal + deliveryFee + tax;
    
    const paymentDataRequest = {
        apiVersion: 2,
        apiVersionMinor: 0,
        allowedPaymentMethods: [{
            type: 'CARD',
            parameters: {
                allowedAuthMethods: ["PAN_ONLY", "CRYPTOGRAM_3DS"],
                allowedCardNetworks: ["AMEX", "DISCOVER", "JCB", "MASTERCARD", "VISA"]
            },
            tokenizationSpecification: {
                type: 'PAYMENT_GATEWAY',
                parameters: {
                    'gateway': 'example',
                    'gatewayMerchantId': 'exampleGatewayMerchantId'
                }
            }
        }],
        merchantInfo: {
            merchantId: '12345678901234567890',
            merchantName: 'Taste Bud Hub'
        },
        transactionInfo: {
            totalPriceStatus: 'FINAL',
            totalPriceLabel: 'Total',
            totalPrice: total.toFixed(2),
            currencyCode: 'USD',
            countryCode: 'US'
        }
    };
    
    let paymentsClient = new google.payments.api.PaymentsClient({environment: 'TEST'});
    
    paymentsClient.loadPaymentData(paymentDataRequest)
        .then(function(paymentData) {
            processPayment(paymentData, 'GooglePay');
        })
        .catch(function(err) {
            console.error('Google Pay error:', err);
        });
}

function initializeApplePay() {
    if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
        document.getElementById('apple-pay-button').querySelector('.apple-pay-btn').addEventListener('click', onApplePayButtonClicked);
    } else {
        // Hide Apple Pay option if not supported
        document.getElementById('apple-pay').closest('.payment-option').style.display = 'none';
    }
}

function onApplePayButtonClicked() {
    const subtotal = cart.getTotal();
    const deliveryFee = 3.99;
    const tax = subtotal * 0.08;
    const total = subtotal + deliveryFee + tax;
    
    const request = {
        countryCode: 'US',
        currencyCode: 'USD',
        supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
        merchantCapabilities: ['supports3DS'],
        total: {
            label: 'Taste Bud Hub',
            amount: total.toFixed(2)
        }
    };
    
    const session = new ApplePaySession(3, request);
    
    session.onvalidatemerchant = function(event) {
        // In a real implementation, you would validate with your server
        console.log('Validate merchant:', event);
    };
    
    session.onpaymentauthorized = function(event) {
        processPayment(event.payment, 'ApplePay');
        session.completePayment(ApplePaySession.STATUS_SUCCESS);
    };
    
    session.begin();
}

function processPayment(paymentData, paymentMethod) {
    // Show payment success modal immediately for better UX
    showPaymentSuccess();
    
    // Process the order
    const form = document.getElementById('checkout-form');
    const formData = new FormData(form);
    
    // Add cart data and payment method
    formData.append('cart_data', JSON.stringify(getCart()));
    formData.append('payment_method', paymentMethod);
    formData.append('payment_data', JSON.stringify(paymentData));
    
    fetch('process_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear cart
            localStorage.removeItem('cart');
            updateCartCount();
            
            // Redirect after delay
            setTimeout(() => {
                window.location.href = `order_success.php?order_id=${data.order_id}`;
            }, 3000);
        } else {
            alert('Order processing failed. Please try again.');
            document.getElementById('payment-success-modal').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Order processing failed. Please try again.');
        document.getElementById('payment-success-modal').style.display = 'none';
    });
}

function showPaymentSuccess() {
    document.getElementById('payment-success-modal').style.display = 'flex';
}

function redirectToDashboard() {
    window.location.href = 'dashboard.php';
}

function updatePaymentButtons(total) {
    // Update button amounts if needed
    // This is where you'd update the display amounts for payment buttons
}

// Handle form submission for regular checkout
document.getElementById('checkout-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const cartItems = getCart();
    if (cartItems.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (paymentMethod === 'Card') {
        // Show payment success for card payment too
        showPaymentSuccess();
        
        // Add cart data to form
        const cartInput = document.createElement('input');
        cartInput.type = 'hidden';
        cartInput.name = 'cart_data';
        cartInput.value = JSON.stringify(cartItems);
        this.appendChild(cartInput);
        
        // Submit form normally for card payment
        fetch('process_order.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear cart
                localStorage.removeItem('cart');
                updateCartCount();
                
                // Redirect after delay
                setTimeout(() => {
                    window.location.href = `order_success.php?order_id=${data.order_id}`;
                }, 3000);
            } else {
                alert('Order processing failed. Please try again.');
                document.getElementById('payment-success-modal').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Order processing failed. Please try again.');
            document.getElementById('payment-success-modal').style.display = 'none';
        });
    }
});

// Listen for cart updates
window.addEventListener('cartUpdated', function() {
    displayCartItems();
    updateOrderSummary();
});
</script>

<style>
.cart-page {
    padding: 2rem 1rem;
    min-height: 80vh;
}

.cart-header {
    text-align: center;
    margin-bottom: 3rem;
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: var(--shadow-sm);
}

.cart-header h1 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
    font-size: 2.5rem;
}

.cart-header p {
    margin: 0 0 1.5rem 0;
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.cart-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.cart-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    align-items: start;
}

.cart-items-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-sm);
}

.cart-items-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border-color);
}

.cart-items-header h3 {
    margin: 0;
    color: var(--text-primary);
    font-size: 1.5rem;
}

.item-count {
    background: var(--primary-color);
    color: var(--text-primary);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.cart-item-page {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    border: 2px solid var(--border-color);
    border-radius: 15px;
    margin-bottom: 1.5rem;
    transition: var(--transition);
    background: var(--bg-light);
}

.cart-item-page:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary-color);
}

.cart-item-page .item-image {
    flex-shrink: 0;
}

.cart-item-page .item-image img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid var(--border-color);
}

.cart-item-page .item-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.cart-item-page .item-details h4 {
    margin: 0;
    color: var(--text-primary);
    font-size: 1.25rem;
    font-weight: 600;
}

.item-price-single {
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin: 0;
}

.item-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: white;
    padding: 0.5rem;
    border-radius: 10px;
    border: 2px solid var(--border-color);
}

.quantity-controls .qty-btn {
    width: 35px;
    height: 35px;
    border: none;
    background: var(--primary-color);
    color: var(--text-primary);
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    transition: var(--transition);
}

.quantity-controls .qty-btn:hover {
    background: var(--secondary-color);
    transform: scale(1.1);
}

.quantity-input {
    width: 60px;
    text-align: center;
    border: none;
    background: transparent;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.quantity-input:focus {
    outline: none;
}

.item-total-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--success-color);
}

.remove-item-page {
    background: var(--error-color);
    color: white;
    border: none;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    transition: var(--transition);
    align-self: flex-start;
}

.remove-item-page:hover {
    background: #DC2626;
    transform: translateY(-1px);
}

.order-summary {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-sm);
    position: sticky;
    top: 2rem;
}

.order-summary h3 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
    font-size: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border-color);
}

.summary-details {
    margin-bottom: 2rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    color: var(--text-secondary);
}

.summary-row.total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    padding-top: 1rem;
}

.delivery-info,
.payment-section {
    margin: 2rem 0;
}

.delivery-info h4,
.payment-section h4 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
    font-weight: 600;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid var(--border-color);
    border-radius: 10px;
    font-family: inherit;
    transition: var(--transition);
    font-size: 1rem;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.payment-options {
    display: grid;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.payment-option {
    position: relative;
}

.payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.payment-label {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 2px solid var(--border-color);
    border-radius: 10px;
    cursor: pointer;
    transition: var(--transition);
    background: white;
}

.payment-option input[type="radio"]:checked + .payment-label {
    border-color: var(--primary-color);
    background: rgba(255, 215, 0, 0.1);
}

.payment-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
}

.google-pay {
    background: linear-gradient(135deg, #4285f4, #34a853);
}

.apple-pay {
    background: linear-gradient(135deg, #000, #333);
}

.card-pay {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.payment-button {
    margin-bottom: 1rem;
}

.apple-pay-btn {
    width: 100%;
    background: #000;
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: var(--transition);
}

.apple-pay-btn:hover {
    background: #333;
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
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    text-align: center;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    padding: 2rem 2rem 1rem;
}

.modal-header h3 {
    margin: 0;
    color: var(--text-primary);
    font-size: 1.5rem;
}

.modal-body {
    padding: 1rem 2rem 2rem;
}

.success-animation {
    text-align: center;
    margin-bottom: 1.5rem;
}

.success-animation i {
    font-size: 4rem;
    color: var(--success-color);
    animation: successPulse 1s ease-in-out;
}

@keyframes successPulse {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

.success-actions {
    text-align: center;
    margin-top: 1.5rem;
}

.empty-cart-message {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
}

.empty-cart-message i {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    opacity: 0.5;
    color: var(--primary-color);
}

.empty-cart-message h3 {
    margin-bottom: 1rem;
    color: var(--text-primary);
}

@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .cart-header h1 {
        font-size: 2rem;
    }
    
    .cart-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .cart-item-page {
        flex-direction: column;
        text-align: center;
    }
    
    .cart-item-page .item-image img {
        width: 100px;
        height: 100px;
    }
    
    .item-controls {
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }
    
    .order-summary {
        position: static;
    }
}

@media (max-width: 480px) {
    .cart-page {
        padding: 1rem 0.5rem;
    }
    
    .cart-header,
    .cart-items-section,
    .order-summary {
        padding: 1.5rem;
    }
    
    .cart-items-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .quantity-controls {
        gap: 0.5rem;
    }
    
    .quantity-controls .qty-btn {
        width: 30px;
        height: 30px;
    }
    
    .quantity-input {
        width: 50px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>