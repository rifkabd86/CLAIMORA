-- CLAIMORA - Insurance Claim Management System
-- Database Schema & Initial Data for MySQL / MariaDB

CREATE DATABASE IF NOT EXISTS `claimora_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `claimora_db`;

-- 1. Users Table (Authentication & RBAC)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'admin',
    `role_label` VARCHAR(50) NOT NULL DEFAULT 'Admin Asuransi',
    `avatar` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Policies Table (Data Polis Asuransi Nasabah & Batas Pertanggungan)
CREATE TABLE IF NOT EXISTS `policies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `policy_number` VARCHAR(50) NOT NULL UNIQUE, -- JS12345678 format
    `customer_name` VARCHAR(100) NOT NULL,
    `nik` VARCHAR(20) NOT NULL,
    `phone` VARCHAR(25) NOT NULL,
    `email` VARCHAR(100) NULL,
    `insurance_type` VARCHAR(50) NOT NULL DEFAULT 'Kendaraan',
    `coverage_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00, -- Batas Pertanggungan
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `vehicle_plate` VARCHAR(20) NOT NULL,
    `vehicle_brand` VARCHAR(100) NOT NULL,
    `vehicle_model` VARCHAR(100) NULL,
    `status` ENUM('Aktif', 'Berakhir', 'Ditangguhkan') NOT NULL DEFAULT 'Aktif',
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_policy_number` (`policy_number`),
    INDEX `idx_customer_name` (`customer_name`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Claims Table (Pengajuan Klaim Kerusakan Kendaraan)
CREATE TABLE IF NOT EXISTS `claims` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `claim_number` VARCHAR(50) NOT NULL UNIQUE, -- e.g. CLM-2025-1047
    `policy_id` INT NOT NULL,
    `submitted_by` INT NOT NULL,
    `incident_date` DATE NOT NULL,
    `incident_time` TIME NOT NULL,
    `incident_location` VARCHAR(255) NOT NULL,
    `chronology` TEXT NOT NULL,
    `estimated_damage_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('Pengajuan Baru', 'Survey Lapangan', 'Dalam Audit', 'Disetujui', 'Ditolak', 'Dibatalkan') NOT NULL DEFAULT 'Pengajuan Baru',
    `cancellation_reason` TEXT NULL,
    `cancelled_at` DATETIME NULL,
    `cancelled_by` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`policy_id`) REFERENCES `policies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`submitted_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`cancelled_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_claim_number` (`claim_number`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Claim Files Table (Berkas Multi-upload Foto Kerusakan)
CREATE TABLE IF NOT EXISTS `claim_files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `claim_id` INT NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` INT NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_category` VARCHAR(50) NOT NULL DEFAULT 'foto_kerusakan',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`claim_id`) REFERENCES `claims`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Survey Reports & Approvals Table (Hasil Survey Kerusakan & Persetujuan Nilai Klaim)
CREATE TABLE IF NOT EXISTS `survey_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `claim_id` INT NOT NULL UNIQUE,
    `surveyor_id` INT NOT NULL,
    `survey_date` DATE NOT NULL,
    `survey_location` VARCHAR(255) NOT NULL,
    `damage_level` ENUM('Ringan', 'Sedang', 'Berat / Total Loss') NOT NULL DEFAULT 'Sedang',
    `surveyor_notes` TEXT NOT NULL,
    `surveyor_estimated_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `approved_claim_amount` DECIMAL(15,2) NULL, -- Nilai Klaim yang Disetujui
    `auditor_notes` TEXT NULL,
    `auditor_id` INT NULL,
    `approval_date` DATETIME NULL,
    `approval_status` ENUM('Pending', 'Disetujui', 'Ditolak') NOT NULL DEFAULT 'Pending',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`claim_id`) REFERENCES `claims`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`surveyor_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`auditor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Audit Logs Table (Jejak Rekam Keamanan & Aktivitas Sistem)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `user_name` VARCHAR(100) NOT NULL,
    `module` VARCHAR(50) NOT NULL, -- 'Pengajuan Klaim', 'Data Polis', 'Hasil Survey', 'Audit Log', 'Pengaturan', 'Autentikasi'
    `activity` VARCHAR(100) NOT NULL, -- 'Tambah Data', 'Ubah Data', 'Approve Klaim', 'Upload Berkas', etc.
    `detail` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL DEFAULT '127.0.0.1',
    `status` ENUM('Berhasil', 'Gagal') NOT NULL DEFAULT 'Berhasil',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_module` (`module`),
    INDEX `idx_user_name` (`user_name`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Notifications Table (Notifikasi Pengguna Terpisah dari Audit Log)
CREATE TABLE IF NOT EXISTS `notifications` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

