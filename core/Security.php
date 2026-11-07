<?php
namespace Core;

/**
 * Class Security
 * Encapsulates application security helpers:
 * - Anti-XSS output sanitization
 * - CSRF token generation and strict verification
 * - File upload MIME & size validation
 * - Format validators (Policy No, NIK, etc.)
 */
class Security
{
    /**
     * Escape strings for HTML output (Anti-XSS)
     */
    public static function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate or retrieve CSRF token
     */
    public static function generateCsrfToken(): string
    {
        Session::start();
        $token = Session::get('csrf_token');
        if (empty($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }

    /**
     * Generate hidden HTML input for CSRF
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . self::escape($token) . '">';
    }

    /**
     * Verify CSRF token from POST or headers
     */
    public static function validateCsrfToken(?string $submittedToken = null): bool
    {
        Session::start();
        $sessionToken = Session::get('csrf_token');
        if (empty($sessionToken)) {
            return false;
        }

        if ($submittedToken === null) {
            $submittedToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        if (empty($submittedToken) || !is_string($submittedToken)) {
            return false;
        }

        return hash_equals($sessionToken, $submittedToken);
    }

    /**
     * Validate a date or time string against an exact format
     */
    public static function isValidDate(string $value, string $format = 'Y-m-d'): bool
    {
        $parsed = \DateTime::createFromFormat('!' . $format, $value);
        return $parsed !== false && $parsed->format($format) === $value;
    }

    /**
     * Validate Policy Number format (e.g. JS12345678)
     */
    public static function validatePolicyNumber(string $policyNumber): bool
    {
        return (bool)preg_match('/^JS\d{8}$/', trim($policyNumber));
    }

    /**
     * Validate Claim Number format (e.g. CLM-2025-1047)
     */
    public static function validateClaimNumber(string $claimNumber): bool
    {
        return (bool)preg_match('/^CLM-\d{4}-\d{4}$/', trim($claimNumber));
    }

    /**
     * Validate uploaded file by size and actual MIME type
     *
     * @param array $file $_FILES['name'] element
     * @param array $allowedMimes Array of permitted MIME types
     * @param int $maxBytes Max file size in bytes (e.g. 5MB = 5 * 1024 * 1024)
     * @return array [bool $isValid, string $errorMessage, string $mimeType]
     */
    public static function validateUpload(array $file, array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'], int $maxBytes = 5242880): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return [false, 'Parameter berkas tidak valid.', ''];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return [false, 'Tidak ada berkas yang diunggah.', ''];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return [false, 'Ukuran berkas melebihi batas upload server.', ''];
            default:
                return [false, 'Terjadi kesalahan saat mengunggah berkas.', ''];
        }

        // File size check
        if ($file['size'] > $maxBytes) {
            $maxMb = round($maxBytes / (1024 * 1024), 1);
            return [false, "Ukuran berkas melebihi batas maksimal {$maxMb}MB.", ''];
        }

        // Real MIME type check using finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowedMimes, true)) {
            return [false, "Tipe format berkas ({$mime}) tidak diizinkan. Hanya JPG, PNG, atau WEBP yang diperbolehkan.", $mime];
        }

        return [true, '', $mime];
    }

    /**
     * Get real client IP address
     */
    public static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP) ?: '127.0.0.1';
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $first = trim($ips[0]);
            return filter_var($first, FILTER_VALIDATE_IP) ?: '127.0.0.1';
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}


