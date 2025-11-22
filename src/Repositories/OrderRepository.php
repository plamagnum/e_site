<?php
// src/Repositories/OrderRepository.php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Order;
use PDO;

/**
 * Репозиторій для роботи із замовленнями в БД
 */
class OrderRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Знайти всі замовлення
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM orders ORDER BY created_at DESC");
        $orders = [];
        
        while ($row = $stmt->fetch()) {
            $order = new Order($row);
            $order->setItems($this->getOrderItems($order->getId()));
            $orders[] = $order;
        }
        
        return $orders;
    }

    /**
     * Знайти замовлення за ID
     */
    public function findById(int $id): ?Order
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if (!$data) {
            return null;
        }

        $order = new Order($data);
        $order->setItems($this->getOrderItems($id));
        
        return $order;
    }

    /**
     * Знайти замовлення користувача
     */
    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $orders = [];
        
        while ($row = $stmt->fetch()) {
            $order = new Order($row);
            $order->setItems($this->getOrderItems($order->getId()));
            $orders[] = $order;
        }
        
        return $orders;
    }

    /**
     * Знайти замовлення за статусом
     */
    public function findByStatus(string $status): array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE status = ? ORDER BY created_at DESC");
        $stmt->execute([$status]);
        $orders = [];
        
        while ($row = $stmt->fetch()) {
            $order = new Order($row);
            $order->setItems($this->getOrderItems($order->getId()));
            $orders[] = $order;
        }
        
        return $orders;
    }

    /**
     * Створити нове замовлення
     */
    public function create(Order $order): ?int
    {
        try {
            $this->db->beginTransaction();

            // Створюємо замовлення
            $sql = "INSERT INTO orders (user_id, total_amount, status, shipping_address, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $order->getUserId(),
                $order->getTotalAmount(),
                $order->getStatus(),
                $order->getShippingAddress()
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // Додаємо товари замовлення
            if (!empty($order->getItems())) {
                $this->addOrderItems($orderId, $order->getItems());
            }

            $this->db->commit();
            
            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Оновити замовлення
     */
    public function update(Order $order): bool
    {
        $sql = "UPDATE orders 
                SET status = ?, shipping_address = ?, total_amount = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $order->getStatus(),
            $order->getShippingAddress(),
            $order->getTotalAmount(),
            $order->getId()
        ]);
    }

    /**
     * Оновити статус замовлення
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $orderId]);
    }

    /**
     * Видалити замовлення
     */
    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            // Спочатку видаляємо товари замовлення
            $stmt = $this->db->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$id]);

            // Потім саме замовлення
            $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
            $result = $stmt->execute([$id]);

            $this->db->commit();
            
            return $result;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Отримати товари замовлення
     */
    private function getOrderItems(int $orderId): array
    {
        $sql = "SELECT oi.*, p.name as product_name, p.image as product_image 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Додати товари до замовлення
     */
    private function addOrderItems(int $orderId, array $items): void
    {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        foreach ($items as $item) {
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
        }
    }

    /**
     * Отримати статистику замовлень
     */
    public function getStatistics(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as average_order_value,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders
                FROM orders";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }

    /**
     * Отримати замовлення за період
     */
    public function findByDateRange(string $startDate, string $endDate): array
    {
        $sql = "SELECT * FROM orders 
                WHERE DATE(created_at) BETWEEN ? AND ? 
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $orders = [];
        
        while ($row = $stmt->fetch()) {
            $order = new Order($row);
            $order->setItems($this->getOrderItems($order->getId()));
            $orders[] = $order;
        }
        
        return $orders;
    }
}