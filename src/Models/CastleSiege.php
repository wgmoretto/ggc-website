<?php
declare(strict_types=1);

namespace WebEngine\Models;

use WebEngine\Database\Database;

class CastleSiege
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getOwner(): ?array
    {
        $sql = "SELECT * FROM MuCastle_DATA WHERE MAP_SVR_GROUP = 0";
        $castle = $this->db->fetch($sql);

        if ($castle && $castle['OWNER_GUILD']) {
            $sql = "SELECT * FROM Guild WHERE G_Name = ?";
            $guild = $this->db->fetch($sql, [$castle['OWNER_GUILD']]);
            $castle['GuildInfo'] = $guild;
        }

        return $castle;
    }

    public function getRegisteredGuilds(): array
    {
        $sql = "SELECT csg.*, g.G_Master, g.G_Score
                FROM MuCastle_SIEGE_GUILDLIST csg
                INNER JOIN Guild g ON csg.REG_SIEGE_GUILD = g.G_Name
                WHERE csg.MAP_SVR_GROUP = 0
                ORDER BY csg.REG_MARKS DESC";

        return $this->db->fetchAll($sql);
    }

    public function isRegistered(string $guildName): bool
    {
        $sql = "SELECT * FROM MuCastle_SIEGE_GUILDLIST
                WHERE REG_SIEGE_GUILD = ? AND MAP_SVR_GROUP = 0";

        return $this->db->fetch($sql, [$guildName]) !== null;
    }

    public function registerGuild(string $guildName, int $marks = 0): bool
    {
        $sql = "INSERT INTO MuCastle_SIEGE_GUILDLIST (MAP_SVR_GROUP, REG_SIEGE_GUILD, REG_MARKS, IS_GIVEUP, SEQ_NUM)
                VALUES (0, ?, ?, 0, (SELECT ISNULL(MAX(SEQ_NUM), 0) + 1 FROM MuCastle_SIEGE_GUILDLIST WHERE MAP_SVR_GROUP = 0))";

        return $this->db->execute($sql, [$guildName, $marks]);
    }

    public function unregisterGuild(string $guildName): bool
    {
        $sql = "DELETE FROM MuCastle_SIEGE_GUILDLIST WHERE REG_SIEGE_GUILD = ? AND MAP_SVR_GROUP = 0";
        return $this->db->execute($sql, [$guildName]);
    }

    public function getSchedule(): ?array
    {
        $sql = "SELECT SIEGE_START_DATE, SIEGE_END_DATE, SIEGE_STIME, SIEGE_ETIME
                FROM MuCastle_DATA WHERE MAP_SVR_GROUP = 0";

        return $this->db->fetch($sql);
    }

    public function getNpcStatus(): array
    {
        $sql = "SELECT * FROM MuCastle_NPC WHERE MAP_SVR_GROUP = 0";
        return $this->db->fetchAll($sql);
    }

    public function updateOwner(string $guildName): bool
    {
        $sql = "UPDATE MuCastle_DATA SET OWNER_GUILD = ? WHERE MAP_SVR_GROUP = 0";
        return $this->db->execute($sql, [$guildName]);
    }
}
