<?php
namespace App\Controllers;

use App\Models\Order;
use App\Structures\Order_Queue;
use InvalidArgumentException;

// :: START ORDER CONTROLLER
class Order_Controller
{
    private Order $order;

    public function __construct()
    {
        $this->order = new Order();
    }

    public function placeOrder(int $orderId, int $customerId, int $cartId, string $customerName): void
    {
        $customerName = trim($customerName);

        if ($customerName === '' || mb_strlen($customerName) > 50) {
            throw new InvalidArgumentException('Enter your name (up to 50 characters).');
        }

        $this->order->placeOrder($orderId, $customerId, $cartId, $customerName);
    }

    // :: START STAFF FIFO QUEUE
    public function getOrderQueue(): Order_Queue
    {
        $queue = new Order_Queue();

        foreach ($this->order->getPendingOrders() as $order) {
            $queue->enqueue($order);
        }

        return $queue;
    }

    public function completeNextOrder(): array
    {
        $queue = $this->getOrderQueue();
        $nextOrder = $queue->peek();

        if ($nextOrder === null) {
            throw new InvalidArgumentException('There are no pending orders.');
        }

        $this->order->markCompleted((int) $nextOrder['order_id']);
        $queue->dequeue();

        return $nextOrder;
    }
    // :: END STAFF FIFO QUEUE
}
// :: END ORDER CONTROLLER
