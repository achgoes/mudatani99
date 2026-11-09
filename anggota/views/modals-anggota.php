<!-- ========================================================
     MODAL ANGGOTA 1: TAMBAH ANGGOTA PETANI
======================================================== -->
<div id="modalTambahAnggota" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-user-plus text-agro-600"></i>
                Tambah Anggota Petani
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahAnggota')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('anggota/') ?>" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-nama">Nama Petani <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-nama"
                        name="nama_petani"
                        required
                        class="modal-input"
                        placeholder="Contoh: Slamet Riyadi"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-alamat">Alamat <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-alamat"
                        name="alamat"
                        required
                        class="modal-input"
                        placeholder="Contoh: Dusun Krajan RT 02 / RW 01, Desa Makmur"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Anggota Aktif / Lahan Blok Utara 1.5 Ha"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahAnggota')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Anggota
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL ANGGOTA 2: EDIT ANGGOTA PETANI
======================================================== -->
<div id="modalEditAnggota" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Anggota Petani
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditAnggota')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('anggota/') ?>" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-nama">Nama Petani <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-nama"
                        name="nama_petani"
                        required
                        class="modal-input"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-alamat">Alamat <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-alamat"
                        name="alamat"
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
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditAnggota')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL ANGGOTA 3: KONFIRMASI HAPUS ANGGOTA PETANI
======================================================== -->
<div id="modalDeleteAnggota" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Anggota Petani
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteAnggota')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('anggota/') ?>" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-2" id="delete-name-display">-</h5>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus anggota petani ini beserta data lahannya? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteAnggota')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>
