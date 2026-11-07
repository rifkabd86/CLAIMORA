<?php
namespace Core;

use PDO;

/**
 * Interface AuditLoggerInterface (Abstraction)
 */
interface AuditLoggerInterface
{
    public function log(string $module, string $activity, string $detail, string $status = 'Berhasil'): bool;
}

/**
 * Interface AuditFormatterInterface (Polymorphism)
 */
interface AuditFormatterInterface
{
    public function format(array $logs): string;
    public function getContentType(): string;
    public function getFileExtension(): string;
}

/**
 * Class CsvAuditFormatter (Polymorphic Implementation 1)
 */
class CsvAuditFormatter implements AuditFormatterInterface
{
    public function format(array $logs): string
    {
        $output = fopen('php://temp', 'r+');
        // UTF-8 BOM for Excel compatibility
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, ['No', 'Tanggal & Waktu', 'Pengguna', 'Modul', 'Aktivitas', 'Detail', 'IP Address', 'Status']);

        $idx = 1;
        foreach ($logs as $log) {
            fputcsv($output, [
                $idx++,
                $log['created_at'],
                $log['user_name'],
                $log['module'],
                $log['activity'],
                $log['detail'],
                $log['ip_address'],
                $log['status']
            ]);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        return $content;
    }

    public function getContentType(): string
    {
        return 'text/csv; charset=UTF-8';
    }

    public function getFileExtension(): string
    {
        return 'csv';
    }
}

/**
 * Class JsonAuditFormatter (Polymorphic Implementation 2)
 */
class JsonAuditFormatter implements AuditFormatterInterface
{
    public function format(array $logs): string
    {
        return json_encode([
            'generated_at' => date('Y-m-d H:i:s'),
            'total_records' => count($logs),
            'audit_trail' => $logs
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function getContentType(): string
    {
        return 'application/json; charset=UTF-8';
    }

    public function getFileExtension(): string
    {
        return 'json';
    }
}

/**
 * Class AuditLogger
 * Core security logging service ensuring non-repudiation and tracking all CRUD actions.
 */
class AuditLogger implements AuditLoggerInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Record an audit trail log entry
     */
    public function log(string $module, string $activity, string $detail, string $status = 'Berhasil'): bool
    {
        try {
            Session::start();
            $user = Session::get('user');

            $userId = $user['id'] ?? null;
            $userName = $user['name'] ?? 'Sistem / Tamu';
            $ipAddress = Security::getClientIp();
            $now = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, user_name, module, activity, detail, ip_address, status, created_at)
                VALUES (:user_id, :user_name, :module, :activity, :detail, :ip_address, :status, :created_at)
            ");

            return $stmt->execute([
                ':user_id'    => $userId,
                ':user_name'  => $userName,
                ':module'     => $module,
                ':activity'   => $activity,
                ':detail'     => $detail,
                ':ip_address' => $ipAddress,
                ':status'     => $status,
                ':created_at' => $now
            ]);
        } catch (\Throwable $e) {
            error_log("AuditLogger Error: " . $e->getMessage());
            return false;
        }
    }
}
