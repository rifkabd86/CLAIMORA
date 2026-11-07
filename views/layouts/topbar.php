<?php
$user = \Core\Session::get('user') ?? [
    'name' => 'Andi Pratama',
    'role_label' => 'Admin Asuransi'
];

$topbarUnreadCount = 0;
try {
    $topbarUnreadCount = (new \Models\NotificationModel())->getUnreadCount((int)($user['id'] ?? 0));
} catch (\Throwable $e) {}
?>
<header class="app-topbar">
    <div class="topbar-left">
        <!-- Mobile Drawer Toggle Button -->
        <button type="button" class="mobile-menu-toggle" aria-label="Buka Menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <!-- Brand Identity: CLAIMORA & Tagline -->
        <div class="topbar-brand">
            <h1 class="topbar-brand-title">CLAIMORA</h1>
            <span class="topbar-brand-tagline">Claim Made Easy</span>
        </div>
    </div>

    <div class="topbar-right">
        <!-- Notification Bell Link to Notifications page -->
        <a href="index.php?page=notifications" class="btn-notification" title="Pusat Notifikasi">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <?php if ($topbarUnreadCount > 0): ?>
                <span class="notification-badge"><?= $topbarUnreadCount ?></span>
            <?php endif; ?>
        </a>

        <!-- User Profile Chip -->
        <a href="index.php?page=settings" class="user-profile-menu">
            <div class="user-avatar-top">
                <span style="display:flex;align-items:center;justify-content:center;height:100%;color:#fff;background:var(--primary-600);font-weight:700;">
                    <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
                </span>
            </div>
            <div class="user-details-top">
                <div class="name"><?= e($user['name'] ?? 'Andi Pratama') ?></div>
                <div class="role"><?= e($user['role_label'] ?? 'Admin Asuransi') ?></div>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </a>
    </div>
</header>
