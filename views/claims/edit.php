<!-- Ubah Pengajuan Klaim View - CLAIMORA -->
<div style="margin-bottom: 24px;">
    <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">
        <a href="index.php?page=dashboard">Dashboard</a> &rsaquo; 
        <a href="index.php?page=claims">Pengajuan Klaim</a> &rsaquo; 
        <a href="index.php?page=claims&action=detail&id=<?= (int)$claim['id'] ?>"><?= e($claim['claim_number']) ?></a> &rsaquo; 
        <span style="color: var(--text-primary); font-weight: 600;">Ubah Data</span>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Ubah Pengajuan Klaim</h2>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px; margin-bottom: 0;">
                Klaim No. <strong style="font-family: monospace; color: var(--primary-800);"><?= e($claim['claim_number']) ?></strong> &bull; Status: <span class="badge badge-survey" style="font-size: 11px;"><?= e($claim['status']) ?></span>
            </p>
        </div>
        <a href="index.php?page=claims&action=detail&id=<?= (int)$claim['id'] ?>" class="btn btn-outline" style="font-weight: 600;">
            &larr; Kembali ke Detail
        </a>
    </div>
</div>

<div class="card claim-edit-container">
    <form method="POST" action="index.php?page=claims&action=update" id="form-edit-claim" enctype="multipart/form-data">
        <?= \Core\Security::csrfField() ?>
        <input type="hidden" name="id" value="<?= (int)$claim['id'] ?>">
        <input type="hidden" name="uploaded_file_ids" id="input-uploaded-file-ids" value="[]">

        <!-- Informasi Polis -->
        <div style="margin-bottom: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; color: var(--primary-900); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span>Polis Asuransi Tertanggung</span>
            </h3>

            <div class="form-group">
                <label class="form-label" for="edit-policy-id">Pilih Polis Asuransi <span class="required">*</span></label>
                <select name="policy_id" id="edit-policy-id" class="form-control" required>
                    <?php foreach ($policies as $pol): ?>
                        <?php
                        $isSelected = ((int)$pol['id'] === (int)$claim['policy_id']);
                        $brandModel = trim(e($pol['vehicle_brand'] . ' ' . ($pol['vehicle_model'] ?? '')));
                        $plate = e($pol['vehicle_plate']);
                        $covText = 'Rp ' . number_format($pol['coverage_amount'], 0, ',', '.');
                        ?>
                        <option value="<?= (int)$pol['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                            <?= e($pol['policy_number']) ?> &bull; <?= e($pol['customer_name']) ?> &bull; <?= $plate ?> (<?= $brandModel ?>) &bull; Plafon: <?= $covText ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint">Hanya polis aktif tertanggung yang dapat diasosiasikan dengan klaim ini.</div>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 24px 0;">

        <!-- Informasi Kejadian -->
        <div style="margin-bottom: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; color: var(--primary-900); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Informasi Waktu & Lokasi Kejadian</span>
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="incident_date">Tanggal Kejadian <span class="required">*</span></label>
                    <input type="date" name="incident_date" id="incident_date" class="form-control" value="<?= e($claim['incident_date']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="incident_time">Waktu Kejadian <span class="required">*</span></label>
                    <input type="time" name="incident_time" id="incident_time" class="form-control" value="<?= e(substr($claim['incident_time'], 0, 5)) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="incident_location">Lokasi Kejadian Kecelakaan / Kerusakan <span class="required">*</span></label>
                <input type="text" name="incident_location" id="incident_location" class="form-control" value="<?= e($claim['incident_location']) ?>" placeholder="Contoh: Jl. Jendral Sudirman KM 12, Jakarta Selatan" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="estimated_damage_cost">Estimasi Biaya Kerusakan (Rp) <span class="required">*</span></label>
                <input type="text" name="estimated_damage_cost" id="estimated_damage_cost" class="form-control" value="Rp <?= number_format($claim['estimated_damage_cost'], 0, ',', '.') ?>" data-rupiah required>
                <div class="form-hint">Estimasi awal biaya perbaikan menurut bengkel atau perkiraan tertanggung.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="chronology">Kronologi Kejadian Kerusakan <span class="required">*</span></label>
                <textarea name="chronology" id="chronology" class="form-control" rows="4" required minlength="15" placeholder="Jelaskan secara rinci kronologi tabrakan/kerusakan kendaraan..."><?= e($claim['chronology']) ?></textarea>
                <div class="form-hint">Minimal 15 karakter. Deskripsi detail mempercepat verifikasi surveyor lapangan.</div>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 24px 0;">

        <!-- Seksi Foto Bukti Kerusakan -->
        <div style="margin-bottom: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; color: var(--primary-900); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                <span>Bukti Foto Kerusakan Kendaraan</span>
            </h3>
            <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 16px;">
                Foto yang sudah diunggah tersimpan aman. Anda dapat menghapus foto tertentu jika salah atau menambahkan foto bukti baru di bawah.
            </p>

            <!-- Foto yang sudah tersimpan -->
            <?php if (!empty($claim['files'])): ?>
                <div style="margin-bottom: 20px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600;">Foto yang Sudah Tersimpan (<?= count($claim['files']) ?> file):</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px; margin-top: 8px;">
                        <?php foreach ($claim['files'] as $file): ?>
                            <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; background: #fff; position: relative;" id="card-file-<?= (int)$file['id'] ?>">
                                <div style="aspect-ratio: 4/3; background: #111; overflow: hidden;">
                                    <img src="<?= e($file['file_path']) ?>" alt="<?= e($file['original_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div style="padding: 8px 10px; font-size: 11px;">
                                    <strong style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-primary);"><?= e($file['original_name']) ?></strong>
                                    <span style="color: var(--text-muted);"><?= round($file['file_size'] / 1024, 1) ?> KB</span>
                                </div>
                                <div style="padding: 6px 10px 8px; border-top: 1px dashed var(--border-color); background: #fafafa;">
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: #b91c1c; cursor: pointer; margin: 0;">
                                        <input type="checkbox" name="delete_file_ids[]" value="<?= (int)$file['id'] ?>" onchange="toggleFileCardMark(this, 'card-file-<?= (int)$file['id'] ?>')">
                                        <span>Tandai Hapus Foto</span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tambah foto baru -->
            <div style="background: var(--primary-soft); padding: 18px 20px; border-radius: var(--radius-md); border: 1px dashed var(--primary-300);">
                <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--primary-900); margin-bottom: 4px;">
                    + Tambah Foto Kerusakan Baru (Opsional)
                </label>
                <p style="font-size: 12px; color: var(--text-secondary); margin-top: 0; margin-bottom: 12px;">
                    Pilih satu atau beberapa foto tambahan (Format: JPG, PNG, WEBP &bull; Maks. 5MB per file).
                </p>
                <input type="file" name="damage_photos[]" multiple accept="image/jpeg,image/png,image/webp" class="form-control" style="background: #fff;">
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid var(--border-color);">
            <a href="index.php?page=claims&action=detail&id=<?= (int)$claim['id'] ?>" class="btn btn-outline" style="font-weight: 600;">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="background-color: var(--primary-700); font-weight: 600; padding: 10px 22px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                <span>Simpan Perubahan Klaim</span>
            </button>
        </div>
    </form>
</div>

<script>
function toggleFileCardMark(checkbox, cardId) {
    const card = document.getElementById(cardId);
    if (!card) return;
    if (checkbox.checked) {
        card.style.opacity = '0.45';
        card.style.border = '1px solid #ef4444';
    } else {
        card.style.opacity = '1';
        card.style.border = '1px solid var(--border-color)';
    }
}
</script>

