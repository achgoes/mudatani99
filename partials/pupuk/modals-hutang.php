<!-- ========================================================
     MODAL 4: TAMBAH HUTANG PIUTANG PUPUK
======================================================== -->
<div id="modalTambahHutang" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog modal-hutang-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-hand-holding-dollar text-agro-600"></i>
                Tambah Data Hutang Piutang Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahHutang')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="pupuk.php?view=<?= urlencode($activeView === 'kelola' ? 'hutang' : $activeView) ?>" method="POST" id="formTambahHutang" novalidate>
            <input type="hidden" name="action" value="add_hutang">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($activeView === 'kelola' ? 'hutang' : $activeView) ?>">

            <div class="custom-modal-body">
                <!-- Notifikasi Peringatan Jika Ada Kolom Belum Diisi -->
                <div id="add-hutang-alert" class="hidden mb-3 p-3 rounded-xl border border-rose-200 bg-rose-50/95 text-rose-800 text-xs flex items-start gap-2.5 shadow-sm">
                    <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1" id="add-hutang-alert-text">
                        Mohon lengkapi semua kolom yang bertanda bintang (*) sebelum menyimpan.
                    </div>
                    <button type="button" class="text-rose-400 hover:text-rose-600 ml-auto" onclick="document.getElementById('add-hutang-alert').classList.add('hidden')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-hutang-anggota">Nama Petani (Dari Daftar Anggota Petani) <span class="text-rose-500">*</span></label>
                    <select id="add-hutang-anggota" name="anggota_id" required class="modal-input">
                        <option value="">-- Pilih Nama Petani --</option>
                        <?php foreach ($anggotaList as $ag): ?>
                            <option value="<?= (int)$ag['id'] ?>"><?= htmlspecialchars($ag['nama_petani']) ?> (<?= htmlspecialchars($ag['alamat']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-hutang-pupuk">Nama Pupuk (Dari Stok Pupuk) <span class="text-rose-500">*</span></label>
                    <select id="add-hutang-pupuk" name="pengelolaan_pupuk_id" required class="modal-input" onchange="onPupukHutangChange('add')">
                        <option value="" data-karung="0" data-berat="0" data-bpk="50">-- Pilih Pupuk --</option>
                        <?php foreach ($pupukList as $pk): ?>
                            <option
                                value="<?= (int)$pk['id'] ?>"
                                data-karung="<?= (int)$pk['karung_num'] ?>"
                                data-berat="<?= (float)$pk['berat_num'] ?>"
                                data-bpk="<?= (float)$pk['berat_per_karung'] ?>"
                            >
                                <?= htmlspecialchars($pk['nama_pupuk']) ?> (Tersedia: <?= htmlspecialchars($pk['jumlah_karung']) ?> / <?= htmlspecialchars($pk['jumlah_berat']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text" id="add-hutang-stok-info">Stok pada menu Stok Pupuk akan otomatis berkurang sesuai jumlah karung yang dipakai.</p>
                </div>

                <!-- Baris 2 Kolom: Jumlah Karung & Jumlah Berat -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-hutang-karung">Jumlah Karung <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-hutang-karung"
                            name="jumlah_karung"
                            required
                            class="modal-input"
                            placeholder="Contoh: 5 Karung"
                            oninput="autoCalcBeratHutang('add'); autoCalcTotalHutang('add')"
                        >
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-hutang-berat">Jumlah Berat <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-hutang-berat"
                            name="jumlah_berat"
                            required
                            class="modal-input"
                            placeholder="Contoh: 250 Kg"
                        >
                    </div>
                </div>

                <!-- Baris 2 Kolom: Harga Satuan & Jumlah Hutang -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-hutang-harga">Harga Satuan Per Kantong (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-hutang-harga"
                            name="harga"
                            required
                            class="modal-input font-semibold"
                            placeholder="Contoh: Rp 150.000"
                            oninput="formatInputRupiah(this); autoCalcTotalHutang('add')"
                        >
                        <p class="modal-helper-text">Harga per satuan kantong.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-hutang-jumlah">Jumlah Hutang (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-hutang-jumlah"
                            name="jumlah_hutang"
                            required
                            class="modal-input font-bold text-blue-900 bg-blue-50/50"
                            placeholder="Rp 0"
                            oninput="formatInputRupiah(this)"
                        >
                        <p class="modal-helper-text">Total: Karung × Harga.</p>
                    </div>
                </div>

                <!-- Baris 2 Kolom: Sisa Hutang & Status -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-hutang-sisa">Sisa Hutang (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-hutang-sisa"
                            name="sisa_hutang"
                            required
                            class="modal-input font-bold text-rose-900 bg-rose-50/50"
                            placeholder="Rp 0"
                            oninput="formatInputRupiah(this)"
                        >
                        <p class="modal-helper-text">Rp 0 jika Lunas.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-hutang-status">Status <span class="text-rose-500">*</span></label>
                        <select id="add-hutang-status" name="status" required class="modal-input" onchange="onStatusHutangChange('add')">
                            <option value="Belum Lunas">Belum Lunas</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                    </div>
                </div>

                <!-- Baris 2 Kolom: Tanggal Pinjam & Keterangan -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-hutang-tanggal">Tanggal Pinjam <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="add-hutang-tanggal"
                            name="tanggal"
                            required
                            class="modal-input"
                            value="<?= date('Y-m-d') ?>"
                        >
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-hutang-keterangan">Keterangan</label>
                        <input
                            type="text"
                            id="add-hutang-keterangan"
                            name="keterangan"
                            class="modal-input"
                            placeholder="Contoh: Pinjaman MT 1"
                        >
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahHutang')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Hutang Pupuk
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 5: EDIT HUTANG PIUTANG PUPUK
======================================================== -->
<div id="modalEditHutang" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog modal-hutang-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Hutang Piutang Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditHutang')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="pupuk.php?view=<?= urlencode($activeView === 'kelola' ? 'hutang' : $activeView) ?>" method="POST" id="formEditHutang" novalidate>
            <input type="hidden" name="action" value="edit_hutang">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($activeView === 'kelola' ? 'hutang' : $activeView) ?>">
            <input type="hidden" id="edit-hutang-id" name="id" value="">

            <div class="custom-modal-body">
                <!-- Notifikasi Peringatan Jika Ada Kolom Belum Diisi -->
                <div id="edit-hutang-alert" class="hidden mb-3 p-3 rounded-xl border border-rose-200 bg-rose-50/95 text-rose-800 text-xs flex items-start gap-2.5 shadow-sm">
                    <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1" id="edit-hutang-alert-text">
                        Mohon lengkapi semua kolom yang bertanda bintang (*) sebelum menyimpan.
                    </div>
                    <button type="button" class="text-rose-400 hover:text-rose-600 ml-auto" onclick="document.getElementById('edit-hutang-alert').classList.add('hidden')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-hutang-anggota">Nama Petani (Dari Daftar Anggota Petani) <span class="text-rose-500">*</span></label>
                    <select id="edit-hutang-anggota" name="anggota_id" required class="modal-input">
                        <option value="">-- Pilih Nama Petani --</option>
                        <?php foreach ($anggotaList as $ag): ?>
                            <option value="<?= (int)$ag['id'] ?>"><?= htmlspecialchars($ag['nama_petani']) ?> (<?= htmlspecialchars($ag['alamat']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-hutang-pupuk">Nama Pupuk (Dari Stok Pupuk) <span class="text-rose-500">*</span></label>
                    <select id="edit-hutang-pupuk" name="pengelolaan_pupuk_id" required class="modal-input" onchange="onPupukHutangChange('edit')">
                        <option value="" data-karung="0" data-berat="0" data-bpk="50">-- Pilih Pupuk --</option>
                        <?php foreach ($pupukList as $pk): ?>
                            <option
                                value="<?= (int)$pk['id'] ?>"
                                data-karung="<?= (int)$pk['karung_num'] ?>"
                                data-berat="<?= (float)$pk['berat_num'] ?>"
                                data-bpk="<?= (float)$pk['berat_per_karung'] ?>"
                            >
                                <?= htmlspecialchars($pk['nama_pupuk']) ?> (Sisa Gudang: <?= htmlspecialchars($pk['jumlah_karung']) ?> / <?= htmlspecialchars($pk['jumlah_berat']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Perubahan jumlah karung otomatis menyesuaikan data pada Stok Pupuk.</p>
                </div>

                <!-- Baris 2 Kolom: Jumlah Karung & Jumlah Berat -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-hutang-karung">Jumlah Karung <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="edit-hutang-karung"
                            name="jumlah_karung"
                            required
                            class="modal-input"
                            oninput="autoCalcBeratHutang('edit'); autoCalcTotalHutang('edit')"
                        >
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-hutang-berat">Jumlah Berat <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="edit-hutang-berat"
                            name="jumlah_berat"
                            required
                            class="modal-input"
                        >
                    </div>
                </div>

                <!-- Baris 2 Kolom: Harga Satuan & Jumlah Hutang -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-hutang-harga">Harga Satuan Per Kantong (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="edit-hutang-harga"
                            name="harga"
                            required
                            class="modal-input font-semibold"
                            placeholder="Contoh: Rp 150.000"
                            oninput="formatInputRupiah(this); autoCalcTotalHutang('edit')"
                        >
                        <p class="modal-helper-text">Harga per satuan kantong.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-hutang-jumlah">Jumlah Hutang (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="edit-hutang-jumlah"
                            name="jumlah_hutang"
                            required
                            class="modal-input font-bold text-blue-900 bg-blue-50/50"
                            placeholder="Rp 0"
                            oninput="formatInputRupiah(this)"
                        >
                        <p class="modal-helper-text">Total: Karung × Harga.</p>
                    </div>
                </div>

                <!-- Baris 2 Kolom: Sisa Hutang & Status -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-hutang-sisa">Sisa Hutang (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="edit-hutang-sisa"
                            name="sisa_hutang"
                            required
                            class="modal-input font-bold text-rose-900 bg-rose-50/50"
                            placeholder="Rp 0"
                            oninput="formatInputRupiah(this)"
                        >
                        <p class="modal-helper-text">Rp 0 jika Lunas.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-hutang-status">Status <span class="text-rose-500">*</span></label>
                        <select id="edit-hutang-status" name="status" required class="modal-input" onchange="onStatusHutangChange('edit')">
                            <option value="Belum Lunas">Belum Lunas</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                    </div>
                </div>

                <!-- Baris 2 Kolom: Tanggal Pinjam & Keterangan -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-hutang-tanggal">Tanggal Pinjam <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="edit-hutang-tanggal"
                            name="tanggal"
                            required
                            class="modal-input"
                        >
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-hutang-keterangan">Keterangan</label>
                        <input
                            type="text"
                            id="edit-hutang-keterangan"
                            name="keterangan"
                            class="modal-input"
                        >
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditHutang')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 6: KONFIRMASI HAPUS HUTANG PIUTANG (KEMBALIKAN STOK)
======================================================== -->
<div id="modalDeleteHutang" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Data Hutang Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteHutang')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="pupuk.php?view=<?= urlencode($activeView === 'kelola' ? 'hutang' : $activeView) ?>" method="POST">
            <input type="hidden" name="action" value="delete_hutang">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($activeView === 'kelola' ? 'hutang' : $activeView) ?>">
            <input type="hidden" id="delete-hutang-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-hutang-petani">-</h5>
                <p class="text-xs text-slate-500 mb-2">
                    Pupuk: <span id="delete-hutang-pupuk" class="font-bold text-slate-700">-</span>
                    (<span id="delete-hutang-karung" class="font-bold text-amber-700">-</span>)
                </p>
                <p class="text-xs text-emerald-700 bg-emerald-50 p-2.5 rounded-lg border border-emerald-200 mb-2">
                    <i class="fa-solid fa-arrows-rotate mr-1"></i>
                    Jumlah karung akan <b>otomatis dikembalikan</b> ke data Stok Pupuk.
                </p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data hutang ini?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteHutang')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus &amp; Kembalikan Stok
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 7: BAYAR HUTANG PUPUK (POP UP BAYAR JUMLAH RUPIAH)
======================================================== -->
<div id="modalBayarHutang" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-md">
        <div class="custom-modal-header bg-emerald-50/70 border-b border-emerald-100">
            <h4 class="text-emerald-800">
                <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                Bayar Hutang Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalBayarHutang')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="pupuk.php?view=<?= urlencode($activeView === 'kelola' ? 'hutang' : $activeView) ?>" method="POST">
            <input type="hidden" name="action" value="pay_hutang">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($activeView === 'kelola' ? 'hutang' : $activeView) ?>">
            <input type="hidden" id="bayar-id" name="id" value="">

            <div class="custom-modal-body">
                <!-- Info Ringkasan Tagihan -->
                <div class="p-3.5 mb-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200">
                    <div class="flex items-center justify-between mb-2 pb-2 border-b border-emerald-200/60">
                        <span class="text-xs text-slate-500 font-medium">Petani</span>
                        <strong class="text-sm text-slate-800" id="bayar-petani-display">-</strong>
                    </div>
                    <div class="flex items-center justify-between mb-2 pb-2 border-b border-emerald-200/60">
                        <span class="text-xs text-slate-500 font-medium">Pupuk &amp; Jumlah</span>
                        <span class="text-xs text-slate-700 font-semibold" id="bayar-pupuk-display">-</span>
                    </div>
                    <div class="flex items-center justify-between mb-2 pb-2 border-b border-emerald-200/60">
                        <span class="text-xs text-slate-500 font-medium">Jumlah Hutang Total</span>
                        <span class="text-xs text-blue-800 font-bold" id="bayar-jumlah-hutang-display">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-emerald-700 font-bold uppercase tracking-wider">Sisa Tagihan Hutang</span>
                        <span class="text-base font-extrabold text-emerald-700" id="bayar-tagihan-display">Rp 0</span>
                    </div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="bayar-nominal">Jumlah Pembayaran (Rupiah) <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="bayar-nominal"
                        name="bayar_nominal"
                        required
                        class="modal-input font-bold text-emerald-800 text-base"
                        placeholder="Contoh: Rp 750.000"
                        oninput="formatInputRupiah(this)"
                    >
                    <p class="modal-helper-text">Masukkan jumlah rupiah yang dibayarkan oleh petani untuk pelunasan.</p>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="bayar-metode">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select id="bayar-metode" name="metode_bayar" required class="modal-input">
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                            <option value="Potong Hasil Panen">Potong Hasil Panen</option>
                        </select>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="bayar-tanggal">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="bayar-tanggal"
                            name="tanggal_bayar"
                            required
                            class="modal-input"
                            value="<?= date('Y-m-d') ?>"
                        >
                    </div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="bayar-catatan">Catatan / Keterangan Pembayaran</label>
                    <input
                        type="text"
                        id="bayar-catatan"
                        name="catatan_bayar"
                        class="modal-input"
                        placeholder="Contoh: Pelunasan tunai via bendahara poktan"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalBayarHutang')">Batal</button>
                <button type="submit" class="btn-modal-save bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30">
                    <i class="fa-solid fa-check-double mr-1"></i>
                    Konfirmasi &amp; Lunasi
                </button>
            </div>
        </form>
    </div>
</div>

