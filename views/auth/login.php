<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Masuk ke Akun Anda - CLAIMORA') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        body {
            background: linear-gradient(135deg, #eaf7f0 0%, #d8f3dc 50%, #f4f8f5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-wrapper {
            display: flex;
            background: #ffffff;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(15, 39, 29, 0.12);
            border: 1px solid var(--border-color);
            max-width: 960px;
            width: 100%;
            min-height: 580px;
        }
        .login-hero {
            flex: 1.1;
            background: linear-gradient(145deg, #183a2b 0%, #1e4635 60%, #286b4f 100%);
            color: #ffffff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .login-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(82, 183, 136, 0.25) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-brand {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }
        .hero-logo-box {
            width: 58px;
            height: 58px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px);
        }
        .hero-logo-box svg {
            width: 32px;
            height: 32px;
            fill: #ffffff;
        }
        .hero-brand h1 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #ffffff;
            line-height: 1.1;
        }
        .hero-brand h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--primary-100);
        }
        .hero-brand p {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.85);
            max-width: 380px;
            line-height: 1.5;
        }
        .hero-illustration {
            margin: 20px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero-illustration svg {
            width: 100%;
            max-width: 320px;
            height: auto;
        }
        .hero-footer {
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.6);
            display: flex;
            justify-content: space-between;
        }
        .login-form-pane {
            flex: 1;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }
        .form-header {
            margin-bottom: 28px;
        }
        .form-header h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 6px;
        }
        .form-header p {
            font-size: 13.5px;
            color: var(--text-muted);
        }
        .input-group-custom {
            position: relative;
        }
        .input-group-custom .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 18px;
            height: 18px;
        }
        .input-group-custom .form-control {
            padding-left: 42px;
            padding-right: 42px;
            height: 44px;
            border-radius: var(--radius-md);
        }
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 4px;
        }
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
        }
        .form-options label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            color: var(--text-secondary);
        }
        .form-options a {
            color: var(--primary-600);
            font-weight: 600;
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
            color: var(--text-muted);
            font-size: 12px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border-color);
        }
        .divider span {
            padding: 0 12px;
        }
        .btn-sso {
            width: 100%;
            height: 44px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-weight: 600;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-sso:hover {
            background-color: var(--primary-soft);
            border-color: var(--primary-300);
        }
        .demo-switcher {
            margin-top: 24px;
            padding: 12px;
            background-color: var(--primary-soft);
            border-radius: var(--radius-md);
            border: 1px dashed var(--primary-200);
        }
        .demo-switcher p {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-800);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .demo-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .demo-btn {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            color: var(--primary-700);
            cursor: pointer;
            transition: var(--transition);
        }
        .demo-btn:hover {
            background: var(--primary-700);
            color: #ffffff;
        }
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }
            .login-hero {
                padding: 32px 24px;
            }
            .login-form-pane {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Left Hero Pane (Matching Image 2) -->
    <div class="login-hero">
        <div class="hero-brand">
            <div class="hero-logo-box">
                <svg viewBox="0 0 24 24">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 15l-4-4 1.41-1.41L10 13.17l6.59-6.59L18 8l-8 8z"/>
                </svg>
            </div>
            <div>
                <h1>CLAIMORA</h1>
                <h3>Solusi Cerdas Pengelolaan Klaim Asuransi</h3>
            </div>
            <p>Ajukan klaim dengan mudah, verifikasi survey lapangan terintegrasi, dan pantau seluruh proses secara transparan dan akuntabel.</p>
        </div>

        <!-- SVG Car & Shield Illustration -->
        <div class="hero-illustration">
            <svg viewBox="0 0 400 240" fill="none">
                <ellipse cx="200" cy="205" rx="160" ry="20" fill="rgba(0,0,0,0.15)"/>
                <!-- Modern White SUV/Sedan -->
                <path d="M70 170 L95 125 Q130 95 190 95 L270 95 Q305 105 325 140 L345 160 Q350 175 340 185 L70 185 Z" fill="#ffffff"/>
                <!-- Windows & Tint -->
                <path d="M110 125 L135 102 L190 102 L190 125 Z" fill="#1b4433" opacity="0.3"/>
                <path d="M198 102 L260 102 Q285 110 295 125 L198 125 Z" fill="#1b4433" opacity="0.3"/>
                <!-- Wheels -->
                <circle cx="120" cy="185" r="24" fill="#2d3748"/>
                <circle cx="120" cy="185" r="14" fill="#e2e8f0"/>
                <circle cx="290" cy="185" r="24" fill="#2d3748"/>
                <circle cx="290" cy="185" r="14" fill="#e2e8f0"/>
                <!-- Shield Icon Hovering -->
                <path d="M280 40 L340 65 V110 C340 150 310 180 280 195 C250 180 220 150 220 110 V65 Z" fill="#2d6a4f" opacity="0.9"/>
                <path d="M280 50 L330 72 V108 C330 142 305 168 280 180 C255 168 230 142 230 108 V72 Z" fill="#52b788"/>
                <polyline points="255,110 273,128 308,90" stroke="#ffffff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <div class="hero-footer">
            <span>CLAIMORA &bull; v1.0.0</span>
            <span>&copy; 2025 Claimora. All rights reserved.</span>
        </div>
    </div>

    <!-- Right Login Form Pane (Matching Image 2) -->
    <div class="login-form-pane">
        <div class="form-header">
            <h2>Masuk ke Akun Anda</h2>
            <p>Silakan login untuk melanjutkan</p>
        </div>

        <?php
        $flashes = \Core\Session::getAllFlashes();
        if (!empty($flashes)):
            foreach ($flashes as $type => $msg):
        ?>
            <div class="alert alert-<?= e($type) ?>" style="margin-bottom: 18px; padding: 10px 14px; font-size: 13px;">
                <span><?= flash_html($msg) ?></span>
            </div>
        <?php
            endforeach;
        endif;
        ?>

        <form method="POST" action="index.php?page=login" id="login-form">
            <?= \Core\Security::csrfField() ?>

            <!-- Email / Username Input -->
            <div class="form-group">
                <label class="form-label">Email / Username</label>
                <div class="input-group-custom">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <input type="text" name="identifier" id="identifier" class="form-control" placeholder="andi@claimora.com atau andipratama" value="<?= e($identifier ?? '') ?>" required autofocus>
                </div>
            </div>

            <!-- Password Input with Toggle -->
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-group-custom">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan kata sandi..." required>
                    <button type="button" class="toggle-password" id="btn-toggle-pwd" title="Lihat kata sandi">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label>
                    <input type="checkbox" name="remember" value="1" checked> Ingat saya
                </label>
                <a href="#" onclick="alert('Silakan hubungi administrator IT asuransi Anda untuk reset kata sandi.')">Lupa password?</a>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; height: 44px; border-radius: var(--radius-md); font-size: 14.5px;">
                <span>Masuk</span> &rarr;
            </button>

            <div class="divider">
                <span>atau</span>
            </div>

            <button type="button" class="btn-sso" onclick="showToast('Otentikasi Single Sign-On (SSO) Perusahaan aktif.', 'info')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2d6a4f" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <span>Login dengan SSO</span>
            </button>

            <!-- Quick Demo Account Switcher -->
            <div class="demo-switcher">
                <p>Akun Demo Cepat (Klik untuk isi otomatis):</p>
                <div class="demo-buttons">
                    <button type="button" class="demo-btn" onclick="fillLogin('andi@claimora.com', 'admin123')">Admin (Andi Pratama)</button>
                    <button type="button" class="demo-btn" onclick="fillLogin('ahmad@claimora.com', 'surveyor123')">Admin / Pemeriksa (Ahmad Surya)</button>
                    <button type="button" class="demo-btn" onclick="fillLogin('budi@claimora.com', 'nasabah123')">Nasabah (Budi Santoso)</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="js/app.js"></script>
<script>
    const btnToggle = document.getElementById('btn-toggle-pwd');
    const pwdInput = document.getElementById('password');
    if (btnToggle && pwdInput) {
        btnToggle.addEventListener('click', () => {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
        });
    }

    function fillLogin(user, pwd) {
        document.getElementById('identifier').value = user;
        document.getElementById('password').value = pwd;
    }
</script>

</body>
</html>
