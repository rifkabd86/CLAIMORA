/**
 * CLAIMORA - Global Interactive Application Logic
 * Mobile Drawer, Live Search, Modals, Toast Alerts, and Support Chat
 */

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initLiveSearch();
    initModals();
    initRupiahFormatters();
    initSupportChat();
});

/**
 * Mobile Drawer Sidebar Navigation
 * Mendukung buka/tutup lewat tombol hamburger, tombol close, klik overlay, dan tombol Escape
 */
function initMobileNav() {
    const toggleBtn = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.app-sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');
    const closeBtns = document.querySelectorAll('.sidebar-close-btn, [data-sidebar-close]');

    if (!sidebar || !backdrop) return;

    const openDrawer = () => {
        sidebar.classList.add('drawer-open');
        backdrop.classList.add('active');
        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
        if (window.innerWidth <= 768) {
            document.body.style.overflow = 'hidden';
        }
    };

    const closeDrawer = () => {
        sidebar.classList.remove('drawer-open');
        backdrop.classList.remove('active');
        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
        if (!document.querySelector('.modal-backdrop.active')) {
            document.body.style.overflow = '';
        }
    };

    if (toggleBtn) {
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sidebar.classList.contains('drawer-open')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });
    }

    closeBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeDrawer();
        });
    });

    backdrop.addEventListener('click', () => {
        closeDrawer();
    });

    // Tutup drawer saat menu bantuan/chat di dalam sidebar diklik
    sidebar.querySelectorAll('[data-open-chat]').forEach(link => {
        link.addEventListener('click', () => {
            closeDrawer();
        });
    });

    // Tutup drawer atau modal aktif dengan tombol Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' || e.key === 'Esc') {
            if (sidebar.classList.contains('drawer-open')) {
                closeDrawer();
            }
            const activeModal = document.querySelector('.modal-backdrop.active');
            if (activeModal && activeModal.id) {
                closeModal(activeModal.id);
            }
        }
    });

    // Reset state drawer jika layar diperbesar melewati breakpoint mobile (> 768px)
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && sidebar.classList.contains('drawer-open')) {
            closeDrawer();
        }
    });
}

/**
 * Live Client-Side Table Search (No page reload)
 */
function initLiveSearch() {
    const searchInputs = document.querySelectorAll('[data-live-search]');
    searchInputs.forEach(input => {
        const targetSelector = input.getAttribute('data-live-search');
        const targetTable = document.querySelector(targetSelector);
        if (!targetTable) return;

        input.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const rows = targetTable.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
}

/**
 * Universal Modal Controller
 * Modal dipindahkan ke document.body saat dibuka agar keluar dari
 * stacking context .app-viewport (yang punya CSS animation/transform).
 * Ini mengatasi masalah modal "tenggelam" di bawah sidebar/topbar.
 */
function initModals() {
    // Open Modal via data attribute
    document.querySelectorAll('[data-modal-target]').forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const modalId = trigger.getAttribute('data-modal-target');
            openModal(modalId);
        });
    });

    // Close Modal buttons
    document.querySelectorAll('.modal-close, [data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-backdrop');
            if (modal) closeModal(modal.id);
        });
    });

    // Close on backdrop click
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                closeModal(backdrop.id);
            }
        });
    });

    // Form submit protection: loading state + anti double-submit
    document.querySelectorAll('.modal-backdrop form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (!submitBtn) return;

            // Cegah double submit
            if (submitBtn.classList.contains('btn-loading')) {
                e.preventDefault();
                return;
            }

            // Validasi HTML5 native terlebih dahulu
            if (!form.checkValidity()) {
                return; // biarkan browser menampilkan pesan error native
            }

            // Tambahkan state loading
            submitBtn.classList.add('btn-loading');
            submitBtn._originalText = submitBtn.textContent;
            submitBtn.textContent = 'Menyimpan...';
        });
    });
}

/**
 * Buka modal — pindahkan ke document.body agar z-index efektif
 * di atas sidebar dan topbar (mengatasi stacking context .app-viewport)
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    // Simpan parent asli untuk dikembalikan saat close
    if (!modal._originalParent) {
        modal._originalParent = modal.parentNode;
        modal._originalNextSibling = modal.nextSibling;
    }

    // Pindahkan ke body agar keluar dari stacking context
    document.body.appendChild(modal);

    // Re-bind close handlers setelah modal dipindahkan
    modal.querySelectorAll('.modal-close, [data-modal-close]').forEach(btn => {
        // Hapus listener lama, pasang baru
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        newBtn.addEventListener('click', () => closeModal(modalId));
    });

    // Re-bind backdrop click
    modal.onclick = function(e) {
        if (e.target === modal) closeModal(modalId);
    };

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Scroll modal-body ke atas
    const modalBody = modal.querySelector('.modal-body');
    if (modalBody) modalBody.scrollTop = 0;
}

/**
 * Tutup modal — kembalikan ke parent asli
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('active');
    document.body.style.overflow = '';

    // Reset loading state pada tombol submit
    const submitBtn = modal.querySelector('button[type="submit"].btn-loading');
    if (submitBtn) {
        submitBtn.classList.remove('btn-loading');
        if (submitBtn._originalText) {
            submitBtn.textContent = submitBtn._originalText;
        }
    }

    // Kembalikan modal ke posisi asli di DOM (untuk cleanup)
    if (modal._originalParent) {
        if (modal._originalNextSibling) {
            modal._originalParent.insertBefore(modal, modal._originalNextSibling);
        } else {
            modal._originalParent.appendChild(modal);
        }
    }
}

/**
 * Delete Confirmation Modal Helper (CLAIMORA Modern Dialog)
 */
function confirmDelete(title, message, confirmCallback) {
    let confirmModal = document.getElementById('modal-delete-confirm');
    if (!confirmModal) {
        const modalHtml = `
            <div id="modal-delete-confirm" class="modal-backdrop">
                <div class="modal" style="max-width: 440px;">
                    <div class="modal-header">
                        <h3 id="delete-modal-title" style="color: #b91c1c; font-size: 16px; font-weight: 800;">Konfirmasi Hapus Data</h3>
                        <button type="button" class="modal-close" data-modal-close>&times;</button>
                    </div>
                    <div class="modal-body">
                        <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 6px;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </div>
                            <p id="delete-modal-msg" style="color: var(--text-secondary); font-size: 13.5px; white-space: pre-line; line-height: 1.55; margin: 0;"></p>
                        </div>
                    </div>
                    <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn btn-outline" data-modal-close style="font-weight: 600;">Batal</button>
                        <button type="button" id="btn-do-delete" class="btn btn-primary" style="background-color: #b91c1c; border-color: #b91c1c; font-weight: 600;">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        confirmModal = document.getElementById('modal-delete-confirm');
        confirmModal.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', () => closeModal('modal-delete-confirm'));
        });
    }

    document.getElementById('delete-modal-title').textContent = title || 'Konfirmasi Hapus Data';
    document.getElementById('delete-modal-msg').textContent = message || 'Apakah Anda yakin ingin menghapus data ini?';

    const doDeleteBtn = document.getElementById('btn-do-delete');
    const newBtn = doDeleteBtn.cloneNode(true);
    doDeleteBtn.parentNode.replaceChild(newBtn, doDeleteBtn);

    newBtn.addEventListener('click', () => {
        closeModal('modal-delete-confirm');
        if (typeof confirmCallback === 'function') {
            confirmCallback();
        }
    });

    openModal('modal-delete-confirm');
}

/**
 * Rupiah Input Formatting
 */
function initRupiahFormatters() {
    const inputs = document.querySelectorAll('[data-rupiah]');
    inputs.forEach(input => {
        input.addEventListener('keyup', (e) => {
            input.value = formatRupiah(input.value, 'Rp ');
        });
    });
}

function formatRupiah(angka, prefix = 'Rp ') {
    let numberString = angka.replace(/[^,\d]/g, '').toString();
    let split = numberString.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix === undefined ? rupiah : (rupiah ? prefix + rupiah : '');
}

/**
 * Toast Notification System
 */
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        container.style.cssText = 'position: fixed; top: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const bgColors = {
        success: '#1b4433',
        danger: '#b91c1c',
        warning: '#b45309',
        info: '#0369a1'
    };

    toast.style.cssText = `
        background-color: ${bgColors[type] || '#1b4433'};
        color: #ffffff;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideDown 0.3s ease;
    `;
    const toastText = document.createElement('span');
    toastText.textContent = message;
    toast.appendChild(toastText);
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

/**
 * Support Live Chat Simulation
 */
function initSupportChat() {
    document.querySelectorAll('.btn-support-chat, [data-open-chat]').forEach(chatBtn => {
        chatBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openSupportDrawer();
        });
    });
}

function openSupportDrawer() {
    let chatModal = document.getElementById('modal-live-chat');
    if (!chatModal) {
        const html = `
            <div id="modal-live-chat" class="modal-backdrop">
                <div class="modal" style="max-width: 480px; height: 560px; display: flex; flex-direction: column;">
                    <div class="modal-header" style="background: var(--primary-700); color: #fff; border-radius: var(--radius-xl) var(--radius-xl) 0 0;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e;"></div>
                            <h3 style="color: #fff; font-size: 15px;">CLAIMORA Live Support</h3>
                        </div>
                        <button type="button" class="modal-close" style="color: #fff;" data-modal-close>&times;</button>
                    </div>
                    <div id="chat-messages" style="flex-grow: 1; padding: 18px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: #f8faf9;">
                        <div style="align-self: flex-start; max-width: 80%; background: #fff; border: 1px solid var(--border-color); border-radius: 12px 12px 12px 0; padding: 12px; font-size: 13px;">
                            Halo! Selamat datang di Layanan Bantuan CLAIMORA. Ada yang bisa kami bantu seputar pengajuan klaim atau verifikasi surveyor?
                        </div>
                    </div>
                    <div style="padding: 12px 16px; border-top: 1px solid var(--border-color); display: flex; gap: 8px; background: #fff; border-radius: 0 0 var(--radius-xl) var(--radius-xl);">
                        <input type="text" id="chat-input" class="form-control" placeholder="Ketik pesan Anda..." style="height: 38px;">
                        <button type="button" id="chat-send-btn" class="btn btn-primary" style="padding: 0 16px;">Kirim</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);
        chatModal = document.getElementById('modal-live-chat');
        chatModal.querySelector('[data-modal-close]').addEventListener('click', () => closeModal('modal-live-chat'));

        const sendBtn = document.getElementById('chat-send-btn');
        const input = document.getElementById('chat-input');
        const msgContainer = document.getElementById('chat-messages');

        const doSend = () => {
            const text = input.value.trim();
            if (!text) return;
            const userMsg = document.createElement('div');
            userMsg.style.cssText = 'align-self: flex-end; max-width: 80%; background: var(--primary-700); color: #fff; border-radius: 12px 12px 0 12px; padding: 10px 14px; font-size: 13px;';
            userMsg.textContent = text;
            msgContainer.appendChild(userMsg);
            input.value = '';
            msgContainer.scrollTop = msgContainer.scrollHeight;

            setTimeout(() => {
                const botMsg = document.createElement('div');
                botMsg.style.cssText = 'align-self: flex-start; max-width: 80%; background: #fff; border: 1px solid var(--border-color); border-radius: 12px 12px 12px 0; padding: 12px; font-size: 13px;';
                botMsg.textContent = "Terima kasih atas pesan Anda. Agen Support CLAIMORA siap membantu verifikasi nomor polis atau status berkas Anda kapan saja.";
                msgContainer.appendChild(botMsg);
                msgContainer.scrollTop = msgContainer.scrollHeight;
            }, 800);
        };

        sendBtn.addEventListener('click', doSend);
        input.addEventListener('keypress', (e) => { if (e.key === 'Enter') doSend(); });
    }
    openModal('modal-live-chat');
}
