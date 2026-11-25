<?php
namespace App\Controllers;
use App\Models\Customer;
class Create_Customer_Controller
{
    public function create()
    {
        // :: START PRESERVE CURRENT ORDER SESSION
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $orderType = $_POST['order_type'] ?? null;
        if (!is_string($orderType) || !in_array($orderType, ['dine_in', 'take_out'], true)) {
            return "Please select how you'd like to order.";
        }

        $customer = new Customer();
        $customerId = (int) ($_SESSION['customer_id'] ?? 0);
        $cartId = (int) ($_SESSION['cart_id'] ?? 0);
        $orderId = (int) ($_SESSION['order_id'] ?? 0);

        if ($customerId > 0 && $cartId > 0 && $orderId > 0) {
            $customer->updateOrderType($orderId, $customerId, $orderType);
            $_SESSION['order_type'] = $orderType;
        } else {
            $ids = $customer->startSessionOrder($orderType);
            $_SESSION['customer_id'] = $ids['customer_id'];
            $_SESSION['cart_id'] = $ids['cart_id'];
            $_SESSION['order_id'] = $ids['order_id'];
            $_SESSION['order_type'] = $orderType;
        }

        // :: START MENU PAGE ROOT REDIRECT
        header('Location: ./menu.php');
        // :: END MENU PAGE ROOT REDIRECT
        exit;
        // :: END PRESERVE CURRENT ORDER SESSION
    }

    // :: START CREATE STAFF DRAFT CART
    public function startStaffCart(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $ids = (new Customer())->startStaffCart();
        $_SESSION['staff_customer_id'] = $ids['customer_id'];
        $_SESSION['staff_cart_id'] = $ids['cart_id'];
        return $ids;
    }
    // :: END CREATE STAFF DRAFT CART

    // :: START PREPARE STAFF ORDER DETAILS
    public function prepareStaffOrder(string $customerName, string $orderType): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $customerName = trim($customerName);
        if ($customerName === '' || mb_strlen($customerName) > 50) {
            throw new \InvalidArgumentException('Enter a name up to 50 characters.');
        }
        if (!in_array($orderType, ['dine_in', 'take_out'], true)) {
            throw new \InvalidArgumentException('Choose dine in or take out.');
        }

        $customerId = (int) ($_SESSION['staff_customer_id'] ?? 0);
        if ($customerId < 1) {
            throw new \InvalidArgumentException('Add an item to your cart before placing an order.');
        }

        $orderId = (new Customer())->prepareStaffOrder(
            $customerId,
            (int) ($_SESSION['staff_order_id'] ?? 0),
            $customerName,
            $orderType
        );
        $_SESSION['staff_order_id'] = $orderId;
        $_SESSION['staff_order_name'] = $customerName;
        $_SESSION['staff_order_type'] = $orderType;

        return $orderId;
    }
    // :: END PREPARE STAFF ORDER DETAILS

    public function checkOder()
    {

    }
}
