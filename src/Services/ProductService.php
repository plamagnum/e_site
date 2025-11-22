<?php
// src/Services/ProductService.php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Helpers\Validator;

class ProductService
{
    private ProductRepository $productRepository;
    private Validator $validator;

    public function __construct(ProductRepository $productRepository, Validator $validator)
    {
        $this->productRepository = $productRepository;
        $this->validator = $validator;
    }

    public function getAllProducts(): array
    {
        return $this->productRepository->findAll();
    }

    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function getProductsByCategory(string $category): array
    {
        return $this->productRepository->findByCategory($category);
    }

    public function createProduct(array $data): array
    {
        $errors = $this->validator->validateProduct($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice((float)$data['price']);
        $product->setQuantity((int)$data['quantity']);
        $product->setImage($data['image'] ?? null);
        $product->setCategory($data['category']);
        $product->setIsActive(true);

        $result = $this->productRepository->create($product);

        return [
            'success' => $result,
            'message' => $result ? 'Товар успішно створено' : 'Помилка створення товару'
        ];
    }

    public function updateProduct(int $id, array $data): array
    {
        $product = $this->productRepository->findById($id);
        
        if (!$product) {
            return ['success' => false, 'errors' => ['product' => 'Товар не знайдено']];
        }

        $product->setName($data['name'] ?? $product->getName());
        $product->setDescription($data['description'] ?? $product->getDescription());
        $product->setPrice((float)($data['price'] ?? $product->getPrice()));
        $product->setQuantity((int)($data['quantity'] ?? $product->getQuantity()));
        $product->setImage($data['image'] ?? $product->getImage());
        $product->setCategory($data['category'] ?? $product->getCategory());

        $result = $this->productRepository->update($product);

        return [
            'success' => $result,
            'message' => $result ? 'Товар успішно оновлено' : 'Помилка оновлення товару'
        ];
    }

    public function deleteProduct(int $id): array
    {
        $result = $this->productRepository->delete($id);

        return [
            'success' => $result,
            'message' => $result ? 'Товар успішно видалено' : 'Помилка видалення товару'
        ];
    }
}