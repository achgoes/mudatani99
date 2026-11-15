/**
 * mudatani99 - Transaksi Akhir JS
 * File: transaksi-akhir/script.js
 */

function escapeHtmlStr(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function parseDecimalNum(val) {
    const match = String(val ?? '').trim().replace(',', '.').match(/([0-9]+(\.[0-9]+)?)/);
    if (!match) return 0;
    const num = parseFloat(match[1]);
    return Number.isFinite(num) ? num : 0;
}

function formatBeratKg(num) {
    const clean = Math.max(0, Math.round((num || 0) * 100) / 100);
    if (Number.isInteger(clean)) {
        return new Intl.NumberFormat('id-ID').format(clean) + ' Kg';
    }
    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 }).format(clean) + ' Kg';
}

function parseRupiahNum(val) {
    const clean = String(val ?? '').replace(/[^0-9]/g, '');
    if (!clean) return 0;
    const num = parseInt(clean, 10);
    return Number.isFinite(num) ? num : 0;
}

function formatRupiahNum(num) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(0, Math.round(num || 0)));
}

function formatInputRupiah(el) {
    const clean = el.value.replace(/[^0-9]/g, '');
    if (!clean) {
        el.value = '';
        return;
    }
    el.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(parseInt(clean, 10));
}

function formatDateID(dateStr) {
    if (!dateStr || dateStr === '0000-00-00') return '-';
    const clean = String(dateStr).substring(0, 10);
    const parts = clean.split('-');
    if (parts.length !== 3) return clean;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

function getSelectedBeratSetoran(mode) {
    const sel = document.getElementById(`${mode}-ta-setoran`);
    if (!sel || sel.selectedIndex < 0) return 0;
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return 0;
    return parseDecimalNum(opt.getAttribute('data-berat') || '0');
}

function onSelectSetoranTa(mode) {
    const sel = document.getElementById(`${mode}-ta-setoran`);
    const previewBox = document.getElementById(`${mode}-ta-setoran-preview`);
    const beratSetoranEl = document.getElementById(`${mode}-ta-berat-setoran`);
    if (!sel) return;

    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        if (previewBox) previewBox.classList.add('hidden');
        if (beratSetoranEl) beratSetoranEl.value = '0 Kg';
        recalcTransaksiAkhir(mode);
        return;
    }

    const beratNum = parseDecimalNum(opt.getAttribute('data-berat') || '0');
    if (beratSetoranEl) {
        beratSetoranEl.value = formatBeratKg(beratNum);
    }

    if (previewBox) {
        previewBox.classList.remove('hidden');
        const pEl = document.getElementById(`${mode}-ta-prev-petani`);
        const tEl = document.getElementById(`${mode}-ta-prev-tanaman`);
        const dEl = document.getElementById(`${mode}-ta-prev-tanggal`);
        if (pEl) pEl.innerText = opt.getAttribute('data-petani') || '-';
        if (tEl) tEl.innerText = opt.getAttribute('data-tanaman') || '-';
        if (dEl) dEl.innerText = opt.getAttribute('data-tanggal') || '-';
    }

    recalcTransaksiAkhir(mode);
}

function onHargaTaInput(mode, el) {
    formatInputRupiah(el);
    recalcTransaksiAkhir(mode);
}

function recalcTransaksiAkhir(mode) {
    const beratSetoran = getSelectedBeratSetoran(mode);
    const rapaksiEl = document.getElementById(`${mode}-ta-rapaksi`);
    const beratAkhirEl = document.getElementById(`${mode}-ta-berat-akhir`);
    const hargaEl = document.getElementById(`${mode}-ta-harga`);
    const jumlahUangEl = document.getElementById(`${mode}-ta-jumlah-uang`);

    const rapaksi = rapaksiEl ? parseDecimalNum(rapaksiEl.value) : 0;
    const beratAkhir = Math.max(0, Math.round((beratSetoran - rapaksi) * 100) / 100);
    const hargaPerKg = hargaEl ? parseRupiahNum(hargaEl.value) : 0;
    const jumlahUang = Math.round(beratAkhir * hargaPerKg);

    if (beratAkhirEl) beratAkhirEl.value = formatBeratKg(beratAkhir);
    if (jumlahUangEl) jumlahUangEl.value = formatRupiahNum(jumlahUang);
}

function openModalTambahTransaksiAkhir() {
    const modal = document.getElementById('modalTambahTransaksiAkhir');
    if (!modal) return;

    const form = document.getElementById('formTambahTransaksiAkhir');
    if (form) {
        form.reset();
        form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
    }

    const alertBox = document.getElementById('add-ta-alert');
    if (alertBox) alertBox.classList.add('hidden');

    const previewBox = document.getElementById('add-ta-setoran-preview');
    if (previewBox) previewBox.classList.add('hidden');

    document.getElementById('add-ta-berat-setoran').value = '0 Kg';
    document.getElementById('add-ta-rapaksi').value = '0';
    document.getElementById('add-ta-berat-akhir').value = '0 Kg';
    document.getElementById('add-ta-jumlah-uang').value = 'Rp 0';
    document.getElementById('add-ta-tanggal').value = new Date().toISOString().split('T')[0];

    modal.classList.add('show');
    setTimeout(() => {
        const sel = document.getElementById('add-ta-setoran');
        if (sel) sel.focus();
    }, 150);
}

function openModalEditTransaksiAkhir(id) {
    if (typeof TRANSAKSI_AKHIR_INFO_MAP === 'undefined') return;
    const t = TRANSAKSI_AKHIR_INFO_MAP[id];
    if (!t) return;

    const modal = document.getElementById('modalEditTransaksiAkhir');
    if (!modal) return;

    const form = document.getElementById('formEditTransaksiAkhir');
    if (form) {
        form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
    }

    const alertBox = document.getElementById('edit-ta-alert');
    if (alertBox) alertBox.classList.add('hidden');

    document.getElementById('edit-ta-id').value = t.id;

    const sel = document.getElementById('edit-ta-setoran');
    if (sel) {
        Array.from(sel.options).forEach(opt => {
            if (!opt.value) return;
            const usedId = parseInt(opt.getAttribute('data-used-ta-id') || '0', 10);
            const isLocked = usedId > 0 && usedId !== parseInt(t.id, 10);
            opt.disabled = isLocked;
        });
        sel.value = String(t.setoran_id || '');
    }

    onSelectSetoranTa('edit');

    document.getElementById('edit-ta-rapaksi').value = t.rapaksi_plain ?? '0';
    document.getElementById('edit-ta-harga').value = t.harga_per_kg_str || 'Rp 0';
    document.getElementById('edit-ta-tanggal').value = (t.tanggal && t.tanggal.length >= 10 && t.tanggal !== '0000-00-00')
        ? t.tanggal.substring(0, 10)
        : new Date().toISOString().split('T')[0];
    document.getElementById('edit-ta-keterangan').value = (t.keterangan && t.keterangan !== '-') ? t.keterangan : '';

    recalcTransaksiAkhir('edit');

    modal.classList.add('show');
}

function openDeleteFromEditTransaksiAkhir() {
    const id = parseInt(document.getElementById('edit-ta-id')?.value || '0', 10);
    if (!id || typeof TRANSAKSI_AKHIR_INFO_MAP === 'undefined') return;
    const t = TRANSAKSI_AKHIR_INFO_MAP[id];
    if (!t) return;

    closeModal('modalEditTransaksiAkhir');

    document.getElementById('delete-ta-id').value = t.id;
    document.getElementById('delete-ta-petani').innerText = t.nama_petani || '-';
    document.getElementById('delete-ta-kode').innerText = t.kode_transaksi || '-';
    document.getElementById('delete-ta-uang').innerText = t.jumlah_uang_str || 'Rp 0';

    const modalDel = document.getElementById('modalDeleteTransaksiAkhir');
    if (modalDel) modalDel.classList.add('show');
}

function openModalInfoTransaksiAkhir(id) {
    if (typeof TRANSAKSI_AKHIR_INFO_MAP === 'undefined') return;
    const t = TRANSAKSI_AKHIR_INFO_MAP[id];
    if (!t) return;

    const namaPetani = t.nama_petani || '-';
    const nParts = namaPetani.trim().split(/\s+/);
    const initials = (nParts.length >= 2)
        ? (nParts[0].charAt(0) + nParts[1].charAt(0)).toUpperCase()
        : namaPetani.substring(0, 2).toUpperCase();

    document.getElementById('info-ta-avatar').innerText = initials;
    document.getElementById('info-ta-nama-petani').innerText = namaPetani;
    document.getElementById('info-ta-alamat').innerText = `Alamat: ${t.alamat_petani || '-'}`;

    const kodeWrap = document.getElementById('info-ta-kode-wrap');
    if (kodeWrap) {
        kodeWrap.innerHTML = `<span class="badge-kode-ta">${escapeHtmlStr(t.kode_transaksi || '-')}</span>`;
    }

    document.getElementById('info-ta-tgl-transaksi').innerText = formatDateID(t.tanggal || t.created_at);
    document.getElementById('info-ta-kode-setoran').innerText = t.kode_setoran || '-';
    document.getElementById('info-ta-tgl-setoran').innerText = formatDateID(t.tanggal_setoran || '');
    document.getElementById('info-ta-petugas').innerText = t.dicatat_oleh || '-';

    document.getElementById('info-ta-berat-setoran').innerText = t.berat_setoran_str || '0 Kg';
    document.getElementById('info-ta-rapaksi').innerText = t.rapaksi_str || '0 Kg';
    document.getElementById('info-ta-berat-akhir').innerText = t.berat_akhir_str || '0 Kg';
    document.getElementById('info-ta-harga').innerText = t.harga_per_kg_str || 'Rp 0';
    document.getElementById('info-ta-jumlah-uang').innerText = t.jumlah_uang_str || 'Rp 0';
    document.getElementById('info-ta-keterangan').innerText = t.keterangan || '-';

    const itemsList = Array.isArray(t.setoran_items) ? t.setoran_items : [];
    const badgeEl = document.getElementById('info-ta-items-badge');
    if (badgeEl) badgeEl.innerText = `${itemsList.length} Item`;

    const tbody = document.getElementById('info-ta-items-tbody');
    if (tbody) {
        if (itemsList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="info-riwayat-empty">Tidak ada rincian item setoran.</td></tr>';
        } else {
            tbody.innerHTML = itemsList.map((it, idx) => `
                <tr>
                    <td class="font-semibold text-slate-500">${idx + 1}</td>
                    <td><span class="font-semibold text-blue-800">${escapeHtmlStr(it.nama_tanaman || '-')}</span></td>
                    <td><span class="font-bold text-emerald-700">${escapeHtmlStr(it.berat || '0 Kg')}</span></td>
                </tr>
            `).join('');
        }
    }

    const printBtn = document.getElementById('btn-info-print-ta');
    if (printBtn) {
        const basePrintUrl = (typeof TRANSAKSI_AKHIR_PRINT_URL !== 'undefined') ? TRANSAKSI_AKHIR_PRINT_URL : 'print-transaksi.php';
        printBtn.href = `${basePrintUrl}?id=${encodeURIComponent(id)}`;
    }

    const modal = document.getElementById('modalInfoTransaksiAkhir');
    if (modal) modal.classList.add('show');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('show');
}

function validateTransaksiAkhirForm(formId, mode) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const alertBox = document.getElementById(`${mode}-ta-alert`);
    const alertText = document.getElementById(`${mode}-ta-alert-text`);

    form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
    if (alertBox) alertBox.classList.add('hidden');

    const errors = [];
    let firstInvalidEl = null;

    function markInvalid(el, label) {
        if (el) el.classList.add('input-error');
        errors.push(label);
        if (!firstInvalidEl && el) firstInvalidEl = el;
    }

    const setoranEl = document.getElementById(`${mode}-ta-setoran`);
    const rapaksiEl = document.getElementById(`${mode}-ta-rapaksi`);
    const hargaEl = document.getElementById(`${mode}-ta-harga`);
    const tglEl = document.getElementById(`${mode}-ta-tanggal`);

    if (!setoranEl || setoranEl.value.trim() === '') {
        markInvalid(setoranEl, 'Data Setoran Petani');
    }

    const beratSetoran = getSelectedBeratSetoran(mode);
    const rapaksiRaw = rapaksiEl ? rapaksiEl.value.trim() : '';
    const rapaksiVal = parseDecimalNum(rapaksiRaw);

    if (rapaksiRaw === '' || parseFloat(rapaksiRaw.replace(',', '.')) < 0) {
        markInvalid(rapaksiEl, 'Rapaksi (>= 0 Kg)');
    } else if (beratSetoran > 0 && rapaksiVal > beratSetoran) {
        markInvalid(rapaksiEl, 'Rapaksi tidak boleh melebihi Berat Setoran');
    }

    if (!hargaEl || parseRupiahNum(hargaEl.value) <= 0) {
        markInvalid(hargaEl, 'Harga /Kg (> Rp 0)');
    }

    if (!tglEl || tglEl.value.trim() === '') {
        markInvalid(tglEl, 'Tanggal Transaksi');
    }

    if (errors.length > 0) {
        if (alertBox && alertText) {
            alertText.innerHTML = `<strong>Peringatan!</strong> Periksa kolom berikut: <span class="font-semibold text-rose-900 underline">${escapeHtmlStr(errors.join(', '))}</span>.`;
            alertBox.classList.remove('hidden');
        }
        showToast(
            'Validasi Gagal!',
            `Terdapat ${errors.length} kolom yang perlu diperbaiki sebelum menyimpan.`,
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

function filterTransaksiAkhirTable() {
    const query = (document.getElementById('ta-search-input')?.value || '').toLowerCase();
    const dateFrom = document.getElementById('ta-date-from')?.value || '';
    const dateTo = document.getElementById('ta-date-to')?.value || '';
    const rows = document.querySelectorAll('.ta-row-item');

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

    const emptyFilterRow = document.getElementById('ta-empty-filter');
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
    [
        { formId: 'formTambahTransaksiAkhir', mode: 'add' },
        { formId: 'formEditTransaksiAkhir', mode: 'edit' }
    ].forEach(({ formId, mode }) => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!validateTransaksiAkhirForm(formId, mode)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            form.querySelectorAll('.modal-input').forEach(input => {
                const clearErr = () => {
                    input.classList.remove('input-error');
                    const alertBox = document.getElementById(`${mode}-ta-alert`);
                    if (alertBox && !form.querySelector('.modal-input.input-error')) {
                        alertBox.classList.add('hidden');
                    }
                };
                input.addEventListener('input', clearErr);
                input.addEventListener('change', clearErr);
            });
        }
    });

    const dateFromEl = document.getElementById('ta-date-from');
    const dateToEl = document.getElementById('ta-date-to');
    if (dateFromEl && dateToEl) {
        const checkClearDate = () => {
            if (!dateFromEl.value && !dateToEl.value) {
                filterTransaksiAkhirTable();
            }
        };
        dateFromEl.addEventListener('change', checkClearDate);
        dateToEl.addEventListener('change', checkClearDate);
    }

    if (window.TRANSAKSI_AKHIR_TOAST) {
        showToast(
            window.TRANSAKSI_AKHIR_TOAST.title,
            window.TRANSAKSI_AKHIR_TOAST.message,
            window.TRANSAKSI_AKHIR_TOAST.type,
            window.TRANSAKSI_AKHIR_TOAST.duration || 1600
        );

        if (window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
        }
    }
});
