<?php
declare(strict_types=1);

namespace WebEngine\Models;

use WebEngine\Database\Database;

class Character
{
    private Database $db;

    private const CLASS_NAMES = [
        0 => 'Dark Wizard', 1 => 'Soul Master', 2 => 'Grand Master', 3 => 'Soul Master',
        16 => 'Dark Knight', 17 => 'Blade Knight', 18 => 'Blade Master', 19 => 'Blade Master',
        32 => 'Fairy Elf', 33 => 'Muse Elf', 34 => 'High Elf', 35 => 'Muse Elf',
        48 => 'Magic Gladiator', 50 => 'Duel Master',
        64 => 'Dark Lord', 66 => 'Lord Emperor',
        80 => 'Summoner', 81 => 'Bloody Summoner', 82 => 'Dimension Master',
        96 => 'Rage Fighter', 98 => 'Fist Master',
        112 => 'Grow Lancer', 114 => 'Mirage Lancer',
        128 => 'Rune Wizard', 130 => 'Rune Spell Master',
        144 => 'Slayer', 146 => 'Royal Slayer',
        160 => 'Gun Crusher', 162 => 'Gun Breaker'
    ];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function find(string $name): ?array
    {
        $sql = "SELECT * FROM Character WHERE Name = ?";
        $char = $this->db->fetch($sql, [$name]);

        if ($char) {
            $char['ClassName'] = $this->getClassName((int)$char['Class']);
        }

        return $char;
    }

    public function findByAccount(string $accountId): array
    {
        $sql = "SELECT * FROM Character WHERE AccountID = ? ORDER BY cLevel DESC";
        $characters = $this->db->fetchAll($sql, [$accountId]);

        foreach ($characters as &$char) {
            $char['ClassName'] = $this->getClassName((int)$char['Class']);
        }

        return $characters;
    }

    public function getClassName(int $classCode): string
    {
        return self::CLASS_NAMES[$classCode] ?? 'Unknown';
    }

    public function addStats(string $name, array $stats): bool
    {
        $fields = [];
        $params = [];

        if (isset($stats['Strength']) && $stats['Strength'] > 0) {
            $fields[] = "Strength = Strength + ?";
            $params[] = $stats['Strength'];
        }
        if (isset($stats['Dexterity']) && $stats['Dexterity'] > 0) {
            $fields[] = "Dexterity = Dexterity + ?";
            $params[] = $stats['Dexterity'];
        }
        if (isset($stats['Vitality']) && $stats['Vitality'] > 0) {
            $fields[] = "Vitality = Vitality + ?";
            $params[] = $stats['Vitality'];
        }
        if (isset($stats['Energy']) && $stats['Energy'] > 0) {
            $fields[] = "Energy = Energy + ?";
            $params[] = $stats['Energy'];
        }

        if (empty($fields)) {
            return false;
        }

        // Deduct level up points
        $totalPoints = array_sum($stats);
        $fields[] = "LevelUpPoint = LevelUpPoint - ?";
        $params[] = $totalPoints;

        $params[] = $name;

        $sql = "UPDATE Character SET " . implode(', ', $fields) . " WHERE Name = ?";
        return $this->db->execute($sql, $params);
    }

    public function reset(string $name): bool
    {
        $sql = "UPDATE Character SET
                cLevel = 1,
                Experience = 0,
                LevelUpPoint = 0,
                Resets = ISNULL(Resets, 0) + 1,
                Money = Money + ?
                WHERE Name = ?";

        $resetReward = (int)($_ENV['RESET_REWARD_ZEN'] ?? 10000000);
        return $this->db->execute($sql, [$resetReward, $name]);
    }

    public function resetStats(string $name): bool
    {
        $char = $this->find($name);

        if (!$char) {
            return false;
        }

        // Calculate total stat points based on level
        $level = (int)$char['cLevel'];
        $basePoints = $level * 5;

        $sql = "UPDATE Character SET
                Strength = ?,
                Dexterity = ?,
                Vitality = ?,
                Energy = ?,
                LevelUpPoint = ?
                WHERE Name = ?";

        return $this->db->execute($sql, [20, 20, 20, 20, $basePoints, $name]);
    }

    public function clearPK(string $name): bool
    {
        $sql = "UPDATE Character SET PkCount = 0, PkLevel = 3, PkTime = 0 WHERE Name = ?";
        return $this->db->execute($sql, [$name]);
    }

    public function clearSkillTree(string $name): bool
    {
        $this->db->beginTransaction();

        try {
            // Clear master skill tree
            $sql = "DELETE FROM MasterSkillTree WHERE Name = ?";
            $this->db->execute($sql, [$name]);

            // Reset master points
            $sql = "UPDATE Character SET MasterLevel = 0, MasterExperience = 0, MasterPoint = 0 WHERE Name = ?";
            $this->db->execute($sql, [$name]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function unstick(string $name): bool
    {
        $sql = "UPDATE Character SET MapNumber = 0, MapPosX = 125, MapPosY = 125 WHERE Name = ?";
        return $this->db->execute($sql, [$name]);
    }

    public function isOnline(string $name): bool
    {
        $sql = "SELECT ConnectStat FROM MEMB_STAT ms
                INNER JOIN Character c ON c.AccountID = ms.memb___id
                WHERE c.Name = ?";

        $result = $this->db->fetch($sql, [$name]);
        return $result && (int)$result['ConnectStat'] === 1;
    }

    public function getGuild(string $characterName): ?array
    {
        $sql = "SELECT g.* FROM Guild g
                INNER JOIN GuildMember gm ON g.G_Name = gm.G_Name
                WHERE gm.Name = ?";

        return $this->db->fetch($sql, [$characterName]);
    }

    public function update(string $name, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }

        $params[] = $name;
        $sql = "UPDATE Character SET " . implode(', ', $fields) . " WHERE Name = ?";

        return $this->db->execute($sql, $params);
    }

    public function getInventory(string $name): ?string
    {
        $sql = "SELECT Inventory FROM Character WHERE Name = ?";
        $result = $this->db->fetch($sql, [$name]);
        return $result['Inventory'] ?? null;
    }

    public function getQuest(string $name): ?string
    {
        $sql = "SELECT Quest FROM Character WHERE Name = ?";
        $result = $this->db->fetch($sql, [$name]);
        return $result['Quest'] ?? null;
    }
}
