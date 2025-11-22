// public/js/app.js

/**
 * Головний клас додатку
 */
class App {
    constructor() {
        this.apiUrl = '/api';
        this.currentUser = null;
        this.init();
    }

    /**
     * Ініціалізація додатку
     */
    init() {
        this.checkAuth();
        this.setupNavigation();
        this.setupEventListeners();
    }

    /**
     * Перевірка авторизації
     */
    async checkAuth() {
        const token = localStorage.getItem('token');
        if (token) {
            try {
                const response = await this.fetch('/auth/me');
                if (response.success) {
                    this.currentUser = response.data;
                    this.updateAuthUI();
                }
            } catch (error) {
                console.error('Auth check failed:', error);
                localStorage.removeItem('token');
            }
        }
    }

    /**
     * Оновлення UI авторизації
     */
    updateAuthUI() {
        const authLink = document.getElementById('auth-link');
        if (this.currentUser) {
            authLink.textContent = `Вітаємо, ${this.currentUser.name}`;
            authLink.href = '#profile';
        } else {
            authLink.textContent = 'Вхід';
            authLink.href = '#login';
        }
    }

    /**
     * Налаштування навігації
     */
    setupNavigation() {
        const links = document.querySelectorAll('.nav-menu a');
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = link.getAttribute('href').substring(1);
                this.navigate(target);
            });
        });
    }

    /**
     * Навігація по сторінках
     */
    navigate(page) {
        // Приховати всі секції
        document.querySelectorAll('section').forEach(section => {
            section.classList.add('hidden');
        });

        // Показати потрібну секцію
        const targetSection = document.getElementById(`${page}-section`) || 
                             document.getElementById('products');
        if (targetSection) {
            targetSection.classList.remove('hidden');
        }

        // Оновити URL
        history.pushState({page}, '', `#${page}`);
    }

    /**
     * Налаштування обробників подій
     */
    setupEventListeners() {
        // Обробка кнопки "Назад" браузера
        window.addEventListener('popstate', (e) => {
            const page = e.state?.page || 'home';
            this.navigate(page);
        });
    }

    /**
     * Fetch wrapper з обробкою помилок
     */
    async fetch(endpoint, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            }
        };

        // Додати токен якщо є
        const token = localStorage.getItem('token');
        if (token) {
            defaultOptions.headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch(`${this.apiUrl}${endpoint}`, {
            ...defaultOptions,
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    /**
     * Показати повідомлення
     */
    showMessage(message, type = 'info') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message message-${type}`;
        messageDiv.textContent = message;
        
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 3000);
    }
}

// Ініціалізація при завантаженні сторінки
document.addEventListener('DOMContentLoaded', () => {
    window.app = new App();
});