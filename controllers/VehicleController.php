<?php
namespace Controllers;

use Core\Controller;
use Core\Database;
use Models\PolicyModel;
use PDO;

/**
 * Class VehicleController
 * Dedicated Controller untuk Halaman Kendaraan Saya (Kendaraan Tertanggung & Proteksi)
 */
class VehicleController extends Controller
{
    private PolicyModel $policyModel;
    private PDO $db;

    public function __construct()
    {
        $this->policyModel = new PolicyModel();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Tampilan Halaman Kendaraan Saya
     */
    public function index(): void
    {
        $currentUser = $this->requireAuth();
        $query  = trim($this->input('q', ''));
        $status = trim($this->input('status', ''));

        // Ambil data kendaraan (dari tabel policies)
        if ($currentUser['role'] === 'customer') {
            $policies = $this->policyModel->getByCustomer(
                $currentUser['email'] ?? '',
                $currentUser['name'] ?? '',
                $query,
                $status
            );
        } else {
            $policies = $this->policyModel->search($query, $status);
        }

        // Ambil total klaim per policy untuk informasi riwayat klaim kendaraan
        $policyIds = array_column($policies, 'id');
        $claimCounts = [];
        if (!empty($policyIds)) {
            $placeholders = implode(',', array_fill(0, count($policyIds), '?'));
            $claimStmt = $this->db->prepare("
                SELECT policy_id, COUNT(*) as total_claims,
                       SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as approved_claims,
                       MAX(created_at) as last_claim_date
                FROM claims 
                WHERE policy_id IN ({$placeholders})
                GROUP BY policy_id
            ");
            $claimStmt->execute(array_values(array_map('intval', $policyIds)));
            while ($row = $claimStmt->fetch(PDO::FETCH_ASSOC)) {
                $claimCounts[(int)$row['policy_id']] = $row;
            }
        }

        // Hitung statistik ringkasan kendaraan
        $today = date('Y-m-d');
        $totalActive = 0;
        $totalExpired = 0;
        $totalCoverage = 0.0;

        $enrichedVehicles = [];
        foreach ($policies as $pol) {
            $isValid = ($pol['status'] === 'Aktif') && ($today >= $pol['start_date']) && ($today <= $pol['end_date']);
            if ($isValid) {
                $totalActive++;
            } else {
                $totalExpired++;
            }
            $totalCoverage += (float)($pol['coverage_amount'] ?? 0);

            // Hitung sisa hari
            $endTimestamp = strtotime($pol['end_date']);
            $currentTimestamp = strtotime($today);
            $diffDays = (int)ceil(($endTimestamp - $currentTimestamp) / 86400);

            $pId = (int)$pol['id'];
            $claimInfo = $claimCounts[$pId] ?? [
                'total_claims'    => 0,
                'approved_claims' => 0,
                'last_claim_date' => null
            ];

            $pol['is_valid']           = $isValid;
            $pol['days_left']          = $diffDays;
            $pol['total_claims']       = (int)$claimInfo['total_claims'];
            $pol['approved_claims']    = (int)$claimInfo['approved_claims'];
            $pol['last_claim_date']    = $claimInfo['last_claim_date'];

            $enrichedVehicles[] = $pol;
        }

        if ($this->isAjax() && $this->input('format') === 'json') {
            $this->json(['success' => true, 'data' => $enrichedVehicles]);
            return;
        }

        $this->render('vehicles/index', [
            'title'            => 'Kendaraan Saya - CLAIMORA',
            'user'             => $currentUser,
            'vehicles'         => $enrichedVehicles,
            'query'            => $query,
            'status'           => $status,
            'totalVehicles'    => count($enrichedVehicles),
            'totalActive'      => $totalActive,
            'totalExpired'     => $totalExpired,
            'totalCoverage'    => $totalCoverage,
            'activeNav'        => 'vehicles'
        ]);
    }

    /**
     * Delete or Deactivate vehicle
     * Menerapkan integritas relasional: Jika terdapat riwayat klaim, dilakukan penonaktifan (soft delete)
     */
    public function delete(): void
    {
        $currentUser = $this->requireRole(['admin', 'customer']);
        $this->validateCsrf();

        $id = (int)$this->input('id', 0);
        $policy = $this->policyModel->findById($id);

        if (!$policy) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Data kendaraan tidak ditemukan.'], 404);
                return;
            }
            \Core\Session::setFlash('danger', 'Data kendaraan tidak ditemukan.');
            $this->redirect('index.php?page=vehicles');
            return;
        }

        // Ownership guard
        if ($currentUser['role'] === 'customer' && !$this->policyModel->isOwnedBy($policy, $currentUser['email'] ?? '', $currentUser['name'] ?? '')) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk menghapus kendaraan ini.'], 403);
                return;
            }
            \Core\Session::setFlash('danger', 'Anda tidak memiliki hak akses untuk menghapus kendaraan ini.');
            $this->redirect('index.php?page=vehicles');
            return;
        }

        $vehiclePlate = $policy['vehicle_plate'];
        $vehicleBrand = $policy['vehicle_brand'] . ' ' . ($policy['vehicle_model'] ?? '');
        $claimsCount = $this->policyModel->getClaimsCount($id);

        $auditLogger = new \Core\AuditLogger();

        if ($claimsCount > 0) {
            // Relational integrity: deactive policy to preserve claim history
            $this->policyModel->deactivate($id);
            $activity = 'Nonaktifkan Kendaraan';
            $byText = ($currentUser['role'] === 'customer') ? 'Nasabah' : 'Admin';
            $detail = "[{$byText}] Status kendaraan {$vehicleBrand} ({$vehiclePlate}) dinonaktifkan (Ditangguhkan) karena memiliki {$claimsCount} riwayat pengajuan klaim.";
            $msg = "Kendaraan {$vehicleBrand} ({$vehiclePlate}) memiliki {$claimsCount} riwayat klaim. Demi integritas data riwayat klaim Anda, data kendaraan tidak dihapus permanen melainkan status proteksinya telah dinonaktifkan (Ditangguhkan).";
        } else {
            // Permanent delete if no claims exist
            $this->policyModel->delete($id);
            $activity = 'Hapus Data Kendaraan';
            $byText = ($currentUser['role'] === 'customer') ? 'Nasabah' : 'Admin';
            $detail = "[{$byText}] Data kendaraan {$vehicleBrand} ({$vehiclePlate}) dihapus permanen dari sistem.";
            $msg = "Data kendaraan {$vehicleBrand} ({$vehiclePlate}) berhasil dihapus.";
        }

        // Audit Trail
        $auditLogger->log('Data Kendaraan', $activity, $detail, 'Berhasil');

        // Notifikasi ke pengguna
        try {
            $notifModel = new \Models\NotificationModel();
            $notifModel->createNotification(
                (int)$currentUser['id'],
                $claimsCount > 0 ? "Kendaraan {$vehiclePlate} Dinonaktifkan" : "Kendaraan {$vehiclePlate} Dihapus",
                $msg,
                'Informasi Kendaraan',
                'index.php?page=vehicles'
            );
        } catch (\Throwable $e) {}

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => $msg]);
            return;
        }

        \Core\Session::setFlash('success', $msg);
        $this->redirect('index.php?page=vehicles');
    }
}

