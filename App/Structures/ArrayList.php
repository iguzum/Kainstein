<?php
namespace App\Structures;

// :: START ARRAY LIST
class ArrayList
{
    private array $items = [];

    public function add(mixed $item): void
    {
        $this->items[] = $item;
    }

    public function get(int $index): mixed
    {
        return $this->items[$index] ?? null;
    }

    public function removeAt(int $index): mixed
    {
        if (!array_key_exists($index, $this->items)) {
            return null;
        }

        $removed = $this->items[$index];
        array_splice($this->items, $index, 1);
        return $removed;
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
// :: END ARRAY LIST
