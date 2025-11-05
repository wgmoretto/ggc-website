<?php
declare(strict_types=1);

namespace WebEngine\Models;

use WebEngine\Database\Database;

class Guild
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function find(string $name): ?array
    {
        $sql = "SELECT * FROM Guild WHERE G_Name = ?";
        $guild = $this->db->fetch($sql, [$name]);

        if ($guild) {
            $guild['Members'] = $this->getMembers($name);
            $guild['MemberCount'] = count($guild['Members']);
        }

        return $guild;
    }

    public function getMembers(string $guildName): array
    {
        $sql = "SELECT gm.Name, gm.G_Status, c.Class, c.cLevel, c.Resets
                FROM GuildMember gm
                INNER JOIN Character c ON gm.Name = c.Name
                WHERE gm.G_Name = ?
                ORDER BY gm.G_Status ASC, c.cLevel DESC";

        $members = $this->db->fetchAll($sql, [$guildName]);

        foreach ($members as &$member) {
            $member['ClassName'] = $this->getClassName((int)$member['Class']);
            $member['RankName'] = $this->getRankName((int)$member['G_Status']);
        }

        return $members;
    }

    public function getRankName(int $status): string
    {
        $ranks = [
            0x00 => 'Guild Master',
            0x20 => 'Assistant',
            0x40 => 'Battle Master',
            0x80 => 'Member'
        ];

        return $ranks[$status] ?? 'Member';
    }

    private function getClassName(int $classCode): string
    {
        $classNames = [
            0 => 'Dark Wizard', 16 => 'Dark Knight', 32 => 'Fairy Elf',
            48 => 'Magic Gladiator', 64 => 'Dark Lord', 80 => 'Summoner', 96 => 'Rage Fighter'
        ];

        return $classNames[$classCode] ?? 'Unknown';
    }

    public function getAll(): array
    {
        $sql = "SELECT g.*, COUNT(gm.Name) as MemberCount
                FROM Guild g
                LEFT JOIN GuildMember gm ON g.G_Name = gm.G_Name
                GROUP BY g.G_Name, g.G_Master, g.G_Mark, g.G_Score, g.G_Notice, g.G_Type, g.G_Rival, g.G_Union
                ORDER BY g.G_Score DESC";

        return $this->db->fetchAll($sql);
    }

    public function getAlliance(string $guildName): array
    {
        $guild = $this->find($guildName);

        if (!$guild || !$guild['G_Union']) {
            return [];
        }

        $sql = "SELECT * FROM Guild WHERE G_Union = ? AND G_Name != ?";
        return $this->db->fetchAll($sql, [$guild['G_Union'], $guildName]);
    }
}
