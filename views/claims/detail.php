<!-- Detail Klaim Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">
            <a href="index.php?page=dashboard">Dashboard</a> &rsaquo; <a href="index.php?page=claims">Pengajuan Klaim</a> &rsaquo; <span style="color: var(--text-primary); font-weight: 600;"><?= e($claim['claim_number']) ?></span>
        </div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Detail Klaim: <?= e($claim['claim_number']) ?></h2>
    </div>

    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <?php
        $isOwnerOrAdmin = (($user['role'] ?? '') !== 'customer') || ((int)$claim['submitted_by'] === (int)$user['id']);
        $canEdit = $isOwnerOrAdmin && in_array($claim['status'], ['Pengajuan Baru', 'Survey Lapangan'], true) && empty($claim['damage_level']) && empty($claim['survey_date']);
        $canCancel = $isOwnerOrAdmin && in_array($claim['status'], ['Pengajuan Baru', 'Survey Lapangan'], true) && empty($claim['survey_date']);
        ?>

        <?php if ($canEdit): ?>
            <a href="index.php?page=claims&action=edit&id=<?= (int)$claim['id'] ?>" class="btn btn-outline" style="font-weight: 600;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                <span>Ubah Data Klaim</span>
            </a>
        <?php endif; ?>

        <?php if ($canCancel): ?>
            <button type="button" class="btn btn-outline" style="color: #dc2626; border-color: #fca5a5; font-weight: 600;" onclick="openCancelClaimModal(<?= (int)$claim['id'] ?>, '<?= e($claim['claim_number']) ?>')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <span>Batalkan Klaim</span>
            </button>
        <?php endif; ?>

        <?php if (($user['role'] ?? '') !== 'customer' && !in_array($claim['status'], ['Dibatalkan', 'Disetujui', 'Ditolak'])): ?>
            <a href="index.php?page=survey&action=verify&id=<?= (int)$claim['id'] ?>" class="btn btn-primary">
                <span>Buka Survey & Persetujuan</span> &rarr;
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="survey-split-view">
    <!-- Left Column: Claim Data & Photo Gallery -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <?php if ($claim['status'] === 'Dibatalkan'): ?>
            <!-- Banner Informasi Pembatalan Klaim -->
            <div style="background-color: #fef2f2; border: 1.5px solid #fecaca; border-radius: var(--radius-lg); padding: 18px 20px; color: #991b1b;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    </div>
                    <div style="flex-grow: 1;">
                        <strong style="font-size: 15px; display: block; margin-bottom: 4px; color: #991b1b;">Pengajuan Klaim Ini Telah Dibatalkan</strong>
                        <p style="font-size: 13px; line-height: 1.5; margin: 0; color: #7f1d1d;">
                            Klaim dibatalkan pada: <strong><?= !empty($claim['cancelled_at']) ? date('d F Y, H:i', strtotime($claim['cancelled_at'])) . ' WIB' : '-' ?></strong>
                            <?= !empty($claim['canceller_name']) ? 'oleh <strong>' . e($claim['canceller_name']) . '</strong>' : '' ?>.
                        </p>
                        <?php if (!empty($claim['cancellation_reason'])): ?>
                            <div style="margin-top: 10px; font-size: 12.5px; background: #ffffff; padding: 10px 14px; border-radius: 6px; border: 1px dashed #fca5a5; color: #7f1d1d;">
                                <strong style="display: block; margin-bottom: 2px;">Alasan Pembatalan:</strong>
                                <?= nl2br(e($claim['cancellation_reason'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Card 1: Data Ringkasan -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg>
                    <h3 style="font-size: 15px;">Informasi Kejadian & Polis</h3>
                </div>
                <?php
                $badgeClass = match($claim['status']) {
                    'Survey Lapangan' => 'badge-survey',
                    'Dalam Audit'     => 'badge-audit',
                    'Disetujui'       => 'badge-approved',
                    'Ditolak'         => 'badge-rejected',
                    'Dibatalkan'      => 'badge-cancelled',
                    default           => 'badge-new'
                };
                ?>
                <span class="badge <?= $badgeClass ?>">
                    <span class="badge-dot"></span>
                    <?= e($claim['status']) ?>
                </span>
            </div>
            <div class="card-body detail-info-grid">
                <div>
                    <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Nomor Polis</span>
                    <strong style="color: var(--primary-700); font-family: monospace; font-size: 14px;"><?= e($claim['policy_number']) ?></strong>
                </div>
                <div>
                    <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Nama Tertanggung</span>
                    <strong style="font-size: 14px;"><?= e($claim['customer_name']) ?></strong>
                </div>
                <div>
                    <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Kendaraan Tertanggung</span>
                    <span style="font-size: 13.5px; font-weight: 600;"><?= e($claim['vehicle_brand'] . ' ' . ($claim['vehicle_model'] ?? '')) ?></span>
                    <div style="font-size: 11.5px; color: var(--text-muted);"><?= e($claim['vehicle_plate']) ?></div>
                </div>
                <div>
                    <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Batas Pertanggungan</span>
                    <span style="font-size: 14px; font-weight: 700; color: #15803d;">Rp <?= number_format($claim['coverage_amount'], 0, ',', '.') ?></span>
                </div>
                <div>
                    <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Tanggal & Waktu Kejadian</span>
                    <span style="font-size: 13.5px;"><?= date('d F Y', strtotime($claim['incident_date'])) ?> &bull; <?= e($claim['incident_time']) ?></span>
                </div>
                <div>
                    <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Estimasi Biaya Klaim</span>
                    <strong style="font-size: 15px; color: #15803d;">Rp <?= number_format($claim['estimated_damage_cost'], 0, ',', '.') ?></strong>
                </div>
                <div style="grid-column: 1 / -1;">
                    <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Lokasi Kejadian</span>
                    <span style="font-size: 13.5px; color: var(--text-primary);"><?= e($claim['incident_location']) ?></span>
                </div>
                <div style="grid-column: 1 / -1;">
                    <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Kronologi Kejadian</span>
                    <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-top: 4px; background: var(--primary-soft); padding: 12px; border-radius: var(--radius-md);">
                        <?= nl2br(e($claim['chronology'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 2: Foto Bukti Kerusakan -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <h3 style="font-size: 15px;">Bukti Foto Kerusakan Kendaraan</h3>
                </div>
                <span style="font-size: 12px; color: var(--text-muted);"><?= count($claim['files']) ?> foto terlampir</span>
            </div>
            <div class="card-body">
                <?php if (!empty($claim['files'])): ?>
                    <div class="survey-photo-grid">
                        <?php foreach ($claim['files'] as $f): ?>
                            <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; background: #fff; box-shadow: var(--shadow-sm);">
                                <div style="aspect-ratio: 4/3; background: #000; overflow: hidden;">
                                    <img src="<?= e($f['file_path']) ?>" alt="<?= e($f['original_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div style="padding: 10px 12px; font-size: 11.5px;">
                                    <strong style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-primary);"><?= e($f['original_name']) ?></strong>
                                    <span style="color: var(--text-muted);"><?= round($f['file_size'] / 1024, 1) ?> KB &bull; <?= e($f['mime_type']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; color: var(--text-muted); padding: 30px;">
                        Belum ada foto kerusakan yang diunggah untuk pengajuan klaim ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Status Verifikasi Surveyor -->
    <div>
        <div class="card" style="position: sticky; top: 90px;">
            <div class="card-header" style="background-color: var(--primary-soft);">
                <div class="card-header-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline></svg>
                    <h3 style="font-size: 15px;">Status Surveyor & Audit</h3>
                </div>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Surveyor Ditugaskan</span>
                    <strong style="font-size: 14px;"><?= e($claim['surveyor_name'] ?? 'Menunggu Penugasan Surveyor') ?></strong>
                </div>

                <?php if (!empty($claim['survey_date'])): ?>
                    <div>
                        <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Tanggal & Lokasi Survey</span>
                        <div style="font-size: 13.5px;"><?= date('d F Y', strtotime($claim['survey_date'])) ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);"><?= e($claim['survey_location'] ?? '-') ?></div>
                    </div>

                    <div>
                        <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Tingkat Kerusakan</span>
                        <span class="badge badge-audit" style="margin-top: 4px;"><?= e($claim['damage_level'] ?? 'Sedang') ?></span>
                    </div>

                    <div>
                        <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Estimasi Biaya Surveyor</span>
                        <strong style="font-size: 15px; color: #15803d;">Rp <?= number_format($claim['surveyor_estimated_cost'] ?? 0, 0, ',', '.') ?></strong>
                    </div>
                <?php endif; ?>

                <?php if ($claim['approval_status'] === 'Ditolak'): ?>
                    <div style="border-top: 1px dashed var(--border-color); padding-top: 14px;">
                        <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Keputusan Klaim</span>
                        <strong style="font-size: 18px; color: #b91c1c; font-weight: 800;">Ditolak</strong>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                            Ditolak oleh: <strong><?= e($claim['auditor_name'] ?? 'Auditor Asuransi') ?></strong>
                        </div>
                    </div>
                <?php elseif ($claim['approval_status'] === 'Disetujui'): ?>
                    <div style="border-top: 1px dashed var(--border-color); padding-top: 14px;">
                        <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Nilai Klaim yang Disetujui</span>
                        <strong style="font-size: 18px; color: #15803d; font-weight: 800;">Rp <?= number_format($claim['approved_claim_amount'] ?? 0, 0, ',', '.') ?></strong>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                            Disetujui oleh: <strong><?= e($claim['auditor_name'] ?? 'Auditor Asuransi') ?></strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($claim['status'] === 'Dibatalkan'): ?>
                    <div style="border-top: 1px dashed var(--border-color); padding-top: 14px;">
                        <span style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Status Klaim</span>
                        <strong style="font-size: 18px; color: #64748b; font-weight: 800;">Dibatalkan</strong>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                            Pengajuan klaim ini telah dibatalkan dan tidak diproses lebih lanjut.
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (($user['role'] ?? '') !== 'customer' && !in_array($claim['status'], ['Dibatalkan', 'Disetujui', 'Ditolak'])): ?>
                <div style="margin-top: 10px;">
                    <a href="index.php?page=survey&action=verify&id=<?= (int)$claim['id'] ?>" class="btn btn-primary" style="width: 100%;">
                        Form Persetujuan Biaya Klaim &rarr;
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
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
            <input type="hidden" name="id" id="cancel-claim-id" value="<?= (int)$claim['id'] ?>">

            <div class="modal-body">
                <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 16px; color: #991b1b; font-size: 13px; line-height: 1.5;">
                    Apakah Anda yakin ingin membatalkan pengajuan klaim <strong id="cancel-claim-number" style="font-family: monospace; font-size: 13.5px;"><?= e($claim['claim_number']) ?></strong>?
                    <div style="margin-top: 6px; font-size: 12px; color: #7f1d1d;">
                        Status klaim akan diubah menjadi <strong>Dibatalkan</strong>. Pengajuan ini tidak akan diproses lebih lanjut oleh tim surveyor dan auditor.
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
