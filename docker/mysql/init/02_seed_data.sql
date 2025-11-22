-- docker/mysql/init/02_seed_data.sql

USE e_site_db;

-- Додаємо адміністратора (пароль: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Адміністратор', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Додаємо тестових користувачів
INSERT INTO users (name, email, password, phone) VALUES
('Іван Петренко', 'ivan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+380501234567'),
('Марія Коваленко', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+380509876543');

-- Додаємо категорії
INSERT INTO categories (name, description) VALUES
('electronics', 'Електроніка та гаджети'),
('clothing', 'Одяг та аксесуари'),
('books', 'Книги та література'),
('home', 'Товари для дому');

-- Додаємо тестові товари
INSERT INTO products (name, description, price, quantity, category) VALUES
('Смартфон Samsung Galaxy S23', 'Потужний смартфон з відмінною камерою', 24999.00, 15, 'electronics'),
('Ноутбук Lenovo IdeaPad', 'Легкий ноутбук для роботи та навчання', 18999.00, 10, 'electronics'),
('Навушники Sony WH-1000XM5', 'Бездротові навушники з шумозаглушенням', 8999.00, 25, 'electronics'),
('Футболка Cotton', 'Якісна бавовняна футболка', 399.00, 50, 'clothing'),
('Джинси Levi''s 501', 'Класичні джинси', 1999.00, 30, 'clothing'),
('Книга "Кобзар"', 'Збірка поезій Тараса Шевченка', 299.00, 100, 'books'),
('Настільна лампа LED', 'Сучасна LED лампа для робочого столу', 799.00, 20, 'home'),
('Чайник електричний', 'Швидкий електрочайник 1.7л', 599.00, 35, 'home');

-- Додаємо тестове замовлення
INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES
(2, 25398.00, 'completed', 'м. Київ, вул. Хрещатик, 1, кв. 10');

INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 24999.00),
(1, 4, 1, 399.00);