<?php
namespace Database;

use PDO;

class Migrator
{
    public static function run(PDO $pdo, string $driver = 'mysql'): void
    {
        if ($driver === 'sqlite') {
            self::runSqlite($pdo);
        } else {
            self::runMysql($pdo);
        }
        self::ensureCancellationSchema($pdo, $driver);
        self::seedData($pdo);
    }

    public static function ensureCancellationSchema(PDO $pdo, string $driver = 'mysql'): void
    {
        if ($driver === 'mysql') {
            try {
                $pdo->exec("ALTER TABLE `claims` MODIFY COLUMN `status` ENUM('Pengajuan Baru', 'Survey Lapangan', 'Dalam Audit', 'Disetujui', 'Ditolak', 'Dibatalkan') NOT NULL DEFAULT 'Pengajuan Baru'");
            } catch (\Throwable $e) {}

            $cols = [
                'cancellation_reason' => "ALTER TABLE `claims` ADD COLUMN `cancellation_reason` TEXT NULL AFTER `status`",
                'cancelled_at'        => "ALTER TABLE `claims` ADD COLUMN `cancelled_at` DATETIME NULL AFTER `cancellation_reason`",
                'cancelled_by'        => "ALTER TABLE `claims` ADD COLUMN `cancelled_by` INT NULL AFTER `cancelled_at`, ADD CONSTRAINT `fk_claims_cancelled_by` FOREIGN KEY (`cancelled_by`) REFERENCES `users`(`id`) ON DELETE SET NULL"
            ];
            foreach ($cols as $col => $sql) {
                try {
                    $check = $pdo->query("SHOW COLUMNS FROM `claims` LIKE '{$col}'")->fetch();
                    if (!$check) {
                        $pdo->exec($sql);
                    }
                } catch (\Throwable $e) {}
            }
        } else {
            // SQLite
            try {
                $cols = $pdo->query("PRAGMA table_info(claims)")->fetchAll(PDO::FETCH_ASSOC);
                $existingCols = array_column($cols, 'name');
                if (!in_array('cancellation_reason', $existingCols, true)) {
                    $pdo->exec("ALTER TABLE claims ADD COLUMN cancellation_reason TEXT NULL");
                }
                if (!in_array('cancelled_at', $existingCols, true)) {
                    $pdo->exec("ALTER TABLE claims ADD COLUMN cancelled_at TEXT NULL");
                }
                if (!in_array('cancelled_by', $existingCols, true)) {
                    $pdo->exec("ALTER TABLE claims ADD COLUMN cancelled_by INTEGER NULL REFERENCES users(id) ON DELETE SET NULL");
                }
            } catch (\Throwable $e) {}
        }
    }

    private static function runSqlite(PDO $pdo): void
    {
        $queries = [
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'admin',
                role_label TEXT NOT NULL DEFAULT 'Admin Asuransi',
                avatar TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NULL
            );",
            "CREATE TABLE IF NOT EXISTS policies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                policy_number TEXT NOT NULL UNIQUE,
                customer_name TEXT NOT NULL,
                nik TEXT NOT NULL,
                phone TEXT NOT NULL,
                email TEXT NULL,
                insurance_type TEXT NOT NULL DEFAULT 'Kendaraan',
                coverage_amount REAL NOT NULL DEFAULT 0.0,
                start_date TEXT NOT NULL,
                end_date TEXT NOT NULL,
                vehicle_plate TEXT NOT NULL,
                vehicle_brand TEXT NOT NULL,
                vehicle_model TEXT NULL,
                status TEXT NOT NULL DEFAULT 'Aktif',
                notes TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NULL
            );",
            "CREATE TABLE IF NOT EXISTS claims (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                claim_number TEXT NOT NULL UNIQUE,
                policy_id INTEGER NOT NULL,
                submitted_by INTEGER NOT NULL,
                incident_date TEXT NOT NULL,
                incident_time TEXT NOT NULL,
                incident_location TEXT NOT NULL,
                chronology TEXT NOT NULL,
                estimated_damage_cost REAL NOT NULL DEFAULT 0.0,
                status TEXT NOT NULL DEFAULT 'Pengajuan Baru',
                cancellation_reason TEXT NULL,
                cancelled_at TEXT NULL,
                cancelled_by INTEGER NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NULL,
                FOREIGN KEY (policy_id) REFERENCES policies(id) ON DELETE CASCADE,
                FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE RESTRICT,
                FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL
            );",
            "CREATE TABLE IF NOT EXISTS claim_files (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                claim_id INTEGER NOT NULL,
                original_name TEXT NOT NULL,
                file_name TEXT NOT NULL,
                file_path TEXT NOT NULL,
                file_size INTEGER NOT NULL,
                mime_type TEXT NOT NULL,
                file_category TEXT NOT NULL DEFAULT 'foto_kerusakan',
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE
            );",
            "CREATE TABLE IF NOT EXISTS survey_reports (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                claim_id INTEGER NOT NULL UNIQUE,
                surveyor_id INTEGER NOT NULL,
                survey_date TEXT NOT NULL,
                survey_location TEXT NOT NULL,
                damage_level TEXT NOT NULL DEFAULT 'Sedang',
                surveyor_notes TEXT NOT NULL,
                surveyor_estimated_cost REAL NOT NULL DEFAULT 0.0,
                approved_claim_amount REAL NULL,
                auditor_notes TEXT NULL,
                auditor_id INTEGER NULL,
                approval_date TEXT NULL,
                approval_status TEXT NOT NULL DEFAULT 'Pending',
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NULL,
                FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
                FOREIGN KEY (surveyor_id) REFERENCES users(id) ON DELETE RESTRICT,
                FOREIGN KEY (auditor_id) REFERENCES users(id) ON DELETE SET NULL
            );",
            "CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                user_name TEXT NOT NULL,
                module TEXT NOT NULL,
                activity TEXT NOT NULL,
                detail TEXT NOT NULL,
                ip_address TEXT NOT NULL DEFAULT '127.0.0.1',
                status TEXT NOT NULL DEFAULT 'Berhasil',
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );",
            "CREATE TABLE IF NOT EXISTS notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                message TEXT NOT NULL,
                category TEXT NOT NULL DEFAULT 'Informasi Sistem',
                link TEXT NULL,
                is_read INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );"
        ];

        foreach ($queries as $sql) {
            $pdo->exec($sql);
        }
    }

    private static function runMysql(PDO $pdo): void
    {
        $schemaPath = dirname(__DIR__) . '/database/claimora.sql';
        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            // Split and run
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                // The connection already targets the configured DB; the dump's hard-coded USE would switch away from it
                $body = preg_replace('/^(\s*--[^\n]*\n)+/', '', $stmt);
                if (preg_match('/^\s*(CREATE\s+DATABASE|USE)\b/i', $body)) {
                    continue;
                }
                if (!empty($stmt)) {
                    try {
                        $pdo->exec($stmt);
                    } catch (\PDOException $e) {
                        // ignore database creation if already in db
                    }
                }
            }
        }
    }

    public static function seedData(PDO $pdo): void
    {
        // Check if users exist
        $check = $pdo->query("SELECT COUNT(*) as cnt FROM users")->fetch();
        if ($check && (int)$check['cnt'] > 0) {
            return; // Already seeded
        }

        $passwordHash = password_hash('admin123', PASSWORD_BCRYPT);
        $surveyorHash = password_hash('surveyor123', PASSWORD_BCRYPT);
        $auditorHash  = password_hash('auditor123', PASSWORD_BCRYPT);
        $customerHash = password_hash('nasabah123', PASSWORD_BCRYPT);

        // 1. Users
        $users = [
            ['Andi Pratama', 'andipratama', 'andi@claimora.com', $passwordHash, 'admin', 'Admin Asuransi', 'andi_pratama.jpg'],
            ['Ahmad Surya', 'ahmadsurya', 'ahmad@claimora.com', $surveyorHash, 'admin', 'Admin Asuransi', 'ahmad_surya.jpg'],
            ['Dewi Anggraini', 'dewianggraini', 'dewi@claimora.com', $auditorHash, 'admin', 'Auditor Klaim', 'dewi_anggraini.jpg'],
            ['Budi Santoso', 'budisantoso', 'budi@claimora.com', $customerHash, 'customer', 'Nasabah', 'budi_santoso.jpg'],
            ['Siti Rahayu', 'sitirahayu', 'siti@claimora.com', $passwordHash, 'admin', 'Admin Asuransi', 'siti_rahayu.jpg'],
            ['Rizky Pratama', 'rizkypratama', 'rizky@claimora.com', $surveyorHash, 'admin', 'Admin Asuransi', 'rizky_pratama.jpg'],
            ['Ahmad Fauzi', 'ahmadfauzi', 'ahmadfauzi@claimora.com', $customerHash, 'customer', 'Nasabah', 'ahmad_fauzi.jpg'],
            ['Dewi Lestari', 'dewilestari', 'dewilestari@claimora.com', $customerHash, 'customer', 'Nasabah', 'dewi_lestari.jpg'],
        ];

        $now = date('Y-m-d H:i:s');
        $stmtUser = $pdo->prepare("INSERT INTO users (name, username, email, password, role, role_label, avatar, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($users as $u) {
            $stmtUser->execute([$u[0], $u[1], $u[2], $u[3], $u[4], $u[5], $u[6], $now]);
        }

        // 2. Policies
        $thisYear = date('Y');
        $nextYear = date('Y', strtotime('+1 year'));
        $policies = [
            ['JS12345678', 'Budi Santoso', '3201019203810001', '081234567890', 'budi@claimora.com', 'Kendaraan', 500000000.00, $thisYear . '-01-01', $nextYear . '-12-31', 'B 1234 CLM', 'Toyota', 'Veloz 2023', 'Aktif', 'Polis komprehensif all-risk kendaraan pribadi.'],
            ['JS12345679', 'Siti Rahayu', '3201019203810002', '081298765432', 'siti@claimora.com', 'Kendaraan', 350000000.00, $thisYear . '-02-15', $nextYear . '-02-14', 'B 5678 SITI', 'Honda', 'HR-V 2022', 'Aktif', 'Proteksi kerusakan bodi dan mesin.'],
            ['JS12345680', 'Ahmad Fauzi', '3201019203810003', '081345678901', 'ahmadfauzi@claimora.com', 'Kendaraan', 250000000.00, $thisYear . '-03-01', $nextYear . '-02-28', 'B 9012 AHM', 'Mitsubishi', 'Xpander 2024', 'Aktif', 'Polis standar kombinasi kehilangan dan tabrakan.'],
            ['JS87654321', 'Dewi Lestari', '3201019203810004', '081211223344', 'dewilestari@claimora.com', 'Kendaraan', 400000000.00, $thisYear . '-01-10', $nextYear . '-01-10', 'B 3456 DEW', 'Hyundai', 'Creta 2023', 'Aktif', 'Polis all-risk perlindungan pihak ketiga.'],
            ['JS99887766', 'Rizky Pratama', '3201019203810005', '081299887766', 'rizky@claimora.com', 'Kendaraan', 300000000.00, $thisYear . '-04-01', $nextYear . '-03-31', 'D 7788 RZK', 'Wuling', 'Almaz 2023', 'Aktif', 'Perlindungan komprehensif kendaraan keluarga.'],
        ];

        $stmtPol = $pdo->prepare("INSERT INTO policies (policy_number, customer_name, nik, phone, email, insurance_type, coverage_amount, start_date, end_date, vehicle_plate, vehicle_brand, vehicle_model, status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($policies as $p) {
            $stmtPol->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9], $p[10], $p[11], $p[12], $p[13], $now]);
        }

        // 3. Claims
        $claims = [
            ['CLM-2025-1047', 1, 4, '2025-06-16', '10:30:00', 'Jl. Jendral Sudirman No. 45, Jakarta Selatan', 'Terjadi tabrakan di persimpangan saat hujan gerimis. Kendaraan terhantam dari arah samping depan sehingga bumper retak dan kap mesin penyok.', 45000000.00, 'Survey Lapangan', '2025-06-16 11:00:00'],
            ['CLM-2025-1046', 2, 5, '2025-06-15', '14:15:00', 'Tol Jagorawi KM 18 arah Bogor', 'Tertabrak pembatas jalan saat menghindari pecahan ban truk. Bodi samping kiri mengalami penyok parah.', 45000000.00, 'Dalam Audit', '2025-06-15 15:20:00'],
            ['CLM-2025-1045', 3, 7, '2025-06-14', '09:00:00', 'Area Parkir Mall Grand Indonesia', 'Pintu belakang dan bumper terserempet pilar parkir beton saat bermanuver mundur.', 38500000.00, 'Disetujui', '2025-06-14 10:15:00'],
            ['CLM-2025-1044', 4, 8, '2025-06-13', '19:45:00', 'Jl. Raya Ciputat, Tangerang Selatan', 'Klaim kerusakan roda dan suspensi setelah melewati lubang galian air.', 62000000.00, 'Ditolak', '2025-06-13 20:30:00'],
            ['CLM-2025-1043', 5, 6, '2025-06-12', '16:10:00', 'Jl. Asia Afrika, Bandung', 'Benturan minor di perempatan lampu merah, grille depan pecah.', 28000000.00, 'Survey Lapangan', '2025-06-12 17:00:00'],
        ];

        $stmtClaim = $pdo->prepare("INSERT INTO claims (claim_number, policy_id, submitted_by, incident_date, incident_time, incident_location, chronology, estimated_damage_cost, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($claims as $c) {
            $stmtClaim->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $c[7], $c[8], $c[9]]);
        }

        // 4. Claim Files (Sample uploaded photos)
        $claimFiles = [
            [1, 'foto_depan_rusak.jpg', 'car_damage_1.jpg', 'uploads/claims/car_damage_1.jpg', 1420500, 'image/jpeg', 'foto_kerusakan'],
            [1, 'foto_bumper_kap.jpg', 'car_damage_2.jpg', 'uploads/claims/car_damage_2.jpg', 1285000, 'image/jpeg', 'foto_kerusakan'],
            [2, 'kerusakan_samping.jpg', 'car_damage_1.jpg', 'uploads/claims/car_damage_1.jpg', 1945000, 'image/jpeg', 'foto_kerusakan'],
            [3, 'pilar_parkir_gesekan.jpg', 'car_damage_2.jpg', 'uploads/claims/car_damage_2.jpg', 1600000, 'image/jpeg', 'foto_kerusakan'],
        ];

        $stmtFiles = $pdo->prepare("INSERT INTO claim_files (claim_id, original_name, file_name, file_path, file_size, mime_type, file_category, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($claimFiles as $f) {
            $stmtFiles->execute([$f[0], $f[1], $f[2], $f[3], $f[4], $f[5], $f[6], $now]);
        }

        // 5. Survey Reports & Approvals
        $surveyReports = [
            // For CLM-2025-1047
            [
                1, 2, '2025-06-16', 'Bengkel Resmi Toyota Jakarta Selatan', 'Sedang',
                'Pemeriksaan fisik menunjukkan kerusakan pada bumper depan bagian kanan, dudukan lampu utama patah, dan kap mesin penyok 15%. Rangka utama sasis dalam kondisi utuh.',
                45000000.00, 45000000.00, 'Kerusakan sesuai laporan, nilai disetujui.', 3, '2025-06-16 15:15:00', 'Disetujui', '2025-06-16 14:30:00'
            ],
            // For CLM-2025-1046
            [
                2, 6, '2025-06-15', 'Lokasi Kejadian Tol Jagorawi', 'Sedang',
                'Panel pintu depan kiri dan spatbor robek. Diperlukan penggantian suku cadang asli dan pengecatan ulang per panel.',
                45000000.00, null, null, null, null, 'Pending', '2025-06-15 16:00:00'
            ],
            // For CLM-2025-1045
            [
                3, 2, '2025-06-14', 'Bengkel Rekanan Body Repair Palmerah', 'Ringan',
                'Kerusakan minor gesekan cat dan sedikit penyok pada bumper belakang.',
                38500000.00, 38500000.00, 'Disetujui sesuai limit proteksi klaim ringan.', 3, '2025-06-14 14:00:00', 'Disetujui', '2025-06-14 11:30:00'
            ],
            // For CLM-2025-1044
            [
                4, 6, '2025-06-13', 'Bengkel Ciledug Raya', 'Berat / Total Loss',
                'Kerusakan suspensi akibat kelalaian modifikasi non-standar pabrikan dan pengemudi bukan pihak tertanggung.',
                62000000.00, 0.00, 'Ditolak: Pelanggaran klausul polis Pasal 4 ayat 2 terkait modifikasi suspensi.', 3, '2025-06-13 21:00:00', 'Ditolak', '2025-06-13 20:00:00'
            ],
        ];

        $stmtSurvey = $pdo->prepare("INSERT INTO survey_reports (claim_id, surveyor_id, survey_date, survey_location, damage_level, surveyor_notes, surveyor_estimated_cost, approved_claim_amount, auditor_notes, auditor_id, approval_date, approval_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($surveyReports as $s) {
            $stmtSurvey->execute([$s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $s[10], $s[11], $s[12]]);
        }

        // 6. Audit Logs (Matching Screenshot 2 exactly)
        $auditLogs = [
            [1, 'Andi Pratama', 'Pengajuan Klaim', 'Tambah Data', 'Klaim No. CLM-2025-1047 (Polis JS12345678)', '103.23.45.67', 'Berhasil', '2025-06-30 14:32:17'],
            [5, 'Siti Rahayu', 'Data Polis', 'Ubah Data', 'Polis JS12345678 (Masa Berlaku: 01/01/2025–31/12/2025)', '103.23.45.67', 'Berhasil', '2025-06-30 13:50:21'],
            [4, 'Budi Santoso', 'Hasil Survey', 'Approve Klaim', 'Klaim No. CLM-2025-1046 (Nilai: Rp 45.000.000)', '192.168.1.10', 'Berhasil', '2025-06-29 16:12:09'],
            [8, 'Dewi Lestari', 'Pengajuan Klaim', 'Upload Berkas', '3 file (foto kerusakan, STNK, BPKB)', '103.23.45.67', 'Berhasil', '2025-06-29 15:47:33'],
            [7, 'Ahmad Fauzi', 'Audit Log', 'Lihat Log', 'Filter: Modul Pengajuan Klaim', '203.0.113.5', 'Berhasil', '2025-06-28 11:20:12'],
            [5, 'Siti Rahayu', 'Data Polis', 'Hapus Data', 'Polis JS87654321', '103.23.45.67', 'Berhasil', '2025-06-27 09:15:50'],
            [6, 'Rizky Pratama', 'Hasil Survey', 'Ubah Nilai Klaim', 'Klaim No. CLM-2025-1045 (Rp 38.500.000)', '192.168.1.10', 'Berhasil', '2025-06-26 17:08:27'],
            [4, 'Budi Santoso', 'Pengaturan', 'Ubah Pengaturan', 'Email Notifikasi: Aktif', '103.23.45.67', 'Berhasil', '2025-06-25 14:22:18'],
            [8, 'Dewi Lestari', 'Pengajuan Klaim', 'Setujui Klaim', 'Klaim No. CLM-2025-1044 (Nilai: Rp 62.000.000)', '203.0.113.5', 'Berhasil', '2025-06-24 10:37:45'],
            [1, 'Andi Pratama', 'Audit Log', 'Export Log', 'File: audit_log_jun2025.xlsx', '103.23.45.67', 'Berhasil', '2025-06-23 16:50:12'],
            [2, 'Ahmad Surya', 'Hasil Survey', 'Tambah Data', 'Laporan Survey Fisik Lapangan CLM-2025-1047', '103.23.45.67', 'Berhasil', '2025-06-16 14:30:00'],
            [3, 'Dewi Anggraini', 'Hasil Survey', 'Approve Klaim', 'Persetujuan Nilai Klaim Rp 45.000.000 untuk CLM-2025-1047', '103.23.45.67', 'Berhasil', '2025-06-16 15:15:00'],
        ];

        $stmtAudit = $pdo->prepare("INSERT INTO audit_logs (user_id, user_name, module, activity, detail, ip_address, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($auditLogs as $a) {
            $stmtAudit->execute([$a[0], $a[1], $a[2], $a[3], $a[4], $a[5], $a[6], $a[7]]);
        }

        // 7. Seed Initial Notifications
        self::seedNotifications($pdo);
    }

    public static function seedNotifications(PDO $pdo): void
    {
        try {
            $check = $pdo->query("SELECT COUNT(*) as cnt FROM notifications")->fetch();
            if ($check && (int)$check['cnt'] > 0) {
                return;
            }
        } catch (\Throwable $e) {
            // Table might not exist yet; create it according to active PDO driver
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    message TEXT NOT NULL,
                    category TEXT NOT NULL DEFAULT 'Informasi Sistem',
                    link TEXT NULL,
                    is_read INTEGER NOT NULL DEFAULT 0,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );");
            } else {
                $pdo->exec("CREATE TABLE IF NOT EXISTS `notifications` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `title` VARCHAR(150) NOT NULL,
                    `message` TEXT NOT NULL,
                    `category` ENUM('Informasi Polis', 'Informasi Kendaraan', 'Pengajuan Klaim', 'Status Verifikasi', 'Persetujuan Klaim', 'Penolakan Klaim', 'Informasi Sistem') NOT NULL DEFAULT 'Informasi Sistem',
                    `link` VARCHAR(255) NULL,
                    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                    INDEX `idx_notif_user` (`user_id`),
                    INDEX `idx_notif_read` (`is_read`),
                    INDEX `idx_notif_category` (`category`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            }
        }

        $sampleNotifications = [
            // User 4 = Budi Santoso (Nasabah)
            [
                4,
                'Survey Fisik Lapangan Selesai',
                'Surveyor Ahmad Surya telah menyelesaikan inspeksi fisik kendaraan Toyota Veloz Anda untuk pengajuan klaim CLM-2025-1047. Berkas kini diteruskan ke tahap audit persetujuan.',
                'Status Verifikasi',
                'index.php?page=claims&action=detail&id=1',
                0,
                date('Y-m-d H:i:s', strtotime('-2 hours'))
            ],
            [
                4,
                'Polis JS12345678 Aktif',
                'Perlindungan asuransi komprehensif kendaraan Toyota Veloz (B 1234 CLM) aktif hingga 31 Desember 2026 dengan plafon pertanggungan Rp 500.000.000.',
                'Informasi Polis',
                'index.php?page=policies',
                0,
                date('Y-m-d H:i:s', strtotime('-1 day'))
            ],
            [
                4,
                'Pendaftaran Kendaraan Berhasil',
                'Unit kendaraan Toyota Veloz 2023 dengan nomor plat B 1234 CLM berhasil terdaftar dan terlindungi dalam sistem CLAIMORA.',
                'Informasi Kendaraan',
                'index.php?page=vehicles',
                1,
                date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                4,
                'Pengajuan Klaim CLM-2025-1047 Diterima',
                'Laporan klaim insiden tabrakan di Jl. Jendral Sudirman berhasil diajukan dan sedang menunggu penjadwalan inspeksi oleh tim surveyor lapangan.',
                'Pengajuan Klaim',
                'index.php?page=claims&action=detail&id=1',
                1,
                date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                4,
                'Pembaruan Sistem Keamanan',
                'Sistem CLAIMORA telah memperbarui protokol verifikasi klaim dan enkripsi data nasabah demi meningkatkan keamanan transaksi.',
                'Informasi Sistem',
                null,
                1,
                date('Y-m-d H:i:s', strtotime('-1 week'))
            ],
            // User 7 = Ahmad Fauzi (Nasabah)
            [
                7,
                'Klaim CLM-2025-1045 Telah Disetujui',
                'Klaim perbaikan bodi Mitsubishi Xpander (B 9012 AHM) senilai Rp 38.500.000 telah disetujui oleh Auditor Klaim.',
                'Persetujuan Klaim',
                'index.php?page=claims&action=detail&id=3',
                0,
                date('Y-m-d H:i:s', strtotime('-4 hours'))
            ],
        ];

        $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, title, message, category, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($sampleNotifications as $n) {
            $stmtNotif->execute($n);
        }
    }
}
