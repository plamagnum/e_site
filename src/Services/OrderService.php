<?php
// src/Services/OrderService.php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Helpers\Validator;

/**
 * Сервіс для бізнес-логіки роботи із замовленнями
 */
class OrderService
{
    private OrderRepository $orderRepository;
    private ProductRepository $productRepository;
    private Validator $validator;

    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        Validator $validator
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->validator = $validator;
    }

    /**
     * Отримати всі замовлення
     */
    public function getAllOrders(): array
    {
        return $this->orderRepository->findAll();
    }

    /**
     * Отримати замовлення за ID
     */
    public function getOrderById(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }

    /**
     * Отримати замовлення користувача
     */
    public function getUserOrders(int $userId): array
    {
        return $this->orderRepository->findByUserId($userId);
    }

    /**
     * Отримати замовлення за статусом
     */
    public function getOrdersByStatus(string $status): array
    {
        return $this->orderRepository->findByStatus($status);
    }

    /**
     * Створити нове замовлення
     */
    public function createOrder(array $data): array
    {
        // Валідація базових даних
        if (empty($data['user_id'])) {
            return ['success' => false, 'errors' => ['user_id' => 'ID користувача обов\'язковий']];
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            return ['success' => false, 'errors' => ['items' => 'Замовлення повинно містити товари']];
        }

        // Перевірка наявності товарів та обчислення суми
        $totalAmount = 0;
        $validatedItems = [];

        foreach ($data['items'] as $item) {
            if (empty($item['product_id']) || empty($item['quantity'])) {
                return ['success' => false, 'errors' => ['items' => 'Невірний формат товарів']];
            }

            $product = $this->productRepository->findById($item['product_id']);
            
            if (!$product) {
                return [
                    'success' => false, 
                    'errors' => ['items' => "Товар з ID {$item['product_id']} не знайдено"]
                ];
            }

            if ($product->getQuantity() < $item['quantity']) {
                return [
                    'success' => false, 
                    'errors' => ['items' => "Недостатньо товару '{$product->getName()}' на складі"]
                ];
            }

            $itemTotal = $product->getPrice() * $item['quantity'];
            $totalAmount += $itemTotal;

            $validatedItems[] = [
                'product_id' => $product->getId(),
                'quantity' => $item['quantity'],
                'price' => $product->getPrice()
            ];

            // Зменшуємо кількість товару на складі
            $product->setQuantity($product->getQuantity() - $item['quantity']);
            $this->productRepository->update($product);
        }

        // Створюємо замовлення
        $order = new Order();
        $order->setUserId($data['user_id']);
        $order->setTotalAmount($totalAmount);
        $order->setStatus($data['status'] ?? 'pending');
        $order->setShippingAddress($data['shipping_address'] ?? null);
        $order->setItems($validatedItems);

        try {
            $orderId = $this->orderRepository->create($order);
            
            if ($orderId) {
                return [
                    'success' => true,
                    'message' => 'Замовлення успішно створено',
                    'order_id' => $orderId,
                    'total_amount' => $totalAmount
                ];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['order' => 'Помилка створення замовлення: ' . $e->getMessage()]];
        }

        return ['success' => false, 'errors' => ['order' => 'Помилка створення замовлення']];
    }

    /**
     * Оновити статус замовлення
     */
    public function updateOrderStatus(int $orderId, string $status): array
    {
        $order = $this->orderRepository->findById($orderId);
        
        if (!$order) {
            return ['success' => false, 'errors' => ['order' => 'Замовлення не знайдено']];
        }

        // Валідація статусу
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'errors' => ['status' => 'Невірний статус замовлення']];
        }

        // Якщо замовлення скасовується, повертаємо товари на склад
        if ($status === 'cancelled' && $order->getStatus() !== 'cancelled') {
            $this->returnProductsToStock($order);
        }

        $result = $this->orderRepository->updateStatus($orderId, $status);

        return [
            'success' => $result,
            'message' => $result ? 'Статус замовлення оновлено' : 'Помилка оновлення статусу'
        ];
    }

    /**
     * Оновити замовлення
     */
    public function updateOrder(int $id, array $data): array
    {
        $order = $this->orderRepository->findById($id);
        
        if (!$order) {
            return ['success' => false, 'errors' => ['order' => 'Замовлення не знайдено']];
        }

        if (isset($data['status'])) {
            $order->setStatus($data['status']);
        }

        if (isset($data['shipping_address'])) {
            $order->setShippingAddress($data['shipping_address']);
        }

        $result = $this->orderRepository->update($order);

        return [
            'success' => $result,
            'message' => $result ? 'Замовлення успішно оновлено' : 'Помилка оновлення замовлення'
        ];
    }

    /**
     * Скасувати замовлення
     */
    public function cancelOrder(int $orderId): array
    {
        return $this->updateOrderStatus($orderId, 'cancelled');
    }

    /**
     * Видалити замовлення
     */
    public function deleteOrder(int $id): array
    {
        $order = $this->orderRepository->findById($id);
        
        if (!$order) {
            return ['success' => false, 'errors' => ['order' => 'Замовлення не знайдено']];
        }

        // Повертаємо товари на склад якщо замовлення не виконано
        if ($order->getStatus() !== 'completed' && $order->getStatus() !== 'cancelled') {
            $this->returnProductsToStock($order);
        }

        $result = $this->orderRepository->delete($id);

        return [
            'success' => $result,
            'message' => $result ? 'Замовлення успішно видалено' : 'Помилка видалення замовлення'
        ];
    }

    /**
     * Повернути товари на склад
     */
    private function returnProductsToStock(Order $order): void
    {
        foreach ($order->getItems() as $item) {
            $product = $this->productRepository->findById($item['product_id']);
            
            if ($product) {
                $product->setQuantity($product->getQuantity() + $item['quantity']);
                $this->productRepository->update($product);
            }
        }
    }

    /**
     * Отримати статистику замовлень
     */
    public function getOrderStatistics(): array
    {
        return $this->orderRepository->getStatistics();
    }

    /**
     * Отримати замовлення за період
     */
    public function getOrdersByDateRange(string $startDate, string $endDate): array
    {
        return $this->orderRepository->findByDateRange($startDate, $endDate);
    }

    /**
     * Обчислити загальну суму замовлення
     */
    public function calculateOrderTotal(array $items): float
    {
        $total = 0;

        foreach ($items as $item) {
            $product = $this->productRepository->findById($item['product_id']);
            if ($product) {
                $total += $product->getPrice() * $item['quantity'];
            }
        }

        return $total;
    }
}