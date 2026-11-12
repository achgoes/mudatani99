/**
 * mudatani99 - Inventaris Stok Tanaman, Penjualan, Hutang Piutang & Riwayat JS
 * File: tanaman/script.js
 */

function openModalTambahTanaman() {
    const modal = document.getElementById('modalTambahTanaman');
    if (modal) {
        const form = document.getElementById('formTambahStokTanaman') || modal.querySelector('form');
        if (form) form.reset();

        const alertBox = document.getElementById('add-stock-status-alert');
        if (alertBox) {
            alertBox.className = 'hidden mb-3 p-3 rounded-xl border text-xs flex items-start gap-2.5';
            alertBox.innerHTML = '';
        }

        const btnText = document.getElementById('btn-save-tambah-tanaman-text');
        if (btnText) btnText.innerText = 'Simpan Stok Tanaman';

        modal.classList.add('show');
        setTimeout(() => {
            const sel = document.getElementById('add-tanaman-id');
            if (sel) sel.focus();
        }, 150);
    }
}

function onSelectTanamanTambahStok() {
    const sel = document.getElementById('add-tanaman-id');
    const alertBox = document.getElementById('add-stock-status-alert');
    const btnText = document.getElementById('btn-save-tambah-tanaman-text');
    if (!sel) return;

    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        if (alertBox) {
            alertBox.className = 'hidden mb-3 p-3 rounded-xl border text-xs flex items-start gap-2.5';
            alertBox.innerHTML = '';
        }
        if (btnText) btnText.innerText = 'Simpan Stok Tanaman';
        return;
    }

    const hasStock   = opt.getAttribute('data-has-stock') === '1';
    const curKemasan = opt.getAttribute('data-cur-kemasan') || '0';
    const curBerat   = opt.getAttribute('data-cur-berat') || '0 Kg';
    const nama       = opt.getAttribute('data-nama') || opt.text;

    if (alertBox) {
        if (hasStock) {
            alertBox.className = 'mb-3 p-3 rounded-xl border border-blue-200 bg-blue-50/90 text-blue-900 text-xs flex items-start gap-2.5 shadow-sm';
            alertBox.innerHTML = `
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </div>
                <div>
                    <strong class="block text-blue-950 font-semibold mb-0.5">Produk Sudah Ada di Tabel Stok (Akumulasi Stok)</strong>
                    <span>Produk <b>${escapeHtmlStr(nama)}</b> saat ini memiliki stok: <b>${escapeHtmlStr(curKemasan)} Pcs (${escapeHtmlStr(curBerat)})</b>. Jumlah kemasan &amp; berat yang Anda masukkan di bawah akan <b>langsung dijumlahkan ke baris stok tersebut</b> dan tidak membuat list nomor baru.</span>
                </div>
            `;
            if (btnText) btnText.innerText = 'Jumlahkan ke Stok yang Ada';
        } else {
            alertBox.className = 'mb-3 p-3 rounded-xl border border-emerald-200 bg-emerald-50/90 text-emerald-900 text-xs flex items-start gap-2.5 shadow-sm';
            alertBox.innerHTML = `
                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-plus"></i>
                </div>
                <div>
                    <strong class="block text-emerald-950 font-semibold mb-0.5">Produk Belum Ada di Tabel Stok (List Baru)</strong>
                    <span>Produk <b>${escapeHtmlStr(nama)}</b> belum ada di daftar stok. Penambahan ini akan didaftarkan sebagai <b>baris baru (nomor baru)</b> di tabel stok tanaman.</span>
                </div>
            `;
            if (btnText) btnText.innerText = 'Buat List Stok Baru';
        }
    }

    autoCalcBeratTambahStok();
}

function autoCalcBeratTambahStok() {
    const sel = document.getElementById('add-tanaman-id');
    const angkaInput = document.getElementById('add-jumlah-angka');
    const beratInput = document.getElementById('add-jumlah-berat');
    if (!sel || !angkaInput || !beratInput) return;

    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;

    const bpk = parseFloat(opt.getAttribute('data-bpk') || '10');
    const rawAngka = angkaInput.value.trim();
    const match = rawAngka.match(/([0-9]+([.,][0-9]+)?)/);
    if (match) {
        const kNum = parseFloat(match[1].replace(',', '.'));
        if (!isNaN(kNum) && kNum > 0) {
            const totalKg = Math.round(kNum * bpk * 100) / 100;
            beratInput.value = `${totalKg} Kg`;
        }
    }
}

function openModalEditTanaman(data) {
    if (!data) return;
    document.getElementById('edit-id').value           = data.id;
    document.getElementById('edit-jumlah-berat').value = data.jumlah_berat;
    document.getElementById('edit-keterangan').value   = data.keterangan;

    // Ambil angka dari jumlah_kemasan (satuan otomatis Pcs)
    const rawKemasan = (data.jumlah_kemasan || '').trim();
    const numMatch = rawKemasan.match(/([0-9]+([.,][0-9]+)?)/);
    document.getElementById('edit-jumlah-angka').value = numMatch ? numMatch[1].replace(',', '.') : '';

    const tanamanSelect = document.getElementById('edit-tanaman-id');
    if (tanamanSelect) {
        if (data.tanaman_id) {
            tanamanSelect.value = data.tanaman_id;
        } else {
            tanamanSelect.value = '';
            for (let i = 0; i < tanamanSelect.options.length; i++) {
                if (tanamanSelect.options[i].text.trim().toLowerCase() === (data.nama_tanaman || '').trim().toLowerCase()) {
                    tanamanSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    const modal = document.getElementById('modalEditTanaman');
    if (modal) modal.classList.add('show');
}

function openModalDeleteTanaman(id, nama, kemasan) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;
    document.getElementById('delete-kemasan-display').innerText = kemasan;

    const modal = document.getElementById('modalDeleteTanaman');
    if (modal) modal.classList.add('show');
}

function parseFirstNum(val) {
    const match = String(val ?? '').trim().match(/([0-9]+([.,][0-9]+)?)/);
    if (!match) return 0;
    const num = parseFloat(match[1].replace(',', '.'));
    return Number.isFinite(num) ? num : 0;
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

function buildTanamanOptionsHtml(selectedId = '') {
    const list = (typeof TANAMAN_STOK_LIST !== 'undefined' && Array.isArray(TANAMAN_STOK_LIST)) ? TANAMAN_STOK_LIST : [];
    const selStr = String(selectedId || '');
    let html = '<option value="" data-kemasan="0" data-berat="0" data-bpk="10">-- Pilih Tanaman --</option>';
    list.forEach(tn => {
        const tnId = String(tn.id || '');
        const selectedAttr = (selStr !== '' && tnId === selStr) ? ' selected' : '';
        const bpk = tn.berat_per_kemasan ?? 10;
        html += `<option value="${escapeHtmlStr(tnId)}" data-kemasan="${escapeHtmlStr(tn.kemasan_num ?? 0)}" data-berat="${escapeHtmlStr(tn.berat_num ?? 0)}" data-bpk="${escapeHtmlStr(bpk)}"${selectedAttr}>${escapeHtmlStr(tn.nama_tanaman || '')} (Tersedia: ${escapeHtmlStr(tn.jumlah_kemasan || '0 Pcs')} / ${escapeHtmlStr(tn.jumlah_berat || '0 Kg')})</option>`;
    });
    return html;
}

function getItemContextConfig(context) {
    if (context === 'add-penjualan' || context === 'edit-penjualan') {
        const mode = context.startsWith('edit') ? 'edit' : 'add';
        return {
            containerId: `${mode}-penjualan-items-container`,
            totalInputId: `${mode}-penjualan-total`,
            alertId: `${mode}-penjualan-alert`,
            isHutang: false,
            mode,
        };
    }
    return {
        containerId: `${context}-hutang-items-container`,
        totalInputId: `${context}-hutang-jumlah`,
        alertId: `${context}-hutang-alert`,
        isHutang: true,
        mode: context,
    };
}

function refreshTanamanItemRowLabels(context) {
    const { containerId } = getItemContextConfig(context);
    const container = document.getElementById(containerId);
    if (!container) return;
    const rows = container.querySelectorAll('.hutang-item-row');
    rows.forEach((row, idx) => {
        const titleEl = row.querySelector('.hutang-item-row-title');
        if (titleEl) titleEl.textContent = `Item Tanaman #${idx + 1}`;
        const removeBtn = row.querySelector('.btn-remove-hutang-item');
        if (removeBtn) {
            const disableRemove = rows.length <= 1;
            removeBtn.disabled = disableRemove;
            removeBtn.classList.toggle('opacity-40', disableRemove);
            removeBtn.classList.toggle('cursor-not-allowed', disableRemove);
        }
    });
}

function autoCalcBeratItemRow(row) {
    if (!row) return;
    const sel = row.querySelector('.hutang-item-tanaman');
    const kemasanInput = row.querySelector('.hutang-item-kemasan');
    const beratInput = row.querySelector('.hutang-item-berat');
    if (!sel || !kemasanInput || !beratInput) return;

    const selectedOpt = sel.options[sel.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) return;

    const bpk = parseFloat(selectedOpt.getAttribute('data-bpk') || '10');
    const kNum = parseFirstNum(kemasanInput.value);
    if (kNum > 0) {
        const totalKg = Math.round(kNum * bpk * 100) / 100;
        beratInput.value = `${totalKg} Kg`;
    }
}

function recalcAllTanamanTotals(context) {
    const cfg = getItemContextConfig(context);
    const container = document.getElementById(cfg.containerId);
    const totalInput = document.getElementById(cfg.totalInputId);
    if (!container || !totalInput) return;

    let grandTotal = 0;
    container.querySelectorAll('.hutang-item-row').forEach(row => {
        const kemasanInput = row.querySelector('.hutang-item-kemasan');
        const hargaInput = row.querySelector('.hutang-item-harga');
        const subInput = row.querySelector('.hutang-item-subtotal');

        const kNum = kemasanInput ? parseFirstNum(kemasanInput.value) : 0;
        const hNum = hargaInput ? parseRupiahNum(hargaInput.value) : 0;
        const sub = Math.round(kNum * hNum);
        grandTotal += sub;

        if (subInput) {
            subInput.value = formatRupiahNum(sub);
        }
    });

    const formattedTotal = formatRupiahNum(grandTotal);
    totalInput.value = formattedTotal;

    if (cfg.isHutang) {
        const sisaInput = document.getElementById(`${cfg.mode}-hutang-sisa`);
        const statusSel = document.getElementById(`${cfg.mode}-hutang-status`);
        if (sisaInput) {
            if (statusSel && statusSel.value === 'Lunas') {
                sisaInput.value = 'Rp 0';
            } else if (cfg.mode === 'add' || sisaInput.dataset.syncWithTotal === '1' || sisaInput.value === '' || sisaInput.value === 'Rp 0') {
                sisaInput.value = formattedTotal;
            }
        }
    }
}

function removeTanamanItemRow(btn, context) {
    const { containerId } = getItemContextConfig(context);
    const container = document.getElementById(containerId);
    if (!container) return;
    const rows = container.querySelectorAll('.hutang-item-row');
    if (rows.length <= 1) return;

    const row = btn.closest('.hutang-item-row');
    if (row) {
        row.remove();
        refreshTanamanItemRowLabels(context);
        recalcAllTanamanTotals(context);
    }
}

function addTanamanItemRow(context, itemData = null) {
    const cfg = getItemContextConfig(context);
    const container = document.getElementById(cfg.containerId);
    if (!container) return;

    const row = document.createElement('div');
    row.className = 'hutang-item-row';

    const selectedTanamanId = itemData && itemData.pengelolaan_tanaman_id ? itemData.pengelolaan_tanaman_id : '';
    const kemasanVal = itemData && itemData.jumlah_kemasan ? itemData.jumlah_kemasan : '';
    const beratVal = itemData && itemData.jumlah_berat ? itemData.jumlah_berat : '';
    const hargaVal = itemData && itemData.harga && itemData.harga !== 'Rp 0' ? itemData.harga : '';

    row.innerHTML = `
        <div class="hutang-item-row-head">
            <span class="hutang-item-row-title">Item Tanaman</span>
            <button type="button" class="btn-remove-hutang-item" title="Hapus item tanaman ini">
                <i class="fa-solid fa-trash-can"></i>
                <span>Hapus</span>
            </button>
        </div>
        <div class="modal-form-group mb-2.5">
            <label class="modal-label">Nama Tanaman (Dari Stok Tanaman) <span class="text-rose-500">*</span></label>
            <select name="item_pengelolaan_tanaman_id[]" required class="modal-input hutang-item-tanaman">
                ${buildTanamanOptionsHtml(selectedTanamanId)}
            </select>
        </div>
        <div class="hutang-item-fields-grid">
            <div class="modal-form-group mb-0">
                <label class="modal-label">Jumlah Kemasan (Pcs) <span class="text-rose-500">*</span></label>
                <input type="text" name="item_jumlah_kemasan[]" required class="modal-input hutang-item-kemasan" placeholder="Contoh: 5 Pcs" value="${escapeHtmlStr(kemasanVal)}">
            </div>
            <div class="modal-form-group mb-0">
                <label class="modal-label">Jumlah Berat <span class="text-rose-500">*</span></label>
                <input type="text" name="item_jumlah_berat[]" required class="modal-input hutang-item-berat" placeholder="Contoh: 50 Kg" value="${escapeHtmlStr(beratVal)}">
            </div>
            <div class="modal-form-group mb-0">
                <label class="modal-label">Harga Per Item (Rp) <span class="text-rose-500">*</span></label>
                <input type="text" name="item_harga[]" required class="modal-input font-semibold hutang-item-harga" placeholder="Contoh: Rp 100.000" value="${escapeHtmlStr(hargaVal)}">
            </div>
            <div class="modal-form-group mb-0">
                <label class="modal-label">Subtotal Item</label>
                <input type="text" readonly class="modal-input font-bold text-blue-900 bg-blue-50/50 hutang-item-subtotal" value="Rp 0">
            </div>
        </div>
    `;

    container.appendChild(row);

    const selEl = row.querySelector('.hutang-item-tanaman');
    const kemasanEl = row.querySelector('.hutang-item-kemasan');
    const beratEl = row.querySelector('.hutang-item-berat');
    const hargaEl = row.querySelector('.hutang-item-harga');
    const removeBtn = row.querySelector('.btn-remove-hutang-item');

    const clearRowError = (el) => {
        el.classList.remove('input-error');
        const form = container.closest('form');
        const alertBox = document.getElementById(cfg.alertId);
        if (form && alertBox && !form.querySelector('.modal-input.input-error')) {
            alertBox.classList.add('hidden');
        }
    };

    if (selEl) {
        selEl.addEventListener('change', () => {
            clearRowError(selEl);
            autoCalcBeratItemRow(row);
            recalcAllTanamanTotals(context);
        });
    }
    if (kemasanEl) {
        kemasanEl.addEventListener('input', () => {
            clearRowError(kemasanEl);
            autoCalcBeratItemRow(row);
            recalcAllTanamanTotals(context);
        });
    }
    if (beratEl) {
        beratEl.addEventListener('input', () => clearRowError(beratEl));
    }
    if (hargaEl) {
        hargaEl.addEventListener('input', () => {
            clearRowError(hargaEl);
            formatInputRupiah(hargaEl);
            recalcAllTanamanTotals(context);
        });
    }
    if (removeBtn) {
        removeBtn.addEventListener('click', () => removeTanamanItemRow(removeBtn, context));
    }

    refreshTanamanItemRowLabels(context);
    recalcAllTanamanTotals(context);
}

function addHutangItemRow(mode, itemData = null) {
    addTanamanItemRow(mode, itemData);
}

function openModalTambahHutang() {
    const modal = document.getElementById('modalTambahHutang');
    if (modal) {
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
        }
        const alertBox = document.getElementById('add-hutang-alert');
        if (alertBox) alertBox.classList.add('hidden');

        const container = document.getElementById('add-hutang-items-container');
        if (container) container.innerHTML = '';
        addHutangItemRow('add');

        const tglInput = document.getElementById('add-hutang-tanggal');
        if (tglInput) tglInput.value = new Date().toISOString().split('T')[0];
        const jmlInput = document.getElementById('add-hutang-jumlah');
        if (jmlInput) jmlInput.value = 'Rp 0';
        const sisaInput = document.getElementById('add-hutang-sisa');
        if (sisaInput) sisaInput.value = 'Rp 0';

        modal.classList.add('show');
        setTimeout(() => {
            const sel = document.getElementById('add-hutang-anggota');
            if (sel) sel.focus();
        }, 150);
    }
}

function openModalEditHutang(data) {
    if (!data) return;
    const modal = document.getElementById('modalEditHutang');
    if (modal) {
        const form = modal.querySelector('form');
        if (form) {
            form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
        }
        const alertBox = document.getElementById('edit-hutang-alert');
        if (alertBox) alertBox.classList.add('hidden');
    }

    document.getElementById('edit-hutang-id').value      = data.id;
    document.getElementById('edit-hutang-anggota').value = data.anggota_id || '';
    document.getElementById('edit-hutang-jumlah').value  = data.jumlah_hutang || 'Rp 0';

    const editSisaInput = document.getElementById('edit-hutang-sisa');
    const resolvedSisa  = data.sisa_hutang || (data.status === 'Lunas' ? 'Rp 0' : (data.jumlah_hutang || 'Rp 0'));
    editSisaInput.value = resolvedSisa;
    const jmlNum  = parseRupiahNum(data.jumlah_hutang);
    const sisaNum = parseRupiahNum(resolvedSisa);
    editSisaInput.dataset.syncWithTotal = (data.status !== 'Lunas' && (sisaNum === jmlNum || sisaNum === 0)) ? '1' : '0';

    document.getElementById('edit-hutang-tanggal').value = (data.tanggal && data.tanggal.length >= 10 && data.tanggal !== '0000-00-00')
        ? data.tanggal.substring(0, 10)
        : new Date().toISOString().split('T')[0];
    document.getElementById('edit-hutang-keterangan').value = data.keterangan || '';
    document.getElementById('edit-hutang-status').value     = data.status || 'Belum Lunas';

    const container = document.getElementById('edit-hutang-items-container');
    if (container) {
        container.innerHTML = '';
        const items = (Array.isArray(data.items) && data.items.length > 0)
            ? data.items
            : [{
                pengelolaan_tanaman_id: data.pengelolaan_tanaman_id || '',
                jumlah_kemasan: data.jumlah_kemasan || '',
                jumlah_berat: data.jumlah_berat || '',
                harga: data.harga || '',
            }];
        items.forEach(item => addHutangItemRow('edit', item));
    }

    if (modal) modal.classList.add('show');
}

function validateHutangForm(formId, mode = 'add') {
    const form = document.getElementById(formId);
    if (!form) return true;

    const alertBox = document.getElementById(`${mode}-hutang-alert`);
    const alertText = document.getElementById(`${mode}-hutang-alert-text`);

    form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
    if (alertBox) alertBox.classList.add('hidden');

    const missingFields = [];
    let firstInvalidEl = null;

    function markInvalid(el, label) {
        if (el) el.classList.add('input-error');
        missingFields.push(label);
        if (!firstInvalidEl && el) firstInvalidEl = el;
    }

    function checkElement(el, label, checkMode = false) {
        if (!el) return;
        const val = el.value.trim();
        let isInvalid = false;

        if (val === '') {
            isInvalid = true;
        } else if (checkMode === 'rupiah') {
            if (parseRupiahNum(val) <= 0) isInvalid = true;
        } else if (checkMode) {
            if (parseFirstNum(val) <= 0) isInvalid = true;
        }

        if (isInvalid) markInvalid(el, label);
    }

    const statusVal = document.getElementById(`${mode}-hutang-status`)?.value || 'Belum Lunas';

    checkElement(document.getElementById(`${mode}-hutang-anggota`), 'Nama Petani');

    const rows = form.querySelectorAll(`#${mode}-hutang-items-container .hutang-item-row`);
    if (rows.length === 0) {
        missingFields.push('Minimal 1 Item Tanaman');
    } else {
        rows.forEach((row, idx) => {
            const no = idx + 1;
            checkElement(row.querySelector('.hutang-item-tanaman'), `Item #${no} Nama Tanaman`);
            checkElement(row.querySelector('.hutang-item-kemasan'), `Item #${no} Jumlah Kemasan (> 0)`, true);
            checkElement(row.querySelector('.hutang-item-berat'), `Item #${no} Jumlah Berat (> 0)`, true);
            checkElement(row.querySelector('.hutang-item-harga'), `Item #${no} Harga Satuan (> 0)`, 'rupiah');
        });
    }

    checkElement(document.getElementById(`${mode}-hutang-jumlah`), 'Jumlah Hutang (> 0)', 'rupiah');
    checkElement(document.getElementById(`${mode}-hutang-sisa`), statusVal === 'Lunas' ? 'Sisa Hutang' : 'Sisa Hutang (> 0)', statusVal === 'Lunas' ? false : 'rupiah');
    checkElement(document.getElementById(`${mode}-hutang-tanggal`), 'Tanggal Pinjam');
    checkElement(document.getElementById(`${mode}-hutang-status`), 'Status');

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

function openModalBayarHutang(data) {
    if (!data) return;
    document.getElementById('bayar-id').value = data.id;
    document.getElementById('bayar-petani-display').innerText  = data.nama_petani || '-';
    document.getElementById('bayar-tanaman-display').innerText = `${data.nama_tanaman || '-'} (${data.jumlah_kemasan || ''} / ${data.jumlah_berat || ''})`;

    const jumlahHutang = data.jumlah_hutang || 'Rp 0';
    const sisa = (data.status === 'Lunas') ? 'Rp 0' : (data.sisa_hutang || data.jumlah_hutang || 'Rp 0');

    const jmlHutangEl = document.getElementById('bayar-jumlah-hutang-display');
    if (jmlHutangEl) jmlHutangEl.innerText = jumlahHutang;

    document.getElementById('bayar-tagihan-display').innerText = sisa;
    document.getElementById('bayar-nominal').value             = sisa;
    const metodeEl = document.getElementById('bayar-metode');
    if (metodeEl) metodeEl.value = 'Tunai';
    document.getElementById('bayar-tanggal').value             = new Date().toISOString().split('T')[0];
    document.getElementById('bayar-catatan').value             = 'Pelunasan hutang tanaman diterima bendahara poktan';

    const modal = document.getElementById('modalBayarHutang');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const nomInput = document.getElementById('bayar-nominal');
            if (nomInput) {
                nomInput.focus();
                nomInput.select();
            }
        }, 150);
    }
}

function onStatusHutangChange(mode) {
    const statusSel   = document.getElementById(`${mode}-hutang-status`);
    const jumlahInput = document.getElementById(`${mode}-hutang-jumlah`);
    const sisaInput   = document.getElementById(`${mode}-hutang-sisa`);
    if (!statusSel || !sisaInput) return;

    if (statusSel.value === 'Lunas') {
        sisaInput.value = 'Rp 0';
    } else {
        if (sisaInput.value === 'Rp 0' || sisaInput.value === '') {
            sisaInput.value = (jumlahInput && jumlahInput.value !== '') ? jumlahInput.value : 'Rp 0';
        }
    }
}

function formatInputRupiah(el) {
    let clean = el.value.replace(/[^0-9]/g, '');
    if (!clean) {
        el.value = '';
        return;
    }
    let formatted = new Intl.NumberFormat('id-ID').format(clean);
    el.value = 'Rp ' + formatted;
}

function openModalDeleteHutang(id, namaPetani, namaTanaman, jumlahKemasan) {
    document.getElementById('delete-hutang-id').value = id;
    document.getElementById('delete-hutang-petani').innerText  = namaPetani;
    document.getElementById('delete-hutang-tanaman').innerText = namaTanaman;
    document.getElementById('delete-hutang-kemasan').innerText = jumlahKemasan;

    const modal = document.getElementById('modalDeleteHutang');
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

function filterTanamanTable() {
    const query = (document.getElementById('tanaman-search-input')?.value || '').toLowerCase();
    const rows  = document.querySelectorAll('.tanaman-row-item');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

function applyDateRangeAndFilter(prefix, rowSelector, filterSelectId, tbodyId, emptyRowId, emptyText) {
    const query = (document.getElementById(`${prefix}-search-input`)?.value || '').toLowerCase();
    const selectVal = document.getElementById(filterSelectId)?.value || 'ALL';
    const fromVal = (document.getElementById(`${prefix}-date-from`)?.value || '').trim();
    const toVal = (document.getElementById(`${prefix}-date-to`)?.value || '').trim();
    const rows = document.querySelectorAll(rowSelector);

    let visibleCount = 0;
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowAttr = row.getAttribute('data-status') || row.getAttribute('data-metode') || '';
        const rowDate = row.getAttribute('data-tanggal') || '';
        const matchQuery = text.includes(query);
        const matchFilter = (selectVal === 'ALL' || rowAttr === selectVal);

        let matchDate = true;
        if (fromVal) matchDate = matchDate && (rowDate !== '' && rowDate >= fromVal);
        if (toVal) matchDate = matchDate && (rowDate !== '' && rowDate <= toVal);

        const isShow = matchQuery && matchFilter && matchDate;
        row.style.display = isShow ? '' : 'none';
        if (isShow) visibleCount++;
    });

    let emptyRow = document.getElementById(emptyRowId);
    if (visibleCount === 0 && rows.length > 0) {
        if (!emptyRow) {
            const tbodyEl = document.getElementById(tbodyId);
            const colCount = tbodyEl?.closest('table')?.querySelectorAll('thead th').length || 11;
            emptyRow = document.createElement('tr');
            emptyRow.id = emptyRowId;
            emptyRow.innerHTML = `
                <td colspan="${colCount}" class="table-empty-cell py-6 text-center text-slate-500">
                    <i class="fa-solid fa-magnifying-glass text-2xl mb-2 text-slate-400 block"></i>
                    ${emptyText}
                </td>
            `;
            tbodyEl?.appendChild(emptyRow);
        }
        emptyRow.style.display = '';
    } else if (emptyRow) {
        emptyRow.style.display = 'none';
    }
}

function normalizeDateInputsOrder(fromId, toId) {
    const fromEl = document.getElementById(fromId);
    const toEl = document.getElementById(toId);
    const fromVal = (fromEl?.value || '').trim();
    const toVal = (toEl?.value || '').trim();
    if (fromVal && toVal && fromVal > toVal) {
        if (fromEl) fromEl.value = toVal;
        if (toEl) toEl.value = fromVal;
    }
}

function applyHutangDateFilter() {
    normalizeDateInputsOrder('hutang-date-from', 'hutang-date-to');
    filterHutangTable();
}

function filterHutangTable() {
    applyDateRangeAndFilter(
        'hutang',
        '.hutang-row-item',
        'hutang-status-filter',
        'table-hutang-tbody',
        'table-hutang-empty-filter',
        'Tidak ada data hutang / pelunasan tanaman yang sesuai dengan pencarian atau filter yang dipilih.'
    );
}

function applyPenjualanDateFilter() {
    normalizeDateInputsOrder('penjualan-date-from', 'penjualan-date-to');
    filterPenjualanTable();
}

function filterPenjualanTable() {
    applyDateRangeAndFilter(
        'penjualan',
        '.penjualan-row-item',
        'penjualan-metode-filter',
        'table-penjualan-tbody',
        'table-penjualan-empty-filter',
        'Tidak ada data atau riwayat penjualan tanaman yang sesuai dengan pencarian atau filter yang dipilih.'
    );
}

function applyRiwayatStokDateFilter() {
    normalizeDateInputsOrder('riwayat-stok-date-from', 'riwayat-stok-date-to');
    filterRiwayatStokTable();
}

function filterRiwayatStokTable() {
    applyDateRangeAndFilter(
        'riwayat-stok',
        '.riwayat-stok-row-item',
        'riwayat-stok-sumber-filter',
        'table-riwayat-stok-tbody',
        'table-riwayat-stok-empty-filter',
        'Tidak ada riwayat pengelolaan stok tanaman yang sesuai dengan pencarian atau filter yang dipilih.'
    );
}

function togglePenjualanAlamatField(mode) {
    const sel = document.getElementById(`${mode}-penjualan-anggota`);
    const wrap = document.getElementById(`${mode}-penjualan-alamat-wrap`);
    if (!sel || !wrap) return;
    const isNonAnggota = (sel.value || '').trim() === '';
    wrap.classList.toggle('hidden', !isNonAnggota);
}

function onPenjualanAnggotaChange(mode) {
    const sel = document.getElementById(`${mode}-penjualan-anggota`);
    const inp = document.getElementById(`${mode}-penjualan-pembeli`);
    const alamatInp = document.getElementById(`${mode}-penjualan-alamat`);
    if (!sel || !inp) return;
    const opt = sel.options[sel.selectedIndex];
    const nama = opt ? (opt.getAttribute('data-nama') || '').trim() : '';
    const alamat = opt ? (opt.getAttribute('data-alamat') || '').trim() : '';
    if (nama !== '') {
        inp.value = nama;
        inp.classList.remove('input-error');
        if (alamatInp) {
            alamatInp.value = alamat;
            alamatInp.dataset.fromAnggota = '1';
        }
    } else if (alamatInp && alamatInp.dataset.fromAnggota === '1') {
        alamatInp.value = '';
        alamatInp.dataset.fromAnggota = '0';
    }
    togglePenjualanAlamatField(mode);
}

function openModalTambahPenjualan() {
    const modal = document.getElementById('modalTambahPenjualan');
    if (!modal) return;
    const form = modal.querySelector('form');
    if (form) {
        form.reset();
        form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
    }
    const alertBox = document.getElementById('add-penjualan-alert');
    if (alertBox) alertBox.classList.add('hidden');

    const container = document.getElementById('add-penjualan-items-container');
    if (container) container.innerHTML = '';
    addTanamanItemRow('add-penjualan');

    const tglInput = document.getElementById('add-penjualan-tanggal');
    if (tglInput) tglInput.value = new Date().toISOString().split('T')[0];
    const totalInput = document.getElementById('add-penjualan-total');
    if (totalInput) totalInput.value = 'Rp 0';
    togglePenjualanAlamatField('add');

    modal.classList.add('show');
    setTimeout(() => {
        const pembeliInput = document.getElementById('add-penjualan-pembeli');
        if (pembeliInput) pembeliInput.focus();
    }, 150);
}

function openModalEditPenjualan(data) {
    if (!data) return;
    const modal = document.getElementById('modalEditPenjualan');
    if (modal) {
        const form = modal.querySelector('form');
        if (form) {
            form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
        }
        const alertBox = document.getElementById('edit-penjualan-alert');
        if (alertBox) alertBox.classList.add('hidden');
    }

    document.getElementById('edit-penjualan-id').value = data.id;
    document.getElementById('edit-penjualan-anggota').value = data.anggota_id || '';
    document.getElementById('edit-penjualan-pembeli').value = data.nama_pembeli || '';
    const editAlamatEl = document.getElementById('edit-penjualan-alamat');
    if (editAlamatEl) {
        editAlamatEl.value = (data.alamat_pembeli && data.alamat_pembeli !== '-') ? data.alamat_pembeli : '';
    }
    togglePenjualanAlamatField('edit');
    document.getElementById('edit-penjualan-metode').value = data.metode_bayar || 'Tunai';
    document.getElementById('edit-penjualan-tanggal').value = (data.tanggal_jual && data.tanggal_jual.length >= 10 && data.tanggal_jual !== '0000-00-00')
        ? data.tanggal_jual.substring(0, 10)
        : new Date().toISOString().split('T')[0];
    document.getElementById('edit-penjualan-keterangan').value = data.keterangan || '';

    const container = document.getElementById('edit-penjualan-items-container');
    if (container) {
        container.innerHTML = '';
        const items = (Array.isArray(data.items) && data.items.length > 0) ? data.items : [{}];
        items.forEach(item => addTanamanItemRow('edit-penjualan', item));
    }

    if (modal) modal.classList.add('show');
}

function openModalDeletePenjualan(id, kode, pembeli, kemasan) {
    document.getElementById('delete-penjualan-id').value = id;
    document.getElementById('delete-penjualan-kode').innerText = kode;
    document.getElementById('delete-penjualan-pembeli').innerText = pembeli;
    document.getElementById('delete-penjualan-kemasan').innerText = kemasan;

    const modal = document.getElementById('modalDeletePenjualan');
    if (modal) modal.classList.add('show');
}

function openModalInfoPenjualan(penjualanId) {
    if (typeof PENJUALAN_TANAMAN_INFO_MAP === 'undefined') return;
    const j = PENJUALAN_TANAMAN_INFO_MAP[penjualanId];
    if (!j) return;

    const namaPembeli = j.nama_pembeli || '-';
    const nParts = namaPembeli.trim().split(/\s+/);
    const initials = (nParts.length >= 2)
        ? (nParts[0].charAt(0) + nParts[1].charAt(0)).toUpperCase()
        : namaPembeli.substring(0, 2).toUpperCase();

    document.getElementById('info-penjualan-avatar').innerText = initials;
    document.getElementById('info-penjualan-nama-pembeli').innerText = namaPembeli;
    const subEl = document.getElementById('info-penjualan-sub-pembeli');
    if (subEl) {
        subEl.innerText = `Alamat: ${j.alamat_pembeli || '-'}`;
    }

    const kodeWrap = document.getElementById('info-penjualan-kode-wrap');
    if (kodeWrap) {
        kodeWrap.innerHTML = `<span class="badge-kode-transaksi">${escapeHtmlStr(j.kode_transaksi || '-')}</span>`;
    }

    document.getElementById('info-penjualan-tgl').innerText = formatDateID(j.tanggal_jual || j.created_at);
    document.getElementById('info-penjualan-metode').innerText = j.metode_bayar || 'Tunai';
    document.getElementById('info-penjualan-petugas').innerText = j.dicatat_oleh || '-';
    document.getElementById('info-penjualan-kuantitas').innerText = j.jumlah_kemasan || '0 Pcs';
    document.getElementById('info-penjualan-berat').innerText = j.jumlah_berat || '0 Kg';
    document.getElementById('info-penjualan-total').innerText = j.total_harga_str || 'Rp 0';
    document.getElementById('info-penjualan-keterangan').innerText = j.keterangan || '-';

    const itemsList = Array.isArray(j.items) ? j.items : [];
    const badgeEl = document.getElementById('info-penjualan-items-badge');
    if (badgeEl) badgeEl.innerText = `${itemsList.length} Item`;

    const tbody = document.getElementById('info-penjualan-items-tbody');
    if (tbody) {
        if (itemsList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="info-riwayat-empty">Tidak ada rincian item tanaman.</td></tr>';
        } else {
            tbody.innerHTML = itemsList.map((it, idx) => `
                <tr>
                    <td class="font-semibold text-slate-500">${idx + 1}</td>
                    <td><span class="badge-nama-tanaman"><i class="fa-solid fa-wheat-awn"></i> ${escapeHtmlStr(it.nama_tanaman || '-')}</span></td>
                    <td><span class="badge-kemasan-tanaman">${escapeHtmlStr(it.jumlah_kemasan || '0 Pcs')}</span></td>
                    <td><span class="nik-badge">${escapeHtmlStr(it.jumlah_berat || '0 Kg')}</span></td>
                    <td class="font-semibold text-emerald-700">${escapeHtmlStr(it.harga || 'Rp 0')}</td>
                    <td class="font-bold text-blue-800">${escapeHtmlStr(it.subtotal || 'Rp 0')}</td>
                </tr>
            `).join('');
        }
    }

    const printBtn = document.getElementById('btn-info-print-penjualan');
    if (printBtn) {
        const basePrintUrl = (typeof TANAMAN_PRINT_JUAL_URL !== 'undefined') ? TANAMAN_PRINT_JUAL_URL : 'print-penjualan.php';
        printBtn.href = `${basePrintUrl}?id=${encodeURIComponent(penjualanId)}`;
    }

    const modal = document.getElementById('modalInfoPenjualan');
    if (modal) modal.classList.add('show');
}

function validatePenjualanForm(formId, mode = 'add') {
    const form = document.getElementById(formId);
    if (!form) return true;

    const alertBox = document.getElementById(`${mode}-penjualan-alert`);
    const alertText = document.getElementById(`${mode}-penjualan-alert-text`);

    form.querySelectorAll('.modal-input').forEach(el => el.classList.remove('input-error'));
    if (alertBox) alertBox.classList.add('hidden');

    const missingFields = [];
    let firstInvalidEl = null;

    function checkEl(el, label, checkMode = false) {
        if (!el) return;
        const val = el.value.trim();
        let isInvalid = false;
        if (val === '') {
            isInvalid = true;
        } else if (checkMode === 'rupiah') {
            if (parseRupiahNum(val) <= 0) isInvalid = true;
        } else if (checkMode) {
            if (parseFirstNum(val) <= 0) isInvalid = true;
        }
        if (isInvalid) {
            el.classList.add('input-error');
            missingFields.push(label);
            if (!firstInvalidEl) firstInvalidEl = el;
        }
    }

    checkEl(document.getElementById(`${mode}-penjualan-pembeli`), 'Nama Pembeli');

    const rows = form.querySelectorAll(`#${mode}-penjualan-items-container .hutang-item-row`);
    if (rows.length === 0) {
        missingFields.push('Minimal 1 Item Tanaman');
    } else {
        rows.forEach((row, idx) => {
            const no = idx + 1;
            checkEl(row.querySelector('.hutang-item-tanaman'), `Item #${no} Nama Tanaman`);
            checkEl(row.querySelector('.hutang-item-kemasan'), `Item #${no} Jumlah Kemasan (> 0)`, true);
            checkEl(row.querySelector('.hutang-item-berat'), `Item #${no} Jumlah Berat (> 0)`, true);
            checkEl(row.querySelector('.hutang-item-harga'), `Item #${no} Harga Per Item (> 0)`, 'rupiah');
        });
    }

    checkEl(document.getElementById(`${mode}-penjualan-total`), 'Total Penjualan (> 0)', 'rupiah');
    checkEl(document.getElementById(`${mode}-penjualan-metode`), 'Metode Pembayaran');
    checkEl(document.getElementById(`${mode}-penjualan-tanggal`), 'Tanggal Penjualan');

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

function escapeHtmlStr(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatDateID(dateStr) {
    if (!dateStr || dateStr === '0000-00-00') return '-';
    const clean = String(dateStr).substring(0, 10);
    const parts = clean.split('-');
    if (parts.length !== 3) return clean;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

function openModalInfoHutang(hutangId, highlightBayarId = 0) {
    if (typeof HUTANG_TANAMAN_INFO_MAP === 'undefined') return;
    const h = HUTANG_TANAMAN_INFO_MAP[hutangId];
    if (!h) return;

    const historyList = (typeof PEMBAYARAN_TANAMAN_BY_HUTANG !== 'undefined' && PEMBAYARAN_TANAMAN_BY_HUTANG[hutangId] || []).slice().sort((a, b) => {
        return (parseInt(a.pembayaran_ke, 10) || 0) - (parseInt(b.pembayaran_ke, 10) || 0);
    });

    const namaPetani = h.nama_petani || '-';
    const nParts = namaPetani.trim().split(/\s+/);
    const initials = (nParts.length >= 2)
        ? (nParts[0].charAt(0) + nParts[1].charAt(0)).toUpperCase()
        : namaPetani.substring(0, 2).toUpperCase();

    document.getElementById('info-hutang-avatar').innerText = initials;
    document.getElementById('info-hutang-nama-petani').innerText = namaPetani;
    const subPetaniEl = document.getElementById('info-hutang-sub-petani');
    if (subPetaniEl) {
        if (h.alamat_petani) {
            subPetaniEl.innerText = h.alamat_petani;
            subPetaniEl.style.display = '';
        } else {
            subPetaniEl.innerText = '';
            subPetaniEl.style.display = 'none';
        }
    }

    const isLunas = (h.status === 'Lunas');
    const statusWrap = document.getElementById('info-hutang-status-wrap');
    if (statusWrap) {
        statusWrap.innerHTML = isLunas
            ? `<span class="badge-status-hutang badge-status-lunas"><i class="fa-solid fa-circle-check"></i> Lunas</span>`
            : `<span class="badge-status-hutang badge-status-belum"><i class="fa-solid fa-clock"></i> Belum Lunas</span>`;
    }

    document.getElementById('info-hutang-tgl-pinjam').innerText = formatDateID(h.tanggal || h.created_at);
    document.getElementById('info-hutang-tgl-lunas').innerText = isLunas ? formatDateID(h.tanggal_lunas || h.tanggal) : 'Belum Lunas';

    const rawKet = (h.keterangan || '-').split(' | Bayar:')[0].trim();
    document.getElementById('info-hutang-keterangan').innerText = rawKet || '-';

    const itemsList = (Array.isArray(h.items) && h.items.length > 0)
        ? h.items
        : [{
            nama_tanaman: h.nama_tanaman || '-',
            jumlah_kemasan: h.jumlah_kemasan || '0 Pcs',
            jumlah_berat: h.jumlah_berat || '0 Kg',
            harga: h.harga || 'Rp 0',
            subtotal: h.jumlah_hutang || 'Rp 0',
        }];

    const itemsBadgeEl = document.getElementById('info-items-badge');
    if (itemsBadgeEl) itemsBadgeEl.innerText = `${itemsList.length} Item`;

    const itemsTbody = document.getElementById('info-items-tbody');
    if (itemsTbody) {
        itemsTbody.innerHTML = itemsList.map((it, idx) => `
            <tr>
                <td class="font-semibold text-slate-500">${idx + 1}</td>
                <td><span class="badge-nama-tanaman"><i class="fa-solid fa-wheat-awn"></i> ${escapeHtmlStr(it.nama_tanaman || '-')}</span></td>
                <td><span class="badge-kemasan-tanaman">${escapeHtmlStr(it.jumlah_kemasan || '0 Pcs')}</span></td>
                <td><span class="nik-badge">${escapeHtmlStr(it.jumlah_berat || '0 Kg')}</span></td>
                <td class="font-semibold text-emerald-700">${escapeHtmlStr(it.harga || 'Rp 0')}</td>
                <td class="font-bold text-blue-800">${escapeHtmlStr(it.subtotal || 'Rp 0')}</td>
            </tr>
        `).join('');
    }

    let sumBayar = 0;
    historyList.forEach(item => {
        sumBayar += parseInt(item.jumlah_bayar, 10) || 0;
    });
    const sumBayarStr = 'Rp ' + new Intl.NumberFormat('id-ID').format(sumBayar);

    document.getElementById('info-hutang-total').innerText = h.jumlah_hutang || 'Rp 0';
    document.getElementById('info-hutang-terbayar').innerText = sumBayarStr;
    document.getElementById('info-hutang-kali-bayar').innerText = `${historyList.length} kali pembayaran`;
    document.getElementById('info-hutang-sisa').innerText = isLunas ? 'Rp 0' : (h.sisa_hutang || 'Rp 0');

    const badgeEl = document.getElementById('info-riwayat-badge');
    if (badgeEl) badgeEl.innerText = `${historyList.length} Transaksi`;

    const printBaseUrl = (typeof TANAMAN_PRINT_BAYAR_URL !== 'undefined') ? TANAMAN_PRINT_BAYAR_URL : 'print-pembayaran.php';
    const tbody = document.getElementById('info-riwayat-tbody');
    if (tbody) {
        if (historyList.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="info-riwayat-empty">
                        <i class="fa-solid fa-clock-rotate-left mr-1.5"></i>
                        Belum ada riwayat pembayaran yang tercatat untuk hutang petani ini.
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = historyList.map(item => {
                const bayarId = parseInt(item.id, 10) || 0;
                const isHighlighted = highlightBayarId > 0 && bayarId === parseInt(highlightBayarId, 10);
                const itemLunas = item.status === 'Lunas';
                const rowClass = isHighlighted ? 'info-riwayat-row-highlight' : '';
                const stBadge = itemLunas
                    ? `<span class="badge-status-hutang badge-status-lunas"><i class="fa-solid fa-circle-check"></i> Lunas</span>`
                    : `<span class="badge-status-hutang badge-status-belum"><i class="fa-solid fa-clock"></i> Belum Lunas</span>`;
                const ketText = escapeHtmlStr(item.keterangan || '-');
                const olehText = item.dicatat_oleh && item.dicatat_oleh !== '-'
                    ? `<span class="info-dicatat-oleh">Dicatat: ${escapeHtmlStr(item.dicatat_oleh)}</span>`
                    : '';

                return `
                    <tr class="${rowClass}">
                        <td><span class="badge-tahap-bayar">Ke-${parseInt(item.pembayaran_ke, 10) || 1}</span></td>
                        <td>${escapeHtmlStr(formatDateID(item.tanggal_bayar))}</td>
                        <td class="text-slate-600 font-semibold">${escapeHtmlStr(item.sisa_sebelum_str || 'Rp 0')}</td>
                        <td><span class="badge-jumlah-bayar">${escapeHtmlStr(item.jumlah_bayar_str || 'Rp 0')}</span></td>
                        <td class="${(parseInt(item.sisa_hutang, 10) || 0) <= 0 ? 'text-emerald-700 font-bold' : 'text-rose-700 font-bold'}">${escapeHtmlStr(item.sisa_hutang_str || 'Rp 0')}</td>
                        <td><span class="badge-metode-bayar">${escapeHtmlStr(item.metode_bayar || 'Tunai')}</span></td>
                        <td>${stBadge}</td>
                        <td>
                            <div class="info-ket-cell">${ketText}${olehText}</div>
                        </td>
                    </tr>
                `;
            }).join('');
        }
    }

    const btnPrintBukti = document.getElementById('btn-info-print-bukti');
    if (btnPrintBukti) {
        if (highlightBayarId > 0) {
            btnPrintBukti.href = `${printBaseUrl}?id=${encodeURIComponent(highlightBayarId)}`;
            btnPrintBukti.style.display = 'inline-flex';
        } else {
            btnPrintBukti.href = '#';
            btnPrintBukti.style.display = 'none';
        }
    }

    const btnBayarNow = document.getElementById('btn-info-bayar-sekarang');
    if (btnBayarNow) {
        if (!isLunas) {
            btnBayarNow.style.display = 'inline-flex';
            btnBayarNow.onclick = function () {
                closeModal('modalInfoHutang');
                openModalBayarHutang(h);
            };
        } else {
            btnBayarNow.style.display = 'none';
            btnBayarNow.onclick = null;
        }
    }

    const modal = document.getElementById('modalInfoHutang');
    if (modal) modal.classList.add('show');
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
    // Inisialisasi validasi interaktif & notifikasi untuk Tambah dan Edit Hutang Tanaman
    ['add', 'edit'].forEach(mode => {
        const formId = mode === 'add' ? 'formTambahHutang' : 'formEditHutang';
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validateHutangForm(formId, mode)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            form.querySelectorAll('.modal-input').forEach(input => {
                const clearErr = () => {
                    input.classList.remove('input-error');
                    const alertBox = document.getElementById(`${mode}-hutang-alert`);
                    if (alertBox && !form.querySelector('.modal-input.input-error')) {
                        alertBox.classList.add('hidden');
                    }
                };
                input.addEventListener('input', clearErr);
                input.addEventListener('change', clearErr);
            });
        }
    });

    // Inisialisasi validasi interaktif & notifikasi untuk Tambah dan Edit Penjualan Tanaman
    ['add', 'edit'].forEach(mode => {
        const formId = mode === 'add' ? 'formTambahPenjualan' : 'formEditPenjualan';
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validatePenjualanForm(formId, mode)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            form.querySelectorAll('.modal-input').forEach(input => {
                const clearErr = () => {
                    input.classList.remove('input-error');
                    const alertBox = document.getElementById(`${mode}-penjualan-alert`);
                    if (alertBox && !form.querySelector('.modal-input.input-error')) {
                        alertBox.classList.add('hidden');
                    }
                };
                input.addEventListener('input', clearErr);
                input.addEventListener('change', clearErr);
            });
        }
    });

    const editSisaEl = document.getElementById('edit-hutang-sisa');
    if (editSisaEl) {
        editSisaEl.addEventListener('input', () => {
            editSisaEl.dataset.syncWithTotal = '0';
        });
    }

    const dateFromEl = document.getElementById('hutang-date-from');
    const dateToEl   = document.getElementById('hutang-date-to');
    if (dateFromEl && dateToEl) {
        const checkClearDateFilter = () => {
            if (!dateFromEl.value && !dateToEl.value) {
                filterHutangTable();
            }
        };
        dateFromEl.addEventListener('change', checkClearDateFilter);
        dateToEl.addEventListener('change', checkClearDateFilter);
    }

    const pDateFromEl = document.getElementById('penjualan-date-from');
    const pDateToEl   = document.getElementById('penjualan-date-to');
    if (pDateFromEl && pDateToEl) {
        const checkClearPenjualanDate = () => {
            if (!pDateFromEl.value && !pDateToEl.value) {
                filterPenjualanTable();
            }
        };
        pDateFromEl.addEventListener('change', checkClearPenjualanDate);
        pDateToEl.addEventListener('change', checkClearPenjualanDate);
    }

    const rsDateFromEl = document.getElementById('riwayat-stok-date-from');
    const rsDateToEl   = document.getElementById('riwayat-stok-date-to');
    if (rsDateFromEl && rsDateToEl) {
        const checkClearRiwayatStokDate = () => {
            if (!rsDateFromEl.value && !rsDateToEl.value) {
                filterRiwayatStokTable();
            }
        };
        rsDateFromEl.addEventListener('change', checkClearRiwayatStokDate);
        rsDateToEl.addEventListener('change', checkClearRiwayatStokDate);
    }

    // Tampilkan toast notifikasi dari konfigurasi PHP (views/toast-config.php)
    if (window.TANAMAN_TOAST) {
        showToast(
            window.TANAMAN_TOAST.title,
            window.TANAMAN_TOAST.message,
            window.TANAMAN_TOAST.type,
            window.TANAMAN_TOAST.duration || 1400
        );

        if (window.history.replaceState) {
            const viewParam = window.TANAMAN_TOAST.viewParam || '';
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + viewParam;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }
    }
});
