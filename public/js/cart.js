// public/js/cart.js

/**
 * Клас для управління кошиком
 */
class CartManager {
    constructor() {
        this.items = [];
        this.init();
    }

    /**
     * Ініціалізація
     */
    init() {
        this.loadFromStorage();
        this.setupCheckoutButton();
        this.updateCartCount();
        this.renderCart();
    }

    /**
     * Завантажити кошик з localStorage
     */
    loadFromStorage() {
        const saved = localStorage.getItem('cart');
        if (saved) {
            try {
                this.items = JSON.parse(saved);
            } catch (error) {
                console.error('Error loading cart:', error);
                this.items = [];
            }
        }
    }

    /**
     * Зберегти кошик в localStorage
     */
    saveToStorage() {
        localStorage.setItem('cart', JSON.stringify(this.items));
    }

    /**
     * Додати товар в кошик
     */
    addToCart(product) {
        const existingItem = this.items.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity++;
            window.app.showMessage(`Кількість "${product.name}" збільшено`, 'success');
        } else {
            this.items.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                quantity: 1
            });
            window.app.showMessage(`"${product.name}" додано в кошик`, 'success');
        }

        this.saveToStorage();
        this.updateCartCount();
        this.renderCart();
    }

    /**
     * Видалити товар з кошика
     */
    removeFromCart(productId) {
        const index = this.items.findIndex(item => item.id === productId);
        
        if (index !== -1) {
            const itemName = this.items[index].name;
            this.items.splice(index, 1);
            this.saveToStorage();
            this.updateCartCount();
            this.renderCart();
            window.app.showMessage(`"${itemName}" видалено з кошика`, 'info');
        }
    }

    /**
     * Оновити кількість товару
     */
    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.id === productId);
        
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                item.quantity = quantity;
                this.saveToStorage();
                this.updateCartCount();
                this.renderCart();
            }
        }
    }

    /**
     * Очистити кошик
     */
    clearCart() {
        this.items = [];
        this.saveToStorage();
        this.updateCartCount();
        this.renderCart();
        window.app.showMessage('Кошик очищено', 'info');
    }

    /**
     * Отримати загальну суму
     */
    getTotalAmount() {
        return this.items.reduce((total, item) => {
            return total + (item.price * item.quantity);
        }, 0);
    }

    /**
     * Отримати кількість товарів
     */
    getTotalItems() {
        return this.items.reduce((total, item) => {
            return total + item.quantity;
        }, 0);
    }

    /**
     * Оновити лічильник кошика
     */
    updateCartCount() {
        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = this.getTotalItems();
        }
    }

    /**
     * Показати кошик
     */
    showCart() {
        // Ховаємо всі секції
        document.querySelectorAll('section').forEach(section => {
            section.classList.add('hidden');
        });

        // Показуємо секцію кошика
        const cartSection = document.getElementById('cart-section');
        if (cartSection) {
            cartSection.classList.remove('hidden');
            this.renderCart();
        }
    }

    /**
     * Відобразити кошик
     */
    renderCart() {
        const cartItemsContainer = document.getElementById('cart-items');
        const cartTotalElement = document.getElementById('cart-total');
        
        if (!cartItemsContainer) return;

        if (this.items.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h3>Ваш кошик порожній</h3>
                    <p>Додайте товари для оформлення замовлення</p>
                    <button class="btn btn-primary" onclick="window.app.navigate('products')">
                        Перейти до покупок
                    </button>
                </div>
            `;
            if (cartTotalElement) {
                cartTotalElement.textContent = '0';
            }
            return;
        }

        cartItemsContainer.innerHTML = this.items.map(item => this.createCartItemHTML(item)).join('');

        if (cartTotalElement) {
            cartTotalElement.textContent = this.getTotalAmount().toFixed(2);
        }

        // Додаємо обробники подій для кнопок
        this.attachCartEventListeners();
    }

    /**
     * Створити HTML для товару в кошику
     */
    createCartItemHTML(item) {
        const itemTotal = (item.price * item.quantity).toFixed(2);
        
        return `
            <div class="cart-item" data-product-id="${item.id}">
                <div class="cart-item-image">
                    <img src="${item.image || '/images/no-image.png'}" alt="${item.name}">
                </div>
                <div class="cart-item-details">
                    <h4 class="cart-item-name">${item.name}</h4>
                    <p class="cart-item-price">${item.price} грн</p>
                </div>
                <div class="cart-item-quantity">
                    <button class="quantity-btn minus" data-action="decrease" data-id="${item.id}">−</button>
                    <input type="number" 
                           class="quantity-input" 
                           value="${item.quantity}" 
                           min="1" 
                           data-id="${item.id}">
                    <button class="quantity-btn plus" data-action="increase" data-id="${item.id}">+</button>
                </div>
                <div class="cart-item-total">
                    <span class="item-total-price">${itemTotal} грн</span>
                </div>
                <div class="cart-item-remove">
                    <button class="remove-btn" data-id="${item.id}" title="Видалити">
                        <span>✕</span>
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Додати обробники подій для кнопок кошика
     */
    attachCartEventListeners() {
        // Кнопки збільшення/зменшення кількості
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productId = parseInt(e.currentTarget.dataset.id);
                const action = e.currentTarget.dataset.action;
                const item = this.items.find(item => item.id === productId);
                
                if (item) {
                    if (action === 'increase') {
                        this.updateQuantity(productId, item.quantity + 1);
                    } else if (action === 'decrease') {
                        this.updateQuantity(productId, item.quantity - 1);
                    }
                }
            });
        });

        // Поля вводу кількості
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', (e) => {
                const productId = parseInt(e.target.dataset.id);
                const quantity = parseInt(e.target.value) || 1;
                this.updateQuantity(productId, quantity);
            });
        });

        // Кнопки видалення
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productId = parseInt(e.currentTarget.dataset.id);
                this.removeFromCart(productId);
            });
        });
    }

    /**
     * Налаштування кнопки оформлення замовлення
     */
    setupCheckoutButton() {
        const checkoutBtn = document.getElementById('checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => this.checkout());
        }

        // Кнопка очищення кошика
        const clearCartBtn = document.getElementById('clear-cart-btn');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', () => {
                if (confirm('Ви впевнені, що хочете очистити кошик?')) {
                    this.clearCart();
                }
            });
        }
    }

    /**
     * Оформлення замовлення
     */
    async checkout() {
        if (this.items.length === 0) {
            window.app.showMessage('Кошик порожній', 'error');
            return;
        }

        // Перевірка чи користувач авторизований
        if (!window.authManager || !window.authManager.isAuthenticated()) {
            window.app.showMessage('Для оформлення замовлення увійдіть в систему', 'error');
            window.app.navigate('login');
            return;
        }

        // Показати форму оформлення замовлення
        this.showCheckoutForm();
    }

    /**
     * Показати форму оформлення замовлення
     */
    showCheckoutForm() {
        const modal = document.createElement('div');
        modal.className = 'modal show';
        modal.id = 'checkout-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('checkout-modal').remove()">&times;</span>
                <h2>Оформлення замовлення</h2>
                <form id="checkoutForm">
                    <div class="form-group">
                        <label for="shipping-address">Адреса доставки:</label>
                        <textarea id="shipping-address" 
                                  rows="3" 
                                  required
                                  placeholder="Вкажіть повну адресу доставки"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Телефон:</label>
                        <input type="tel" 
                               id="phone" 
                               required
                               placeholder="+380...">
                    </div>

                    <div class="form-group">
                        <label for="comment">Коментар до замовлення (необов'язково):</label>
                        <textarea id="comment" 
                                  rows="2" 
                                  placeholder="Додаткова інформація"></textarea>
                    </div>

                    <div class="checkout-summary">
                        <h3>Ваше замовлення:</h3>
                        <div class="summary-items">
                            ${this.items.map(item => `
                                <div class="summary-item">
                                    <span>${item.name} × ${item.quantity}</span>
                                    <span>${(item.price * item.quantity).toFixed(2)} грн</span>
                                </div>
                            `).join('')}
                        </div>
                        <div class="summary-total">
                            <strong>Всього:</strong>
                            <strong>${this.getTotalAmount().toFixed(2)} грн</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-block">
                        Підтвердити замовлення
                    </button>
                </form>
            </div>
        `;

        document.body.appendChild(modal);

        // Обробка форми
        document.getElementById('checkoutForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitOrder();
        });
    }

    /**
     * Відправити замовлення
     */
    async submitOrder() {
        const shippingAddress = document.getElementById('shipping-address').value;
        const phone = document.getElementById('phone').value;
        const comment = document.getElementById('comment').value;

        const currentUser = window.authManager.getCurrentUser();
        
        if (!currentUser) {
            window.app.showMessage('Помилка авторизації', 'error');
            return;
        }

        const orderData = {
            user_id: currentUser.id,
            items: this.items.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price
            })),
            shipping_address: shippingAddress,
            phone: phone,
            comment: comment,
            total_amount: this.getTotalAmount()
        };

        try {
            const response = await window.app.fetch('/orders', {
                method: 'POST',
                body: JSON.stringify(orderData)
            });

            if (response.success) {
                window.app.showMessage('Замовлення успішно оформлено!', 'success');
                
                // Очищуємо кошик
                this.clearCart();
                
                // Закриваємо модальне вікно
                const modal = document.getElementById('checkout-modal');
                if (modal) {
                    modal.remove();
                }
                
                // Показуємо повідомлення про успіх
                this.showOrderSuccessMessage(response.order_id);
            } else {
                window.app.showMessage(response.errors?.order || 'Помилка оформлення замовлення', 'error');
            }
        } catch (error) {
            console.error('Checkout error:', error);
            window.app.showMessage('Помилка з\'єднання з сервером', 'error');
        }
    }

    /**
     * Показати повідомлення про успішне замовлення
     */
    showOrderSuccessMessage(orderId) {
        const modal = document.createElement('div');
        modal.className = 'modal show';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="success-message">
                    <div class="success-icon">✓</div>
                    <h2>Замовлення оформлено!</h2>
                    <p>Номер вашого замовлення: <strong>#${orderId}</strong></p>
                    <p>Ми зв'яжемося з вами найближчим часом для підтвердження.</p>
                    <div class="success-actions">
                        <button class="btn btn-primary" onclick="window.app.navigate('products'); this.closest('.modal').remove();">
                            Продовжити покупки
                        </button>
                        <button class="btn btn-secondary" onclick="window.authManager.showProfile(); this.closest('.modal').remove();">
                            Мої замовлення
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Закриття через 10 секунд
        setTimeout(() => {
            if (modal.parentElement) {
                modal.remove();
            }
        }, 10000);
    }

    /**
     * Отримати всі товари в кошику
     */
    getItems() {
        return this.items;
    }
}

// Ініціалізація при завантаженні
document.addEventListener('DOMContentLoaded', () => {
    window.cartManager = new CartManager();
});