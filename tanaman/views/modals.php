<!-- ========================================================
     MODAL 1: TAMBAH PENGELOLAAN TANAMAN
======================================================== -->
<div id="modalTambahTanaman" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-wheat-awn text-agro-600"></i>
                Tambah Data Pengelolaan Tanaman
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahTanaman')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('tanaman/') ?>" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-tanaman-id">Nama Tanaman (Dari Master - Tanaman) <span class="text-rose-500">*</span></label>
                    <select id="add-tanaman-id" name="tanaman_id" required class="modal-input">
                        <option value="">-- Pilih Nama Tanaman --</option>
                        <?php foreach ($masterTanamanList as $mt): ?>
                            <option value="<?= (int)$mt['id'] ?>"><?= htmlspecialchars($mt['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Diambil dari menu Master Obat, Pupuk, dan Tanaman (khusus jenis Tanaman).</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jumlah-angka">Jumlah Kemasan <span class="text-rose-500">*</span></label>
                    <div class="input-kemasan-group">
                        <input
                            type="number"
                            min="1"
                            step="any"
                            id="add-jumlah-angka"
                            name="jumlah_angka"
                            required
                            class="modal-input"
                            placeholder="Contoh: 20"
                        >
                        <select name="satuan_kemasan" class="modal-input select-satuan-tanaman">
                            <option value="Pcs">Pcs</option>
                        </select>
                    </div>
                    <p class="modal-helper-text">Satuan kemasan tanaman menggunakan <b>Pcs</b>.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jumlah-berat">Jumlah Berat <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-jumlah-berat"
                        name="jumlah_berat"
                        required
                        class="modal-input"
                        placeholder="Contoh: 500 Kg"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Stok Benih Unggul Siap Tanam"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahTanaman')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Data Tanaman
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 2: EDIT PENGELOLAAN TANAMAN
======================================================== -->
<div id="modalEditTanaman" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Pengelolaan Tanaman
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditTanaman')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('tanaman/') ?>" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-tanaman-id">Nama Tanaman (Dari Master - Tanaman) <span class="text-rose-500">*</span></label>
                    <select id="edit-tanaman-id" name="tanaman_id" required class="modal-input">
                        <option value="">-- Pilih Nama Tanaman --</option>
                        <?php foreach ($masterTanamanList as $mt): ?>
                            <option value="<?= (int)$mt['id'] ?>"><?= htmlspecialchars($mt['nama_produk']) ?></option>
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
                        <select name="satuan_kemasan" class="modal-input select-satuan-tanaman">
                            <option value="Pcs">Pcs</option>
                        </select>
                    </div>
                    <p class="modal-helper-text">Satuan kemasan tanaman menggunakan <b>Pcs</b>.</p>
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
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditTanaman')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 3: KONFIRMASI HAPUS PENGELOLAAN TANAMAN
======================================================== -->
<div id="modalDeleteTanaman" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Data Tanaman
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteTanaman')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('tanaman/') ?>" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-name-display">-</h5>
                <p class="text-xs text-slate-500 mb-2">Jumlah Kemasan: <span id="delete-kemasan-display" class="font-bold text-slate-700">-</span></p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data pengelolaan tanaman ini?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteTanaman')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

