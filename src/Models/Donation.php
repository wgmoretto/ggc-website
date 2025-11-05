<?php
declare(strict_types=1);

namespace WebEngine\Models;

use WebEngine\Database\Database;

class Donation
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getPackages(): array
    {
        $sql = "SELECT * FROM GGC_CREDITS_CONFIG WHERE active = 1 ORDER BY amount ASC";
        return $this->db->fetchAll($sql);
    }

    public function getPackage(int $id): ?array
    {
        $sql = "SELECT * FROM GGC_CREDITS_CONFIG WHERE id = ? AND active = 1";
        return $this->db->fetch($sql, [$id]);
    }

    public function recordTransaction(array $data): int
    {
        $sql = "INSERT INTO GGC_PAYPAL_TRANSACTIONS
                (username, transaction_id, payment_status, amount, currency, credits, created_at)
                VALUES (?, ?, ?, ?, ?, ?, GETDATE())";

        return $this->db->insert($sql, [
            $data['username'],
            $data['transaction_id'],
            $data['payment_status'],
            $data['amount'],
            $data['currency'] ?? 'USD',
            $data['credits']
        ]);
    }

    public function processPayment(string $username, int $credits): bool
    {
        $this->db->beginTransaction();

        try {
            // Add credits to account
            $sql = "UPDATE MEMB_INFO SET credits = ISNULL(credits, 0) + ? WHERE memb___id = ?";
            $this->db->execute($sql, [$credits, $username]);

            // Log transaction
            $sql = "INSERT INTO GGC_CREDITS_LOGS (username, amount, reason, created_at)
                    VALUES (?, ?, 'Donation', GETDATE())";
            $this->db->execute($sql, [$username, $credits]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getTransactionHistory(string $username, int $limit = 50): array
    {
        $sql = "SELECT TOP ($limit) * FROM GGC_PAYPAL_TRANSACTIONS
                WHERE username = ?
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, [$username]);
    }

    public function getAllTransactions(int $limit = 100): array
    {
        $sql = "SELECT TOP ($limit) * FROM GGC_PAYPAL_TRANSACTIONS
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql);
    }

    public function buyItem(string $username, int $cost, string $itemName): bool
    {
        $this->db->beginTransaction();

        try {
            // Check credits
            $sql = "SELECT credits FROM MEMB_INFO WHERE memb___id = ?";
            $account = $this->db->fetch($sql, [$username]);

            if (!$account || $account['credits'] < $cost) {
                throw new \Exception('Insufficient credits');
            }

            // Deduct credits
            $sql = "UPDATE MEMB_INFO SET credits = credits - ? WHERE memb___id = ?";
            $this->db->execute($sql, [$cost, $username]);

            // Log transaction
            $sql = "INSERT INTO GGC_CREDITS_LOGS (username, amount, reason, created_at)
                    VALUES (?, ?, ?, GETDATE())";
            $this->db->execute($sql, [$username, -$cost, "Purchased: {$itemName}"]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
