/**
 * CLAIMORA - Policy Management JavaScript Module
 * Handles JS Form Validation (JS-format regex, coverage amounts),
 * Interactive Policy Detail Preview, and Date Range Validation.
 */

document.addEventListener('DOMContentLoaded', () => {
    initPolicyValidation();
    initPolicyDetailSelection();
    initPolicyDeleteActions();
});

/**
 * JS Form Validation for Policy Creation/Edit
 */
function initPolicyValidation() {
    const policyInput = document.getElementById('input-policy-number');
    const policyFeedback = document.getElementById('policy-number-feedback');
    const policyForm = document.getElementById('form-policy');

    if (policyInput && policyFeedback) {
        policyInput.addEventListener('input', () => {
            const val = policyInput.value.trim().toUpperCase();
            policyInput.value = val;

            // Regex: Must start with 'JS' followed by exactly 8 digits
            const regex = /^JS\d{8}$/;

            if (val === '') {
                policyFeedback.textContent = 'Format wajib: JS diikuti 8 digit angka (contoh: JS12345678)';
                policyFeedback.style.color = 'var(--text-muted)';
                policyInput.style.borderColor = 'var(--border-color)';
            } else if (regex.test(val)) {
                policyFeedback.textContent = '✓ Format nomor polis valid (JS + 8 digit)';
                policyFeedback.style.color = '#15803d';
                policyInput.style.borderColor = '#22c55e';
            } else {
                policyFeedback.textContent = '✕ Nomor polis belum sesuai (contoh: JS12345678)';
                policyFeedback.style.color = '#b91c1c';
                policyInput.style.borderColor = '#ef4444';
            }
        });
    }

    // Protection Period Date validation
    const startDateInput = document.getElementById('input-start-date');
    const endDateInput = document.getElementById('input-end-date');
    const dateFeedback = document.getElementById('date-feedback');

    const checkDates = () => {
        if (!startDateInput || !endDateInput || !dateFeedback) return;
        const start = startDateInput.value;
        const end = endDateInput.value;

        if (start && end) {
            const sTime = new Date(start).getTime();
            const eTime = new Date(end).getTime();

            if (eTime <= sTime) {
                dateFeedback.textContent = '✕ Tanggal akhir proteksi harus lebih lama dari tanggal mulai.';
                dateFeedback.style.color = '#b91c1c';
                endDateInput.style.borderColor = '#ef4444';
            } else {
                const diffDays = Math.ceil((eTime - sTime) / (1000 * 60 * 60 * 24));
                dateFeedback.textContent = `✓ Masa berlaku proteksi: ${diffDays} hari (${Math.round(diffDays / 30)} bulan)`;
                dateFeedback.style.color = '#15803d';
                endDateInput.style.borderColor = '#22c55e';
            }
        }
    };

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', checkDates);
        endDateInput.addEventListener('change', checkDates);
    }

    // Client-side form submit guard
    if (policyForm) {
        policyForm.addEventListener('submit', (e) => {
            const val = policyInput.value.trim().toUpperCase();
            const regex = /^JS\d{8}$/;

            if (!regex.test(val)) {
                e.preventDefault();
                showToast('Nomor polis tidak valid! Wajib diawali JS diikuti 8 digit angka.', 'danger');
                policyInput.focus();
                return false;
            }

            if (startDateInput && endDateInput) {
                if (new Date(endDateInput.value) <= new Date(startDateInput.value)) {
                    e.preventDefault();
                    showToast('Tanggal akhir proteksi harus setelah tanggal mulai!', 'danger');
                    endDateInput.focus();
                    return false;
                }
            }
        });
    }
}

/**
 * Interactive Policy Detail Preview Drawer / Side Card
 */
function initPolicyDetailSelection() {
    const rows = document.querySelectorAll('.policy-table-row, [data-policy-id]');
    const detailCard = document.getElementById('policy-detail-card');
    if (!detailCard) return;

    rows.forEach(row => {
        row.addEventListener('click', (e) => {
            // Ignore if clicked on an action button (edit/delete)
            if (e.target.closest('.policy-action-btn')) return;

            const policyId = row.getAttribute('data-policy-id');
            if (!policyId) return;

            // Highlight selected row
            rows.forEach(r => r.classList.remove('selected-row'));
            row.classList.add('selected-row');

            fetchPolicyDetail(policyId);
        });
    });
}

function fetchPolicyDetail(id) {
    const detailCard = document.getElementById('policy-detail-card');
    if (!detailCard) return;

    detailCard.style.opacity = '0.5';

    fetch(`index.php?page=policies&action=detail&id=${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(res => {
        detailCard.style.opacity = '1';
        if (res.success && res.data) {
            updatePolicyDetailCard(res.data);
        } else {
            showToast('Gagal memuat detail polis.', 'danger');
        }
    })
    .catch(() => {
        detailCard.style.opacity = '1';
        showToast('Koneksi terganggu saat memuat detail polis.', 'danger');
    });
}

function updatePolicyDetailCard(data) {
    const elNo = document.getElementById('detail-policy-number');
    const elName = document.getElementById('detail-customer-name');
    const elType = document.getElementById('detail-insurance-type');
    const elCoverage = document.getElementById('detail-coverage-amount');
    const elPeriod = document.getElementById('detail-period');
    const elStatusBadge = document.getElementById('detail-status-badge');
    const elVehicle = document.getElementById('detail-vehicle-info');

    if (elNo) elNo.textContent = data.policy_number;
    if (elName) elName.textContent = data.customer_name;
    if (elType) elType.textContent = data.insurance_type;
    if (elCoverage) elCoverage.textContent = data.formatted_coverage;
    if (elPeriod) elPeriod.textContent = data.formatted_period;
    if (elVehicle) elVehicle.textContent = `${data.vehicle_brand} ${data.vehicle_model || ''} (${data.vehicle_plate})`;

    if (elStatusBadge) {
        if (data.is_protection_valid) {
            elStatusBadge.className = 'badge badge-approved';
            elStatusBadge.innerHTML = '<span class="badge-dot"></span> Polis Valid';
        } else {
            elStatusBadge.className = 'badge badge-rejected';
            elStatusBadge.innerHTML = '<span class="badge-dot"></span> Kedaluwarsa / Ditangguhkan';
        }
    }
}

/**
 * Policy Delete Confirm
 */
function initPolicyDeleteActions() {
    document.querySelectorAll('[data-delete-policy]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.getAttribute('data-delete-policy');
            const pno = btn.getAttribute('data-policy-no') || 'ini';
            const hasClaims = parseInt(btn.getAttribute('data-has-claims') || '0', 10);

            let msg = `Apakah Anda yakin ingin menghapus Polis ${pno}? Tindakan ini akan dicatat dalam Audit Log keamanan.`;
            if (hasClaims > 0) {
                msg += `\n\nCatatan: Polis ini memiliki riwayat klaim. Status perlindungan akan dinonaktifkan (Ditangguhkan) untuk menjaga arsip data riwayat klaim Anda.`;
            }

            confirmDelete(
                'Konfirmasi Hapus Polis',
                msg,
                () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `index.php?page=policies&action=delete`;

                    const inputId = document.createElement('input');
                    inputId.type = 'hidden';
                    inputId.name = 'id';
                    inputId.value = id;
                    form.appendChild(inputId);

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const inputCsrf = document.createElement('input');
                    inputCsrf.type = 'hidden';
                    inputCsrf.name = 'csrf_token';
                    inputCsrf.value = csrfToken;
                    form.appendChild(inputCsrf);

                    document.body.appendChild(form);
                    form.submit();
                }
            );
        });
    });
}
