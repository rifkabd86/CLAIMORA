/**
 * CLAIMORA - Claim Multi-step Wizard & Multi-upload Photos
 * Handles Drag & drop photo upload, MIME validation, and Wizard steps.
 */

document.addEventListener('DOMContentLoaded', () => {
    initClaimWizard();
    initDamagePhotoUpload();
    initPolicyDropdownIntegration();
});

let currentStep = 1;
const uploadedFileIds = [];

/**
 * Multi-Step Wizard Controller
 */
function initClaimWizard() {
    const wizardContainer = document.querySelector('.claim-wizard-container');
    if (!wizardContainer) return;

    const btnNext = document.getElementById('btn-wizard-next');
    const btnPrev = document.getElementById('btn-wizard-prev');
    const btnSubmit = document.getElementById('btn-wizard-submit');

    if (btnNext) {
        btnNext.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                goToStep(currentStep + 1);
            }
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            if (currentStep > 1) {
                goToStep(currentStep - 1);
            }
        });
    }
}

function goToStep(step) {
    currentStep = step;

    // Toggle step panes
    document.querySelectorAll('.wizard-step-pane').forEach((pane, idx) => {
        pane.style.display = (idx + 1 === step) ? 'block' : 'none';
    });

    // Toggle stepper indicators
    document.querySelectorAll('.wizard-step').forEach((st, idx) => {
        const stepNum = idx + 1;
        st.classList.remove('active', 'completed');
        if (stepNum === step) {
            st.classList.add('active');
        } else if (stepNum < step) {
            st.classList.add('completed');
        }
    });

    // Control button visibility
    const btnPrev = document.getElementById('btn-wizard-prev');
    const btnNext = document.getElementById('btn-wizard-next');
    const btnSubmit = document.getElementById('btn-wizard-submit');

    if (btnPrev) btnPrev.style.display = (step > 1) ? 'inline-flex' : 'none';
    if (btnNext) btnNext.style.display = (step < 3) ? 'inline-flex' : 'none';
    if (btnSubmit) btnSubmit.style.display = (step === 3) ? 'inline-flex' : 'none';

    // Populate confirmation step summary
    if (step === 3) {
        populateConfirmationSummary();
    }
}

function validateStep(step) {
    if (step === 1) {
        const policySelect = document.getElementById('claim-policy-id');
        const incDate = document.getElementById('claim-incident-date');
        const incTime = document.getElementById('claim-incident-time');
        const incLoc = document.getElementById('claim-incident-location');
        const chronology = document.getElementById('claim-chronology');
        const estCost = document.getElementById('claim-damage-cost');

        if (!policySelect || !policySelect.value) {
            showToast('Silakan pilih nomor polis tertanggung terlebih dahulu.', 'warning');
            policySelect?.focus();
            return false;
        }

        if (!incDate?.value || !incTime?.value) {
            showToast('Tanggal dan waktu kejadian wajib diisi.', 'warning');
            incDate?.focus();
            return false;
        }

        if (!incLoc?.value.trim()) {
            showToast('Lokasi kejadian wajib diisi.', 'warning');
            incLoc?.focus();
            return false;
        }

        if (!chronology?.value.trim() || chronology.value.trim().length < 15) {
            showToast('Kronologi kejadian wajib diisi minimal 15 karakter.', 'warning');
            chronology?.focus();
            return false;
        }

        if (!estCost?.value.trim()) {
            showToast('Estimasi biaya perbaikan wajib diisi.', 'warning');
            estCost?.focus();
            return false;
        }

        return true;
    }

    if (step === 2) {
        // Step 2: Ensure at least one photo is uploaded or selected
        const previewItems = document.querySelectorAll('.preview-item');
        if (previewItems.length === 0) {
            if (!confirm('Anda belum mengunggah foto kerusakan kendaraan. Apakah Anda yakin ingin melanjutkan tanpa foto?')) {
                return false;
            }
        }
        return true;
    }

    return true;
}

function populateConfirmationSummary() {
    const policySelect = document.getElementById('claim-policy-id');
    const selectedOption = policySelect ? policySelect.options[policySelect.selectedIndex] : null;
    const selectedPolicyId = policySelect ? parseInt(policySelect.value, 10) : 0;

    let policyObj = null;
    if (typeof availablePoliciesData !== 'undefined' && Array.isArray(availablePoliciesData)) {
        policyObj = availablePoliciesData.find(p => parseInt(p.id, 10) === selectedPolicyId);
    }

    if (policyObj) {
        const brandModel = `${policyObj.vehicle_brand || ''} ${policyObj.vehicle_model || ''}`.trim();
        const plate = policyObj.vehicle_plate || '-';
        const prod = policyObj.insurance_type || 'Kendaraan';
        const cov = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(policyObj.coverage_amount || 0);

        const elPol = document.getElementById('summary-policy');
        if (elPol) elPol.textContent = `${policyObj.policy_number} (${prod})`;

        const elVeh = document.getElementById('summary-vehicle');
        if (elVeh) elVeh.textContent = `${brandModel} [${plate}]`;

        const elCov = document.getElementById('summary-coverage');
        if (elCov) elCov.textContent = cov;
    } else {
        const elPol = document.getElementById('summary-policy');
        if (elPol) elPol.textContent = selectedOption ? selectedOption.text : '-';
    }

    const incDate = document.getElementById('claim-incident-date')?.value || '';
    const incTime = document.getElementById('claim-incident-time')?.value || '';
    const elDatetime = document.getElementById('summary-datetime');
    if (elDatetime) elDatetime.textContent = `${incDate} ${incTime}`.trim();

    const elLoc = document.getElementById('summary-location');
    if (elLoc) elLoc.textContent = document.getElementById('claim-incident-location')?.value || '-';

    const elChron = document.getElementById('summary-chronology');
    if (elChron) elChron.textContent = document.getElementById('claim-chronology')?.value || '-';

    const elCost = document.getElementById('summary-damage-cost');
    if (elCost) elCost.textContent = document.getElementById('claim-damage-cost')?.value || '-';

    const countPhotos = document.querySelectorAll('.preview-item').length;
    const elPhotos = document.getElementById('summary-photos-count');
    if (elPhotos) elPhotos.textContent = `${countPhotos} foto kerusakan terunggah`;
}

/**
 * Integrasi Pilihan Dropdown Polis dengan Data Kendaraan Otomatis
 */
function initPolicyDropdownIntegration() {
    const policySelect = document.getElementById('claim-policy-id');
    const detailsCard = document.getElementById('policy-vehicle-details-card');

    if (!policySelect || !detailsCard) return;

    function updateDetails(policyId) {
        if (!policyId || typeof availablePoliciesData === 'undefined' || !Array.isArray(availablePoliciesData)) {
            detailsCard.style.display = 'none';
            return;
        }

        const pol = availablePoliciesData.find(p => parseInt(p.id, 10) === parseInt(policyId, 10));
        if (!pol) {
            detailsCard.style.display = 'none';
            return;
        }

        // Fill detail fields
        const plateEl = document.getElementById('detail-card-plate');
        if (plateEl) plateEl.textContent = pol.vehicle_plate || '-';

        const brandEl = document.getElementById('detail-card-brand');
        const fullBrand = `${pol.vehicle_brand || ''} ${pol.vehicle_model || ''}`.trim();
        if (brandEl) brandEl.textContent = fullBrand || '-';

        const prodEl = document.getElementById('detail-card-product');
        if (prodEl) prodEl.textContent = pol.insurance_type ? `Asuransi ${pol.insurance_type}` : 'Asuransi Kendaraan';

        const covEl = document.getElementById('detail-card-coverage');
        const formattedCov = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(pol.coverage_amount || 0);
        if (covEl) covEl.textContent = formattedCov;

        const periodEl = document.getElementById('detail-card-period');
        if (periodEl) {
            const startFmt = pol.start_date ? formatDateId(pol.start_date) : '-';
            const endFmt = pol.end_date ? formatDateId(pol.end_date) : '-';
            periodEl.textContent = `${startFmt} s/d ${endFmt}`;
        }

        const custEl = document.getElementById('detail-card-customer');
        if (custEl) custEl.textContent = pol.customer_name || '-';

        const statusEl = document.getElementById('detail-card-status');
        if (statusEl) {
            statusEl.textContent = pol.status || 'Aktif';
            statusEl.className = (pol.status === 'Aktif') ? 'badge badge-approved' : 'badge badge-rejected';
        }

        detailsCard.style.display = 'block';
    }

    function formatDateId(dateStr) {
        if (!dateStr) return '-';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return dateStr;
    }

    policySelect.addEventListener('change', (e) => {
        updateDetails(e.target.value);
    });

    // Auto-select if there is exactly 1 policy
    if (policySelect.options.length === 2 && !policySelect.value) {
        policySelect.selectedIndex = 1;
        updateDetails(policySelect.value);
    } else if (policySelect.value) {
        updateDetails(policySelect.value);
    }
}

/**
 * Drag & Drop Multi-file Damage Photos Upload
 */
function initDamagePhotoUpload() {
    const dropzone = document.getElementById('damage-dropzone');
    const fileInput = document.getElementById('damage-file-input');
    const previewGrid = document.getElementById('uploaded-previews-grid');

    if (!dropzone || !fileInput) return;

    // Click to open file picker
    dropzone.addEventListener('click', (e) => {
        if (!e.target.closest('.preview-remove')) {
            fileInput.click();
        }
    });

    // Drag-over styling
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        });
    });

    // Handle Drop
    dropzone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    // Handle File Input Change
    fileInput.addEventListener('change', (e) => {
        handleFiles(fileInput.files);
    });
}

function handleFiles(files) {
    if (!files || files.length === 0) return;

    const allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    const maxBytes = 5 * 1024 * 1024; // 5 MB

    Array.from(files).forEach(file => {
        // 1. Client-side MIME check
        if (!allowedMimes.includes(file.type)) {
            showToast(`Berkas "${file.name}" ditolak. Hanya format JPG, PNG, atau WEBP yang diizinkan!`, 'danger');
            return;
        }

        // 2. Client-side Size check
        if (file.size > maxBytes) {
            showToast(`Berkas "${file.name}" melebihi batas maksimal 5MB!`, 'danger');
            return;
        }

        // 3. Upload file via AJAX
        uploadSingleFile(file);
    });
}

function uploadSingleFile(file) {
    const previewGrid = document.getElementById('uploaded-previews-grid');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Create temporary preview element with loading spinner
    const previewDiv = document.createElement('div');
    previewDiv.className = 'preview-item';
    previewDiv.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:#fff;background:#2d6a4f;font-size:12px;">
            Mengunggah...
        </div>
    `;
    previewGrid.appendChild(previewDiv);

    const formData = new FormData();
    formData.append('file', file);
    formData.append('csrf_token', csrfToken);

    fetch('index.php?page=claims&action=upload_file', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            uploadedFileIds.push(data.file_id);
            syncUploadedIdsInput();

            previewDiv.innerHTML = `
                <img>
                <button type="button" class="preview-remove" title="Hapus foto">&times;</button>
            `;

            // Set as properties: the file name comes from the user and must not be parsed as HTML
            const previewImg = previewDiv.querySelector('img');
            previewImg.src = data.file_url;
            previewImg.alt = data.file_name;

            previewDiv.querySelector('.preview-remove').addEventListener('click', (e) => {
                e.stopPropagation();
                removeFile(data.file_id, previewDiv);
            });

            showToast(`Foto "${file.name}" berhasil diunggah (${data.file_size}).`, 'success');
        } else {
            previewDiv.remove();
            showToast(`Gagal: ${data.message}`, 'danger');
        }
    })
    .catch(err => {
        previewDiv.remove();
        showToast(`Kesalahan jaringan saat mengunggah foto "${file.name}".`, 'danger');
    });
}

function removeFile(fileId, element) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch(`index.php?page=claims&action=delete_file&file_id=${fileId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `csrf_token=${encodeURIComponent(csrfToken)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            element.remove();
            const idx = uploadedFileIds.indexOf(fileId);
            if (idx > -1) uploadedFileIds.splice(idx, 1);
            syncUploadedIdsInput();
            showToast('Foto berhasil dihapus.', 'info');
        } else {
            showToast(data.message, 'danger');
        }
    });
}

function syncUploadedIdsInput() {
    const hiddenInput = document.getElementById('input-uploaded-file-ids');
    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(uploadedFileIds);
    }
}
