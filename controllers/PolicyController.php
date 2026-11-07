<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\Security;
use Core\AuditLogger;
use Models\PolicyModel;

/**
 * Class PolicyController
 * CRUD Data Polis Asuransi Nasabah & Batas Pertanggungan
 */
class PolicyController extends Controller
{
    private PolicyModel $policyModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->policyModel = new PolicyModel();
        $this->auditLogger = new AuditLogger();
    }

    /**
     * List policies with live search & status filtering
     * Semua role yang sudah login bisa melihat daftar polis
     */
    public function index(): void
    {
        $currentUser = $this->requireAuth();

        $query  = $this->input('q', '');
        $status = $this->input('status', '');

        // If logged-in user is a customer, only show their own policies
        if ($currentUser['role'] === 'customer') {
            $policies = $this->policyModel->getByCustomer($currentUser['email'] ?? '', $currentUser['name'] ?? '', $query, $status);
        } else {
            $policies = $this->policyModel->search($query, $status);
        }

        // Ambil jumlah klaim untuk setiap polis (untuk perlindungan integritas relasi & info UI)
        $policyIds = array_column($policies, 'id');
        if (!empty($policyIds)) {
            $placeholders = implode(',', array_fill(0, count($policyIds), '?'));
            $db = \Core\Database::getInstance()->getConnection();
            $claimStmt = $db->prepare("SELECT policy_id, COUNT(*) as cnt FROM claims WHERE policy_id IN ({$placeholders}) GROUP BY policy_id");
            $claimStmt->execute(array_values(array_map('intval', $policyIds)));
            $counts = [];
            while ($row = $claimStmt->fetch(\PDO::FETCH_ASSOC)) {
                $counts[(int)$row['policy_id']] = (int)$row['cnt'];
            }
            foreach ($policies as &$pol) {
                $pol['claims_count'] = $counts[(int)$pol['id']] ?? 0;
            }
            unset($pol);
        }

        // If AJAX request, return partial JSON or HTML
        if ($this->isAjax() && $this->input('format') === 'json') {
            $this->json(['success' => true, 'data' => $policies]);
        }

        $this->render('policies/index', [
            'title'        => 'Data Polis - CLAIMORA',
            'user'         => $currentUser,
            'policies'     => $policies,
            'query'        => $query,
            'status'       => $status,
            'activeNav'    => 'policies'
        ]);
    }

    /**
     * Get single policy details (used for Detail Polis Side Card / Modal)
     * Admin: seluruh polis | Nasabah (customer): hanya polis miliknya
     */
    public function detail(): void
    {
        $currentUser = $this->requireAuth();
        $id = (int)$this->input('id', 0);
        $policy = $this->policyModel->findById($id);

        if (!$policy) {
            $this->json(['success' => false, 'message' => 'Data polis tidak ditemukan.'], 404);
        }

        if ($currentUser['role'] === 'customer'
            && !$this->policyModel->isOwnedBy($policy, $currentUser['email'] ?? '', $currentUser['name'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Anda hanya dapat melihat polis milik Anda sendiri.'], 403);
        }

        // Calculate protection status
        $today = date('Y-m-d');
        $isValid = ($policy['status'] === 'Aktif') && ($today >= $policy['start_date']) && ($today <= $policy['end_date']);
        $policy['is_protection_valid'] = $isValid;
        $policy['formatted_coverage']  = 'Rp ' . number_format($policy['coverage_amount'], 0, ',', '.');
        $policy['formatted_period']    = date('d/m/Y', strtotime($policy['start_date'])) . ' – ' . date('d/m/Y', strtotime($policy['end_date']));

        $this->json(['success' => true, 'data' => $policy]);
    }

    /**
     * Create or Update policy record
     * Admin dan Nasabah (Customer) dapat mendaftarkan/menyimpan data polis baru dan kendaraan baru
     */
    public function store(): void
    {
        $user = $this->requireRole(['admin', 'customer']);
        $this->validateCsrf();

        $id              = (int)$this->input('id', 0);
        $policyNumber    = strtoupper(trim($this->input('policy_number', '')));
        $customerName    = trim($this->input('customer_name', ''));
        $nik             = trim($this->input('nik', ''));
        $phone           = trim($this->input('phone', ''));
        $email           = trim($this->input('email', ''));
        $insuranceType   = trim($this->input('insurance_type', 'Kendaraan'));
        $rawCoverage     = str_replace(['Rp', '.', ' ', ','], '', $this->input('coverage_amount', '0'));
        $coverageAmount  = (float)$rawCoverage;
        $startDate       = trim($this->input('start_date', ''));
        $endDate         = trim($this->input('end_date', ''));
        $vehiclePlate    = strtoupper(trim($this->input('vehicle_plate', '')));
        $vehicleBrand    = trim($this->input('vehicle_brand', ''));
        $vehicleModel    = trim($this->input('vehicle_model', ''));
        $status          = trim($this->input('status', 'Aktif'));
        $notes           = trim($this->input('notes', ''));

        // Jika diajukan oleh Nasabah (customer), pastikan data tersinkron dengan akun nasabah
        if ($user['role'] === 'customer') {
            if (empty($customerName)) {
                $customerName = $user['name'] ?? '';
            }
            if (empty($email)) {
                $email = $user['email'] ?? '';
            }
            if (empty($status) || !in_array($status, ['Aktif', 'Berakhir', 'Ditangguhkan'], true)) {
                $status = 'Aktif';
            }
        }

        // Ownership guard jika mengedit (update) polis yang sudah ada
        if ($id > 0 && $user['role'] === 'customer') {
            $existing = $this->policyModel->findById($id);
            if (!$existing || !$this->policyModel->isOwnedBy($existing, $user['email'] ?? '', $user['name'] ?? '')) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Anda hanya berhak mengedit data polis milik akun Anda sendiri.'], 403);
                    return;
                }
                Session::setFlash('danger', 'Anda hanya berhak mengedit data polis milik akun Anda sendiri.');
                $this->redirect('index.php?page=policies');
                return;
            }
        }

        // Validation rules
        $errors = [];

        // 1. Policy Number JS-format validation: regex /^JS\d{8}$/
        if (!Security::validatePolicyNumber($policyNumber)) {
            $errors[] = 'Nomor Polis wajib diawali "JS" diikuti 8 digit angka (contoh: JS12345678).';
        }

        if ($id > 0 && !$this->policyModel->findById($id)) {
            $errors[] = 'Data polis yang akan diubah tidak ditemukan.';
        }

        // 2. Uniqueness check
        if ($this->policyModel->isPolicyNumberExists($policyNumber, $id > 0 ? $id : null)) {
            $errors[] = "Nomor Polis {$policyNumber} sudah terdaftar dalam sistem.";
        }

        // 3. Mandatory fields
        if (empty($customerName)) {
            $errors[] = 'Nama nasabah wajib diisi.';
        }
        if (!preg_match('/^\d{16}$/', $nik)) {
            $errors[] = 'NIK wajib 16 digit angka.';
        }
        if (empty($phone)) {
            $errors[] = 'Nomor telepon wajib diisi.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email nasabah tidak valid.';
        }
        if ($coverageAmount <= 0) {
            $errors[] = 'Batas pertanggungan harus lebih besar dari Rp 0.';
        }
        if (empty($startDate) || empty($endDate)) {
            $errors[] = 'Masa berlaku awal dan akhir proteksi wajib diisi.';
        } elseif (!Security::isValidDate($startDate) || !Security::isValidDate($endDate)) {
            $errors[] = 'Format tanggal masa berlaku tidak valid.';
        } elseif ($endDate <= $startDate) {
            $errors[] = 'Masa berlaku akhir harus setelah masa berlaku awal.';
        }
        if (empty($vehiclePlate)) {
            $errors[] = 'Plat nomor kendaraan wajib diisi.';
        }
        if (!in_array($status, ['Aktif', 'Berakhir', 'Ditangguhkan'], true)) {
            $errors[] = 'Status polis tidak valid.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors], 422);
            }
            Session::setFlash('danger', implode('<br>', $errors));
            $this->redirect('index.php?page=policies');
            return;
        }

        $data = [
            'policy_number'   => $policyNumber,
            'customer_name'   => $customerName,
            'nik'             => $nik,
            'phone'           => $phone,
            'email'           => $email,
            'insurance_type'  => $insuranceType,
            'coverage_amount' => $coverageAmount,
            'start_date'      => $startDate,
            'end_date'        => $endDate,
            'vehicle_plate'   => $vehiclePlate,
            'vehicle_brand'   => $vehicleBrand,
            'vehicle_model'   => $vehicleModel,
            'status'          => $status,
            'notes'           => $notes,
            'updated_at'      => date('Y-m-d H:i:s')
        ];

        if ($id > 0) {
            // Update
            $this->policyModel->update($id, $data);
            $activity = 'Ubah Data';
            $detail = "Polis {$policyNumber} (Kendaraan: {$vehicleBrand} {$vehicleModel} [{$vehiclePlate}])";
            $msg = "Data Polis {$policyNumber} berhasil diperbarui.";
        } else {
            // Create
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->policyModel->create($data);
            $activity = 'Tambah Data';
            $byText = ($user['role'] === 'customer') ? 'Pendaftaran Mandiri Nasabah' : 'Admin';
            $detail = "[{$byText}] Polis baru {$policyNumber} atas nama {$customerName} (Kendaraan: {$vehicleBrand} {$vehicleModel} [{$vehiclePlate}], Batas: Rp " . number_format($coverageAmount, 0, ',', '.') . ")";
            $msg = "Polis dan kendaraan baru ({$policyNumber} - {$vehiclePlate}) berhasil didaftarkan dan disimpan ke database.";
        }

        // Security Audit Trail
        $this->auditLogger->log('Data Polis', $activity, $detail, 'Berhasil');

        // Kirim Notifikasi Resmi kepada Nasabah
        try {
            $notifModel = new \Models\NotificationModel();
            $targetUserId = (int)$user['id'];
            $notifTitle = ($id > 0) ? "Pembaruan Data Polis {$policyNumber}" : "Pendaftaran Polis & Kendaraan Berhasil";
            $notifMsg = ($id > 0)
                ? "Data polis {$policyNumber} untuk unit {$vehicleBrand} {$vehicleModel} ({$vehiclePlate}) telah berhasil diperbarui."
                : "Polis baru {$policyNumber} atas unit {$vehicleBrand} {$vehicleModel} ({$vehiclePlate}) berhasil didaftarkan dengan plafon Rp " . number_format($coverageAmount, 0, ',', '.') . ".";
            $notifModel->createNotification($targetUserId, $notifTitle, $notifMsg, 'Informasi Polis', 'index.php?page=policies');
        } catch (\Throwable $e) {}

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => $msg, 'policy_id' => $id]);
        }

        Session::setFlash('success', $msg);
        $this->redirect('index.php?page=policies');
    }

    /**
     * Delete or Deactivate policy
     * Admin dan Nasabah (Customer) berhak menghapus/menonaktifkan polis miliknya
     * Menerapkan integritas relasional: Jika terdapat riwayat klaim, dilakukan penonaktifan (soft delete)
     */
    public function delete(): void
    {
        $user = $this->requireRole(['admin', 'customer']);
        $this->validateCsrf();

        $id = (int)$this->input('id', 0);
        $policy = $this->policyModel->findById($id);

        if (!$policy) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Polis tidak ditemukan.'], 404);
                return;
            }
            Session::setFlash('danger', 'Polis tidak ditemukan.');
            $this->redirect('index.php?page=policies');
            return;
        }

        // Ownership guard: Nasabah hanya boleh menghapus polis miliknya sendiri
        if ($user['role'] === 'customer' && !$this->policyModel->isOwnedBy($policy, $user['email'] ?? '', $user['name'] ?? '')) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk menghapus data polis milik akun lain.'], 403);
                return;
            }
            Session::setFlash('danger', 'Anda tidak memiliki hak akses untuk menghapus data polis milik akun lain.');
            $this->redirect('index.php?page=policies');
            return;
        }

        $policyNumber = $policy['policy_number'];
        $claimsCount = $this->policyModel->getClaimsCount($id);

        if ($claimsCount > 0) {
            // Integritas Relasional: Cegah hard delete jika ada relasi klaim
            $this->policyModel->deactivate($id);
            $activity = 'Nonaktifkan Polis';
            $byText = ($user['role'] === 'customer') ? 'Nasabah' : 'Admin';
            $detail = "[{$byText}] Polis {$policyNumber} dinonaktifkan (Status: Ditangguhkan) karena memiliki {$claimsCount} riwayat pengajuan klaim.";
            $msg = "Polis {$policyNumber} memiliki {$claimsCount} riwayat klaim. Demi integritas data dan bukti klaim, data tidak dihapus permanen melainkan status proteksinya telah dinonaktifkan (Ditangguhkan).";
        } else {
            // Hard delete aman jika tidak ada relasi klaim
            $this->policyModel->delete($id);
            $activity = 'Hapus Data';
            $byText = ($user['role'] === 'customer') ? 'Nasabah' : 'Admin';
            $detail = "[{$byText}] Polis {$policyNumber} dihapus permanen (tanpa riwayat klaim).";
            $msg = "Polis {$policyNumber} berhasil dihapus dari sistem.";
        }

        // Security Audit Log
        $this->auditLogger->log('Data Polis', $activity, $detail, 'Berhasil');

        // Buat notifikasi resmi untuk nasabah
        try {
            $notifModel = new \Models\NotificationModel();
            $notifModel->createNotification(
                (int)$user['id'],
                $claimsCount > 0 ? "Polis {$policyNumber} Dinonaktifkan" : "Polis {$policyNumber} Dihapus",
                $msg,
                'Informasi Polis',
                'index.php?page=policies'
            );
        } catch (\Throwable $e) {}

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => $msg]);
            return;
        }

        Session::setFlash('success', $msg);
        $this->redirect('index.php?page=policies');
    }
}
