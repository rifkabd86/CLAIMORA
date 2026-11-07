<!-- Pengajuan Klaim Multi-step Wizard (Matching Image 1) -->
<div style="margin-bottom: 24px;">
    <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">
        <a href="index.php?page=dashboard">Dashboard</a> &rsaquo; <a href="index.php?page=claims">Pengajuan Klaim</a> &rsaquo; <span style="color: var(--text-primary); font-weight: 600;">Formulir Pengajuan Baru</span>
    </div>
    <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Pengajuan Klaim Kerusakan Kendaraan</h2>
</div>

<div class="card claim-wizard-container">
    <!-- Wizard Stepper Indicators -->
    <div class="wizard-steps">
        <div class="wizard-step active" id="step-node-1">
            <div class="step-number">1</div>
            <div class="step-label">Data Klaim</div>
        </div>
        <div class="wizard-step" id="step-node-2">
            <div class="step-number">2</div>
            <div class="step-label">Upload Berkas</div>
        </div>
        <div class="wizard-step" id="step-node-3">
            <div class="step-number">3</div>
            <div class="step-label">Konfirmasi</div>
        </div>
    </div>

    <form method="POST" action="index.php?page=claims&action=store" id="form-claim-wizard" enctype="multipart/form-data">
        <?= \Core\Security::csrfField() ?>
        <input type="hidden" name="uploaded_file_ids" id="input-uploaded-file-ids" value="[]">

        <!-- =================== STEP 1: DATA KLAIM =================== -->
        <div class="wizard-step-pane" id="pane-step-1">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; color: var(--primary-900);">Informasi Polis & Kronologi Kejadian</h3>

            <div class="form-group">
                <label class="form-label">Pilih Polis Asuransi Tertanggung <span class="required">*</span></label>
                <?php if (!empty($policies)): ?>
                    <select name="policy_id" id="claim-policy-id" class="form-control" required>
                        <option value="">-- Pilih Nomor Polis Terdaftar --</option>
                        <?php foreach ($policies as $pol): ?>
                            <?php
                            $brandModel = trim(e($pol['vehicle_brand'] . ' ' . ($pol['vehicle_model'] ?? '')));
                            $plate = e($pol['vehicle_plate']);
                            $prodName = e($pol['insurance_type'] ?? 'Kendaraan');
                            $covText = 'Rp ' . number_format($pol['coverage_amount'], 0, ',', '.');
                            ?>
                            <option value="<?= (int)$pol['id'] ?>">
                                <?= e($pol['policy_number']) ?> &bull; <?= $prodName ?> &bull; <?= $plate ?> (<?= $brandModel ?>) &bull; Plafon: <?= $covText ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint">Hanya polis aktif dan dalam masa berlaku yang dapat dipilih untuk pengajuan klaim.</div>
                <?php else: ?>
                    <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-md); padding: 16px 20px; color: #991b1b;">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <div>
                                <strong style="font-size: 14px; display: block; margin-bottom: 4px;">Belum Ada Polis & Kendaraan yang Memenuhi Syarat</strong>
                                <p style="font-size: 13px; line-height: 1.5; margin: 0; color: #7f1d1d;">
                                    <?php if (!empty($totalCustomerPolicies) && $totalCustomerPolicies > 0): ?>
                                        Anda memiliki polis terdaftar, namun statusnya saat ini tidak aktif atau masa perlindungannya telah berakhir.
                                    <?php else: ?>
                                        Anda belum memiliki polis dan kendaraan yang terdaftar dalam sistem CLAIMORA. Silakan daftarkan terlebih dahulu sebelum mengajukan klaim.
                                    <?php endif; ?>
                                </p>
                                <div style="display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap;">
                                    <a href="index.php?page=policies" class="btn btn-sm btn-primary" style="background-color: var(--primary-700);">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                        <span>Buka Menu Polis Saya</span>
                                    </a>
                                    <a href="index.php?page=vehicles" class="btn btn-sm btn-outline" style="border-color: #dc2626; color: #991b1b; background: #fff;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                                        <span>Buka Kendaraan Saya</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Auto-populated Vehicle & Policy Details Card -->
            <div id="policy-vehicle-details-card" class="card" style="display: none; background: linear-gradient(135deg, #f4fbf7 0%, #ffffff 100%); border: 1.5px solid #a7d7b5; border-radius: var(--radius-lg); padding: 18px 20px; margin-bottom: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px dashed #c2e2cf; padding-bottom: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-700)" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                        <h4 style="font-size: 14px; font-weight: 800; color: var(--primary-900); margin: 0;">Data Kendaraan Tertanggung & Batas Pertanggungan</h4>
                    </div>
                    <span id="detail-card-status" class="badge badge-approved" style="font-size: 11px;">Aktif</span>
                </div>

                <div class="detail-auto-grid">
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); display: block;">Nomor Polisi (Plat)</span>
                        <strong id="detail-card-plate" style="font-size: 15px; color: var(--primary-800); font-family: monospace;">-</strong>
                    </div>
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); display: block;">Merek & Tipe Kendaraan</span>
                        <strong id="detail-card-brand" style="font-size: 14px; color: var(--text-primary);">-</strong>
                    </div>
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); display: block;">Nama Produk Asuransi</span>
                        <strong id="detail-card-product" style="font-size: 14px; color: var(--text-primary);">-</strong>
                    </div>
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); display: block;">Batas Pertanggungan (Plafon)</span>
                        <strong id="detail-card-coverage" style="font-size: 15px; color: #15803d;">-</strong>
                    </div>
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); display: block;">Masa Berlaku Proteksi</span>
                        <span id="detail-card-period" style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">-</span>
                    </div>
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); display: block;">Tertanggung (Nasabah)</span>
                        <span id="detail-card-customer" style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">-</span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tanggal Kejadian <span class="required">*</span></label>
                    <input type="date" name="incident_date" id="claim-incident-date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Waktu Kejadian <span class="required">*</span></label>
                    <input type="time" name="incident_time" id="claim-incident-time" class="form-control" value="<?= date('H:i') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Lokasi Kejadian Kecelakaan / Kerusakan <span class="required">*</span></label>
                <input type="text" name="incident_location" id="claim-incident-location" class="form-control" placeholder="Contoh: Jl. Jendral Sudirman KM 12, Jakarta Selatan" required>
            </div>

            <div class="form-group">
                <label class="form-label">Estimasi Awal Biaya Kerusakan (Rp) <span class="required">*</span></label>
                <input type="text" name="estimated_damage_cost" id="claim-damage-cost" class="form-control" placeholder="Rp 45.000.000" data-rupiah required>
                <div class="form-hint">Estimasi awal biaya perbaikan menurut bengkel atau perkiraan tertanggung.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Kronologi Kejadian Kerusakan <span class="required">*</span></label>
                <textarea name="chronology" id="claim-chronology" class="form-control" rows="4" placeholder="Jelaskan secara rinci kronologi tabrakan/kerusakan kendaraan, posisi kerusakan bodi, dan pihak lain yang terlibat jika ada..." required></textarea>
                <div class="form-hint">Minimal 15 karakter. Deskripsi detail mempercepat verifikasi surveyor lapangan.</div>
            </div>
        </div>

        <!-- =================== STEP 2: UPLOAD BERKAS =================== -->
        <div class="wizard-step-pane" id="pane-step-2" style="display: none;">
            <div style="margin-bottom: 20px;">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--primary-900);">Unggah Foto Kerusakan Kendaraan</h3>
                <p style="font-size: 12.5px; color: var(--text-secondary); margin-top: 4px;">
                    Format: <strong>JPG, PNG, WEBP</strong> | Ukuran Maksimal: <strong>5MB per file</strong>
                </p>
            </div>

            <!-- Drag & Drop Area -->
            <div class="dropzone" id="damage-dropzone">
                <div class="dropzone-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                </div>
                <h4>Drag & drop foto di sini</h4>
                <p>atau <span>klik untuk memilih file</span> dari komputer/ponsel Anda</p>
                <input type="file" id="damage-file-input" multiple accept="image/jpeg,image/png,image/webp" style="display: none;">
            </div>

            <!-- Previews Grid -->
            <div style="margin-top: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <h4 style="font-size: 13.5px; font-weight: 700; color: var(--text-primary);">Foto Terunggah</h4>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('damage-file-input').click()">
                        + Tambah File
                    </button>
                </div>

                <div class="uploaded-previews-grid" id="uploaded-previews-grid">
                    <!-- Dynamic preview thumbnails appear here -->
                </div>
            </div>
        </div>

        <!-- =================== STEP 3: KONFIRMASI =================== -->
        <div class="wizard-step-pane" id="pane-step-3" style="display: none;">
            <div style="margin-bottom: 20px;">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--primary-900);">Konfirmasi & Ringkasan Pengajuan Klaim</h3>
                <p style="font-size: 12.5px; color: var(--text-secondary); margin-top: 4px;">
                    Silakan periksa kembali data klaim dan bukti foto yang telah diunggah sebelum mengirimkan ke sistem.
                </p>
            </div>

            <div style="background-color: var(--primary-soft); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 22px; display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block;">Polis Asuransi & Kendaraan:</span>
                    <strong id="summary-policy" style="font-size: 14.5px; color: var(--primary-900);">-</strong>
                </div>

                <div class="detail-info-grid">
                    <div>
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block;">Kendaraan Tertanggung:</span>
                        <strong id="summary-vehicle" style="font-size: 13.5px; color: var(--text-primary);">-</strong>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block;">Batas Pertanggungan:</span>
                        <strong id="summary-coverage" style="font-size: 14px; color: var(--primary-800);">-</strong>
                    </div>
                </div>

                <div class="detail-info-grid">
                    <div>
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block;">Waktu Kejadian:</span>
                        <strong id="summary-datetime" style="font-size: 13.5px; color: var(--text-primary);">-</strong>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block;">Estimasi Biaya:</span>
                        <strong id="summary-damage-cost" style="font-size: 15px; color: #15803d;">-</strong>
                    </div>
                </div>

                <div>
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block;">Lokasi Kejadian:</span>
                    <span id="summary-location" style="font-size: 13.5px; color: var(--text-primary);">-</span>
                </div>

                <div>
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block;">Kronologi Kejadian:</span>
                    <p id="summary-chronology" style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-top: 2px;">-</p>
                </div>

                <div>
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block;">Bukti Berkas:</span>
                    <span id="summary-photos-count" class="badge badge-approved" style="margin-top: 4px;">0 foto kerusakan terunggah</span>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 13px; color: var(--text-secondary);">
                    <input type="checkbox" id="confirm-declaration" required style="margin-top: 3px;">
                    <span>Saya menyatakan dengan sesungguhnya bahwa kronologi dan foto kerusakan kendaraan yang diajukan adalah benar, akurat, dan dapat diverifikasi langsung oleh surveyor asuransi.</span>
                </label>
            </div>
        </div>

        <!-- Wizard Navigation Footer Buttons (Matching Image 1) -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
            <button type="button" id="btn-wizard-prev" class="btn btn-outline" style="display: none;">
                &larr; Kembali
            </button>
            <div style="margin-left: auto; display: flex; gap: 10px;">
                <button type="button" id="btn-wizard-next" class="btn btn-primary" <?= empty($policies) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
                    <span>Lanjutkan</span> &rarr;
                </button>
                <button type="submit" id="btn-wizard-submit" class="btn btn-primary" style="display: none; background-color: #15803d; border-color: #15803d;">
                    <span>Kirim Pengajuan Klaim</span> &check;
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    const availablePoliciesData = <?= json_encode($policies, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
