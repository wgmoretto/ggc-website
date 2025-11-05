<?php
declare(strict_types=1);

namespace WebEngine\Models;

use WebEngine\Database\Database;

class Account
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function find(string $username): ?array
    {
        $sql = "SELECT * FROM MEMB_INFO WHERE memb___id = ?";
        return $this->db->fetch($sql, [$username]);
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM MEMB_INFO WHERE mail_addr = ?";
        return $this->db->fetch($sql, [$email]);
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO MEMB_INFO (memb___id, memb__pwd, memb_name, sno__numb, mail_addr, phon_numb, country)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->db->execute($sql, [
            $data['username'],
            $data['password'],
            $data['name'] ?? $data['username'],
            $data['social_number'] ?? '1234567890',
            $data['email'],
            $data['phone'] ?? '',
            $data['country'] ?? 'BR'
        ]);
    }

    public function update(string $username, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }

        $params[] = $username;
        $sql = "UPDATE MEMB_INFO SET " . implode(', ', $fields) . " WHERE memb___id = ?";

        return $this->db->execute($sql, $params);
    }

    public function verifyPassword(string $username, string $password): bool
    {
        $account = $this->find($username);

        if (!$account) {
            return false;
        }

        $encryption = $_ENV['PASSWORD_ENCRYPTION'] ?? 'sha256';
        $hashedPassword = $this->hashPassword($password, $encryption);

        return $hashedPassword === $account['memb__pwd'];
    }

    public function hashPassword(string $password, string $method = 'sha256'): string
    {
        switch ($method) {
            case 'md5':
                return md5($password);
            case 'sha256':
                return hash('sha256', $password);
            case 'wzmd5':
                // WebZen MD5 implementation
                $hash = md5($password);
                return strtoupper(substr($hash, 0, 10));
            default:
                return $password;
        }
    }

    public function getCharacters(string $username): array
    {
        $sql = "SELECT * FROM Character WHERE AccountID = ? ORDER BY cLevel DESC";
        return $this->db->fetchAll($sql, [$username]);
    }

    public function ban(string $username, string $reason, int $duration = 0): bool
    {
        $sql = "INSERT INTO GGC_BANS (username, reason, banned_by, banned_at, expires_at)
                VALUES (?, ?, ?, GETDATE(), ?)";

        $expiresAt = $duration > 0 ? date('Y-m-d H:i:s', time() + $duration) : null;

        return $this->db->execute($sql, [
            $username,
            $reason,
            $_SESSION['username'] ?? 'System',
            $expiresAt
        ]);
    }

    public function unban(string $username): bool
    {
        $sql = "DELETE FROM GGC_BANS WHERE username = ?";
        return $this->db->execute($sql, [$username]);
    }

    public function isBanned(string $username): bool
    {
        $sql = "SELECT * FROM GGC_BANS
                WHERE username = ?
                AND (expires_at IS NULL OR expires_at > GETDATE())";

        $ban = $this->db->fetch($sql, [$username]);
        return $ban !== null;
    }

    public function getCredits(string $username): int
    {
        $account = $this->find($username);
        return (int) ($account['credits'] ?? 0);
    }

    public function addCredits(string $username, int $amount, string $reason = ''): bool
    {
        $this->db->beginTransaction();

        try {
            // Update credits
            $sql = "UPDATE MEMB_INFO SET credits = ISNULL(credits, 0) + ? WHERE memb___id = ?";
            $this->db->execute($sql, [$amount, $username]);

            // Log transaction
            $sql = "INSERT INTO GGC_CREDITS_LOGS (username, amount, reason, created_at)
                    VALUES (?, ?, ?, GETDATE())";
            $this->db->execute($sql, [$username, $amount, $reason]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function deductCredits(string $username, int $amount, string $reason = ''): bool
    {
        return $this->addCredits($username, -$amount, $reason);
    }

    public function changePassword(string $username, string $newPassword): bool
    {
        $encryption = $_ENV['PASSWORD_ENCRYPTION'] ?? 'sha256';
        $hashedPassword = $this->hashPassword($newPassword, $encryption);

        $sql = "UPDATE MEMB_INFO SET memb__pwd = ? WHERE memb___id = ?";
        return $this->db->execute($sql, [$hashedPassword, $username]);
    }

    public function changeEmail(string $username, string $newEmail): bool
    {
        $sql = "UPDATE MEMB_INFO SET mail_addr = ?, mail_chek = 0 WHERE memb___id = ?";
        return $this->db->execute($sql, [$newEmail, $username]);
    }

    public function verifyEmail(string $username): bool
    {
        $sql = "UPDATE MEMB_INFO SET mail_chek = 1 WHERE memb___id = ?";
        return $this->db->execute($sql, [$username]);
    }

    public function getAllAccounts(int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT TOP {$limit} * FROM MEMB_INFO ORDER BY memb___id OFFSET {$offset} ROWS";
        return $this->db->fetchAll($sql);
    }

    public function getRecentRegistrations(int $days = 7): array
    {
        $sql = "SELECT * FROM MEMB_INFO
                WHERE memb_join >= DATEADD(day, -{$days}, GETDATE())
                ORDER BY memb_join DESC";
        return $this->db->fetchAll($sql);
    }
}
