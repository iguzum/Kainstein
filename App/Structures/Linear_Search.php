<?php
namespace App\Structures;

// :: START STAFF LINEAR SEARCH
class Linear_Search
{
    public function searchStaffByName(array $staff, string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return $staff;
        }

        $matches = [];
        foreach ($staff as $member) {
            $name = (string) ($member['account_name'] ?? '');
            $position = function_exists('mb_stripos')
                ? mb_stripos($name, $query)
                : stripos($name, $query);

            if ($position !== false) {
                $matches[] = $member;
            }
        }

        return $matches;
    }

    // :: START BEGINNER FRIENDLY FOOD LINEAR SEARCH
    public function searchFoodsByName(array $foods, string $query): array
    {
        $query = strtolower(trim($query));
        if ($query === '') {
            return $foods;
        }

        $matches = [];
        foreach ($foods as $food) {
            $foodName = strtolower((string) ($food['item_name'] ?? ''));

            if (strpos($foodName, $query) !== false) {
                $matches[] = $food;
            }
        }

        return $matches;
    }
    // :: END BEGINNER FRIENDLY FOOD LINEAR SEARCH
}
// :: END STAFF LINEAR SEARCH
