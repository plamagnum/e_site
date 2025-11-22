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
    }

    /**
     * Завантажити кошик з localStorage
     */
    loadFromStorage() {
        const saved = localStorage.getItem('cart');
        if (saved) {
            this.items = JSON.parse(saved);
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
        }
    }
}