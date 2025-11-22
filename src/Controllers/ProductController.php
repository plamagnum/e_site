<?php
// src/Controllers/ProductController.php

namespace App\Controllers;

use App\Services\ProductService;
use App\Helpers\Response;

/**
 * Контролер для обробки запитів товарів
 */
class ProductController
{
    private ProductService $productService;
    private Response $response;

    public function __construct(ProductService $productService, Response $response)
    {
        $this->productService = $productService;
        $this->response = $response;
    }

    /**
     * GET /products - Отримати всі товари
     */
    public function index(): void
    {
        $products = $this->productService->getAllProducts();
        $data = array_map(fn($product) => $product->toArray(), $products);
        
        $this->response->json(['success' => true, 'data' => $data]);
    }

    /**
     * GET /products/{id} - Отримати товар за ID
     */
    public function show(int $id): void
    {
        $product = $this->productService->getProductById($id);
        
        if (!$product) {
            $this->response->json(['success' => false, 'error' => 'Товар не знайдено'], 404);
            return;
        }

        $this->response->json(['success' => true, 'data' => $product->toArray()]);
    }

    /**
     * GET /products/category/{category} - Отримати товари за категорією
     */
    public function byCategory(string $category): void
    {
        $products = $this->productService->getProductsByCategory($category);
        $data = array_map(fn($product) => $product->toArray(), $products);
        
        $this->response->json(['success' => true, 'data' => $data]);
    }

    /**
     * POST /products - Створити новий товар
     */
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Обробка завантаження зображення
        if (isset($_FILES['image'])) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                $data['image'] = $uploadResult['path'];
            }
        }

        $result = $this->productService->createProduct($data);
        
        $statusCode = $result['success'] ? 201 : 400;
        $this->response->json($result, $statusCode);
    }

    /**
     * PUT /products/{id} - Оновити товар
     */
    public function update(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Обробка завантаження зображення
        if (isset($_FILES['image'])) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                $data['image'] = $uploadResult['path'];
            }
        }

        $result = $this->productService->updateProduct($id, $data);
        
        $statusCode = $result['success'] ? 200 : 400;
        $this->response->json($result, $statusCode);
    }

    /**
     * DELETE /products/{id} - Видалити товар
     */
    public function destroy(int $id): void
    {
        $result = $this->productService->deleteProduct($id);
        
        $statusCode = $result['success'] ? 200 : 400;
        $this->response->json($result, $statusCode);
    }

    /**
     * POST /products/search - Пошук товарів
     */
    public function search(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $query = $data['query'] ?? '';
        
        // Тут можна додати метод пошуку в ProductService
        $this->response->json(['success' => true, 'data' => [], 'message' => 'Search functionality']);
    }

    /**
     * Обробка завантаження зображення
     */
    private function handleImageUpload(array $file): array
    {
        $uploadDir = __DIR__ . '/../../storage/uploads/images/';
        
        // Створюємо директорію якщо не існує
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Перевірка типу файлу
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Невірний тип файлу'];
        }

        // Перевірка розміру (макс 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Файл занадто великий'];
        }

        // Генеруємо унікальне ім'я файлу
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('product_') . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Переміщуємо файл
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'path' => '/storage/uploads/images/' . $filename
            ];
        }

        return ['success' => false, 'error' => 'Помилка завантаження файлу'];
    }
}