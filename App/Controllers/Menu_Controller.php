<?php
namespace App\Controllers;
use App\Models\Menu;

class Menu_Controller
{
    private Menu $menu;

    public function __construct()
    {
        $this->menu = new Menu();
    }

    // ADD FOOD — signature must match Menu::addFood() so params forward cleanly
    public function addFood(
        string $item_name,
        string $category,
        string $description,
        float $price,
        string $image_url,
        string $is_available = 'Y',
    ) {
        // :: START ADD FOOD VALIDATION
        if (!$this->validFood($item_name, $category, $price, $image_url, $is_available)) {
            throw new \InvalidArgumentException('Please enter valid menu details.');
        }
        // :: END ADD FOOD VALIDATION

        return $this->menu->addFood(
            $item_name,
            $category,
            $description,
            $price,
            $image_url,
            $is_available,
        );
    }

    // :: START ADMIN MENU ACTIONS
    public function updateFood(int $menuId, string $itemName, string $category, string $description, float $price, string $imageUrl, string $isAvailable): bool
    {
        if ($menuId < 1 || !$this->validFood($itemName, $category, $price, $imageUrl, $isAvailable)) {
            return false;
        }

        return $this->menu->updateFood($menuId, $itemName, $category, $description, $price, $imageUrl, $isAvailable);
    }

    public function deleteFood(int $menuId): bool
    {
        return $menuId > 0 && $this->menu->deleteFood($menuId);
    }

    public function validFood(string $itemName, string $category, float $price, string $imageUrl, string $isAvailable): bool
    {
        return trim($itemName) !== ''
            && in_array($category, ['main_dishes', 'desserts', 'starter', 'sides', 'drinks'], true)
            && $price >= 0
            && filter_var($imageUrl, FILTER_VALIDATE_URL) !== false
            && in_array($isAvailable, ['Y', 'N'], true);
    }
    // :: END ADMIN MENU ACTIONS

    // GET ALL MENU
    public function getAllMenu()
    {
        return $this->menu->getAllMenu();
    }

    // :: START MENU CATEGORY FILTER
    public function getMenuByCategory(string $category): array
    {
        // :: START DATABASE CATEGORY VALUES
        $categoryMap = [
            'all' => null,
            'main_dishes' => 'main_dishes',
            'desserts' => 'desserts',
            'starter' => 'starter',
            'sides' => 'sides',
            'drinks' => 'drinks',
        ];
        // :: END DATABASE CATEGORY VALUES

        $category = strtolower(trim($category));
        if (!array_key_exists($category, $categoryMap)) {
            $category = 'all';
        }

        $items = $this->getAllMenu();
        $selectedCategory = $categoryMap[$category];

        if ($selectedCategory === null) {
            return $items;
        }

        return array_values(array_filter(
            $items,
            static fn(array $item): bool => strtolower((string) ($item['category'] ?? '')) === strtolower($selectedCategory)
        ));
    }
    // :: END MENU CATEGORY FILTER

    // GET MAIN DISHES
    public function getMainDishes()
    {
        return $this->menu->getMainDishes();
    }

    // GET DESSERTS
    public function getDesserts()
    {
        return $this->menu->getDesserts();
    }

    // GET STARTERS
    public function getStarter()
    {
        return $this->menu->getStarter();
    }

    // GET SIDES
    public function getSides()
    {
        return $this->menu->getSides();
    }

    // GET DRINKS
    public function getDrinks()
    {
        return $this->menu->getDrinks();
    }
}
