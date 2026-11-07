<?php
namespace Core;

use PDO;
use PDOStatement;

/**
 * Abstract Class Model
 * Base class for all data models.
 * Implements Abstraction and Encapsulation for database operations.
 */
abstract class Model
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Abstract method enforcing child models to declare table name
     */
    abstract public function getTable(): string;

    /**
     * Find a record by primary key ID
     */
    public function findById(int $id): ?array
    {
        $table = $this->getTable();
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find single record by column
     */
    public function findBy(string $column, $value): ?array
    {
        $table = $this->getTable();
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE {$column} = :val LIMIT 1");
        $stmt->execute([':val' => $value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all records with optional ordering and pagination
     */
    public function findAll(string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $table = $this->getTable();
        $sql = "SELECT * FROM {$table}";
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Filter records with dynamic conditions using prepared statements
     */
    public function where(array $conditions, string $orderBy = '', int $limit = 0): array
    {
        $table = $this->getTable();
        $clauses = [];
        $params = [];

        foreach ($conditions as $col => $val) {
            $paramName = ":p_" . preg_replace('/[^a-zA-Z0-9_]/', '', $col);
            $clauses[] = "{$col} = {$paramName}";
            $params[$paramName] = $val;
        }

        $sql = "SELECT * FROM {$table}";
        if (!empty($clauses)) {
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Insert a new record into table
     */
    public function create(array $data): int
    {
        $table = $this->getTable();
        // Filter by fillable if defined
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);

        $params = [];
        foreach ($data as $k => $v) {
            $params[":{$k}"] = $v;
        }

        $stmt->execute($params);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing record
     */
    public function update(int $id, array $data): bool
    {
        $table = $this->getTable();
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }

        $sets = [];
        $params = [':id' => $id];
        foreach ($data as $col => $val) {
            $sets[] = "{$col} = :{$col}";
            $params[":{$col}"] = $val;
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a record by primary key ID
     */
    public function delete(int $id): bool
    {
        $table = $this->getTable();
        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Count rows matching conditions
     */
    public function count(array $conditions = []): int
    {
        $table = $this->getTable();
        $sql = "SELECT COUNT(*) as total FROM {$table}";
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $col => $val) {
                $p = ":cnt_" . preg_replace('/[^a-zA-Z0-9_]/', '', $col);
                $clauses[] = "{$col} = {$p}";
                $params[$p] = $val;
            }
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Execute arbitrary raw query with prepared parameters
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
