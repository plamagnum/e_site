<?php
// src/Models/Order.php

namespace App\Models;

use DateTime;

/**
 * Модель замовлення
 */
class Order
{
    private ?int $id = null;
    private int $userId;
    private float $totalAmount;
    private string $status = 'pending';
    private ?string $shippingAddress = null;
    private array $items = [];
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
        $this->userId = (int)($data['user_id'] ?? 0);
        $this->totalAmount = (float)($data['total_amount'] ?? 0);
        $this->status = $data['status'] ?? 'pending';
        $this->shippingAddress = $data['shipping_address'] ?? null;
        $this->items = $data['items'] ?? [];
        
        if (isset($data['created_at'])) {
            $this->createdAt = new DateTime($data['created_at']);
        }
        if (isset($data['updated_at'])) {
            $this->updatedAt = new DateTime($data['updated_at']);
        }
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getTotalAmount(): float { return $this->totalAmount; }
    public function getStatus(): string { return $this->status; }
    public function getShippingAddress(): ?string { return $this->shippingAddress; }
    public function getItems(): array { return $this->items; }
    public function getCreatedAt(): ?DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTime { return $this->updatedAt; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setUserId(int $userId): void { $this->userId = $userId; }
    public function setTotalAmount(float $totalAmount): void { $this->totalAmount = $totalAmount; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setShippingAddress(?string $address): void { $this->shippingAddress = $address; }
    public function setItems(array $items): void { $this->items = $items; }

    public function addItem(array $item): void
    {
        $this->items[] = $item;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'total_amount' => $this->totalAmount,
            'status' => $this->status,
            'shipping_address' => $this->shippingAddress,
            'items' => $this->items,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}