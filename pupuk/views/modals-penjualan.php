<?php
$penjualanFormAction = appUrl('pupuk/?view=' . urlencode($activeView === 'kelola' ? 'penjualan' : $activeView));
$penjualanReturnView = $activeView === 'kelola' ? 'penjualan' : $activeView;
?>
<!-- ========================================================
     MODAL PENJUALAN 1: TAMBAH PENJUALAN PUPUK (MULTI-ITEM)
======================================================== -->
<div id="modalTambahPenjualan" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog modal-hutang-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-cart-shopping text-agro-600"></i>
                Tambah Transaksi Penjualan Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahPenjualan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $penjualanFormAction ?>" method="POST" id="formTambahPenjualan" novalidate>
            <input type="hidden" name="action" value="add_penjualan">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($penjualanReturnView) ?>">

            <div class="custom-modal-body">
                <div id="add-penjualan-alert" class="hidden mb-3 p-3 rounded-xl border border-rose-200 bg-rose-50/95 text-rose-800 text-xs flex items-start gap-2.5 shadow-sm">
                    <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1" id="add-penjualan-alert-text">
                        Mohon lengkapi semua kolom yang bertanda bintang (*) sebelum menyimpan.
                    </div>
                    <button type="button" class="text-rose-400 hover:text-rose-600 ml-auto" onclick="document.getElementById('add-penjualan-alert').classList.add('hidden')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-penjualan-anggota">Pilih Anggota Petani (Opsional)</label>
                        <select id="add-penjualan-anggota" name="anggota_id" class="modal-input" onchange="onPenjualanAnggotaChange('add')">
                            <option value="">-- Pembeli Umum / Non-Anggota --</option>
                            <?php foreach ($anggotaList as $ag): ?>
                                <option value="<?= (int)$ag['id'] ?>" data-nama="<?= htmlspecialchars($ag['nama_petani']) ?>" data-alamat="<?= htmlspecialchars($ag['alamat']) ?>">
                                    <?= htmlspecialchars($ag['nama_petani']) ?> (<?= htmlspecialchars($ag['alamat']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="modal-helper-text">Pilih anggota untuk mengisi nama pembeli otomatis.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-penjualan-pembeli">Nama Pembeli <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-penjualan-pembeli"
                            name="nama_pembeli"
                            required
                            class="modal-input"
                            placeholder="Contoh: Budi Santoso / Pembeli Umum"
                        >
                    </div>
                </div>

                <div class="modal-form-group" id="add-penjualan-alamat-wrap">
                    <label class="modal-label" for="add-penjualan-alamat">Alamat Pembeli (Non-Anggota)</label>
                    <input
                        type="text"
                        id="add-penjualan-alamat"
                        name="alamat_pembeli"
                        class="modal-input"
                        placeholder="Contoh: Dusun Krajan RT 02 / RW 01, Desa Makmur"
                    >
                    <p class="modal-helper-text">Alamat ini akan langsung dicetak pada nota penjualan.</p>
                </div>

                <!-- Daftar Item Pupuk Dinamis -->
                <div class="hutang-items-section">
                    <div class="hutang-items-header">
                        <div>
                            <span class="modal-label mb-0">Daftar Item Pupuk Dijual <span class="text-rose-500">*</span></span>
                            <p class="modal-helper-text mt-0.5">Stok pada menu Stok Pupuk otomatis berkurang sesuai jumlah item terjual.</p>
                        </div>
                        <button type="button" class="btn-add-hutang-item" onclick="addPupukItemRow('add-penjualan')">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Item Pupuk</span>
                        </button>
                    </div>
                    <div id="add-penjualan-items-container" class="hutang-items-container"></div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-penjualan-total">Total Penjualan (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-penjualan-total"
                            name="total_harga"
                            required
                            readonly
                            class="modal-input font-bold text-emerald-800 bg-emerald-50/70"
                            value="Rp 0"
                        >
                        <p class="modal-helper-text">Otomatis dijumlahkan dari subtotal semua item.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-penjualan-metode">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select id="add-penjualan-metode" name="metode_bayar" required class="modal-input">
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                            <option value="QRIS">QRIS</option>
                        </select>
                    </div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-penjualan-tanggal">Tanggal Penjualan <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="add-penjualan-tanggal"
                            name="tanggal_jual"
                            required
                            class="modal-input"
                            value="<?= date('Y-m-d') ?>"
                        >
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-penjualan-keterangan">Keterangan</label>
                        <input
                            type="text"
                            id="add-penjualan-keterangan"
                            name="keterangan"
                            class="modal-input"
                            placeholder="Contoh: Pembelian tunai non-subsidi"
                        >
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahPenjualan')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Penjualan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL PENJUALAN 2: EDIT PENJUALAN PUPUK (MULTI-ITEM)
======================================================== -->
<div id="modalEditPenjualan" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog modal-hutang-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Transaksi Penjualan Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditPenjualan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $penjualanFormAction ?>" method="POST" id="formEditPenjualan" novalidate>
            <input type="hidden" name="action" value="edit_penjualan">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($penjualanReturnView) ?>">
            <input type="hidden" id="edit-penjualan-id" name="id" value="">

            <div class="custom-modal-body">
                <div id="edit-penjualan-alert" class="hidden mb-3 p-3 rounded-xl border border-rose-200 bg-rose-50/95 text-rose-800 text-xs flex items-start gap-2.5 shadow-sm">
                    <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1" id="edit-penjualan-alert-text">
                        Mohon lengkapi semua kolom yang bertanda bintang (*) sebelum menyimpan.
                    </div>
                    <button type="button" class="text-rose-400 hover:text-rose-600 ml-auto" onclick="document.getElementById('edit-penjualan-alert').classList.add('hidden')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-penjualan-anggota">Pilih Anggota Petani (Opsional)</label>
                        <select id="edit-penjualan-anggota" name="anggota_id" class="modal-input" onchange="onPenjualanAnggotaChange('edit')">
                            <option value="">-- Pembeli Umum / Non-Anggota --</option>
                            <?php foreach ($anggotaList as $ag): ?>
                                <option value="<?= (int)$ag['id'] ?>" data-nama="<?= htmlspecialchars($ag['nama_petani']) ?>" data-alamat="<?= htmlspecialchars($ag['alamat']) ?>">
                                    <?= htmlspecialchars($ag['nama_petani']) ?> (<?= htmlspecialchars($ag['alamat']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-penjualan-pembeli">Nama Pembeli <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="edit-penjualan-pembeli"
                            name="nama_pembeli"
                            required
                            class="modal-input"
                        >
                    </div>
                </div>

                <div class="modal-form-group" id="edit-penjualan-alamat-wrap">
                    <label class="modal-label" for="edit-penjualan-alamat">Alamat Pembeli (Non-Anggota)</label>
                    <input
                        type="text"
                        id="edit-penjualan-alamat"
                        name="alamat_pembeli"
                        class="modal-input"
                        placeholder="Contoh: Dusun Krajan RT 02 / RW 01, Desa Makmur"
                    >
                    <p class="modal-helper-text">Alamat ini akan langsung dicetak pada nota penjualan.</p>
                </div>

                <div class="hutang-items-section">
                    <div class="hutang-items-header">
                        <div>
                            <span class="modal-label mb-0">Daftar Item Pupuk Dijual <span class="text-rose-500">*</span></span>
                            <p class="modal-helper-text mt-0.5">Perubahan item &amp; jumlah karung otomatis menyesuaikan data pada Stok Pupuk.</p>
                        </div>
                        <button type="button" class="btn-add-hutang-item" onclick="addPupukItemRow('edit-penjualan')">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Item Pupuk</span>
                        </button>
                    </div>
                    <div id="edit-penjualan-items-container" class="hutang-items-container"></div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-penjualan-total">Total Penjualan (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="edit-penjualan-total"
                            name="total_harga"
                            required
                            readonly
                            class="modal-input font-bold text-emerald-800 bg-emerald-50/70"
                            value="Rp 0"
                        >
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-penjualan-metode">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select id="edit-penjualan-metode" name="metode_bayar" required class="modal-input">
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                            <option value="QRIS">QRIS</option>
                        </select>
                    </div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-penjualan-tanggal">Tanggal Penjualan <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="edit-penjualan-tanggal"
                            name="tanggal_jual"
                            required
                            class="modal-input"
                        >
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-penjualan-keterangan">Keterangan</label>
                        <input
                            type="text"
                            id="edit-penjualan-keterangan"
                            name="keterangan"
                            class="modal-input"
                        >
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditPenjualan')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL PENJUALAN 3: KONFIRMASI HAPUS (KEMBALIKAN STOK)
======================================================== -->
<div id="modalDeletePenjualan" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Transaksi Penjualan
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeletePenjualan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $penjualanFormAction ?>" method="POST">
            <input type="hidden" name="action" value="delete_penjualan">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($penjualanReturnView) ?>">
            <input type="hidden" id="delete-penjualan-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-penjualan-pembeli">-</h5>
                <p class="text-xs text-slate-500 mb-2">
                    Nota: <span id="delete-penjualan-kode" class="font-bold text-slate-700">-</span>
                    (<span id="delete-penjualan-karung" class="font-bold text-amber-700">-</span>)
                </p>
                <p class="text-xs text-emerald-700 bg-emerald-50 p-2.5 rounded-lg border border-emerald-200 mb-2">
                    <i class="fa-solid fa-arrows-rotate mr-1"></i>
                    Seluruh stok karung pada transaksi ini akan <b>otomatis dikembalikan</b> ke data Stok Pupuk.
                </p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus transaksi penjualan ini?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeletePenjualan')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus &amp; Kembalikan Stok
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL PENJUALAN 4: INFO DETAIL PENJUALAN PUPUK
======================================================== -->
<div id="modalInfoPenjualan" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog modal-info-hutang-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-circle-info text-blue-600"></i>
                Detail Transaksi Penjualan Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalInfoPenjualan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="custom-modal-body">
            <div class="info-hutang-top-banner">
                <div class="info-hutang-petani-meta">
                    <div class="user-avatar-badge" id="info-penjualan-avatar">PB</div>
                    <div>
                        <h5 id="info-penjualan-nama-pembeli">-</h5>
                        <p id="info-penjualan-sub-pembeli">-</p>
                    </div>
                </div>
                <div id="info-penjualan-kode-wrap"></div>
            </div>

            <div class="info-hutang-detail-grid">
                <div class="info-detail-item">
                    <span class="info-detail-label">Tanggal Penjualan</span>
                    <strong class="info-detail-val" id="info-penjualan-tgl">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Metode Pembayaran</span>
                    <strong class="info-detail-val" id="info-penjualan-metode">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Dicatat Oleh</span>
                    <strong class="info-detail-val" id="info-penjualan-petugas">-</strong>
                </div>
            </div>

            <div class="info-riwayat-section mb-4">
                <div class="info-riwayat-header">
                    <h6>
                        <i class="fa-solid fa-flask-vial text-agro-600"></i>
                        Rincian Item Pupuk Terjual
                    </h6>
                    <span class="info-riwayat-count" id="info-penjualan-items-badge">0 Item</span>
                </div>
                <div class="info-riwayat-table-wrap">
                    <table class="info-riwayat-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pupuk</th>
                                <th>Jumlah Karung</th>
                                <th>Jumlah Berat</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="info-penjualan-items-tbody">
                            <tr>
                                <td colspan="6" class="info-riwayat-empty">Tidak ada rincian item pupuk.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="info-hutang-summary-cards">
                <div class="info-summary-box info-summary-total">
                    <span class="info-summary-title">Total Kuantitas</span>
                    <strong class="info-summary-amount" id="info-penjualan-kuantitas">0 Karung</strong>
                    <small id="info-penjualan-berat">0 Kg</small>
                </div>
                <div class="info-summary-box info-summary-paid">
                    <span class="info-summary-title">Total Penjualan</span>
                    <strong class="info-summary-amount" id="info-penjualan-total">Rp 0</strong>
                </div>
                <div class="info-summary-box" style="background:#f8fafc;border-color:#e2e8f0;color:#334155;">
                    <span class="info-summary-title">Keterangan</span>
                    <strong class="text-xs font-bold block mt-1" id="info-penjualan-keterangan">-</strong>
                </div>
            </div>
        </div>

        <div class="custom-modal-footer">
            <button type="button" class="btn-modal-cancel" onclick="closeModal('modalInfoPenjualan')">Tutup</button>
            <a href="#" target="_blank" rel="noopener" class="btn-modal-save bg-slate-800 hover:bg-slate-900 shadow-slate-800/20" id="btn-info-print-penjualan" style="text-decoration: none;">
                <i class="fa-solid fa-print mr-1"></i>
                Print Nota Penjualan
            </a>
        </div>
    </div>
</div>
