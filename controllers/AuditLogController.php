<?php
namespace Controllers;

use Core\Controller;
use Core\AuditLogger;
use Core\CsvAuditFormatter;
use Core\JsonAuditFormatter;
use Models\AuditLogModel;

/**
 * Class AuditLogController
 * Displays system security audit logs with multi-parameter filtering,
 * pagination, and CSV export (matching Image 2).
 */
class AuditLogController extends Controller
{
    private AuditLogModel $auditModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->auditModel = new AuditLogModel();
        $this->auditLogger = new AuditLogger();
    }

    /**
     * Tampilkan audit log — hanya Admin yang bisa mengakses
     */
    public function index(): void
    {
        $currentUser = $this->requireRole(['admin']);

        $startDate = $this->input('start_date', '');
        $endDate   = $this->input('end_date', '');
        $module    = $this->input('module', '');
        $userName  = $this->input('user_name', '');
        $search    = $this->input('q', '');
        $page      = max(1, (int)$this->input('p', 1));
        $perPage   = 10;
        $offset    = ($page - 1) * $perPage;

        $logs       = $this->auditModel->filterLogs($startDate, $endDate, $module, $userName, $search, $perPage, $offset);
        $totalRows  = $this->auditModel->countFiltered($startDate, $endDate, $module, $userName, $search);
        $totalPages = max(1, (int)ceil($totalRows / $perPage));

        $modules   = $this->auditModel->getDistinctModules();
        $userNames = $this->auditModel->getDistinctUsers();

        $this->render('audit/index', [
            'title'       => 'Audit Log - Riwayat Aktivitas CLAIMORA',
            'user'        => $currentUser,
            'logs'        => $logs,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'module'      => $module,
            'userName'    => $userName,
            'search'      => $search,
            'page'        => $page,
            'perPage'     => $perPage,
            'offset'      => $offset,
            'totalRows'   => $totalRows,
            'totalPages'  => $totalPages,
            'modules'     => $modules,
            'userNames'   => $userNames,
            'activeNav'   => 'audit'
        ]);
    }

    /**
     * Export Audit Trail to CSV or JSON using Polymorphic Formatters
     * Hanya Admin yang bisa export log
     */
    public function export(): void
    {
        $currentUser = $this->requireRole(['admin']);

        $startDate = $this->input('start_date', '');
        $endDate   = $this->input('end_date', '');
        $module    = $this->input('module', '');
        $userName  = $this->input('user_name', '');
        $search    = $this->input('q', '');
        $format    = strtolower($this->input('format', 'csv'));

        // Retrieve up to 2000 log records for export
        $logs = $this->auditModel->filterLogs($startDate, $endDate, $module, $userName, $search, 2000, 0);

        // Select polymorphic formatter (Abstraction & Polymorphism)
        if ($format === 'json') {
            $formatter = new JsonAuditFormatter();
            $filename  = 'audit_log_' . date('Ymd_His') . '.json';
        } else {
            $formatter = new CsvAuditFormatter();
            $filename  = 'audit_log_' . date('Ymd_His') . '.csv';
        }

        $content = $formatter->format($logs);

        $totalCount = count($logs);

        // Record export activity into audit log itself
        $this->auditLogger->log(
            'Audit Log',
            'Export Log',
            "Export {$totalCount} rekaman log ke berkas: {$filename}",
            'Berhasil'
        );

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $formatter->getContentType());
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($content));

        echo $content;
        exit;
    }
}
