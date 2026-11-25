<?php
namespace App\Structures;

// :: START ARRAY LIST ORDER QUEUE
class Order_Queue
{
    // :: START ARRAY LIST STORAGE
    private ArrayList $items;

    public function __construct()
    {
        $this->items = new ArrayList();
    }
    // :: END ARRAY LIST STORAGE

    public function enqueue(array $order): void
    {
        // :: START ENQUEUE ORDER
        $this->items->add($order);
        // :: END ENQUEUE ORDER
    }

    public function dequeue(): ?array
    {
        // :: START DEQUEUE OLDEST ORDER
        $order = $this->items->removeAt(0);
        return is_array($order) ? $order : null;
        // :: END DEQUEUE OLDEST ORDER
    }

    public function peek(): ?array
    {
        // :: START PEEK OLDEST ORDER
        $order = $this->items->get(0);
        return is_array($order) ? $order : null;
        // :: END PEEK OLDEST ORDER
    }

    public function all(): array
    {
        // :: START READ QUEUE ORDERS
        return $this->items->toArray();
        // :: END READ QUEUE ORDERS
    }
}
// :: END ARRAY LIST ORDER QUEUE
