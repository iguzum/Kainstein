<?php
namespace App\Models;

use App\Config\Connection;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class Customer
{
    private ?PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Connection())->connect();
    }

    // :: START CREATE ORDER WITH CUSTOMER NAME
    public function startSessionOrder(string $order_type, string $customerName = 'Guest'): array
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO customer(customer_name) VALUES(:customer_name)"
        );
        $stmt->execute([":customer_name" => $customerName]);
        $customer_id = (int) $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare(
            "INSERT INTO cart (customer_id) VALUES (:customer_id)"
        );
        $stmt->execute([":customer_id" => $customer_id]);
        $cart_id = (int) $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare(
            "INSERT INTO orders (customer_id, order_type) VALUES (:customer_id, :order_type)"
        );
        $stmt->execute([
            ":customer_id" => $customer_id,
            ":order_type" => $order_type
        ]);
        $order_id = (int) $this->pdo->lastInsertId();

        return [
            'customer_id' => $customer_id,
            'cart_id' => $cart_id,
            'order_id' => $order_id
        ];
    }
    // :: END CREATE ORDER WITH CUSTOMER NAME

    // :: START CREATE STAFF DRAFT CART
    public function startStaffCart(): array
    {
        $this->pdo->beginTransaction();

        try {
            $addCustomer = $this->pdo->prepare('INSERT INTO customer (customer_name) VALUES (:name)');
            $addCustomer->execute(['name' => 'Guest']);
            $customerId = (int) $this->pdo->lastInsertId();

            $addCart = $this->pdo->prepare('INSERT INTO cart (customer_id) VALUES (:customer_id)');
            $addCart->execute(['customer_id' => $customerId]);
            $cartId = (int) $this->pdo->lastInsertId();

            $this->pdo->commit();
            return ['customer_id' => $customerId, 'cart_id' => $cartId];
        } catch (\Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }
    // :: END CREATE STAFF DRAFT CART

    // :: START PREPARE STAFF ORDER
    public function prepareStaffOrder(int $customerId, int $existingOrderId, string $customerName, string $orderType): int
    {
        $this->pdo->beginTransaction();

        try {
            $customerCheck = $this->pdo->prepare('SELECT customer_id FROM customer WHERE customer_id = :id');
            $customerCheck->execute(['id' => $customerId]);
            if (!$customerCheck->fetchColumn()) {
                throw new RuntimeException('The customer cart could not be found.');
            }

            $updateCustomer = $this->pdo->prepare('UPDATE customer SET customer_name = :name WHERE customer_id = :id');
            $updateCustomer->execute(['name' => $customerName, 'id' => $customerId]);

            if ($existingOrderId > 0) {
                $orderCheck = $this->pdo->prepare('SELECT order_id FROM orders WHERE order_id = :order_id AND customer_id = :customer_id');
                $orderCheck->execute(['order_id' => $existingOrderId, 'customer_id' => $customerId]);
                $orderId = (int) $orderCheck->fetchColumn();
            } else {
                $orderId = 0;
            }

            if ($orderId > 0) {
                $updateOrder = $this->pdo->prepare('UPDATE orders SET order_type = :type WHERE order_id = :id');
                $updateOrder->execute(['type' => $orderType, 'id' => $orderId]);
            } else {
                $addOrder = $this->pdo->prepare('INSERT INTO orders (customer_id, order_type) VALUES (:customer_id, :type)');
                $addOrder->execute(['customer_id' => $customerId, 'type' => $orderType]);
                $orderId = (int) $this->pdo->lastInsertId();
            }

            $this->pdo->commit();
            return $orderId;
        } catch (\Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }
    // :: END PREPARE STAFF ORDER

    // :: START UPDATE CURRENT ORDER TYPE
    public function updateOrderType(int $orderId, int $customerId, string $orderType): void
    {
        if (!in_array($orderType, ['dine_in', 'take_out'], true)) {
            throw new InvalidArgumentException('Invalid order type.');
        }

        $checkOrder = $this->pdo->prepare(
            'SELECT order_id FROM orders WHERE order_id = :order_id AND customer_id = :customer_id'
        );
        $checkOrder->execute([
            'order_id' => $orderId,
            'customer_id' => $customerId,
        ]);

        if (!$checkOrder->fetchColumn()) {
            throw new RuntimeException('The current order could not be found.');
        }

        $updateOrder = $this->pdo->prepare(
            'UPDATE orders SET order_type = :order_type WHERE order_id = :order_id AND customer_id = :customer_id'
        );
        $updateOrder->execute([
            'order_type' => $orderType,
            'order_id' => $orderId,
            'customer_id' => $customerId,
        ]);
    }
    // :: END UPDATE CURRENT ORDER TYPE
}
