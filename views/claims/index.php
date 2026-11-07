<!-- Pengajuan Klaim Header & Breadcrumbs -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">
            <a href="index.php?page=dashboard">Dashboard</a> &rsaquo; <span style="color: var(--text-primary); font-weight: 600;">Pengajuan Klaim</span>
        </div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Daftar Pengajuan Klaim Kendaraan</h2>
    </div>

    <?php if (in_array($user['role'] ?? '', ['admin', 'customer'])): ?>
    <a href="index.php?page=claims&action=create" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>Ajukan Klaim Baru</span>
    </a>
    <?php endif; ?>
</div>

<!-- Filter Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
    <form method="GET" action="index.php" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;" class="filter-bar-flex">
        <input type="hidden" name="page" value="claims">

        <div style="flex-grow: 1; min-width: 260px;">
            <input type="text" name="q" class="form-control" placeholder="Cari nomor klaim, nasabah, polis, plat..." value="<?= e($search ?? '') ?>" data-live-search="#table-claims">
        </div>

        <div style="width: 200px;">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="Semua Status" <?= ($status ?? '') === 'Semua Status' ? 'selected' : '' ?>>Semua Status</option>
                <option value="Pengajuan Baru" <?= ($status ?? '') === 'Pengajuan Baru' ? 'selected' : '' ?>>Pengajuan Baru</option>
                <option value="Survey Lapangan" <?= ($status ?? '') === 'Survey Lapangan' ? 'selected' : '' ?>>Survey Lapangan</option>
                <option value="Dalam Audit" <?= ($status ?? '') === 'Dalam Audit' ? 'selected' : '' ?>>Dalam Audit</option>
                <option value="Disetujui" <?= ($status ?? '') === 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                <option value="Ditolak" <?= ($status ?? '') === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                <option value="Dibatalkan" <?= ($status ?? '') === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($search) || !empty($status)): ?>
            <a href="index.php?page=claims" class="btn btn-outline">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table" id="table-claims">
            <thead>
                <tr>
                    <th>No. Klaim</th>
                    <th>Nama Nasabah & Polis</th>
                    <th>Kendaraan Tertanggung</th>
                    <th>Estimasi Kerusakan</th>
                    <th>Berkas Foto</th>
                    <th>Status</th>
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
                            'Dibatalkan'      => 'badge-cancelled',
                            default           => 'badge-new'
                        };

                        $isOwnerOrAdmin = (($user['role'] ?? '') !== 'customer') || ((int)$c['submitted_by'] === (int)$user['id']);
                        $isInitialStage = in_array($c['status'], ['Pengajuan Baru', 'Survey Lapangan'], true) && empty($c['damage_level']) && empty($c['survey_date']);
                        $canEdit = $isOwnerOrAdmin && $isInitialStage;
                        $canCancel = $isOwnerOrAdmin && in_array($c['status'], ['Pengajuan Baru', 'Survey Lapangan'], true) && empty($c['survey_date']);
                        ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary-800); font-family: monospace; font-size: 14px;"><?= e($c['claim_number']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= date('d M Y', strtotime($c['incident_date'])) ?> &bull; <?= e($c['incident_time']) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 700;"><?= e($c['customer_name']) ?></div>
                                <div style="font-size: 11.5px; color: var(--primary-600); font-family: monospace;"><?= e($c['policy_number']) ?></div>
                            </td>
                            <td>
                                <div><?= e($c['vehicle_brand'] . ' ' . ($c['vehicle_model'] ?? '')) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;"><?= e($c['vehicle_plate']) ?></div>
                            </td>
                            <td>
                                <strong style="color: #15803d;">Rp <?= number_format($c['estimated_damage_cost'], 0, ',', '.') ?></strong>
                                <?php if (!empty($c['approved_claim_amount'])): ?>
                                    <div style="font-size: 11px; color: var(--text-muted);">Disetujui: Rp <?= number_format($c['approved_claim_amount'], 0, ',', '.') ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--primary-soft); color: var(--primary-700); border: 1px solid var(--primary-200);">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    <?= (int)($c['files_count'] ?? 0) ?> foto
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?>">
                                    <span class="badge-dot"></span>
                                    <?= e($c['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <?php if (($user['role'] ?? '') !== 'customer' && !in_array($c['status'], ['Dibatalkan', 'Disetujui', 'Ditolak'])): ?>
                                    <a href="index.php?page=survey&action=verify&id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-secondary" title="Surveyor & Verifikasi">
                                        Survey / Audit
                                    </a>
                                    <?php endif; ?>

                                    <!-- Tombol Detail -->
                                    <a href="index.php?page=claims&action=detail&id=<?= (int)$c['id'] ?>" class="btn-icon" title="Lihat Detail Klaim">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </a>

                                    <!-- Tombol Edit (Hanya jika eligible) -->
                                    <?php if ($canEdit): ?>
                                    <a href="index.php?page=claims&action=edit&id=<?= (int)$c['id'] ?>" class="btn-icon" title="Ubah Pengajuan Klaim" style="color: var(--primary-700);">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                    </a>
                                    <?php endif; ?>

                                    <!-- Tombol Batalkan (Hanya jika eligible) -->
                                    <?php if ($canCancel): ?>
                                    <button type="button" class="btn-icon" title="Batalkan Pengajuan Klaim" style="color: #dc2626;" onclick="openCancelClaimModal(<?= (int)$c['id'] ?>, '<?= e($c['claim_number']) ?>')">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                    </button>
                                    <?php endif; ?>

                                    <!-- Indikator Terkunci jika tidak dapat diedit/dibatalkan -->
                                    <?php if (!$canEdit && !$canCancel): ?>
                                    <span class="btn-icon" title="Klaim telah diproses/terkunci (tidak dapat diubah atau dibatalkan)" style="opacity: 0.35; cursor: not-allowed; color: var(--text-muted);">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 36px;">
                            Tidak ada pengajuan klaim yang sesuai kriteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Dialog Konfirmasi Pembatalan Klaim (CLAIMORA Modern Dialog) -->
<div id="modal-cancel-claim" class="modal-backdrop">
    <div class="modal" style="max-width: 480px;">
        <div class="modal-header">
            <h3 style="color: #b91c1c; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <span>Konfirmasi Pembatalan Klaim</span>
            </h3>
            <button type="button" class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="index.php?page=claims&action=cancel" id="form-cancel-claim">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="id" id="cancel-claim-id" value="">

            <div class="modal-body">
                <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 16px; color: #991b1b; font-size: 13px; line-height: 1.5;">
                    Apakah Anda yakin ingin membatalkan pengajuan klaim <strong id="cancel-claim-number" style="font-family: monospace; font-size: 13.5px;">-</strong>?
                    <div style="margin-top: 6px; font-size: 12px; color: #7f1d1d;">
                        Status klaim akan diubah menjadi <strong>Dibatalkan</strong>. Tindakan ini tidak menghapus riwayat klaim Anda, namun pengajuan ini tidak akan diproses lebih lanjut oleh surveyor.
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="cancel-reason">Alasan Pembatalan (Opsional)</label>
                    <textarea name="cancellation_reason" id="cancel-reason" class="form-control" rows="3" placeholder="Contoh: Salah memasukkan data kerusakan, kendaraan sudah diperbaiki mandiri, dll..."></textarea>
                    <div class="form-hint">Alasan pembatalan akan dicatat ke dalam rekam jejak audit sistem.</div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" data-modal-close style="font-weight: 600;">
                    Kembali
                </button>
                <button type="submit" class="btn btn-primary" style="background-color: #b91c1c; border-color: #b91c1c; font-weight: 600;">
                    Ya, Batalkan Klaim
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCancelClaimModal(id, claimNumber) {
    document.getElementById('cancel-claim-id').value = id;
    document.getElementById('cancel-claim-number').textContent = claimNumber;
    document.getElementById('cancel-reason').value = '';
    if (typeof openModal === 'function') {
        openModal('modal-cancel-claim');
    } else {
        const m = document.getElementById('modal-cancel-claim');
        if (m) m.classList.add('active');
    }
}
</script>
