# Dockerfile

# Використовуємо офіційний образ PHP 8.2 з FPM
FROM php:8.2-fpm

# Встановлюємо аргументи для користувача
ARG user=www
ARG uid=1000

# Встановлюємо системні залежності
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    nano \
    vim

# Очищуємо кеш apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Встановлюємо PHP розширення
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Встановлюємо Redis розширення для кешування (опціонально)
RUN pecl install redis && docker-php-ext-enable redis

# Отримуємо останню версію Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Створюємо системного користувача для запуску Composer та Artisan команд
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Встановлюємо робочу директорію
WORKDIR /var/www

# Копіюємо файли проекту
COPY . /var/www

# Копіюємо PHP конфігурацію
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Встановлюємо права доступу
RUN chown -R $user:www-data /var/www
RUN chmod -R 755 /var/www
RUN chmod -R 775 /var/www/storage /var/www/public

# Перемикаємось на користувача www
USER $user

# Відкриваємо порт 9000 для PHP-FPM
EXPOSE 9000

# Запускаємо PHP-FPM
CMD ["php-fpm"]