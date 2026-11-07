<!-- Header & Breadcrumbs Kendaraan Saya -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">
            <a href="index.php?page=dashboard">Dashboard</a> &rsaquo; <span style="color: var(--text-primary); font-weight: 600;">Kendaraan Saya</span>
        </div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Kendaraan Saya & Status Proteksi</h2>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">
            Kelola data kendaraan tertanggung, cek batas pertanggungan, dan ajukan klaim kerusakan dengan cepat.
        </p>
    </div>
</div>

<!-- CTA Banner: Daftarkan Polis & Kendaraan Baru (prominent, tidak tenggelam) -->
<div class="cta-register-banner">
    <div class="cta-text">
        <h3>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align: -4px; margin-right: 6px;"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            Daftarkan Polis & Kendaraan Baru
        </h3>
        <p>Tambahkan kendaraan baru beserta polis asuransinya. Proteksi langsung aktif setelah data diverifikasi oleh tim CLAIMORA.</p>
    </div>
    <div class="cta-actions">
        <button type="button" class="btn-cta-primary" onclick="openCreatePolicyModal()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ Daftarkan Sekarang</span>
        </button>
        <a href="index.php?page=claims&action=create" class="btn-cta-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            <span>Ajukan Klaim</span>
        </a>
    </div>
</div>

<!-- 4 KPI Summary Cards for Vehicles -->
<section class="kpi-grid" style="margin-bottom: 24px;">
    <!-- KPI 1: Total Kendaraan -->
    <div class="kpi-card">
        <div class="kpi-icon icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13"></rect>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                <circle cx="18.5" cy="18.5" r="2.5"></circle>
            </svg>
        </div>
        <div class="kpi-info">
            <div class="kpi-title">Total Kendaraan Terdaftar</div>
            <div class="kpi-value-row">
                <div class="kpi-value"><?= (int)$totalVehicles ?></div>
                <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Unit</span>
            </div>
        </div>
    </div>

    <!-- KPI 2: Proteksi Aktif -->
    <div class="kpi-card">
        <div class="kpi-icon icon-green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                <polyline points="9 12 11 14 15 10"></polyline>
            </svg>
        </div>
        <div class="kpi-info">
            <div class="kpi-title">Proteksi Aktif (Valid)</div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="color: #15803d;"><?= (int)$totalActive ?></div>
                <span style="font-size: 12px; color: #15803d; font-weight: 600;">Terlindungi</span>
            </div>
        </div>
    </div>

    <!-- KPI 3: Kedaluwarsa / Perlu Perpanjangan -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background: #fee2e2; color: #b91c1c;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="kpi-info">
            <div class="kpi-title">Kedaluwarsa / Non-Aktif</div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="color: #b91c1c;"><?= (int)$totalExpired ?></div>
                <span style="font-size: 12px; color: #b91c1c; font-weight: 600;">Unit</span>
            </div>
        </div>
    </div>

    <!-- KPI 4: Total Plafon Pertanggungan -->
    <div class="kpi-card">
        <div class="kpi-icon icon-yellow">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
        </div>
        <div class="kpi-info">
            <div class="kpi-title">Total Plafon Pertanggungan</div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="font-size: 18px;"><?= format_rupiah((float)$totalCoverage) ?></div>
            </div>
        </div>
    </div>
</section>

<!-- Filter & Search Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
    <form method="GET" action="index.php" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;" class="filter-bar-flex">
        <input type="hidden" name="page" value="vehicles">

        <div style="flex-grow: 1; min-width: 260px; position: relative;">
            <input type="text" name="q" id="vehicle-search" class="form-control" placeholder="Cari nomor plat, merk, model, atau nomor polis..." value="<?= e($query ?? '') ?>">
        </div>

        <div style="width: 220px;">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="" <?= empty($status) ? 'selected' : '' ?>>Semua Status Proteksi</option>
                <option value="Aktif" <?= ($status ?? '') === 'Aktif' ? 'selected' : '' ?>>Proteksi Aktif</option>
                <option value="Berakhir" <?= ($status ?? '') === 'Berakhir' ? 'selected' : '' ?>>Masa Berlaku Berakhir</option>
                <option value="Ditangguhkan" <?= ($status ?? '') === 'Ditangguhkan' ? 'selected' : '' ?>>Ditangguhkan</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($query) || !empty($status)): ?>
            <a href="index.php?page=vehicles" class="btn btn-outline">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Vehicles Grid Section -->
<?php if (!empty($vehicles)): ?>
    <div class="vehicles-grid" id="vehicles-grid">
        <?php foreach ($vehicles as $v): ?>
            <?php
            $isValid = $v['is_valid'];
            $daysLeft = $v['days_left'];
            ?>
            <div class="card vehicle-card" data-vehicle-card style="display: flex; flex-direction: column; overflow: hidden; position: relative;">
                <!-- Status Top Bar Accent -->
                <div style="height: 5px; background: <?= $isValid ? 'linear-gradient(90deg, #10b981, #059669)' : '#ef4444' ?>;"></div>

                <div class="card-body" style="padding: 22px 24px; flex-grow: 1; display: flex; flex-direction: column;">
                    <!-- Card Top Header: Plate & Status -->
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
                        <!-- License Plate Style -->
                        <div style="background: #0f172a; color: #ffffff; padding: 4px 12px; border-radius: 6px; font-family: monospace; font-size: 14px; font-weight: 800; letter-spacing: 1px; border: 2px solid #334155; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <?= e($v['vehicle_plate']) ?>
                        </div>

                        <!-- Status Badge -->
                        <span class="badge <?= $isValid ? 'badge-approved' : 'badge-rejected' ?>" style="font-size: 11.5px; padding: 4px 10px;">
                            <span class="badge-dot"></span>
                            <?= $isValid ? 'Proteksi Aktif' : e($v['status']) ?>
                        </span>
                    </div>

                    <!-- Vehicle Brand & Model with Car Icon -->
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 18px;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: #eaf7f0; color: #15803d; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(21, 128, 61, 0.12);">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                        </div>
                        <div>
                            <h3 style="font-size: 17px; font-weight: 800; color: var(--text-primary); margin-bottom: 2px; line-height: 1.2;">
                                <?= e($v['vehicle_brand'] . ' ' . ($v['vehicle_model'] ?? '')) ?>
                            </h3>
                            <div style="font-size: 12px; color: var(--text-muted);">
                                <?= e($v['insurance_type']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div style="background: var(--bg-surface-alt, #f8fafc); border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 18px; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12.5px;">
                            <span style="color: var(--text-muted);">Nomor Polis</span>
                            <a href="index.php?page=policies" style="font-family: monospace; font-weight: 700; color: var(--primary-800); text-decoration: none;">
                                <?= e($v['policy_number']) ?>
                            </a>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12.5px;">
                            <span style="color: var(--text-muted);">Batas Pertanggungan</span>
                            <strong style="color: #15803d; font-size: 13.5px;"><?= format_rupiah((float)$v['coverage_amount']) ?></strong>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12.5px;">
                            <span style="color: var(--text-muted);">Masa Berlaku</span>
                            <span style="font-weight: 600; color: var(--text-primary);">
                                <?= date('d/m/Y', strtotime($v['start_date'])) ?> – <?= date('d/m/Y', strtotime($v['end_date'])) ?>
                            </span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                            <span style="color: var(--text-muted);">Status Proteksi Hari Ini</span>
                            <?php if ($isValid && $daysLeft >= 0): ?>
                                <span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 12px; font-weight: 600;">
                                    Sisa <?= $daysLeft ?> hari lagi
                                </span>
                            <?php else: ?>
                                <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 12px; font-weight: 600;">
                                    Proteksi telah berakhir
                                </span>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; padding-top: 6px; border-top: 1px dashed var(--border-color);">
                            <span style="color: var(--text-muted);">Riwayat Klaim</span>
                            <span style="font-weight: 700; color: var(--text-primary);">
                                <?= (int)$v['total_claims'] ?> Klaim (<?= (int)$v['approved_claims'] ?> Disetujui)
                            </span>
                        </div>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div style="margin-top: auto; display: flex; gap: 8px;">
                        <a href="index.php?page=claims&action=create&policy_id=<?= (int)$v['id'] ?>" class="btn btn-primary" style="flex: 1; font-size: 12.5px; padding: 9px 14px; text-decoration: none; justify-content: center;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Ajukan Klaim</span>
                        </a>
                        <button type="button" class="btn btn-outline" style="font-size: 12.5px; padding: 9px 12px;" onclick="openEditPolicyModal(<?= htmlspecialchars(json_encode($v), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Data Kendaraan & Polis">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button type="button" class="btn btn-outline btn-danger" style="font-size: 12.5px; padding: 9px 12px; border-color: var(--status-danger-border); color: var(--status-danger-text);" data-delete-vehicle="<?= (int)$v['id'] ?>" data-vehicle-name="<?= e($v['vehicle_brand'] . ' ' . ($v['vehicle_model'] ?? '') . ' (' . $v['vehicle_plate'] . ')') ?>" data-claims-count="<?= (int)$v['total_claims'] ?>" title="Hapus / Nonaktifkan Kendaraan">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- Empty State -->
    <div class="card" style="padding: 60px 24px; text-align: center;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: #eaf7f0; color: #15803d; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px auto;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="1" y="3" width="15" height="13"></rect>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                <circle cx="18.5" cy="18.5" r="2.5"></circle>
            </svg>
        </div>
        <h3 style="font-size: 18px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">
            Belum Ada Kendaraan Tertanggung
        </h3>
        <p style="font-size: 13.5px; color: var(--text-muted); max-width: 460px; margin: 0 auto 20px auto; line-height: 1.5;">
            Anda belum mendaftarkan unit kendaraan yang diproteksi oleh polis CLAIMORA. Daftarkan kendaraan Anda sekarang agar siap mendapatkan jaminan perlindungan asuransi.
        </p>
        <button type="button" class="btn btn-primary" onclick="openCreatePolicyModal()" style="padding: 11px 24px; font-size: 13.5px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Daftarkan Kendaraan & Polis Baru Sekarang</span>
        </button>
    </div>
<?php endif; ?>

<!-- Modal Form: Tambah / Edit Polis & Kendaraan -->
<div id="modal-policy-form" class="modal-backdrop">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal-policy-title">Daftarkan Polis & Kendaraan Baru</h3>
            <button type="button" class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="index.php?page=policies&action=store" id="form-policy">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="id" id="input-policy-id" value="0">

            <div class="modal-body">
                <!-- Nomor Polis with JS validation -->
                <div class="form-group">
                    <label class="form-label">Nomor Polis <span class="required">*</span></label>
                    <input type="text" name="policy_number" id="input-policy-number" class="form-control" placeholder="JS12345678" maxlength="10" required>
                    <div id="policy-number-feedback" class="form-hint">Format wajib: JS diikuti 8 digit angka (contoh: JS12345678)</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap Nasabah <span class="required">*</span></label>
                        <input type="text" name="customer_name" id="input-customer-name" class="form-control" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIK (16 Digit) <span class="required">*</span></label>
                        <input type="text" name="nik" id="input-nik" class="form-control" placeholder="3201..." maxlength="16" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">No. Telepon / WhatsApp <span class="required">*</span></label>
                        <input type="text" name="phone" id="input-phone" class="form-control" placeholder="0812..." required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Nasabah</label>
                        <input type="email" name="email" id="input-email" class="form-control" placeholder="nasabah@mail.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jenis Asuransi <span class="required">*</span></label>
                        <select name="insurance_type" id="input-insurance-type" class="form-control" required>
                            <option value="Kendaraan">Kendaraan (Comprehensive / All Risk)</option>
                            <option value="Total Loss Only (TLO)">Total Loss Only (TLO)</option>
                            <option value="Komersial">Komersial / Operasional</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Batas Pertanggungan (Rp) <span class="required">*</span></label>
                        <input type="text" name="coverage_amount" id="input-coverage-amount" class="form-control" placeholder="Rp 500.000.000" data-rupiah required>
                    </div>
                </div>

                <!-- Masa Berlaku Proteksi with JS validation -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Masa Berlaku Awal <span class="required">*</span></label>
                        <input type="date" name="start_date" id="input-start-date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Masa Berlaku Akhir <span class="required">*</span></label>
                        <input type="date" name="end_date" id="input-end-date" class="form-control" required>
                    </div>
                </div>
                <div id="date-feedback" class="form-hint" style="margin-top: -10px; margin-bottom: 14px;"></div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Plat Nomor Kendaraan <span class="required">*</span></label>
                        <input type="text" name="vehicle_plate" id="input-vehicle-plate" class="form-control" placeholder="B 1234 CLM" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Merk Kendaraan <span class="required">*</span></label>
                        <input type="text" name="vehicle_brand" id="input-vehicle-brand" class="form-control" placeholder="Toyota" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Model Kendaraan</label>
                        <input type="text" name="vehicle_model" id="input-vehicle-model" class="form-control" placeholder="Veloz 2023">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status Polis</label>
                        <select name="status" id="input-status" class="form-control">
                            <option value="Aktif">Aktif</option>
                            <option value="Berakhir">Berakhir</option>
                            <option value="Ditangguhkan">Ditangguhkan</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Polis</label>
                    <textarea name="notes" id="input-notes" class="form-control" rows="2" placeholder="Keterangan tambahan polis..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary" id="btn-save-policy">Simpan Data Polis & Kendaraan</button>
            </div>
        </form>
    </div>
</div>

<script>
const currentUserData = {
    role: <?= json_encode($user['role'] ?? '') ?>,
    name: <?= json_encode($user['name'] ?? '') ?>,
    email: <?= json_encode($user['email'] ?? '') ?>,
    phone: <?= json_encode($user['phone'] ?? '') ?>
};

function openCreatePolicyModal() {
    const isCustomer = currentUserData.role === 'customer';
    document.getElementById('modal-policy-title').textContent = isCustomer ? 'Daftarkan Polis & Kendaraan Baru' : 'Tambah Data Polis Baru';
    document.getElementById('input-policy-id').value = '0';
    document.getElementById('form-policy').reset();
    
    // Auto-generate nomor polis acak JS + 8 digit
    const rand8 = Math.floor(10000000 + Math.random() * 90000000);
    const polNumInput = document.getElementById('input-policy-number');
    if (polNumInput) {
        polNumInput.value = 'JS' + rand8;
        polNumInput.dispatchEvent(new Event('input'));
    }

    if (isCustomer) {
        if (currentUserData.name) document.getElementById('input-customer-name').value = currentUserData.name;
        if (currentUserData.email) document.getElementById('input-email').value = currentUserData.email;
        if (currentUserData.phone) document.getElementById('input-phone').value = currentUserData.phone;
    }

    const today = new Date();
    const nextYear = new Date();
    nextYear.setFullYear(today.getFullYear() + 1);
    const pad = n => String(n).padStart(2, '0');
    const formatDate = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

    const startInput = document.getElementById('input-start-date');
    const endInput = document.getElementById('input-end-date');
    if (startInput) startInput.value = formatDate(today);
    if (endInput) {
        endInput.value = formatDate(nextYear);
        endInput.dispatchEvent(new Event('change'));
    }

    const coverageInput = document.getElementById('input-coverage-amount');
    if (coverageInput && (!coverageInput.value || coverageInput.value === '0')) {
        coverageInput.value = 'Rp 250.000.000';
    }

    const saveBtn = document.getElementById('btn-save-policy');
    if (saveBtn) {
        saveBtn.textContent = isCustomer ? 'Simpan Data Polis & Kendaraan' : 'Simpan Data Polis';
    }

    openModal('modal-policy-form');
}

function openEditPolicyModal(pol) {
    document.getElementById('modal-policy-title').textContent = 'Edit Kendaraan: ' + (pol.vehicle_plate || pol.policy_number);
    document.getElementById('input-policy-id').value = pol.id;
    document.getElementById('input-policy-number').value = pol.policy_number;
    document.getElementById('input-customer-name').value = pol.customer_name;
    document.getElementById('input-nik').value = pol.nik;
    document.getElementById('input-phone').value = pol.phone;
    document.getElementById('input-email').value = pol.email || '';
    document.getElementById('input-insurance-type').value = pol.insurance_type;
    document.getElementById('input-coverage-amount').value = formatRupiah(String(Math.round(pol.coverage_amount)), 'Rp ');
    document.getElementById('input-start-date').value = pol.start_date;
    document.getElementById('input-end-date').value = pol.end_date;
    document.getElementById('input-vehicle-plate').value = pol.vehicle_plate;
    document.getElementById('input-vehicle-brand').value = pol.vehicle_brand;
    document.getElementById('input-vehicle-model').value = pol.vehicle_model || '';
    document.getElementById('input-status').value = pol.status;
    document.getElementById('input-notes').value = pol.notes || '';

    const saveBtn = document.getElementById('btn-save-policy');
    if (saveBtn) {
        saveBtn.textContent = 'Perbarui Data Kendaraan';
    }

    const polNumInput = document.getElementById('input-policy-number');
    if (polNumInput) polNumInput.dispatchEvent(new Event('input'));
    const endInput = document.getElementById('input-end-date');
    if (endInput) endInput.dispatchEvent(new Event('change'));

    openModal('modal-policy-form');
}

// Client-side quick search for vehicles grid
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('vehicle-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const cards = document.querySelectorAll('[data-vehicle-card]');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    const params = new URLSearchParams(window.location.search);
    if (params.get('register') === 'true' || params.get('action') === 'create') {
        openCreatePolicyModal();
    }

    // ─── Client-Side Validation Enhancement ─────────────────────────────

    // Validasi Nomor Polis (real-time feedback)
    const polNumInput = document.getElementById('input-policy-number');
    const polFeedback = document.getElementById('policy-number-feedback');
    if (polNumInput && polFeedback) {
        polNumInput.addEventListener('input', () => {
            const val = polNumInput.value.trim().toUpperCase();
            polNumInput.value = val;
            const valid = /^JS\d{8}$/.test(val);
            if (val.length === 0) {
                polFeedback.textContent = 'Format wajib: JS diikuti 8 digit angka (contoh: JS12345678)';
                polFeedback.style.color = '';
                polNumInput.style.borderColor = '';
            } else if (valid) {
                polFeedback.textContent = '✓ Format nomor polis valid (JS + 8 digit)';
                polFeedback.style.color = '#15803d';
                polNumInput.style.borderColor = '#52b788';
            } else {
                polFeedback.textContent = '✗ Format salah — harus JS diikuti 8 digit angka';
                polFeedback.style.color = '#b91c1c';
                polNumInput.style.borderColor = '#fca5a5';
            }
        });
    }

    // Validasi NIK (harus 16 digit angka)
    const nikInput = document.getElementById('input-nik');
    if (nikInput) {
        nikInput.addEventListener('input', () => {
            nikInput.value = nikInput.value.replace(/\D/g, '').slice(0, 16);
        });
    }

    // Validasi Telepon (hanya angka dan karakter telepon)
    const phoneInput = document.getElementById('input-phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', () => {
            phoneInput.value = phoneInput.value.replace(/[^\d+\-() ]/g, '');
        });
    }

    // Validasi Tanggal: akhir harus setelah awal
    const startDateInput = document.getElementById('input-start-date');
    const endDateInput = document.getElementById('input-end-date');
    const dateFeedback = document.getElementById('date-feedback');
    if (startDateInput && endDateInput && dateFeedback) {
        const validateDates = () => {
            const start = startDateInput.value;
            const end = endDateInput.value;
            if (start && end) {
                if (end <= start) {
                    dateFeedback.textContent = '✗ Masa berlaku akhir harus setelah masa berlaku awal';
                    dateFeedback.style.color = '#b91c1c';
                    endDateInput.style.borderColor = '#fca5a5';
                } else {
                    const diffMs = new Date(end) - new Date(start);
                    const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
                    dateFeedback.textContent = `✓ Durasi proteksi: ${diffDays} hari`;
                    dateFeedback.style.color = '#15803d';
                    endDateInput.style.borderColor = '#52b788';
                }
            } else {
                dateFeedback.textContent = '';
                endDateInput.style.borderColor = '';
            }
        };
        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);
    }

    // Form submit: validasi lengkap sebelum kirim
    const formPolicy = document.getElementById('form-policy');
    if (formPolicy) {
        formPolicy.addEventListener('submit', function(e) {
            const errors = [];
            const polNum = (document.getElementById('input-policy-number').value || '').trim();
            const name = (document.getElementById('input-customer-name').value || '').trim();
            const nik = (document.getElementById('input-nik').value || '').trim();
            const phone = (document.getElementById('input-phone').value || '').trim();
            const email = (document.getElementById('input-email').value || '').trim();
            const coverage = (document.getElementById('input-coverage-amount').value || '').replace(/[^\d]/g, '');
            const startDate = document.getElementById('input-start-date').value;
            const endDate = document.getElementById('input-end-date').value;
            const plate = (document.getElementById('input-vehicle-plate').value || '').trim();
            const brand = (document.getElementById('input-vehicle-brand').value || '').trim();

            if (!/^JS\d{8}$/.test(polNum)) errors.push('Nomor polis harus format JS + 8 digit angka');
            if (!name) errors.push('Nama nasabah wajib diisi');
            if (!/^\d{16}$/.test(nik)) errors.push('NIK harus 16 digit angka');
            if (!phone) errors.push('Nomor telepon wajib diisi');
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Format email tidak valid');
            if (!coverage || parseInt(coverage) <= 0) errors.push('Batas pertanggungan harus lebih dari Rp 0');
            if (!startDate || !endDate) errors.push('Masa berlaku wajib diisi');
            if (startDate && endDate && endDate <= startDate) errors.push('Masa berlaku akhir harus setelah awal');
            if (!plate) errors.push('Plat nomor kendaraan wajib diisi');
            if (!brand) errors.push('Merk kendaraan wajib diisi');

            if (errors.length > 0) {
                e.preventDefault();
                alert('Perbaiki data berikut:\n\n• ' + errors.join('\n• '));
                // Reset loading state
                const submitBtn = formPolicy.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.remove('btn-loading');
                    if (submitBtn._originalText) submitBtn.textContent = submitBtn._originalText;
                }
                return false;
            }
        });
    }

    // Handler Konfirmasi Hapus Kendaraan
    document.querySelectorAll('[data-delete-vehicle]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.getAttribute('data-delete-vehicle');
            const vname = btn.getAttribute('data-vehicle-name') || 'kendaraan ini';
            const claimsCount = parseInt(btn.getAttribute('data-claims-count') || '0', 10);

            let msg = `Apakah Anda yakin ingin menghapus data kendaraan ${vname}?`;
            if (claimsCount > 0) {
                msg += `\n\nPerhatian: Kendaraan ini memiliki ${claimsCount} riwayat pengajuan klaim. Status perlindungan akan dinonaktifkan (Ditangguhkan) untuk menjaga riwayat arsip klaim Anda.`;
            }

            confirmDelete(
                'Konfirmasi Hapus Kendaraan',
                msg,
                () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'index.php?page=vehicles&action=delete';

                    const inputId = document.createElement('input');
                    inputId.type = 'hidden';
                    inputId.name = 'id';
                    inputId.value = id;
                    form.appendChild(inputId);

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const inputCsrf = document.createElement('input');
                    inputCsrf.type = 'hidden';
                    inputCsrf.name = 'csrf_token';
                    inputCsrf.value = csrfToken;
                    form.appendChild(inputCsrf);

                    document.body.appendChild(form);
                    form.submit();
                }
            );
        });
    });
});
</script>

