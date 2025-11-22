<?php
// src/Helpers/Session.php

namespace App\Helpers;

/**
 * Клас для управління сесіями
 */
class Session
{
    /**
     * Запустити сесію якщо вона ще не запущена
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Встановити значення в сесію
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Отримати значення з сесії
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Перевірити чи існує ключ в сесії
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Видалити ключ з сесії
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Очистити всю сесію
     */
    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }

    /**
     * Знищити сесію
     */
    public static function destroy(): void
    {
        self::start();
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Отримати ID сесії
     */
    public static function id(): string
    {
        self::start();
        return session_id();
    }

    /**
     * Регенерувати ID сесії (для безпеки після логіну)
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }
}