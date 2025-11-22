<?php
// public/index.php

require_once __DIR__ . '/../src/autoload.php';

use App\Config\Database;
use App\Controllers\UserController;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\AuthService;
use App\Repositories\UserRepository;
use App\Repositories\ProductRepository;
use App\Helpers\Validator;
use App\Helpers\Response;

// Ініціалізація залежностей (Dependency Container)
$validator = new Validator();
$response = new Response();

// Repositories
$userRepository = new UserRepository();
$productRepository = new ProductRepository();

// Services
$userService = new UserService($userRepository, $validator);
$productService = new ProductService($productRepository, $validator);
$authService = new AuthService($userRepository);

// Controllers
$userController = new UserController($userService, $response);

// Простий роутинг
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// API Routes
if (str_starts_with($requestUri, '/api/users')) {
    $id = null;
    if (preg_match('/\/api\/users\/(\d+)/', $requestUri, $matches)) {
        $id = (int)$matches[1];
        print_r($id);
    }

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
} else {
    // Віддаємо HTML сторінку
    include __DIR__ . '/views/index.html';
}