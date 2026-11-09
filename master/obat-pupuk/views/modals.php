<!-- ========================================================
     MODAL 1: TAMBAH PRODUK (OBAT / PUPUK / TANAMAN)
======================================================== -->
<div id="modalTambahProduk" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-plus-circle text-agro-600"></i>
                Tambah Master Produk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahProduk')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('master/obat-pupuk/') ?>" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-nama-produk">Nama Produk <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-nama-produk"
                        name="nama_produk"
                        required
                        class="modal-input"
                        placeholder="Contoh: Pupuk Urea Bersubsidi / Inpari 32 / Virtako"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jenis">Jenis <span class="text-rose-500">*</span></label>
                    <select id="add-jenis" name="jenis" required class="modal-input">
                        <option value="">-- Pilih Jenis (Obat / Pupuk / Tanaman) --</option>
                        <option value="Obat">Obat</option>
                        <option value="Pupuk">Pupuk</option>
                        <option value="Tanaman">Tanaman</option>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Pupuk Nitrogen 46% / Varietas unggul panen 115 HST"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahProduk')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 2: EDIT PRODUK (OBAT / PUPUK / TANAMAN)
======================================================== -->
<div id="modalEditProduk" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Master Produk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditProduk')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('master/obat-pupuk/') ?>" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-nama-produk">Nama Produk <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-nama-produk"
                        name="nama_produk"
                        required
                        class="modal-input"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-jenis">Jenis <span class="text-rose-500">*</span></label>
                    <select id="edit-jenis" name="jenis" required class="modal-input">
                        <option value="Obat">Obat</option>
                        <option value="Pupuk">Pupuk</option>
                        <option value="Tanaman">Tanaman</option>
                    </select>
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
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditProduk')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 3: KONFIRMASI HAPUS PRODUK
======================================================== -->
<div id="modalDeleteProduk" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Master Produk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteProduk')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('master/obat-pupuk/') ?>" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-name-display">-</h5>
                <p class="text-xs text-slate-500 mb-2">Jenis: <span id="delete-jenis-display" class="font-bold text-slate-700">-</span></p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus produk ini dari daftar master?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteProduk')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

