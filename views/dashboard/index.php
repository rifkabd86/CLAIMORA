<!-- Error Alert Handling -->
<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= e($error) ?></span>
        </div>
        <a href="index.php?page=dashboard" class="btn btn-outline" style="padding: 4px 12px; font-size: 12px;">Muat Ulang</a>
    </div>
<?php endif; ?>

<!-- Welcome Banner (Matching Image 1) -->
<section class="welcome-card">
    <div class="welcome-content">
        <h2>Selamat Datang di <span>CLAIMORA</span></h2>
        <div class="subheading">Sistem Pengajuan Klaim Asuransi Terintegrasi</div>
        <p>Kelola pengajuan klaim, verifikasi surveyor lapangan, dan pantau riwayat klaim dengan lebih mudah, cepat, dan aman.</p>
        <?php if (($user['role'] ?? '') === 'admin'): ?>
        <a href="index.php?page=claims&action=create" class="btn-primary">
            <span>Mulai Pengajuan Klaim</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </a>
        <?php endif; ?>
    </div>

    <!-- Vector Car & Shield Illustration Graphic -->
    <div class="welcome-graphic">
        <svg viewBox="0 0 320 180" fill="none">
            <ellipse cx="160" cy="155" rx="120" ry="14" fill="rgba(45, 106, 79, 0.12)"/>
            <!-- White Modern Sedan -->
            <path d="M45 130 L65 95 Q95 72 145 72 L210 72 Q240 80 255 108 L275 125 Q280 138 270 145 L45 145 Z" fill="#ffffff" stroke="#cde3d6" stroke-width="2"/>
            <!-- Tinted Windows -->
            <path d="M78 95 L100 77 L145 77 L145 95 Z" fill="#2d6a4f" opacity="0.25"/>
            <path d="M152 77 L202 77 Q222 83 232 95 L152 95 Z" fill="#2d6a4f" opacity="0.25"/>
            <!-- Wheels -->
            <circle cx="85" cy="144" r="18" fill="#2d3748"/>
            <circle cx="85" cy="144" r="10" fill="#e2ece6"/>
            <circle cx="230" cy="144" r="18" fill="#2d3748"/>
            <circle cx="230" cy="144" r="10" fill="#e2ece6"/>
            <!-- Headlight glow -->
            <polygon points="275,130 310,135 305,145 270,140" fill="#fef08a" opacity="0.6"/>
            <!-- Green Protection Shield with Checkmark -->
            <path d="M225 25 L265 42 V75 C265 105 245 128 225 138 C205 128 185 105 185 75 V42 Z" fill="#2d6a4f"/>
            <path d="M225 32 L258 46 V73 C258 98 242 118 225 126 C208 118 192 98 192 73 V46 Z" fill="#52b788"/>
            <polyline points="208,76 220,88 244,60" stroke="#ffffff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
</section>

<!-- Dashboard Control & Period Filter Bar -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h3 style="font-size: 17px; font-weight: 800; color: var(--text-primary); margin: 0;">Ringkasan Kinerja & Metrik Utama</h3>
        <p style="font-size: 12.5px; color: var(--text-muted); margin: 2px 0 0 0;">
            Data dihitung dinamis secara real-time dari database MySQL &bull; Terakhir diperbarui: <strong><?= date('H:i') ?> WIB</strong>
        </p>
    </div>

    <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 10px;">
        <input type="hidden" name="page" value="dashboard">
        <label for="period-select" style="font-size: 12.5px; font-weight: 600; color: var(--text-secondary); white-space: nowrap;">
            Filter Periode:
        </label>
        <select name="period" id="period-select" class="form-control" onchange="this.form.submit()" style="width: 170px; font-size: 13px; height: 38px; padding: 4px 10px;">
            <option value="all" <?= ($selectedPeriod ?? 'all') === 'all' ? 'selected' : '' ?>>Semua Periode</option>
            <option value="month" <?= ($selectedPeriod ?? '') === 'month' ? 'selected' : '' ?>>Bulan Ini</option>
            <option value="30_days" <?= ($selectedPeriod ?? '') === '30_days' ? 'selected' : '' ?>>30 Hari Terakhir</option>
            <option value="year" <?= ($selectedPeriod ?? '') === 'year' ? 'selected' : '' ?>>Tahun Ini (<?= date('Y') ?>)</option>
        </select>
    </form>
</div>

<!-- KPI Cards Grid (Matching Image 1) -->
<section class="kpi-grid">
    <!-- 1. Total Polis Aktif -->
    <div class="kpi-card" style="cursor: pointer;" onclick="window.location.href='index.php?page=policies'">
        <div class="kpi-icon icon-green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
        </div>
        <div class="kpi-info">
            <div class="kpi-title">Total Polis Aktif</div>
            <div class="kpi-value-row">
                <div class="kpi-value"><?= e($kpi['total_polis']['formatted'] ?? '0') ?></div>
                <div class="kpi-trend <?= ($kpi['total_polis']['is_positive'] ?? true) ? 'positive' : 'negative' ?>" title="Perubahan dibanding periode sebelumnya">
                    <?php if (($kpi['total_polis']['direction'] ?? '') === 'up'): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    <?php elseif (($kpi['total_polis']['direction'] ?? '') === 'down'): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    <?php endif; ?>
                    <span><?= e($kpi['total_polis']['change'] ?? '0%') ?></span>
                </div>
            </div>
        </div>
        <!-- Real Dynamic Sparkline Curve from MySQL History -->
        <?= $kpi['total_polis']['sparkline'] ?? '' ?>
    </div>

    <!-- 2. Pengajuan Klaim -->
    <div class="kpi-card" style="cursor: pointer;" onclick="window.location.href='index.php?page=claims'">
        <div class="kpi-icon icon-yellow">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <div class="kpi-info">
            <div class="kpi-title">Pengajuan Klaim</div>
            <div class="kpi-value-row">
                <div class="kpi-value"><?= e($kpi['total_claims']['formatted'] ?? '0') ?></div>
                <div class="kpi-trend <?= ($kpi['total_claims']['is_positive'] ?? true) ? 'positive' : 'negative' ?>" title="Perubahan dibanding periode sebelumnya">
                    <?php if (($kpi['total_claims']['direction'] ?? '') === 'up'): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    <?php elseif (($kpi['total_claims']['direction'] ?? '') === 'down'): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    <?php endif; ?>
                    <span><?= e($kpi['total_claims']['change'] ?? '0%') ?></span>
                </div>
            </div>
        </div>
        <?= $kpi['total_claims']['sparkline'] ?? '' ?>
    </div>

    <!-- 3. Klaim Disetujui -->
    <div class="kpi-card" style="cursor: pointer;" onclick="window.location.href='index.php?page=survey'">
        <div class="kpi-icon icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="kpi-info">
            <div class="kpi-title">Klaim Disetujui</div>
            <div class="kpi-value-row">
                <div class="kpi-value"><?= e($kpi['approved_claims']['formatted'] ?? '0') ?></div>
                <div class="kpi-trend <?= ($kpi['approved_claims']['is_positive'] ?? true) ? 'positive' : 'negative' ?>" title="Perubahan dibanding periode sebelumnya">
                    <?php if (($kpi['approved_claims']['direction'] ?? '') === 'up'): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    <?php elseif (($kpi['approved_claims']['direction'] ?? '') === 'down'): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    <?php endif; ?>
                    <span><?= e($kpi['approved_claims']['change'] ?? '0%') ?></span>
                </div>
            </div>
        </div>
        <?= $kpi['approved_claims']['sparkline'] ?? '' ?>
    </div>

    <!-- 4. Rata-rata Proses -->
    <div class="kpi-card" style="cursor: pointer;" onclick="window.location.href='index.php?page=survey'">
        <div class="kpi-icon icon-orange">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="kpi-info">
            <div class="kpi-title">Rata-rata Proses</div>
            <div class="kpi-value-row">
                <div class="kpi-value"><?= e($kpi['avg_process_days']['formatted'] ?? '0,0 Hari') ?></div>
                <div class="kpi-trend <?= ($kpi['avg_process_days']['is_positive'] ?? true) ? 'positive' : 'negative' ?>" title="Durasi lebih cepat bertanda hijau">
                    <?php if (($kpi['avg_process_days']['direction'] ?? '') === 'down'): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    <?php elseif (($kpi['avg_process_days']['direction'] ?? '') === 'up'): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    <?php endif; ?>
                    <span><?= e($kpi['avg_process_days']['change'] ?? '0%') ?></span>
                </div>
            </div>
        </div>
        <?= $kpi['avg_process_days']['sparkline'] ?? '' ?>
    </div>
</section>

<!-- Integrated Cross-Module Summary Widgets (Polis, Pengajuan Klaim, Hasil Survey) -->
<?php if (!empty($workflowSummary)): ?>
<section class="workflow-summary-grid">
    <!-- 1. Integrasi Data Polis -->
    <div class="card" style="padding: 16px 20px; border-left: 4px solid var(--primary-600);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 13px; font-weight: 800; color: var(--primary-900); display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span>Integrasi Data Polis</span>
            </div>
            <a href="index.php?page=policies" style="font-size: 11.5px; font-weight: 700; color: var(--primary-700);">Kelola &rarr;</a>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: var(--text-secondary); margin-bottom: 4px;">
            <span>Total Terdaftar:</span>
            <strong><?= number_format($workflowSummary['total_policies'], 0, ',', '.') ?> Polis</strong>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: var(--text-secondary); margin-bottom: 4px;">
            <span>Polis Aktif:</span>
            <span style="color: #15803d; font-weight: 700;"><?= number_format($workflowSummary['active_policies'], 0, ',', '.') ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: var(--text-secondary);">
            <span>Total Nilai Limit:</span>
            <strong style="color: var(--text-primary);"><?= format_rupiah($workflowSummary['total_coverage_limit']) ?></strong>
        </div>
    </div>

    <!-- 2. Integrasi Pengajuan Klaim -->
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 13px; font-weight: 800; color: #b45309; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                <span>Integrasi Pengajuan Klaim</span>
            </div>
            <a href="index.php?page=claims" style="font-size: 11.5px; font-weight: 700; color: #b45309;">Daftar &rarr;</a>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: var(--text-secondary); margin-bottom: 4px;">
            <span>Total Berkas Klaim:</span>
            <strong><?= number_format($workflowSummary['total_claims'], 0, ',', '.') ?> Berkas</strong>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: var(--text-secondary); margin-bottom: 4px;">
            <span>Survey Lapangan:</span>
            <span style="color: #0284c7; font-weight: 700;"><?= number_format($workflowSummary['survey_claims'], 0, ',', '.') ?> Klaim</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: var(--text-secondary);">
            <span>Total Estimasi Kerusakan:</span>
            <strong style="color: var(--text-primary);"><?= format_rupiah($workflowSummary['total_estimated_cost']) ?></strong>
        </div>
    </div>

    <!-- 3. Integrasi Hasil Survey & Persetujuan -->
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 13px; font-weight: 800; color: #065f46; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                <span>Integrasi Hasil Survey</span>
            </div>
            <a href="index.php?page=survey" style="font-size: 11.5px; font-weight: 700; color: #065f46;">Verifikasi &rarr;</a>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: var(--text-secondary); margin-bottom: 4px;">
            <span>Laporan Survey Fisik:</span>
            <strong><?= number_format($workflowSummary['total_survey_reports'], 0, ',', '.') ?> Laporan</strong>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: var(--text-secondary); margin-bottom: 4px;">
            <span>Klaim Disetujui:</span>
            <span style="color: #15803d; font-weight: 700;"><?= number_format($workflowSummary['approved_reports'], 0, ',', '.') ?> Klaim</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: var(--text-secondary);">
            <span>Nilai Klaim Disetujui:</span>
            <strong style="color: #15803d;"><?= format_rupiah($workflowSummary['total_approved_amount']) ?></strong>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Dashboard Main Grid (Status Klaim Terbaru & Grafik 6 Bulan) -->
<section class="dashboard-grid">
    <!-- Left Table: Status Klaim Terbaru -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                <h3>Status Klaim & Hasil Survey Terbaru</h3>
            </div>
            <a href="index.php?page=claims" class="card-link">Lihat Semua Klaim &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="table" id="main-data-table">
                <thead>
                    <tr>
                        <th>No. Klaim & Polis</th>
                        <th>Nasabah & Kendaraan</th>
                        <th>Estimasi / Survey</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentClaims)): ?>
                        <?php foreach ($recentClaims as $claim): ?>
                            <?php
                            $badgeClass = match($claim['status']) {
                                'Survey Lapangan' => 'badge-survey',
                                'Dalam Audit'     => 'badge-audit',
                                'Disetujui'       => 'badge-approved',
                                'Ditolak'         => 'badge-rejected',
                                default           => 'badge-new'
                            };
                            ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary-800); font-family: monospace;"><?= e($claim['claim_number']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);">
                                        Polis: <a href="index.php?page=policies&q=<?= urlencode($claim['policy_number']) ?>" style="color: var(--primary-700); font-weight: 600;"><?= e($claim['policy_number']) ?></a>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-primary);"><?= e($claim['customer_name']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= e($claim['vehicle_brand'] . ' ' . ($claim['vehicle_model'] ?? '') . ' (' . $claim['vehicle_plate'] . ')') ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #15803d; font-size: 12.5px;">
                                        <?= format_rupiah((float)($claim['approved_claim_amount'] ?? $claim['estimated_damage_cost'])) ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-muted);">
                                        <?= !empty($claim['damage_level']) ? 'Kerusakan: ' . e($claim['damage_level']) : 'Belum Survey' ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <span class="badge-dot"></span>
                                        <?= e($claim['status']) ?>
                                    </span>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                                        <?= date('d M Y', strtotime($claim['incident_date'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="index.php?page=survey&action=verify&id=<?= (int)$claim['id'] ?>" class="btn-icon" title="Verifikasi Survey & Persetujuan" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 36px 20px;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted);"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                                    <span>Belum ada data klaim yang tercatat di sistem.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Chart: Grafik Klaim 6 Bulan Terakhir -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                <h3>Grafik Klaim 6 Bulan Terakhir</h3>
            </div>
            <!-- Legend matching mockup -->
            <div style="display: flex; align-items: center; gap: 14px; font-size: 12px; font-weight: 600;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #a7d7b5;"></div>
                    <span style="color: var(--text-secondary);">Pengajuan</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #1b4433;"></div>
                    <span style="color: var(--text-secondary);">Disetujui</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Dynamic Data-Driven Container -->
            <div id="chart-claims-container" 
                 data-chart="<?= htmlspecialchars(json_encode($chartData), ENT_QUOTES, 'UTF-8') ?>"
                 style="width: 100%; height: 240px; position: relative;">
            </div>
        </div>
    </div>
</section>
