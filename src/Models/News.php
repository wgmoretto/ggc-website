<?php
declare(strict_types=1);

namespace WebEngine\Models;

use WebEngine\Database\Database;

class News
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAll(int $limit = 10, int $offset = 0): array
    {
        // Para SQL Server com paginação, usamos ROW_NUMBER() com TOP
        if ($offset > 0) {
            $sql = "WITH PaginatedNews AS (
                    SELECT *, ROW_NUMBER() OVER (ORDER BY created_at DESC) as RowNum
                    FROM GGC_NEWS
                    WHERE published = 1
                )
                SELECT * FROM PaginatedNews
                WHERE RowNum > $offset AND RowNum <= ($offset + $limit)";
            return $this->db->fetchAll($sql);
        } else {
            $sql = "SELECT TOP ($limit) * FROM GGC_NEWS
                    WHERE published = 1
                    ORDER BY created_at DESC";
            return $this->db->fetchAll($sql);
        }
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM GGC_NEWS WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO GGC_NEWS (title, content, author, category, published, created_at)
                VALUES (?, ?, ?, ?, ?, GETDATE())";

        return $this->db->insert($sql, [
            $data['title'],
            $data['content'],
            $data['author'],
            $data['category'] ?? 'general',
            $data['published'] ?? 1
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }

        $fields[] = "updated_at = GETDATE()";
        $params[] = $id;

        $sql = "UPDATE GGC_NEWS SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $params);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM GGC_NEWS WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    public function getTranslations(int $newsId, string $language = null): array
    {
        if ($language) {
            $sql = "SELECT * FROM GGC_NEWS_TRANSLATIONS
                    WHERE news_id = ? AND language = ?";
            $result = $this->db->fetch($sql, [$newsId, $language]);
            return $result ? [$result] : [];
        }

        $sql = "SELECT * FROM GGC_NEWS_TRANSLATIONS WHERE news_id = ?";
        return $this->db->fetchAll($sql, [$newsId]);
    }

    public function addTranslation(int $newsId, string $language, string $title, string $content): bool
    {
        $sql = "INSERT INTO GGC_NEWS_TRANSLATIONS (news_id, language, title, content)
                VALUES (?, ?, ?, ?)";

        return $this->db->execute($sql, [$newsId, $language, $title, $content]);
    }

    public function getRecent(int $limit = 5): array
    {
        $sql = "SELECT TOP ($limit) * FROM GGC_NEWS
                WHERE published = 1
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql);
    }

    public function search(string $query): array
    {
        $sql = "SELECT * FROM GGC_NEWS
                WHERE published = 1
                AND (title LIKE ? OR content LIKE ?)
                ORDER BY created_at DESC";

        $searchTerm = "%{$query}%";
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm]);
    }
}
