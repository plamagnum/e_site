<?php
// src/Helpers/Validator.php

namespace App\Helpers;

/**
 * Клас для валідації даних
 */
class Validator
{
    /**
     * Валідація даних користувача
     */
    public function validateUser(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = "Ім'я обов'язкове";
        }

        if (empty($data['email'])) {
            $errors['email'] = "Email обов'язковий";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Невірний формат email";
        }

        if (empty($data['password'])) {
            $errors['password'] = "Пароль обов'язковий";
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = "Пароль повинен містити мінімум 6 символів";
        }

        return $errors;
    }

    /**
     * Валідація даних товару
     */
    public function validateProduct(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = "Назва обов'язкова";
        }

        if (empty($data['description'])) {
            $errors['description'] = "Опис обов'язковий";
        }

        if (!isset($data['price']) || $data['price'] <= 0) {
            $errors['price'] = "Ціна повинна бути більше 0";
        }

        if (!isset($data['quantity']) || $data['quantity'] < 0) {
            $errors['quantity'] = "Кількість не може бути від'ємною";
        }

        if (empty($data['category'])) {
            $errors['category'] = "Категорія обов'язкова";
        }

        return $errors;
    }
}