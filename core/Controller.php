<?php
namespace Core;

/**
 * Class Controller
 * Base Controller providing layout rendering, JSON responses,
 * authentication gating, and CSRF protection.
 */
abstract class Controller
{
    /**
     * Render a view file within a parent layout
     */
    protected function render(string $viewPath, array $data = [], ?string $layout = 'layouts/main'): void
    {
        // Extract data variables to view scope
        extract($data);

        // Capture view content
        ob_start();
        $viewFile = dirname(__DIR__) . '/views/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$viewFile}");
        }
        require $viewFile;
        $content = ob_get_clean();

        // If no layout requested, output view directly
        if (!$layout) {
            echo $content;
            return;
        }

        // Render layout containing $content
        $layoutFile = dirname(__DIR__) . '/views/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout file not found: {$layoutFile}");
        }
        require $layoutFile;
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Require authenticated session; redirects to login if unauthenticated
     */
    protected function requireAuth(): array
    {
        Session::start();
        $user = Session::get('user');
        if (empty($user)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'], 401);
            }
            Session::setFlash('warning', 'Silakan masuk ke akun Anda untuk mengakses halaman ini.');
            $this->redirect('index.php?page=login');
        }
        return $user;
    }

    /**
     * Require specific user role(s)
     */
    protected function requireRole(array $allowedRoles): array
    {
        $user = $this->requireAuth();
        if (!in_array($user['role'], $allowedRoles, true)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk tindakan ini.'], 403);
            }
            Session::setFlash('danger', 'Akses ditolak. Anda tidak memiliki izin untuk tindakan tersebut.');
            $this->redirect('index.php?page=dashboard');
        }
        return $user;
    }

    /**
     * Verify CSRF token. Every caller is a state-changing action, so a non-POST
     * request (which carries no token) is rejected rather than skipped.
     */
    protected function validateCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrfToken()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Token keamanan (CSRF) tidak valid atau telah kedaluwarsa.'], 403);
            }
            Session::setFlash('danger', 'Validasi keamanan gagal. Silakan coba kirim ulang formulir.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard');
        }
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Helper to get sanitized request input
     */
    protected function input(string $key, $default = null)
    {
        if (isset($_POST[$key])) {
            return is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key];
        }
        if (isset($_GET[$key])) {
            return is_string($_GET[$key]) ? trim($_GET[$key]) : $_GET[$key];
        }
        return $default;
    }
}
