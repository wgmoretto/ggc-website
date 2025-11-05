<?php
declare(strict_types=1);

namespace WebEngine\Models;

use WebEngine\Database\Database;

class Vote
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getSites(): array
    {
        $sql = "SELECT * FROM GGC_VOTE_SITES WHERE active = 1 ORDER BY sort_order ASC";
        return $this->db->fetchAll($sql);
    }

    public function canVote(string $username, int $siteId): bool
    {
        $sql = "SELECT * FROM GGC_VOTES
                WHERE username = ? AND site_id = ?
                AND voted_at >= DATEADD(hour, -12, GETDATE())";

        $vote = $this->db->fetch($sql, [$username, $siteId]);
        return $vote === null;
    }

    public function recordVote(string $username, int $siteId, string $ipAddress): bool
    {
        $this->db->beginTransaction();

        try {
            // Record vote
            $sql = "INSERT INTO GGC_VOTES (username, site_id, ip_address, voted_at)
                    VALUES (?, ?, ?, GETDATE())";
            $this->db->execute($sql, [$username, $siteId, $ipAddress]);

            // Get reward from site config
            $sql = "SELECT credits_reward FROM GGC_VOTE_SITES WHERE id = ?";
            $site = $this->db->fetch($sql, [$siteId]);

            if ($site && $site['credits_reward'] > 0) {
                // Add credits
                $sql = "UPDATE MEMB_INFO SET credits = ISNULL(credits, 0) + ? WHERE memb___id = ?";
                $this->db->execute($sql, [$site['credits_reward'], $username]);

                // Log credits
                $sql = "INSERT INTO GGC_CREDITS_LOGS (username, amount, reason, created_at)
                        VALUES (?, ?, 'Vote reward', GETDATE())";
                $this->db->execute($sql, [$username, $site['credits_reward']]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getUserVotes(string $username, int $days = 30): array
    {
        $sql = "SELECT v.*, vs.name as site_name
                FROM GGC_VOTES v
                INNER JOIN GGC_VOTE_SITES vs ON v.site_id = vs.id
                WHERE v.username = ?
                AND v.voted_at >= DATEADD(day, -{$days}, GETDATE())
                ORDER BY v.voted_at DESC";

        return $this->db->fetchAll($sql, [$username]);
    }

    public function getTopVoters(int $limit = 100): array
    {
        $sql = "SELECT TOP ($limit) username, COUNT(*) as vote_count
                FROM GGC_VOTES
                WHERE voted_at >= DATEADD(month, -1, GETDATE())
                GROUP BY username
                ORDER BY vote_count DESC";

        return $this->db->fetchAll($sql);
    }
}
