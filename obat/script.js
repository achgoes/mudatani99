function openModalTambahObat() {
    const modal = document.getElementById('modalTambahObat');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const sel = document.getElementById('add-obat-id');
            if (sel) sel.focus();
        }, 150);
    }
}

function openModalEditObat(data) {
    if (!data) return;
    document.getElementById('edit-id').value           = data.id;
    document.getElementById('edit-jumlah-berat').value = data.jumlah_berat;
    document.getElementById('edit-keterangan').value   = data.keterangan;

    // Pisahkan angka dan satuan (Pcs / Botol) dari jumlah_kemasan
    const rawKemasan = (data.jumlah_kemasan || '').trim();
    const numMatch = rawKemasan.match(/([0-9]+([.,][0-9]+)?)/);
    document.getElementById('edit-jumlah-angka').value = numMatch ? numMatch[1].replace(',', '.') : '';

    const satuanSelect = document.getElementById('edit-satuan-kemasan');
    if (satuanSelect) {
        if (rawKemasan.toLowerCase().includes('botol')) {
            satuanSelect.value = 'Botol';
        } else {
            satuanSelect.value = 'Pcs';
        }
    }

    const obatSelect = document.getElementById('edit-obat-id');
    if (obatSelect) {
        if (data.obat_id) {
            obatSelect.value = data.obat_id;
        } else {
            obatSelect.value = '';
            for (let i = 0; i < obatSelect.options.length; i++) {
                if (obatSelect.options[i].text.trim().toLowerCase() === (data.nama_obat || '').trim().toLowerCase()) {
                    obatSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    const modal = document.getElementById('modalEditObat');
    if (modal) modal.classList.add('show');
}

function openModalDeleteObat(id, nama, kemasan) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;
    document.getElementById('delete-kemasan-display').innerText = kemasan;

    const modal = document.getElementById('modalDeleteObat');
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

function filterObatTable() {
    const query = (document.getElementById('obat-search-input')?.value || '').toLowerCase();
    const rows  = document.querySelectorAll('.obat-row-item');

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

if (window.OBAT_TOAST) {
    document.addEventListener('DOMContentLoaded', () => {
        showToast(
            window.OBAT_TOAST.title,
            window.OBAT_TOAST.message,
            window.OBAT_TOAST.type,
            1000
        );

        if (window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }
    });
}

