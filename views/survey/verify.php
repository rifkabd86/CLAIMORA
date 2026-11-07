<!-- Hasil Survey & Persetujuan Header & Breadcrumb (Matching Image 1) -->
<div style="margin-bottom: 24px;">
    <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">
        <a href="index.php?page=dashboard">Dashboard</a> &rsaquo; <a href="index.php?page=survey">Hasil Survey</a> &rsaquo; <span style="color: var(--text-primary); font-weight: 600;">Verifikasi & Persetujuan</span>
    </div>
    <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Hasil Survey Kerusakan & Persetujuan Nilai Klaim</h2>
</div>

<!-- Header Vehicle Summary Card (Matching Image 1) -->
<div class="card survey-header-card" style="margin-bottom: 24px; padding: 20px 24px;">
    <div class="survey-summary-flex">
        <div class="survey-vehicle-left">
            <!-- Car Image Thumbnail -->
            <div class="survey-vehicle-thumb">
                <?php if (!empty($claim['files'][0]['file_path'])): ?>
                    <img src="<?= e($claim['files'][0]['file_path']) ?>" alt="Kendaraan Klaim" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                <?php else: ?>
                    <svg viewBox="0 0 160 100" style="width: 100%; height: auto;">
                        <ellipse cx="80" cy="85" rx="60" ry="8" fill="#cde3d6"/>
                        <path d="M20 70 L35 50 Q55 35 85 35 L120 35 Q135 40 142 55 L150 68 Z" fill="#ffffff" stroke="#2d6a4f" stroke-width="2"/>
                        <circle cx="45" cy="74" r="12" fill="#2d3748"/>
                        <circle cx="125" cy="74" r="12" fill="#2d3748"/>
                    </svg>
                <?php endif; ?>
            </div>

            <div style="min-width: 0;">
                <div style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">No. Klaim</div>
                <div style="font-size: 20px; font-weight: 800; color: var(--primary-800); font-family: monospace; line-height: 1.2; word-break: break-word;">
                    <?= e($claim['claim_number']) ?>
                </div>
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-top: 4px;">
                    <?= e($claim['customer_name']) ?>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); word-break: break-word;">
                    <?= e($claim['vehicle_brand'] . ' ' . ($claim['vehicle_model'] ?? '') . ' (' . $claim['vehicle_plate'] . ')') ?> &bull; Polis: <strong><?= e($claim['policy_number']) ?></strong>
                </div>
            </div>
        </div>

        <div class="survey-status-right">
            <div style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Status</div>
            <?php
            $isSurveyDone = !empty($survey['survey_date']);
            $surveyStatusBadge = $isSurveyDone ? 'badge-approved' : 'badge-survey';
            $surveyStatusLabel = $isSurveyDone ? 'Survey Selesai' : 'Perlu Survey Lapangan';
            ?>
            <span class="badge <?= $surveyStatusBadge ?>" style="font-size: 13px; padding: 6px 14px;">
                <span class="badge-dot"></span>
                <?= e($surveyStatusLabel) ?>
            </span>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">
                Tanggal Survey: <strong><?= !empty($survey['survey_date']) ? date('d M Y', strtotime($survey['survey_date'])) : 'Menunggu Jadwal' ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- Two-Column Layout (Form with Tabs on Left + Informasi Klaim & Jejak Audit on Right) -->
<div class="survey-split-view">
    <!-- Left Column: Tabs Navigation & Panels -->
    <div class="card survey-workstation-main">
        <div class="card-header" style="padding-bottom: 0; border-bottom: none;">
            <div class="tabs-nav" style="margin-bottom: 0; width: 100%; overflow-x: auto;">
                <button type="button" class="tab-btn" id="tab-btn-survey" onclick="switchTab('survey')">Hasil Survey</button>
                <button type="button" class="tab-btn active" id="tab-btn-approval" onclick="switchTab('approval')">Persetujuan Klaim</button>
                <button type="button" class="tab-btn" id="tab-btn-audit" onclick="switchTab('audit')">Audit Log</button>
            </div>
        </div>

        <div class="card-body">
            <!-- TAB 1: HASIL SURVEY LAPANGAN -->
            <div id="tab-pane-survey" style="display: none;">
                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 16px; color: var(--primary-900);">Laporan Temuan Surveyor Lapangan</h3>

                <form method="POST" action="index.php?page=survey&action=save_survey">
                    <?= \Core\Security::csrfField() ?>
                    <input type="hidden" name="claim_id" value="<?= (int)$claim['id'] ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Petugas Pemeriksa / Admin <span class="required">*</span></label>
                            <select name="surveyor_id" class="form-control" required>
                                <?php foreach ($surveyors as $sur): ?>
                                    <option value="<?= (int)$sur['id'] ?>" <?= (($survey['surveyor_id'] ?? 0) == $sur['id']) ? 'selected' : '' ?>>
                                        <?= e($sur['name']) ?> (<?= e($sur['role_label'] ?? 'Admin Asuransi') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Pelaksanaan Survey <span class="required">*</span></label>
                            <input type="date" name="survey_date" class="form-control" value="<?= e($survey['survey_date'] ?? date('Y-m-d')) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Lokasi Pemeriksaan Fisik <span class="required">*</span></label>
                            <input type="text" name="survey_location" class="form-control" placeholder="Nama Bengkel / Alamat Inspeksi Fisik" value="<?= e($survey['survey_location'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tingkat Kerusakan Kendaraan <span class="required">*</span></label>
                            <select name="damage_level" class="form-control" required>
                                <option value="Ringan" <?= (($survey['damage_level'] ?? '') === 'Ringan') ? 'selected' : '' ?>>Ringan (Baret / Denting Minor)</option>
                                <option value="Sedang" <?= (($survey['damage_level'] ?? 'Sedang') === 'Sedang') ? 'selected' : '' ?>>Sedang (Bumper, Kap Mesin, Pintu)</option>
                                <option value="Berat / Total Loss" <?= (($survey['damage_level'] ?? '') === 'Berat / Total Loss') ? 'selected' : '' ?>>Berat / Total Loss (&ge; 75%)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Estimasi Biaya Perbaikan Menurut Surveyor (Rp) <span class="required">*</span></label>
                        <input type="text" name="surveyor_estimated_cost" class="form-control" value="<?= isset($survey['surveyor_estimated_cost']) ? 'Rp ' . number_format($survey['surveyor_estimated_cost'], 0, ',', '.') : 'Rp ' . number_format($claim['estimated_damage_cost'], 0, ',', '.') ?>" data-rupiah required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Catatan Temuan Fisik Surveyor <span class="required">*</span></label>
                        <textarea name="surveyor_notes" class="form-control" rows="4" placeholder="Tuliskan temuan komponen mobil yang rusak, estimasi penggantian suku cadang asli, dan rekomendasi perbaikan..." required><?= e($survey['surveyor_notes'] ?? '') ?></textarea>
                    </div>

                    <?php if (in_array($claim['status'], ['Disetujui', 'Ditolak'], true)): ?>
                    <span style="color: var(--text-muted); font-size: 13px; font-style: italic;">Klaim sudah <?= e($claim['status']) ?>, laporan survey tidak dapat diubah lagi.</span>
                    <?php else: ?>
                    <div class="survey-form-actions">
                        <button type="submit" class="btn btn-primary">
                            Simpan Laporan Surveyor &rarr;
                        </button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- TAB 2: PERSETUJUAN KLAIM (Matching Image 1) -->
            <div id="tab-pane-approval">
                <form method="POST" action="index.php?page=survey&action=approve" id="form-approve-claim">
                    <?= \Core\Security::csrfField() ?>
                    <input type="hidden" name="claim_id" value="<?= (int)$claim['id'] ?>">

                    <!-- Nilai Klaim yang Disetujui -->
                    <div class="form-group">
                        <label class="form-label">Nilai Klaim yang Disetujui</label>
                        <?php
                        $defaultApprovedAmount = !empty($survey['approved_claim_amount']) 
                            ? $survey['approved_claim_amount'] 
                            : (!empty($survey['surveyor_estimated_cost']) ? $survey['surveyor_estimated_cost'] : $claim['estimated_damage_cost']);
                        ?>
                        <input type="text" name="approved_claim_amount" id="input-approved-amount" class="form-control" value="Rp <?= number_format($defaultApprovedAmount, 0, ',', '.') ?>" data-rupiah required style="font-size: 16px; font-weight: 700; color: #15803d;">
                        <div class="form-hint">Maksimal pertanggungan polis ini: Rp <?= number_format($claim['coverage_amount'], 0, ',', '.') ?></div>
                    </div>

                    <!-- Catatan Auditor -->
                    <div class="form-group">
                        <label class="form-label">Catatan Auditor</label>
                        <textarea name="auditor_notes" class="form-control" rows="3" placeholder="Masukkan alasan atau persetujuan klaim..."><?= e($survey['auditor_notes'] ?? '') ?></textarea>
                    </div>

                    <!-- Checkbox: Setujui Klaim -->
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13.5px; font-weight: 600; color: var(--text-primary);">
                            <input type="checkbox" name="agree_approval" value="1" <?= (($survey['approval_status'] ?? '') === 'Disetujui') ? 'checked' : '' ?>>
                            <span>Setujui Klaim</span>
                        </label>
                    </div>

                    <!-- Action Buttons -->
                    <div class="survey-approval-actions">
                        <button type="submit" name="approval_action" value="reject" class="btn btn-outline" style="color: #b91c1c; border-color: #fecaca;" onclick="return confirm('Apakah Anda yakin ingin MENOLAK pengajuan klaim ini?')">
                            Tolak Klaim
                        </button>
                        <button type="submit" name="approval_action" value="approve" class="btn btn-primary" style="background-color: var(--primary-700);">
                            Simpan Persetujuan
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 3: AUDIT LOG RIWAYAT KLAIM -->
            <div id="tab-pane-audit" style="display: none;">
                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 16px; color: var(--primary-900);">Log Jejak Aktivitas untuk Klaim Ini</h3>
                <?php if (!empty($timeline)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Aktivitas</th>
                                    <th>Detail</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timeline as $tl): ?>
                                    <tr>
                                        <td style="font-size: 12px; color: var(--text-muted);"><?= date('d M Y H:i', strtotime($tl['created_at'])) ?></td>
                                        <td><strong><?= e($tl['user_name']) ?></strong></td>
                                        <td><?= e($tl['activity']) ?></td>
                                        <td style="font-size: 12.5px;"><?= e($tl['detail']) ?></td>
                                        <td>
                                            <span class="badge badge-approved" style="font-size: 11px;">
                                                <span class="badge-dot"></span> <?= e($tl['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 13px;">Belum ada jejak audit khusus untuk nomor klaim ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Informasi Klaim, Dokumentasi Kerusakan & Jejak Audit Keamanan -->
    <div class="survey-workstation-side" style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Panel Informasi Klaim & Dokumentasi Kerusakan Kendaraan -->
        <div class="card">
            <div class="card-header" style="background-color: var(--primary-soft);">
                <div class="card-header-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <h3 style="font-size: 15px;">Informasi Klaim &amp; Dokumentasi Kerusakan</h3>
                </div>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="detail-info-grid">
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Waktu Kejadian</span>
                        <strong style="font-size: 13px; color: var(--text-primary);"><?= date('d M Y', strtotime($claim['incident_date'])) ?> &bull; <?= e(substr((string)$claim['incident_time'], 0, 5)) ?></strong>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Estimasi Awal Klaim</span>
                        <strong style="font-size: 13.5px; color: #15803d;">Rp <?= number_format((float)$claim['estimated_damage_cost'], 0, ',', '.') ?></strong>
                    </div>
                </div>

                <div>
                    <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Lokasi Kejadian</span>
                    <span style="font-size: 13px; color: var(--text-primary); word-break: break-word;"><?= e($claim['incident_location']) ?></span>
                </div>

                <div>
                    <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">Kronologi Kerusakan</span>
                    <p style="font-size: 12.5px; color: var(--text-secondary); line-height: 1.5; margin-top: 4px; background: var(--primary-soft); padding: 10px 12px; border-radius: var(--radius-sm); word-break: break-word;">
                        <?= nl2br(e($claim['chronology'])) ?>
                    </p>
                </div>

                <div>
                    <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 8px;">
                        Dokumentasi Foto Kendaraan (<?= count($claim['files'] ?? []) ?> Foto)
                    </span>
                    <?php if (!empty($claim['files'])): ?>
                        <div class="survey-photo-grid">
                            <?php foreach ($claim['files'] as $f): ?>
                                <div class="survey-photo-item">
                                    <div class="survey-photo-frame">
                                        <img src="<?= e($f['file_path']) ?>" alt="<?= e($f['original_name']) ?>">
                                    </div>
                                    <div class="survey-photo-caption">
                                        <strong title="<?= e($f['original_name']) ?>"><?= e($f['original_name']) ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="font-size: 12.5px; color: var(--text-muted); background: #f8fafc; padding: 12px; border-radius: var(--radius-sm); text-align: center; border: 1px dashed var(--border-color);">
                            Belum ada foto dokumentasi kerusakan yang diunggah.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel Jejak Audit Keamanan -->
        <div class="card survey-sticky-card">
            <div class="card-header" style="background-color: var(--primary-soft);">
                <div class="card-header-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <h3 style="font-size: 15px;">Jejak Audit Keamanan</h3>
                </div>
            </div>
            <div class="card-body">
                <?php $isDecided = !empty($survey['approval_date']); ?>
                <div class="timeline">
                    <!-- Milestone 1 -->
                    <div class="timeline-item active">
                        <div class="timeline-dot"></div>
                        <div class="timeline-time"><?= date('d M Y H:i', strtotime($claim['created_at'])) ?></div>
                        <div class="timeline-content">
                            Klaim diajukan oleh <strong><?= e($claim['submitter_name']) ?></strong>
                        </div>
                    </div>

                    <!-- Milestone 2 -->
                    <div class="timeline-item <?= $isSurveyDone ? 'active' : '' ?>">
                        <div class="timeline-dot"></div>
                        <div class="timeline-time"><?= $isSurveyDone ? date('d M Y H:i', strtotime($survey['created_at'])) : 'Belum dilakukan' ?></div>
                        <div class="timeline-content">
                            <?php if ($isSurveyDone): ?>
                                Survey selesai oleh <strong><?= e($survey['surveyor_name'] ?? '-') ?></strong>
                            <?php else: ?>
                                Menunggu survey lapangan
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Milestone 3 -->
                    <div class="timeline-item <?= $isDecided ? 'active' : '' ?>">
                        <div class="timeline-dot"></div>
                        <div class="timeline-time"><?= $isDecided ? date('d M Y H:i', strtotime($survey['approval_date'])) : 'Belum diputuskan' ?></div>
                        <div class="timeline-content">
                            <?php if ($isDecided): ?>
                                Status: <strong style="color: <?= $survey['approval_status'] === 'Ditolak' ? '#b91c1c' : '#15803d' ?>;"><?= e($survey['approval_status']) ?></strong>
                                oleh <strong><?= e($survey['auditor_name'] ?? '-') ?></strong>
                            <?php else: ?>
                                Menunggu persetujuan klaim
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px dashed var(--border-color); font-size: 12px; color: var(--text-muted);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Seluruh riwayat persetujuan tercatat terenkripsi dalam log audit integritas sistem CLAIMORA.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('tab-btn-' + tab).classList.add('active');

    document.getElementById('tab-pane-survey').style.display = (tab === 'survey') ? 'block' : 'none';
    document.getElementById('tab-pane-approval').style.display = (tab === 'approval') ? 'block' : 'none';
    document.getElementById('tab-pane-audit').style.display = (tab === 'audit') ? 'block' : 'none';
}
</script>
