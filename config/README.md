# CLAIMORA - Konfigurasi Sistem

Folder `config/` berisi semua file konfigurasi utama untuk aplikasi **CLAIMORA** (Sistem Manajemen Klaim Asuransi).

## Daftar File

| File | Fungsi |
|---|---|
| `config.php` | Pengaturan umum aplikasi (nama, timezone, upload, email, session) |
| `database.php` | Koneksi database MySQL / SQLite (kredensial, opsi PDO) |
| `database.sql` | Skema SQL lengkap untuk inisialisasi database MySQL |
| `setup.php` | Script setup otomatis: cek dependensi, buat database, seed data awal |
| `index.php` | File kosong untuk mencegah directory listing |
| `.gitignore` | Abaikan file sensitif agar tidak masuk ke repository |

## Cara Menggunakan

### 1. Import Database Manual
```bash
mysql -u root -p claimora_db < config/database.sql
```

### 2. Atau Gunakan Setup Otomatis
Buka di browser:
```
http://localhost/claimora/config/setup.php
```

### 3. Konfigurasi Koneksi
Edit `database.php` untuk menyesuaikan kredensial MySQL:
```php
'host'     => '127.0.0.1',
'dbname'   => 'claimora_db',
'username' => 'root',
'password' => '',
```

## Catatan Keamanan

> ⚠️ **Jangan pernah commit file ini ke repository publik** jika berisi password asli.  
> Gunakan environment variable atau file `.env` untuk data sensitif.

