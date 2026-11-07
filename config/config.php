<?php
/**
 * CLAIMORA - Insurance Claim Management System
 * Application Configuration
 * 
 * Pengaturan umum aplikasi. Ubah sesuai kebutuhan environment Anda.
 */

return [

    // ─── Informasi Aplikasi ────────────────────────────────────────────
    'app_name'    => 'CLAIMORA',
    'app_tagline' => 'Sistem Manajemen Klaim Asuransi',
    'app_version' => '1.0.0',
    'app_url'     => 'http://localhost/claimora',

    // ─── Environment ──────────────────────────────────────────────────
    // 'development' | 'production'
    'env'         => getenv('APP_ENV') ?: 'development',
    'debug'       => (getenv('APP_ENV') ?: 'development') === 'development',

    // ─── Lokalisasi ────────────────────────────────────────────────────
    'timezone'    => 'Asia/Jakarta',
    'locale'      => 'id_ID',
    'currency'    => 'IDR',

    // ─── Session ───────────────────────────────────────────────────────
    'session' => [
        'name'     => 'CLAIMORA_SESSION',
        'lifetime' => 7200,   // 2 jam (detik)
        'secure'   => false,  // set true jika menggunakan HTTPS
    ],

    // ─── Upload File ───────────────────────────────────────────────────
    'upload' => [
        'path'      => dirname(__DIR__) . '/public/uploads/',
        'max_size'  => 5 * 1024 * 1024,  // 5 MB
        'allowed'   => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
    ],

    // ─── Pagination ────────────────────────────────────────────────────
    'per_page' => 15,

    // ─── Email (opsional, untuk notifikasi) ────────────────────────────
    'mail' => [
        'from_name'  => 'CLAIMORA System',
        'from_email' => 'noreply@claimora.id',
        'smtp_host'  => getenv('MAIL_HOST')  ?: 'smtp.gmail.com',
        'smtp_port'  => getenv('MAIL_PORT')  ?: 587,
        'smtp_user'  => getenv('MAIL_USER')  ?: '',
        'smtp_pass'  => getenv('MAIL_PASS')  ?: '',
    ],

];

