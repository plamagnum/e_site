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
        this.setupProfileSection();
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
     * Налаштування профілю
     */
    setupProfileSection() {
        // Форма редагування профілю
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', (e) => this.handleProfileUpdate(e));
        }

        // Кнопка зміни пароля
        const changePasswordBtn = document.getElementById('change-password-btn');
        if (changePasswordBtn) {
            changePasswordBtn.addEventListener('click', () => this.showChangePasswordModal());
        }

        // Форма зміни пароля
        const changePasswordForm = document.getElementById('changePasswordForm');
        if (changePasswordForm) {
            changePasswordForm.addEventListener('submit', (e) => this.handlePasswordChange(e));
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
                    this.showProfile();
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
     * Показати профіль користувача
     */
    async showProfile() {
        if (!this.isLoggedIn || !this.currentUser) {
            this.showAuthSection();
            return;
        }

        // Ховаємо всі секції
        document.querySelectorAll('section').forEach(section => {
            section.classList.add('hidden');
        });

        // Показуємо секцію профілю
        let profileSection = document.getElementById('profile-section');
        
        if (!profileSection) {
            profileSection = this.createProfileSection();
            document.querySelector('.main').appendChild(profileSection);
        }

        // Заповнюємо дані профілю
        this.fillProfileData();
        
        // Завантажуємо замовлення користувача
        await this.loadUserOrders();

        profileSection.classList.remove('hidden');
    }

    /**
     * Створити секцію профілю
     */
    createProfileSection() {
        const section = document.createElement('section');
        section.id = 'profile-section';
        section.className = 'profile-section';
        section.innerHTML = `
            <div class="container">
                <h2>Мій профіль</h2>
                
                <div class="profile-container">
                    <!-- Sidebar -->
                    <div class="profile-sidebar">
                        <div class="profile-menu">
                            <button class="profile-menu-item active" data-tab="info">
                                <span class="icon">👤</span>
                                Особиста інформація
                            </button>
                            <button class="profile-menu-item" data-tab="orders">
                                <span class="icon">📦</span>
                                Мої замовлення
                            </button>
                            <button class="profile-menu-item" data-tab="security">
                                <span class="icon">🔒</span>
                                Безпека
                            </button>
                            <button class="profile-menu-item logout-btn" id="profile-logout-btn">
                                <span class="icon">🚪</span>
                                Вийти
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="profile-content">
                        <!-- Особиста інформація -->
                        <div class="profile-tab active" id="tab-info">
                            <div class="profile-card">
                                <h3>Особиста інформація</h3>
                                <form id="profileForm">
                                    <div class="form-group">
                                        <label for="profile-name">Ім'я:</label>
                                        <input type="text" id="profile-name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="profile-email">Email:</label>
                                        <input type="email" id="profile-email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="profile-phone">Телефон:</label>
                                        <input type="tel" id="profile-phone">
                                    </div>
                                    <div class="profile-info-item">
                                        <label>Роль:</label>
                                        <span id="profile-role" class="badge"></span>
                                    </div>
                                    <div class="profile-info-item">
                                        <label>Дата реєстрації:</label>
                                        <span id="profile-created"></span>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Зберегти зміни</button>
                                </form>
                            </div>
                        </div>

                        <!-- Замовлення -->
                        <div class="profile-tab" id="tab-orders">
                            <div class="profile-card">
                                <h3>Мої замовлення</h3>
                                <div id="user-orders-list" class="orders-list">
                                    <p class="loading">Завантаження замовлень...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Безпека -->
                        <div class="profile-tab" id="tab-security">
                            <div class="profile-card">
                                <h3>Зміна пароля</h3>
                                <form id="changePasswordForm">
                                    <div class="form-group">
                                        <label for="current-password">Поточний пароль:</label>
                                        <input type="password" id="current-password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="new-password">Новий пароль:</label>
                                        <input type="password" id="new-password" required minlength="6">
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm-password">Підтвердіть пароль:</label>
                                        <input type="password" id="confirm-password" required minlength="6">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Змінити пароль</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Додаємо обробники подій для вкладок
        const menuItems = section.querySelectorAll('.profile-menu-item:not(.logout-btn)');
        menuItems.forEach(item => {
            item.addEventListener('click', (e) => {
                const tab = e.currentTarget.dataset.tab;
                this.switchProfileTab(tab);
            });
        });

        // Кнопка виходу
        const logoutBtn = section.querySelector('#profile-logout-btn');
        logoutBtn.addEventListener('click', () => this.logout());

        return section;
    }

    /**
     * Перемикання вкладок профілю
     */
    switchProfileTab(tabName) {
        // Деактивуємо всі вкладки
        document.querySelectorAll('.profile-menu-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelectorAll('.profile-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Активуємо вибрану вкладку
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`tab-${tabName}`).classList.add('active');

        // Завантажуємо замовлення при переході на вкладку
        if (tabName === 'orders') {
            this.loadUserOrders();
        }
    }

    /**
     * Заповнити дані профілю
     */
    fillProfileData() {
        if (!this.currentUser) return;

        document.getElementById('profile-name').value = this.currentUser.name || '';
        document.getElementById('profile-email').value = this.currentUser.email || '';
        document.getElementById('profile-phone').value = this.currentUser.phone || '';
        
        const roleElement = document.getElementById('profile-role');
        roleElement.textContent = this.getRoleLabel(this.currentUser.role);
        roleElement.className = `badge badge-${this.currentUser.role}`;
        
        const createdDate = new Date(this.currentUser.created_at);
        document.getElementById('profile-created').textContent = createdDate.toLocaleDateString('uk-UA');
    }

    /**
     * Отримати назву ролі
     */
    getRoleLabel(role) {
        const roles = {
            'admin': 'Адміністратор',
            'manager': 'Менеджер',
            'user': 'Користувач'
        };
        return roles[role] || role;
    }

    /**
     * Обробка оновлення профілю
     */
    async handleProfileUpdate(event) {
        event.preventDefault();

        const name = document.getElementById('profile-name').value;
        const email = document.getElementById('profile-email').value;
        const phone = document.getElementById('profile-phone').value;

        if (name.length < 2) {
            window.app.showMessage('Ім\'я повинно містити мінімум 2 символи', 'error');
            return;
        }

        if (!this.validateEmail(email)) {
            window.app.showMessage('Невірний формат email', 'error');
            return;
        }

        try {
            const response = await window.app.fetch(`/users/${this.currentUser.id}`, {
                method: 'PUT',
                body: JSON.stringify({ name, email, phone })
            });

            if (response.success) {
                this.currentUser = { ...this.currentUser, name, email, phone };
                this.updateAuthUI();
                window.app.showMessage('Профіль успішно оновлено', 'success');
            } else {
                window.app.showMessage(response.error || 'Помилка оновлення профілю', 'error');
            }
        } catch (error) {
            console.error('Profile update error:', error);
            window.app.showMessage('Помилка з\'єднання з сервером', 'error');
        }
    }

    /**
     * Обробка зміни пароля
     */
    async handlePasswordChange(event) {
        event.preventDefault();

        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        if (newPassword !== confirmPassword) {
            window.app.showMessage('Паролі не співпадають', 'error');
            return;
        }

        if (newPassword.length < 6) {
            window.app.showMessage('Новий пароль повинен містити мінімум 6 символів', 'error');
            return;
        }

        try {
            const response = await window.app.fetch('/auth/change-password', {
                method: 'POST',
                body: JSON.stringify({ 
                    current_password: currentPassword,
                    new_password: newPassword 
                })
            });

            if (response.success) {
                window.app.showMessage('Пароль успішно змінено', 'success');
                document.getElementById('changePasswordForm').reset();
            } else {
                window.app.showMessage(response.error || 'Помилка зміни пароля', 'error');
            }
        } catch (error) {
            console.error('Password change error:', error);
            window.app.showMessage('Помилка з\'єднання з сервером', 'error');
        }
    }

    /**
     * Завантажити замовлення користувача
     */
    async loadUserOrders() {
        if (!this.currentUser) return;

        const ordersList = document.getElementById('user-orders-list');
        ordersList.innerHTML = '<p class="loading">Завантаження замовлень...</p>';

        try {
            const response = await window.app.fetch(`/orders/user/${this.currentUser.id}`);

            if (response.success) {
                if (response.data.length === 0) {
                    ordersList.innerHTML = '<p class="no-data">У вас поки немає замовлень</p>';
                } else {
                    ordersList.innerHTML = response.data.map(order => this.createOrderCard(order)).join('');
                }
            } else {
                ordersList.innerHTML = '<p class="error">Помилка завантаження замовлень</p>';
            }
        } catch (error) {
            console.error('Load orders error:', error);
            ordersList.innerHTML = '<p class="error">Помилка з\'єднання з сервером</p>';
        }
    }

    /**
     * Створити картку замовлення
     */
    createOrderCard(order) {
        const statusLabels = {
            'pending': 'Очікує',
            'processing': 'Обробляється',
            'completed': 'Виконано',
            'cancelled': 'Скасовано'
        };

        const orderDate = new Date(order.created_at).toLocaleDateString('uk-UA');

        return `
            <div class="order-card">
                <div class="order-header">
                    <div class="order-number">Замовлення #${order.id}</div>
                    <div class="order-status status-${order.status}">
                        ${statusLabels[order.status] || order.status}
                    </div>
                </div>
                <div class="order-body">
                    <div class="order-info">
                        <p><strong>Дата:</strong> ${orderDate}</p>
                        <p><strong>Сума:</strong> ${order.total_amount} грн</p>
                        <p><strong>Адреса доставки:</strong> ${order.shipping_address || 'Не вказана'}</p>
                    </div>
                    <div class="order-items">
                        <strong>Товари:</strong>
                        <ul>
                            ${order.items.map(item => `
                                <li>${item.product_name} x ${item.quantity} = ${item.price * item.quantity} грн</li>
                            `).join('')}
                        </ul>
                    </div>
                </div>
                <div class="order-footer">
                    <button class="btn btn-sm" onclick="window.authManager.viewOrderDetails(${order.id})">
                        Детальніше
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Переглянути деталі замовлення
     */
    async viewOrderDetails(orderId) {
        // Тут можна додати модальне вікно з детальною інформацією про замовлення
        console.log('View order details:', orderId);
        window.app.showMessage('Функція в розробці', 'info');
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