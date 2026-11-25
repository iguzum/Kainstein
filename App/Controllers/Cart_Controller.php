<?php
namespace App\Controllers;

use App\Models\Cart;

// :: START CART CONTROLLER
class Cart_Controller
{
    private Cart $cart;

    public function __construct()
    {
        $this->cart = new Cart();
    }

    public function addToCart(int $cartId, int $menuId, int $quantity): void
    {
        $this->cart->addItem($cartId, $menuId, $quantity);
    }

    public function getCartItems(int $cartId): array
    {
        return $this->cart->getItems($cartId);
    }

    // :: START REMOVE CART ITEM
    public function removeFromCart(int $cartId, int $menuId): void
    {
        $this->cart->removeItem($cartId, $menuId);
    }
    // :: END REMOVE CART ITEM
}
// :: END CART CONTROLLER
