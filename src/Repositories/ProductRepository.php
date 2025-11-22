<?php
// src/Repositories/ProductRepository.php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Product;
use PDO;

class ProductRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC");
        $products = [];
        
        while ($row = $stmt->fetch()) {
            $products[] = new Product($row);
        }
        
        return $products;
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? new Product($data) : null;
    }

    public function findByCategory(string $category): array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE category = ? AND is_active = 1");
        $stmt->execute([$category]);
        $products = [];
        
        while ($row = $stmt->fetch()) {
            $products[] = new Product($row);
        }
        
        return $products;
    }

    public function create(Product $product): bool
    {
        $sql = "INSERT INTO products (name, description, price, quantity, image, category, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $product->getName(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getQuantity(),
            $product->getImage(),
            $product->getCategory(),
            $product->isActive()
        ]);
    }

    public function update(Product $product): bool
    {
        $sql = "UPDATE products 
                SET name = ?, description = ?, price = ?, quantity = ?, 
                    image = ?, category = ?, is_active = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $product->getName(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getQuantity(),
            $product->getImage(),
            $product->getCategory(),
            $product->isActive(),
            $product->getId()
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}