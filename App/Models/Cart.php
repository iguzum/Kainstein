<?php
namespace App\Models;

use App\Config\Connection;
use InvalidArgumentException;
use PDO;
use RuntimeException;

// :: START CART MODEL
class Cart
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Connection())->connect();
    }

    public function addItem(int $cartId, int $menuId, int $quantity): void
    {
        if ($cartId < 1 || $menuId < 1 || $quantity < 1) {
            throw new InvalidArgumentException('Invalid cart item.');
        }

        $menuCheck = $this->pdo->prepare(
            'SELECT menu_id FROM menu WHERE menu_id = :menu_id AND is_available = 1'
        );
        $menuCheck->execute(['menu_id' => $menuId]);

        if (!$menuCheck->fetchColumn()) {
            throw new RuntimeException('This menu item is unavailable.');
        }

        $findItem = $this->pdo->prepare(
            'SELECT cart_item_id FROM cart_items WHERE cart_id = :cart_id AND menu_id = :menu_id'
        );
        $findItem->execute([
            'cart_id' => $cartId,
            'menu_id' => $menuId,
        ]);
        $cartItemId = $findItem->fetchColumn();

        if ($cartItemId) {
            $updateItem = $this->pdo->prepare(
                'UPDATE cart_items SET quantity = quantity + :quantity WHERE cart_item_id = :cart_item_id'
            );
            $updateItem->execute([
                'quantity' => $quantity,
                'cart_item_id' => $cartItemId,
            ]);
            return;
        }

        $addItem = $this->pdo->prepare(
            'INSERT INTO cart_items (cart_id, menu_id, quantity) VALUES (:cart_id, :menu_id, :quantity)'
        );
        $addItem->execute([
            'cart_id' => $cartId,
            'menu_id' => $menuId,
            'quantity' => $quantity,
        ]);
    }

    public function getItems(int $cartId): array
    {
        if ($cartId < 1) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT ci.menu_id, ci.quantity, m.item_name, m.price,
                    (ci.quantity * m.price) AS line_total
             FROM cart_items ci
             JOIN menu m ON m.menu_id = ci.menu_id
             WHERE ci.cart_id = :cart_id
             ORDER BY ci.cart_item_id'
        );
        $stmt->execute(['cart_id' => $cartId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // :: START REMOVE CART ITEM
    public function removeItem(int $cartId, int $menuId): void
    {
        if ($cartId < 1 || $menuId < 1) {
            throw new InvalidArgumentException('Invalid cart item.');
        }

        $stmt = $this->pdo->prepare(
            'DELETE FROM cart_items WHERE cart_id = :cart_id AND menu_id = :menu_id'
        );
        $stmt->execute([
            'cart_id' => $cartId,
            'menu_id' => $menuId,
        ]);
    }
    // :: END REMOVE CART ITEM
}
// :: END CART MODEL
