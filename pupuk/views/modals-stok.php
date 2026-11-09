<!-- ========================================================
     MODAL 1: TAMBAH STOK PUPUK
======================================================== -->
<div id="modalTambahPupuk" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-boxes-stacked text-agro-600"></i>
                Tambah Stok Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahPupuk')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('pupuk/') ?>" method="POST" id="formTambahStokPupuk">
            <input type="hidden" name="action" value="add">

            <div class="custom-modal-body">
                <!-- Info Status Stok (Baru vs Akumulasi) -->
                <div id="add-stock-status-alert" class="hidden mb-3 p-3 rounded-xl border text-xs flex items-start gap-2.5"></div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-pupuk-id">Nama Pupuk (Dari Master - Pupuk) <span class="text-rose-500">*</span></label>
                    <select id="add-pupuk-id" name="pupuk_id" required class="modal-input" onchange="onSelectPupukTambahStok()">
                        <option value="">-- Pilih Nama Pupuk --</option>
                        <?php foreach ($masterPupukList as $mp): 
                            $mId = (int)$mp['id'];
                            $mNameLower = mb_strtolower(trim($mp['nama_produk']));
                            $existStock = $stokMapByPupukId[$mId] ?? ($stokMapByNameLower[$mNameLower] ?? null);
                            $hasStock   = ($existStock !== null);
                            $curKarung  = $hasStock ? $existStock['karung_num'] : 0;
                            $curBerat   = $hasStock ? $existStock['jumlah_berat'] : '0 Kg';
                            $bpk        = $hasStock ? $existStock['berat_per_karung'] : 50;
                        ?>
                            <option
                                value="<?= $mId ?>"
                                data-has-stock="<?= $hasStock ? '1' : '0' ?>"
                                data-cur-karung="<?= $curKarung ?>"
                                data-cur-berat="<?= htmlspecialchars($curBerat) ?>"
                                data-bpk="<?= $bpk ?>"
                                data-nama="<?= htmlspecialchars($mp['nama_produk']) ?>"
                            >
                                <?= htmlspecialchars($mp['nama_produk']) ?><?= $hasStock ? " (Sudah Ada: {$curKarung} Karung)" : " (Produk Baru)" ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Pilih pupuk dari Master. Jika sudah ada stok, penambahan akan otomatis <b>menjumlahkan stok yang ada</b> (tidak menambah baris baru).</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jumlah-karung">Jumlah Karung Ditambahkan <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-jumlah-karung"
                        name="jumlah_karung"
                        required
                        class="modal-input"
                        placeholder="Contoh: 25 Karung"
                        oninput="autoCalcBeratTambahStok()"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jumlah-berat">Jumlah Berat Ditambahkan <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-jumlah-berat"
                        name="jumlah_berat"
                        required
                        class="modal-input"
                        placeholder="Contoh: 1250 Kg"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Tambahan Pasokan Gudang Tahap 2"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahPupuk')">Batal</button>
                <button type="submit" class="btn-modal-save" id="btn-save-tambah-pupuk">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    <span id="btn-save-tambah-pupuk-text">Simpan Stok Pupuk</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 2: EDIT STOK PUPUK
======================================================== -->
<div id="modalEditPupuk" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Stok Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditPupuk')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('pupuk/') ?>" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-pupuk-id">Nama Pupuk (Dari Master - Pupuk) <span class="text-rose-500">*</span></label>
                    <select id="edit-pupuk-id" name="pupuk_id" required class="modal-input">
                        <option value="">-- Pilih Nama Pupuk --</option>
                        <?php foreach ($masterPupukList as $mp): ?>
                            <option value="<?= (int)$mp['id'] ?>"><?= htmlspecialchars($mp['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-jumlah-karung">Jumlah Karung <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-jumlah-karung"
                        name="jumlah_karung"
                        required
                        class="modal-input"
                    >
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
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditPupuk')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 3: KONFIRMASI HAPUS STOK PUPUK
======================================================== -->
<div id="modalDeletePupuk" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Data Stok Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeletePupuk')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('pupuk/') ?>" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-name-display">-</h5>
                <p class="text-xs text-slate-500 mb-2">Jumlah: <span id="delete-karung-display" class="font-bold text-slate-700">-</span></p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data stok pupuk ini?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeletePupuk')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>
