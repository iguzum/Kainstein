<?php
namespace App\Controllers;

use App\Models\Authentication;

// :: START ACCOUNT CONTROLLER
class Authentication_Controller
{
    private Authentication $authentication;

    public function __construct()
    {
        $this->authentication = new Authentication();
    }

    public function login(string $email, string $password): ?array
    {
        return $this->authentication->login($email, $password);
    }

    public function getAllStaff(): array
    {
        return $this->authentication->getAllStaff();
    }

    // :: START VALIDATE STAFF NAME
    public function createStaff(string $accountName, string $email, string $password): bool
    {
        return trim($accountName) !== ''
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && strlen($password) >= 8
            && $this->authentication->createStaff($accountName, $email, $password);
    }

    public function updateStaff(int $accountId, string $accountName, string $email, string $password = ''): bool
    {
        return $accountId > 0
            && trim($accountName) !== ''
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && ($password === '' || strlen($password) >= 8)
            && $this->authentication->updateStaff($accountId, $accountName, $email, $password);
    }
    // :: END VALIDATE STAFF NAME

    public function deleteStaff(int $accountId): bool
    {
        return $accountId > 0 && $this->authentication->deleteStaff($accountId);
    }
}
// :: END ACCOUNT CONTROLLER
