// Modal Handlers: Daftar Anggota Petani
function openModalTambahAnggota() {
    const modal = document.getElementById('modalTambahAnggota');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const namaInput = document.getElementById('add-nama');
            if (namaInput) namaInput.focus();
        }, 150);
    }
}

function openModalEditAnggota(data) {
    if (!data) return;
    document.getElementById('edit-id').value         = data.id;
    document.getElementById('edit-nama').value       = data.nama_petani;
    document.getElementById('edit-alamat').value     = data.alamat;
    document.getElementById('edit-keterangan').value = data.keterangan;

    const modal = document.getElementById('modalEditAnggota');
    if (modal) modal.classList.add('show');
}

function openModalDeleteAnggota(id, nama) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;

    const modal = document.getElementById('modalDeleteAnggota');
    if (modal) modal.classList.add('show');
}

// Modal Handlers: Pemetaan & Luas Lahan
function openModalTambahLahan() {
    const modal = document.getElementById('modalTambahLahan');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const sel = document.getElementById('add-lahan-anggota');
            if (sel) sel.focus();
        }, 150);
    }
}

function openModalEditLahan(data) {
    if (!data) return;
    document.getElementById('edit-lahan-id').value         = data.id;
    document.getElementById('edit-lahan-anggota').value    = data.anggota_id;
    document.getElementById('edit-lahan-luas').value       = data.luas_lahan;
    document.getElementById('edit-lahan-keterangan').value = data.keterangan;

    const tanamanSelect = document.getElementById('edit-lahan-tanaman');
    if (tanamanSelect) {
        if (data.tanaman_id) {
            tanamanSelect.value = data.tanaman_id;
        } else {
            // Fallback pencocokan berdasarkan teks nama produk jika tanaman_id belum terisi
            tanamanSelect.value = '';
            for (let i = 0; i < tanamanSelect.options.length; i++) {
                if (tanamanSelect.options[i].text.trim().toLowerCase() === (data.ditanami || '').trim().toLowerCase()) {
                    tanamanSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    const modal = document.getElementById('modalEditLahan');
    if (modal) modal.classList.add('show');
}

function openModalDeleteLahan(id, nama, luas) {
    document.getElementById('delete-lahan-id').value = id;
    document.getElementById('delete-lahan-name-display').innerText = nama;
    document.getElementById('delete-lahan-luas-display').innerText = luas;

    const modal = document.getElementById('modalDeleteLahan');
    if (modal) modal.classList.add('show');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('show');
}

document.querySelectorAll('.custom-modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
            backdrop.classList.remove('show');
        }
    });
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.custom-modal-backdrop.show').forEach(m => m.classList.remove('show'));
    }
});

function filterTableRows(inputId, rowSelector) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const query = input.value.toLowerCase();
    const rows  = document.querySelectorAll(rowSelector);

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

function showToast(title, message, type = 'success', durationMs = 1000) {
    let portal = document.getElementById('toast-portal');
    if (!portal) {
        portal = document.createElement('div');
        portal.id = 'toast-portal';
        portal.className = 'toast-portal-topright';
        document.body.appendChild(portal);
    }

    const toast = document.createElement('div');
    toast.className = `toast-item-card toast-${type}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('title', 'Klik notifikasi untuk menutup seketika');

    const iconClass = (type === 'success') ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';

    toast.innerHTML = `
        <div class="toast-card-inner">
            <div class="toast-icon-wrapper">
                <i class="${iconClass}"></i>
            </div>
            <div class="toast-content-wrapper">
                <div class="toast-title-text">${title}</div>
                <div class="toast-body-text">${message}</div>
            </div>
            <button type="button" class="toast-close-btn" aria-label="Tutup notifikasi">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="toast-progress-container">
            <div class="toast-progress-bar" style="animation-duration: ${durationMs}ms;"></div>
        </div>
    `;

    portal.appendChild(toast);

    let isDismissed = false;
    function dismiss() {
        if (isDismissed) return;
        isDismissed = true;
        toast.classList.add('toast-dismissing');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 320);
    }

    const autoTimer = setTimeout(dismiss, durationMs);

    toast.addEventListener('click', () => {
        clearTimeout(autoTimer);
        dismiss();
    });
}

if (window.ANGGOTA_TOAST) {
    document.addEventListener('DOMContentLoaded', () => {
        showToast(
            window.ANGGOTA_TOAST.title,
            window.ANGGOTA_TOAST.message,
            window.ANGGOTA_TOAST.type,
            1000
        );

        if (window.history.replaceState) {
            const viewParam = (window.ANGGOTA_CONFIG && window.ANGGOTA_CONFIG.viewParam) ? window.ANGGOTA_CONFIG.viewParam : '';
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + viewParam;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }
    });
}
