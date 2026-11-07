<?php
$activeNav = $activeNav ?? 'dashboard';
$curPage   = $_GET['page'] ?? 'dashboard';
$curAction = $_GET['action'] ?? '';
$role      = $user['role'] ?? 'admin';

$isBtmDashboard = ($activeNav === 'dashboard') || ($curPage === 'dashboard' && empty($curAction));
$isBtmPolicies  = ($activeNav === 'policies') || ($curPage === 'policies');
$isBtmVehicles  = ($activeNav === 'vehicles') || ($curPage === 'vehicles');
$isBtmClaims    = in_array($activeNav, ['claims', 'claims_history', 'claims_create'], true) || ($curPage === 'claims');
$isBtmSurvey    = ($activeNav === 'survey') || ($curPage === 'survey');
$isBtmProfile   = in_array($activeNav, ['settings', 'profile'], true) || in_array($curPage, ['settings', 'profile'], true);
?>
<!-- Mobile Bottom Navigation Bar (Visible only on mobile <= 768px) -->
<nav class="mobile-bottom-nav" aria-label="Navigasi Bawah Mobile">
    <div class="bottom-nav-items">
        <a href="index.php?page=dashboard" class="bottom-nav-item <?= $isBtmDashboard ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Beranda</span>
        </a>

        <a href="index.php?page=policies" class="bottom-nav-item <?= $isBtmPolicies ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            <span>Polis</span>
        </a>

        <?php if ($role === 'customer'): ?>
        <a href="index.php?page=vehicles" class="bottom-nav-item <?= $isBtmVehicles ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            <span>Kendaraan</span>
        </a>
        <?php endif; ?>

        <a href="index.php?page=claims" class="bottom-nav-item <?= $isBtmClaims ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            <span>Klaim</span>
        </a>

        <?php if ($role !== 'customer'): ?>
        <a href="index.php?page=survey" class="bottom-nav-item <?= $isBtmSurvey ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
            <span>Survey</span>
        </a>
        <?php endif; ?>

        <a href="index.php?page=settings" class="bottom-nav-item <?= $isBtmProfile ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span>Profil</span>
        </a>
    </div>
</nav>
