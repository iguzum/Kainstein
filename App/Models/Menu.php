<?php
namespace App\Models;
use App\Config\Connection;
use PDO;
class Menu
{
    private ?PDO $pdo;
    public function __construct()
    {
        $this->pdo = (new Connection())->connect();
    }

    // :: START ADMIN MENU WRITES
    public function addFood(
        string $item_name,
        string $category,
        string $description,
        float $price,
        string $image_url,
        string $is_available = 'Y',
    ) {
        $stmt = $this->pdo->prepare("
        INSERT INTO menu (item_name, category, description, price, image_url, is_available)
        VALUES (:item_name, :category, :description, :price, :image_url, :is_available)
    ");

        $stmt->execute([
            ":item_name" => $item_name,
            ":category" => $category,
            ":description" => $description,
            ":price" => $price,
            ":image_url" => $image_url,
            ":is_available" => $is_available,
        ]);

        return $this->pdo->lastInsertId();
    }

    public function updateFood(int $menuId, string $itemName, string $category, string $description, float $price, string $imageUrl, string $isAvailable): bool
    {
        $stmt = $this->pdo->prepare('UPDATE menu SET item_name = :name, category = :category, description = :description, price = :price, image_url = :image, is_available = :available WHERE menu_id = :id');
        return $stmt->execute([
            'name' => trim($itemName),
            'category' => $category,
            'description' => trim($description),
            'price' => $price,
            'image' => trim($imageUrl),
            'available' => $isAvailable,
            'id' => $menuId,
        ]);
    }

    public function deleteFood(int $menuId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM menu WHERE menu_id = :id');
        return $stmt->execute(['id' => $menuId]);
    }
    // :: END ADMIN MENU WRITES

    public function getAllMenu()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM menu");
        $stmt->execute();
        $menu = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $menu;
    }

    public function getMainDishes()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM menu WHERE category = 'main_dishes'");
        $stmt->execute();
        $dish = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $dish;
    }

    public function getDesserts()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM menu WHERE category = 'desserts'");
        $stmt->execute();
        $desserts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $desserts;
    }

    public function getStarter()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM menu WHERE category = 'starter'");
        $stmt->execute();
        $starter = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $starter;
    }

    public function getSides()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM menu WHERE category = 'sides'");
        $stmt->execute();
        $sides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $sides;
    }

    public function getDrinks()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM menu WHERE category = 'drinks'");
        $stmt->execute();
        $drinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $drinks;
    }
}

?>
