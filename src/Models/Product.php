<?php
// src/Models/Product.php

namespace App\Models;

use DateTime;

/**
 * Модель товару
 */
class Product
{
    private ?int $id = null;
    private string $name;
    private string $description;
    private float $price;
    private int $quantity;
    private ?string $image = null;
    private string $category;
    private bool $isActive = true;
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    public function fill(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->price = (float)($data['price'] ?? 0);
        $this->quantity = (int)($data['quantity'] ?? 0);
        $this->image = $data['image'] ?? null;
        $this->category = $data['category'] ?? '';
        $this->isActive = (bool)($data['is_active'] ?? true);
        
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
    public function getDescription(): string { return $this->description; }
    public function getPrice(): float { return $this->price; }
    public function getQuantity(): int { return $this->quantity; }
    public function getImage(): ?string { return $this->image; }
    public function getCategory(): string { return $this->category; }
    public function isActive(): bool { return $this->isActive; }
    public function getCreatedAt(): ?DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTime { return $this->updatedAt; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setPrice(float $price): void { $this->price = $price; }
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }
    public function setImage(?string $image): void { $this->image = $image; }
    public function setCategory(string $category): void { $this->category = $category; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'image' => $this->image,
            'category' => $this->category,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}