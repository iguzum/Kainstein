<?php
namespace App\Models;

use App\Config\Connection;
use PDO;

// :: START ACCOUNT MODEL
class Authentication
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Connection())->connect();
    }

    public function login(string $email, string $password): ?array
    {
        // :: START LOAD ACCOUNT NAME ON LOGIN
        $stmt = $this->pdo->prepare('SELECT account_id, account_name, email, password, role FROM account WHERE email = :email LIMIT 1');
        // :: END LOAD ACCOUNT NAME ON LOGIN
        $stmt->execute(['email' => strtolower(trim($email))]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$account || !password_verify($password, $account['password'])) {
            return null;
        }

        unset($account['password']);
        return $account;
    }

    public function getAllStaff(): array
    {
        // :: START INCLUDE STAFF NAME IN LIST
        $stmt = $this->pdo->query("SELECT account_id, account_name, email, created_at FROM account WHERE role = 'staff' ORDER BY account_id DESC");
        // :: END INCLUDE STAFF NAME IN LIST
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // :: START SAVE STAFF NAME
    public function createStaff(string $accountName, string $email, string $password): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO account (account_name, email, password, role) VALUES (:account_name, :email, :password, 'staff')");
        return $stmt->execute([
            'account_name' => trim($accountName),
            'email' => strtolower(trim($email)),
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public function updateStaff(int $accountId, string $accountName, string $email, ?string $password = null): bool
    {
        if ($password !== null && $password !== '') {
            $stmt = $this->pdo->prepare("UPDATE account SET account_name = :account_name, email = :email, password = :password WHERE account_id = :id AND role = 'staff'");
            return $stmt->execute([
                'account_name' => trim($accountName),
                'email' => strtolower(trim($email)),
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'id' => $accountId,
            ]);
        }

        $stmt = $this->pdo->prepare("UPDATE account SET account_name = :account_name, email = :email WHERE account_id = :id AND role = 'staff'");
        return $stmt->execute([
            'account_name' => trim($accountName),
            'email' => strtolower(trim($email)),
            'id' => $accountId,
        ]);
    }
    // :: END SAVE STAFF NAME

    public function deleteStaff(int $accountId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM account WHERE account_id = :id AND role = 'staff'");
        return $stmt->execute(['id' => $accountId]);
    }
}
// :: END ACCOUNT MODEL
