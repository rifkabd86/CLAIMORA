<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\AuditLogger;
use Models\UserModel;

/**
 * Class AuthController
 * Handles user authentication, security session regeneration, and logout
 */
class AuthController extends Controller
{
    private UserModel $userModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->auditLogger = new AuditLogger();
    }

    /**
     * Display Login page or authenticate credentials
     */
    public function login(): void
    {
        Session::start();
        if (Session::has('user')) {
            $this->redirect('index.php?page=dashboard');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->validateCsrf();

            $identifier = $this->input('identifier', '');
            $password   = $this->input('password', '');
            $remember   = !empty($this->input('remember'));

            if (empty($identifier) || empty($password)) {
                Session::setFlash('danger', 'Email/Username dan Kata Sandi wajib diisi.');
                $this->render('auth/login', [
                    'identifier' => $identifier,
                    'title'      => 'Masuk ke Akun Anda - CLAIMORA'
                ], null);
                return;
            }

            $user = $this->userModel->findByEmailOrUsername($identifier);

            if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                // Mitigate session fixation by regenerating session ID
                Session::regenerate();

                // Save sanitized user state in session
                unset($user['password']);
                Session::set('user', $user);

                // Record security audit trail
                $this->auditLogger->log(
                    'Autentikasi',
                    'Login',
                    "Pengguna {$user['name']} ({$user['username']}) berhasil masuk ke sistem.",
                    'Berhasil'
                );

                Session::setFlash('success', "Selamat datang kembali, {$user['name']}!");
                $this->redirect('index.php?page=dashboard');
            } else {
                // Record failed login audit log
                $this->auditLogger->log(
                    'Autentikasi',
                    'Login Gagal',
                    "Percobaan masuk gagal untuk identitas: {$identifier}",
                    'Gagal'
                );

                Session::setFlash('danger', 'Kombinasi email/username atau kata sandi tidak cocok.');
                $this->render('auth/login', [
                    'identifier' => $identifier,
                    'title'      => 'Masuk ke Akun Anda - CLAIMORA'
                ], null);
                return;
            }
        }

        // GET request - render clean login view (no main layout)
        $this->render('auth/login', [
            'identifier' => '',
            'title'      => 'Masuk ke Akun Anda - CLAIMORA'
        ], null);
    }

    /**
     * Logout and destroy session
     */
    public function logout(): void
    {
        Session::start();
        $user = Session::get('user');
        if ($user) {
            $this->auditLogger->log(
                'Autentikasi',
                'Logout',
                "Pengguna {$user['name']} ({$user['username']}) keluar dari sistem.",
                'Berhasil'
            );
        }

        Session::destroy();
        Session::start();
        Session::setFlash('info', 'Anda telah berhasil keluar dari sistem CLAIMORA.');
        $this->redirect('index.php?page=login');
    }
}
