<?php
$page = $page ?? 1;
$perPage = $perPage ?? 10;
$offset = $offset ?? (($page - 1) * $perPage);
$totalRows = $totalRows ?? 0;
$totalPages = $totalPages ?? 1;
$modules = $modules ?? [];
$userNames = $userNames ?? [];
$logs = $logs ?? [];
?>
<!-- Audit Log Header Banner (Matching Image 2) -->
<section class="welcome-card" style="margin-bottom: 24px; padding: 24px 30px;">
    <div style="display: flex; align-items: center; gap: 20px;">
        <div style="width: 58px; height: 58px; border-radius: var(--radius-lg); background: #ffffff; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--primary-700); box-shadow: var(--shadow-sm); flex-shrink: 0;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <circle cx="12" cy="14" r="4"></circle>
                <polyline points="12 12 12 14 14 14"></polyline>
            </svg>
        </div>
        <div>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--primary-900); line-height: 1.2;">Audit Log</h2>
            <p style="font-size: 13.5px; color: var(--text-secondary); margin-top: 4px; margin-bottom: 0;">
                Riwayat aktivitas pengguna, keamanan transaksi, dan jejak rekam sistem CLAIMORA.
            </p>
        </div>
    </div>

    <!-- Background graphic accent -->
    <div class="welcome-graphic" style="max-width: 140px;">
        <svg viewBox="0 0 160 120" fill="none">
            <path d="M80 15 L120 32 V60 C120 88 102 105 80 114 C58 105 40 88 40 60 V32 Z" fill="#2d6a4f" opacity="0.85"/>
            <path d="M80 22 L112 36 V58 C112 82 98 96 80 104 C62 96 48 82 48 58 V36 Z" fill="#52b788"/>
            <polyline points="65,60 75,70 95,48" stroke="#ffffff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
</section>

<!-- Filter Controls Bar (Matching Image 2) -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
    <form method="GET" action="index.php" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;" class="filter-bar-flex">
        <input type="hidden" name="page" value="audit">

        <!-- Rentang Tanggal -->
        <div style="min-width: 200px; flex-grow: 1;">
            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase;">Rentang Tanggal</div>
            <div class="audit-date-range">
                <input type="date" name="start_date" class="form-control" value="<?= e($startDate ?? '') ?>" style="font-size: 12.5px; min-width: 0;">
                <input type="date" name="end_date" class="form-control" value="<?= e($endDate ?? '') ?>" style="font-size: 12.5px; min-width: 0;">
            </div>
        </div>

        <!-- Filter Modul -->
        <div style="min-width: 180px;">
            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase;">Modul</div>
            <select name="module" class="form-control" style="font-size: 13px;">
                <option value="Semua Modul" <?= ($module ?? '') === 'Semua Modul' ? 'selected' : '' ?>>Semua Modul</option>
                <?php foreach ($modules as $mod): ?>
                    <option value="<?= e($mod) ?>" <?= ($module ?? '') === $mod ? 'selected' : '' ?>><?= e($mod) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Pengguna -->
        <div style="min-width: 180px;">
            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase;">Pengguna</div>
            <select name="user_name" class="form-control" style="font-size: 13px;">
                <option value="Semua Pengguna" <?= ($userName ?? '') === 'Semua Pengguna' ? 'selected' : '' ?>>Semua Pengguna</option>
                <?php foreach ($userNames as $u): ?>
                    <option value="<?= e($u) ?>" <?= ($userName ?? '') === $u ? 'selected' : '' ?>><?= e($u) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="align-self: flex-end;">
            <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 20px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <span>Cari</span>
            </button>
        </div>

        <?php if (!empty($startDate) || !empty($endDate) || (!empty($module) && $module !== 'Semua Modul') || (!empty($userName) && $userName !== 'Semua Pengguna')): ?>
            <div style="align-self: flex-end;">
                <a href="index.php?page=audit" class="btn btn-outline" style="height: 42px;">Reset</a>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Audit Log Table Card (Matching Image 2) -->
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <h3>Daftar Audit Log</h3>
        </div>

        <!-- Export Log Button -->
        <div class="audit-export-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="index.php?page=audit&action=export&format=csv&start_date=<?= urlencode($startDate ?? '') ?>&end_date=<?= urlencode($endDate ?? '') ?>&module=<?= urlencode($module ?? '') ?>&user_name=<?= urlencode($userName ?? '') ?>" class="btn btn-outline" title="Unduh format CSV / Excel">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Export Log (CSV)</span>
            </a>
            <a href="index.php?page=audit&action=export&format=json&start_date=<?= urlencode($startDate ?? '') ?>&end_date=<?= urlencode($endDate ?? '') ?>&module=<?= urlencode($module ?? '') ?>&user_name=<?= urlencode($userName ?? '') ?>" class="btn btn-outline" title="Unduh format JSON">
                <span>JSON</span>
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table" id="table-audit-log">
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th>Tanggal & Waktu</th>
                    <th>Pengguna</th>
                    <th>Modul</th>
                    <th>Aktivitas</th>
                    <th>Detail</th>
                    <th>IP Address</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php
                    $offsetCount = ($page - 1) * $perPage;
                    foreach ($logs as $i => $log):
                        // Color badges for modules matching Image 2
                        $moduleBadgeStyle = match($log['module']) {
                            'Pengajuan Klaim' => 'background: #eaf7f0; color: #15803d; border: 1px solid #bbf7d0;',
                            'Data Polis'      => 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;',
                            'Hasil Survey'    => 'background: #faf5ff; color: #7e22ce; border: 1px solid #e9d5ff;',
                            'Audit Log'       => 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
                            'Pengaturan'      => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                            default           => 'background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb;'
                        };
                    ?>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 600;"><?= $offsetCount + $i + 1 ?></td>
                            <td style="white-space: nowrap; font-size: 13px;">
                                <?= date('d M Y', strtotime($log['created_at'])) ?>
                                <span style="color: var(--text-muted); font-size: 11.5px;"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                            </td>
                            <td>
                                <strong><?= e($log['user_name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge" style="<?= $moduleBadgeStyle ?>">
                                    <?= e($log['module']) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: var(--text-primary); font-size: 13px;"><?= e($log['activity']) ?></strong>
                            </td>
                            <td style="max-width: 380px; font-size: 13px; line-height: 1.4;">
                                <?= e($log['detail']) ?>
                            </td>
                            <td style="font-family: monospace; font-size: 12px; color: var(--text-secondary);">
                                <?= e($log['ip_address']) ?>
                            </td>
                            <td>
                                <span class="badge <?= $log['status'] === 'Berhasil' ? 'badge-approved' : 'badge-rejected' ?>">
                                    <span class="badge-dot"></span>
                                    <?= e($log['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 36px;">
                            Tidak ada riwayat aktivitas yang sesuai filter.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer (Matching Image 2) -->
    <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div style="font-size: 13px; color: var(--text-secondary);">
            Menampilkan <strong><?= count($logs) > 0 ? ($offset + 1) : 0 ?>–<?= min($offset + $perPage, $totalRows) ?></strong> dari <strong><?= $totalRows ?></strong> data
        </div>

        <!-- Pagination Buttons -->
        <div style="display: flex; gap: 4px; align-items: center;">
            <?php if ($page > 1): ?>
                <a href="index.php?page=audit&p=<?= $page - 1 ?>&start_date=<?= urlencode($startDate ?? '') ?>&end_date=<?= urlencode($endDate ?? '') ?>&module=<?= urlencode($module ?? '') ?>&user_name=<?= urlencode($userName ?? '') ?>" class="btn-icon" title="Halaman Sebelumnya">&larr;</a>
            <?php endif; ?>

            <?php for ($p = 1; $p <= min(5, $totalPages); $p++): ?>
                <a href="index.php?page=audit&p=<?= $p ?>&start_date=<?= urlencode($startDate ?? '') ?>&end_date=<?= urlencode($endDate ?? '') ?>&module=<?= urlencode($module ?? '') ?>&user_name=<?= urlencode($userName ?? '') ?>" class="btn-icon <?= $p === $page ? 'active' : '' ?>" style="<?= $p === $page ? 'background: var(--primary-700); color: #fff; border-color: var(--primary-700); font-weight: 700;' : '' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>

            <?php if ($totalPages > 5): ?>
                <span style="padding: 0 4px; color: var(--text-muted);">&hellip;</span>
                <a href="index.php?page=audit&p=<?= $totalPages ?>&start_date=<?= urlencode($startDate ?? '') ?>&end_date=<?= urlencode($endDate ?? '') ?>&module=<?= urlencode($module ?? '') ?>&user_name=<?= urlencode($userName ?? '') ?>" class="btn-icon <?= $totalPages === $page ? 'active' : '' ?>">
                    <?= $totalPages ?>
                </a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="index.php?page=audit&p=<?= $page + 1 ?>&start_date=<?= urlencode($startDate ?? '') ?>&end_date=<?= urlencode($endDate ?? '') ?>&module=<?= urlencode($module ?? '') ?>&user_name=<?= urlencode($userName ?? '') ?>" class="btn-icon" title="Halaman Berikutnya">&rarr;</a>
            <?php endif; ?>
        </div>
    </div>
</div>
