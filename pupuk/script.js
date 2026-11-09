/**
 * mudatani99 - Inventaris Stok Pupuk, Hutang Piutang & Riwayat JS
 * File: pupuk/script.js
 */

function openModalTambahPupuk() {
    const modal = document.getElementById('modalTambahPupuk');
    if (modal) {
        const form = document.getElementById('formTambahStokPupuk') || modal.querySelector('form');
        if (form) form.reset();

        const alertBox = document.getElementById('add-stock-status-alert');
        if (alertBox) {
            alertBox.className = 'hidden mb-3 p-3 rounded-xl border text-xs flex items-start gap-2.5';
            alertBox.innerHTML = '';
        }

        const btnText = document.getElementById('btn-save-tambah-pupuk-text');
        if (btnText) btnText.innerText = 'Simpan Stok Pupuk';

        modal.classList.add('show');
        setTimeout(() => {
            const sel = document.getElementById('add-pupuk-id');
            if (sel) sel.focus();
        }, 150);
    }
}

function onSelectPupukTambahStok() {
    const sel = document.getElementById('add-pupuk-id');
    const alertBox = document.getElementById('add-stock-status-alert');
    const btnText = document.getElementById('btn-save-tambah-pupuk-text');
    if (!sel) return;

    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        if (alertBox) {
            alertBox.className = 'hidden mb-3 p-3 rounded-xl border text-xs flex items-start gap-2.5';
            alertBox.innerHTML = '';
        }
        if (btnText) btnText.innerText = 'Simpan Stok Pupuk';
        return;
    }

    const hasStock  = opt.getAttribute('data-has-stock') === '1';
    const curKarung = opt.getAttribute('data-cur-karung') || '0';
    const curBerat  = opt.getAttribute('data-cur-berat') || '0 Kg';
    const nama      = opt.getAttribute('data-nama') || opt.text;

    if (alertBox) {
        if (hasStock) {
            alertBox.className = 'mb-3 p-3 rounded-xl border border-blue-200 bg-blue-50/90 text-blue-900 text-xs flex items-start gap-2.5 shadow-sm';
            alertBox.innerHTML = `
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </div>
                <div>
                    <strong class="block text-blue-950 font-semibold mb-0.5">Produk Sudah Ada di Tabel Stok (Akumulasi Stok)</strong>
                    <span>Produk <b>${nama}</b> saat ini memiliki stok: <b>${curKarung} Karung (${curBerat})</b>. Jumlah karung &amp; berat yang Anda masukkan di bawah akan <b>langsung dijumlahkan ke baris stok tersebut</b> dan tidak membuat list nomor baru.</span>
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
                    <span>Produk <b>${nama}</b> belum ada di daftar stok. Penambahan ini akan didaftarkan sebagai <b>baris baru (nomor baru)</b> di tabel stok pupuk.</span>
                </div>
            `;
            if (btnText) btnText.innerText = 'Buat List Stok Baru';
        }
    }

    autoCalcBeratTambahStok();
}

function autoCalcBeratTambahStok() {
    const sel = document.getElementById('add-pupuk-id');
    const karungInput = document.getElementById('add-jumlah-karung');
    const beratInput  = document.getElementById('add-jumlah-berat');
    if (!sel || !karungInput || !beratInput) return;

    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;

    const bpk = parseFloat(opt.getAttribute('data-bpk') || '50');
    const rawKarung = karungInput.value.trim();
    const match = rawKarung.match(/([0-9]+([.,][0-9]+)?)/);
    if (match) {
        const kNum = parseFloat(match[1].replace(',', '.'));
        if (!isNaN(kNum) && kNum > 0) {
            const totalKg = Math.round(kNum * bpk * 100) / 100;
            beratInput.value = `${totalKg} Kg`;
        }
    }
}

function openModalEditPupuk(data) {
    if (!data) return;
    document.getElementById('edit-id').value            = data.id;
    document.getElementById('edit-jumlah-karung').value = data.jumlah_karung;
    document.getElementById('edit-jumlah-berat').value  = data.jumlah_berat;
    document.getElementById('edit-keterangan').value    = data.keterangan;

    const pupukSelect = document.getElementById('edit-pupuk-id');
    if (pupukSelect) {
        if (data.pupuk_id) {
            pupukSelect.value = data.pupuk_id;
        } else {
            pupukSelect.value = '';
            for (let i = 0; i < pupukSelect.options.length; i++) {
                if (pupukSelect.options[i].text.trim().toLowerCase() === (data.nama_pupuk || '').trim().toLowerCase()) {
                    pupukSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    const modal = document.getElementById('modalEditPupuk');
    if (modal) modal.classList.add('show');
}

function openModalDeletePupuk(id, nama, karung) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;
    document.getElementById('delete-karung-display').innerText = karung;

    const modal = document.getElementById('modalDeletePupuk');
    if (modal) modal.classList.add('show');
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

        const tglInput = document.getElementById('add-hutang-tanggal');
        if (tglInput) tglInput.value = new Date().toISOString().split('T')[0];
        const hrgInput = document.getElementById('add-hutang-harga');
        if (hrgInput) hrgInput.value = '';
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

    document.getElementById('edit-hutang-id').value         = data.id;
    document.getElementById('edit-hutang-anggota').value    = data.anggota_id || '';
    document.getElementById('edit-hutang-karung').value     = data.jumlah_karung || '';
    document.getElementById('edit-hutang-berat').value      = data.jumlah_berat || '';
    document.getElementById('edit-hutang-harga').value      = data.harga || 'Rp 0';
    document.getElementById('edit-hutang-jumlah').value     = data.jumlah_hutang || 'Rp 0';
    document.getElementById('edit-hutang-sisa').value       = data.sisa_hutang || (data.status === 'Lunas' ? 'Rp 0' : (data.jumlah_hutang || 'Rp 0'));
    document.getElementById('edit-hutang-tanggal').value    = (data.tanggal && data.tanggal.length >= 10 && data.tanggal !== '0000-00-00')
        ? data.tanggal.substring(0, 10)
        : new Date().toISOString().split('T')[0];
    document.getElementById('edit-hutang-keterangan').value = data.keterangan || '';
    document.getElementById('edit-hutang-status').value     = data.status || 'Belum Lunas';

    const pupukSel = document.getElementById('edit-hutang-pupuk');
    if (pupukSel) {
        if (data.pengelolaan_pupuk_id) {
            pupukSel.value = data.pengelolaan_pupuk_id;
        } else {
            pupukSel.value = '';
        }
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

    function checkField(id, label, checkNumeric = false) {
        const el = document.getElementById(id);
        if (!el) return;
        const val = el.value.trim();
        let isInvalid = false;

        if (val === '') {
            isInvalid = true;
        } else if (checkNumeric) {
            const num = parseFloat(val.replace(/[^0-9.,]/g, '').replace(',', '.'));
            if (isNaN(num) || num <= 0) {
                isInvalid = true;
            }
        }

        if (isInvalid) {
            el.classList.add('input-error');
            missingFields.push(label);
            if (!firstInvalidEl) firstInvalidEl = el;
        }
    }

    checkField(`${mode}-hutang-anggota`, 'Nama Petani');
    checkField(`${mode}-hutang-pupuk`, 'Nama Pupuk');
    checkField(`${mode}-hutang-karung`, 'Jumlah Karung (> 0)', true);
    checkField(`${mode}-hutang-berat`, 'Jumlah Berat (> 0)', true);
    checkField(`${mode}-hutang-harga`, 'Harga Satuan');
    checkField(`${mode}-hutang-jumlah`, 'Jumlah Hutang');
    checkField(`${mode}-hutang-tanggal`, 'Tanggal Pinjam');
    checkField(`${mode}-hutang-status`, 'Status');

    if (missingFields.length > 0) {
        if (alertBox && alertText) {
            alertText.innerHTML = `<strong>Peringatan!</strong> Kolom berikut belum diisi dengan benar: <span class="font-semibold text-rose-900 underline">${missingFields.join(', ')}</span>. Harap lengkapi sebelum menyimpan.`;
            alertBox.classList.remove('hidden');
        }

        showToast(
            'Kolom Belum Lengkap!',
            `Terdapat ${missingFields.length} kolom wajib yang belum diisi: ${missingFields.join(', ')}.`,
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
    document.getElementById('bayar-petani-display').innerText = data.nama_petani || '-';
    document.getElementById('bayar-pupuk-display').innerText  = `${data.nama_pupuk || '-'} (${data.jumlah_karung || ''} / ${data.jumlah_berat || ''})`;
    
    const jumlahHutang = data.jumlah_hutang || 'Rp 0';
    const sisa = (data.status === 'Lunas') ? 'Rp 0' : (data.sisa_hutang || data.jumlah_hutang || 'Rp 0');

    const jmlHutangEl = document.getElementById('bayar-jumlah-hutang-display');
    if (jmlHutangEl) jmlHutangEl.innerText = jumlahHutang;

    document.getElementById('bayar-tagihan-display').innerText = sisa;
    document.getElementById('bayar-nominal').value             = sisa;
    const metodeEl = document.getElementById('bayar-metode');
    if (metodeEl) metodeEl.value = 'Tunai';
    document.getElementById('bayar-tanggal').value             = new Date().toISOString().split('T')[0];
    document.getElementById('bayar-catatan').value             = 'Pelunasan hutang pupuk diterima bendahara poktan';

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

function autoCalcTotalHutang(mode) {
    const karungInput = document.getElementById(`${mode}-hutang-karung`);
    const hargaInput  = document.getElementById(`${mode}-hutang-harga`);
    const jumlahInput = document.getElementById(`${mode}-hutang-jumlah`);
    const sisaInput   = document.getElementById(`${mode}-hutang-sisa`);
    const statusSel   = document.getElementById(`${mode}-hutang-status`);
    if (!karungInput || !hargaInput || !jumlahInput) return;

    const rawKarung = karungInput.value.trim();
    const match = rawKarung.match(/([0-9]+([.,][0-9]+)?)/);
    const kNum = match ? parseFloat(match[1].replace(',', '.')) : 0;

    const rawHarga = hargaInput.value.replace(/[^0-9]/g, '');
    const hNum = rawHarga ? parseFloat(rawHarga) : 0;

    const total = Math.round(kNum * hNum);
    const formattedTotal = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    jumlahInput.value = formattedTotal;

    if (sisaInput) {
        if (statusSel && statusSel.value === 'Lunas') {
            sisaInput.value = 'Rp 0';
        } else if (mode === 'add' || sisaInput.value === '' || sisaInput.value === 'Rp 0') {
            sisaInput.value = formattedTotal;
        }
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

function openModalDeleteHutang(id, namaPetani, namaPupuk, jumlahKarung) {
    document.getElementById('delete-hutang-id').value = id;
    document.getElementById('delete-hutang-petani').innerText = namaPetani;
    document.getElementById('delete-hutang-pupuk').innerText  = namaPupuk;
    document.getElementById('delete-hutang-karung').innerText = jumlahKarung;

    const modal = document.getElementById('modalDeleteHutang');
    if (modal) modal.classList.add('show');
}

function onPupukHutangChange(mode) {
    autoCalcBeratHutang(mode);
}

function autoCalcBeratHutang(mode) {
    const sel = document.getElementById(`${mode}-hutang-pupuk`);
    const karungInput = document.getElementById(`${mode}-hutang-karung`);
    const beratInput  = document.getElementById(`${mode}-hutang-berat`);
    if (!sel || !karungInput || !beratInput) return;

    const selectedOpt = sel.options[sel.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) return;

    const bpk = parseFloat(selectedOpt.getAttribute('data-bpk') || '50');
    const rawKarung = karungInput.value.trim();
    const match = rawKarung.match(/([0-9]+([.,][0-9]+)?)/);
    if (match) {
        const kNum = parseFloat(match[1].replace(',', '.'));
        if (!isNaN(kNum) && kNum > 0) {
            const totalKg = Math.round(kNum * bpk * 100) / 100;
            beratInput.value = `${totalKg} Kg`;
        }
    }
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

function filterPupukTable() {
    const query = (document.getElementById('pupuk-search-input')?.value || '').toLowerCase();
    const rows  = document.querySelectorAll('.pupuk-row-item');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

function filterHutangTable() {
    const query        = (document.getElementById('hutang-search-input')?.value || '').toLowerCase();
    const statusFilter = document.getElementById('hutang-status-filter')?.value || 'ALL';
    const rows         = document.querySelectorAll('.hutang-row-item');

    let visibleCount = 0;
    rows.forEach(row => {
        const text        = row.innerText.toLowerCase();
        const rowStatus   = row.getAttribute('data-status') || '';
        const matchQuery  = text.includes(query);
        const matchStatus = (statusFilter === 'ALL' || rowStatus === statusFilter);
        const isShow      = matchQuery && matchStatus;
        row.style.display = isShow ? '' : 'none';
        if (isShow) visibleCount++;
    });

    let emptyRow = document.getElementById('table-hutang-empty-filter');
    if (visibleCount === 0 && rows.length > 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'table-hutang-empty-filter';
            emptyRow.innerHTML = `
                <td colspan="12" class="table-empty-cell py-6 text-center text-slate-500">
                    <i class="fa-solid fa-magnifying-glass text-2xl mb-2 text-slate-400 block"></i>
                    Tidak ada data hutang / pelunasan pupuk yang sesuai dengan pencarian atau filter status.
                </td>
            `;
            document.getElementById('table-hutang-tbody')?.appendChild(emptyRow);
        }
        emptyRow.style.display = '';
    } else if (emptyRow) {
        emptyRow.style.display = 'none';
    }
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
    if (typeof HUTANG_INFO_MAP === 'undefined') return;
    const h = HUTANG_INFO_MAP[hutangId];
    if (!h) return;

    const historyList = (typeof PEMBAYARAN_BY_HUTANG !== 'undefined' && PEMBAYARAN_BY_HUTANG[hutangId] || []).slice().sort((a, b) => {
        return (parseInt(a.pembayaran_ke, 10) || 0) - (parseInt(b.pembayaran_ke, 10) || 0);
    });

    const namaPetani = h.nama_petani || '-';
    const nParts = namaPetani.trim().split(/\s+/);
    const initials = (nParts.length >= 2)
        ? (nParts[0].charAt(0) + nParts[1].charAt(0)).toUpperCase()
        : namaPetani.substring(0, 2).toUpperCase();

    document.getElementById('info-hutang-avatar').innerText = initials;
    document.getElementById('info-hutang-nama-petani').innerText = namaPetani;
    const alamatInfo = h.alamat_petani ? ` • ${h.alamat_petani}` : '';
    document.getElementById('info-hutang-sub-petani').innerText = `ID Anggota: #${h.anggota_id || 0}${alamatInfo}`;

    const isLunas = (h.status === 'Lunas');
    const statusWrap = document.getElementById('info-hutang-status-wrap');
    if (statusWrap) {
        statusWrap.innerHTML = isLunas
            ? `<span class="badge-status-hutang badge-status-lunas"><i class="fa-solid fa-circle-check"></i> Lunas</span>`
            : `<span class="badge-status-hutang badge-status-belum"><i class="fa-solid fa-clock"></i> Belum Lunas</span>`;
    }

    document.getElementById('info-hutang-pupuk').innerText = h.nama_pupuk || '-';
    document.getElementById('info-hutang-kuantitas').innerText = `${h.jumlah_karung || '0 Karung'} (${h.jumlah_berat || '0 Kg'})`;
    document.getElementById('info-hutang-harga').innerText = h.harga || 'Rp 0';
    document.getElementById('info-hutang-tgl-pinjam').innerText = formatDateID(h.tanggal || h.created_at);
    document.getElementById('info-hutang-tgl-lunas').innerText = isLunas ? formatDateID(h.tanggal_lunas || h.tanggal) : 'Belum Lunas';

    // Bersihkan catatan sistem " | Bayar: ..." agar keterangan asli mudah dibaca
    const rawKet = (h.keterangan || '-').split(' | Bayar:')[0].trim();
    document.getElementById('info-hutang-keterangan').innerText = rawKet || '-';

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
                const isHighlighted = highlightBayarId > 0 && parseInt(item.id, 10) === parseInt(highlightBayarId, 10);
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
    // Inisialisasi validasi interaktif & notifikasi untuk Tambah dan Edit Hutang Pupuk
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

    // Tampilkan toast notifikasi dari konfigurasi PHP (views/toast-config.php)
    if (window.PUPUK_TOAST) {
        showToast(
            window.PUPUK_TOAST.title,
            window.PUPUK_TOAST.message,
            window.PUPUK_TOAST.type,
            window.PUPUK_TOAST.duration || 1400
        );

        if (window.history.replaceState) {
            const viewParam = window.PUPUK_TOAST.viewParam || '';
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + viewParam;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }
    }
});
