<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

// :: START LOGIN REQUEST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

use App\Controllers\Authentication_Controller;

try {
    $controller = new Authentication_Controller();
    $account = $controller->login((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));

    if ($account === null) {
        header('Location: index.php?login=failed');
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['account'] = $account;
    $destination = $account['role'] === 'admin' ? 'admin/index.php' : 'staff/index.php';
    header('Location: ' . $destination);
    exit;
} catch (Throwable $error) {
    header('Location: index.php?login=unavailable');
    exit;
}
// :: END LOGIN REQUEST
