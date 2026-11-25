<?php
namespace App\Models;

use App\Config\Connection;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

// :: START ORDER MODEL
class Order
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Connection())->connect();
    }

    public function placeOrder(int $orderId, int $customerId, int $cartId, string $customerName): void
    {
        if ($orderId < 1 || $customerId < 1 || $cartId < 1) {
            throw new InvalidArgumentException('Order information is missing.');
        }

        $this->pdo->beginTransaction();

        try {
            $orderCheck = $this->pdo->prepare(
                'SELECT order_id FROM orders WHERE order_id = :order_id AND customer_id = :customer_id'
            );
            $orderCheck->execute([
                'order_id' => $orderId,
                'customer_id' => $customerId,
            ]);

            if (!$orderCheck->fetchColumn()) {
                throw new RuntimeException('Order could not be found.');
            }

            $cartCheck = $this->pdo->prepare(
                'SELECT COUNT(*) FROM cart_items WHERE cart_id = :cart_id'
            );
            $cartCheck->execute(['cart_id' => $cartId]);

            if ((int) $cartCheck->fetchColumn() < 1) {
                throw new InvalidArgumentException('Your cart is empty.');
            }

            $updateCustomer = $this->pdo->prepare(
                'UPDATE customer SET customer_name = :customer_name WHERE customer_id = :customer_id'
            );
            $updateCustomer->execute([
                'customer_name' => $customerName,
                'customer_id' => $customerId,
            ]);

            // :: START SAVE ORDER PLACEMENT TIME
            $setPlacedAt = $this->pdo->prepare(
                'UPDATE orders SET placed_at = CURRENT_TIMESTAMP WHERE order_id = :order_id'
            );
            $setPlacedAt->execute(['order_id' => $orderId]);
            // :: END SAVE ORDER PLACEMENT TIME

            $copyItems = $this->pdo->prepare(
                'INSERT INTO order_items (order_id, menu_id, quantity, unit_price)
                 SELECT :order_id, ci.menu_id, ci.quantity, m.price
                 FROM cart_items ci
                 JOIN menu m ON m.menu_id = ci.menu_id
                 WHERE ci.cart_id = :cart_id'
            );
            $copyItems->execute([
                'order_id' => $orderId,
                'cart_id' => $cartId,
            ]);

            $clearCart = $this->pdo->prepare(
                'DELETE FROM cart_items WHERE cart_id = :cart_id'
            );
            $clearCart->execute(['cart_id' => $cartId]);

            $this->pdo->commit();
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    // :: START STAFF ORDER QUEUE DATA
    public function getPendingOrders(): array
    {
        // :: START FIFO ORDER SORT
        $stmt = $this->pdo->prepare(
            "SELECT o.order_id, o.order_type, c.customer_name,
                    oi.quantity, oi.unit_price, m.item_name
             FROM orders o
             JOIN customer c ON c.customer_id = o.customer_id
             JOIN order_items oi ON oi.order_id = o.order_id
             JOIN menu m ON m.menu_id = oi.menu_id
             WHERE o.order_status = 'Pending'
             ORDER BY (o.placed_at IS NULL) DESC, o.placed_at ASC,
                      o.order_id ASC, oi.order_item_id ASC"
        );
        // :: END FIFO ORDER SORT
        $stmt->execute();

        $orders = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orderId = (int) $row['order_id'];

            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'order_id' => $orderId,
                    'customer_name' => $row['customer_name'],
                    'order_type' => $row['order_type'],
                    'items' => [],
                    'total' => 0,
                ];
            }

            $quantity = (int) $row['quantity'];
            $unitPrice = (float) $row['unit_price'];
            $lineTotal = $quantity * $unitPrice;

            $orders[$orderId]['items'][] = [
                'item_name' => $row['item_name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
            $orders[$orderId]['total'] += $lineTotal;
        }

        return array_values($orders);
    }

    public function markCompleted(int $orderId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE orders SET order_status = 'Completed'
             WHERE order_id = :order_id AND order_status = 'Pending'"
        );
        $stmt->execute(['order_id' => $orderId]);

        if ($stmt->rowCount() < 1) {
            throw new RuntimeException('This order is no longer in the queue.');
        }
    }
    // :: END STAFF ORDER QUEUE DATA
}
// :: END ORDER MODEL
