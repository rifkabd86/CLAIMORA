<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\AuditLogger;
use Models\UserModel;
use Models\PolicyModel;
use Models\NotificationModel;

class SettingsController extends Controller
{
    private UserModel $userModel;
    private PolicyModel $policyModel;
    private NotificationModel $notificationModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->policyModel = new PolicyModel();
        $this->notificationModel = new NotificationModel();
        $this->auditLogger = new AuditLogger();
    }

    public function index(): void
    {
        $currentUser = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $name = trim($this->input('name', ''));
            $email = strtolower(trim($this->input('email', '')));
            $notifyEmail = !empty($this->input('notify_email'));
            $errors = [];
            // Handle password change if fields are present
            $oldPassword = $this->input('old_password', '');
            $newPassword = $this->input('new_password', '');
            $confirmPassword = $this->input('confirm_password', '');
            $passwordChangeRequested = $oldPassword !== '' || $newPassword !== '' || $confirmPassword !== '';



        if ($passwordChangeRequested) {
            // Load full user record to get stored password hash
            $userRecord = $this->userModel->findById((int)$currentUser['id']);
            $storedHash = $userRecord['password'] ?? '';

            if (!password_verify($oldPassword, $storedHash)) {
                $errors[] = 'Password lama tidak sesuai.';
            }
            if (strlen($newPassword) < 8) {
                $errors[] = 'Password baru minimal 8 karakter.';
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Konfirmasi password baru tidak cocok.';
            }
        }

            if ($name === '') {
                $errors[] = 'Nama lengkap wajib diisi.';
            } elseif (strlen($name) < 3) {
                $errors[] = 'Nama lengkap minimal 3 karakter.';
            }

            if ($email === '') {
                $errors[] = 'Alamat email wajib diisi.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format alamat email tidak valid.';
            } elseif ($this->userModel->isEmailTaken($email, (int)$currentUser['id'])) {
                $errors[] = 'Alamat email sudah digunakan oleh akun lain.';
            }

            if (!empty($errors)) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => implode('<br>', $errors), 'errors' => $errors], 422);
                    return;
                }
                Session::setFlash('danger', implode('<br>', $errors));
                $this->redirect('index.php?page=settings');
                return;
            }

            $oldName = $currentUser['name'];
            $oldEmail = $currentUser['email'];

            // Prepare data for update
            $updateData = [
                'name'       => $name,
                'email'      => $email,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // If password change requested and no validation errors, hash new password and include
            if ($passwordChangeRequested && empty($errors)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateData['password'] = $hashedPassword;
            }

            $this->userModel->update((int)$currentUser['id'], $updateData);

            // Jika nasabah mengubah nama atau email, sinkronkan data polis yang dimilikinya
            if (($currentUser['role'] ?? '') === 'customer') {
                $this->policyModel->updateCustomerInfo($oldEmail, $oldName, $email, $name);
            }

            // Sinkronkan data session secara konsisten di seluruh aplikasi
            $currentUser['name'] = $name;
            $currentUser['email'] = $email;
            Session::set('user', $currentUser);

            // Jejak Audit Log Keamanan
            $roleLabel = e($currentUser['role_label'] ?? ucfirst($currentUser['role']));
            $this->auditLogger->log(
                'Pengaturan',
                'Ubah Data',
                "Pembaruan profil {$roleLabel}: Nama '{$oldName}' -> '{$name}', Email '{$oldEmail}' -> '{$email}'",
                'Berhasil'
            );

            // Buat notifikasi sistem untuk pengguna
            $this->notificationModel->createNotification([
                'user_id'  => (int)$currentUser['id'],
                'title'    => 'Pembaruan Profil Berhasil',
                'message'  => "Informasi profil Anda berhasil diperbarui menjadi Nama: {$name}, Email: {$email}.",
                'category' => 'Informasi Sistem',
                'link'     => 'index.php?page=settings'
            ]);

            $successMsg = 'Informasi profil Anda berhasil diperbarui.';

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => $successMsg,
                    'user'    => [
                        'name'       => $name,
                        'email'      => $email,
                        'role_label' => $currentUser['role_label'] ?? ''
                    ]
                ]);
                return;
            }

            Session::setFlash('success', $successMsg);
            $this->redirect('index.php?page=settings');
            return;
        }

        $isCustomer = ($currentUser['role'] ?? '') === 'customer';
        $this->render('settings/index', [
            'title'     => $isCustomer ? 'Profil Saya - CLAIMORA' : 'Pengaturan Akun & Sistem - CLAIMORA',
            'user'      => $currentUser,
            'activeNav' => 'settings'
        ]);
    }
}
