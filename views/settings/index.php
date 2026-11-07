<?php $isCustomer = ($user['role'] ?? '') === 'customer'; ?>
<div style="margin-bottom: 24px;">
    <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">
        <a href="index.php?page=dashboard">Dashboard</a> &rsaquo; <span style="color: var(--text-primary); font-weight: 600;"><?= $isCustomer ? 'Profil Saya' : 'Pengaturan' ?></span>
    </div>
    <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);"><?= $isCustomer ? 'Profil Saya &amp; Informasi Akun' : 'Pengaturan Akun &amp; Profil Pengguna' ?></h2>
    <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">
        <?= $isCustomer
            ? 'Kelola informasi pribadi akun nasabah Anda dan preferensi notifikasi pembaruan klaim asuransi.'
            : 'Kelola informasi profil akun administrator dan preferensi keamanan sistem CLAIMORA.' ?>
    </p>
</div>

<div class="settings-grid">
    <!-- Kolom Kiri: Profil + Password -->
    <div style="display: flex; flex-direction: column; gap: 24px;">

        <!-- ===== CARD: Informasi Profil ===== -->
        <div class="card" id="profile-card">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                <div class="card-header-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <h3 style="font-size: 15px;">Informasi Profil Akun</h3>
                </div>
                <span id="badge-profile-mode" class="badge" style="background: var(--primary-soft); color: var(--primary-800); border: 1px solid var(--primary-200); font-size: 11px; font-weight: 600;">
                    Mode Lihat
                </span>
            </div>

            <div class="card-body">
                <!-- Alert Feedback inline -->
                <div id="profile-alert" style="display: none; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 18px; font-size: 13px; font-weight: 600;"></div>

                <!-- Profile Summary Box -->
                <div id="profile-avatar-box" style="display: flex; align-items: center; flex-wrap: wrap; gap: 16px; padding: 16px; background: linear-gradient(135deg, #f4fbf7 0%, #ffffff 100%); border: 1px solid var(--border-color); border-radius: var(--radius-lg); margin-bottom: 22px;">
                    <div class="user-avatar-lg" style="width: 52px; height: 52px; border-radius: 50%; background: var(--primary-700); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 12px rgba(27, 68, 51, 0.2);">
                        <span id="avatar-initial"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></span>
                    </div>
                    <div style="flex: 1 1 160px; min-width: 0; overflow: hidden;">
                        <div id="display-header-name" style="font-size: 16px; font-weight: 800; color: var(--text-primary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                            <?= e($user['name'] ?? '') ?>
                        </div>
                        <div id="display-header-email" style="font-size: 12.5px; color: var(--text-muted); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                            <?= e($user['email'] ?? '') ?>
                        </div>
                        <div style="margin-top: 4px;">
                            <span class="badge badge-approved" style="font-size: 11px;">
                                <?= e($user['role_label'] ?? ucfirst($user['role'] ?? 'Pengguna')) ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <button type="button" id="btn-edit-profile" class="btn btn-sm btn-outline" style="white-space: nowrap; font-weight: 600;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            Edit Profil
                        </button>
                    </div>
                </div>

                <form method="POST" action="index.php?page=settings" id="form-profile">
                    <?= \Core\Security::csrfField() ?>

                    <!-- Nama Lengkap -->
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label class="form-label" for="input-profile-name">
                            Nama Lengkap <span class="required" id="req-name" style="display: none;">*</span>
                        </label>
                        <input type="text" name="name" id="input-profile-name" class="form-control"
                               value="<?= e($user['name'] ?? '') ?>" required readonly
                               style="background-color: var(--primary-soft); transition: var(--transition);">
                        <div class="form-hint" id="hint-name">Nama lengkap pengguna terdaftar.</div>
                    </div>

                    <!-- Alamat Email -->
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label class="form-label" for="input-profile-email">
                            Alamat Email <span class="required" id="req-email" style="display: none;">*</span>
                        </label>
                        <input type="email" name="email" id="input-profile-email" class="form-control"
                               value="<?= e($user['email'] ?? '') ?>" required readonly
                               style="background-color: var(--primary-soft); transition: var(--transition);">
                        <div class="form-hint" id="hint-email">
                            <?= $isCustomer
                                ? 'Email terhubung dengan verifikasi akun dan klaim polis kendaraan Anda.'
                                : 'Email resmi administrator untuk login dan pemberitahuan sistem.' ?>
                        </div>
                    </div>

                    <!-- Peran Sistem (Read-Only) -->
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label class="form-label">Peran Sistem (Role)</label>
                        <div style="position: relative;">
                            <input type="text" class="form-control" value="<?= e($user['role_label'] ?? 'Admin Asuransi') ?>"
                                   disabled readonly style="background: #eef3f0; cursor: not-allowed; color: var(--text-secondary); font-weight: 600;">
                            <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 11px; color: var(--text-muted); font-style: italic;">
                                Read-Only
                            </span>
                        </div>
                        <div class="form-hint">Hak akses role dikelola secara terpusat oleh sistem keamanan CLAIMORA.</div>
                    </div>

                    <!-- Notifikasi Email -->
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13.5px; font-weight: 500; color: var(--text-primary);">
                            <input type="checkbox" name="notify_email" id="input-notify-email" value="1" checked>
                            <span>Aktifkan Notifikasi Email untuk update status klaim dan sistem</span>
                        </label>
                    </div>

                    <!-- Action Buttons Edit Mode -->
                    <div id="profile-edit-actions" style="display: none; align-items: center; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px dashed var(--border-color);">
                        <button type="button" id="btn-cancel-edit" class="btn btn-outline" style="font-weight: 600;">
                            Batal
                        </button>
                        <button type="submit" id="btn-save-profile" class="btn btn-primary" style="background-color: var(--primary-700); font-weight: 600;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ===== CARD: Ubah Password ===== -->
        <div class="card" id="password-card">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                <div class="card-header-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <h3 style="font-size: 15px;">Ubah Password</h3>
                </div>
                <span class="badge" style="background: var(--primary-soft); color: var(--primary-800); border: 1px solid var(--primary-200); font-size: 11px; font-weight: 600;">Keamanan</span>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?page=settings" id="form-password">
                    <?php echo \Core\Security::csrfField(); ?>

                    <!-- Password Lama -->
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label class="form-label" for="old_password">Password Lama</label>
                        <div class="pwd-wrapper" style="position: relative;">
                            <input type="password" name="old_password" id="old_password"
                                   class="form-control" required placeholder="Masukkan password lama"
                                   style="padding-right: 44px;">
                            <button type="button" class="pwd-eye-btn" data-target="old_password"
                                    aria-label="Tampilkan password lama" aria-pressed="false"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
                                           background: none; border: none; padding: 4px; cursor: pointer;
                                           color: var(--primary-700); display: flex; align-items: center;
                                           transition: color 0.2s, opacity 0.2s; opacity: 0.65; line-height: 1;">
                                <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Password Baru -->
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label class="form-label" for="new_password">Password Baru</label>
                        <div class="pwd-wrapper" style="position: relative;">
                            <input type="password" name="new_password" id="new_password"
                                   class="form-control" required placeholder="Minimal 8 karakter"
                                   style="padding-right: 44px;">
                            <button type="button" class="pwd-eye-btn" data-target="new_password"
                                    aria-label="Tampilkan password baru" aria-pressed="false"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
                                           background: none; border: none; padding: 4px; cursor: pointer;
                                           color: var(--primary-700); display: flex; align-items: center;
                                           transition: color 0.2s, opacity 0.2s; opacity: 0.65; line-height: 1;">
                                <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div class="form-group" style="margin-bottom: 22px;">
                        <label class="form-label" for="confirm_password">Konfirmasi Password Baru</label>
                        <div class="pwd-wrapper" style="position: relative;">
                            <input type="password" name="confirm_password" id="confirm_password"
                                   class="form-control" required placeholder="Ulangi password baru"
                                   style="padding-right: 44px;">
                            <button type="button" class="pwd-eye-btn" data-target="confirm_password"
                                    aria-label="Tampilkan konfirmasi password" aria-pressed="false"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
                                           background: none; border: none; padding: 4px; cursor: pointer;
                                           color: var(--primary-700); display: flex; align-items: center;
                                           transition: color 0.2s, opacity 0.2s; opacity: 0.65; line-height: 1;">
                                <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary" style="background-color: var(--primary-700); font-weight: 600;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Simpan Perubahan Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div><!-- /Kolom Kiri -->

    <!-- ===== Kolom Kanan: Keamanan & Info ===== -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <h3 style="font-size: 15px;">Keamanan Akun &amp; Proteksi Profil</h3>
            </div>
        </div>
        <div class="card-body" style="display: flex; flex-direction: column; gap: 14px; font-size: 13px;">
            <div style="background: var(--primary-soft); padding: 14px; border-radius: var(--radius-md); border-left: 3px solid var(--primary-600);">
                <strong style="color: var(--primary-900); display: block; margin-bottom: 2px;">• Proteksi Integritas Data &amp; RBAC</strong>
                <span style="color: var(--text-secondary);">Peran akun (Role) dilindungi secara read-only dan divalidasi ketat di sisi backend. Perubahan profil terisolasi hanya untuk akun Anda yang sedang aktif.</span>
            </div>

            <div style="background: var(--primary-soft); padding: 14px; border-radius: var(--radius-md); border-left: 3px solid var(--primary-600);">
                <strong style="color: var(--primary-900); display: block; margin-bottom: 2px;">• Sinkronisasi Otomatis Antar Modul</strong>
                <span style="color: var(--text-secondary);">Setiap pembaruan nama dan email secara instan disinkronkan ke header aplikasi, sesi login, data polis kendaraan, serta terekam pada Jejak Audit (Audit Log).</span>
            </div>

            <div style="background: var(--primary-soft); padding: 14px; border-radius: var(--radius-md); border-left: 3px solid var(--primary-600);">
                <strong style="color: var(--primary-900); display: block; margin-bottom: 2px;">• Keamanan Sesi &amp; CSRF Protection</strong>
                <span style="color: var(--text-secondary);">Setiap transaksi formulir dilindungi token CSRF sekali pakai, sanitasi htmlspecialchars(), dan PDO Prepared Statements anti-SQL Injection.</span>
            </div>

            <div style="margin-top: 10px;">
                <a href="index.php?page=logout" class="btn btn-outline" style="color: #b91c1c; border-color: #fecaca; width: 100%; font-weight: 600;">
                    Keluar dari Sesi (Logout)
                </a>
            </div>
        </div>
    </div><!-- /Kolom Kanan -->

</div><!-- /grid -->

<!-- ===== JavaScript ===== -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Show/Hide Password Toggle ────────────────────────────────────────
    document.querySelectorAll('.pwd-eye-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-target');
            var input    = document.getElementById(targetId);
            if (!input) return;

            var isHidden = (input.type === 'password');
            input.type   = isHidden ? 'text' : 'password';

            var iconEye    = btn.querySelector('.icon-eye');
            var iconEyeOff = btn.querySelector('.icon-eye-off');
            if (iconEye)    iconEye.style.display    = isHidden ? 'none'  : '';
            if (iconEyeOff) iconEyeOff.style.display = isHidden ? ''      : 'none';

            btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            btn.style.opacity = isHidden ? '1' : '0.65';
        });

        // Hover effect
        btn.addEventListener('mouseenter', function () {
            btn.style.opacity = '1';
        });
        btn.addEventListener('mouseleave', function () {
            var pressed = btn.getAttribute('aria-pressed') === 'true';
            btn.style.opacity = pressed ? '1' : '0.65';
        });
    });

    // ── 2. Edit Profil Toggle ────────────────────────────────────────────────
    var btnEdit    = document.getElementById('btn-edit-profile');
    var btnCancel  = document.getElementById('btn-cancel-edit');
    var editActions = document.getElementById('profile-edit-actions');
    var badgeMode  = document.getElementById('badge-profile-mode');

    var inputName  = document.getElementById('input-profile-name');
    var inputEmail = document.getElementById('input-profile-email');
    var reqName    = document.getElementById('req-name');
    var reqEmail   = document.getElementById('req-email');

    var formProfile = document.getElementById('form-profile');
    var btnSave     = document.getElementById('btn-save-profile');
    var alertBox    = document.getElementById('profile-alert');

    var originalName  = inputName  ? inputName.value  : '';
    var originalEmail = inputEmail ? inputEmail.value : '';

    function showAlert(msg, type) {
        alertBox.innerHTML = msg;
        alertBox.style.display = 'block';
        if (type === 'success') {
            alertBox.style.backgroundColor = '#ecfdf5';
            alertBox.style.color           = '#065f46';
            alertBox.style.border          = '1px solid #a7f3d0';
        } else {
            alertBox.style.backgroundColor = '#fef2f2';
            alertBox.style.color           = '#991b1b';
            alertBox.style.border          = '1px solid #fecaca';
        }
    }

    function hideAlert() {
        alertBox.style.display = 'none';
        alertBox.innerHTML = '';
    }

    function enterEditMode() {
        hideAlert();
        inputName.removeAttribute('readonly');
        inputEmail.removeAttribute('readonly');
        inputName.style.backgroundColor  = '#ffffff';
        inputEmail.style.backgroundColor = '#ffffff';
        inputName.style.borderColor      = 'var(--primary-500)';
        inputEmail.style.borderColor     = 'var(--primary-300)';

        if (reqName)  reqName.style.display  = 'inline';
        if (reqEmail) reqEmail.style.display = 'inline';

        badgeMode.textContent               = 'Mode Pengeditan Aktif';
        badgeMode.style.backgroundColor     = '#fef3c7';
        badgeMode.style.color               = '#92400e';
        badgeMode.style.borderColor         = '#fde68a';

        editActions.style.display = 'flex';
        btnEdit.style.display     = 'none';
        inputName.focus();
    }

    function exitEditMode(rollback) {
        if (rollback) {
            inputName.value  = originalName;
            inputEmail.value = originalEmail;
        }
        inputName.setAttribute('readonly', 'readonly');
        inputEmail.setAttribute('readonly', 'readonly');
        inputName.style.backgroundColor  = 'var(--primary-soft)';
        inputEmail.style.backgroundColor = 'var(--primary-soft)';
        inputName.style.borderColor      = '';
        inputEmail.style.borderColor     = '';

        if (reqName)  reqName.style.display  = 'none';
        if (reqEmail) reqEmail.style.display = 'none';

        badgeMode.textContent           = 'Mode Lihat';
        badgeMode.style.backgroundColor = 'var(--primary-soft)';
        badgeMode.style.color           = 'var(--primary-800)';
        badgeMode.style.borderColor     = 'var(--primary-200)';

        editActions.style.display = 'none';
        btnEdit.style.display     = 'inline-flex';
    }

    if (btnEdit)   btnEdit.addEventListener('click', enterEditMode);
    if (btnCancel) btnCancel.addEventListener('click', function () { hideAlert(); exitEditMode(true); });

    // ── 3. AJAX Submit Profil ────────────────────────────────────────────────
    if (formProfile) {
        formProfile.addEventListener('submit', function (e) {
            e.preventDefault();
            hideAlert();

            var nameVal  = inputName.value.trim();
            var emailVal = inputEmail.value.trim();

            if (!nameVal) { showAlert('Nama lengkap wajib diisi.', 'danger'); inputName.focus(); return; }
            if (nameVal.length < 3) { showAlert('Nama lengkap minimal 3 karakter.', 'danger'); inputName.focus(); return; }
            if (!emailVal) { showAlert('Alamat email wajib diisi.', 'danger'); inputEmail.focus(); return; }
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailVal)) { showAlert('Format alamat email tidak valid.', 'danger'); inputEmail.focus(); return; }

            btnSave.disabled   = true;
            btnCancel.disabled = true;
            var origHtml = btnSave.innerHTML;
            btnSave.innerHTML = '<svg class="spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite; margin-right: 6px;"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-linecap="round"></circle></svg>Menyimpan...';

            fetch('index.php?page=settings', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(formProfile)
            })
            .then(function (res) { return res.json().catch(function () { return null; }); })
            .then(function (data) {
                btnSave.disabled   = false;
                btnCancel.disabled = false;
                btnSave.innerHTML  = origHtml;

                if (data && data.success) {
                    originalName  = nameVal;
                    originalEmail = emailVal;

                    document.getElementById('display-header-name').textContent  = nameVal;
                    document.getElementById('display-header-email').textContent = emailVal;
                    var initial = nameVal.charAt(0).toUpperCase();
                    document.getElementById('avatar-initial').textContent = initial;

                    var topbarName   = document.querySelector('.user-details-top .name');
                    if (topbarName) topbarName.textContent = nameVal;
                    var topbarAvatar = document.querySelector('.user-avatar-top span');
                    if (topbarAvatar) topbarAvatar.textContent = initial;
                    var sidebarName  = document.querySelector('.user-info-name');
                    if (sidebarName) sidebarName.textContent = nameVal;
                    var sidebarAvatar = document.querySelector('.user-avatar-sm span');
                    if (sidebarAvatar) sidebarAvatar.textContent = initial;

                    exitEditMode(false);
                    showAlert(data.message || 'Informasi profil Anda berhasil diperbarui.', 'success');
                    if (typeof showToast === 'function') showToast(data.message || 'Profil berhasil diperbarui.', 'success');
                } else {
                    showAlert((data && data.message) ? data.message : 'Terjadi kesalahan saat menyimpan profil.', 'danger');
                }
            })
            .catch(function () {
                btnSave.disabled   = false;
                btnCancel.disabled = false;
                btnSave.innerHTML  = origHtml;
                showAlert('Gagal terhubung ke server. Periksa koneksi internet Anda.', 'danger');
            });
        });
    }
});
</script>

<style>
@keyframes spin {
    0%   { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
/* Pastikan input password tidak tertutupi ikon */
.pwd-wrapper .form-control {
    padding-right: 44px !important;
}
/* Responsif: stack ke satu kolom di layar kecil */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr !important;
    }
}
</style>
