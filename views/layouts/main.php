<?php
require __DIR__ . '/header.php';
require __DIR__ . '/sidebar.php';
?>
<div class="app-main">
    <?php require __DIR__ . '/topbar.php'; ?>

    <main class="app-viewport">
        <!-- System Alerts / Flash Messages -->
        <?php
        $flashes = \Core\Session::getAllFlashes();
        if (!empty($flashes)):
        ?>
            <div class="alert-container">
                <?php foreach ($flashes as $type => $msg): ?>
                    <div class="alert alert-<?= e($type) ?>">
                        <span><?= flash_html($msg) ?></span>
                        <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Injected View Content -->
        <?= $content ?? '' ?>
    </main>

    <?php require __DIR__ . '/bottomnav.php'; ?>
</div>
</div><!-- /.app-container -->
<?php require __DIR__ . '/footer.php'; ?>
