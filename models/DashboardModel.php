<?php
namespace Models;

use Core\Model;
use Core\Database;
use PDO;
use Exception;

/**
 * Class DashboardModel
 * Encapsulates all statistical aggregation, KPI analytics,
 * sparkline history, and period trend calculations.
 */
class DashboardModel extends Model
{
    protected string $table = 'claims';
    private string $driver;

    public function __construct()
    {
        parent::__construct();
        $this->driver = Database::getInstance()->getActiveDriver();
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Retrieve complete Customer Insurance Dashboard data strictly filtered by logged-in customer ID/identity
     */
    public function getCustomerDashboardData(int $userId, string $userEmail, string $userName): array
    {
        $today = date('Y-m-d');

        // 1. KPI Cards (Strictly filtered by Customer)
        // (1) Total Polis Aktif
        $stmtPolis = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM policies 
            WHERE (email = :email OR LOWER(customer_name) = LOWER(:name))
              AND status = 'Aktif'
              AND start_date <= :today1 AND end_date >= :today2
        ");
        $stmtPolis->execute([
            ':email'  => trim($userEmail),
            ':name'   => trim($userName),
            ':today1' => $today,
            ':today2' => $today
        ]);
        $activePoliciesCount = (int)($stmtPolis->fetch()['total'] ?? 0);

        // (2) Total Pengajuan Klaim
        $stmtTotalClaims = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM claims 
            WHERE submitted_by = :user_id
        ");
        $stmtTotalClaims->execute([':user_id' => $userId]);
        $totalClaimsCount = (int)($stmtTotalClaims->fetch()['total'] ?? 0);

        // (3) Klaim Sedang Diproses
        $stmtProcessing = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM claims 
            WHERE submitted_by = :user_id 
              AND status IN ('Pengajuan Baru', 'Survey Lapangan', 'Dalam Audit')
        ");
        $stmtProcessing->execute([':user_id' => $userId]);
        $processingClaimsCount = (int)($stmtProcessing->fetch()['total'] ?? 0);

        // (4) Klaim Disetujui
        $stmtApproved = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM claims 
            WHERE submitted_by = :user_id 
              AND status = 'Disetujui'
        ");
        $stmtApproved->execute([':user_id' => $userId]);
        $approvedClaimsCount = (int)($stmtApproved->fetch()['total'] ?? 0);

        $kpis = [
            'total_polis' => [
                'value'     => $activePoliciesCount,
                'formatted' => number_format($activePoliciesCount, 0, ',', '.')
            ],
            'total_claims' => [
                'value'     => $totalClaimsCount,
                'formatted' => number_format($totalClaimsCount, 0, ',', '.')
            ],
            'processing_claims' => [
                'value'     => $processingClaimsCount,
                'formatted' => number_format($processingClaimsCount, 0, ',', '.')
            ],
            'approved_claims' => [
                'value'     => $approvedClaimsCount,
                'formatted' => number_format($approvedClaimsCount, 0, ',', '.')
            ],
        ];

        // 2. Customer's Vehicles & Policies (Informasi Polis Kendaraan Saya)
        $stmtMyPolicies = $this->db->prepare("
            SELECT * FROM policies 
            WHERE (email = :email OR LOWER(customer_name) = LOWER(:name))
            ORDER BY id DESC
        ");
        $stmtMyPolicies->execute([
            ':email' => trim($userEmail),
            ':name'  => trim($userName)
        ]);
        $policies = $stmtMyPolicies->fetchAll();

        // 3. Customer's Claims (Pengajuan Klaim Terbaru)
        $stmtMyClaims = $this->db->prepare("
            SELECT c.*, 
                   p.policy_number, 
                   p.customer_name, 
                   p.vehicle_brand, 
                   p.vehicle_model, 
                   p.vehicle_plate, 
                   p.insurance_type,
                   p.coverage_amount,
                   sr.damage_level,
                   sr.approved_claim_amount,
                   sr.approval_status as survey_approval_status,
                   sr.survey_date,
                   sr.surveyor_notes
            FROM claims c
            JOIN policies p ON c.policy_id = p.id
            LEFT JOIN survey_reports sr ON c.id = sr.claim_id
            WHERE c.submitted_by = :user_id
            ORDER BY c.id DESC
            LIMIT 10
        ");
        $stmtMyClaims->execute([':user_id' => $userId]);
        $claims = $stmtMyClaims->fetchAll();

        // 4. Identify Primary/Active Claim for the Visual Progress Tracker
        $activeClaim = null;
        if (!empty($claims)) {
            // Pick first claim that is in-progress, or the latest one
            foreach ($claims as $c) {
                if (in_array($c['status'], ['Pengajuan Baru', 'Survey Lapangan', 'Dalam Audit'], true)) {
                    $activeClaim = $c;
                    break;
                }
            }
            if (!$activeClaim) {
                $activeClaim = $claims[0]; // fallback to latest claim
            }
        }

        // 5. Recent Activity & Notifications (Aktivitas Terbaru Akun Nasabah)
        $activities = [];
        $claimNumbers = array_column($claims, 'claim_number');
        if (!empty($claimNumbers)) {
            // Fetch relevant audit logs
            $likeClauses = [];
            $params = [':user_id' => $userId];
            foreach ($claimNumbers as $idx => $cn) {
                $likeClauses[] = "detail LIKE :cn_{$idx}";
                $params[":cn_{$idx}"] = "%{$cn}%";
            }
            $whereOr = !empty($likeClauses) ? "OR " . implode(' OR ', $likeClauses) : "";
            $sqlLogs = "
                SELECT * FROM audit_logs 
                WHERE (user_id = :user_id {$whereOr})
                ORDER BY id DESC 
                LIMIT 6
            ";
            $stmtLogs = $this->db->prepare($sqlLogs);
            $stmtLogs->execute($params);
            $activities = $stmtLogs->fetchAll();
        }

        return [
            'kpis'        => $kpis,
            'policies'    => $policies,
            'claims'      => $claims,
            'activeClaim' => $activeClaim,
            'activities'  => $activities
        ];
    }

    /**
     * Retrieve all 4 primary KPIs with percentage changes and sparkline historical curves
     */
    public function getKpis(string $period = 'all'): array
    {
        $today = date('Y-m-d');
        $dateRanges = $this->resolveDateRanges($period);

        // 1. Total Polis Aktif (Status 'Aktif' and today between start_date and end_date)
        $activePoliciesNow = $this->queryActivePolicies($today, $dateRanges['current']);
        $activePoliciesPrev = $this->queryActivePolicies($today, $dateRanges['previous']);
        $activePoliciesTrend = $this->queryActivePolicies($today, $dateRanges['trend']);
        $trendPolis = $this->calculatePercentageChange((float)$activePoliciesTrend, (float)$activePoliciesPrev, false);
        $sparklinePolis = $this->getMonthlySparklineData('policies');

        // 2. Pengajuan Klaim (Total claims recorded)
        $claimsNow = $this->queryClaimsCount($dateRanges['current']);
        $claimsPrev = $this->queryClaimsCount($dateRanges['previous']);
        $claimsTrend = $this->queryClaimsCount($dateRanges['trend']);
        $trendClaims = $this->calculatePercentageChange((float)$claimsTrend, (float)$claimsPrev, false);
        $sparklineClaims = $this->getMonthlySparklineData('claims');

        // 3. Klaim Disetujui (Claims with status 'Disetujui' or survey approved)
        $approvedNow = $this->queryApprovedClaimsCount($dateRanges['current']);
        $approvedPrev = $this->queryApprovedClaimsCount($dateRanges['previous']);
        $approvedTrend = $this->queryApprovedClaimsCount($dateRanges['trend']);
        $trendApproved = $this->calculatePercentageChange((float)$approvedTrend, (float)$approvedPrev, false);
        $sparklineApproved = $this->getMonthlySparklineData('approved');

        // 4. Rata-rata Proses (Duration in days from submission created_at to decision approval_date)
        $avgDaysNow = $this->queryAverageProcessingDays($dateRanges['current']);
        $avgDaysPrev = $this->queryAverageProcessingDays($dateRanges['previous']);
        $avgDaysTrend = $this->queryAverageProcessingDays($dateRanges['trend']);
        // For process duration, a decrease in days is positive (faster processing)
        $trendDays = $this->calculatePercentageChange($avgDaysTrend, $avgDaysPrev, true);
        $sparklineDays = $this->getMonthlySparklineData('duration');

        return [
            'total_polis' => [
                'raw_value'   => $activePoliciesNow,
                'formatted'   => number_format($activePoliciesNow, 0, ',', '.'),
                'change'      => $trendPolis['formatted'],
                'direction'   => $trendPolis['direction'],
                'is_positive' => $trendPolis['is_positive'],
                'class'       => $trendPolis['is_positive'] ? 'positive' : 'negative',
                'sparkline'   => $this->generateSvgSparkline($sparklinePolis, '#22c55e', 'grad_green'),
                'points'      => $sparklinePolis
            ],
            'total_claims' => [
                'raw_value'   => $claimsNow,
                'formatted'   => number_format($claimsNow, 0, ',', '.'),
                'change'      => $trendClaims['formatted'],
                'direction'   => $trendClaims['direction'],
                'is_positive' => $trendClaims['is_positive'],
                'class'       => $trendClaims['is_positive'] ? 'positive' : 'negative',
                'sparkline'   => $this->generateSvgSparkline($sparklineClaims, '#f59e0b', 'grad_yellow'),
                'points'      => $sparklineClaims
            ],
            'approved_claims' => [
                'raw_value'   => $approvedNow,
                'formatted'   => number_format($approvedNow, 0, ',', '.'),
                'change'      => $trendApproved['formatted'],
                'direction'   => $trendApproved['direction'],
                'is_positive' => $trendApproved['is_positive'],
                'class'       => $trendApproved['is_positive'] ? 'positive' : 'negative',
                'sparkline'   => $this->generateSvgSparkline($sparklineApproved, '#22c55e', 'grad_green_app'),
                'points'      => $sparklineApproved
            ],
            'avg_process_days' => [
                'raw_value'   => $avgDaysNow,
                'formatted'   => number_format($avgDaysNow, 1, ',', '.') . ' Hari',
                'change'      => $trendDays['formatted'],
                'direction'   => $trendDays['direction'],
                'is_positive' => $trendDays['is_positive'],
                'class'       => $trendDays['is_positive'] ? 'positive' : 'negative',
                'sparkline'   => $this->generateSvgSparkline($sparklineDays, '#10b981', 'grad_teal', true),
                'points'      => $sparklineDays
            ],
            'period' => $period
        ];
    }

    /**
     * KPI 1: Query count of active policies
     */
    private function queryActivePolicies(string $today, array $range): int
    {
        $sql = "SELECT COUNT(*) as total FROM policies WHERE status = 'Aktif' AND start_date <= :today1 AND end_date >= :today2";
        $params = [
            ':today1' => $today,
            ':today2' => $today
        ];

        if (!empty($range['start']) && !empty($range['end'])) {
            $sql .= " AND created_at >= :start AND created_at <= :end";
            $params[':start'] = $range['start'];
            $params[':end'] = $range['end'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }

    /**
     * KPI 2: Query total submitted claims count
     */
    private function queryClaimsCount(array $range): int
    {
        $sql = "SELECT COUNT(*) as total FROM claims WHERE 1=1";
        $params = [];

        if (!empty($range['start']) && !empty($range['end'])) {
            $sql .= " AND created_at >= :start AND created_at <= :end";
            $params[':start'] = $range['start'];
            $params[':end'] = $range['end'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }

    /**
     * KPI 3: Query total approved claims count
     */
    private function queryApprovedClaimsCount(array $range): int
    {
        $sql = "SELECT COUNT(*) as total FROM claims WHERE status = 'Disetujui'";
        $params = [];

        if (!empty($range['start']) && !empty($range['end'])) {
            $sql .= " AND (created_at >= :start AND created_at <= :end)";
            $params[':start'] = $range['start'];
            $params[':end'] = $range['end'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }

    /**
     * KPI 4: Query average processing duration (days from created_at to decision approval_date)
     */
    private function queryAverageProcessingDays(array $range): float
    {
        if ($this->driver === 'sqlite') {
            $sql = "SELECT AVG(julianday(COALESCE(sr.approval_date, c.updated_at)) - julianday(c.created_at)) as avg_days
                    FROM claims c
                    LEFT JOIN survey_reports sr ON c.id = sr.claim_id
                    WHERE (c.status IN ('Disetujui', 'Ditolak') OR sr.approval_status IN ('Disetujui', 'Ditolak'))
                      AND COALESCE(sr.approval_date, c.updated_at) IS NOT NULL
                      AND COALESCE(sr.approval_date, c.updated_at) >= c.created_at";
        } else {
            $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, c.created_at, COALESCE(sr.approval_date, c.updated_at)) / 1440.0) as avg_days
                    FROM claims c
                    LEFT JOIN survey_reports sr ON c.id = sr.claim_id
                    WHERE (c.status IN ('Disetujui', 'Ditolak') OR sr.approval_status IN ('Disetujui', 'Ditolak'))
                      AND COALESCE(sr.approval_date, c.updated_at) IS NOT NULL
                      AND COALESCE(sr.approval_date, c.updated_at) >= c.created_at";
        }

        $params = [];
        if (!empty($range['start']) && !empty($range['end'])) {
            $sql .= " AND c.created_at >= :start AND c.created_at <= :end";
            $params[':start'] = $range['start'];
            $params[':end'] = $range['end'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        $avg = $res['avg_days'] ?? null;
        return $avg !== null ? max(0.0, (float)$avg) : 0.0;
    }

    /**
     * Generate monthly historical trend data for sparklines across 6 monthly intervals
     */
    private function getMonthlySparklineData(string $metric): array
    {
        $points = [];
        $months = 6;

        for ($i = $months - 1; $i >= 0; $i--) {
            // "first day of" keeps month arithmetic correct on the 29th-31st
            $time = strtotime("first day of -{$i} month");
            $start = date('Y-m-01 00:00:00', $time);
            $end = date('Y-m-t 23:59:59', $time);
            $evalDate = min(date('Y-m-d'), date('Y-m-t', $time));

            switch ($metric) {
                case 'policies':
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) as c FROM policies 
                        WHERE status = 'Aktif' 
                          AND start_date <= :eval_date1 
                          AND end_date >= :eval_date2
                    ");
                    $stmt->execute([
                        ':eval_date1' => $evalDate,
                        ':eval_date2' => $evalDate
                    ]);
                    $points[] = (float)($stmt->fetch()['c'] ?? 0);
                    break;

                case 'claims':
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) as c FROM claims 
                        WHERE created_at >= :start AND created_at <= :end
                    ");
                    $stmt->execute([':start' => $start, ':end' => $end]);
                    $points[] = (float)($stmt->fetch()['c'] ?? 0);
                    break;

                case 'approved':
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) as c FROM claims 
                        WHERE status = 'Disetujui' 
                          AND created_at >= :start AND created_at <= :end
                    ");
                    $stmt->execute([':start' => $start, ':end' => $end]);
                    $points[] = (float)($stmt->fetch()['c'] ?? 0);
                    break;

                case 'duration':
                    if ($this->driver === 'sqlite') {
                        $sql = "SELECT AVG(julianday(COALESCE(sr.approval_date, c.updated_at)) - julianday(c.created_at)) as avg_days
                                FROM claims c
                                LEFT JOIN survey_reports sr ON c.id = sr.claim_id
                                WHERE (c.status IN ('Disetujui', 'Ditolak') OR sr.approval_status IN ('Disetujui', 'Ditolak'))
                                  AND c.created_at >= :start AND c.created_at <= :end
                                  AND COALESCE(sr.approval_date, c.updated_at) >= c.created_at";
                    } else {
                        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, c.created_at, COALESCE(sr.approval_date, c.updated_at)) / 1440.0) as avg_days
                                FROM claims c
                                LEFT JOIN survey_reports sr ON c.id = sr.claim_id
                                WHERE (c.status IN ('Disetujui', 'Ditolak') OR sr.approval_status IN ('Disetujui', 'Ditolak'))
                                  AND c.created_at >= :start AND c.created_at <= :end
                                  AND COALESCE(sr.approval_date, c.updated_at) >= c.created_at";
                    }
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':start' => $start, ':end' => $end]);
                    $res = $stmt->fetch()['avg_days'] ?? null;
                    $points[] = $res !== null ? round((float)$res, 2) : 0.0;
                    break;
            }
        }

        return $points;
    }

    /**
     * Compute clean, smooth, responsive SVG sparkline path from actual data points
     */
    public function generateSvgSparkline(array $points, string $strokeColor = '#22c55e', string $gradientId = 'spark_grad', bool $invertTrend = false): string
    {
        $count = count($points);
        if ($count < 2) {
            return '<svg class="kpi-sparkline" viewBox="0 0 100 40" preserveAspectRatio="none"><path d="M 0 30 L 100 30" fill="none" stroke="' . htmlspecialchars($strokeColor, ENT_QUOTES, 'UTF-8') . '" stroke-width="2.5"/></svg>';
        }

        $min = min($points);
        $max = max($points);
        $range = $max - $min;

        $width = 100;
        $height = 40;
        $paddingY = 8;
        $drawableHeight = $height - ($paddingY * 2);

        $coords = [];
        for ($i = 0; $i < $count; $i++) {
            $x = ($count === 1) ? 50 : round(($i / ($count - 1)) * $width, 1);
            if ($range == 0) {
                $y = round($height / 2, 1);
            } else {
                $norm = ($points[$i] - $min) / $range;
                $y = round($height - $paddingY - ($norm * $drawableHeight), 1);
            }
            $coords[] = ['x' => $x, 'y' => $y];
        }

        // Build smooth SVG Bézier curve
        $pathD = "M {$coords[0]['x']} {$coords[0]['y']}";
        for ($i = 0; $i < $count - 1; $i++) {
            $curr = $coords[$i];
            $next = $coords[$i + 1];
            $cp1x = round($curr['x'] + ($next['x'] - $curr['x']) * 0.45, 1);
            $cp1y = $curr['y'];
            $cp2x = round($curr['x'] + ($next['x'] - $curr['x']) * 0.55, 1);
            $cp2y = $next['y'];
            $pathD .= " C {$cp1x} {$cp1y}, {$cp2x} {$cp2y}, {$next['x']} {$next['y']}";
        }

        $stroke = htmlspecialchars($strokeColor, ENT_QUOTES, 'UTF-8');
        return '<svg class="kpi-sparkline" viewBox="0 0 ' . $width . ' ' . $height . '" preserveAspectRatio="none">
            <path d="' . $pathD . '" fill="none" stroke="' . $stroke . '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>';
    }

    /**
     * Retrieve monthly aggregated trends for the 6-Month Claims Bar Chart from database
     */
    public function getMonthlyChartData(int $months = 6): array
    {
        $indonesianMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $labels = [];
        $pengajuan = [];
        $disetujui = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $time = strtotime("first day of -{$i} month");
            $mNum = (int)date('n', $time);
            $year = date('Y', $time);
            $labels[] = ($indonesianMonths[$mNum] ?? date('M', $time)) . ' ' . substr($year, 2);

            $start = date('Y-m-01 00:00:00', $time);
            $end = date('Y-m-t 23:59:59', $time);

            // 1. Claims submitted in this month
            $stmt1 = $this->db->prepare("
                SELECT COUNT(*) as c FROM claims 
                WHERE created_at >= :start AND created_at <= :end
            ");
            $stmt1->execute([':start' => $start, ':end' => $end]);
            $countPengajuan = (int)($stmt1->fetch()['c'] ?? 0);
            $pengajuan[] = $countPengajuan;

            // 2. Claims approved in this month
            $stmt2 = $this->db->prepare("
                SELECT COUNT(*) as c FROM claims 
                WHERE status = 'Disetujui' 
                  AND created_at >= :start AND created_at <= :end
            ");
            $stmt2->execute([':start' => $start, ':end' => $end]);
            $countDisetujui = (int)($stmt2->fetch()['c'] ?? 0);
            $disetujui[] = $countDisetujui;
        }

        $allVals = array_merge($pengajuan, $disetujui);
        $maxRaw = !empty($allVals) ? max($allVals) : 0;
        // Provide round ceiling for axis grid
        $maxVal = max(10, (int)(ceil(($maxRaw + 1) / 5) * 5));

        return [
            'labels'    => $labels,
            'pengajuan' => $pengajuan,
            'disetujui' => $disetujui,
            'maxVal'    => $maxVal,
            'has_data'  => array_sum($pengajuan) > 0 || array_sum($disetujui) > 0
        ];
    }

    /**
     * Retrieve recent claims for the dashboard table with joined Policy & Survey data
     */
    public function getRecentClaims(int $limit = 5): array
    {
        $lim = (int)$limit;
        $sql = "
            SELECT c.*, 
                   p.customer_name, 
                   p.vehicle_brand, 
                   p.vehicle_model, 
                   p.vehicle_plate, 
                   p.policy_number,
                   sr.id as survey_id,
                   sr.damage_level,
                   sr.approval_status as survey_approval_status,
                   sr.approved_claim_amount,
                   sr.survey_date
            FROM claims c
            JOIN policies p ON c.policy_id = p.id
            LEFT JOIN survey_reports sr ON c.id = sr.claim_id
            ORDER BY c.id DESC
            LIMIT {$lim}
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Retrieve integrated cross-module workflow summary (Polis, Pengajuan Klaim, Hasil Survey)
     */
    public function getWorkflowSummary(): array
    {
        // 1. Data Polis summary
        $stmtPol = $this->db->query("
            SELECT 
                COUNT(*) as total_policies,
                SUM(CASE WHEN status = 'Aktif' THEN 1 ELSE 0 END) as active_policies,
                SUM(CASE WHEN status = 'Berakhir' THEN 1 ELSE 0 END) as expired_policies,
                COALESCE(SUM(coverage_amount), 0) as total_coverage_limit
            FROM policies
        ");
        $pol = $stmtPol->fetch() ?: [];

        // 2. Pengajuan Klaim summary
        $stmtClaim = $this->db->query("
            SELECT 
                COUNT(*) as total_claims,
                SUM(CASE WHEN status = 'Pengajuan Baru' THEN 1 ELSE 0 END) as new_claims,
                SUM(CASE WHEN status = 'Survey Lapangan' THEN 1 ELSE 0 END) as survey_claims,
                SUM(CASE WHEN status = 'Dalam Audit' THEN 1 ELSE 0 END) as audit_claims,
                SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as approved_claims,
                SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as rejected_claims,
                COALESCE(SUM(estimated_damage_cost), 0) as total_estimated_cost
            FROM claims
        ");
        $claim = $stmtClaim->fetch() ?: [];

        // 3. Hasil Survey & Persetujuan summary
        $stmtSurvey = $this->db->query("
            SELECT 
                COUNT(*) as total_reports,
                COALESCE(SUM(CASE WHEN approval_status = 'Disetujui' THEN approved_claim_amount ELSE 0 END), 0) as total_approved_amount,
                SUM(CASE WHEN approval_status = 'Disetujui' THEN 1 ELSE 0 END) as approved_reports,
                SUM(CASE WHEN approval_status = 'Pending' THEN 1 ELSE 0 END) as pending_reports
            FROM survey_reports
        ");
        $survey = $stmtSurvey->fetch() ?: [];

        return [
            'total_policies'         => (int)($pol['total_policies'] ?? 0),
            'active_policies'        => (int)($pol['active_policies'] ?? 0),
            'expired_policies'       => (int)($pol['expired_policies'] ?? 0),
            'total_coverage_limit'   => (float)($pol['total_coverage_limit'] ?? 0),
            'total_claims'           => (int)($claim['total_claims'] ?? 0),
            'new_claims'             => (int)($claim['new_claims'] ?? 0),
            'survey_claims'          => (int)($claim['survey_claims'] ?? 0),
            'audit_claims'           => (int)($claim['audit_claims'] ?? 0),
            'approved_claims'        => (int)($claim['approved_claims'] ?? 0),
            'rejected_claims'        => (int)($claim['rejected_claims'] ?? 0),
            'total_estimated_cost'   => (float)($claim['total_estimated_cost'] ?? 0),
            'total_survey_reports'   => (int)($survey['total_reports'] ?? 0),
            'total_approved_amount'  => (float)($survey['total_approved_amount'] ?? 0),
            'approved_reports'       => (int)($survey['approved_reports'] ?? 0),
            'pending_reports'        => (int)($survey['pending_reports'] ?? 0),
        ];
    }

    /**
     * Calculate percentage change with zero division safeguard
     */
    public function calculatePercentageChange(float $current, float $previous, bool $lowerIsBetter = false): array
    {
        if ($previous == 0.0) {
            if ($current == 0.0) {
                return ['change' => 0, 'formatted' => '0%', 'direction' => 'neutral', 'is_positive' => true];
            }
            return ['change' => 100, 'formatted' => '+100%', 'direction' => 'up', 'is_positive' => !$lowerIsBetter];
        }

        $diff = $current - $previous;
        $percent = round(($diff / $previous) * 100);

        if ($percent > 0) {
            return [
                'change'      => $percent,
                'formatted'   => '+' . abs($percent) . '%',
                'direction'   => 'up',
                'is_positive' => !$lowerIsBetter
            ];
        } elseif ($percent < 0) {
            return [
                'change'      => $percent,
                'formatted'   => '-' . abs($percent) . '%',
                'direction'   => 'down',
                'is_positive' => $lowerIsBetter
            ];
        } else {
            return [
                'change'      => 0,
                'formatted'   => '0%',
                'direction'   => 'neutral',
                'is_positive' => true
            ];
        }
    }

    /**
     * Helper to resolve current and comparison previous date ranges
     */
    private function resolveDateRanges(string $period): array
    {
        switch ($period) {
            case 'month':
                // Current Month vs Last Month
                $curStart  = date('Y-m-01 00:00:00');
                $curEnd    = date('Y-m-t 23:59:59');
                $prevStart = date('Y-m-01 00:00:00', strtotime('first day of -1 month'));
                $prevEnd   = date('Y-m-t 23:59:59', strtotime('first day of -1 month'));
                break;

            case 'year':
                // Current Year vs Last Year
                $curStart  = date('Y-01-01 00:00:00');
                $curEnd    = date('Y-12-31 23:59:59');
                $prevStart = date('Y-01-01 00:00:00', strtotime('-1 year'));
                $prevEnd   = date('Y-12-31 23:59:59', strtotime('-1 year'));
                break;

            case '30_days':
                // Last 30 Days vs Prior 30 Days
                $curStart  = date('Y-m-d 00:00:00', strtotime('-30 days'));
                $curEnd    = date('Y-m-d 23:59:59');
                $prevStart = date('Y-m-d 00:00:00', strtotime('-60 days'));
                $prevEnd   = date('Y-m-d 23:59:59', strtotime('-31 days'));
                break;

            case 'all':
            default:
                // All time for current value, comparison uses current month vs prior month
                $curStart  = null;
                $curEnd    = null;
                $prevStart = date('Y-m-01 00:00:00', strtotime('first day of -1 month'));
                $prevEnd   = date('Y-m-t 23:59:59', strtotime('first day of -1 month'));

                return [
                    'current'  => ['start' => $curStart, 'end' => $curEnd],
                    'previous' => ['start' => $prevStart, 'end' => $prevEnd],
                    'trend'    => ['start' => date('Y-m-01 00:00:00'), 'end' => date('Y-m-t 23:59:59')],
                ];
        }

        return [
            'current'  => ['start' => $curStart, 'end' => $curEnd],
            'previous' => ['start' => $prevStart, 'end' => $prevEnd],
            'trend'    => ['start' => $curStart, 'end' => $curEnd],
        ];
    }
}

