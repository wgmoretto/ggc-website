<?php
declare(strict_types=1);

namespace WebEngine\Services;

use WebEngine\Database\Database;
use WebEngine\Models\Account;

class AuthService
{
    private Database $db;
    private Account $accountModel;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->accountModel = new Account($db);
    }

    public function attempt(string $username, string $password): bool
    {
        // Check if account is banned
        if ($this->accountModel->isBanned($username)) {
            return false;
        }

        // Verify password
        if (!$this->accountModel->verifyPassword($username, $password)) {
            return false;
        }

        // Get account data
        $account = $this->accountModel->find($username);

        if (!$account) {
            return false;
        }

        // Start session
        $this->login($account);

        return true;
    }

    public function login(array $account): void
    {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $account['memb___id'];
        $_SESSION['email'] = $account['mail_addr'];
        $_SESSION['credits'] = $account['credits'] ?? 0;
        $_SESSION['is_admin'] = $this->isAdmin($account['memb___id']);
        $_SESSION['login_time'] = time();
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public function check(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }

        return [
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'credits' => $_SESSION['credits'] ?? 0,
            'is_admin' => $_SESSION['is_admin'] ?? false
        ];
    }

    public function isAdmin(string $username): bool
    {
        $sql = "SELECT admin_level FROM MEMB_INFO WHERE memb___id = ?";
        $result = $this->db->fetch($sql, [$username]);

        return $result && (int)($result['admin_level'] ?? 0) > 0;
    }

    public function register(array $data): bool
    {
        // Check if username exists
        if ($this->accountModel->find($data['username'])) {
            return false;
        }

        // Check if email exists
        if ($this->accountModel->findByEmail($data['email'])) {
            return false;
        }

        // Hash password
        $data['password'] = $this->accountModel->hashPassword(
            $data['password'],
            $_ENV['PASSWORD_ENCRYPTION'] ?? 'sha256'
        );

        // Create account
        return $this->accountModel->create($data);
    }

    public function resetPassword(string $username, string $newPassword): bool
    {
        return $this->accountModel->changePassword($username, $newPassword);
    }

    public function createPasswordResetToken(string $username): string
    {
        $token = bin2hex(random_bytes(32));

        $sql = "INSERT INTO GGC_PASSCHANGE_REQUEST (username, token, created_at, expires_at)
                VALUES (?, ?, GETDATE(), DATEADD(hour, 24, GETDATE()))";

        $this->db->execute($sql, [$username, $token]);

        return $token;
    }

    public function validatePasswordResetToken(string $token): ?string
    {
        $sql = "SELECT username FROM GGC_PASSCHANGE_REQUEST
                WHERE token = ? AND expires_at > GETDATE() AND used = 0";

        $result = $this->db->fetch($sql, [$token]);

        return $result ? $result['username'] : null;
    }

    public function markTokenAsUsed(string $token): bool
    {
        $sql = "UPDATE GGC_PASSCHANGE_REQUEST SET used = 1 WHERE token = ?";
        return $this->db->execute($sql, [$token]);
    }
}
