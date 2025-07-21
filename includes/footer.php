<footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>🍽️ Taste Bud Hub</h3>
                <p>Your go-to destination for exploring and ordering delicious meals from the comfort of your home.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Delivery Areas</h4>
                <ul>
                    <li>Downtown</li>
                    <li>Uptown</li>
                    <li>Suburbs</li>
                    <li>Business District</li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Contact Info</h4>
                <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                <p><i class="fas fa-envelope"></i> info@tastebudhub.com</p>
                <p><i class="fas fa-map-marker-alt"></i> 123 Food Street, City</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 Taste Bud Hub. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- Enhanced Cart Sidebar -->
    <div class="cart-sidebar" id="cart-sidebar">
        <div class="cart-header">
            <h3><i class="fas fa-shopping-cart"></i> Your Order</h3>
            <button class="close-cart" onclick="toggleCart()" title="Close cart">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="cart-items" id="cart-items">
            <p class="empty-cart">Your cart is empty</p>
        </div>
        
        <div class="cart-footer">
            <div class="cart-total">
                <strong>Total: $<span id="cart-total">0.00</span></strong>
            </div>
            <div class="cart-actions">
                <button class="btn btn-outline btn-sm" onclick="clearCart()" id="sidebar-clear-btn" style="display: none;">
                    <i class="fas fa-trash"></i>
                    Clear
                </button>
                <button class="btn btn-primary btn-full" onclick="proceedToCheckout()">
                    <i class="fas fa-credit-card"></i>
                    Checkout
                </button>
            </div>
        </div>
    </div>
    
    <div class="cart-overlay" id="cart-overlay" onclick="toggleCart()"></div>
    
    <script src="assets/js/cart.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/mobile-nav.js"></script>
    
    <style>
    /* Enhanced Cart Sidebar Styles */
    .cart-sidebar {
        position: fixed;
        top: 0;
        right: -400px;
        width: 400px;
        height: 100vh;
        background: white;
        box-shadow: var(--shadow-lg);
        z-index: 10000;
        transition: right 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    .cart-sidebar.active {
        right: 0;
    }
    
    .cart-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .cart-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .cart-header {
        padding: 1.5rem;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bg-light);
    }
    
    .cart-header h3 {
        margin: 0;
        color: var(--text-primary);
        font-size: 1.25rem;
    }
    
    .close-cart {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-secondary);
        padding: 0.5rem;
        border-radius: 8px;
        transition: var(--transition);
    }
    
    .close-cart:hover {
        background: var(--border-color);
        color: var(--text-primary);
    }
    
    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }
    
    .cart-items::-webkit-scrollbar {
        width: 6px;
    }
    
    .cart-items::-webkit-scrollbar-track {
        background: var(--bg-light);
    }
    
    .cart-items::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }
    
    .cart-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        margin-bottom: 1rem;
        background: var(--bg-light);
        transition: var(--transition);
    }
    
    .cart-item:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
    
    .cart-item img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }
    
    .cart-item-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .cart-item-info h4 {
        margin: 0;
        font-size: 0.95rem;
        color: var(--text-primary);
        font-weight: 600;
    }
    
    .cart-item-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .cart-quantity {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        padding: 0.25rem;
        border-radius: 6px;
        border: 1px solid var(--border-color);
    }
    
    .cart-quantity .qty-btn {
        width: 24px;
        height: 24px;
        border: none;
        background: var(--primary-color);
        color: var(--text-primary);
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        transition: var(--transition);
    }
    
    .cart-quantity .qty-btn:hover {
        background: var(--secondary-color);
        transform: scale(1.1);
    }
    
    .cart-quantity .qty-btn.minus:hover {
        background: var(--error-color);
    }
    
    .cart-quantity .qty-btn.plus:hover {
        background: var(--success-color);
    }
    
    .quantity-display {
        font-weight: 600;
        color: var(--text-primary);
        min-width: 20px;
        text-align: center;
        font-size: 0.9rem;
    }
    
    .cart-item-price {
        font-weight: 600;
        color: var(--success-color);
        font-size: 0.9rem;
    }
    
    .remove-item {
        background: var(--error-color);
        color: white;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        align-self: flex-start;
    }
    
    .remove-item:hover {
        background: #DC2626;
        transform: scale(1.1);
    }
    
    .cart-footer {
        padding: 1.5rem;
        border-top: 2px solid var(--border-color);
        background: var(--bg-light);
    }
    
    .cart-total {
        text-align: center;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        color: var(--text-primary);
    }
    
    .cart-actions {
        display: flex;
        gap: 0.75rem;
        flex-direction: column;
    }
    
    .empty-cart {
        text-align: center;
        color: var(--text-secondary);
        padding: 2rem 1rem;
        font-style: italic;
    }
    
    .empty-cart-message {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--text-secondary);
    }
    
    .empty-cart-message i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
        color: var(--primary-color);
    }
    
    .empty-cart-message h3 {
        margin-bottom: 0.5rem;
        color: var(--text-primary);
        font-size: 1.1rem;
    }
    
    .empty-cart-message p {
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }
    
    /* Mobile Cart Sidebar */
    @media (max-width: 768px) {
        .cart-sidebar {
            width: 100%;
            right: -100%;
        }
        
        .cart-header {
            padding: 1rem;
        }
        
        .cart-item {
            padding: 0.75rem;
        }
        
        .cart-item img {
            width: 50px;
            height: 50px;
        }
        
        .cart-item-info h4 {
            font-size: 0.9rem;
        }
        
        .cart-quantity .qty-btn {
            width: 28px;
            height: 28px;
        }
        
        .cart-footer {
            padding: 1rem;
        }
    }
    
    /* Animation for cart count */
    @keyframes cartCountPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    /* Update cart count visibility */
    .cart-count {
        display: none;
        position: absolute;
        top: -8px;
        right: -8px;
        background: var(--error-color);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
    }
    
    .mobile-cart-count {
        display: none;
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--error-color);
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
    }
    </style>
    
    <script>
    // Update sidebar clear button visibility
    function updateSidebarClearButton() {
        const sidebarClearBtn = document.getElementById('sidebar-clear-btn');
        if (sidebarClearBtn) {
            sidebarClearBtn.style.display = cart.getItemCount() > 0 ? 'inline-flex' : 'none';
        }
    }
    
    // Listen for cart updates to show/hide clear button
    window.addEventListener('cartUpdated', updateSidebarClearButton);
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', updateSidebarClearButton);
    </script>
</body>
</html>