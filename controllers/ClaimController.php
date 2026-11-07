<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\Security;
use Core\AuditLogger;
use Core\VehicleClaimEvaluator;
use Models\ClaimModel;
use Models\PolicyModel;

/**
 * Class ClaimController
 * CRUD Berkas Pengajuan Klaim Kerusakan Kendaraan
 * Implements Multi-step submission wizard, Multi-upload photos, and MIME validation
 */
class ClaimController extends Controller
{
    private ClaimModel $claimModel;
    private PolicyModel $policyModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->claimModel = new ClaimModel();
        $this->policyModel = new PolicyModel();
        $this->auditLogger = new AuditLogger();
    }

    /**
     * List all claims with filter
     * Admin: seluruh klaim | Nasabah (customer): hanya klaim miliknya
     */
    public function index(): void
    {
        $currentUser = $this->requireAuth();

        $search = $this->input('q', '');
        $status = $this->input('status', '');

        // Nasabah hanya boleh melihat klaim yang dia ajukan sendiri
        if ($currentUser['role'] === 'customer') {
            $claims = $this->claimModel->getAllByUser($currentUser['id'], $search, $status);
        } else {
            $claims = $this->claimModel->getAllWithDetails($search, $status);
        }

        $this->render('claims/index', [
            'title'     => 'Pengajuan Klaim - CLAIMORA',
            'user'      => $currentUser,
            'claims'    => $claims,
            'search'    => $search,
            'status'    => $status,
            'activeNav' => 'claims_history'
        ]);
    }

    /**
     * Multi-step submission wizard form
     * Hanya Admin & Nasabah (customer) yang boleh mengajukan klaim baru
     */
    public function create(): void
    {
        $currentUser = $this->requireRole(['admin', 'customer']);

        // Nasabah hanya boleh mengajukan klaim atas polis aktif miliknya yang masih berlaku
        if ($currentUser['role'] === 'customer') {
            $policies = $this->policyModel->getEligiblePoliciesForCustomer(
                $currentUser['email'] ?? '',
                $currentUser['name'] ?? ''
            );
            $totalCustomerPolicies = count($this->policyModel->getByCustomer(
                $currentUser['email'] ?? '',
                $currentUser['name'] ?? ''
            ));
        } else {
            $policies = $this->policyModel->getActivePolicies();
            $totalCustomerPolicies = count($policies);
        }

        $this->render('claims/create', [
            'title'                 => 'Form Pengajuan Klaim Baru - CLAIMORA',
            'user'                  => $currentUser,
            'policies'              => $policies,
            'totalCustomerPolicies' => $totalCustomerPolicies,
            'activeNav'             => 'claims_create'
        ]);
    }

    /**
     * Process new claim submission
     * Hanya Admin & Nasabah (customer) yang boleh submit klaim
     */
    public function store(): void
    {
        $currentUser = $this->requireRole(['admin', 'customer']);
        $this->validateCsrf();

        $policyId          = (int)$this->input('policy_id', 0);
        $incidentDate      = trim($this->input('incident_date', ''));
        $incidentTime      = trim($this->input('incident_time', ''));
        $incidentLocation  = trim($this->input('incident_location', ''));
        $chronology        = trim($this->input('chronology', ''));
        $rawEstimatedCost  = str_replace(['Rp', '.', ' ', ','], '', $this->input('estimated_damage_cost', '0'));
        $estimatedCost     = (float)$rawEstimatedCost;
        $fileIds           = $this->input('uploaded_file_ids', []);

        $errors = [];

        $hasValidIncidentDate = Security::isValidDate($incidentDate);
        if (!$hasValidIncidentDate || !Security::isValidDate(substr($incidentTime, 0, 5), 'H:i')) {
            $errors[] = 'Tanggal dan waktu kejadian wajib diisi dengan format yang benar.';
        }

        // Fetch policy
        $policy = $this->policyModel->findById($policyId);
        $isOwnPolicy = $policy && ($currentUser['role'] !== 'customer'
            || $this->policyModel->isOwnedBy($policy, $currentUser['email'] ?? '', $currentUser['name'] ?? ''));

        if (!$isOwnPolicy) {
            $errors[] = 'Polis asuransi yang dipilih tidak valid atau bukan milik Anda.';
        } else {
            // Validasi status dan masa berlaku polis
            $today = date('Y-m-d');
            if (($policy['status'] ?? '') !== 'Aktif') {
                $errors[] = "Polis {$policy['policy_number']} saat ini berstatus '{$policy['status']}'. Hanya polis berstatus Aktif yang dapat mengajukan klaim.";
            } elseif ($today < $policy['start_date'] || $today > $policy['end_date']) {
                $errors[] = "Masa berlaku proteksi polis {$policy['policy_number']} telah berakhir atau belum aktif (" . date('d/m/Y', strtotime($policy['start_date'])) . " s/d " . date('d/m/Y', strtotime($policy['end_date'])) . ").";
            }

            if ($hasValidIncidentDate) {
                // Polymorphic evaluation using VehicleClaimEvaluator (OOP Abstraction & Polymorphism)
                $evaluator = new VehicleClaimEvaluator($policy);
                $evaluation = $evaluator->evaluate($policy, $estimatedCost, $incidentDate);

                if (!$evaluation['isValid']) {
                    $errors = array_merge($errors, $evaluation['errors']);
                }
            }
        }

        if (empty($incidentLocation)) {
            $errors[] = 'Lokasi kejadian kecelakaan/kerusakan wajib diisi.';
        }
        if (empty($chronology) || strlen($chronology) < 15) {
            $errors[] = 'Kronologi kejadian wajib dijelaskan minimal 15 karakter.';
        }
        if ($estimatedCost <= 0) {
            $errors[] = 'Estimasi biaya perbaikan kerusakan wajib lebih dari Rp 0.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors], 422);
            }
            Session::setFlash('danger', implode('<br>', $errors));
            $this->redirect('index.php?page=claims&action=create');
            return;
        }

        $claimNumber = $this->claimModel->generateClaimNumber();
        $now = date('Y-m-d H:i:s');

        $claimData = [
            'claim_number'          => $claimNumber,
            'policy_id'             => $policyId,
            'submitted_by'          => $currentUser['id'],
            'incident_date'         => $incidentDate,
            'incident_time'         => $incidentTime,
            'incident_location'     => $incidentLocation,
            'chronology'            => $chronology,
            'estimated_damage_cost' => $estimatedCost,
            'status'                => 'Survey Lapangan', // Initial status awaiting survey
            'created_at'            => $now,
            'updated_at'            => $now
        ];

        $claimId = $this->claimModel->create($claimData);

        // Associate files pre-uploaded during wizard step 2; only this session's own uploads are accepted
        if (is_string($fileIds)) {
            $fileIds = json_decode($fileIds, true);
        }
        if (is_array($fileIds)) {
            $pending = Session::get('pending_claim_files', []);
            foreach ($fileIds as $token) {
                if (is_string($token) && isset($pending[$token])) {
                    $this->claimModel->addClaimFile($claimId, $pending[$token]);
                    unset($pending[$token]);
                }
            }
            Session::set('pending_claim_files', $pending);
        }

        // Process standard multi-file upload if submitted as normal multipart form
        if (!empty($_FILES['damage_photos']['name'][0])) {
            $this->handleDirectUploads($claimId, $_FILES['damage_photos']);
        }

        // Security Audit Log (matches screenshot format)
        $detail = "Klaim No. {$claimNumber} (Polis {$policy['policy_number']})";
        $this->auditLogger->log('Pengajuan Klaim', 'Tambah Data', $detail, 'Berhasil');

        $countFiles = count($this->claimModel->getClaimFiles($claimId));
        if ($countFiles > 0) {
            $this->auditLogger->log('Pengajuan Klaim', 'Upload Berkas', "{$countFiles} file (foto kerusakan kendaraan)", 'Berhasil');
        }

        if ($this->isAjax()) {
            $this->json([
                'success'      => true,
                'message'      => "Pengajuan klaim {$claimNumber} berhasil dikirim.",
                'claim_number' => $claimNumber,
                'claim_id'     => $claimId
            ]);
        }

        Session::setFlash('success', "Pengajuan klaim {$claimNumber} berhasil dikirim dan siap diverifikasi surveyor.");
        $this->redirect('index.php?page=claims');
    }

    /**
     * AJAX Endpoint for Drag & Drop Multi-file Upload
     * Hanya Admin & Nasabah yang boleh upload file klaim
     * Validates MIME type and file size (< 5MB)
     * The claim does not exist yet at this step (claim_files.claim_id is a required FK),
     * so the upload is held in the session until store() creates the claim.
     */
    public function upload_file(): void
    {
        $this->requireRole(['admin', 'customer']);
        $this->validateCsrf();

        if (empty($_FILES['file'])) {
            $this->json(['success' => false, 'message' => 'Tidak ada berkas yang diterima.'], 400);
        }

        $file = $_FILES['file'];
        // Allowed MIME types & max size 5MB
        [$isValid, $errorMessage, $mime] = Security::validateUpload($file, ['image/jpeg', 'image/png', 'image/webp'], 5 * 1024 * 1024);

        if (!$isValid) {
            $this->json(['success' => false, 'message' => $errorMessage], 422);
        }

        $uploadDir = dirname(__DIR__) . '/public/uploads/claims/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg'
        };

        $uniqueName = 'claim_' . bin2hex(random_bytes(12)) . '.' . $extension;
        $destPath   = $uploadDir . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->json(['success' => false, 'message' => 'Gagal memindahkan berkas unggahan ke direktori server.'], 500);
        }

        $fileData = [
            'original_name' => basename($file['name']),
            'file_name'     => $uniqueName,
            'file_path'     => 'uploads/claims/' . $uniqueName,
            'file_size'     => (int)$file['size'],
            'mime_type'     => $mime,
            'file_category' => 'foto_kerusakan'
        ];

        $fileId = bin2hex(random_bytes(12));
        $pending = Session::get('pending_claim_files', []);
        $pending[$fileId] = $fileData;
        Session::set('pending_claim_files', $pending);

        $this->json([
            'success'   => true,
            'file_id'   => $fileId,
            'file_name' => $fileData['original_name'],
            'file_url'  => $fileData['file_path'],
            'file_size' => round($fileData['file_size'] / 1024, 1) . ' KB'
        ]);
    }

    /**
     * AJAX Endpoint to remove a pending upload from the wizard
     * Hanya Admin & Nasabah yang boleh hapus file klaim
     */
    public function delete_file(): void
    {
        $this->requireRole(['admin', 'customer']);
        $this->validateCsrf();
        $fileId = (string)$this->input('file_id', '');

        $pending = Session::get('pending_claim_files', []);
        if (!isset($pending[$fileId])) {
            $this->json(['success' => false, 'message' => 'Berkas tidak ditemukan atau gagal dihapus.'], 404);
        }

        $absPath = dirname(__DIR__) . '/public/' . $pending[$fileId]['file_path'];
        if (is_file($absPath)) {
            @unlink($absPath);
        }
        unset($pending[$fileId]);
        Session::set('pending_claim_files', $pending);

        $this->json(['success' => true, 'message' => 'Foto berhasil dihapus.']);
    }

    /**
     * Helper to process traditional multi-file input arrays
     */
    private function handleDirectUploads(int $claimId, array $files): void
    {
        $uploadDir = dirname(__DIR__) . '/public/uploads/claims/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $singleFile = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i]
                ];

                [$isValid, , $mime] = Security::validateUpload($singleFile, ['image/jpeg', 'image/png', 'image/webp'], 5 * 1024 * 1024);
                if ($isValid) {
                    $ext = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
                    $uniqueName = 'claim_' . bin2hex(random_bytes(12)) . '.' . $ext;
                    $destPath = $uploadDir . $uniqueName;

                    if (move_uploaded_file($singleFile['tmp_name'], $destPath)) {
                        $this->claimModel->addClaimFile($claimId, [
                            'original_name' => basename($singleFile['name']),
                            'file_name'     => $uniqueName,
                            'file_path'     => 'uploads/claims/' . $uniqueName,
                            'file_size'     => (int)$singleFile['size'],
                            'mime_type'     => $mime,
                            'file_category' => 'foto_kerusakan'
                        ]);
                    }
                }
            }
        }
    }

    /**
     * View Claim detail
     * Nasabah (customer) hanya bisa melihat detail klaim miliknya sendiri
     */
    public function detail(): void
    {
        $currentUser = $this->requireAuth();
        $id = (int)$this->input('id', 0);

        $claim = $this->claimModel->getByIdWithDetails($id);
        if (!$claim) {
            Session::setFlash('danger', 'Klaim tidak ditemukan.');
            $this->redirect('index.php?page=claims');
            return;
        }

        // Nasabah hanya boleh melihat klaim yang dia ajukan sendiri
        if ($currentUser['role'] === 'customer' && (int)$claim['submitted_by'] !== (int)$currentUser['id']) {
            Session::setFlash('danger', 'Akses ditolak. Anda hanya dapat melihat klaim milik Anda sendiri.');
            $this->redirect('index.php?page=claims');
            return;
        }

        $this->render('claims/detail', [
            'title'     => "Detail Klaim {$claim['claim_number']} - CLAIMORA",
            'user'      => $currentUser,
            'claim'     => $claim,
            'activeNav' => 'claims_history'
        ]);
    }

    /**
     * Show Edit Claim form
     * Only eligible claims in initial status can be edited by the owner (or admin)
     */
    public function edit(): void
    {
        $currentUser = $this->requireAuth();
        $id = (int)$this->input('id', 0);

        $claim = $this->claimModel->getByIdWithDetails($id);
        if (!$claim) {
            Session::setFlash('danger', 'Klaim tidak ditemukan.');
            $this->redirect('index.php?page=claims');
            return;
        }

        // Ownership authorization check: nasabah hanya boleh mengedit klaim miliknya sendiri
        if ($currentUser['role'] === 'customer' && (int)$claim['submitted_by'] !== (int)$currentUser['id']) {
            Session::setFlash('danger', 'Akses ditolak. Anda hanya dapat mengedit klaim milik Anda sendiri.');
            $this->redirect('index.php?page=claims');
            return;
        }

        // Status restriction check
        if (!$this->claimModel->isEditable($claim)) {
            Session::setFlash('danger', "Klaim No. {$claim['claim_number']} berstatus '{$claim['status']}' dan sudah tidak dapat diubah.");
            $this->redirect('index.php?page=claims&action=detail&id=' . $id);
            return;
        }

        // Retrieve eligible policies for dropdown
        if ($currentUser['role'] === 'customer') {
            $policies = $this->policyModel->getEligiblePoliciesForCustomer(
                $currentUser['email'] ?? '',
                $currentUser['name'] ?? ''
            );
        } else {
            $policies = $this->policyModel->getActivePolicies();
        }

        // Ensure the current policy is present in the list
        $policyIds = array_column($policies, 'id');
        if (!in_array($claim['policy_id'], $policyIds)) {
            $currentPolicy = $this->policyModel->findById((int)$claim['policy_id']);
            if ($currentPolicy) {
                $policies[] = $currentPolicy;
            }
        }

        $this->render('claims/edit', [
            'title'     => "Ubah Pengajuan Klaim {$claim['claim_number']} - CLAIMORA",
            'user'      => $currentUser,
            'claim'     => $claim,
            'policies'  => $policies,
            'activeNav' => 'claims_history'
        ]);
    }

    /**
     * Process Claim update
     */
    public function update(): void
    {
        $currentUser = $this->requireAuth();
        $this->validateCsrf();

        $id = (int)$this->input('id', 0);
        $claim = $this->claimModel->getByIdWithDetails($id);

        if (!$claim) {
            Session::setFlash('danger', 'Klaim tidak ditemukan.');
            $this->redirect('index.php?page=claims');
            return;
        }

        // Ownership authorization check
        if ($currentUser['role'] === 'customer' && (int)$claim['submitted_by'] !== (int)$currentUser['id']) {
            Session::setFlash('danger', 'Akses ditolak. Anda hanya dapat mengubah klaim milik Anda sendiri.');
            $this->redirect('index.php?page=claims');
            return;
        }

        // Status restriction check
        if (!$this->claimModel->isEditable($claim)) {
            Session::setFlash('danger', "Klaim No. {$claim['claim_number']} berstatus '{$claim['status']}' dan sudah tidak dapat diubah.");
            $this->redirect('index.php?page=claims&action=detail&id=' . $id);
            return;
        }

        $policyId          = (int)$this->input('policy_id', $claim['policy_id']);
        $incidentDate      = trim($this->input('incident_date', ''));
        $incidentTime      = trim($this->input('incident_time', ''));
        $incidentLocation  = trim($this->input('incident_location', ''));
        $chronology        = trim($this->input('chronology', ''));
        $rawEstimatedCost  = str_replace(['Rp', '.', ' ', ','], '', $this->input('estimated_damage_cost', '0'));
        $estimatedCost     = (float)$rawEstimatedCost;
        $deleteFileIds     = $this->input('delete_file_ids', []);
        $fileIds           = $this->input('uploaded_file_ids', []);

        $errors = [];

        $hasValidIncidentDate = Security::isValidDate($incidentDate);
        if (!$hasValidIncidentDate || !Security::isValidDate(substr($incidentTime, 0, 5), 'H:i')) {
            $errors[] = 'Tanggal dan waktu kejadian wajib diisi dengan format yang benar.';
        }

        // Validate policy
        $policy = $this->policyModel->findById($policyId);
        $isOwnPolicy = $policy && ($currentUser['role'] !== 'customer'
            || $this->policyModel->isOwnedBy($policy, $currentUser['email'] ?? '', $currentUser['name'] ?? ''));

        if (!$isOwnPolicy) {
            $errors[] = 'Polis asuransi yang dipilih tidak valid atau bukan milik Anda.';
        } else {
            if ($hasValidIncidentDate) {
                $evaluator = new VehicleClaimEvaluator($policy);
                $evaluation = $evaluator->evaluate($policy, $estimatedCost, $incidentDate);
                if (!$evaluation['isValid']) {
                    $errors = array_merge($errors, $evaluation['errors']);
                }
            }
        }

        if (empty($incidentLocation)) {
            $errors[] = 'Lokasi kejadian kecelakaan/kerusakan wajib diisi.';
        }
        if (empty($chronology) || strlen($chronology) < 15) {
            $errors[] = 'Kronologi kejadian wajib dijelaskan minimal 15 karakter.';
        }
        if ($estimatedCost <= 0) {
            $errors[] = 'Estimasi biaya perbaikan kerusakan wajib lebih dari Rp 0.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors], 422);
                return;
            }
            Session::setFlash('danger', implode('<br>', $errors));
            $this->redirect('index.php?page=claims&action=edit&id=' . $id);
            return;
        }

        // Update claim core record
        $updateData = [
            'policy_id'             => $policyId,
            'incident_date'         => $incidentDate,
            'incident_time'         => $incidentTime,
            'incident_location'     => $incidentLocation,
            'chronology'            => $chronology,
            'estimated_damage_cost' => $estimatedCost
        ];

        $this->claimModel->updateClaim($id, $updateData);

        // Delete requested files
        if (is_array($deleteFileIds)) {
            foreach ($deleteFileIds as $fId) {
                $this->claimModel->deleteClaimFile((int)$fId, $id);
            }
        }

        // Associate files uploaded via drag & drop in wizard step 2
        if (is_string($fileIds)) {
            $fileIds = json_decode($fileIds, true);
        }
        if (is_array($fileIds)) {
            $pending = Session::get('pending_claim_files', []);
            foreach ($fileIds as $token) {
                if (is_string($token) && isset($pending[$token])) {
                    $this->claimModel->addClaimFile($id, $pending[$token]);
                    unset($pending[$token]);
                }
            }
            Session::set('pending_claim_files', $pending);
        }

        // Process new multi-file direct upload
        if (!empty($_FILES['damage_photos']['name'][0])) {
            $this->handleDirectUploads($id, $_FILES['damage_photos']);
        }

        // Audit Log
        $detail = "Pembaruan data klaim No. {$claim['claim_number']} (Polis {$policy['policy_number']})";
        $this->auditLogger->log('Pengajuan Klaim', 'Ubah Data', $detail, 'Berhasil');

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "Pengajuan klaim {$claim['claim_number']} berhasil diperbarui.",
                'claim_id' => $id
            ]);
            return;
        }

        Session::setFlash('success', "Data pengajuan klaim {$claim['claim_number']} berhasil diperbarui.");
        $this->redirect('index.php?page=claims&action=detail&id=' . $id);
    }

    /**
     * Cancel an eligible claim
     */
    public function cancel(): void
    {
        $currentUser = $this->requireAuth();
        $this->validateCsrf();

        $id = (int)$this->input('id', 0);
        $reason = trim($this->input('cancellation_reason', ''));

        $claim = $this->claimModel->getByIdWithDetails($id);
        if (!$claim) {
            Session::setFlash('danger', 'Klaim tidak ditemukan.');
            $this->redirect('index.php?page=claims');
            return;
        }

        // Ownership authorization check
        if ($currentUser['role'] === 'customer' && (int)$claim['submitted_by'] !== (int)$currentUser['id']) {
            Session::setFlash('danger', 'Akses ditolak. Anda hanya dapat membatalkan klaim milik Anda sendiri.');
            $this->redirect('index.php?page=claims');
            return;
        }

        // Check if cancellable
        if (!$this->claimModel->isCancellable($claim)) {
            Session::setFlash('danger', "Klaim No. {$claim['claim_number']} berstatus '{$claim['status']}' dan sudah tidak dapat dibatalkan.");
            $this->redirect('index.php?page=claims');
            return;
        }

        if (empty($reason)) {
            $reason = 'Dibatalkan oleh pemohon klaim.';
        }

        $this->claimModel->cancelClaim($id, (int)$currentUser['id'], $reason);

        // Security Audit Log
        $detail = "Klaim No. {$claim['claim_number']} dibatalkan oleh {$currentUser['name']}. Alasan: {$reason}";
        $this->auditLogger->log('Pengajuan Klaim', 'Batalkan Klaim', $detail, 'Berhasil');

        // Notification
        try {
            $notifModel = new \Models\NotificationModel();
            $notifModel->createNotification([
                'user_id'  => (int)$claim['submitted_by'],
                'title'    => 'Pengajuan Klaim Dibatalkan',
                'message'  => "Klaim {$claim['claim_number']} telah berhasil dibatalkan. Alasan: {$reason}",
                'category' => 'Pengajuan Klaim',
                'link'     => 'index.php?page=claims&action=detail&id=' . $id
            ]);
        } catch (\Throwable $e) {}

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "Pengajuan klaim {$claim['claim_number']} berhasil dibatalkan."
            ]);
            return;
        }

        Session::setFlash('success', "Pengajuan klaim {$claim['claim_number']} berhasil dibatalkan.");
        $this->redirect('index.php?page=claims');
    }
}
