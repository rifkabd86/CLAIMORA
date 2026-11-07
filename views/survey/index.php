<!-- Hasil Survey List Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">
            <a href="index.php?page=dashboard">Dashboard</a> &rsaquo; <span style="color: var(--text-primary); font-weight: 600;">Hasil Survey & Persetujuan</span>
        </div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Verifikasi Surveyor Lapangan & Persetujuan Biaya Klaim</h2>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="font-size: 15px; font-weight: 700;">Daftar Klaim untuk Survey & Audit</h3>
        <span style="font-size: 12px; color: var(--text-muted);"><?= count($claims) ?> Pengajuan Klaim</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Klaim</th>
                    <th>Nama Nasabah</th>
                    <th>Kendaraan Tertanggung</th>
                    <th>Tingkat Kerusakan</th>
                    <th>Estimasi Biaya</th>
                    <th>Status Survey & Klaim</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($claims)): ?>
                    <?php foreach ($claims as $c): ?>
                        <?php
                        $badgeClass = match($c['status']) {
                            'Survey Lapangan' => 'badge-survey',
                            'Dalam Audit'     => 'badge-audit',
                            'Disetujui'       => 'badge-approved',
                            'Ditolak'         => 'badge-rejected',
                            default           => 'badge-new'
                        };
                        ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary-800); font-family: monospace;"><?= e($c['claim_number']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= date('d M Y', strtotime($c['incident_date'])) ?></div>
                            </td>
                            <td>
                                <div><strong><?= e($c['customer_name']) ?></strong></div>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= e($c['policy_number']) ?></div>
                            </td>
                            <td>
                                <div><?= e($c['vehicle_brand'] . ' ' . ($c['vehicle_model'] ?? '')) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;"><?= e($c['vehicle_plate']) ?></div>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--primary-soft); color: var(--text-primary); border: 1px solid var(--border-color);">
                                    <?= e($c['damage_level'] ?? 'Belum Disurvey') ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: #15803d;">Rp <?= number_format($c['estimated_damage_cost'], 0, ',', '.') ?></strong>
                                <?php if (!empty($c['approved_claim_amount'])): ?>
                                    <div style="font-size: 11px; color: var(--primary-600); font-weight: 600;">Disetujui: Rp <?= number_format($c['approved_claim_amount'], 0, ',', '.') ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?>">
                                    <span class="badge-dot"></span>
                                    <?= e($c['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?page=survey&action=verify&id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-primary">
                                    Verifikasi & Setujui &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            Tidak ada data klaim untuk verifikasi survey.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
