<?php
namespace Core;

use PDO;
use PDOException;
use Exception;

/**
 * Class Database
 * Encapsulates the PDO connection in Strict Mode (Singleton Pattern)
 * Provides Abstraction for Database interaction across MySQL and SQLite.
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private string $activeDriver = 'mysql';

    /**
     * Private constructor to enforce Singleton Pattern (Encapsulation)
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * Prevent cloning of singleton instance
     */
    private function __clone() {}

    /**
     * Prevent unserialize of singleton instance
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Get Singleton Instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish PDO connection with strict configuration
     */
    private function connect(): void
    {
        $config = require dirname(__DIR__) . '/config/database.php';
        $options = $config['options'];

        // Try preferred driver (MySQL by default)
        if ($config['driver'] === 'mysql') {
            try {
                $my = $config['mysql'];
                // Test connecting to server to ensure database exists
                $dsnWithoutDb = "mysql:host={$my['host']};port={$my['port']};charset={$my['charset']}";
                $rootPdo = new PDO($dsnWithoutDb, $my['username'], $my['password'], $options);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$my['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                unset($rootPdo);

                $dsn = "mysql:host={$my['host']};port={$my['port']};dbname={$my['dbname']};charset={$my['charset']}";
                $this->pdo = new PDO($dsn, $my['username'], $my['password'], $options);
                $this->activeDriver = 'mysql';
            } catch (PDOException $e) {
                // MySQL is offline or refused connection -> Fallback smoothly to SQLite
                error_log("CLAIMORA Database Notice: MySQL connection failed (" . $e->getMessage() . "). Falling back to SQLite.");
                $this->connectSqlite($config['sqlite']['path'], $options);
            }
        } else {
            $this->connectSqlite($config['sqlite']['path'], $options);
        }

        // Auto-run schema migrations and seeders if tables do not exist
        $this->ensureSchema();
    }

    /**
     * Fallback or primary connection to SQLite
     */
    private function connectSqlite(string $sqlitePath, array $options): void
    {
        $dir = dirname($sqlitePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $dsn = "sqlite:" . $sqlitePath;
        $this->pdo = new PDO($dsn, null, null, $options);
        $this->pdo->exec("PRAGMA foreign_keys = ON;");
        $this->activeDriver = 'sqlite';
    }

    /**
     * Get underlying PDO instance
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Get active database driver name
     */
    public function getActiveDriver(): string
    {
        return $this->activeDriver;
    }

    /**
     * Check if tables exist; if not, invoke Migrator
     */
    private function ensureSchema(): void
    {
        try {
            $test = $this->pdo->query("SELECT 1 FROM users LIMIT 1");
            if ($test === false) {
                \Database\Migrator::run($this->pdo, $this->activeDriver);
            } else {
                require_once dirname(__DIR__) . '/database/Migrator.php';
                \Database\Migrator::ensureCancellationSchema($this->pdo, $this->activeDriver);
            }
        } catch (\Throwable $e) {
            // Table doesn't exist yet, run migrator
            require_once dirname(__DIR__) . '/database/Migrator.php';
            \Database\Migrator::run($this->pdo, $this->activeDriver);
        }

        // Also ensure notifications table exists (prevent table doesn't exist errors)
        try {
            $this->pdo->query("SELECT 1 FROM notifications LIMIT 1");
        } catch (\Throwable $e) {
            require_once dirname(__DIR__) . '/database/Migrator.php';
            if ($this->activeDriver === 'sqlite') {
                $this->pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    message TEXT NOT NULL,
                    category TEXT NOT NULL DEFAULT 'Informasi Sistem',
                    link TEXT NULL,
                    is_read INTEGER NOT NULL DEFAULT 0,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );");
            } else {
                $this->pdo->exec("CREATE TABLE IF NOT EXISTS `notifications` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `title` VARCHAR(150) NOT NULL,
                    `message` TEXT NOT NULL,
                    `category` ENUM('Informasi Polis', 'Informasi Kendaraan', 'Pengajuan Klaim', 'Status Verifikasi', 'Persetujuan Klaim', 'Penolakan Klaim', 'Informasi Sistem') NOT NULL DEFAULT 'Informasi Sistem',
                    `link` VARCHAR(255) NULL,
                    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                    INDEX `idx_notif_user` (`user_id`),
                    INDEX `idx_notif_read` (`is_read`),
                    INDEX `idx_notif_category` (`category`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            }
            \Database\Migrator::seedNotifications($this->pdo);
        }
    }

    /**
     * Transaction helper methods
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
