<?php
namespace Models;

use Core\Model;

/**
 * Class ClaimModel
 * Manages Vehicle Damage Claim submissions and multi-file evidence
 */
class ClaimModel extends Model
{
    protected string $table = 'claims';
    protected array $fillable = [
        'claim_number', 'policy_id', 'submitted_by',
        'incident_date', 'incident_time', 'incident_location',
        'chronology', 'estimated_damage_cost', 'status',
        'cancellation_reason', 'cancelled_at', 'cancelled_by',
        'created_at', 'updated_at'
    ];

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get claims filtered by the user who submitted them (for customer/nasabah role)
     */
    public function getAllByUser(int $userId, string $search = '', string $status = ''): array
    {
        $sql = "
            SELECT 
                c.*, 
                p.policy_number, 
                p.customer_name, 
                p.vehicle_brand, 
                p.vehicle_model, 
                p.vehicle_plate, 
                p.coverage_amount,
                u.name as submitter_name,
                (SELECT COUNT(*) FROM claim_files WHERE claim_id = c.id) as files_count,
                sr.damage_level,
                sr.approved_claim_amount,
                sr.approval_status
            FROM {$this->table} c
            JOIN policies p ON c.policy_id = p.id
            JOIN users u ON c.submitted_by = u.id
            LEFT JOIN survey_reports sr ON sr.claim_id = c.id
            WHERE c.submitted_by = :user_id
        ";
        $params = [':user_id' => $userId];

        if (!empty($search)) {
            $sql .= " AND (c.claim_number LIKE :q1 OR p.customer_name LIKE :q2 OR p.policy_number LIKE :q3 OR p.vehicle_plate LIKE :q4)";
            $params[':q1'] = "%{$search}%";
            $params[':q2'] = "%{$search}%";
            $params[':q3'] = "%{$search}%";
            $params[':q4'] = "%{$search}%";
        }

        if (!empty($status) && $status !== 'Semua Status') {
            $sql .= " AND c.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY c.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get claims joined with policy and user information
     */
    public function getAllWithDetails(string $search = '', string $status = '', int $limit = 50, int $offset = 0): array
    {
        $sql = "
            SELECT 
                c.*, 
                p.policy_number, 
                p.customer_name, 
                p.vehicle_brand, 
                p.vehicle_model, 
                p.vehicle_plate, 
                p.coverage_amount,
                u.name as submitter_name,
                (SELECT COUNT(*) FROM claim_files WHERE claim_id = c.id) as files_count,
                sr.damage_level,
                sr.approved_claim_amount,
                sr.approval_status
            FROM {$this->table} c
            JOIN policies p ON c.policy_id = p.id
            JOIN users u ON c.submitted_by = u.id
            LEFT JOIN survey_reports sr ON sr.claim_id = c.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (c.claim_number LIKE :q1 OR p.customer_name LIKE :q2 OR p.policy_number LIKE :q3 OR p.vehicle_plate LIKE :q4)";
            $params[':q1'] = "%{$search}%";
            $params[':q2'] = "%{$search}%";
            $params[':q3'] = "%{$search}%";
            $params[':q4'] = "%{$search}%";
        }

        if (!empty($status) && $status !== 'Semua Status') {
            $sql .= " AND c.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY c.id DESC";

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
     * Get single claim with complete related data
     */
    public function getByIdWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                c.*, 
                p.policy_number, 
                p.customer_name, 
                p.nik,
                p.phone,
                p.email as customer_email,
                p.insurance_type,
                p.coverage_amount,
                p.start_date as policy_start_date,
                p.end_date as policy_end_date,
                p.vehicle_plate, 
                p.vehicle_brand, 
                p.vehicle_model,
                p.status as policy_status,
                u.name as submitter_name,
                u.role_label as submitter_role,
                sr.id as survey_id,
                sr.surveyor_id,
                sr.survey_date,
                sr.survey_location,
                sr.damage_level,
                sr.surveyor_notes,
                sr.surveyor_estimated_cost,
                sr.approved_claim_amount,
                sr.auditor_notes,
                sr.approval_status,
                sr.approval_date,
                aud.name as auditor_name,
                sur.name as surveyor_name,
                can.name as canceller_name
            FROM {$this->table} c
            JOIN policies p ON c.policy_id = p.id
            JOIN users u ON c.submitted_by = u.id
            LEFT JOIN survey_reports sr ON sr.claim_id = c.id
            LEFT JOIN users sur ON sr.surveyor_id = sur.id
            LEFT JOIN users aud ON sr.auditor_id = aud.id
            LEFT JOIN users can ON c.cancelled_by = can.id
            WHERE c.id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $claim = $stmt->fetch();

        if ($claim) {
            $claim['files'] = $this->getClaimFiles($id);
        }

        return $claim ?: null;
    }

    /**
     * Generate unique Claim Number: CLM-YYYY-XXXX
     */
    public function generateClaimNumber(): string
    {
        $year = date('Y');
        $stmt = $this->db->query("SELECT MAX(id) as max_id FROM {$this->table}");
        $row = $stmt->fetch();
        $next = ((int)($row['max_id'] ?? 0)) + 1048; // aligned with mockup CLM-2025-1047
        return sprintf("CLM-%s-%04d", $year, $next);
    }

    /**
     * Add uploaded file record
     */
    public function addClaimFile(int $claimId, array $fileData): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO claim_files (claim_id, original_name, file_name, file_path, file_size, mime_type, file_category, created_at)
            VALUES (:claim_id, :original_name, :file_name, :file_path, :file_size, :mime_type, :file_category, :created_at)
        ");
        $stmt->execute([
            ':claim_id'      => $claimId,
            ':original_name' => $fileData['original_name'],
            ':file_name'     => $fileData['file_name'],
            ':file_path'     => $fileData['file_path'],
            ':file_size'     => $fileData['file_size'],
            ':mime_type'     => $fileData['mime_type'],
            ':file_category' => $fileData['file_category'] ?? 'foto_kerusakan',
            ':created_at'    => date('Y-m-d H:i:s')
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Retrieve all uploaded files for a claim
     */
    public function getClaimFiles(int $claimId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM claim_files
            WHERE claim_id = :claim_id
            ORDER BY id ASC
        ");
        $stmt->execute([':claim_id' => $claimId]);
        return $stmt->fetchAll();
    }

    /**
     * Update claim workflow status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET status = :status, updated_at = :updated_at
            WHERE id = :id
        ");
        return $stmt->execute([
            ':status'     => $status,
            ':updated_at' => date('Y-m-d H:i:s'),
            ':id'         => $id
        ]);
    }

    /**
     * Check if a claim can be edited
     * Claim only editable if status is early stage (Pengajuan Baru or Survey Lapangan before physical survey executed)
     */
    public function isEditable(array $claim): bool
    {
        $status = $claim['status'] ?? '';
        // Closed / advanced status cannot be edited
        if (in_array($status, ['Dalam Audit', 'Disetujui', 'Ditolak', 'Dibatalkan'], true)) {
            return false;
        }

        // If survey has already been conducted or recorded
        if (!empty($claim['survey_date']) || !empty($claim['surveyor_notes']) || !empty($claim['damage_level'])) {
            return false;
        }

        // Only initial stages are allowed
        return in_array($status, ['Pengajuan Baru', 'Survey Lapangan'], true);
    }

    /**
     * Check if a claim can be cancelled
     * Claim only cancellable before survey or decision
     */
    public function isCancellable(array $claim): bool
    {
        $status = $claim['status'] ?? '';
        // Cannot cancel if already cancelled, audit, approved, or rejected
        if (in_array($status, ['Dalam Audit', 'Disetujui', 'Ditolak', 'Dibatalkan'], true)) {
            return false;
        }

        // Cannot cancel if survey has already taken place
        if (!empty($claim['survey_date']) || !empty($claim['surveyor_notes'])) {
            return false;
        }

        return in_array($status, ['Pengajuan Baru', 'Survey Lapangan'], true);
    }

    /**
     * Update editable claim data
     */
    public function updateClaim(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowedFields = [
            'policy_id', 'incident_date', 'incident_time',
            'incident_location', 'chronology', 'estimated_damage_cost'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = :updated_at";
        $params[':updated_at'] = date('Y-m-d H:i:s');

        $setClause = implode(', ', $fields);
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Cancel claim (soft cancellation without deleting data)
     */
    public function cancelClaim(int $id, int $cancelledBy, string $reason = ''): bool
    {
        $now = date('Y-m-d H:i:s');
        $sql = "
            UPDATE {$this->table}
            SET status = 'Dibatalkan',
                cancellation_reason = :reason,
                cancelled_at = :cancelled_at,
                cancelled_by = :cancelled_by,
                updated_at = :updated_at
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':reason'       => trim($reason),
            ':cancelled_at' => $now,
            ':cancelled_by' => $cancelledBy,
            ':updated_at'   => $now,
            ':id'           => $id
        ]);
    }

    /**
     * Get single claim file by ID
     */
    public function getClaimFileById(int $fileId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM claim_files WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $fileId]);
        $file = $stmt->fetch();
        return $file ?: null;
    }

    /**
     * Delete a single claim file from database and disk
     */
    public function deleteClaimFile(int $fileId, int $claimId): bool
    {
        $file = $this->getClaimFileById($fileId);
        if (!$file || (int)$file['claim_id'] !== $claimId) {
            return false;
        }

        // Delete physical file if exists
        $filePath = dirname(__DIR__) . '/public/' . $file['file_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $stmt = $this->db->prepare("DELETE FROM claim_files WHERE id = :id AND claim_id = :claim_id");
        return $stmt->execute([':id' => $fileId, ':claim_id' => $claimId]);
    }
}
