<?php
// public/index.php

// Ініціалізація сесії
//session_start();

// Дозволяємо CORS для розробки
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Обробка preflight запитів
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../src/autoload.php';

use App\Config\Database;
use App\Controllers\UserController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;
use App\Controllers\AuthController;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\AuthService;
use App\Repositories\UserRepository;
use App\Repositories\ProductRepository;
use App\Repositories\OrderRepository;
use App\Helpers\Validator;
use App\Helpers\Response;

// Ініціалізація залежностей
$validator = new Validator();
$response = new Response();

// Repositories
$userRepository = new UserRepository();
$productRepository = new ProductRepository();
$orderRepository = new OrderRepository();

// Services
$userService = new UserService($userRepository, $validator);
$productService = new ProductService($productRepository, $validator);
$orderService = new OrderService($orderRepository, $productRepository, $validator);
$authService = new AuthService($userRepository);

// Controllers
$userController = new UserController($userService, $response);
$productController = new ProductController($productService, $response);
$orderController = new OrderController($orderService, $response);
$authController = new AuthController($authService, $response);

// Простий роутинг
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// API Routes - ЗМІНЕНО str_starts_with на strpos
if (strpos($requestUri, '/api/') === 0) {
    
    // Auth routes
    if ($requestUri === '/api/auth/login' && $requestMethod === 'POST') {
        $authController->login();
        exit;
    }
    
    if ($requestUri === '/api/auth/register' && $requestMethod === 'POST') {
        $authController->register();
        exit;
    }
    
    if ($requestUri === '/api/auth/logout' && $requestMethod === 'POST') {
        $authController->logout();
        exit;
    }
    
    if ($requestUri === '/api/auth/me' && $requestMethod === 'GET') {
        $authController->me();
        exit;
    }

    // User routes
    if (preg_match('#^/api/users(/(\d+))?$#', $requestUri, $matches)) {
        $id = isset($matches[2]) ? (int)$matches[2] : null;

        switch ($requestMethod) {
            case 'GET':
                if ($id) {
                    $userController->show($id);
                } else {
                    $userController->index();
                }
                break;
            case 'POST':
                $userController->store();
                break;
            case 'PUT':
                if ($id) {
                    $userController->update($id);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $userController->destroy($id);
                }
                break;
        }
        exit;
    }

    // Product routes
    if (preg_match('#^/api/products(/(\d+))?$#', $requestUri, $matches)) {
        $id = isset($matches[2]) ? (int)$matches[2] : null;

        switch ($requestMethod) {
            case 'GET':
                if ($id) {
                    $productController->show($id);
                } else {
                    $productController->index();
                }
                break;
            case 'POST':
                $productController->store();
                break;
            case 'PUT':
                if ($id) {
                    $productController->update($id);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $productController->destroy($id);
                }
                break;
        }
        exit;
    }

    // Order routes
    if (preg_match('#^/api/orders(/(\d+))?$#', $requestUri, $matches)) {
        $id = isset($matches[2]) ? (int)$matches[2] : null;

        switch ($requestMethod) {
            case 'GET':
                if ($id) {
                    $orderController->show($id);
                } else {
                    $orderController->index();
                }
                break;
            case 'POST':
                $orderController->store();
                break;
            case 'PUT':
                if ($id) {
                    $orderController->update($id);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $orderController->destroy($id);
                }
                break;
        }
        exit;
    }

    // Якщо маршрут не знайдено
    $response->json(['success' => false, 'error' => 'Route not found'], 404);
    exit;
}

// Віддаємо HTML сторінку для всіх інших запитів
if (file_exists(__DIR__ . '/views/index.html')) {
    include __DIR__ . '/views/index.html';
} else {
    echo '<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>E-Commerce Site</title>
</head>
<body>
    <h1>Помилка: index.html не знайдено</h1>
    <p>Створіть файл public/views/index.html</p>
</body>
</html>';
}