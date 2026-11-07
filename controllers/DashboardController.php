<?php
namespace Controllers;

use Core\Controller;
use Models\DashboardModel;
use Exception;

/**
 * Class DashboardController
 * Serves dynamic analytics, aggregated SQL KPIs, real historical sparklines,
 * monthly claims trend visualization, and recent claims table.
 */
class DashboardController extends Controller
{
    private DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Main Dashboard View
     */
    public function index(): void
    {
        $currentUser = $this->requireAuth();

        // Dedicated Customer Insurance Dashboard for role 'customer'
        if ($currentUser['role'] === 'customer') {
            try {
                $customerData = $this->dashboardModel->getCustomerDashboardData(
                    (int)$currentUser['id'],
                    $currentUser['email'] ?? '',
                    $currentUser['name'] ?? ''
                );
            } catch (Exception $e) {
                error_log("CLAIMORA Customer Dashboard Error: " . $e->getMessage());
                $customerData = [
                    'kpis' => [
                        'total_polis'       => ['value' => 0, 'formatted' => '0'],
                        'total_claims'      => ['value' => 0, 'formatted' => '0'],
                        'processing_claims' => ['value' => 0, 'formatted' => '0'],
                        'approved_claims'   => ['value' => 0, 'formatted' => '0'],
                    ],
                    'policies'    => [],
                    'claims'      => [],
                    'activeClaim' => null,
                    'activities'  => []
                ];
            }

            $this->render('dashboard/customer', [
                'title'       => 'Dashboard Perlindungan & Klaim Saya - CLAIMORA',
                'user'        => $currentUser,
                'kpi'         => $customerData['kpis'],
                'policies'    => $customerData['policies'],
                'claims'      => $customerData['claims'],
                'activeClaim' => $customerData['activeClaim'],
                'activities'  => $customerData['activities'],
                'activeNav'   => 'dashboard'
            ]);
            return;
        }

        $period = $this->input('period', 'all');
        $validPeriods = ['all', 'month', '30_days', 'year'];
        if (!in_array($period, $validPeriods, true)) {
            $period = 'all';
        }

        $error = null;
        $kpis = [];
        $recentClaims = [];
        $chartData = [];
        $workflowSummary = [];

        try {
            // Retrieve dynamic KPI statistics aggregated from database
            $kpis = $this->dashboardModel->getKpis($period);

            // Retrieve recent claims with joined policy and survey data
            $recentClaims = $this->dashboardModel->getRecentClaims(5);

            // Retrieve 6-month historical trend data for Chart.js / SVG bar chart
            $chartData = $this->dashboardModel->getMonthlyChartData(6);

            // Retrieve integrated workflow summary across Polis, Pengajuan Klaim, & Hasil Survey
            $workflowSummary = $this->dashboardModel->getWorkflowSummary();
        } catch (Exception $e) {
            error_log("CLAIMORA Dashboard Error: " . $e->getMessage());
            $error = "Terjadi kendala saat memuat data statistik dashboard: " . $e->getMessage();

            // Safe fallback structure to prevent null errors in view
            $kpis = [
                'total_polis' => [
                    'raw_value' => 0, 'formatted' => '0', 'change' => '0%',
                    'direction' => 'neutral', 'is_positive' => true, 'class' => 'positive',
                    'sparkline' => '', 'points' => [0, 0, 0, 0, 0, 0]
                ],
                'total_claims' => [
                    'raw_value' => 0, 'formatted' => '0', 'change' => '0%',
                    'direction' => 'neutral', 'is_positive' => true, 'class' => 'positive',
                    'sparkline' => '', 'points' => [0, 0, 0, 0, 0, 0]
                ],
                'approved_claims' => [
                    'raw_value' => 0, 'formatted' => '0', 'change' => '0%',
                    'direction' => 'neutral', 'is_positive' => true, 'class' => 'positive',
                    'sparkline' => '', 'points' => [0, 0, 0, 0, 0, 0]
                ],
                'avg_process_days' => [
                    'raw_value' => 0.0, 'formatted' => '0,0 Hari', 'change' => '0%',
                    'direction' => 'neutral', 'is_positive' => true, 'class' => 'positive',
                    'sparkline' => '', 'points' => [0, 0, 0, 0, 0, 0]
                ],
                'period' => $period
            ];
            $recentClaims = [];
            $chartData = [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                'pengajuan' => [0, 0, 0, 0, 0, 0],
                'disetujui' => [0, 0, 0, 0, 0, 0],
                'maxVal' => 10,
                'has_data' => false
            ];
        }

        // If AJAX request requesting JSON format
        if ($this->isAjax() && $this->input('format') === 'json') {
            $this->json([
                'success'      => $error === null,
                'kpis'         => $kpis,
                'chartData'    => $chartData,
                'recentClaims' => $recentClaims,
                'period'       => $period,
                'error'        => $error
            ]);
            return;
        }

        $this->render('dashboard/index', [
            'title'           => 'Dashboard - CLAIMORA',
            'user'            => $currentUser,
            'kpi'             => $kpis,
            'recentClaims'    => $recentClaims,
            'chartData'       => $chartData,
            'workflowSummary' => $workflowSummary,
            'selectedPeriod'  => $period,
            'error'           => $error,
            'activeNav'       => 'dashboard'
        ]);
    }

    /**
     * 404 Not Found Page
     */
    public function notFound(): void
    {
        $currentUser = $this->requireAuth();

        $this->render('dashboard/404', [
            'title'     => 'Halaman Tidak Ditemukan - CLAIMORA',
            'user'      => $currentUser,
            'activeNav' => ''
        ]);
    }
}
