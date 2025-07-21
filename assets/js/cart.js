// Enhanced Cart functionality with full editing capabilities
class Cart {
    constructor() {
        this.items = this.loadCart();
        this.updateDisplay();
        this.bindEvents();
    }
    
    loadCart() {
        try {
            return JSON.parse(localStorage.getItem('cart')) || [];
        } catch (e) {
            return [];
        }
    }
    
    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.items));
        this.updateCartCount();
        this.updateSidebarDisplay();
        this.updateCartPageDisplay();
        this.dispatchCartUpdate();
    }
    
    addItem(id, name, price, image, quantity = 1) {
        const existingItem = this.items.find(item => item.id === id);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.items.push({
                id: parseInt(id),
                name: name,
                price: parseFloat(price),
                image: image,
                quantity: quantity
            });
        }
        
        this.saveCart();
        this.showAddedToCartMessage(name);
    }
    
    removeItem(id) {
        this.items = this.items.filter(item => item.id !== parseInt(id));
        this.saveCart();
        this.showRemovedFromCartMessage();
    }
    
    updateQuantity(id, quantity) {
        const item = this.items.find(item => item.id === parseInt(id));
        if (item) {
            if (quantity <= 0) {
                this.removeItem(id);
            } else {
                item.quantity = quantity;
                this.saveCart();
            }
        }
    }
    
    increaseQuantity(id) {
        const item = this.items.find(item => item.id === parseInt(id));
        if (item) {
            item.quantity += 1;
            this.saveCart();
        }
    }
    
    decreaseQuantity(id) {
        const item = this.items.find(item => item.id === parseInt(id));
        if (item) {
            if (item.quantity > 1) {
                item.quantity -= 1;
                this.saveCart();
            } else {
                this.removeItem(id);
            }
        }
    }
    
    getTotal() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }
    
    getItemCount() {
        return this.items.reduce((total, item) => total + item.quantity, 0);
    }
    
    clear() {
        this.items = [];
        this.saveCart();
    }
    
    updateCartCount() {
        const count = this.getItemCount();
        
        // Update main cart count
        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = count;
            cartCountElement.style.display = count > 0 ? 'block' : 'none';
            
            // Add animation when count changes
            if (count > 0) {
                cartCountElement.style.animation = 'cartCountPulse 0.3s ease';
                setTimeout(() => {
                    cartCountElement.style.animation = '';
                }, 300);
            }
        }
        
        // Update mobile cart count
        const mobileCartCount = document.getElementById('mobile-cart-count');
        if (mobileCartCount) {
            mobileCartCount.textContent = count;
            mobileCartCount.style.display = count > 0 ? 'block' : 'none';
        }
        
        // Update dashboard cart count
        const dashboardCartCount = document.getElementById('cart-count-dashboard');
        if (dashboardCartCount) {
            dashboardCartCount.textContent = count;
        }
    }
    
    updateSidebarDisplay() {
        const cartItemsContainer = document.getElementById('cart-items');
        const cartTotalElement = document.getElementById('cart-total');
        
        if (!cartItemsContainer) return;
        
        if (this.items.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="empty-cart-message">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Add some delicious items to get started!</p>
                </div>
            `;
        } else {
            cartItemsContainer.innerHTML = this.items.map(item => `
                <div class="cart-item" data-id="${item.id}">
                    <img src="${item.image}" alt="${item.name}">
                    <div class="cart-item-info">
                        <h4>${item.name}</h4>
                        <div class="cart-item-controls">
                            <div class="cart-quantity">
                                <button class="qty-btn minus" onclick="cart.decreaseQuantity(${item.id})" title="Decrease quantity">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="quantity-display">${item.quantity}</span>
                                <button class="qty-btn plus" onclick="cart.increaseQuantity(${item.id})" title="Increase quantity">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="cart-item-price">$${(item.price * item.quantity).toFixed(2)}</div>
                        </div>
                    </div>
                    <button class="remove-item" onclick="cart.removeItem(${item.id})" title="Remove item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `).join('');
        }
        
        if (cartTotalElement) {
            cartTotalElement.textContent = this.getTotal().toFixed(2);
        }
    }
    
    updateCartPageDisplay() {
        // Update cart page if we're on it
        const cartItemsList = document.getElementById('cart-items-list');
        if (cartItemsList) {
            this.displayCartPageItems();
        }
    }
    
    displayCartPageItems() {
        const cartItemsList = document.getElementById('cart-items-list');
        if (!cartItemsList) return;
        
        if (this.items.length === 0) {
            cartItemsList.innerHTML = `
                <div class="empty-cart-message">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Add some delicious items to get started!</p>
                    <a href="menu.php" class="btn btn-primary">Browse Menu</a>
                </div>
            `;
            return;
        }
        
        let html = '';
        this.items.forEach(item => {
            html += `
                <div class="cart-item-page" data-id="${item.id}">
                    <div class="item-image">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="item-details">
                        <h4>${item.name}</h4>
                        <p class="item-price-single">$${item.price.toFixed(2)} each</p>
                        <div class="item-controls">
                            <div class="quantity-controls">
                                <button class="qty-btn" onclick="cart.decreaseQuantity(${item.id})" title="Decrease quantity">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" 
                                       value="${item.quantity}" 
                                       min="1" 
                                       max="99"
                                       class="quantity-input"
                                       onchange="cart.updateQuantity(${item.id}, parseInt(this.value))"
                                       onblur="cart.validateQuantity(this, ${item.id})">
                                <button class="qty-btn" onclick="cart.increaseQuantity(${item.id})" title="Increase quantity">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="item-total-price">$${(item.price * item.quantity).toFixed(2)}</div>
                        </div>
                    </div>
                    <button class="remove-item-page" onclick="cart.removeItemWithConfirmation(${item.id})" title="Remove item">
                        <i class="fas fa-trash"></i>
                        <span>Remove</span>
                    </button>
                </div>
            `;
        });
        
        cartItemsList.innerHTML = html;
        
        // Update order summary
        this.updateOrderSummary();
    }
    
    updateOrderSummary() {
        const subtotal = this.getTotal();
        const deliveryFee = this.items.length > 0 ? 3.99 : 0;
        const tax = subtotal * 0.08; // 8% tax
        const total = subtotal + deliveryFee + tax;
        
        const subtotalElement = document.getElementById('subtotal');
        const deliveryFeeElement = document.getElementById('delivery-fee');
        const taxElement = document.getElementById('tax');
        const orderTotalElement = document.getElementById('order-total');
        const checkoutBtn = document.getElementById('checkout-btn');
        
        if (subtotalElement) subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
        if (deliveryFeeElement) deliveryFeeElement.textContent = `$${deliveryFee.toFixed(2)}`;
        if (taxElement) taxElement.textContent = `$${tax.toFixed(2)}`;
        if (orderTotalElement) orderTotalElement.textContent = `$${total.toFixed(2)}`;
        
        // Enable/disable checkout button
        if (checkoutBtn) {
            checkoutBtn.disabled = this.items.length === 0;
        }
    }
    
    validateQuantity(input, itemId) {
        let quantity = parseInt(input.value);
        if (isNaN(quantity) || quantity < 1) {
            quantity = 1;
            input.value = 1;
        } else if (quantity > 99) {
            quantity = 99;
            input.value = 99;
        }
        this.updateQuantity(itemId, quantity);
    }
    
    removeItemWithConfirmation(id) {
        const item = this.items.find(item => item.id === parseInt(id));
        if (item) {
            if (confirm(`Remove "${item.name}" from your cart?`)) {
                this.removeItem(id);
            }
        }
    }
    
    updateDisplay() {
        this.updateCartCount();
        this.updateSidebarDisplay();
        this.updateCartPageDisplay();
    }
    
    bindEvents() {
        // Close cart when clicking overlay
        const cartOverlay = document.getElementById('cart-overlay');
        if (cartOverlay) {
            cartOverlay.addEventListener('click', () => {
                this.closeCart();
            });
        }
        
        // Handle escape key to close cart
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const cartSidebar = document.getElementById('cart-sidebar');
                if (cartSidebar && cartSidebar.classList.contains('active')) {
                    this.closeCart();
                }
            }
        });
    }
    
    openCart() {
        const cartSidebar = document.getElementById('cart-sidebar');
        const cartOverlay = document.getElementById('cart-overlay');
        
        if (cartSidebar && cartOverlay) {
            cartSidebar.classList.add('active');
            cartOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            this.updateSidebarDisplay(); // Refresh display when opening
        }
    }
    
    closeCart() {
        const cartSidebar = document.getElementById('cart-sidebar');
        const cartOverlay = document.getElementById('cart-overlay');
        
        if (cartSidebar && cartOverlay) {
            cartSidebar.classList.remove('active');
            cartOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    toggleCart() {
        const cartSidebar = document.getElementById('cart-sidebar');
        
        if (cartSidebar) {
            const isActive = cartSidebar.classList.contains('active');
            
            if (isActive) {
                this.closeCart();
            } else {
                this.openCart();
            }
        }
    }
    
    showAddedToCartMessage(itemName) {
        this.showNotification(`${itemName} added to cart!`, 'success', 'fa-check-circle');
    }
    
    showRemovedFromCartMessage() {
        this.showNotification('Item removed from cart', 'info', 'fa-info-circle');
    }
    
    showNotification(message, type = 'info', icon = 'fa-info-circle') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${this.getNotificationColor(type)};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 15px;
            z-index: 10000;
            animation: slideInRight 0.3s ease-out;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            max-width: 350px;
            min-width: 250px;
        `;
        notification.innerHTML = `
            <i class="fas ${icon}" style="font-size: 1.25rem;"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideInRight 0.3s ease-out reverse';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    getNotificationColor(type) {
        switch (type) {
            case 'success': return 'linear-gradient(135deg, var(--success-color), #059669)';
            case 'error': return 'linear-gradient(135deg, var(--error-color), #DC2626)';
            case 'warning': return 'linear-gradient(135deg, var(--warning-color), #D97706)';
            default: return 'linear-gradient(135deg, var(--secondary-color), #6366f1)';
        }
    }
    
    dispatchCartUpdate() {
        // Dispatch custom event for cart updates
        window.dispatchEvent(new CustomEvent('cartUpdated', {
            detail: {
                items: this.items,
                count: this.getItemCount(),
                total: this.getTotal()
            }
        }));
    }
    
    proceedToCheckout() {
        if (this.getItemCount() === 0) {
            this.showNotification('Your cart is empty!', 'warning', 'fa-exclamation-triangle');
            return;
        }
        
        // Close cart sidebar
        this.closeCart();
        
        // Check if user is logged in
        fetch('auth/check_login.php')
            .then(response => response.json())
            .then(data => {
                if (data.logged_in) {
                    window.location.href = 'cart.php';
                } else {
                    window.location.href = 'auth/login.php?redirect=cart.php';
                }
            })
            .catch(() => {
                // Fallback - assume not logged in
                window.location.href = 'auth/login.php?redirect=cart.php';
            });
    }
}

// Initialize cart
const cart = new Cart();

// Global functions for adding items to cart
window.addToCart = function(id, name, price, image, quantity = 1) {
    cart.addItem(id, name, price, image, quantity);
};

window.removeFromCart = function(id) {
    cart.removeItem(id);
};

window.updateCartQuantity = function(id, quantity) {
    cart.updateQuantity(id, quantity);
};

window.increaseCartQuantity = function(id) {
    cart.increaseQuantity(id);
};

window.decreaseCartQuantity = function(id) {
    cart.decreaseQuantity(id);
};

window.clearCart = function() {
    if (cart.getItemCount() > 0) {
        if (confirm('Are you sure you want to clear your entire cart?')) {
            cart.clear();
            cart.showNotification('Cart cleared', 'info', 'fa-trash');
        }
    }
};

window.getCart = function() {
    return cart.items;
};

window.getCartTotal = function() {
    return cart.getTotal();
};

window.getCartItemCount = function() {
    return cart.getItemCount();
};

// Cart sidebar toggle
window.toggleCart = function() {
    cart.toggleCart();
};

// Proceed to checkout
window.proceedToCheckout = function() {
    cart.proceedToCheckout();
};

// Update cart count function
window.updateCartCount = function() {
    cart.updateCartCount();
};

// Initialize cart display when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    cart.updateDisplay();
    
    // Add click handlers for cart icon - will be overridden in menu.php for auth check
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon && !cartIcon.onclick) {
        cartIcon.addEventListener('click', () => cart.toggleCart());
    }
    
    // Add click handler for mobile cart
    const mobileCart = document.querySelector('.mobile-cart');
    if (mobileCart) {
        mobileCart.addEventListener('click', () => cart.toggleCart());
    }
});

// Auto-save cart periodically (in case of browser issues)
setInterval(() => {
    if (cart.items.length > 0) {
        localStorage.setItem('cart', JSON.stringify(cart.items));
    }
}, 30000); // Save every 30 seconds

// Handle page visibility change to save cart
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden' && cart.items.length > 0) {
        localStorage.setItem('cart', JSON.stringify(cart.items));
    }
});

// Export cart instance for external use
window.cart = cart;