<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\Security;
use Core\AuditLogger;
use Models\ClaimModel;
use Models\SurveyModel;
use Models\UserModel;

/**
 * Class SurveyController
 * CRUD Hasil Survey Kerusakan & Persetujuan Nilai Klaim
 * Handles surveyor field reports, auditor final cost approvals, and audit trail timeline
 */
class SurveyController extends Controller
{
    private ClaimModel $claimModel;
    private SurveyModel $surveyModel;
    private UserModel $userModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->claimModel = new ClaimModel();
        $this->surveyModel = new SurveyModel();
        $this->userModel = new UserModel();
        $this->auditLogger = new AuditLogger();
    }

    /**
     * List of claims ready for survey or approval
     * Hanya Admin yang berhak mengakses survey dan persetujuan klaim
     */
    public function index(): void
    {
        $currentUser = $this->requireRole(['admin']);

        $search = $this->input('q', '');
        $claims = $this->claimModel->getAllWithDetails($search);

        $this->render('survey/index', [
            'title'     => 'Hasil Survey & Persetujuan - CLAIMORA',
            'user'      => $currentUser,
            'claims'    => $claims,
            'search'    => $search,
            'activeNav' => 'survey'
        ]);
    }

    /**
     * View and Edit Survey Verification & Approval for a specific claim
     * Hanya Admin yang berhak mengakses halaman verifikasi survey
     * (Matches the exact mockup in Image 1)
     */
    public function verify(): void
    {
        $currentUser = $this->requireRole(['admin']);
        $claimId = (int)$this->input('id', 1); // default to claim 1 (CLM-2025-1047) if not specified

        $claim = $this->claimModel->getByIdWithDetails($claimId);
        if (!$claim) {
            Session::setFlash('danger', 'Data klaim tidak ditemukan.');
            $this->redirect('index.php?page=survey');
            return;
        }

        $survey = $this->surveyModel->findByClaimId($claimId);
        $timeline = $this->surveyModel->getClaimAuditTimeline($claim['claim_number']);

        // List of admins who perform inspection / survey
        $surveyors = $this->userModel->where(['role' => 'admin']);
        if (empty($surveyors)) {
            $surveyors = [$currentUser];
        }

        $this->render('survey/verify', [
            'title'     => "Hasil Survey & Persetujuan: {$claim['claim_number']} - CLAIMORA",
            'user'      => $currentUser,
            'claim'     => $claim,
            'survey'    => $survey,
            'timeline'  => $timeline,
            'surveyors' => $surveyors,
            'activeNav' => 'survey'
        ]);
    }

    /**
     * Save Field Surveyor Findings
     * Admin berhak mengisi dan menyimpan laporan survey pemeriksaan fisik
     */
    public function save_survey(): void
    {
        $currentUser = $this->requireRole(['admin']);
        $this->validateCsrf();

        $claimId       = (int)$this->input('claim_id', 0);
        $surveyorId    = (int)$this->input('surveyor_id', $currentUser['id']);
        $surveyDate    = trim($this->input('survey_date', date('Y-m-d')));
        $surveyLocation = trim($this->input('survey_location', ''));
        $damageLevel   = trim($this->input('damage_level', 'Sedang'));
        $surveyorNotes = trim($this->input('surveyor_notes', ''));
        $rawCost       = str_replace(['Rp', '.', ' ', ','], '', $this->input('surveyor_estimated_cost', '0'));
        $estimatedCost = (float)$rawCost;

        $claim = $this->claimModel->findById($claimId);
        if (!$claim) {
            Session::setFlash('danger', 'Klaim tidak ditemukan.');
            $this->redirect('index.php?page=survey');
            return;
        }

        $verifyUrl = "index.php?page=survey&action=verify&id={$claimId}";

        // A decided claim is final; re-saving the survey would drop it back to 'Dalam Audit'
        if (in_array($claim['status'], ['Disetujui', 'Ditolak'], true)) {
            Session::setFlash('danger', "Klaim {$claim['claim_number']} sudah {$claim['status']}, laporan survey tidak dapat diubah lagi.");
            $this->redirect($verifyUrl);
            return;
        }

        $errors = [];
        if (!$this->userModel->findById($surveyorId)) {
            $errors[] = 'Surveyor yang dipilih tidak valid.';
        }
        if (!Security::isValidDate($surveyDate)) {
            $errors[] = 'Tanggal pelaksanaan survey tidak valid.';
        }
        if (empty($surveyLocation)) {
            $errors[] = 'Lokasi pemeriksaan fisik wajib diisi.';
        }
        if (!in_array($damageLevel, ['Ringan', 'Sedang', 'Berat / Total Loss'], true)) {
            $errors[] = 'Tingkat kerusakan tidak valid.';
        }
        if (empty($surveyorNotes)) {
            $errors[] = 'Catatan temuan surveyor wajib diisi.';
        }

        if (!empty($errors)) {
            Session::setFlash('danger', implode('<br>', $errors));
            $this->redirect($verifyUrl);
            return;
        }

        $surveyData = [
            'claim_id'                => $claimId,
            'surveyor_id'             => $surveyorId,
            'survey_date'             => $surveyDate,
            'survey_location'         => $surveyLocation,
            'damage_level'            => $damageLevel,
            'surveyor_notes'          => $surveyorNotes,
            'surveyor_estimated_cost' => $estimatedCost
        ];

        $this->surveyModel->saveSurvey($surveyData);

        // Advance claim status to 'Dalam Audit'
        $this->claimModel->updateStatus($claimId, 'Dalam Audit');

        // Audit Trail
        $surveyorName = $currentUser['name'];
        $this->auditLogger->log(
            'Hasil Survey',
            'Tambah Data',
            "Laporan Survey Fisik Lapangan {$claim['claim_number']} oleh {$surveyorName}",
            'Berhasil'
        );

        Session::setFlash('success', "Hasil survey lapangan untuk {$claim['claim_number']} berhasil disimpan. Klaim dialihkan ke tahap Audit & Persetujuan.");
        $this->redirect("index.php?page=survey&action=verify&id={$claimId}");
    }

    /**
     * Process Auditor Approval / Rejection of Claim Amount
     * HANYA Admin yang bisa approve atau tolak klaim
     */
    public function approve(): void
    {
        $currentUser = $this->requireRole(['admin']);
        $this->validateCsrf();

        $claimId        = (int)$this->input('claim_id', 0);
        $rawAmount      = str_replace(['Rp', '.', ' ', ','], '', $this->input('approved_claim_amount', '0'));
        $approvedAmount = (float)$rawAmount;
        $auditorNotes   = trim($this->input('auditor_notes', ''));
        $isApproved     = !empty($this->input('agree_approval'));
        $actionChoice   = $this->input('approval_action', 'approve'); // 'approve' or 'reject'

        $claim = $this->claimModel->getByIdWithDetails($claimId);
        if (!$claim) {
            Session::setFlash('danger', 'Klaim tidak ditemukan.');
            $this->redirect('index.php?page=survey');
            return;
        }

        if ($actionChoice === 'reject') {
            // A rejected claim pays nothing; keeps dashboard approved totals accurate
            $approvedAmount = 0.0;
            $status = 'Ditolak';
            $claimStatus = 'Ditolak';
            $auditAction = 'Tolak Klaim';
            $msg = "Klaim {$claim['claim_number']} telah ditolak.";
        } else {
            if (!$isApproved) {
                Session::setFlash('danger', 'Harap centang konfirmasi persetujuan klaim sebelum menyimpan.');
                $this->redirect("index.php?page=survey&action=verify&id={$claimId}");
                return;
            }
            if ($approvedAmount <= 0 || $approvedAmount > (float)$claim['coverage_amount']) {
                Session::setFlash('danger', 'Nilai klaim harus lebih dari Rp 0 dan tidak boleh melebihi batas pertanggungan polis (Rp ' . number_format((float)$claim['coverage_amount'], 0, ',', '.') . ').');
                $this->redirect("index.php?page=survey&action=verify&id={$claimId}");
                return;
            }
            $status = 'Disetujui';
            $claimStatus = 'Disetujui';
            $auditAction = 'Approve Klaim';
            $msg = "Klaim {$claim['claim_number']} berhasil disetujui sebesar Rp " . number_format($approvedAmount, 0, ',', '.') . ".";
        }

        $this->surveyModel->approveClaim($claimId, $approvedAmount, $auditorNotes, $currentUser['id'], $status);
        $this->claimModel->updateStatus($claimId, $claimStatus);

        // Security Audit Trail (matches exact format in mockup Image 2)
        $detail = "Klaim No. {$claim['claim_number']} (Nilai: Rp " . number_format($approvedAmount, 0, ',', '.') . ")";
        $this->auditLogger->log('Hasil Survey', $auditAction, $detail, 'Berhasil');

        // Second milestone entry for audit timeline
        $this->auditLogger->log(
            'Hasil Survey',
            'Persetujuan Klaim',
            "Persetujuan oleh {$currentUser['name']} - Status: {$status}",
            'Berhasil'
        );

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => $msg]);
        }

        Session::setFlash('success', $msg);
        $this->redirect("index.php?page=survey&action=verify&id={$claimId}");
    }
}
