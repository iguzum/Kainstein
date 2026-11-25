<?php
namespace App\Structures;

// :: START BUBBLE SORT
class Bubble_Sort
{
    public function sortByPrice(array $items, string $direction = 'asc'): array
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';
        $count = count($items);

        for ($i = 0; $i < $count - 1; $i++) {
            for ($j = 0; $j < $count - $i - 1; $j++) {
                $leftPrice = (float) ($items[$j]['price'] ?? 0);
                $rightPrice = (float) ($items[$j + 1]['price'] ?? 0);
                $shouldSwap = $direction === 'asc'
                    ? $leftPrice > $rightPrice
                    : $leftPrice < $rightPrice;

                if ($shouldSwap) {
                    [$items[$j], $items[$j + 1]] = [$items[$j + 1], $items[$j]];
                }
            }
        }

        return $items;
    }
}
// :: END BUBBLE SORT
