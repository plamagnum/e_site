// public/js/products.js

/**
 * Клас для роботи з товарами
 */
class ProductManager {
    constructor() {
        this.products = [];
        this.currentCategory = '';
        this.init();
    }

    /**
     * Ініціалізація
     */
    async init() {
        await this.loadProducts();
        this.setupFilters();
        this.setupProductModal();
    }

    /**
     * Завантажити товари з API
     */
    async loadProducts() {
        try {
            const response = await window.app.fetch('/products');
            if (response.success) {
                this.products = response.data;
                this.renderProducts();
            }
        } catch (error) {
            console.error('Failed to load products:', error);
            window.app.showMessage('Помилка завантаження товарів', 'error');
        }
    }

    /**
     * Відобразити товари
     */
    renderProducts() {
        const grid = document.getElementById('products-grid');
        grid.innerHTML = '';

        const filteredProducts = this.currentCategory 
            ? this.products.filter(p => p.category === this.currentCategory)
            : this.products;

        filteredProducts.forEach(product => {
            const card = this.createProductCard(product);
            grid.appendChild(card);
        });
    }

    /**
     * Створити картку товару
     */
    createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.innerHTML = `
            <img src="${product.image || '/images/no-image.png'}" 
                 alt="${product.name}" 
                 class="product-image">
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <div class="product-price">${product.price} грн</div>
                <button class="btn btn-add-cart" data-id="${product.id}">
                    Додати в кошик
                </button>
            </div>
        `;

        // Показати деталі при кліку на картку
        card.addEventListener('click', (e) => {
            if (!e.target.classList.contains('btn-add-cart')) {
                this.showProductDetails(product);
            }
        });

        // Додати в кошик
        card.querySelector('.btn-add-cart').addEventListener('click', (e) => {
            e.stopPropagation();
            window.cartManager.addToCart(product);
        });

        return card;
    }

    /**
     * Показати деталі товару
     */
    showProductDetails(product) {
        const modal = document.getElementById('product-modal');
        const details = document.getElementById('product-details');
        
        details.innerHTML = `
            <img src="${product.image || '/images/no-image.png'}" 
                 alt="${product.name}" 
                 style="width: 100%; max-height: 300px; object-fit: cover; margin-bottom: 1rem;">
            <h2>${product.name}</h2>
            <p>${product.description}</p>
            <div class="product-price" style="margin: 1rem 0;">${product.price} грн</div>
            <p>Категорія: ${product.category}</p>
            <p>В наявності: ${product.quantity} шт.</p>
            <button class="btn btn-primary btn-add-cart" data-id="${product.id}">
                Додати в кошик
            </button>
        `;

        modal.classList.add('show');

        // Додати в кошик з модального вікна
        details.querySelector('.btn-add-cart').addEventListener('click', () => {
            window.cartManager.addToCart(product);
            modal.classList.remove('show');
        });
    }

    /**
     * Налаштування модального вікна
     */
    setupProductModal() {
        const modal = document.getElementById('product-modal');
        const closeBtn = modal.querySelector('.close');

        closeBtn.addEventListener('click', () => {
            modal.classList.remove('show');
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    }

    /**
     * Налаштування фільтрів
     */
    setupFilters() {
        const categoryFilter = document.getElementById('category-filter');
        categoryFilter.addEventListener('change', (e) => {
            this.currentCategory = e.target.value;
            this.renderProducts();
        });
    }
}

// Ініціалізація при завантаженні
document.addEventListener('DOMContentLoaded', () => {
    window.productManager = new ProductManager();
});