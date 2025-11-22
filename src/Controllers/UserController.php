<?php
// src/Controllers/UserController.php

namespace App\Controllers;

use App\Services\UserService;
use App\Helpers\Response;

/**
 * Контролер для обробки запитів користувачів
 */
class UserController
{
    private UserService $userService;
    private Response $response;

    public function __construct(UserService $userService, Response $response)
    {
        $this->userService = $userService;
        $this->response = $response;
    }

    /**
     * GET /users - Отримати всіх користувачів
     */
    public function index(): void
    {
        $users = $this->userService->getAllUsers();
        $data = array_map(fn($user) => $user->toArray(), $users);
        
        $this->response->json(['success' => true, 'data' => $data]);
    }

    /**
     * GET /users/{id} - Отримати користувача за ID
     */
    public function show(int $id): void
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            $this->response->json(['success' => false, 'error' => 'Користувача не знайдено'], 404);
            return;
        }

        $this->response->json(['success' => true, 'data' => $user->toArray()]);
    }

    /**
     * POST /users - Створити нового користувача
     */
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->userService->createUser($data);
        
        $statusCode = $result['success'] ? 201 : 400;
        $this->response->json($result, $statusCode);
    }

    /**
     * PUT /users/{id} - Оновити користувача
     */
    public function update(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->userService->updateUser($id, $data);
        
        $statusCode = $result['success'] ? 200 : 400;
        $this->response->json($result, $statusCode);
    }

    /**
     * DELETE /users/{id} - Видалити користувача
     */
    public function destroy(int $id): void
    {
        $result = $this->userService->deleteUser($id);
        
        $statusCode = $result['success'] ? 200 : 400;
        $this->response->json($result, $statusCode);
    }
}