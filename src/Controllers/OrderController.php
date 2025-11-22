<?php
// src/Controllers/OrderController.php

namespace App\Controllers;

use App\Services\OrderService;
use App\Helpers\Response;

/**
 * Контролер для обробки запитів замовлень
 */
class OrderController
{
    private OrderService $orderService;
    private Response $response;

    public function __construct(OrderService $orderService, Response $response)
    {
        $this->orderService = $orderService;
        $this->response = $response;
    }

    /**
     * GET /orders - Отримати всі замовлення
     */
    public function index(): void
    {
        $orders = $this->orderService->getAllOrders();
        $data = array_map(fn($order) => $order->toArray(), $orders);
        
        $this->response->json(['success' => true, 'data' => $data]);
    }

    /**
     * GET /orders/{id} - Отримати замовлення за ID
     */
    public function show(int $id): void
    {
        $order = $this->orderService->getOrderById($id);
        
        if (!$order) {
            $this->response->json(['success' => false, 'error' => 'Замовлення не знайдено'], 404);
            return;
        }

        $this->response->json(['success' => true, 'data' => $order->toArray()]);
    }

    /**
     * GET /orders/user/{userId} - Отримати замовлення користувача
     */
    public function userOrders(int $userId): void
    {
        $orders = $this->orderService->getUserOrders($userId);
        $data = array_map(fn($order) => $order->toArray(), $orders);
        
        $this->response->json(['success' => true, 'data' => $data]);
    }

    /**
     * GET /orders/status/{status} - Отримати замовлення за статусом
     */
    public function byStatus(string $status): void
    {
        $orders = $this->orderService->getOrdersByStatus($status);
        $data = array_map(fn($order) => $order->toArray(), $orders);
        
        $this->response->json(['success' => true, 'data' => $data]);
    }

    /**
     * POST /orders - Створити нове замовлення
     */
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->orderService->createOrder($data);
        
        $statusCode = $result['success'] ? 201 : 400;
        $this->response->json($result, $statusCode);
    }

    /**
     * PUT /orders/{id} - Оновити замовлення
     */
    public function update(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->orderService->updateOrder($id, $data);
        
        $statusCode = $result['success'] ? 200 : 400;
        $this->response->json($result, $statusCode);
    }

    /**
     * PATCH /orders/{id}/status - Оновити статус замовлення
     */
    public function updateStatus(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'] ?? '';
        
        $result = $this->orderService->updateOrderStatus($id, $status);
        
        $statusCode = $result['success'] ? 200 : 400;
        $this->response->json($result, $statusCode);
    }

    /**
     * POST /orders/{id}/cancel - Скасувати замовлення
     */
    public function cancel(int $id): void
    {
        $result = $this->orderService->cancelOrder($id);
        
        $statusCode = $result['success'] ? 200 : 400;
        $this->response->json($result, $statusCode);
    }

    /**
     * DELETE /orders/{id} - Видалити замовлення
     */
    public function destroy(int $id): void
    {
        $result = $this->orderService->deleteOrder($id);
        
        $statusCode = $result['success'] ? 200 : 400;
        $this->response->json($result, $statusCode);
    }

    /**
     * GET /orders/statistics - Отримати статистику замовлень
     */
    public function statistics(): void
    {
        $stats = $this->orderService->getOrderStatistics();
        
        $this->response->json(['success' => true, 'data' => $stats]);
    }

    /**
     * POST /orders/date-range - Отримати замовлення за період
     */
    public function dateRange(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $startDate = $data['start_date'] ?? date('Y-m-01');
        $endDate = $data['end_date'] ?? date('Y-m-d');
        
        $orders = $this->orderService->getOrdersByDateRange($startDate, $endDate);
        $data = array_map(fn($order) => $order->toArray(), $orders);
        
        $this->response->json(['success' => true, 'data' => $data]);
    }
}