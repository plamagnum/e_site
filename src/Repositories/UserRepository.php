<?php
// src/Repositories/UserRepository.php

namespace App\Repositories;

use App\Config\Database;
use App\Models\User;
use PDO;

/**
 * Репозиторій для роботи з користувачами в БД
 */
class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Знайти всіх користувачів
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        $users = [];
        
        while ($row = $stmt->fetch()) {
            $users[] = new User($row);
        }
        
        return $users;
    }

    /**
     * Знайти користувача за ID
     */
    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? new User($data) : null;
    }

    /**
     * Знайти користувача за email
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        
        return $data ? new User($data) : null;
    }

    /**
     * Створити нового користувача
     */
    public function create(User $user): bool
    {
        $sql = "INSERT INTO users (name, email, password, phone, role, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $user->getName(),
            $user->getEmail(),
            $user->getPassword(),
            $user->getPhone(),
            $user->getRole()
        ]);
    }

    /**
     * Оновити користувача
     */
    public function update(User $user): bool
    {
        $sql = "UPDATE users 
                SET name = ?, email = ?, phone = ?, role = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $user->getName(),
            $user->getEmail(),
            $user->getPhone(),
            $user->getRole(),
            $user->getId()
        ]);
    }

    /**
     * Видалити користувача
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}