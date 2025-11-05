<?php
declare(strict_types=1);

namespace WebEngine\Database;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private ?PDO $connection = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connect(): PDO
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        try {
            $driver = $this->config['driver'];
            $host = $this->config['host'];
            $port = $this->config['port'];
            $database = $this->config['database'];
            $username = $this->config['username'];
            $password = $this->config['password'];

            $dsn = "{$driver}:Server={$host},{$port};Database={$database}";

            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return $this->connection;
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $connection = $this->connect();
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $statement = $this->query($sql, $params);
        $result = $statement->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $statement = $this->query($sql, $params);
        return $statement->fetchAll();
    }

    public function execute(string $sql, array $params = []): bool
    {
        $statement = $this->query($sql, $params);
        return $statement->rowCount() > 0;
    }

    public function insert(string $sql, array $params = []): int
    {
        $this->query($sql, $params);
        return (int) $this->connect()->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->connect()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connect()->commit();
    }

    public function rollBack(): bool
    {
        return $this->connect()->rollBack();
    }

    public function lastInsertId(): string
    {
        return $this->connect()->lastInsertId();
    }
}
