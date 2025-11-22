<?php
// src/Config/Database.php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Клас для роботи з базою даних
 * Реалізує патерн Singleton для єдиного підключення
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private string $host;
    private string $dbName;
    private string $username;
    private string $password;
    private string $charset = 'utf8mb4';

    /**
     * Приватний конструктор для Singleton
     */
    private function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->dbName = $_ENV['DB_NAME'] ?? 'e_site_db';
        $this->username = $_ENV['DB_USER'] ?? 'e_site_user';
        $this->password = $_ENV['DB_PASSWORD'] ?? 'secure_password';
    }

    /**
     * Отримання єдиного екземпляра класу
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Отримання PDO з'єднання
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                throw new PDOException("Connection failed: " . $e->getMessage());
            }
        }

        return $this->connection;
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