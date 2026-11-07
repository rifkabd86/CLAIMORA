<!-- Data Polis Header & Breadcrumb -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">
            <a href="index.php?page=dashboard">Dashboard</a> &rsaquo; <span style="color: var(--text-primary); font-weight: 600;">Data Polis</span>
        </div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Data Polis & Batas Pertanggungan</h2>
    </div>

    <?php if (($user['role'] ?? '') === 'admin'): ?>
    <button type="button" class="btn btn-primary" data-modal-target="modal-policy-form" onclick="openCreatePolicyModal()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>+ Tambah Polis</span>
    </button>
    <?php endif; ?>
</div>

<?php if (($user['role'] ?? '') === 'customer'): ?>
<!-- CTA Banner: Daftarkan Polis & Kendaraan Baru (prominent, tidak tenggelam) -->
<div class="cta-register-banner">
    <div class="cta-text">
        <h3>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align: -4px; margin-right: 6px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            Daftarkan Polis & Kendaraan Baru
        </h3>
        <p>Lindungi kendaraan Anda dengan polis asuransi CLAIMORA. Proses pendaftaran cepat, mudah, dan langsung aktif setelah diverifikasi.</p>
    </div>
    <div class="cta-actions">
        <button type="button" class="btn-cta-primary" onclick="openCreatePolicyModal()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ Daftarkan Sekarang</span>
        </button>
        <a href="index.php?page=vehicles" class="btn-cta-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            <span>Lihat Kendaraan Saya</span>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Search & Filter Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
    <form method="GET" action="index.php" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;" class="filter-bar-flex">
        <input type="hidden" name="page" value="policies">

        <div style="flex-grow: 1; min-width: 260px; position: relative;">
            <input type="text" name="q" id="policy-search" class="form-control" placeholder="Cari nomor polis, nasabah, plat..." value="<?= e($query ?? '') ?>" data-live-search="#table-policies">
        </div>

        <div style="width: 200px;">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="Semua Status" <?= ($status ?? '') === 'Semua Status' ? 'selected' : '' ?>>Semua Status</option>
                <option value="Aktif" <?= ($status ?? '') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="Berakhir" <?= ($status ?? '') === 'Berakhir' ? 'selected' : '' ?>>Berakhir</option>
                <option value="Ditangguhkan" <?= ($status ?? '') === 'Ditangguhkan' ? 'selected' : '' ?>>Ditangguhkan</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($query) || !empty($status)): ?>
            <a href="index.php?page=policies" class="btn btn-outline">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Two-Column Layout (Matching Image 1 Data Polis) -->
<div class="policy-split-view">
    <!-- Left Table -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 15px; font-weight: 700;">Daftar Polis Terdaftar</h3>
            <span style="font-size: 12px; color: var(--text-muted);"><?= count($policies) ?> Polis ditemukan</span>
        </div>
        <div class="table-responsive">
            <table class="table" id="table-policies">
                <thead>
                    <tr>
                        <th>No. Polis</th>
                        <th>Nama Nasabah</th>
                        <th>Jenis Asuransi</th>
                        <th>Masa Berlaku</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($policies)): ?>
                        <?php foreach ($policies as $idx => $pol): ?>
                            <?php
                            $badgeClass = match($pol['status']) {
                                'Aktif'         => 'badge-approved',
                                'Berakhir'      => 'badge-expired',
                                'Ditangguhkan'  => 'badge-audit',
                                default         => 'badge-audit'
                            };
                            ?>
                            <tr class="policy-table-row <?= $idx === 0 ? 'selected-row' : '' ?>" data-policy-id="<?= (int)$pol['id'] ?>" style="cursor: pointer;">
                                <td>
                                    <strong><?= e($pol['policy_number']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= e($pol['vehicle_plate']) ?></div>
                                </td>
                                <td>
                                    <?= e($pol['customer_name']) ?>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= e($pol['phone']) ?></div>
                                </td>
                                <td><?= e($pol['insurance_type']) ?></td>
                                <td>
                                    <div style="font-size: 12.5px;"><?= date('d/m/Y', strtotime($pol['start_date'])) ?> – <?= date('d/m/Y', strtotime($pol['end_date'])) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <span class="badge-dot"></span>
                                        <?= e($pol['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;" class="policy-action-btn">
                                        <?php if (($user['role'] ?? '') === 'admin'): ?>
                                        <button type="button" class="btn-icon" title="Edit Polis" onclick="openEditPolicyModal(<?= htmlspecialchars(json_encode($pol), ENT_QUOTES, 'UTF-8') ?>)">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </button>
                                        <button type="button" class="btn-icon btn-danger" title="Hapus Polis" data-delete-policy="<?= (int)$pol['id'] ?>" data-policy-no="<?= e($pol['policy_number']) ?>" data-has-claims="<?= (int)($pol['claims_count'] ?? 0) ?>">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                        <?php elseif (($user['role'] ?? '') === 'customer'): ?>
                                        <button type="button" class="btn-icon" title="Edit Data Polis & Kendaraan" onclick="openEditPolicyModal(<?= htmlspecialchars(json_encode($pol), ENT_QUOTES, 'UTF-8') ?>)">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </button>
                                        <button type="button" class="btn-icon btn-danger" title="Hapus / Nonaktifkan Polis" data-delete-policy="<?= (int)$pol['id'] ?>" data-policy-no="<?= e($pol['policy_number']) ?>" data-has-claims="<?= (int)($pol['claims_count'] ?? 0) ?>">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                        <?php else: ?>
                                        <span style="font-size:11px; color: var(--text-muted); font-style: italic;">Lihat Saja</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 36px 20px;">
                                <p style="margin-bottom: 12px; font-size: 13.5px;">Tidak ada data polis yang sesuai kriteria pencarian.</p>
                                <?php if (($user['role'] ?? '') === 'customer'): ?>
                                <button type="button" class="btn btn-primary" onclick="openCreatePolicyModal()" style="font-size: 13px; margin: 0 auto; display: inline-flex; align-items: center; gap: 8px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    <span>Daftarkan Polis & Kendaraan Anda Sekarang</span>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="padding: 14px 20px; font-size: 12px; color: var(--text-muted); border-top: 1px solid var(--border-color);">
            Menampilkan <?= count($policies) ?> data polis
        </div>
    </div>

    <!-- Right Side: Detail Polis Card (Matching Image 1) -->
    <?php
    $firstPolicy = !empty($policies) ? $policies[0] : null;
    $today = date('Y-m-d');
    $isFirstValid = $firstPolicy && ($firstPolicy['status'] === 'Aktif') && ($today >= $firstPolicy['start_date']) && ($today <= $firstPolicy['end_date']);
    ?>
    <div>
        <div class="card" id="policy-detail-card" style="position: sticky; top: 90px;">
            <div class="card-header" style="background-color: var(--primary-soft);">
                <div class="card-header-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <h3 style="font-size: 15px;">Detail Polis</h3>
                </div>
                <span id="detail-status-badge" class="badge <?= $isFirstValid ? 'badge-approved' : 'badge-rejected' ?>">
                    <span class="badge-dot"></span>
                    <?= $isFirstValid ? 'Polis Valid' : 'Kedaluwarsa' ?>
                </span>
            </div>

            <div class="card-body" style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <div style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">No. Polis</div>
                    <div id="detail-policy-number" style="font-size: 18px; font-weight: 800; color: var(--primary-800); font-family: monospace;">
                        <?= e($firstPolicy['policy_number'] ?? '-') ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">Nama Nasabah</div>
                    <div id="detail-customer-name" style="font-size: 15px; font-weight: 700; color: var(--text-primary);">
                        <?= e($firstPolicy['customer_name'] ?? '-') ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">Jenis Asuransi</div>
                    <div id="detail-insurance-type" style="font-size: 14px; color: var(--text-primary);">
                        <?= e($firstPolicy['insurance_type'] ?? '-') ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">Batas Pertanggungan</div>
                    <div id="detail-coverage-amount" style="font-size: 17px; font-weight: 800; color: #15803d;">
                        <?= isset($firstPolicy['coverage_amount']) ? 'Rp ' . number_format($firstPolicy['coverage_amount'], 0, ',', '.') : '-' ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">Masa Berlaku Proteksi</div>
                    <div id="detail-period" style="font-size: 13.5px; font-weight: 600; color: var(--text-primary);">
                        <?= isset($firstPolicy['start_date']) ? date('d/m/Y', strtotime($firstPolicy['start_date'])) . ' – ' . date('d/m/Y', strtotime($firstPolicy['end_date'])) : '-' ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 11.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">Kendaraan Tertanggung</div>
                    <div id="detail-vehicle-info" style="font-size: 13.5px; color: var(--text-secondary);">
                        <?= e(($firstPolicy['vehicle_brand'] ?? '') . ' ' . ($firstPolicy['vehicle_model'] ?? '') . ' (' . ($firstPolicy['vehicle_plate'] ?? '-') . ')') ?>
                    </div>
                </div>

                <?php if (in_array($user['role'] ?? '', ['admin', 'customer'])): ?>
                <div style="margin-top: 10px; padding-top: 14px; border-top: 1px dashed var(--border-color);">
                    <a href="index.php?page=claims&action=create" class="btn btn-primary" style="width: 100%; font-size: 13px;">
                        Ajukan Klaim untuk Polis Ini &rarr;
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form: Tambah / Edit Polis -->
<div id="modal-policy-form" class="modal-backdrop">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal-policy-title">Tambah Data Polis Baru</h3>
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
                <button type="submit" class="btn btn-primary" id="btn-save-policy">Simpan Data Polis</button>
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
    
    // Auto-generate nomor polis (JS + 8 digit acak) agar nasabah/admin langsung siap simpan
    const rand8 = Math.floor(10000000 + Math.random() * 90000000);
    const polNumInput = document.getElementById('input-policy-number');
    if (polNumInput) {
        polNumInput.value = 'JS' + rand8;
        polNumInput.dispatchEvent(new Event('input'));
    }

    // Prefill data nasabah jika login sebagai nasabah
    if (isCustomer) {
        if (currentUserData.name) {
            document.getElementById('input-customer-name').value = currentUserData.name;
        }
        if (currentUserData.email) {
            document.getElementById('input-email').value = currentUserData.email;
        }
        if (currentUserData.phone) {
            document.getElementById('input-phone').value = currentUserData.phone;
        }
    }

    // Default masa berlaku proteksi: hari ini sampai 1 tahun ke depan
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
    const isCustomer = currentUserData.role === 'customer';
    document.getElementById('modal-policy-title').textContent = (isCustomer ? 'Edit Polis & Kendaraan: ' : 'Edit Data Polis: ') + pol.policy_number;
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
        saveBtn.textContent = 'Perbarui Data Polis';
    }

    // Trigger validation feedback
    const polNumInput = document.getElementById('input-policy-number');
    if (polNumInput) polNumInput.dispatchEvent(new Event('input'));
    const endInput = document.getElementById('input-end-date');
    if (endInput) endInput.dispatchEvent(new Event('change'));

    openModal('modal-policy-form');
}

// Buka otomatis modal pendaftaran jika ada query parameter register=true atau action=create
document.addEventListener('DOMContentLoaded', () => {
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
});
</script>
