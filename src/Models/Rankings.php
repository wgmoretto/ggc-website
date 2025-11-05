<?php
declare(strict_types=1);

namespace WebEngine\Models;

use WebEngine\Database\Database;

class Rankings
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getTopLevel(int $limit = 100): array
    {
        $sql = "SELECT c.Name, c.Class, c.cLevel, c.MapNumber,
                c.Resets, c.AccountID, c.CtlCode
                FROM Character c
                WHERE c.CtlCode = 0
                ORDER BY c.Resets DESC, c.cLevel DESC, c.Experience DESC
                OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";

        $characters = $this->db->fetchAll($sql, [$limit]);

        foreach ($characters as &$char) {
            $char['ClassName'] = $this->getClassName((int)$char['Class']);
        }

        return $characters;
    }

    public function getTopResets(int $limit = 100): array
    {
        $sql = "SELECT c.Name, c.Class, c.cLevel, c.Resets, c.AccountID
                FROM Character c
                WHERE c.CtlCode = 0
                ORDER BY c.Resets DESC, c.cLevel DESC
                OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";

        $characters = $this->db->fetchAll($sql, [$limit]);

        foreach ($characters as &$char) {
            $char['ClassName'] = $this->getClassName((int)$char['Class']);
        }

        return $characters;
    }

    public function getTopGuilds(int $limit = 50): array
    {
        $sql = "SELECT g.G_Name, g.G_Master, g.G_Score, g.G_Notice,
                COUNT(gm.Name) as MemberCount
                FROM Guild g
                LEFT JOIN GuildMember gm ON g.G_Name = gm.G_Name
                GROUP BY g.G_Name, g.G_Master, g.G_Score, g.G_Notice
                ORDER BY g.G_Score DESC
                OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";

        return $this->db->fetchAll($sql, [$limit]);
    }

    public function getTopKillers(int $limit = 100): array
    {
        $sql = "SELECT c.Name, c.Class, c.cLevel, c.PkCount, c.PkLevel, c.AccountID
                FROM Character c
                WHERE c.CtlCode = 0 AND c.PkCount > 0
                ORDER BY c.PkCount DESC
                OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";

        $characters = $this->db->fetchAll($sql, [$limit]);

        foreach ($characters as &$char) {
            $char['ClassName'] = $this->getClassName((int)$char['Class']);
        }

        return $characters;
    }

    public function getOnlinePlayers(int $limit = 100): array
    {
        $sql = "SELECT c.Name, c.Class, c.cLevel, c.MapNumber, c.Resets, c.AccountID
                FROM Character c
                INNER JOIN MEMB_STAT ms ON c.AccountID = ms.memb___id
                WHERE ms.ConnectStat = 1 AND c.CtlCode = 0
                ORDER BY c.cLevel DESC
                OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";

        $characters = $this->db->fetchAll($sql, [$limit]);

        foreach ($characters as &$char) {
            $char['ClassName'] = $this->getClassName((int)$char['Class']);
        }

        return $characters;
    }

    public function getTopVoters(int $limit = 100): array
    {
        $sql = "SELECT username, COUNT(*) as vote_count
                FROM GGC_VOTES
                WHERE voted_at >= DATEADD(month, -1, GETDATE())
                GROUP BY username
                ORDER BY vote_count DESC
                OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";

        return $this->db->fetchAll($sql, [$limit]);
    }

    public function getTopMasterLevel(int $limit = 100): array
    {
        $sql = "SELECT c.Name, c.Class, c.cLevel, c.MasterLevel, c.Resets, c.AccountID
                FROM Character c
                WHERE c.CtlCode = 0 AND c.MasterLevel > 0
                ORDER BY c.MasterLevel DESC, c.MasterExperience DESC
                OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";

        $characters = $this->db->fetchAll($sql, [$limit]);

        foreach ($characters as &$char) {
            $char['ClassName'] = $this->getClassName((int)$char['Class']);
        }

        return $characters;
    }

    public function getTopGens(int $limit = 100, int $family = null): array
    {
        $sql = "SELECT c.Name, c.Class, c.cLevel, c.Resets, c.AccountID,
                ISNULL(c.GensContribution, 0) as Contribution,
                ISNULL(c.GensFamily, 0) as Family
                FROM Character c
                WHERE c.CtlCode = 0";

        $params = [];

        if ($family !== null) {
            $sql .= " AND c.GensFamily = ?";
            $params[] = $family;
        }

        $sql .= " ORDER BY Contribution DESC OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";
        $params[] = $limit;

        $characters = $this->db->fetchAll($sql, $params);

        foreach ($characters as &$char) {
            $char['ClassName'] = $this->getClassName((int)$char['Class']);
        }

        return $characters;
    }

    public function getTopGrandResets(int $limit = 100): array
    {
        $sql = "SELECT c.Name, c.Class, c.cLevel, c.Resets,
                ISNULL(c.GrandResets, 0) as GrandResets, c.AccountID
                FROM Character c
                WHERE c.CtlCode = 0
                ORDER BY GrandResets DESC, c.Resets DESC, c.cLevel DESC
                OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";

        $characters = $this->db->fetchAll($sql, [$limit]);

        foreach ($characters as &$char) {
            $char['ClassName'] = $this->getClassName((int)$char['Class']);
        }

        return $characters;
    }

    private function getClassName(int $classCode): string
    {
        $classNames = [
            0 => 'Dark Wizard', 1 => 'Soul Master', 2 => 'Grand Master',
            16 => 'Dark Knight', 17 => 'Blade Knight', 18 => 'Blade Master',
            32 => 'Fairy Elf', 33 => 'Muse Elf', 34 => 'High Elf',
            48 => 'Magic Gladiator', 50 => 'Duel Master',
            64 => 'Dark Lord', 66 => 'Lord Emperor',
            80 => 'Summoner', 81 => 'Bloody Summoner', 82 => 'Dimension Master',
            96 => 'Rage Fighter', 98 => 'Fist Master'
        ];

        return $classNames[$classCode] ?? 'Unknown';
    }
}
