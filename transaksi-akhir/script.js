/**
 * mudatani99 - Manajemen Transaksi Akhir JS
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

function parseBeratNum(val) {
    const match = String(val ?? '').trim().replace(',', '.').match(/([0-9]+(\.[0-9]+)?)/);
    if (!match) return 0;
    const num = parseFloat(match[1]);
    return Number.isFinite(num) ? num : 0;
}

function parseRapaksiStrict(raw) {
    const clean = String(raw ?? '').trim().replace(',', '.');
    if (clean === '' || !/^\d+(\.\d+)?$/.test(clean)) {
        return null;
    }
    const num = parseFloat(clean);
    return Number.isFinite(num) ? Math.round(num * 100) / 100 : null;
}

function parseHargaInt(raw) {
    const digits = String(raw ?? '').replace(/[^0-9]/g, '');
    if (digits === '') return 0;
    const val = parseInt(digits, 10);
    return Number.isFinite(val) ? val : 0;
}

function formatRupiahDisplay(num) {
    const clean = Math.max(0, parseInt(num || 0, 10) || 0);
    return clean > 0 ? clean.toLocaleString('id-ID') : '';
}

function formatRupiahText(num) {
    const clean = Math.max(0, Math.round(num || 0));
    return `Rp ${clean.toLocaleString('id-ID')}`;
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

function getEditContext(mode) {
    if (mode !== 'edit') return null;
    const idVal = document.getElementById('edit-ta-id')?.value || '';
    if (!idVal || typeof TRANSAKSI_AKHIR_INFO_MAP === 'undefined') return null;
    return TRANSAKSI_AKHIR_INFO_MAP[idVal] || null;
}

function getSisaSetoranPetani(anggotaId, editRecord = null) {
    const aKey = String(anggotaId || '');
    if (aKey === '') return 0;

    let total = 0;
    if (typeof TRANSAKSI_AKHIR_REKAP_SETORAN !== 'undefined' && TRANSAKSI_AKHIR_REKAP_SETORAN[aKey]) {
        const val = parseFloat(TRANSAKSI_AKHIR_REKAP_SETORAN[aKey].total_berat || 0);
        if (Number.isFinite(val) && val > 0) total = val;
    }

    if (editRecord && String(editRecord.anggota_id || '') === aKey) {
        const recSetoran = parseFloat(editRecord.jumlah_setoran_num ?? parseBeratNum(editRecord.jumlah_setoran)) || 0;
        total += recSetoran;
    }

    return Math.max(0, Math.round(total * 100) / 100);
}

function getSisaSetoranTanaman(anggotaId, tanamanId, editRecord = null) {
    const aKey = String(anggotaId || '');
    const tKey = String(tanamanId || '');
    if (aKey === '' || tKey === '') return 0;

    let sisa = 0;
    if (typeof TRANSAKSI_AKHIR_REKAP_SETORAN !== 'undefined' && TRANSAKSI_AKHIR_REKAP_SETORAN[aKey]) {
        const rekap = TRANSAKSI_AKHIR_REKAP_SETORAN[aKey];
        if (rekap.berat_per_tanaman && rekap.berat_per_tanaman[tKey] !== undefined) {
            const val = parseFloat(rekap.berat_per_tanaman[tKey]);
            if (Number.isFinite(val) && val > 0) sisa = val;
        }
    }

    if (
        editRecord
        && String(editRecord.anggota_id || '') === aKey
        && String(editRecord.tanaman_id || '') === tKey
    ) {
        const recSetoran = parseFloat(editRecord.jumlah_setoran_num ?? parseBeratNum(editRecord.jumlah_setoran)) || 0;
        sisa += recSetoran;
    }

    return Math.max(0, Math.round(sisa * 100) / 100);
}

function getDefaultTanamanByAnggota(anggotaId, editRecord = null) {
    const aKey = String(anggotaId || '');
    if (aKey === '') return '';

    if (typeof TRANSAKSI_AKHIR_MASTER_TANAMAN !== 'undefined' && Array.isArray(TRANSAKSI_AKHIR_MASTER_TANAMAN)) {
        for (const mt of TRANSAKSI_AKHIR_MASTER_TANAMAN) {
            const tid = String(mt.id || '');
            if (tid !== '' && getSisaSetoranTanaman(aKey, tid, editRecord) > 0) {
                return tid;
            }
        }
    }

    if (typeof TRANSAKSI_AKHIR_REKAP_SETORAN !== 'undefined' && TRANSAKSI_AKHIR_REKAP_SETORAN[aKey]) {
        const defId = TRANSAKSI_AKHIR_REKAP_SETORAN[aKey].default_tanaman_id;
        return defId ? String(defId) : '';
    }
    return '';
}

function refreshAnggotaSelectOptions(mode, editRecord = null) {
    const prefix = mode === 'edit' ? 'edit-ta' : 'add-ta';
    const anggotaEl = document.getElementById(`${prefix}-anggota`);
    if (!anggotaEl) return;

    Array.from(anggotaEl.options).forEach(opt => {
        if (!opt.value) return;
        const nama = opt.getAttribute('data-nama') || opt.textContent.replace(/\s*\(Setoran:.*\)$/i, '').trim();
        opt.setAttribute('data-nama', nama);
        const sisa = getSisaSetoranPetani(opt.value, editRecord);
        opt.setAttribute('data-setoran', String(sisa));
        opt.textContent = `${nama} (Setoran: ${formatBeratKg(sisa)})`;
    });
}

function refreshTanamanSelectOptions(mode, anggotaId, editRecord = null) {
    const prefix = mode === 'edit' ? 'edit-ta' : 'add-ta';
    const tanamanEl = document.getElementById(`${prefix}-tanaman`);
    const helperEl = document.getElementById(`${prefix}-tanaman-helper`);
    if (!tanamanEl) return;

    const aKey = String(anggotaId || '').trim();

    Array.from(tanamanEl.options).forEach(opt => {
        if (!opt.value) return;
        const nama = opt.getAttribute('data-nama') || opt.textContent.replace(/\s*\(Setoran:.*\)$/i, '').trim();
        opt.setAttribute('data-nama', nama);
        const sisa = aKey !== '' ? getSisaSetoranTanaman(aKey, opt.value, editRecord) : 0;
        opt.setAttribute('data-setoran', String(sisa));
        opt.textContent = `${nama} (Setoran: ${formatBeratKg(sisa)})`;
    });

    if (helperEl) {
        const selectedTid = tanamanEl.value.trim();
        helperEl.classList.remove('ta-tanaman-status-ok', 'ta-tanaman-status-zero');
        if (aKey === '') {
            helperEl.textContent = 'Pilih Nama Petani terlebih dahulu untuk melihat sisa setoran per tanaman (Kg).';
        } else if (selectedTid === '') {
            helperEl.textContent = 'Pilih jenis tanaman di atas untuk melihat jumlah setorannya (Kg).';
        } else {
            const sisaDipilih = getSisaSetoranTanaman(aKey, selectedTid, editRecord);
            if (sisaDipilih > 0) {
                helperEl.textContent = `Sisa setoran tanaman terpilih untuk petani ini: ${formatBeratKg(sisaDipilih)}.`;
                helperEl.classList.add('ta-tanaman-status-ok');
            } else {
                helperEl.textContent = 'Sisa setoran tanaman ini untuk petani terpilih adalah 0 Kg.';
                helperEl.classList.add('ta-tanaman-status-zero');
            }
        }
    }
}

function updateCalcTransaksiAkhir(mode, overrideSetoranNum = null) {
    const prefix = mode === 'edit' ? 'edit-ta' : 'add-ta';
    const anggotaEl = document.getElementById(`${prefix}-anggota`);
    const tanamanEl = document.getElementById(`${prefix}-tanaman`);
    const hargaEl = document.getElementById(`${prefix}-harga`);
    const setoranEl = document.getElementById(`${prefix}-jumlah-setoran`);
    const rapaksiEl = document.getElementById(`${prefix}-rapaksi`);
    const finalEl = document.getElementById(`${prefix}-berat-final`);
    const totalUangEl = document.getElementById(`${prefix}-total-uang`);

    if (!anggotaEl || !setoranEl || !rapaksiEl || !finalEl) return;

    const editRecord = getEditContext(mode);
    const anggotaId = anggotaEl.value.trim();
    const tanamanId = tanamanEl ? tanamanEl.value.trim() : '';
    let jumlahSetoran = 0;

    if (overrideSetoranNum !== null) {
        jumlahSetoran = Math.max(0, Math.round(overrideSetoranNum * 100) / 100);
    } else if (anggotaId !== '' && tanamanId !== '') {
        jumlahSetoran = getSisaSetoranTanaman(anggotaId, tanamanId, editRecord);
    }

    setoranEl.value = formatBeratKg(jumlahSetoran);

    const rapaksiNum = parseRapaksiStrict(rapaksiEl.value);
    const effectiveRapaksi = rapaksiNum !== null ? rapaksiNum : 0;
    const beratFinal = Math.max(0, Math.round((jumlahSetoran - effectiveRapaksi) * 100) / 100);

    finalEl.value = formatBeratKg(beratFinal);

    if (totalUangEl) {
        const hargaPerKg = hargaEl ? parseHargaInt(hargaEl.value) : 0;
        const totalUang = Math.round(beratFinal * hargaPerKg);
        totalUangEl.value = formatRupiahText(totalUang);
    }
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

    const tglInput = document.getElementById('add-ta-tanggal');
    if (tglInput) tglInput.value = new Date().toISOString().split('T')[0];

    const rapaksiInput = document.getElementById('add-ta-rapaksi');
    if (rapaksiInput) rapaksiInput.value = '0';

    refreshAnggotaSelectOptions('add', null);
    refreshTanamanSelectOptions('add', '', null);
    updateCalcTransaksiAkhir('add', 0);

    modal.classList.add('show');
    setTimeout(() => {
        const sel = document.getElementById('add-ta-anggota');
        if (sel) sel.focus();
    }, 150);
}

function openModalEditTransaksiAkhir(transaksiId) {
    if (typeof TRANSAKSI_AKHIR_INFO_MAP === 'undefined') return;
    const t = TRANSAKSI_AKHIR_INFO_MAP[transaksiId];
    if (!t) return;

    const modal = document.getElementById('modalEditTransaksiAkhir');
    const form = document.getElementById('formEditTransaksiAkhir');
    if (!modal || !form) return;

    form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
    const alertBox = document.getElementById('edit-ta-alert');
    if (alertBox) alertBox.classList.add('hidden');

    document.getElementById('edit-ta-id').value = t.id || '';
    refreshAnggotaSelectOptions('edit', t);
    document.getElementById('edit-ta-anggota').value = String(t.anggota_id || '');

    refreshTanamanSelectOptions('edit', String(t.anggota_id || ''), t);
    document.getElementById('edit-ta-tanaman').value = String(t.tanaman_id || '');
    refreshTanamanSelectOptions('edit', String(t.anggota_id || ''), t);

    document.getElementById('edit-ta-harga').value = formatRupiahDisplay(t.harga_per_kg_num || 0);
    document.getElementById('edit-ta-tanggal').value = (t.tanggal || '').substring(0, 10);
    document.getElementById('edit-ta-rapaksi').value = String(t.rapaksi_num ?? parseBeratNum(t.rapaksi));
    document.getElementById('edit-ta-keterangan').value = (t.keterangan && t.keterangan !== '-') ? t.keterangan : '';

    const setoranNum = getSisaSetoranTanaman(t.anggota_id, t.tanaman_id || '', t);
    updateCalcTransaksiAkhir('edit', setoranNum);

    modal.classList.add('show');
    setTimeout(() => {
        const rapaksiEl = document.getElementById('edit-ta-rapaksi');
        if (rapaksiEl) rapaksiEl.focus();
    }, 150);
}

function openModalInfoTransaksiAkhir(transaksiId) {
    if (typeof TRANSAKSI_AKHIR_INFO_MAP === 'undefined') return;
    const t = TRANSAKSI_AKHIR_INFO_MAP[transaksiId];
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
        kodeWrap.innerHTML = `<span class="badge-kode-transaksi">${escapeHtmlStr(t.kode_transaksi || '-')}</span>`;
    }

    document.getElementById('info-ta-jumlah-setoran').innerText = t.jumlah_setoran || '0 Kg';
    document.getElementById('info-ta-rapaksi').innerText = t.rapaksi || '0 Kg';
    document.getElementById('info-ta-berat-final').innerText = t.berat_final || '0 Kg';
    document.getElementById('info-ta-tanaman').innerText = t.nama_tanaman || '-';
    document.getElementById('info-ta-harga').innerText = t.harga_per_kg_fmt || 'Rp 0 /Kg';
    const infoTotalUangEl = document.getElementById('info-ta-total-uang');
    if (infoTotalUangEl) {
        infoTotalUangEl.innerText = t.jumlah_uang_fmt || 'Rp 0';
    }
    document.getElementById('info-ta-tgl').innerText = formatDateID(t.tanggal || t.created_at);
    document.getElementById('info-ta-petugas').innerText = t.dicatat_oleh || '-';
    document.getElementById('info-ta-keterangan').innerText = t.keterangan || '-';

    const riwayatList = Array.isArray(t.setoran_riwayat) ? t.setoran_riwayat : [];
    const badgeEl = document.getElementById('info-ta-setoran-badge');
    if (badgeEl) badgeEl.innerText = `${riwayatList.length} Nota`;

    const tbody = document.getElementById('info-ta-setoran-tbody');
    if (tbody) {
        if (riwayatList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="info-riwayat-empty">Belum ada rincian nota setoran petani terkait.</td></tr>';
        } else {
            tbody.innerHTML = riwayatList.map((st, idx) => `
                <tr>
                    <td class="font-semibold text-slate-500">${idx + 1}</td>
                    <td>
                        <div class="font-bold text-slate-800">${escapeHtmlStr(st.kode_setoran || '-')}</div>
                        <div class="text-[11px] text-slate-500">${escapeHtmlStr(formatDateID(st.tanggal))}</div>
                    </td>
                    <td>
                        <span class="badge-nama-tanaman">
                            <i class="fa-solid fa-wheat-awn"></i>
                            ${escapeHtmlStr(st.nama_tanaman || '-')}
                        </span>
                    </td>
                    <td>
                        <span class="badge-jumlah-setoran">${escapeHtmlStr(st.jumlah_berat || '0 Kg')}</span>
                    </td>
                </tr>
            `).join('');
        }
    }

    const printBtn = document.getElementById('btn-info-print-ta');
    if (printBtn) {
        const basePrintUrl = (typeof TRANSAKSI_AKHIR_PRINT_URL !== 'undefined') ? TRANSAKSI_AKHIR_PRINT_URL : 'print-transaksi-akhir.php';
        printBtn.href = `${basePrintUrl}?id=${encodeURIComponent(transaksiId)}`;
    }

    const modal = document.getElementById('modalInfoTransaksiAkhir');
    if (modal) modal.classList.add('show');
}

function openModalDeleteTransaksiAkhir(id, kode, namaPetani, beratFinal) {
    document.getElementById('delete-ta-id').value = id;
    document.getElementById('delete-ta-kode').innerText = kode || '-';
    document.getElementById('delete-ta-petani').innerText = namaPetani || '-';
    document.getElementById('delete-ta-berat').innerText = beratFinal || '0 Kg';

    const modal = document.getElementById('modalDeleteTransaksiAkhir');
    if (modal) modal.classList.add('show');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('show');
}

function validateTransaksiAkhirForm(mode) {
    const prefix = mode === 'edit' ? 'edit-ta' : 'add-ta';
    const formId = mode === 'edit' ? 'formEditTransaksiAkhir' : 'formTambahTransaksiAkhir';
    const form = document.getElementById(formId);
    if (!form) return true;

    const alertBox = document.getElementById(`${prefix}-alert`);
    const alertText = document.getElementById(`${prefix}-alert-text`);

    form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
    if (alertBox) alertBox.classList.add('hidden');

    const issues = [];
    let firstInvalidEl = null;

    function markInvalid(el, message) {
        if (el) el.classList.add('input-error');
        issues.push(message);
        if (!firstInvalidEl && el) firstInvalidEl = el;
    }

    const anggotaEl = document.getElementById(`${prefix}-anggota`);
    const tanamanEl = document.getElementById(`${prefix}-tanaman`);
    const hargaEl = document.getElementById(`${prefix}-harga`);
    const tglEl = document.getElementById(`${prefix}-tanggal`);
    const setoranEl = document.getElementById(`${prefix}-jumlah-setoran`);
    const rapaksiEl = document.getElementById(`${prefix}-rapaksi`);

    const anggotaId = anggotaEl ? anggotaEl.value.trim() : '';
    if (anggotaId === '') {
        markInvalid(anggotaEl, 'Nama Petani wajib dipilih');
    }

    if (!tanamanEl || tanamanEl.value.trim() === '') {
        markInvalid(tanamanEl, 'Tanaman wajib dipilih');
    }

    const hargaNum = hargaEl ? parseHargaInt(hargaEl.value) : 0;
    if (hargaNum <= 0) {
        markInvalid(hargaEl, 'Harga (/Kg) wajib diisi lebih dari Rp 0');
    }

    if (!tglEl || tglEl.value.trim() === '') {
        markInvalid(tglEl, 'Tanggal wajib diisi');
    }

    const jumlahSetoran = setoranEl ? parseBeratNum(setoranEl.value) : 0;
    const selectedTanamanId = tanamanEl ? tanamanEl.value.trim() : '';
    if (anggotaId !== '' && selectedTanamanId !== '' && jumlahSetoran <= 0) {
        markInvalid(tanamanEl, 'Sisa setoran tanaman ini untuk petani yang dipilih adalah 0 Kg');
    } else if (anggotaId !== '' && selectedTanamanId === '' && jumlahSetoran <= 0) {
        markInvalid(anggotaEl, 'Petani yang dipilih belum memiliki sisa Setoran Petani (> 0 Kg)');
    }

    const rapaksiNum = rapaksiEl ? parseRapaksiStrict(rapaksiEl.value) : null;
    if (rapaksiNum === null) {
        markInvalid(rapaksiEl, 'Rapaksi wajib diisi angka minimal 0 Kg');
    } else if (jumlahSetoran > 0 && rapaksiNum > jumlahSetoran) {
        markInvalid(rapaksiEl, `Rapaksi (${formatBeratKg(rapaksiNum)}) tidak boleh melebihi Jumlah Setoran (${formatBeratKg(jumlahSetoran)})`);
    }

    if (issues.length > 0) {
        if (alertBox && alertText) {
            alertText.innerHTML = `<strong>Peringatan!</strong> ${escapeHtmlStr(issues.join('; '))}.`;
            alertBox.classList.remove('hidden');
        }

        showToast(
            'Validasi Gagal!',
            issues[0],
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
    ['add', 'edit'].forEach(mode => {
        const prefix = mode === 'edit' ? 'edit-ta' : 'add-ta';
        const formId = mode === 'edit' ? 'formEditTransaksiAkhir' : 'formTambahTransaksiAkhir';
        const form = document.getElementById(formId);
        const anggotaEl = document.getElementById(`${prefix}-anggota`);
        const tanamanEl = document.getElementById(`${prefix}-tanaman`);
        const hargaEl = document.getElementById(`${prefix}-harga`);
        const rapaksiEl = document.getElementById(`${prefix}-rapaksi`);

        const handleAnggotaOrTanamanChange = (autoSelectDefaultTanaman = false) => {
            if (!anggotaEl) return;
            const anggotaId = anggotaEl.value.trim();
            const editRecord = getEditContext(mode);

            if (autoSelectDefaultTanaman && tanamanEl && anggotaId !== '') {
                const currentTid = tanamanEl.value.trim();
                const currentSisa = currentTid !== '' ? getSisaSetoranTanaman(anggotaId, currentTid, editRecord) : 0;
                if (currentTid === '' || currentSisa <= 0) {
                    const defTanamanId = getDefaultTanamanByAnggota(anggotaId, editRecord);
                    if (defTanamanId !== '') {
                        tanamanEl.value = defTanamanId;
                    }
                }
            }

            refreshTanamanSelectOptions(mode, anggotaId, editRecord);
            const tanamanId = tanamanEl ? tanamanEl.value.trim() : '';

            if (mode === 'edit') {
                const setoranNum = getSisaSetoranTanaman(anggotaId, tanamanId, editRecord);
                updateCalcTransaksiAkhir('edit', setoranNum);
            } else {
                updateCalcTransaksiAkhir('add');
            }
        };

        if (anggotaEl) {
            anggotaEl.addEventListener('change', () => handleAnggotaOrTanamanChange(true));
        }

        if (tanamanEl) {
            tanamanEl.addEventListener('change', () => handleAnggotaOrTanamanChange(false));
        }

        if (hargaEl) {
            hargaEl.addEventListener('input', () => {
                const num = parseHargaInt(hargaEl.value);
                hargaEl.value = formatRupiahDisplay(num);
                const setoranEl = document.getElementById(`${prefix}-jumlah-setoran`);
                const currentSetoran = setoranEl ? parseBeratNum(setoranEl.value) : 0;
                updateCalcTransaksiAkhir(mode, currentSetoran);
            });
        }

        if (rapaksiEl) {
            rapaksiEl.addEventListener('input', () => {
                const setoranEl = document.getElementById(`${prefix}-jumlah-setoran`);
                const currentSetoran = setoranEl ? parseBeratNum(setoranEl.value) : 0;
                updateCalcTransaksiAkhir(mode, currentSetoran);
            });
        }

        if (form) {
            form.addEventListener('submit', (e) => {
                if (!validateTransaksiAkhirForm(mode)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            form.querySelectorAll('.modal-input').forEach(input => {
                const clearErr = () => {
                    input.classList.remove('input-error');
                    const alertBox = document.getElementById(`${prefix}-alert`);
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
