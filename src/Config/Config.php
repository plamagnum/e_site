<?php
// src/Config/Config.php

namespace App\Config;

/**
 * Клас конфігурації додатку
 */
class Config
{
    private static ?Config $instance = null;
    private array $config = [];

    /**
     * Приватний конструктор для Singleton
     */
    private function __construct()
    {
        $this->loadEnvironment();
        $this->loadConfig();
    }

    /**
     * Отримання єдиного екземпляра
     */
    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Завантаження змінних середовища з .env файлу
     */
    private function loadEnvironment(): void
    {
        $envFile = __DIR__ . '/../../.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Пропускаємо коментарі
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                // Розбиваємо по знаку =
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Видаляємо лапки якщо є
                    $value = trim($value, '"\'');
                    
                    // Зберігаємо в $_ENV
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    /**
     * Завантаження конфігурації
     */
    private function loadConfig(): void
    {
        // Конфігурація додатку
        $this->config['app'] = [
            'name' => $this->env('APP_NAME', 'E-Commerce Site'),
            'env' => $this->env('APP_ENV', 'development'),
            'debug' => $this->env('APP_DEBUG', true),
            'url' => $this->env('APP_URL', 'http://localhost:8080'),
            'timezone' => $this->env('APP_TIMEZONE', 'Europe/Kiev'),
        ];

        // Конфігурація бази даних
        $this->config['database'] = [
            'host' => $this->env('DB_HOST', 'db'),
            'port' => $this->env('DB_PORT', 3306),
            'database' => $this->env('DB_DATABASE', 'e_site_db'),
            'username' => $this->env('DB_USERNAME', 'e_site_user'),
            'password' => $this->env('DB_PASSWORD', 'secure_password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        // Конфігурація Redis
        $this->config['redis'] = [
            'host' => $this->env('REDIS_HOST', 'redis'),
            'port' => $this->env('REDIS_PORT', 6379),
            'password' => $this->env('REDIS_PASSWORD', null),
            'database' => 0,
        ];

        // Конфігурація сесій
        $this->config['session'] = [
            'lifetime' => $this->env('SESSION_LIFETIME', 120),
            'driver' => $this->env('SESSION_DRIVER', 'file'),
            'path' => __DIR__ . '/../../storage/sessions',
        ];

        // Конфігурація пошти
        $this->config['mail'] = [
            'mailer' => $this->env('MAIL_MAILER', 'smtp'),
            'host' => $this->env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => $this->env('MAIL_PORT', 587),
            'username' => $this->env('MAIL_USERNAME', ''),
            'password' => $this->env('MAIL_PASSWORD', ''),
            'encryption' => $this->env('MAIL_ENCRYPTION', 'tls'),
            'from' => [
                'address' => $this->env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
                'name' => $this->env('MAIL_FROM_NAME', $this->config['app']['name']),
            ],
        ];

        // Конфігурація завантаження файлів
        $this->config['upload'] = [
            'max_size' => 10 * 1024 * 1024, // 10MB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'path' => __DIR__ . '/../../storage/uploads',
        ];

        // Конфігурація безпеки
        $this->config['security'] = [
            'jwt_secret' => $this->env('JWT_SECRET', 'your-secret-key-change-this'),
            'jwt_lifetime' => 3600, // 1 година
            'bcrypt_rounds' => 10,
        ];

        // Конфігурація API
        $this->config['api'] = [
            'prefix' => '/api',
            'version' => 'v1',
            'rate_limit' => 60, // запитів на хвилину
        ];
    }

    /**
     * Отримати значення змінної середовища
     */
    private function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }

        // Конвертація булевих значень
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }

    /**
     * Отримати конфігураційне значення
     * 
     * @param string $key Ключ у форматі 'section.key'
     * @param mixed $default Значення за замовчуванням
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Встановити конфігураційне значення
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        while (count($keys) > 1) {
            $key = array_shift($keys);
            
            if (!isset($config[$key]) || !is_array($config[$key])) {
                $config[$key] = [];
            }
            
            $config = &$config[$key];
        }

        $config[array_shift($keys)] = $value;
    }

    /**
     * Отримати всю конфігурацію
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Перевірка чи додаток у режимі debug
     */
    public function isDebug(): bool
    {
        return (bool)$this->get('app.debug', false);
    }

    /**
     * Отримати базовий URL додатку
     */
    public function getAppUrl(): string
    {
        return $this->get('app.url', 'http://localhost:8080');
    }

    /**
     * Запобігаємо клонування
     */
    private function __clone() {}

    /**
     * Запобігаємо десеріалізації
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}