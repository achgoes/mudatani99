<!-- ========================================================
     MODAL 1: TAMBAH STOK OBAT
======================================================== -->
<div id="modalTambahObat" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-shield-virus text-agro-600"></i>
                Tambah Stok Obat
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahObat')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('obat/') ?>" method="POST" id="formTambahStokObat">
            <input type="hidden" name="action" value="add">

            <div class="custom-modal-body">
                <!-- Info Status Stok (Baru vs Akumulasi) -->
                <div id="add-stock-status-alert" class="hidden mb-3 p-3 rounded-xl border text-xs flex items-start gap-2.5"></div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-obat-id">Nama Obat (Dari Master - Obat) <span class="text-rose-500">*</span></label>
                    <select id="add-obat-id" name="obat_id" required class="modal-input" onchange="onSelectObatTambahStok()">
                        <option value="">-- Pilih Nama Obat --</option>
                        <?php foreach ($masterObatList as $mo):
                            $mId = (int)$mo['id'];
                            $mNameLower = mb_strtolower(trim($mo['nama_produk']));
                            $existStock = $stokMapByObatId[$mId] ?? ($stokMapByNameLower[$mNameLower] ?? null);
                            $hasStock   = ($existStock !== null);
                            $curKemasan = $hasStock ? $existStock['kemasan_num'] : 0;
                            $curSatuan  = $hasStock ? $existStock['satuan_kemasan'] : 'Pcs';
                            $curBerat   = $hasStock ? $existStock['jumlah_berat'] : '0 Kg';
                            $bpk        = $hasStock ? $existStock['berat_per_kemasan'] : 1;
                        ?>
                            <option
                                value="<?= $mId ?>"
                                data-has-stock="<?= $hasStock ? '1' : '0' ?>"
                                data-cur-kemasan="<?= $curKemasan ?>"
                                data-satuan="<?= htmlspecialchars($curSatuan) ?>"
                                data-cur-berat="<?= htmlspecialchars($curBerat) ?>"
                                data-bpk="<?= $bpk ?>"
                                data-nama="<?= htmlspecialchars($mo['nama_produk']) ?>"
                            >
                                <?= htmlspecialchars($mo['nama_produk']) ?><?= $hasStock ? " (Sudah Ada: {$curKemasan} {$curSatuan})" : " (Produk Baru)" ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Pilih obat dari Master. Jika sudah ada stok, penambahan akan otomatis <b>menjumlahkan stok yang ada</b> (tidak menambah baris baru).</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jumlah-angka">Jumlah Kemasan Ditambahkan <span class="text-rose-500">*</span></label>
                    <div class="input-kemasan-group">
                        <input
                            type="number"
                            min="1"
                            step="any"
                            id="add-jumlah-angka"
                            name="jumlah_angka"
                            required
                            class="modal-input"
                            placeholder="Contoh: 12"
                            oninput="autoCalcBeratTambahStok()"
                        >
                        <select id="add-satuan-kemasan" name="satuan_kemasan" required class="modal-input select-satuan-obat">
                            <option value="Pcs">Pcs</option>
                            <option value="Botol">Botol</option>
                        </select>
                    </div>
                    <p class="modal-helper-text">Pilih satuan kemasan obat: <b>Pcs</b> atau <b>Botol</b>.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jumlah-berat">Jumlah Berat Ditambahkan <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-jumlah-berat"
                        name="jumlah_berat"
                        required
                        class="modal-input"
                        placeholder="Contoh: 12 Kg"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Stok Obat Pengendali Hama Gudang Poktan"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahObat')">Batal</button>
                <button type="submit" class="btn-modal-save" id="btn-save-tambah-obat">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    <span id="btn-save-tambah-obat-text">Simpan Stok Obat</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 2: EDIT STOK OBAT
======================================================== -->
<div id="modalEditObat" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Stok Obat
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditObat')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('obat/') ?>" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-obat-id">Nama Obat (Dari Master - Obat) <span class="text-rose-500">*</span></label>
                    <select id="edit-obat-id" name="obat_id" required class="modal-input">
                        <option value="">-- Pilih Nama Obat --</option>
                        <?php foreach ($masterObatList as $mo): ?>
                            <option value="<?= (int)$mo['id'] ?>"><?= htmlspecialchars($mo['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-jumlah-angka">Jumlah Kemasan <span class="text-rose-500">*</span></label>
                    <div class="input-kemasan-group">
                        <input
                            type="number"
                            min="1"
                            step="any"
                            id="edit-jumlah-angka"
                            name="jumlah_angka"
                            required
                            class="modal-input"
                        >
                        <select id="edit-satuan-kemasan" name="satuan_kemasan" required class="modal-input select-satuan-obat">
                            <option value="Pcs">Pcs</option>
                            <option value="Botol">Botol</option>
                        </select>
                    </div>
                    <p class="modal-helper-text">Pilih satuan kemasan obat: <b>Pcs</b> atau <b>Botol</b>.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-jumlah-berat">Jumlah Berat <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-jumlah-berat"
                        name="jumlah_berat"
                        required
                        class="modal-input"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="edit-keterangan"
                        name="keterangan"
                        class="modal-input"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditObat')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 3: KONFIRMASI HAPUS STOK OBAT
======================================================== -->
<div id="modalDeleteObat" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Data Stok Obat
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteObat')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('obat/') ?>" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-name-display">-</h5>
                <p class="text-xs text-slate-500 mb-2">Jumlah Kemasan: <span id="delete-kemasan-display" class="font-bold text-slate-700">-</span></p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data stok obat ini?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteObat')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>
