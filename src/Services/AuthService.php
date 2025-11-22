<?php
// src/Services/AuthService.php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Сервіс для аутентифікації та авторизації
 */
class AuthService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Вхід користувача
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'error' => 'Невірний email або пароль'];
        }

        if (!password_verify($password, $user->getPassword())) {
            return ['success' => false, 'error' => 'Невірний email або пароль'];
        }

        // Створення сесії
        session_start();
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRole();

        return [
            'success' => true,
            'message' => 'Успішний вхід',
            'user' => $user->toArray()
        ];
    }

    /**
     * Реєстрація користувача
     */
    public function register(array $data): array
    {
        // Перевірка чи існує користувач
        if ($this->userRepository->findByEmail($data['email'])) {
            return ['success' => false, 'error' => 'Користувач з таким email вже існує'];
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        $user->setPhone($data['phone'] ?? null);

        $result = $this->userRepository->create($user);

        if ($result) {
            return $this->login($data['email'], $data['password']);
        }

        return ['success' => false, 'error' => 'Помилка реєстрації'];
    }

    /**
     * Вихід користувача
     */
    public function logout(): void
    {
        session_start();
        session_destroy();
    }

    /**
     * Перевірка чи користувач авторизований
     */
    public function isAuthenticated(): bool
    {
        session_start();
        return isset($_SESSION['user_id']);
    }

    /**
     * Отримати поточного користувача
     */
    public function getCurrentUser(): ?User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $this->userRepository->findById($_SESSION['user_id']);
    }
}