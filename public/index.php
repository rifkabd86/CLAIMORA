<?php
/**
 * CLAIMORA - Insurance Claim Management System
 * Front Controller & Application Bootstrap
 */

// Error reporting for robust development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// All timestamps are stored and displayed as WIB
date_default_timezone_set('Asia/Jakarta');

// Register PSR-4 compatible autoloader for Core, Models, Controllers, Database
spl_autoload_register(function ($class) {
    $prefixMap = [
        'Core\\'        => dirname(__DIR__) . '/core/',
        'Models\\'      => dirname(__DIR__) . '/models/',
        'Controllers\\' => dirname(__DIR__) . '/controllers/',
        'Database\\'    => dirname(__DIR__) . '/database/',
    ];

    foreach ($prefixMap as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Load Security helper early
require_once dirname(__DIR__) . '/core/Security.php';
require_once dirname(__DIR__) . '/core/helpers.php';

// ClaimProcessor.php declares several classes, so the PSR-4 autoloader cannot find them by name
require_once dirname(__DIR__) . '/core/ClaimProcessor.php';

// Initialize Secure Session
\Core\Session::start();

// Dispatch route
try {
    $router = new \Core\Router();
    $router->dispatch();
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>Terjadi Kesalahan Sistem CLAIMORA</h1>";
    echo "<p>Pesan: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . "</pre>";
}
