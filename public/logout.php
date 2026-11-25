<?php
// :: START LOGOUT
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Config\Connection;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// :: START CLEAR STAFF DRAFT CART
if (
    ($_SESSION['account']['role'] ?? '') === 'staff'
    && !empty($_SESSION['staff_cart_id'])
) {
    $pdo = (new Connection())->connect();
    $stmt = $pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id');
    $stmt->execute(['cart_id' => (int) $_SESSION['staff_cart_id']]);
}
// :: END CLEAR STAFF DRAFT CART

$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
// :: END LOGOUT
