<?php
namespace Models;

use Core\Model;

/**
 * Class SurveyModel
 * Manages Field Surveyor Inspection Reports & Final Claim Approval
 */
class SurveyModel extends Model
{
    protected string $table = 'survey_reports';
    protected array $fillable = [
        'claim_id', 'surveyor_id', 'survey_date', 'survey_location',
        'damage_level', 'surveyor_notes', 'surveyor_estimated_cost',
        'approved_claim_amount', 'auditor_notes', 'auditor_id',
        'approval_date', 'approval_status', 'created_at', 'updated_at'
    ];

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Find survey by associated claim ID
     */
    public function findByClaimId(int $claimId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT sr.*, 
                   u.name as surveyor_name, 
                   aud.name as auditor_name
            FROM {$this->table} sr
            LEFT JOIN users u ON sr.surveyor_id = u.id
            LEFT JOIN users aud ON sr.auditor_id = aud.id
            WHERE sr.claim_id = :cid
            LIMIT 1
        ");
        $stmt->execute([':cid' => $claimId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Create or update survey inspection report
     */
    public function saveSurvey(array $data): int
    {
        $existing = $this->findByClaimId($data['claim_id']);
        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET surveyor_id = :surveyor_id,
                    survey_date = :survey_date,
                    survey_location = :survey_location,
                    damage_level = :damage_level,
                    surveyor_notes = :surveyor_notes,
                    surveyor_estimated_cost = :surveyor_estimated_cost,
                    updated_at = :updated_at
                WHERE claim_id = :claim_id
            ");
            $stmt->execute([
                ':surveyor_id'             => $data['surveyor_id'],
                ':survey_date'             => $data['survey_date'],
                ':survey_location'         => $data['survey_location'],
                ':damage_level'            => $data['damage_level'],
                ':surveyor_notes'          => $data['surveyor_notes'],
                ':surveyor_estimated_cost' => $data['surveyor_estimated_cost'],
                ':updated_at'              => $now,
                ':claim_id'                => $data['claim_id']
            ]);
            return (int)$existing['id'];
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table}
                (claim_id, surveyor_id, survey_date, survey_location, damage_level, surveyor_notes, surveyor_estimated_cost, approval_status, created_at)
                VALUES
                (:claim_id, :surveyor_id, :survey_date, :survey_location, :damage_level, :surveyor_notes, :surveyor_estimated_cost, 'Pending', :created_at)
            ");
            $stmt->execute([
                ':claim_id'                => $data['claim_id'],
                ':surveyor_id'             => $data['surveyor_id'],
                ':survey_date'             => $data['survey_date'],
                ':survey_location'         => $data['survey_location'],
                ':damage_level'            => $data['damage_level'],
                ':surveyor_notes'          => $data['surveyor_notes'],
                ':surveyor_estimated_cost' => $data['surveyor_estimated_cost'],
                ':created_at'              => $now
            ]);
            return (int)$this->db->lastInsertId();
        }
    }

    /**
     * Submit Auditor claim approval or rejection
     */
    public function approveClaim(int $claimId, float $approvedAmount, string $auditorNotes, int $auditorId, string $status = 'Disetujui'): bool
    {
        $now = date('Y-m-d H:i:s');
        $existing = $this->findByClaimId($claimId);

        if (!$existing) {
            // Insert initial record if surveyor report wasn't formally created
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table}
                (claim_id, surveyor_id, survey_date, survey_location, damage_level, surveyor_notes, surveyor_estimated_cost, approved_claim_amount, auditor_notes, auditor_id, approval_date, approval_status, created_at)
                VALUES
                (:claim_id, :surveyor_id, :survey_date, 'Lokasi Terdaftar', 'Sedang', 'Verifikasi berkas administratif', :estimated_cost, :approved_amount, :auditor_notes, :auditor_id, :approval_date, :status, :created_at)
            ");
            return $stmt->execute([
                ':claim_id'        => $claimId,
                ':surveyor_id'     => $auditorId,
                ':survey_date'     => date('Y-m-d'),
                ':estimated_cost'  => $approvedAmount,
                ':approved_amount' => $approvedAmount,
                ':auditor_notes'   => $auditorNotes,
                ':auditor_id'      => $auditorId,
                ':approval_date'   => $now,
                ':status'          => $status,
                ':created_at'      => $now
            ]);
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET approved_claim_amount = :approved_amount,
                auditor_notes = :auditor_notes,
                auditor_id = :auditor_id,
                approval_date = :approval_date,
                approval_status = :status,
                updated_at = :updated_at
            WHERE claim_id = :claim_id
        ");

        return $stmt->execute([
            ':approved_amount' => $approvedAmount,
            ':auditor_notes'   => $auditorNotes,
            ':auditor_id'      => $auditorId,
            ':approval_date'   => $now,
            ':status'          => $status,
            ':updated_at'      => $now,
            ':claim_id'        => $claimId
        ]);
    }

    /**
     * Get security audit timeline events for a claim
     */
    public function getClaimAuditTimeline(string $claimNumber): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM audit_logs
            WHERE detail LIKE :pattern
            ORDER BY created_at ASC
        ");
        $stmt->execute([':pattern' => "%{$claimNumber}%"]);
        return $stmt->fetchAll();
    }
}
