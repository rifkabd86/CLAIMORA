<?php
/**
 * CLAIMORA - Dedicated Notifications View
 * Terpisah dari Dashboard & Audit Log
 */
?>
<!-- Notifications Header & Breadcrumb -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">
            <a href="index.php?page=dashboard">Dashboard</a> &rsaquo; <span style="color: var(--text-primary); font-weight: 600;">Notifikasi</span>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Pusat Notifikasi</h2>
            <?php if ($unreadCount > 0): ?>
                <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; font-size: 12px; font-weight: 700; padding: 3px 10px;">
                    <?= $unreadCount ?> Belum Dibaca
                </span>
            <?php else: ?>
                <span class="badge" style="background: #eaf7f0; color: #15803d; border: 1px solid #bbf7d0; font-size: 12px; font-weight: 600; padding: 3px 10px;">
                    Semua Terbaca
                </span>
            <?php endif; ?>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">
            Pemberitahuan resmi mengenai perkembangan klaim, masa berlaku polis, verifikasi kendaraan, dan pembaruan sistem Anda.
        </p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <?php if ($unreadCount > 0): ?>
            <a href="index.php?page=notifications&action=markAllRead" class="btn btn-outline" style="font-size: 12.5px; padding: 9px 16px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>Tandai Semua Sudah Dibaca</span>
            </a>
        <?php endif; ?>
        <a href="index.php?page=claims&action=create" class="btn btn-primary" style="font-size: 12.5px; padding: 9px 18px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ Ajukan Klaim</span>
        </a>
    </div>
</div>

<!-- Filter & Search Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
    <form method="GET" action="index.php" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;" class="filter-bar-flex">
        <input type="hidden" name="page" value="notifications">

        <div style="flex-grow: 1; min-width: 240px; position: relative;">
            <input type="text" name="q" id="notif-search" class="form-control" placeholder="Cari isi notifikasi atau judul..." value="<?= e($query ?? '') ?>">
        </div>

        <div style="width: 220px;">
            <select name="category" class="form-control" onchange="this.form.submit()">
                <option value="" <?= empty($currentCategory) ? 'selected' : '' ?>>Semua Kategori</option>
                <option value="Pengajuan Klaim" <?= ($currentCategory ?? '') === 'Pengajuan Klaim' ? 'selected' : '' ?>>Pengajuan Klaim</option>
                <option value="Status Verifikasi" <?= ($currentCategory ?? '') === 'Status Verifikasi' ? 'selected' : '' ?>>Status Verifikasi</option>
                <option value="Persetujuan Klaim" <?= ($currentCategory ?? '') === 'Persetujuan Klaim' ? 'selected' : '' ?>>Persetujuan Klaim</option>
                <option value="Penolakan Klaim" <?= ($currentCategory ?? '') === 'Penolakan Klaim' ? 'selected' : '' ?>>Penolakan Klaim</option>
                <option value="Informasi Polis" <?= ($currentCategory ?? '') === 'Informasi Polis' ? 'selected' : '' ?>>Informasi Polis</option>
                <option value="Informasi Kendaraan" <?= ($currentCategory ?? '') === 'Informasi Kendaraan' ? 'selected' : '' ?>>Informasi Kendaraan</option>
                <option value="Informasi Sistem" <?= ($currentCategory ?? '') === 'Informasi Sistem' ? 'selected' : '' ?>>Informasi Sistem</option>
            </select>
        </div>

        <div style="width: 170px;">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="" <?= empty($currentStatus) ? 'selected' : '' ?>>Semua Status</option>
                <option value="unread" <?= ($currentStatus ?? '') === 'unread' ? 'selected' : '' ?>>Belum Dibaca</option>
                <option value="read" <?= ($currentStatus ?? '') === 'read' ? 'selected' : '' ?>>Sudah Dibaca</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($query) || !empty($currentCategory) || !empty($currentStatus)): ?>
            <a href="index.php?page=notifications" class="btn btn-outline">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Category Tabs -->
<div class="tabs-nav" style="overflow-x: auto; padding-bottom: 2px;">
    <?php
    $catList = [
        ''                     => 'Semua',
        'Pengajuan Klaim'      => 'Klaim',
        'Status Verifikasi'    => 'Verifikasi',
        'Persetujuan Klaim'    => 'Disetujui',
        'Informasi Polis'      => 'Polis',
        'Informasi Kendaraan'  => 'Kendaraan',
        'Informasi Sistem'     => 'Sistem'
    ];
    ?>
    <?php foreach ($catList as $cKey => $cLabel): ?>
        <a href="index.php?page=notifications<?= !empty($cKey) ? '&category=' . urlencode($cKey) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?>" 
           class="tab-btn <?= ($currentCategory === $cKey) ? 'active' : '' ?>">
            <?= e($cLabel) ?>
            <?php if (!empty($cKey) && isset($categoryCounts[$cKey]) && $categoryCounts[$cKey]['unread'] > 0): ?>
                <span style="background: #ef4444; color: #fff; font-size: 10.5px; border-radius: 10px; padding: 1px 6px; margin-left: 4px; font-weight: 700;">
                    <?= $categoryCounts[$cKey]['unread'] ?>
                </span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Notification List -->
<?php if (!empty($notifications)): ?>
    <div style="display: flex; flex-direction: column; gap: 12px;" id="notifications-container">
        <?php foreach ($notifications as $n): ?>
            <?php
            $isUnread = ((int)$n['is_read'] === 0);
            $cat = $n['category'];

            // Resolusi ikon & warna berdasarkan kategori
            $badgeColor = match($cat) {
                'Persetujuan Klaim'     => ['bg' => '#eaf7f0', 'text' => '#15803d', 'border' => '#bbf7d0', 'icon' => 'check'],
                'Penolakan Klaim'       => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fecaca', 'icon' => 'alert'],
                'Status Verifikasi'     => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe', 'icon' => 'search'],
                'Pengajuan Klaim'       => ['bg' => '#faf5ff', 'text' => '#7e22ce', 'border' => '#e9d5ff', 'icon' => 'file'],
                'Informasi Polis'       => ['bg' => '#fef3c7', 'text' => '#b45309', 'border' => '#fde68a', 'icon' => 'shield'],
                'Informasi Kendaraan'   => ['bg' => '#e0f2fe', 'text' => '#0369a1', 'border' => '#bae6fd', 'icon' => 'car'],
                default                 => ['bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#cbd5e1', 'icon' => 'info']
            };
            ?>
            <div class="card" style="padding: 20px 24px; transition: all 0.2s; border-left: 4px solid <?= $isUnread ? 'var(--primary-600)' : 'transparent' ?>; background: <?= $isUnread ? '#ffffff' : 'var(--bg-surface)' ?>; position: relative;">
                <div style="display: flex; gap: 18px; align-items: flex-start;">
                    <!-- Category Icon -->
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: <?= $badgeColor['bg'] ?>; color: <?= $badgeColor['text'] ?>; border: 1px solid <?= $badgeColor['border'] ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <?php if ($badgeColor['icon'] === 'check'): ?>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <?php elseif ($badgeColor['icon'] === 'alert'): ?>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <?php elseif ($badgeColor['icon'] === 'search'): ?>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <?php elseif ($badgeColor['icon'] === 'shield'): ?>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <?php elseif ($badgeColor['icon'] === 'car'): ?>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                        <?php elseif ($badgeColor['icon'] === 'file'): ?>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        <?php else: ?>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <?php endif; ?>
                    </div>

                    <!-- Content Body -->
                    <div style="flex-grow: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: <?= $badgeColor['text'] ?>; letter-spacing: 0.5px;">
                                        <?= e($n['category']) ?>
                                    </span>
                                    <?php if ($isUnread): ?>
                                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #ef4444; display: inline-block;" title="Belum dibaca"></span>
                                    <?php endif; ?>
                                </div>
                                <h3 style="font-size: 15.5px; font-weight: <?= $isUnread ? '800' : '700' ?>; color: var(--text-primary); margin-bottom: 6px; line-height: 1.3;">
                                    <?= e($n['title']) ?>
                                </h3>
                            </div>

                            <div style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <span><?= date('d M Y, H:i', strtotime($n['created_at'])) ?> WIB</span>
                            </div>
                        </div>

                        <p style="font-size: 13.5px; color: var(--text-secondary); line-height: 1.55; margin: 0 0 12px 0;">
                            <?= e($n['message']) ?>
                        </p>

                        <!-- Action Bar for each notification -->
                        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                            <?php if (!empty($n['link'])): ?>
                                <a href="<?= e($n['link']) ?>" class="btn btn-outline" style="font-size: 12px; padding: 5px 14px;">
                                    Buka Detail Terkait &rarr;
                                </a>
                            <?php endif; ?>

                            <?php if ($isUnread): ?>
                                <a href="index.php?page=notifications&action=markRead&id=<?= (int)$n['id'] ?>" class="btn-link" style="font-size: 12px; font-weight: 600; color: var(--primary-700); text-decoration: underline;">
                                    Tandai Sudah Dibaca
                                </a>
                            <?php else: ?>
                                <span style="font-size: 11.5px; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Sudah dibaca
                                </span>
                            <?php endif; ?>

                            <!-- Delete button with confirmation -->
                            <form method="POST" action="index.php?page=notifications&action=delete" style="display: inline; margin-left: auto;" class="form-delete-notification">
                                <?= \Core\Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                                <button type="button" class="btn-icon btn-delete-notif" style="width: 28px; height: 28px;" title="Hapus Notifikasi" data-notif-title="<?= e($n['title']) ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- Empty State -->
    <div class="card" style="padding: 60px 24px; text-align: center;">
        <div style="width: 68px; height: 68px; border-radius: 50%; background: #eaf7f0; color: #15803d; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px auto;">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </div>
        <h3 style="font-size: 17px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">
            Tidak Ada Notifikasi
        </h3>
        <p style="font-size: 13.5px; color: var(--text-muted); max-width: 440px; margin: 0 auto 20px auto; line-height: 1.5;">
            <?php if (!empty($query) || !empty($currentCategory) || !empty($currentStatus)): ?>
                Tidak ada notifikasi yang sesuai dengan kriteria filter pencarian Anda. Silakan reset filter.
            <?php else: ?>
                Saat ini belum ada pemberitahuan baru untuk akun Anda. Seluruh pembaruan klaim dan polis akan muncul di sini.
            <?php endif; ?>
        </p>
        <?php if (!empty($query) || !empty($currentCategory) || !empty($currentStatus)): ?>
            <a href="index.php?page=notifications" class="btn btn-outline" style="font-size: 13px;">Reset Filter Pencarian</a>
        <?php else: ?>
            <a href="index.php?page=dashboard" class="btn btn-primary" style="font-size: 13px;">&larr; Kembali ke Dashboard</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-delete-notif').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const notifTitle = this.getAttribute('data-notif-title') || 'notifikasi ini';
            
            if (typeof confirmDelete === 'function') {
                confirmDelete(
                    'Konfirmasi Hapus Notifikasi',
                    'Apakah Anda yakin ingin menghapus notifikasi "' + notifTitle + '"?\nData yang dihapus tidak dapat dipulihkan.',
                    function() {
                        form.submit();
                    }
                );
            } else if (confirm('Apakah Anda yakin ingin menghapus notifikasi "' + notifTitle + '"?')) {
                form.submit();
            }
        });
    });
});
</script>

