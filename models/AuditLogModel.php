<?php
namespace Models;

use Core\Model;

/**
 * Class AuditLogModel
 * Provides querying, filtering, and export capabilities for security audit trail
 */
class AuditLogModel extends Model
{
    protected string $table = 'audit_logs';
    protected array $fillable = [
        'user_id', 'user_name', 'module', 'activity', 'detail', 'ip_address', 'status', 'created_at'
    ];

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Filter audit trail with multiple criteria
     */
    public function filterLogs(
        string $startDate = '',
        string $endDate = '',
        string $module = '',
        string $userName = '',
        string $search = '',
        int $limit = 10,
        int $offset = 0
    ): array {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($startDate)) {
            $sql .= " AND created_at >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }

        if (!empty($endDate)) {
            $sql .= " AND created_at <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        if (!empty($module) && $module !== 'Semua Modul') {
            $sql .= " AND module = :module";
            $params[':module'] = $module;
        }

        if (!empty($userName) && $userName !== 'Semua Pengguna') {
            $sql .= " AND user_name = :user_name";
            $params[':user_name'] = $userName;
        }

        if (!empty($search)) {
            $sql .= " AND (detail LIKE :q1 OR activity LIKE :q2 OR ip_address LIKE :q3 OR user_name LIKE :q4)";
            $params[':q1'] = "%{$search}%";
            $params[':q2'] = "%{$search}%";
            $params[':q3'] = "%{$search}%";
            $params[':q4'] = "%{$search}%";
        }

        $sql .= " ORDER BY id DESC";

        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count total filtered records for pagination
     */
    public function countFiltered(
        string $startDate = '',
        string $endDate = '',
        string $module = '',
        string $userName = '',
        string $search = ''
    ): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($startDate)) {
            $sql .= " AND created_at >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }

        if (!empty($endDate)) {
            $sql .= " AND created_at <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        if (!empty($module) && $module !== 'Semua Modul') {
            $sql .= " AND module = :module";
            $params[':module'] = $module;
        }

        if (!empty($userName) && $userName !== 'Semua Pengguna') {
            $sql .= " AND user_name = :user_name";
            $params[':user_name'] = $userName;
        }

        if (!empty($search)) {
            $sql .= " AND (detail LIKE :q1 OR activity LIKE :q2 OR ip_address LIKE :q3 OR user_name LIKE :q4)";
            $params[':q1'] = "%{$search}%";
            $params[':q2'] = "%{$search}%";
            $params[':q3'] = "%{$search}%";
            $params[':q4'] = "%{$search}%";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Get distinct modules for filter select options
     */
    public function getDistinctModules(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT module FROM {$this->table} ORDER BY module ASC");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get distinct usernames for filter select options
     */
    public function getDistinctUsers(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT user_name FROM {$this->table} ORDER BY user_name ASC");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
