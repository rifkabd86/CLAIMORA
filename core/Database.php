<?php
namespace Core;

use PDO;
use PDOException;
use Exception;
use RuntimeException;

/**
 * Class Database
 * PDO Database Connection
 * Singleton Pattern
 * Supports MySQL and SQLite
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private string $activeDriver = 'mysql';

    /**
     * Private constructor
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
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
     * Establish database connection
     */
    private function connect(): void
    {
        $config = require dirname(__DIR__) . '/config/database.php';

        $options = $config['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $driver = strtolower($config['driver'] ?? 'mysql');

        if ($driver === 'mysql') {
            $this->connectMysql($config['mysql'], $options);
        } elseif ($driver === 'sqlite') {
            // SQLite hanya digunakan jika memang dipilih
            // secara eksplisit melalui konfigurasi.
            $this->connectSqlite($config['sqlite']['path'], $options);
        } else {
            throw new RuntimeException(
                "Database driver tidak didukung: {$driver}"
            );
        }

        // Jalankan pemeriksaan dan migrasi skema
        $this->ensureSchema();
    }

    /**
     * Connect to MySQL
     *
     * Menggunakan database yang sudah disediakan Railway.
     * Tidak membuat database baru.
     */
    private function connectMysql(array $my, array $options): void
    {
        $required = ['host', 'port', 'dbname', 'username', 'password'];

        foreach ($required as $key) {
            if (!array_key_exists($key, $my) || $my[$key] === '') {
                throw new RuntimeException(
                    "Konfigurasi MySQL tidak lengkap: {$key}"
                );
            }
        }

        $host = $my['host'];
        $port = (int) $my['port'];
        $dbname = $my['dbname'];
        $charset = $my['charset'] ?? 'utf8mb4';

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbname)) {
            throw new RuntimeException(
                "Nama database MySQL tidak valid."
            );
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        try {
            // Langsung terhubung ke database Railway
            $this->pdo = new PDO(
                $dsn,
                $my['username'],
                $my['password'],
                $options
            );

            $this->activeDriver = 'mysql';

            error_log(
                "CLAIMORA: MySQL connection established successfully."
            );

        } catch (PDOException $e) {
            error_log(
                "CLAIMORA MySQL Connection Error: " . $e->getMessage()
            );

            // Tidak melakukan fallback SQLite secara otomatis.
            throw new RuntimeException(
                "Koneksi MySQL CLAIMORA gagal. Periksa konfigurasi Railway dan kredensial database.",
                0,
                $e
            );
        }
    }

    /**
     * Connect to SQLite
     *
     * Hanya digunakan jika driver SQLite dipilih
     * secara eksplisit pada konfigurasi.
     */
    private function connectSqlite(
        string $sqlitePath,
        array $options
    ): void {
        $dir = dirname($sqlitePath);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException(
                    "Direktori SQLite tidak dapat dibuat."
                );
            }
        }

        try {
            $dsn = "sqlite:" . $sqlitePath;

            $this->pdo = new PDO(
                $dsn,
                null,
                null,
                $options
            );

            $this->pdo->exec("PRAGMA foreign_keys = ON;");

            $this->activeDriver = 'sqlite';

        } catch (PDOException $e) {
            error_log(
                "CLAIMORA SQLite Connection Error: " . $e->getMessage()
            );

            throw new RuntimeException(
                "Koneksi SQLite gagal.",
                0,
                $e
            );
        }
    }

    /**
     * Get underlying PDO instance
     */
    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            throw new RuntimeException(
                "Database connection belum tersedia."
            );
        }

        return $this->pdo;
    }

    /**
     * Get active database driver
     */
    public function getActiveDriver(): string
    {
        return $this->activeDriver;
    }

    /**
     * Check and initialize database schema
     */
    private function ensureSchema(): void
    {
        require_once dirname(__DIR__) . '/database/Migrator.php';

        try {
            // Periksa apakah tabel users sudah tersedia
            $this->pdo->query("SELECT 1 FROM users LIMIT 1");

            // Pastikan skema tambahan tersedia
            \Database\Migrator::ensureCancellationSchema(
                $this->pdo,
                $this->activeDriver
            );

        } catch (PDOException $e) {
            $message = $e->getMessage();

            $missingTable =
                ($this->activeDriver === 'mysql'
                    && in_array($e->getCode(), ['42S02', '42S22'], true))
                ||
                ($this->activeDriver === 'sqlite'
                    && stripos($message, 'no such table') !== false);

            if (!$missingTable) {
                throw $e;
            }

            // Tabel utama belum tersedia, jalankan migrasi
            \Database\Migrator::run(
                $this->pdo,
                $this->activeDriver
            );
        }

        // Pastikan tabel notifikasi tersedia
        $this->ensureNotificationsTable();
    }

    /**
     * Ensure notifications table exists
     */
    private function ensureNotificationsTable(): void
    {
        try {
            $this->pdo->query(
                "SELECT 1 FROM notifications LIMIT 1"
            );

        } catch (PDOException $e) {
            $message = $e->getMessage();

            $missingTable =
                ($this->activeDriver === 'mysql'
                    && in_array($e->getCode(), ['42S02', '42S22'], true))
                ||
                ($this->activeDriver === 'sqlite'
                    && stripos($message, 'no such table') !== false);

            if (!$missingTable) {
                throw $e;
            }

            if ($this->activeDriver === 'sqlite') {
                $sql = "
                    CREATE TABLE IF NOT EXISTS notifications (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER NOT NULL,
                        title TEXT NOT NULL,
                        message TEXT NOT NULL,
                        category TEXT NOT NULL DEFAULT 'Informasi Sistem',
                        link TEXT NULL,
                        is_read INTEGER NOT NULL DEFAULT 0,
                        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id)
                            REFERENCES users(id)
                            ON DELETE CASCADE
                    )
                ";
            } else {
                $sql = "
                    CREATE TABLE IF NOT EXISTS `notifications` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `user_id` INT NOT NULL,
                        `title` VARCHAR(150) NOT NULL,
                        `message` TEXT NOT NULL,
                        `category` ENUM(
                            'Informasi Polis',
                            'Informasi Kendaraan',
                            'Pengajuan Klaim',
                            'Status Verifikasi',
                            'Persetujuan Klaim',
                            'Penolakan Klaim',
                            'Informasi Sistem'
                        ) NOT NULL DEFAULT 'Informasi Sistem',
                        `link` VARCHAR(255) NULL,
                        `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (`user_id`)
                            REFERENCES `users`(`id`)
                            ON DELETE CASCADE,
                        INDEX `idx_notif_user` (`user_id`),
                        INDEX `idx_notif_read` (`is_read`),
                        INDEX `idx_notif_category` (`category`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ";
            }

            $this->pdo->exec($sql);

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
