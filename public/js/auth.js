// public/js/auth.js

/**
 * Клас для управління аутентифікацією
 */
class AuthManager {
    constructor() {
        this.isLoggedIn = false;
        this.currentUser = null;
        this.init();
    }

    /**
     * Ініціалізація
     */
    init() {
        this.checkAuthStatus();
        this.setupAuthForms();
        this.setupAuthLinks();
    }

    /**
     * Перевірка статусу авторизації
     */
    async checkAuthStatus() {
        const token = localStorage.getItem('auth_token');
        
        if (token) {
            try {
                const response = await window.app.fetch('/auth/me', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (response.success) {
                    this.isLoggedIn = true;
                    this.currentUser = response.data;
                    this.updateAuthUI();
                } else {
                    this.logout();
                }
            } catch (error) {
                console.error('Auth check failed:', error);
                this.logout();
            }
        }
    }

    /**
     * Налаштування форм авторизації
     */
    setupAuthForms() {
        // Форма входу
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Форма реєстрації
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => this.handleRegister(e));
        }

        // Перемикання між формами
        const showRegisterLink = document.getElementById('show-register');
        const showLoginLink = document.getElementById('show-login');

        if (showRegisterLink) {
            showRegisterLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleAuthForms('register');
            });
        }

        if (showLoginLink) {
            showLoginLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleAuthForms('login');
            });
        }
    }

    /**
     * Налаштування посилань авторизації
     */
    setupAuthLinks() {
        const authLink = document.getElementById('auth-link');
        
        if (authLink) {
            authLink.addEventListener('click', (e) => {
                e.preventDefault();
                
                if (this.isLoggedIn) {
                    this.showUserMenu();
                } else {
                    this.showAuthSection();
                }
            });
        }
    }

    /**
     * Обробка входу
     */
    async handleLogin(event) {
        event.preventDefault();

        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;

        // Валідація
        if (!this.validateEmail(email)) {
            window.app.showMessage('Невірний формат email', 'error');
            return;
        }

        if (password.length < 6) {
            window.app.showMessage('Пароль повинен містити мінімум 6 символів', 'error');
            return;
        }

        try {
            const response = await window.app.fetch('/auth/login', {
                method: 'POST',
                body: JSON.stringify({ email, password })
            });

            if (response.success) {
                // Зберігаємо токен
                localStorage.setItem('auth_token', response.token);
                
                this.isLoggedIn = true;
                this.currentUser = response.user;
                
                this.updateAuthUI();
                this.hideAuthSection();
                
                window.app.showMessage(`Вітаємо, ${response.user.name}!`, 'success');
                
                // Перенаправлення на головну
                window.app.navigate('products');
            } else {
                window.app.showMessage(response.error || 'Помилка входу', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            window.app.showMessage('Помилка з\'єднання з сервером', 'error');
        }
    }

    /**
     * Обробка реєстрації
     */
    async handleRegister(event) {
        event.preventDefault();

        const name = document.getElementById('register-name').value;
        const email = document.getElementById('register-email').value;
        const password = document.getElementById('register-password').value;
        const phone = document.getElementById('register-phone').value;

        // Валідація
        if (name.length < 2) {
            window.app.showMessage('Ім\'я повинно містити мінімум 2 символи', 'error');
            return;
        }

        if (!this.validateEmail(email)) {
            window.app.showMessage('Невірний формат email', 'error');
            return;
        }

        if (password.length < 6) {
            window.app.showMessage('Пароль повинен містити мінімум 6 символів', 'error');
            return;
        }

        try {
            const response = await window.app.fetch('/auth/register', {
                method: 'POST',
                body: JSON.stringify({ name, email, password, phone })
            });

            if (response.success) {
                // Зберігаємо токен
                localStorage.setItem('auth_token', response.token);
                
                this.isLoggedIn = true;
                this.currentUser = response.user;
                
                this.updateAuthUI();
                this.hideAuthSection();
                
                window.app.showMessage(`Реєстрація успішна! Вітаємо, ${response.user.name}!`, 'success');
                
                // Перенаправлення на головну
                window.app.navigate('products');
            } else {
                window.app.showMessage(response.error || 'Помилка реєстрації', 'error');
            }
        } catch (error) {
            console.error('Register error:', error);
            window.app.showMessage('Помилка з\'єднання з сервером', 'error');
        }
    }

    /**
     * Вихід з системи
     */
    async logout() {
        try {
            await window.app.fetch('/auth/logout', {
                method: 'POST'
            });
        } catch (error) {
            console.error('Logout error:', error);
        }

        // Очищуємо дані
        localStorage.removeItem('auth_token');
        this.isLoggedIn = false;
        this.currentUser = null;
        
        this.updateAuthUI();
        window.app.showMessage('Ви вийшли з системи', 'info');
        
        // Перенаправлення на головну
        window.app.navigate('home');
    }

    /**
     * Оновлення UI авторизації
     */
    updateAuthUI() {
        const authLink = document.getElementById('auth-link');
        
        if (this.isLoggedIn && this.currentUser) {
            authLink.textContent = this.currentUser.name;
            authLink.href = '#profile';
            
            // Показати кнопку виходу
            this.addLogoutButton();
        } else {
            authLink.textContent = 'Вхід';
            authLink.href = '#login';
            
            // Сховати кнопку виходу
            this.removeLogoutButton();
        }
    }

    /**
     * Додати кнопку виходу
     */
    addLogoutButton() {
        const navMenu = document.querySelector('.nav-menu');
        
        // Перевіряємо чи вже є кнопка
        if (document.getElementById('logout-btn')) {
            return;
        }

        const logoutItem = document.createElement('li');
        logoutItem.innerHTML = '<a href="#" id="logout-btn">Вихід</a>';
        navMenu.appendChild(logoutItem);

        document.getElementById('logout-btn').addEventListener('click', (e) => {
            e.preventDefault();
            this.logout();
        });
    }

    /**
     * Видалити кнопку виходу
     */
    removeLogoutButton() {
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.parentElement.remove();
        }
    }

    /**
     * Показати секцію авторизації
     */
    showAuthSection() {
        const authSection = document.getElementById('auth-section');
        if (authSection) {
            authSection.classList.remove('hidden');
            
            // Приховати інші секції
            document.querySelectorAll('section').forEach(section => {
                if (section.id !== 'auth-section') {
                    section.classList.add('hidden');
                }
            });
        }
    }

    /**
     * Сховати секцію авторизації
     */
    hideAuthSection() {
        const authSection = document.getElementById('auth-section');
        if (authSection) {
            authSection.classList.add('hidden');
        }
    }

    /**
     * Перемикання між формами входу і реєстрації
     */
    toggleAuthForms(formType) {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');

        if (formType === 'register') {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
        } else {
            registerForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
        }
    }

    /**
     * Показати меню користувача
     */
    showUserMenu() {
        // Тут можна додати випадаюче меню з профілем, замовленнями тощо
        const menu = `
            <div class="user-menu">
                <ul>
                    <li><a href="#profile">Профіль</a></li>
                    <li><a href="#orders">Мої замовлення</a></li>
                    <li><a href="#" id="logout-menu-btn">Вихід</a></li>
                </ul>
            </div>
        `;
        
        // Логіка відображення меню
        console.log('Show user menu');
    }

    /**
     * Валідація email
     */
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    /**
     * Перевірка чи користувач авторизований
     */
    isAuthenticated() {
        return this.isLoggedIn;
    }

    /**
     * Отримати поточного користувача
     */
    getCurrentUser() {
        return this.currentUser;
    }

    /**
     * Отримати токен
     */
    getToken() {
        return localStorage.getItem('auth_token');
    }
}

// Ініціалізація при завантаженні
document.addEventListener('DOMContentLoaded', () => {
    window.authManager = new AuthManager();
});