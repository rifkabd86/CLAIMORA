<?php
namespace Models;

use Core\Model;

/**
 * Class PolicyModel
 * Encapsulates CRUD operations for Customer Insurance Policies & Coverage Limits
 */
class PolicyModel extends Model
{
    protected string $table = 'policies';
    protected array $fillable = [
        'policy_number', 'customer_name', 'nik', 'phone', 'email',
        'insurance_type', 'coverage_amount', 'start_date', 'end_date',
        'vehicle_plate', 'vehicle_brand', 'vehicle_model', 'status', 'notes',
        'created_at', 'updated_at'
    ];

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get policies belonging to a specific customer by email or customer name
     */
    public function getByCustomer(string $email, string $name, string $query = '', string $status = ''): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE (email = :email OR LOWER(customer_name) = LOWER(:name))";
        $params = [
            ':email' => trim($email),
            ':name'  => trim($name)
        ];

        if (!empty($query)) {
            $sql .= " AND (policy_number LIKE :q1 OR vehicle_plate LIKE :q2)";
            $params[':q1'] = "%{$query}%";
            $params[':q2'] = "%{$query}%";
        }

        if (!empty($status) && $status !== 'Semua Status') {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Check a policy against the same customer identity rule as getByCustomer()
     */
    public function isOwnedBy(array $policy, string $email, string $name): bool
    {
        $email = trim($email);
        return ($email !== '' && strcasecmp((string)$policy['email'], $email) === 0)
            || strcasecmp((string)$policy['customer_name'], trim($name)) === 0;
    }

    /**
     * Search policies with multi-criteria filtering
     */
    public function search(string $query = '', string $status = '', int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (policy_number LIKE :q1 OR customer_name LIKE :q2 OR vehicle_plate LIKE :q3 OR phone LIKE :q4)";
            $params[':q1'] = "%{$query}%";
            $params[':q2'] = "%{$query}%";
            $params[':q3'] = "%{$query}%";
            $params[':q4'] = "%{$query}%";
        }

        if (!empty($status) && $status !== 'Semua Status') {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
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
     * Count policies matching search
     */
    public function countSearch(string $query = '', string $status = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (policy_number LIKE :q1 OR customer_name LIKE :q2 OR vehicle_plate LIKE :q3)";
            $params[':q1'] = "%{$query}%";
            $params[':q2'] = "%{$query}%";
            $params[':q3'] = "%{$query}%";
        }

        if (!empty($status) && $status !== 'Semua Status') {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Check if policy number exists (unique validation)
     */
    public function isPolicyNumberExists(string $policyNumber, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table} WHERE policy_number = :pno";
        $params = [':pno' => $policyNumber];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return ((int)($res['cnt'] ?? 0)) > 0;
    }

    /**
     * Get active policies list for claim submission dropdown
     */
    public function getActivePolicies(): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT id, policy_number, customer_name, insurance_type, vehicle_brand, vehicle_model, vehicle_plate, coverage_amount, start_date, end_date, status
            FROM {$this->table}
            WHERE status = 'Aktif'
              AND :today1 >= start_date
              AND :today2 <= end_date
            ORDER BY customer_name ASC
        ");
        $stmt->execute([':today1' => $today, ':today2' => $today]);
        return $stmt->fetchAll();
    }

    /**
     * Get eligible policies for claim submission for a specific customer
     * Hanya polis milik nasabah dengan status 'Aktif' dan masa berlaku masih berjalan (start_date <= today <= end_date)
     */
    public function getEligiblePoliciesForCustomer(string $email, string $name): array
    {
        $today = date('Y-m-d');
        $sql = "
            SELECT id, policy_number, customer_name, nik, phone, email, insurance_type,
                   coverage_amount, start_date, end_date, vehicle_plate, vehicle_brand,
                   vehicle_model, status, notes
            FROM {$this->table}
            WHERE (email = :email OR LOWER(customer_name) = LOWER(:name))
              AND status = 'Aktif'
              AND :today1 >= start_date
              AND :today2 <= end_date
            ORDER BY id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email'  => trim($email),
            ':name'   => trim($name),
            ':today1' => $today,
            ':today2' => $today
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Update customer name and email across all policies owned by that customer
     */
    public function updateCustomerInfo(string $oldEmail, string $oldName, string $newEmail, string $newName): int
    {
        $sql = "UPDATE {$this->table} 
                SET email = :new_email, customer_name = :new_name, updated_at = :up 
                WHERE (email = :old_email OR LOWER(customer_name) = LOWER(:old_name))";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':new_email' => trim($newEmail),
            ':new_name'  => trim($newName),
            ':up'        => date('Y-m-d H:i:s'),
            ':old_email' => trim($oldEmail),
            ':old_name'  => trim($oldName)
        ]);
        return $stmt->rowCount();
    }
    /**
     * Hitung jumlah klaim yang terhubung dengan polis ini
     */
    public function getClaimsCount(int $policyId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM claims WHERE policy_id = :pid");
        $stmt->execute([':pid' => $policyId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Nonaktifkan polis (soft deactivation jika memiliki riwayat klaim)
     */
    public function deactivate(int $policyId): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'Ditangguhkan', updated_at = :up WHERE id = :id");
        return $stmt->execute([':up' => date('Y-m-d H:i:s'), ':id' => $policyId]);
    }
}
