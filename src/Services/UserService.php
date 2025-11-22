<?php
// src/Services/UserService.php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Helpers\Validator;

/**
 * Сервіс для бізнес-логіки роботи з користувачами
 */
class UserService
{
    private UserRepository $userRepository;
    private Validator $validator;

    /**
     * Dependency Injection через конструктор
     */
    public function __construct(UserRepository $userRepository, Validator $validator)
    {
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    /**
     * Отримати всіх користувачів
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * Отримати користувача за ID
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Створити нового користувача
     */
    public function createUser(array $data): array
    {
        // Валідація даних
        $errors = $this->validator->validateUser($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Перевірка чи email вже існує
        if ($this->userRepository->findByEmail($data['email'])) {
            return ['success' => false, 'errors' => ['email' => 'Email вже використовується']];
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        $user->setPhone($data['phone'] ?? null);
        $user->setRole($data['role'] ?? 'user');

        $result = $this->userRepository->create($user);

        return [
            'success' => $result,
            'message' => $result ? 'Користувача успішно створено' : 'Помилка створення користувача'
        ];
    }

    /**
     * Оновити користувача
     */
    public function updateUser(int $id, array $data): array
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            return ['success' => false, 'errors' => ['user' => 'Користувача не знайдено']];
        }

        $user->setName($data['name'] ?? $user->getName());
        $user->setEmail($data['email'] ?? $user->getEmail());
        $user->setPhone($data['phone'] ?? $user->getPhone());
        $user->setRole($data['role'] ?? $user->getRole());

        if (isset($data['password']) && !empty($data['password'])) {
            $user->setPassword($data['password']);
        }

        $result = $this->userRepository->update($user);

        return [
            'success' => $result,
            'message' => $result ? 'Користувача успішно оновлено' : 'Помилка оновлення користувача'
        ];
    }

    /**
     * Видалити користувача
     */
    public function deleteUser(int $id): array
    {
        $result = $this->userRepository->delete($id);

        return [
            'success' => $result,
            'message' => $result ? 'Користувача успішно видалено' : 'Помилка видалення користувача'
        ];
    }
}