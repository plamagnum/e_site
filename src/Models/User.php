<?php
// src/Models/User.php

namespace App\Models;

use DateTime;

/**
 * Модель користувача
 */
class User
{
    private ?int $id = null;
    private string $name;
    private string $email;
    private string $password;
    private ?string $phone = null;
    private string $role = 'user';
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;

    /**
     * Конструктор з можливістю ініціалізації через масив
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    /**
     * Заповнення моделі даними
     */
    public function fill(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->phone = $data['phone'] ?? null;
        $this->role = $data['role'] ?? 'user';
        
        if (isset($data['created_at'])) {
            $this->createdAt = new DateTime($data['created_at']);
        }
        if (isset($data['updated_at'])) {
            $this->updatedAt = new DateTime($data['updated_at']);
        }
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getPhone(): ?string { return $this->phone; }
    public function getRole(): string { return $this->role; }
    public function getCreatedAt(): ?DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTime { return $this->updatedAt; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPassword(string $password): void { $this->password = password_hash($password, PASSWORD_DEFAULT); }
    public function setPhone(?string $phone): void { $this->phone = $phone; }
    public function setRole(string $role): void { $this->role = $role; }

    /**
     * Перетворення об'єкту в масив
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}