<?php
// src/Controllers/AuthController.php

namespace App\Controllers;

use App\Services\AuthService;
use App\Helpers\Response;

/**
 * Контролер для авторизації
 */
class AuthController
{
    private AuthService $authService;
    private Response $response;

    public function __construct(AuthService $authService, Response $response)
    {
        $this->authService = $authService;
        $this->response = $response;
    }

    /**
     * POST /auth/login - Вхід
     */
    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            $this->response->json([
                'success' => false,
                'error' => 'Email та пароль обов\'язкові'
            ], 400);
            return;
        }

        $result = $this->authService->login($data['email'], $data['password']);
        
        if ($result['success']) {
            // Генеруємо простий токен (в продакшн використовуйте JWT)
            $token = bin2hex(random_bytes(32));
            $_SESSION['token'] = $token;
            
            $result['token'] = $token;
            $this->response->json($result, 200);
        } else {
            $this->response->json($result, 401);
        }
    }

    /**
     * POST /auth/register - Реєстрація
     */
    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $result = $this->authService->register($data);
        
        if ($result['success']) {
            // Генеруємо токен
            $token = bin2hex(random_bytes(32));
            $_SESSION['token'] = $token;
            
            $result['token'] = $token;
            $this->response->json($result, 201);
        } else {
            $this->response->json($result, 400);
        }
    }

    /**
     * POST /auth/logout - Вихід
     */
    public function logout(): void
    {
        $this->authService->logout();
        
        $this->response->json([
            'success' => true,
            'message' => 'Успішний вихід'
        ]);
    }

    /**
     * GET /auth/me - Отримати поточного користувача
     */
    public function me(): void
    {
        if (!$this->authService->isAuthenticated()) {
            $this->response->json([
                'success' => false,
                'error' => 'Не авторизований'
            ], 401);
            return;
        }

        $user = $this->authService->getCurrentUser();
        
        $this->response->json([
            'success' => true,
            'data' => $user ? $user->toArray() : null
        ]);
    }
}