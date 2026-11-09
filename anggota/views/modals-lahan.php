<!-- ========================================================
     MODAL LAHAN 1: TAMBAH DATA PEMETAAN & LUAS LAHAN
======================================================== -->
<div id="modalTambahLahan" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-map-location-dot text-agro-600"></i>
                Tambah Data Pemetaan & Luas Lahan
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahLahan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('anggota/?view=lahan') ?>" method="POST">
            <input type="hidden" name="action" value="add_lahan">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-lahan-anggota">Nama Petani (Dari Daftar Anggota) <span class="text-rose-500">*</span></label>
                    <select id="add-lahan-anggota" name="anggota_id" required class="modal-input">
                        <option value="">-- Pilih Nama Petani --</option>
                        <?php foreach ($anggotaList as $a): ?>
                            <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['nama_petani']) ?> (<?= htmlspecialchars($a['alamat']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Diambil langsung dari tabel Daftar Anggota Petani.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-lahan-luas">Luas Lahan <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-lahan-luas"
                        name="luas_lahan"
                        required
                        class="modal-input"
                        placeholder="Contoh: 1.5 Ha"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-lahan-tanaman">Ditanami (Dari Master Tanaman) <span class="text-rose-500">*</span></label>
                    <select id="add-lahan-tanaman" name="tanaman_id" required class="modal-input">
                        <option value="">-- Pilih Komoditas Tanaman --</option>
                        <?php foreach ($tanamanList as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Diambil dari Master Obat, Pupuk, dan Tanaman (khusus jenis Tanaman).</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-lahan-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-lahan-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Blok Utara A-1, Irigasi Teknis"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahLahan')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Data Lahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL LAHAN 2: EDIT DATA PEMETAAN & LUAS LAHAN
======================================================== -->
<div id="modalEditLahan" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Pemetaan & Luas Lahan
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditLahan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('anggota/?view=lahan') ?>" method="POST">
            <input type="hidden" name="action" value="edit_lahan">
            <input type="hidden" id="edit-lahan-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-lahan-anggota">Nama Petani (Dari Daftar Anggota) <span class="text-rose-500">*</span></label>
                    <select id="edit-lahan-anggota" name="anggota_id" required class="modal-input">
                        <option value="">-- Pilih Nama Petani --</option>
                        <?php foreach ($anggotaList as $a): ?>
                            <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['nama_petani']) ?> (<?= htmlspecialchars($a['alamat']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-lahan-luas">Luas Lahan <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-lahan-luas"
                        name="luas_lahan"
                        required
                        class="modal-input"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-lahan-tanaman">Ditanami (Dari Master Tanaman) <span class="text-rose-500">*</span></label>
                    <select id="edit-lahan-tanaman" name="tanaman_id" required class="modal-input">
                        <option value="">-- Pilih Komoditas Tanaman --</option>
                        <?php foreach ($tanamanList as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Diambil dari Master Obat, Pupuk, dan Tanaman (khusus jenis Tanaman).</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-lahan-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="edit-lahan-keterangan"
                        name="keterangan"
                        class="modal-input"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditLahan')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL LAHAN 3: KONFIRMASI HAPUS DATA LAHAN
======================================================== -->
<div id="modalDeleteLahan" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Data Lahan
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteLahan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('anggota/?view=lahan') ?>" method="POST">
            <input type="hidden" name="action" value="delete_lahan">
            <input type="hidden" id="delete-lahan-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-lahan-name-display">-</h5>
                <p class="text-xs text-slate-500 mb-2">Luas Lahan: <span id="delete-lahan-luas-display" class="font-bold text-slate-700">-</span></p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data pemetaan lahan ini?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteLahan')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>
