function openModalTambahProduk() {
    const modal = document.getElementById('modalTambahProduk');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const inp = document.getElementById('add-nama-produk');
            if (inp) inp.focus();
        }, 150);
    }
}

function openModalEditProduk(data) {
    if (!data) return;
    document.getElementById('edit-id').value          = data.id;
    document.getElementById('edit-nama-produk').value = data.nama_produk;
    document.getElementById('edit-jenis').value       = data.jenis;
    document.getElementById('edit-keterangan').value  = data.keterangan;

    const modal = document.getElementById('modalEditProduk');
    if (modal) modal.classList.add('show');
}

function openModalDeleteProduk(id, nama, jenis) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;
    document.getElementById('delete-jenis-display').innerText = jenis;

    const modal = document.getElementById('modalDeleteProduk');
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

// ========================================================
// PENGURUTAN (SORT) TABEL MASTER PRODUK
// Hanya berlaku untuk Nomor, Nama Produk, dan Jenis
// ========================================================
const cfg = window.OBAT_PUPUK_CONFIG || {};
let currentSortColumn = cfg.sortCol || 'nomor';
let currentSortOrder  = cfg.sortOrder || 'asc';

function sortTableBy(column) {
    if (!['nomor', 'nama', 'jenis'].includes(column)) return;

    if (currentSortColumn === column) {
        currentSortOrder = (currentSortOrder === 'asc') ? 'desc' : 'asc';
    } else {
        currentSortColumn = column;
        currentSortOrder  = 'asc';
    }

    applyTableSort();
}

function handleSortSelectChange(val) {
    const parts = val.split('-');
    if (parts.length === 2 && ['nomor', 'nama', 'jenis'].includes(parts[0]) && ['asc', 'desc'].includes(parts[1])) {
        currentSortColumn = parts[0];
        currentSortOrder  = parts[1];
        applyTableSort();
    }
}

function updateSortUI() {
    ['nomor', 'nama', 'jenis'].forEach(col => {
        const th   = document.getElementById(`th-sort-${col}`);
        const icon = document.getElementById(`sort-icon-${col}`);
        if (!th || !icon) return;

        if (col === currentSortColumn) {
            th.classList.add('active-sort');
            if (currentSortOrder === 'asc') {
                icon.className = 'fa-solid fa-sort-up sort-icon';
            } else {
                icon.className = 'fa-solid fa-sort-down sort-icon';
            }
        } else {
            th.classList.remove('active-sort');
            icon.className = 'fa-solid fa-sort sort-icon';
        }
    });

    const sortSelect = document.getElementById('produk-sort-select');
    if (sortSelect) {
        sortSelect.value = `${currentSortColumn}-${currentSortOrder}`;
    }
}

function applyTableSort() {
    const tbody = document.getElementById('table-produk-tbody');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('.produk-row-item'));
    if (rows.length === 0) return;

    rows.sort((a, b) => {
        if (currentSortColumn === 'nomor') {
            const valA = parseInt(a.getAttribute('data-nomor') || '0', 10);
            const valB = parseInt(b.getAttribute('data-nomor') || '0', 10);
            return (currentSortOrder === 'asc') ? (valA - valB) : (valB - valA);
        } else if (currentSortColumn === 'nama') {
            const valA = (a.getAttribute('data-nama') || '').trim();
            const valB = (b.getAttribute('data-nama') || '').trim();
            const cmp = valA.localeCompare(valB, 'id', { sensitivity: 'base' });
            return (currentSortOrder === 'asc') ? cmp : -cmp;
        } else if (currentSortColumn === 'jenis') {
            const valA = (a.getAttribute('data-jenis') || '').trim();
            const valB = (b.getAttribute('data-jenis') || '').trim();
            const cmp = valA.localeCompare(valB, 'id', { sensitivity: 'base' });
            if (cmp !== 0) {
                return (currentSortOrder === 'asc') ? cmp : -cmp;
            }
            const subA = (a.getAttribute('data-nama') || '').trim();
            const subB = (b.getAttribute('data-nama') || '').trim();
            return subA.localeCompare(subB, 'id', { sensitivity: 'base' });
        }
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
    updateSortUI();
}

function filterProdukTable() {
    const query = (document.getElementById('produk-search-input')?.value || '').toLowerCase();
    const jenisFilter = document.getElementById('produk-jenis-filter')?.value || 'ALL';
    const rows = document.querySelectorAll('.produk-row-item');

    let visibleCount = 0;
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowJenis = row.getAttribute('data-jenis') || '';
        const matchQuery = text.includes(query);
        const matchJenis = (jenisFilter === 'ALL' || rowJenis === jenisFilter);
        const isShow = matchQuery && matchJenis;
        row.style.display = isShow ? '' : 'none';
        if (isShow) visibleCount++;
    });

    let emptyRow = document.getElementById('table-produk-empty-search');
    if (visibleCount === 0 && rows.length > 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'table-produk-empty-search';
            emptyRow.innerHTML = `
                <td colspan="5" class="table-empty-cell py-6 text-center text-slate-500">
                    <i class="fa-solid fa-magnifying-glass text-2xl mb-2 text-slate-400 block"></i>
                    Tidak ada produk yang sesuai dengan kriteria pencarian atau filter.
                </td>
            `;
            document.getElementById('table-produk-tbody')?.appendChild(emptyRow);
        }
        emptyRow.style.display = '';
    } else if (emptyRow) {
        emptyRow.style.display = 'none';
    }
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

document.addEventListener('DOMContentLoaded', () => {
    updateSortUI();

    if (cfg.toast) {
        showToast(cfg.toast.title, cfg.toast.message, cfg.toast.type, 1000);

        if (window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }
    }
});

