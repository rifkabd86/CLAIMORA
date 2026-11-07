<?php
namespace Core;

/**
 * Class Session
 * Encapsulates secure session handling with automatic ID regeneration,
 * session hijacking mitigation, and flash message management.
 */
class Session
{
    private static bool $started = false;

    /**
     * Start secure session with strict cookie parameters
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        // Configure session cookie params before starting
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        session_set_cookie_params([
            'lifetime' => 86400, // 24 hours
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
        self::$started = true;

        // Session expiration check (2 hours idle timeout)
        $timeout = 7200;
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
            self::destroy();
            session_start();
            self::setFlash('warning', 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.');
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    /**
     * Regenerate session ID to mitigate session fixation attacks
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Set one-time flash message
     */
    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION['_flash'][$type] = $message;
    }

    /**
     * Get and clear flash message
     */
    public static function getFlash(string $type): ?string
    {
        self::start();
        if (isset($_SESSION['_flash'][$type])) {
            $msg = $_SESSION['_flash'][$type];
            unset($_SESSION['_flash'][$type]);
            return $msg;
        }
        return null;
    }

    /**
     * Get all active flash messages and clear them
     */
    public static function getAllFlashes(): array
    {
        self::start();
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    /**
     * Destroy session on logout
     */
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
            // Drop the old ID so the next start() issues a new cookie instead of reusing the expired one
            unset($_COOKIE[session_name()]);
            self::$started = false;
        }
    }
}
