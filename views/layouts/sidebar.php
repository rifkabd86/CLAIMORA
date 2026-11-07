<?php
$activeNav = $activeNav ?? 'dashboard';
$user = \Core\Session::get('user') ?? [
    'name' => 'Andi Pratama',
    'role_label' => 'Admin Asuransi',
    'avatar' => 'andi_pratama.jpg'
];

$currentPage   = $_GET['page'] ?? 'dashboard';
$currentAction = $_GET['action'] ?? '';

// Robust menu active flags checking both $activeNav and current URL query parameters
$isDashboard   = ($activeNav === 'dashboard') || ($currentPage === 'dashboard' && empty($currentAction));
$isPolicies    = ($activeNav === 'policies') || ($currentPage === 'policies');
$isVehicles    = ($activeNav === 'vehicles') || ($currentPage === 'vehicles');
$isClaimCreate = ($activeNav === 'claims_create') || ($currentPage === 'claims' && $currentAction === 'create');
$isClaimHistory= (($activeNav === 'claims_history' || $activeNav === 'claims') && !$isClaimCreate) || ($currentPage === 'claims' && $currentAction !== 'create');
$isProfile     = in_array($activeNav, ['settings', 'profile'], true) || in_array($currentPage, ['settings', 'profile'], true);
$isSurvey      = ($activeNav === 'survey') || ($currentPage === 'survey');
$isAudit       = ($activeNav === 'audit') || ($currentPage === 'audit');
$isNotifications = ($activeNav === 'notifications') || ($currentPage === 'notifications');

$unreadNotifCount = 0;
try {
    $unreadNotifCount = (new \Models\NotificationModel())->getUnreadCount((int)($user['id'] ?? 0));
} catch (\Throwable $e) {
    // fallback if table is not yet initialized
}
?>
<!-- Sidebar Navigation (Desktop & Mobile Drawer) -->
<aside class="app-sidebar" id="appSidebar">
    <div>
        <!-- Brand Header -->
        <div class="sidebar-brand">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 15l-4-4 1.41-1.41L10 13.17l6.59-6.59L18 8l-8 8z"/>
                </svg>
            </div>
            <div class="brand-text">
                <h1>CLAIMORA</h1>
                <p>Claim Made Easy</p>
            </div>
            <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" data-sidebar-close aria-label="Tutup Menu Navigasi" title="Tutup Menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <?php if (($user['role'] ?? '') === 'customer'): ?>
                <!-- Dedicated Nasabah (Customer) Navigation Menu -->
                <a href="index.php?page=dashboard" class="nav-link <?= $isDashboard ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <span>Dashboard</span>
                    </div>
                </a>

                <a href="index.php?page=policies" class="nav-link <?= $isPolicies ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span>Polis Saya</span>
                    </div>
                    <span class="nav-arrow">&rsaquo;</span>
                </a>

                <a href="index.php?page=vehicles" class="nav-link <?= $isVehicles ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                        <span>Kendaraan Saya</span>
                    </div>
                    <span class="nav-arrow">&rsaquo;</span>
                </a>

                <a href="index.php?page=claims&action=create" class="nav-link <?= $isClaimCreate ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Pengajuan Klaim</span>
                    </div>
                    <span class="nav-arrow">&rsaquo;</span>
                </a>

                <a href="index.php?page=claims" class="nav-link <?= $isClaimHistory ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Riwayat Klaim</span>
                    </div>
                    <span class="nav-arrow">&rsaquo;</span>
                </a>

                <a href="index.php?page=notifications" class="nav-link <?= $isNotifications ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span>Notifikasi</span>
                    </div>
                    <?php if ($unreadNotifCount > 0): ?>
                        <span style="background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 10px;">
                            <?= $unreadNotifCount ?>
                        </span>
                    <?php else: ?>
                        <span class="nav-arrow">&rsaquo;</span>
                    <?php endif; ?>
                </a>

                <a href="index.php?page=settings" class="nav-link <?= $isProfile ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span>Profil Saya</span>
                    </div>
                    <span class="nav-arrow">&rsaquo;</span>
                </a>

                <a href="#" class="nav-link" data-open-chat>
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <span>Bantuan</span>
                    </div>
                </a>
            <?php else: ?>
                <!-- Admin Navigation Menu -->
                <a href="index.php?page=dashboard" class="nav-link <?= $isDashboard ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <span>Dashboard</span>
                    </div>
                </a>

                <a href="index.php?page=policies" class="nav-link <?= $isPolicies ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span>Data Polis</span>
                    </div>
                    <span class="nav-arrow">&rsaquo;</span>
                </a>

                <a href="index.php?page=vehicles" class="nav-link <?= $isVehicles ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                        <span>Data Kendaraan</span>
                    </div>
                    <span class="nav-arrow">&rsaquo;</span>
                </a>

                <a href="index.php?page=claims" class="nav-link <?= ($isClaimCreate || $isClaimHistory) ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <span>Pengajuan Klaim</span>
                    </div>
                    <span class="nav-arrow">&rsaquo;</span>
                </a>

                <a href="index.php?page=survey" class="nav-link <?= $isSurvey ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                        <span>Hasil Survey</span>
                    </div>
                    <span class="nav-arrow">&rsaquo;</span>
                </a>

                <a href="index.php?page=audit" class="nav-link <?= $isAudit ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Audit Log</span>
                    </div>
                </a>

                <a href="index.php?page=notifications" class="nav-link <?= $isNotifications ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span>Notifikasi</span>
                    </div>
                    <?php if ($unreadNotifCount > 0): ?>
                        <span style="background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 10px;">
                            <?= $unreadNotifCount ?>
                        </span>
                    <?php else: ?>
                        <span class="nav-arrow">&rsaquo;</span>
                    <?php endif; ?>
                </a>

                <a href="index.php?page=settings" class="nav-link <?= $isProfile ? 'active' : '' ?>">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <span>Pengaturan</span>
                    </div>
                </a>

                <a href="#" class="nav-link" data-open-chat>
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <span>Bantuan</span>
                    </div>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <div>
        <!-- Support Widget Card -->
        <div class="sidebar-support">
            <div class="support-header">
                <div class="support-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
                </div>
                <div class="support-text">
                    <h4>Butuh Bantuan?</h4>
                    <p>Hubungi Support Kami</p>
                </div>
            </div>
            <button type="button" class="btn-support-chat" data-open-chat>Live Chat</button>
        </div>

        <!-- User Profile & Logout -->
        <div class="sidebar-user">
            <a href="index.php?page=settings" class="user-snippet" style="text-decoration: none; color: inherit; cursor: pointer;" title="Buka Profil Saya">
                <div class="user-avatar-sm">
                    <span><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></span>
                </div>
                <div class="user-info-text">
                    <div class="user-info-name"><?= e($user['name'] ?? 'Andi Pratama') ?></div>
                    <div class="user-info-role"><?= e($user['role_label'] ?? 'Admin Asuransi') ?></div>
                </div>
            </a>
            <a href="index.php?page=logout" class="btn-logout-sidebar" title="Keluar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </a>
        </div>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
