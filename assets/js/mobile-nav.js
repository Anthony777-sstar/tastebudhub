// Mobile Navigation JavaScript
class MobileNavigation {
    constructor() {
        this.hamburger = document.getElementById('hamburger');
        this.mobileMenu = document.getElementById('mobile-menu');
        this.mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        this.mobileMenuClose = document.getElementById('mobile-menu-close');
        this.mobileSearch = document.getElementById('mobile-search');
        this.mobileCartCount = document.getElementById('mobile-cart-count');
        
        this.isOpen = false;
        this.focusableElements = [];
        this.lastFocusedElement = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateCartCount();
        this.setupFocusManagement();
    }
    
    bindEvents() {
        // Hamburger click
        if (this.hamburger) {
            this.hamburger.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleMenu();
            });
        }
        
        // Close button click
        if (this.mobileMenuClose) {
            this.mobileMenuClose.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeMenu();
            });
        }
        
        // Overlay click
        if (this.mobileMenuOverlay) {
            this.mobileMenuOverlay.addEventListener('click', () => {
                this.closeMenu();
            });
        }
        
        // Mobile search
        if (this.mobileSearch) {
            this.mobileSearch.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.handleMobileSearch();
                }
            });
        }
        
        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeMenu();
            }
        });
        
        // Window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1023 && this.isOpen) {
                this.closeMenu();
            }
        });
        
        // Cart count updates
        window.addEventListener('cartUpdated', () => {
            this.updateCartCount();
        });
    }
    
    setupFocusManagement() {
        if (!this.mobileMenu) return;
        
        // Get all focusable elements in mobile menu
        this.focusableElements = this.mobileMenu.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
    }
    
    toggleMenu() {
        if (this.isOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }
    
    openMenu() {
        if (!this.mobileMenu || !this.mobileMenuOverlay || !this.hamburger) return;
        
        this.isOpen = true;
        this.lastFocusedElement = document.activeElement;
        
        // Add active classes
        this.hamburger.classList.add('active');
        this.hamburger.setAttribute('aria-expanded', 'true');
        this.mobileMenu.classList.add('active');
        this.mobileMenuOverlay.classList.add('active');
        
        // Prevent body scroll
        document.body.classList.add('mobile-menu-open');
        
        // Focus management
        setTimeout(() => {
            if (this.mobileMenuClose) {
                this.mobileMenuClose.focus();
            }
        }, 300);
        
        // Trap focus
        this.trapFocus();
        
        // Announce to screen readers
        this.announceMenuState('opened');
    }
    
    closeMenu() {
        if (!this.mobileMenu || !this.mobileMenuOverlay || !this.hamburger) return;
        
        this.isOpen = false;
        
        // Remove active classes
        this.hamburger.classList.remove('active');
        this.hamburger.setAttribute('aria-expanded', 'false');
        this.mobileMenu.classList.remove('active');
        this.mobileMenuOverlay.classList.remove('active');
        
        // Restore body scroll
        document.body.classList.remove('mobile-menu-open');
        
        // Restore focus
        if (this.lastFocusedElement) {
            this.lastFocusedElement.focus();
        }
        
        // Remove focus trap
        this.removeFocusTrap();
        
        // Announce to screen readers
        this.announceMenuState('closed');
    }
    
    trapFocus() {
        if (this.focusableElements.length === 0) return;
        
        const firstElement = this.focusableElements[0];
        const lastElement = this.focusableElements[this.focusableElements.length - 1];
        
        this.focusTrapHandler = (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    // Shift + Tab
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    // Tab
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        };
        
        document.addEventListener('keydown', this.focusTrapHandler);
    }
    
    removeFocusTrap() {
        if (this.focusTrapHandler) {
            document.removeEventListener('keydown', this.focusTrapHandler);
            this.focusTrapHandler = null;
        }
    }
    
    handleMobileSearch() {
        const searchTerm = this.mobileSearch.value.trim();
        if (searchTerm) {
            this.closeMenu();
            window.location.href = `menu.php?search=${encodeURIComponent(searchTerm)}`;
        }
    }
    
    updateCartCount() {
        const cart = this.getCart();
        const count = cart.reduce((total, item) => total + item.quantity, 0);
        
        if (this.mobileCartCount) {
            this.mobileCartCount.textContent = count;
            this.mobileCartCount.style.display = count > 0 ? 'block' : 'none';
        }
    }
    
    getCart() {
        try {
            return JSON.parse(localStorage.getItem('cart')) || [];
        } catch (e) {
            return [];
        }
    }
    
    announceMenuState(state) {
        // Create announcement for screen readers
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = `Navigation menu ${state}`;
        
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }
}

// Initialize mobile navigation when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.mobileNav = new MobileNavigation();
});

// Export for external use
window.MobileNavigation = MobileNavigation;

// Screen reader only class
const srOnlyStyles = document.createElement('style');
srOnlyStyles.textContent = `
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
`;
document.head.appendChild(srOnlyStyles);