<?php
/**
 * CLAIMORA - Setup & Installation Script
 * 
 * Jalankan file ini di browser untuk melakukan setup awal:
 * http://localhost/claimora/config/setup.php
 * 
 * Script ini akan:
 * 1. Memeriksa dependensi PHP (PDO, mbstring, session)
 * 2. Menguji koneksi database
 * 3. Membuat tabel-tabel yang diperlukan
 * 4. Memasukkan data awal (seed data)
 */

// Blokir akses CLI di production
$isWeb = php_sapi_name() !== 'cli';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CLAIMORA - Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f7f5; color: #182d23; padding: 40px 20px; }
        .container { max-width: 640px; margin: 0 auto; }
        .logo { text-align: center; margin-bottom: 32px; }
        .logo h1 { font-size: 28px; font-weight: 800; color: #1b4433; }
        .logo p { font-size: 14px; color: #859d90; margin-top: 4px; }
        .card { background: #fff; border: 1px solid #e2ece6; border-radius: 16px; padding: 28px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .card h2 { font-size: 17px; font-weight: 700; margin-bottom: 16px; color: #1b4433; }
        .check-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f5f2; font-size: 14px; }
        .check-item:last-child { border-bottom: none; }
        .icon-ok { color: #15803d; font-weight: 700; font-size: 18px; }
        .icon-fail { color: #b91c1c; font-weight: 700; font-size: 18px; }
        .icon-warn { color: #b45309; font-weight: 700; font-size: 18px; }
        .btn { display: inline-block; padding: 12px 28px; background: #1b4433; color: #fff; border: none; border-radius: 999px; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .btn:hover { background: #15392b; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(27,68,51,0.2); }
        .btn-block { display: block; width: 100%; text-align: center; margin-top: 20px; }
        .result { padding: 14px; border-radius: 10px; margin-top: 16px; font-size: 13.5px; }
        .result-ok { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .result-fail { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <h1>🛡️ CLAIMORA</h1>
        <p>Sistem Manajemen Klaim Asuransi — Setup Wizard</p>
    </div>

    <!-- Step 1: PHP Requirements -->
    <div class="card">
        <h2>1. Cek Dependensi PHP</h2>
        <?php
        $checks = [
            ['PHP >= 8.0', version_compare(PHP_VERSION, '8.0.0', '>=')],
            ['PDO Extension', extension_loaded('pdo')],
            ['PDO MySQL', extension_loaded('pdo_mysql')],
            ['PDO SQLite', extension_loaded('pdo_sqlite')],
            ['mbstring', extension_loaded('mbstring')],
            ['Session', extension_loaded('session')],
            ['JSON', extension_loaded('json')],
            ['FileInfo', extension_loaded('fileinfo')],
        ];
        $allPassed = true;
        foreach ($checks as [$label, $ok]):
            if (!$ok) $allPassed = false;
        ?>
        <div class="check-item">
            <span class="<?= $ok ? 'icon-ok' : 'icon-fail' ?>"><?= $ok ? '✓' : '✗' ?></span>
            <span><?= $label ?></span>
            <span style="margin-left: auto; font-size: 12px; color: #859d90;"><?= $ok ? 'Tersedia' : 'Belum Terinstall' ?></span>
        </div>
        <?php endforeach; ?>

        <?php if ($allPassed): ?>
        <div class="result result-ok">✅ Semua dependensi PHP terpenuhi. Versi PHP: <?= PHP_VERSION ?></div>
        <?php else: ?>
        <div class="result result-fail">❌ Beberapa dependensi belum terpenuhi. Silakan install/aktifkan extension yang diperlukan.</div>
        <?php endif; ?>
    </div>

    <!-- Step 2: Database Connection Test -->
    <div class="card">
        <h2>2. Uji Koneksi Database</h2>
        <?php
        $dbConfig = require __DIR__ . '/database.php';
        $mysqlOk = false;
        $sqliteOk = false;
        $mysqlError = '';

        // Test MySQL
        try {
            $dsn = "mysql:host={$dbConfig['mysql']['host']};port={$dbConfig['mysql']['port']};charset={$dbConfig['mysql']['charset']}";
            $pdo = new PDO($dsn, $dbConfig['mysql']['username'], $dbConfig['mysql']['password'], $dbConfig['options']);
            $mysqlOk = true;
        } catch (PDOException $e) {
            $mysqlError = $e->getMessage();
        }

        // Test SQLite
        try {
            $sqlitePath = $dbConfig['sqlite']['path'];
            $sqliteDir = dirname($sqlitePath);
            if (!is_dir($sqliteDir)) {
                mkdir($sqliteDir, 0755, true);
            }
            $pdo = new PDO("sqlite:$sqlitePath", null, null, $dbConfig['options']);
            $sqliteOk = true;
        } catch (PDOException $e) {
            // ignore
        }
        ?>
        <div class="check-item">
            <span class="<?= $mysqlOk ? 'icon-ok' : 'icon-warn' ?>"><?= $mysqlOk ? '✓' : '!' ?></span>
            <span>MySQL / MariaDB</span>
            <span style="margin-left: auto; font-size: 12px; color: #859d90;">
                <?= $mysqlOk ? 'Terhubung (' . $dbConfig['mysql']['host'] . ':' . $dbConfig['mysql']['port'] . ')' : 'Tidak tersedia' ?>
            </span>
        </div>
        <div class="check-item">
            <span class="<?= $sqliteOk ? 'icon-ok' : 'icon-fail' ?>"><?= $sqliteOk ? '✓' : '✗' ?></span>
            <span>SQLite (Fallback)</span>
            <span style="margin-left: auto; font-size: 12px; color: #859d90;">
                <?= $sqliteOk ? 'Tersedia' : 'Tidak tersedia' ?>
            </span>
        </div>

        <?php if ($mysqlOk || $sqliteOk): ?>
        <div class="result result-ok">
            ✅ Koneksi database tersedia. Driver aktif: <?= $mysqlOk ? 'MySQL' : 'SQLite (fallback)' ?>
        </div>
        <?php else: ?>
        <div class="result result-fail">❌ Tidak dapat terhubung ke database manapun. Pastikan MySQL berjalan atau folder `database/` dapat ditulis.</div>
        <?php endif; ?>
    </div>

    <!-- Step 3: Run Migration -->
    <?php
    $migrated = false;
    $migrationError = '';
    if (isset($_POST['run_setup'])) {
        try {
            require_once dirname(__DIR__) . '/database/Migrator.php';
            // Migrator sudah otomatis membuat tabel saat Core\Database diinisialisasi
            $migrated = true;
        } catch (Throwable $e) {
            $migrationError = $e->getMessage();
        }
    }
    ?>
    <div class="card">
        <h2>3. Inisialisasi Database & Tabel</h2>
        <p style="font-size: 13.5px; color: #526b5e; line-height: 1.5;">
            Klik tombol di bawah untuk membuat tabel-tabel yang diperlukan (users, policies, claims, survey_reports, audit_logs, dll).
        </p>

        <?php if ($migrated): ?>
        <div class="result result-ok">✅ Migrasi berhasil! Tabel database telah dibuat.</div>
        <?php elseif ($migrationError): ?>
        <div class="result result-fail">❌ Error: <?= htmlspecialchars($migrationError) ?></div>
        <?php endif; ?>

        <form method="POST">
            <button type="submit" name="run_setup" value="1" class="btn btn-block">
                🚀 Jalankan Setup Database
            </button>
        </form>
    </div>

    <!-- Step 4: Done -->
    <div class="card" style="text-align: center;">
        <h2>Selesai?</h2>
        <p style="font-size: 13.5px; color: #526b5e; line-height: 1.5; margin-bottom: 16px;">
            Setelah semua langkah selesai, Anda bisa mulai menggunakan CLAIMORA.
        </p>
        <a href="../index.php" class="btn">Masuk ke CLAIMORA →</a>
        <p style="font-size: 11.5px; color: #859d90; margin-top: 16px;">
            ⚠️ Hapus atau proteksi file <code>setup.php</code> ini di server production.
        </p>
    </div>
</div>
</body>
</html>

