<?php
$hutangFormAction = appUrl('tanaman/?view=' . urlencode($activeView === 'kelola' ? 'hutang' : $activeView));
$hutangReturnView = $activeView === 'kelola' ? 'hutang' : $activeView;
?>
<!-- ========================================================
     MODAL 4: TAMBAH HUTANG PIUTANG TANAMAN (MULTI-ITEM)
======================================================== -->
<div id="modalTambahHutang" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog modal-hutang-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-hand-holding-dollar text-agro-600"></i>
                Tambah Data Hutang Piutang Tanaman
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahHutang')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $hutangFormAction ?>" method="POST" id="formTambahHutang" novalidate>
            <input type="hidden" name="action" value="add_hutang">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($hutangReturnView) ?>">

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

                <!-- Daftar Item Tanaman Dinamis -->
                <div class="hutang-items-section">
                    <div class="hutang-items-header">
                        <div>
                            <span class="modal-label mb-0">Daftar Item Tanaman <span class="text-rose-500">*</span></span>
                            <p class="modal-helper-text mt-0.5">Stok pada menu Stok Tanaman otomatis berkurang sesuai tiap item yang dipilih.</p>
                        </div>
                        <button type="button" class="btn-add-hutang-item" onclick="addHutangItemRow('add')">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Item Tanaman</span>
                        </button>
                    </div>
                    <div id="add-hutang-items-container" class="hutang-items-container"></div>
                </div>

                <!-- Baris 2 Kolom: Jumlah Hutang Total & Sisa Hutang -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-hutang-jumlah">Jumlah Hutang Total (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-hutang-jumlah"
                            name="jumlah_hutang"
                            required
                            readonly
                            class="modal-input font-bold text-blue-900 bg-blue-50/60"
                            value="Rp 0"
                        >
                        <p class="modal-helper-text">Otomatis dijumlahkan dari subtotal semua item tanaman.</p>
                    </div>

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
                </div>

                <!-- Baris 2 Kolom: Status & Tanggal Pinjam -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-hutang-status">Status <span class="text-rose-500">*</span></label>
                        <select id="add-hutang-status" name="status" required class="modal-input" onchange="onStatusHutangChange('add')">
                            <option value="Belum Lunas">Belum Lunas</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                    </div>

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
                </div>

                <div class="modal-form-group mb-0">
                    <label class="modal-label" for="add-hutang-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-hutang-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Pinjaman Benih MT 1"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahHutang')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Hutang Tanaman
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 5: EDIT HUTANG PIUTANG TANAMAN (MULTI-ITEM)
======================================================== -->
<div id="modalEditHutang" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog modal-hutang-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Hutang Piutang Tanaman
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditHutang')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $hutangFormAction ?>" method="POST" id="formEditHutang" novalidate>
            <input type="hidden" name="action" value="edit_hutang">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($hutangReturnView) ?>">
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

                <!-- Daftar Item Tanaman Dinamis -->
                <div class="hutang-items-section">
                    <div class="hutang-items-header">
                        <div>
                            <span class="modal-label mb-0">Daftar Item Tanaman <span class="text-rose-500">*</span></span>
                            <p class="modal-helper-text mt-0.5">Perubahan item &amp; jumlah kemasan otomatis menyesuaikan data pada Stok Tanaman.</p>
                        </div>
                        <button type="button" class="btn-add-hutang-item" onclick="addHutangItemRow('edit')">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Item Tanaman</span>
                        </button>
                    </div>
                    <div id="edit-hutang-items-container" class="hutang-items-container"></div>
                </div>

                <!-- Baris 2 Kolom: Jumlah Hutang Total & Sisa Hutang -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-hutang-jumlah">Jumlah Hutang Total (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="edit-hutang-jumlah"
                            name="jumlah_hutang"
                            required
                            readonly
                            class="modal-input font-bold text-blue-900 bg-blue-50/60"
                            value="Rp 0"
                        >
                        <p class="modal-helper-text">Otomatis dijumlahkan dari subtotal semua item tanaman.</p>
                    </div>

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
                </div>

                <!-- Baris 2 Kolom: Status & Tanggal Pinjam -->
                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-hutang-status">Status <span class="text-rose-500">*</span></label>
                        <select id="edit-hutang-status" name="status" required class="modal-input" onchange="onStatusHutangChange('edit')">
                            <option value="Belum Lunas">Belum Lunas</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                    </div>

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
                </div>

                <div class="modal-form-group mb-0">
                    <label class="modal-label" for="edit-hutang-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="edit-hutang-keterangan"
                        name="keterangan"
                        class="modal-input"
                    >
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
                Hapus Data Hutang Tanaman
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteHutang')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $hutangFormAction ?>" method="POST">
            <input type="hidden" name="action" value="delete_hutang">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($hutangReturnView) ?>">
            <input type="hidden" id="delete-hutang-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-hutang-petani">-</h5>
                <p class="text-xs text-slate-500 mb-2">
                    Tanaman: <span id="delete-hutang-tanaman" class="font-bold text-slate-700">-</span>
                    (<span id="delete-hutang-kemasan" class="font-bold text-emerald-700">-</span>)
                </p>
                <p class="text-xs text-emerald-700 bg-emerald-50 p-2.5 rounded-lg border border-emerald-200 mb-2">
                    <i class="fa-solid fa-arrows-rotate mr-1"></i>
                    Seluruh stok kemasan item pada transaksi ini akan <b>otomatis dikembalikan</b> ke data Stok Tanaman.
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
