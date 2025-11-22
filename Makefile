# Makefile

.PHONY: help build up down restart logs shell db-shell composer-install

# Показати допомогу
help:
	@echo "Доступні команди:"
	@echo "  make build          - Зібрати Docker образи"
	@echo "  make up             - Запустити контейнери"
	@echo "  make down           - Зупинити контейнери"
	@echo "  make restart        - Перезапустити контейнери"
	@echo "  make logs           - Показати логи"
	@echo "  make shell          - Відкрити shell в PHP контейнері"
	@echo "  make db-shell       - Відкрити MySQL shell"
	@echo "  make composer-install - Встановити Composer залежності"
	@echo "  make test           - Запустити тести"
	@echo "  make clean          - Очистити всі контейнери та томи"

# Зібрати образи
build:
	docker-compose build --no-cache

# Запустити контейнери
up:
	docker-compose up -d

# Зупинити контейнери
down:
	docker-compose down

# Перезапустити
restart: down up

# Показати логи
logs:
	docker-compose logs -f

# Shell в PHP контейнері
shell:
	docker-compose exec app bash

# MySQL shell
db-shell:
	docker-compose exec db mysql -ue_site_user -psecure_password e_site_db

# Встановити Composer залежності
composer-install:
	docker-compose exec app composer install

# Оновити Composer залежності
composer-update:
	docker-compose exec app composer update

# Очистити кеш
cache-clear:
	docker-compose exec app rm -rf /var/www/storage/cache/*

# Встановити права доступу
permissions:
	docker-compose exec app chown -R www:www-data /var/www
	docker-compose exec app chmod -R 755 /var/www
	docker-compose exec app chmod -R 775 /var/www/storage

# Запустити тести
test:
	docker-compose exec app php vendor/bin/phpunit

# Повне очищення
clean:
	docker-compose down -v
	docker system prune -af

# Перша установка
install: build up permissions
	@echo "Проект успішно встановлено!"
	@echo "Відкрийте http://localhost:8080 в браузері"