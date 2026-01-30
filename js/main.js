/**
 * Grab & Go - Main JavaScript
 * Core functionality and utilities
 */

// ============================================
// Cart Management
// ============================================
const Cart = {
    // Add item to cart
    addItem: async function (productId, quantity = 1) {
        try {
            const response = await fetch('/Mini%20Project/cart/cart-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (data.success) {
                this.updateCartBadge(data.cart_count);
                this.showNotification('Item added to cart!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to add item', 'error');
            }

            return data;
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('An error occurred', 'error');
        }
    },

    // Update item quantity
    updateQuantity: async function (cartId, quantity) {
        try {
            const response = await fetch('/Mini%20Project/cart/cart-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    cart_id: cartId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (data.success) {
                location.reload(); // Reload to update totals
            }

            return data;
        } catch (error) {
            console.error('Error updating quantity:', error);
        }
    },

    // Remove item from cart
    removeItem: async function (cartId) {
        if (!confirm('Remove this item from cart?')) return;

        try {
            const response = await fetch('/Mini%20Project/cart/cart-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    cart_id: cartId
                })
            });

            const data = await response.json();

            if (data.success) {
                location.reload();
            }

            return data;
        } catch (error) {
            console.error('Error removing item:', error);
        }
    },

    // Update cart badge count
    updateCartBadge: function (count) {
        const badge = document.querySelector('.cart-badge');
        if (badge) {
            badge.textContent = count;
        }
    },

    // Show notification
    showNotification: function (message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.minWidth = '250px';

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
};

// ============================================
// Form Validation
// ============================================
const FormValidator = {
    validateEmail: function (email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    validatePhone: function (phone) {
        const re = /^[\d\s\-\+\(\)]{10,}$/;
        return re.test(phone);
    },

    validateRequired: function (value) {
        return value.trim() !== '';
    },

    showError: function (input, message) {
        const formGroup = input.closest('.form-group');
        let errorDiv = formGroup.querySelector('.form-error');

        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'form-error';
            formGroup.appendChild(errorDiv);
        }

        errorDiv.textContent = message;
        input.style.borderColor = 'var(--color-error)';
    },

    clearError: function (input) {
        const formGroup = input.closest('.form-group');
        const errorDiv = formGroup.querySelector('.form-error');

        if (errorDiv) {
            errorDiv.remove();
        }

        input.style.borderColor = '';
    }
};

// ============================================
// Quantity Controls
// ============================================
function initQuantityControls() {
    document.querySelectorAll('.quantity-control').forEach(control => {
        const minusBtn = control.querySelector('.quantity-minus');
        const plusBtn = control.querySelector('.quantity-plus');
        const valueSpan = control.querySelector('.quantity-value');
        const cartId = control.dataset.cartId;

        if (minusBtn) {
            minusBtn.addEventListener('click', () => {
                let value = parseInt(valueSpan.textContent);
                if (value > 1) {
                    value--;
                    valueSpan.textContent = value;
                    if (cartId) {
                        Cart.updateQuantity(cartId, value);
                    }
                }
            });
        }

        if (plusBtn) {
            plusBtn.addEventListener('click', () => {
                let value = parseInt(valueSpan.textContent);
                value++;
                valueSpan.textContent = value;
                if (cartId) {
                    Cart.updateQuantity(cartId, value);
                }
            });
        }
    });
}

// ============================================
// Add to Cart Buttons
// ============================================
function initAddToCartButtons() {
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const productId = button.dataset.productId;
            const quantity = button.dataset.quantity || 1;

            button.disabled = true;
            button.textContent = 'Adding...';

            await Cart.addItem(productId, quantity);

            button.disabled = false;
            button.textContent = 'Add to Cart';
        });
    });
}

// ============================================
// Filter Controls
// ============================================
function initFilters() {
    // Category checkboxes
    document.querySelectorAll('.category-filter').forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });


    // Dietary filters
    document.querySelectorAll('.dietary-filter').forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });
}


function applyFilters() {
    const categories = Array.from(document.querySelectorAll('.category-filter:checked'))
        .map(cb => cb.value);

    const dietary = Array.from(document.querySelectorAll('.dietary-filter:checked'))
        .map(cb => cb.value);

    // Build query string
    const params = new URLSearchParams();
    if (categories.length) params.append('categories', categories.join(','));
    if (dietary.length) params.append('dietary', dietary.join(','));

    // Reload page with filters
    window.location.href = '?' + params.toString();
}

// ============================================
// Initialize on DOM Ready
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    initQuantityControls();
    initAddToCartButtons();
    initFilters();

    // Form validation on submit
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function (e) {
            let isValid = true;

            // Validate required fields
            form.querySelectorAll('[required]').forEach(input => {
                if (!FormValidator.validateRequired(input.value)) {
                    FormValidator.showError(input, 'This field is required');
                    isValid = false;
                } else {
                    FormValidator.clearError(input);
                }
            });

            // Validate email fields
            form.querySelectorAll('input[type="email"]').forEach(input => {
                if (input.value && !FormValidator.validateEmail(input.value)) {
                    FormValidator.showError(input, 'Please enter a valid email');
                    isValid = false;
                }
            });

            // Validate phone fields
            form.querySelectorAll('input[type="tel"]').forEach(input => {
                if (input.value && !FormValidator.validatePhone(input.value)) {
                    FormValidator.showError(input, 'Please enter a valid phone number');
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });
});
