/**
 * mudatani99 - Manajemen Setoran Petani JS
 * File: setoran/script.js
 */

function escapeHtmlStr(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function parseBeratNum(val) {
    const match = String(val ?? '').trim().replace(',', '.').match(/([0-9]+(\.[0-9]+)?)/);
    if (!match) return 0;
    const num = parseFloat(match[1]);
    return Number.isFinite(num) ? num : 0;
}

function formatBeratKg(num) {
    const clean = Math.max(0, Math.round((num || 0) * 100) / 100);
    if (Number.isInteger(clean)) {
        return `${clean} Kg`;
    }
    return `${parseFloat(clean.toFixed(2))} Kg`;
}

function formatDateID(dateStr) {
    if (!dateStr || dateStr === '0000-00-00') return '-';
    const clean = String(dateStr).substring(0, 10);
    const parts = clean.split('-');
    if (parts.length !== 3) return clean;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

function buildMasterTanamanOptionsHtml(selectedId = '') {
    const list = (typeof SETORAN_MASTER_TANAMAN !== 'undefined' && Array.isArray(SETORAN_MASTER_TANAMAN))
        ? SETORAN_MASTER_TANAMAN
        : [];
    const selStr = String(selectedId || '');
    let html = '<option value="">-- Pilih Jenis Tanaman --</option>';
    list.forEach(item => {
        const idStr = String(item.id || '');
        const selectedAttr = (selStr !== '' && idStr === selStr) ? ' selected' : '';
        html += `<option value="${escapeHtmlStr(idStr)}"${selectedAttr}>${escapeHtmlStr(item.nama_produk || '')}</option>`;
    });
    return html;
}

function refreshSetoranItemLabels() {
    const container = document.getElementById('add-setoran-items-container');
    if (!container) return;
    const rows = container.querySelectorAll('.setoran-item-row');
    rows.forEach((row, idx) => {
        const titleEl = row.querySelector('.setoran-item-row-title');
        if (titleEl) {
            titleEl.textContent = `Item Setoran #${idx + 1}`;
        }
        const removeBtn = row.querySelector('.btn-remove-setoran-item');
        if (removeBtn) {
            const disableRemove = rows.length <= 1;
            removeBtn.disabled = disableRemove;
            removeBtn.classList.toggle('opacity-40', disableRemove);
            removeBtn.classList.toggle('cursor-not-allowed', disableRemove);
        }
    });
}

function recalcTotalBeratSetoran() {
    const container = document.getElementById('add-setoran-items-container');
    const totalEl = document.getElementById('add-setoran-total-berat');
    if (!container || !totalEl) return;

    let totalBerat = 0;
    container.querySelectorAll('.setoran-item-berat').forEach(input => {
        totalBerat += parseBeratNum(input.value);
    });

    totalEl.value = formatBeratKg(totalBerat);
}

function removeSetoranItemRow(btn) {
    const container = document.getElementById('add-setoran-items-container');
    if (!container) return;
    const rows = container.querySelectorAll('.setoran-item-row');
    if (rows.length <= 1) return;

    const row = btn.closest('.setoran-item-row');
    if (row) {
        row.remove();
        refreshSetoranItemLabels();
        recalcTotalBeratSetoran();
    }
}

function addSetoranItemRow(itemData = null) {
    const container = document.getElementById('add-setoran-items-container');
    if (!container) return;

    const selectedTanamanId = itemData && itemData.tanaman_id ? itemData.tanaman_id : '';
    const beratVal = itemData && itemData.berat ? parseBeratNum(itemData.berat) : '';

    const row = document.createElement('div');
    row.className = 'setoran-item-row';
    row.innerHTML = `
        <div class="setoran-item-row-head">
            <span class="setoran-item-row-title">Item Setoran</span>
            <button type="button" class="btn-remove-setoran-item" title="Hapus item tanaman ini">
                <i class="fa-solid fa-trash-can"></i>
                <span>Hapus</span>
            </button>
        </div>
        <div class="setoran-item-fields-grid">
            <div class="modal-form-group mb-0">
                <label class="modal-label">Jenis Tanaman (Master Tanaman) <span class="text-rose-500">*</span></label>
                <select name="item_tanaman_id[]" required class="modal-input setoran-item-tanaman">
                    ${buildMasterTanamanOptionsHtml(selectedTanamanId)}
                </select>
            </div>
            <div class="modal-form-group mb-0">
                <label class="modal-label">Berat Item <span class="text-rose-500">*</span></label>
                <div class="input-berat-group">
                    <input type="number" min="0.01" step="0.01" name="item_berat[]" required class="modal-input setoran-item-berat" placeholder="Contoh: 25" value="${escapeHtmlStr(beratVal)}">
                    <span class="berat-unit-badge">Kg</span>
                </div>
            </div>
        </div>
    `;

    container.appendChild(row);

    const selEl = row.querySelector('.setoran-item-tanaman');
    const beratEl = row.querySelector('.setoran-item-berat');
    const removeBtn = row.querySelector('.btn-remove-setoran-item');
    const form = container.closest('form');
    const alertBox = document.getElementById('add-setoran-alert');

    const clearRowError = (el) => {
        el.classList.remove('input-error');
        if (form && alertBox && !form.querySelector('.modal-input.input-error')) {
            alertBox.classList.add('hidden');
        }
    };

    if (selEl) {
        selEl.addEventListener('change', () => clearRowError(selEl));
    }
    if (beratEl) {
        beratEl.addEventListener('input', () => {
            clearRowError(beratEl);
            recalcTotalBeratSetoran();
        });
    }
    if (removeBtn) {
        removeBtn.addEventListener('click', () => removeSetoranItemRow(removeBtn));
    }

    refreshSetoranItemLabels();
    recalcTotalBeratSetoran();
}

function openModalTambahSetoran() {
    const modal = document.getElementById('modalTambahSetoran');
    if (!modal) return;

    const form = document.getElementById('formTambahSetoran');
    if (form) {
        form.reset();
        form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
    }

    const alertBox = document.getElementById('add-setoran-alert');
    if (alertBox) alertBox.classList.add('hidden');

    const container = document.getElementById('add-setoran-items-container');
    if (container) container.innerHTML = '';
    addSetoranItemRow();

    const tglInput = document.getElementById('add-setoran-tanggal');
    if (tglInput) tglInput.value = new Date().toISOString().split('T')[0];

    const totalEl = document.getElementById('add-setoran-total-berat');
    if (totalEl) totalEl.value = '0 Kg';

    modal.classList.add('show');
    setTimeout(() => {
        const sel = document.getElementById('add-setoran-anggota');
        if (sel) sel.focus();
    }, 150);
}

function openModalInfoSetoran(setoranId) {
    if (typeof SETORAN_INFO_MAP === 'undefined') return;
    const s = SETORAN_INFO_MAP[setoranId];
    if (!s) return;

    const namaPetani = s.nama_petani || '-';
    const nParts = namaPetani.trim().split(/\s+/);
    const initials = (nParts.length >= 2)
        ? (nParts[0].charAt(0) + nParts[1].charAt(0)).toUpperCase()
        : namaPetani.substring(0, 2).toUpperCase();

    document.getElementById('info-setoran-avatar').innerText = initials;
    document.getElementById('info-setoran-nama-petani').innerText = namaPetani;
    document.getElementById('info-setoran-alamat').innerText = `Alamat: ${s.alamat_petani || '-'}`;

    const kodeWrap = document.getElementById('info-setoran-kode-wrap');
    if (kodeWrap) {
        kodeWrap.innerHTML = `<span class="badge-kode-setoran">${escapeHtmlStr(s.kode_setoran || '-')}</span>`;
    }

    document.getElementById('info-setoran-tgl').innerText = formatDateID(s.tanggal || s.created_at);
    document.getElementById('info-setoran-total-berat').innerText = s.jumlah_berat || '0 Kg';
    document.getElementById('info-setoran-petugas').innerText = s.dicatat_oleh || '-';
    document.getElementById('info-setoran-keterangan').innerText = s.keterangan || '-';

    const itemsList = Array.isArray(s.items) ? s.items : [];
    const badgeEl = document.getElementById('info-setoran-items-badge');
    if (badgeEl) badgeEl.innerText = `${itemsList.length} Item`;

    const tbody = document.getElementById('info-setoran-items-tbody');
    if (tbody) {
        if (itemsList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="info-riwayat-empty">Tidak ada rincian item setoran.</td></tr>';
        } else {
            tbody.innerHTML = itemsList.map((it, idx) => `
                <tr>
                    <td class="font-semibold text-slate-500">${idx + 1}</td>
                    <td><span class="badge-nama-tanaman"><i class="fa-solid fa-wheat-awn"></i> ${escapeHtmlStr(it.nama_tanaman || '-')}</span></td>
                    <td><span class="nik-badge"><i class="fa-solid fa-weight-hanging text-agro-600"></i> ${escapeHtmlStr(it.berat || '0 Kg')}</span></td>
                </tr>
            `).join('');
        }
    }

    const printBtn = document.getElementById('btn-info-print-setoran');
    if (printBtn) {
        const basePrintUrl = (typeof SETORAN_PRINT_URL !== 'undefined') ? SETORAN_PRINT_URL : 'print-setoran.php';
        printBtn.href = `${basePrintUrl}?id=${encodeURIComponent(setoranId)}`;
    }

    const modal = document.getElementById('modalInfoSetoran');
    if (modal) modal.classList.add('show');
}

function openModalDeleteSetoran(id, kode, namaPetani, jumlahBerat) {
    document.getElementById('delete-setoran-id').value = id;
    document.getElementById('delete-setoran-kode').innerText = kode || '-';
    document.getElementById('delete-setoran-petani').innerText = namaPetani || '-';
    document.getElementById('delete-setoran-berat').innerText = jumlahBerat || '0 Kg';

    const modal = document.getElementById('modalDeleteSetoran');
    if (modal) modal.classList.add('show');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('show');
}

function validateSetoranForm() {
    const form = document.getElementById('formTambahSetoran');
    if (!form) return true;

    const alertBox = document.getElementById('add-setoran-alert');
    const alertText = document.getElementById('add-setoran-alert-text');

    form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
    if (alertBox) alertBox.classList.add('hidden');

    const missingFields = [];
    let firstInvalidEl = null;

    function markInvalid(el, label) {
        if (el) el.classList.add('input-error');
        missingFields.push(label);
        if (!firstInvalidEl && el) firstInvalidEl = el;
    }

    const anggotaEl = document.getElementById('add-setoran-anggota');
    if (!anggotaEl || anggotaEl.value.trim() === '') {
        markInvalid(anggotaEl, 'Nama Petani');
    }

    const tglEl = document.getElementById('add-setoran-tanggal');
    if (!tglEl || tglEl.value.trim() === '') {
        markInvalid(tglEl, 'Tanggal Setoran');
    }

    const rows = form.querySelectorAll('#add-setoran-items-container .setoran-item-row');
    if (rows.length === 0) {
        missingFields.push('Minimal 1 Item Tanaman');
    } else {
        rows.forEach((row, idx) => {
            const no = idx + 1;
            const selEl = row.querySelector('.setoran-item-tanaman');
            const beratEl = row.querySelector('.setoran-item-berat');
            if (!selEl || selEl.value.trim() === '') {
                markInvalid(selEl, `Item #${no} Jenis Tanaman`);
            }
            if (!beratEl || parseBeratNum(beratEl.value) <= 0) {
                markInvalid(beratEl, `Item #${no} Berat (> 0 Kg)`);
            }
        });
    }

    if (missingFields.length > 0) {
        if (alertBox && alertText) {
            alertText.innerHTML = `<strong>Peringatan!</strong> Kolom berikut belum diisi dengan benar: <span class="font-semibold text-rose-900 underline">${escapeHtmlStr(missingFields.join(', '))}</span>. Harap lengkapi sebelum menyimpan.`;
            alertBox.classList.remove('hidden');
        }

        showToast(
            'Kolom Belum Lengkap!',
            `Terdapat ${missingFields.length} kolom wajib yang belum diisi dengan benar.`,
            'danger',
            3200
        );

        if (firstInvalidEl) {
            firstInvalidEl.focus();
            if (typeof firstInvalidEl.scrollIntoView === 'function') {
                firstInvalidEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        return false;
    }

    return true;
}

function filterSetoranTable() {
    const query = (document.getElementById('setoran-search-input')?.value || '').toLowerCase();
    const dateFrom = document.getElementById('setoran-date-from')?.value || '';
    const dateTo = document.getElementById('setoran-date-to')?.value || '';
    const rows = document.querySelectorAll('.setoran-row-item');

    let visibleCount = 0;
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowTanggal = row.getAttribute('data-tanggal') || '';

        const matchSearch = text.includes(query);
        let matchTanggal = true;
        if (dateFrom && (!rowTanggal || rowTanggal < dateFrom)) {
            matchTanggal = false;
        }
        if (dateTo && (!rowTanggal || rowTanggal > dateTo)) {
            matchTanggal = false;
        }

        const isVisible = matchSearch && matchTanggal;
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    });

    const emptyFilterRow = document.getElementById('setoran-empty-filter');
    if (emptyFilterRow && rows.length > 0) {
        emptyFilterRow.classList.toggle('hidden', visibleCount > 0);
    }
}

function showToast(title, message, type = 'success', durationMs = 1400) {
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
            if (toast.parentElement) toast.remove();
        }, 320);
    }

    const autoTimer = setTimeout(dismiss, durationMs);
    toast.addEventListener('click', () => {
        clearTimeout(autoTimer);
        dismiss();
    });
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

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formTambahSetoran');
    if (form) {
        form.addEventListener('submit', (e) => {
            if (!validateSetoranForm()) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        form.querySelectorAll('.modal-input').forEach(input => {
            const clearErr = () => {
                input.classList.remove('input-error');
                const alertBox = document.getElementById('add-setoran-alert');
                if (alertBox && !form.querySelector('.modal-input.input-error')) {
                    alertBox.classList.add('hidden');
                }
            };
            input.addEventListener('input', clearErr);
            input.addEventListener('change', clearErr);
        });
    }

    const dateFromEl = document.getElementById('setoran-date-from');
    const dateToEl = document.getElementById('setoran-date-to');
    if (dateFromEl && dateToEl) {
        const checkClearDate = () => {
            if (!dateFromEl.value && !dateToEl.value) {
                filterSetoranTable();
            }
        };
        dateFromEl.addEventListener('change', checkClearDate);
        dateToEl.addEventListener('change', checkClearDate);
    }

    if (window.SETORAN_TOAST) {
        showToast(
            window.SETORAN_TOAST.title,
            window.SETORAN_TOAST.message,
            window.SETORAN_TOAST.type,
            window.SETORAN_TOAST.duration || 1600
        );

        if (window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
        }
    }
});
