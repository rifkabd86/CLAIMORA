<!-- Customer Insurance Dashboard -->
<div class="customer-dashboard">
    <!-- Header Welcome Card -->
    <section class="welcome-card" style="margin-bottom: 24px; padding: 26px 32px; background: linear-gradient(135deg, #1b4433 0%, #245b44 60%, #2d6a4f 100%);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div style="max-width: 620px;">
                <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.15); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; color: #d8f3dc; margin-bottom: 10px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #4ade80;"></span>
                    <span>Portal Nasabah CLAIMORA</span>
                </div>
                <h2 style="font-size: 24px; font-weight: 800; color: #ffffff; margin-bottom: 6px; line-height: 1.2;">
                    Halo, <?= e($user['name'] ?? 'Nasabah') ?>!
                </h2>
                <p style="font-size: 13.5px; color: #d8f3dc; margin-bottom: 0; line-height: 1.5;">
                    Pantau status perlindungan kendaraan aktif Anda dan perkembangan klaim terkini secara transparan dan real-time.
                </p>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="index.php?page=policies&register=true" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #ffffff; font-weight: 600; padding: 10px 18px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span>+ Daftar Polis Baru</span>
                </a>
                <a href="index.php?page=claims&action=create" class="btn btn-primary" style="background: #ffffff; color: #1b4433; font-weight: 700; border: none; box-shadow: 0 4px 14px rgba(0,0,0,0.15); padding: 10px 20px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>+ Ajukan Klaim Baru</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Quick Actions Bar -->
    <div class="customer-quick-actions">
        <a href="index.php?page=claims&action=create" class="card" style="padding: 14px 18px; text-decoration: none; display: flex; align-items: center; gap: 14px; transition: all 0.2s; border: 1px solid var(--border-color);">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #eaf7f0; color: #15803d; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </div>
            <div>
                <div style="font-size: 13.5px; font-weight: 700; color: var(--text-primary);">Ajukan Klaim</div>
                <div style="font-size: 11.5px; color: var(--text-muted);">Form klaim baru</div>
            </div>
        </a>

        <a href="index.php?page=vehicles" class="card" style="padding: 14px 18px; text-decoration: none; display: flex; align-items: center; gap: 14px; transition: all 0.2s; border: 1px solid var(--border-color);">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
            </div>
            <div>
                <div style="font-size: 13.5px; font-weight: 700; color: var(--text-primary);">Kendaraan Saya</div>
                <div style="font-size: 11.5px; color: var(--text-muted);">Halaman terpisah</div>
            </div>
        </a>

        <a href="index.php?page=policies" class="card" style="padding: 14px 18px; text-decoration: none; display: flex; align-items: center; gap: 14px; transition: all 0.2s; border: 1px solid var(--border-color);">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <div>
                <div style="font-size: 13.5px; font-weight: 700; color: var(--text-primary);">Polis Saya</div>
                <div style="font-size: 11.5px; color: var(--text-muted);">Cek masa berlaku</div>
            </div>
        </a>

        <a href="index.php?page=claims" class="card" style="padding: 14px 18px; text-decoration: none; display: flex; align-items: center; gap: 14px; transition: all 0.2s; border: 1px solid var(--border-color);">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #faf5ff; color: #7e22ce; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div>
                <div style="font-size: 13.5px; font-weight: 700; color: var(--text-primary);">Riwayat Klaim</div>
                <div style="font-size: 11.5px; color: var(--text-muted);">Status pengajuan</div>
            </div>
        </a>
    </div>

    <!-- 4 Customer KPI Cards -->
    <section class="kpi-grid" style="margin-bottom: 24px;">
        <!-- KPI 1: Total Polis Aktif -->
        <div class="kpi-card">
            <div class="kpi-icon icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <div class="kpi-title">Total Polis Aktif</div>
                <div class="kpi-value-row">
                    <div class="kpi-value"><?= e($kpi['total_polis']['formatted'] ?? '0') ?></div>
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Polis</span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Total Pengajuan Klaim -->
        <div class="kpi-card">
            <div class="kpi-icon icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </div>
            <div class="kpi-info">
                <div class="kpi-title">Total Pengajuan Klaim</div>
                <div class="kpi-value-row">
                    <div class="kpi-value"><?= e($kpi['total_claims']['formatted'] ?? '0') ?></div>
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Berkas</span>
                </div>
            </div>
        </div>

        <!-- KPI 3: Klaim Sedang Diproses -->
        <div class="kpi-card">
            <div class="kpi-icon icon-yellow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="kpi-info">
                <div class="kpi-title">Klaim Sedang Diproses</div>
                <div class="kpi-value-row">
                    <div class="kpi-value" style="color: #b45309;"><?= e($kpi['processing_claims']['formatted'] ?? '0') ?></div>
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Dalam Tahap</span>
                </div>
            </div>
        </div>

        <!-- KPI 4: Klaim Disetujui -->
        <div class="kpi-card">
            <div class="kpi-icon icon-green" style="background: #eaf7f0; color: #15803d;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="kpi-info">
                <div class="kpi-title">Klaim Disetujui</div>
                <div class="kpi-value-row">
                    <div class="kpi-value" style="color: #15803d;"><?= e($kpi['approved_claims']['formatted'] ?? '0') ?></div>
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Tuntas</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Visual Progress Tracker Section (Tahapan Proses Klaim Terkini) -->
    <?php if (!empty($activeClaim)): ?>
        <?php
        $status = $activeClaim['status'];
        // Step calculation (1: Menunggu Verifikasi, 2: Sedang Disurvei, 3: Menunggu Persetujuan, 4: Disetujui/Ditolak, 5: Selesai)
        $currentStep = match($status) {
            'Pengajuan Baru'  => 1,
            'Survey Lapangan' => 2,
            'Dalam Audit'     => 3,
            'Disetujui'       => 5,
            'Ditolak'         => 4,
            default           => 1
        };
        $isRejected = ($status === 'Ditolak');
        ?>
        <div class="card" style="margin-bottom: 24px; padding: 22px 24px; border-left: 4px solid var(--primary-700);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--primary-700); letter-spacing: 0.5px;">
                        Pantauan Progres Berkas Klaim Aktif
                    </div>
                    <h3 style="font-size: 17px; font-weight: 800; color: var(--text-primary); margin-top: 2px;">
                        Klaim No. <?= e($activeClaim['claim_number']) ?>
                    </h3>
                    <div style="font-size: 12.5px; color: var(--text-muted);">
                        Kendaraan: <strong><?= e($activeClaim['vehicle_brand'] . ' (' . $activeClaim['vehicle_plate'] . ')') ?></strong> &bull; Diajukan: <?= date('d M Y', strtotime($activeClaim['created_at'])) ?>
                    </div>
                </div>

                <div style="text-align: right;">
                    <span class="badge <?= $status === 'Disetujui' ? 'badge-approved' : ($status === 'Ditolak' ? 'badge-rejected' : 'badge-survey') ?>" style="font-size: 13px; padding: 6px 14px;">
                        <span class="badge-dot"></span> <?= e($status) ?>
                    </span>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        Estimasi: <strong><?= format_rupiah((float)$activeClaim['estimated_damage_cost']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Visual Progress Stepper Tracker -->
            <div class="progress-stepper">
                <!-- Background Connection Bar -->
                <div class="progress-stepper-track" style="position: absolute; top: 27px; left: 35px; right: 35px; height: 3px; background: #e5ede8; z-index: 1;">
                    <div style="height: 100%; background: var(--primary-600); width: <?= $isRejected ? '75%' : min(100, (($currentStep - 1) / 4) * 100) ?>%; transition: width 0.4s ease;"></div>
                </div>

                <!-- Step 1 -->
                <div class="progress-step-item" style="position: relative; z-index: 2; text-align: center; flex: 1;">
                    <div style="width: 36px; height: 36px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; <?= $currentStep >= 1 ? 'background: var(--primary-700); color: #ffffff;' : 'background: #f1f5f9; color: var(--text-muted); border: 2px solid #cbd5e1;' ?>">
                        <?= $currentStep > 1 ? '✓' : '1' ?>
                    </div>
                    <div class="progress-step-text">
                        <div style="font-size: 12px; font-weight: 700; margin-top: 8px; color: var(--text-primary);">Menunggu Verifikasi</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Berkas diterima</div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="progress-step-item" style="position: relative; z-index: 2; text-align: center; flex: 1;">
                    <div style="width: 36px; height: 36px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; <?= $currentStep >= 2 ? 'background: var(--primary-700); color: #ffffff;' : 'background: #ffffff; color: var(--text-muted); border: 2px solid #cbd5e1;' ?>">
                        <?= $currentStep > 2 ? '✓' : '2' ?>
                    </div>
                    <div class="progress-step-text">
                        <div style="font-size: 12px; font-weight: 700; margin-top: 8px; color: var(--text-primary);">Sedang Disurvei</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Inspeksi fisik</div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="progress-step-item" style="position: relative; z-index: 2; text-align: center; flex: 1;">
                    <div style="width: 36px; height: 36px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; <?= $currentStep >= 3 ? 'background: var(--primary-700); color: #ffffff;' : 'background: #ffffff; color: var(--text-muted); border: 2px solid #cbd5e1;' ?>">
                        <?= $currentStep > 3 ? '✓' : '3' ?>
                    </div>
                    <div class="progress-step-text">
                        <div style="font-size: 12px; font-weight: 700; margin-top: 8px; color: var(--text-primary);">Menunggu Persetujuan</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Audit nilai klaim</div>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="progress-step-item" style="position: relative; z-index: 2; text-align: center; flex: 1;">
                    <div style="width: 36px; height: 36px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; <?= $isRejected ? 'background: #dc2626; color: #ffffff;' : ($currentStep >= 4 ? 'background: var(--primary-700); color: #ffffff;' : 'background: #ffffff; color: var(--text-muted); border: 2px solid #cbd5e1;') ?>">
                        <?= $isRejected ? '✕' : ($currentStep >= 4 ? '✓' : '4') ?>
                    </div>
                    <div class="progress-step-text">
                        <div style="font-size: 12px; font-weight: 700; margin-top: 8px; color: <?= $isRejected ? '#dc2626' : 'var(--text-primary)' ?>;">
                            <?= $isRejected ? 'Klaim Ditolak' : 'Keputusan Klaim' ?>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">Keputusan auditor</div>
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="progress-step-item" style="position: relative; z-index: 2; text-align: center; flex: 1;">
                    <div style="width: 36px; height: 36px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; <?= $currentStep >= 5 ? 'background: #15803d; color: #ffffff;' : 'background: #ffffff; color: var(--text-muted); border: 2px solid #cbd5e1;' ?>">
                        <?= $currentStep >= 5 ? '✓' : '5' ?>
                    </div>
                    <div class="progress-step-text">
                        <div style="font-size: 12px; font-weight: 700; margin-top: 8px; color: var(--text-primary);">Selesai</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Klaim dituntaskan</div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Two-Column Grid: Informasi Polis Kendaraan Saya & Aktivitas Terbaru -->
    <div class="customer-main-grid">
        <!-- Left: Ringkasan Kendaraan Terdaftar -->
        <div class="card" id="my-vehicles">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div class="card-header-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                    <h3>Kendaraan Saya (Ringkasan)</h3>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="index.php?page=vehicles" class="btn btn-primary" style="font-size: 12px; padding: 6px 14px; text-decoration: none;">
                        Buka Halaman Kendaraan &rarr;
                    </a>
                </div>
            </div>

            <div class="card-body" style="padding: 18px 24px;">
                <?php if (!empty($policies)): ?>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach ($policies as $idx => $pol): ?>
                            <?php
                            $today = date('Y-m-d');
                            $isValid = ($pol['status'] === 'Aktif') && ($today >= $pol['start_date']) && ($today <= $pol['end_date']);
                            ?>
                            <div style="padding: 18px 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: var(--bg-surface); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <div style="width: 50px; height: 50px; border-radius: var(--radius-md); background: #eaf7f0; color: #15803d; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="1" y="3" width="15" height="13"></rect>
                                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                        </svg>
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <strong style="font-size: 15px; color: var(--text-primary);"><?= e($pol['vehicle_brand'] . ' ' . ($pol['vehicle_model'] ?? '')) ?></strong>
                                            <span class="badge <?= $isValid ? 'badge-approved' : 'badge-rejected' ?>" style="font-size: 11px;">
                                                <?= $isValid ? 'Proteksi Aktif' : e($pol['status']) ?>
                                            </span>
                                        </div>
                                        <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 2px;">
                                            No. Polisi: <strong style="color: var(--text-secondary);"><?= e($pol['vehicle_plate']) ?></strong> &bull; No. Polis: <span style="font-family: monospace; font-weight: 700; color: var(--primary-800);"><?= e($pol['policy_number']) ?></span>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                            Masa Berlaku: <?= date('d/m/Y', strtotime($pol['start_date'])) ?> – <?= date('d/m/Y', strtotime($pol['end_date'])) ?>
                                        </div>
                                    </div>
                                </div>

                                <div style="text-align: right;">
                                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Batas Pertanggungan</div>
                                    <div style="font-size: 15px; font-weight: 800; color: #15803d;">
                                        <?= format_rupiah((float)$pol['coverage_amount']) ?>
                                    </div>
                                    <a href="index.php?page=claims&action=create" class="btn btn-outline" style="font-size: 11.5px; padding: 4px 10px; margin-top: 6px;">
                                        Ajukan Klaim &rarr;
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--border-color); text-align: center;">
                        <a href="index.php?page=vehicles" class="card-link" style="font-weight: 700; font-size: 13px;">
                            Buka Seluruh Kendaraan di Halaman Terpisah &rarr;
                        </a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 36px 20px;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted); margin-bottom: 12px;">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                        <h4 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Belum Ada Kendaraan Terdaftar</h4>
                        <p style="font-size: 13px; color: var(--text-muted); max-width: 400px; margin: 0 auto 16px auto;">
                            Anda belum memiliki data kendaraan tertanggung yang tercatat di akun ini.
                        </p>
                        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                            <a href="index.php?page=vehicles" class="btn btn-primary" style="font-size: 13px;">+ Buka Halaman Kendaraan Saya</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Ringkasan Aktivitas Singkat (Terpisah dari Halaman Notifikasi Penuh) -->
        <div class="card" id="my-activities">
            <div class="card-header">
                <div class="card-header-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <h3>Aktivitas Singkat</h3>
                </div>
                <a href="index.php?page=notifications" class="card-link" style="font-size: 12.5px; font-weight: 600;">Lihat Notifikasi &rarr;</a>
            </div>

            <div class="card-body" style="padding: 16px 20px;">
                <?php if (!empty($activities)): ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach (array_slice($activities, 0, 3) as $act): ?>
                            <div style="display: flex; gap: 12px; align-items: flex-start; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: #eaf7f0; color: #15803d; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 11px; font-weight: 700;">
                                    ✓
                                </div>
                                <div style="flex-grow: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                                        <strong style="font-size: 12.5px; color: var(--text-primary);"><?= e($act['activity']) ?></strong>
                                        <span style="font-size: 11px; color: var(--text-muted);"><?= date('d M H:i', strtotime($act['created_at'])) ?></span>
                                    </div>
                                    <div style="font-size: 11.5px; color: var(--text-secondary); margin-top: 2px;">
                                        <?= e($act['detail']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 12px; text-align: center;">
                        <a href="index.php?page=notifications" class="btn btn-outline" style="font-size: 12px; padding: 6px 14px; width: 100%; justify-content: center;">
                            Buka Pusat Notifikasi Lengkap &rarr;
                        </a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 26px 10px; color: var(--text-muted);">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted); margin-bottom: 8px;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <div style="font-size: 12.5px;">Belum ada aktivitas baru pada akun Anda.</div>
                        <a href="index.php?page=notifications" class="card-link" style="display: inline-block; font-size: 12px; margin-top: 8px;">Cek Halaman Notifikasi &rarr;</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Pengajuan Klaim Terbaru Saya -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <h3>Riwayat Pengajuan Klaim Saya</h3>
            </div>
            <a href="index.php?page=claims" class="card-link">Lihat Semua Klaim &rarr;</a>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Klaim</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Kendaraan Terkait</th>
                        <th>Tingkat Kerusakan</th>
                        <th>Estimasi Kerugian</th>
                        <th>Status Klaim</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($claims)): ?>
                        <?php foreach ($claims as $c): ?>
                            <?php
                            $badgeClass = match($c['status']) {
                                'Survey Lapangan' => 'badge-survey',
                                'Dalam Audit'     => 'badge-audit',
                                'Disetujui'       => 'badge-approved',
                                'Ditolak'         => 'badge-rejected',
                                default           => 'badge-new'
                            };
                            ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary-800); font-family: monospace; font-size: 13.5px;"><?= e($c['claim_number']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);">Polis: <?= e($c['policy_number']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary); font-size: 13px;"><?= date('d M Y', strtotime($c['created_at'])) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= date('H:i', strtotime($c['created_at'])) ?> WIB</div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);"><?= e($c['vehicle_brand'] . ' ' . ($c['vehicle_model'] ?? '')) ?></div>
                                    <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;"><?= e($c['vehicle_plate']) ?></div>
                                </td>
                                <td>
                                    <span class="badge" style="background: var(--primary-soft); color: var(--text-primary); border: 1px solid var(--border-color); font-size: 11.5px;">
                                        <?= !empty($c['damage_level']) ? e($c['damage_level']) : 'Menunggu Survey' ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: #15803d; font-size: 13.5px;"><?= format_rupiah((float)$c['estimated_damage_cost']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>" style="font-size: 12px; padding: 4px 10px;">
                                        <span class="badge-dot"></span>
                                        <?= e($c['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?page=claims&action=detail&id=<?= (int)$c['id'] ?>" class="btn btn-outline" style="font-size: 12px; padding: 4px 12px;">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px 20px;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted);"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                                    <strong style="color: var(--text-primary); font-size: 14px;">Belum Ada Riwayat Pengajuan Klaim</strong>
                                    <p style="font-size: 12.5px; color: var(--text-muted); max-width: 420px; margin: 0 0 12px 0;">
                                        Jika kendaraan Anda mengalami kerusakan atau insiden kecelakaan, ajukan klaim dengan mudah secara online.
                                    </p>
                                    <a href="index.php?page=claims&action=create" class="btn btn-primary" style="font-size: 13px;">+ Ajukan Klaim Sekarang</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

