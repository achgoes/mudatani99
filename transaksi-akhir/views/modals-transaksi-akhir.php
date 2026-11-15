<?php
$taFormAction = appUrl('transaksi-akhir/');
?>
<!-- ========================================================
     MODAL 1: TAMBAH TRANSAKSI AKHIR
======================================================== -->
<div id="modalTambahTransaksiAkhir" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalTambahTaTitle">
    <div class="custom-modal-dialog modal-ta-dialog">
        <div class="custom-modal-header">
            <h4 id="modalTambahTaTitle">
                <i class="fa-solid fa-file-invoice-dollar text-agro-600"></i>
                Tambah Transaksi Akhir
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahTransaksiAkhir')" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $taFormAction ?>" method="POST" id="formTambahTransaksiAkhir" novalidate>
            <input type="hidden" name="action" value="add_transaksi_akhir">

            <div class="custom-modal-body">
                <div id="add-ta-alert" class="hidden mb-3 p-3 rounded-xl border border-rose-200 bg-rose-50/95 text-rose-800 text-xs flex items-start gap-2.5 shadow-sm">
                    <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1" id="add-ta-alert-text">
                        Mohon lengkapi semua kolom wajib sebelum menyimpan.
                    </div>
                    <button type="button" class="text-rose-400 hover:text-rose-600 ml-auto" onclick="document.getElementById('add-ta-alert').classList.add('hidden')" aria-label="Tutup peringatan">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <?php if (empty($setoranOptions)): ?>
                <div class="mb-3 p-3 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-amber-600 mt-0.5"></i>
                    <div>
                        Belum ada data <b>Setoran Petani</b>. Silakan catat setoran petani terlebih dahulu pada menu Setoran Petani.
                    </div>
                </div>
                <?php elseif ($availableSetoranCount === 0): ?>
                <div class="mb-3 p-3 rounded-xl border border-blue-200 bg-blue-50 text-blue-900 text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-info text-blue-600 mt-0.5"></i>
                    <div>
                        Seluruh data <b>Setoran Petani</b> yang tercatat sudah diproses di Transaksi Akhir. Tambahkan setoran baru atau gunakan tombol Edit pada tabel.
                    </div>
                </div>
                <?php endif; ?>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-ta-setoran">Pilih Setoran Petani (Nama Petani &amp; Berat Setoran) <span class="text-rose-500">*</span></label>
                    <select id="add-ta-setoran" name="setoran_id" required class="modal-input" onchange="onSelectSetoranTa('add')">
                        <option value="">-- Pilih Data Setoran Petani --</option>
                        <?php foreach ($setoranOptions as $opt):
                            $isUsed = ((int)$opt['used_ta_id'] > 0);
                            $tglOpt = (!empty($opt['tanggal']) && $opt['tanggal'] !== '0000-00-00') ? date('d/m/Y', strtotime($opt['tanggal'])) : '-';
                        ?>
                            <option
                                value="<?= (int)$opt['id'] ?>"
                                data-berat="<?= htmlspecialchars($opt['berat_num']) ?>"
                                data-petani="<?= htmlspecialchars($opt['nama_petani']) ?>"
                                data-kode="<?= htmlspecialchars($opt['kode_setoran']) ?>"
                                data-tanaman="<?= htmlspecialchars($opt['nama_tanaman']) ?>"
                                data-tanggal="<?= htmlspecialchars($tglOpt) ?>"
                                <?= $isUsed ? 'disabled' : '' ?>
                            >
                                <?= htmlspecialchars($opt['nama_petani']) ?> | <?= htmlspecialchars($opt['kode_setoran']) ?> | <?= htmlspecialchars($opt['berat_str']) ?> (<?= htmlspecialchars($tglOpt) ?>)<?= $isUsed ? ' [Sudah Diproses]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Berat setoran diambil otomatis dari total jumlah berat pada menu Setoran Petani.</p>
                </div>

                <!-- Ringkasan Info Setoran Terpilih -->
                <div id="add-ta-setoran-preview" class="hidden ta-setoran-preview">
                    <div>
                        <span class="ta-preview-label">Petani</span>
                        <strong class="ta-preview-val" id="add-ta-prev-petani">-</strong>
                    </div>
                    <div>
                        <span class="ta-preview-label">Komoditas Setoran</span>
                        <strong class="ta-preview-val" id="add-ta-prev-tanaman">-</strong>
                    </div>
                    <div>
                        <span class="ta-preview-label">Tanggal Setoran</span>
                        <strong class="ta-preview-val" id="add-ta-prev-tanggal">-</strong>
                    </div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-ta-berat-setoran">Berat Setoran</label>
                        <input
                            type="text"
                            id="add-ta-berat-setoran"
                            readonly
                            class="modal-input font-bold text-slate-800 bg-slate-100"
                            value="0 Kg"
                        >
                        <p class="modal-helper-text">Diambil dari jumlah berat Setoran Petani.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-ta-rapaksi">Rapaksi (Kg) <span class="text-rose-500">*</span></label>
                        <div class="input-unit-group">
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                id="add-ta-rapaksi"
                                name="rapaksi"
                                required
                                class="modal-input"
                                value="0"
                                placeholder="Contoh: 2.5"
                                oninput="recalcTransaksiAkhir('add')"
                            >
                            <span class="unit-badge">Kg</span>
                        </div>
                        <p class="modal-helper-text">Potongan berat setoran dalam satuan Kg.</p>
                    </div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-ta-berat-akhir">Berat Akhir</label>
                        <input
                            type="text"
                            id="add-ta-berat-akhir"
                            readonly
                            class="modal-input font-bold text-emerald-800 bg-emerald-50/70"
                            value="0 Kg"
                        >
                        <p class="modal-helper-text">Otomatis: Berat Setoran dikurangi Rapaksi.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-ta-harga">Harga /Kg (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-ta-harga"
                            name="harga_per_kg"
                            required
                            class="modal-input font-semibold"
                            placeholder="Contoh: Rp 5.000"
                            oninput="onHargaTaInput('add', this)"
                        >
                        <p class="modal-helper-text">Masukkan harga beli per kilogram.</p>
                    </div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-ta-jumlah-uang">Jumlah Uang (Rp)</label>
                        <input
                            type="text"
                            id="add-ta-jumlah-uang"
                            readonly
                            class="modal-input font-extrabold text-emerald-900 bg-emerald-100/70"
                            value="Rp 0"
                        >
                        <p class="modal-helper-text">Otomatis: Berat Akhir dikali Harga /Kg.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-ta-tanggal">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="add-ta-tanggal"
                            name="tanggal"
                            required
                            class="modal-input"
                            value="<?= date('Y-m-d') ?>"
                        >
                    </div>
                </div>

                <div class="modal-form-group mb-0">
                    <label class="modal-label" for="add-ta-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-ta-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Kualitas grade A, kadar air normal"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahTransaksiAkhir')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Transaksi Akhir
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 2: EDIT TRANSAKSI AKHIR (TOMBOL HAPUS ADA DI DALAMNYA)
======================================================== -->
<div id="modalEditTransaksiAkhir" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalEditTaTitle">
    <div class="custom-modal-dialog modal-ta-dialog">
        <div class="custom-modal-header">
            <h4 id="modalEditTaTitle">
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Transaksi Akhir
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditTransaksiAkhir')" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $taFormAction ?>" method="POST" id="formEditTransaksiAkhir" novalidate>
            <input type="hidden" name="action" value="edit_transaksi_akhir">
            <input type="hidden" id="edit-ta-id" name="id" value="">

            <div class="custom-modal-body">
                <div id="edit-ta-alert" class="hidden mb-3 p-3 rounded-xl border border-rose-200 bg-rose-50/95 text-rose-800 text-xs flex items-start gap-2.5 shadow-sm">
                    <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1" id="edit-ta-alert-text">
                        Mohon lengkapi semua kolom wajib sebelum menyimpan.
                    </div>
                    <button type="button" class="text-rose-400 hover:text-rose-600 ml-auto" onclick="document.getElementById('edit-ta-alert').classList.add('hidden')" aria-label="Tutup peringatan">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-ta-setoran">Data Setoran Petani <span class="text-rose-500">*</span></label>
                    <select id="edit-ta-setoran" name="setoran_id" required class="modal-input" onchange="onSelectSetoranTa('edit')">
                        <option value="">-- Pilih Data Setoran Petani --</option>
                        <?php foreach ($setoranOptions as $opt):
                            $tglOpt = (!empty($opt['tanggal']) && $opt['tanggal'] !== '0000-00-00') ? date('d/m/Y', strtotime($opt['tanggal'])) : '-';
                        ?>
                            <option
                                value="<?= (int)$opt['id'] ?>"
                                data-used-ta-id="<?= (int)$opt['used_ta_id'] ?>"
                                data-berat="<?= htmlspecialchars($opt['berat_num']) ?>"
                                data-petani="<?= htmlspecialchars($opt['nama_petani']) ?>"
                                data-kode="<?= htmlspecialchars($opt['kode_setoran']) ?>"
                                data-tanaman="<?= htmlspecialchars($opt['nama_tanaman']) ?>"
                                data-tanggal="<?= htmlspecialchars($tglOpt) ?>"
                            >
                                <?= htmlspecialchars($opt['nama_petani']) ?> | <?= htmlspecialchars($opt['kode_setoran']) ?> | <?= htmlspecialchars($opt['berat_str']) ?> (<?= htmlspecialchars($tglOpt) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="edit-ta-setoran-preview" class="ta-setoran-preview">
                    <div>
                        <span class="ta-preview-label">Petani</span>
                        <strong class="ta-preview-val" id="edit-ta-prev-petani">-</strong>
                    </div>
                    <div>
                        <span class="ta-preview-label">Komoditas Setoran</span>
                        <strong class="ta-preview-val" id="edit-ta-prev-tanaman">-</strong>
                    </div>
                    <div>
                        <span class="ta-preview-label">Tanggal Setoran</span>
                        <strong class="ta-preview-val" id="edit-ta-prev-tanggal">-</strong>
                    </div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-ta-berat-setoran">Berat Setoran</label>
                        <input
                            type="text"
                            id="edit-ta-berat-setoran"
                            readonly
                            class="modal-input font-bold text-slate-800 bg-slate-100"
                            value="0 Kg"
                        >
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-ta-rapaksi">Rapaksi (Kg) <span class="text-rose-500">*</span></label>
                        <div class="input-unit-group">
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                id="edit-ta-rapaksi"
                                name="rapaksi"
                                required
                                class="modal-input"
                                value="0"
                                oninput="recalcTransaksiAkhir('edit')"
                            >
                            <span class="unit-badge">Kg</span>
                        </div>
                    </div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-ta-berat-akhir">Berat Akhir</label>
                        <input
                            type="text"
                            id="edit-ta-berat-akhir"
                            readonly
                            class="modal-input font-bold text-emerald-800 bg-emerald-50/70"
                            value="0 Kg"
                        >
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-ta-harga">Harga /Kg (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="edit-ta-harga"
                            name="harga_per_kg"
                            required
                            class="modal-input font-semibold"
                            oninput="onHargaTaInput('edit', this)"
                        >
                    </div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-ta-jumlah-uang">Jumlah Uang (Rp)</label>
                        <input
                            type="text"
                            id="edit-ta-jumlah-uang"
                            readonly
                            class="modal-input font-extrabold text-emerald-900 bg-emerald-100/70"
                            value="Rp 0"
                        >
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-ta-tanggal">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="edit-ta-tanggal"
                            name="tanggal"
                            required
                            class="modal-input"
                        >
                    </div>
                </div>

                <div class="modal-form-group mb-0">
                    <label class="modal-label" for="edit-ta-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="edit-ta-keterangan"
                        name="keterangan"
                        class="modal-input"
                    >
                </div>
            </div>

            <div class="custom-modal-footer ta-edit-footer">
                <button type="button" class="btn-modal-delete-inside" onclick="openDeleteFromEditTransaksiAkhir()" title="Hapus data transaksi akhir ini">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Hapus Transaksi</span>
                </button>
                <div class="ta-edit-footer-right">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditTransaksiAkhir')">Batal</button>
                    <button type="submit" class="btn-modal-save">
                        <i class="fa-solid fa-check mr-1"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 3: INFORMASI LANJUTAN TRANSAKSI AKHIR
======================================================== -->
<div id="modalInfoTransaksiAkhir" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalInfoTaTitle">
    <div class="custom-modal-dialog modal-info-ta-dialog">
        <div class="custom-modal-header">
            <h4 id="modalInfoTaTitle">
                <i class="fa-solid fa-circle-info text-blue-600"></i>
                Informasi Lanjutan Transaksi Akhir
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalInfoTransaksiAkhir')" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="custom-modal-body">
            <div class="info-ta-top-banner">
                <div class="info-ta-petani-meta">
                    <div class="user-avatar-badge" id="info-ta-avatar">PT</div>
                    <div>
                        <h5 id="info-ta-nama-petani">-</h5>
                        <p id="info-ta-alamat">-</p>
                    </div>
                </div>
                <div id="info-ta-kode-wrap"></div>
            </div>

            <div class="info-ta-detail-grid">
                <div class="info-detail-item">
                    <span class="info-detail-label">Tanggal Transaksi</span>
                    <strong class="info-detail-val" id="info-ta-tgl-transaksi">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Referensi Setoran</span>
                    <strong class="info-detail-val" id="info-ta-kode-setoran">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Tanggal Setoran</span>
                    <strong class="info-detail-val" id="info-ta-tgl-setoran">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Dicatat Oleh</span>
                    <strong class="info-detail-val" id="info-ta-petugas">-</strong>
                </div>
            </div>

            <div class="info-riwayat-section mb-4">
                <div class="info-riwayat-header">
                    <h6>
                        <i class="fa-solid fa-wheat-awn text-agro-600"></i>
                        Rincian Tanaman dari Setoran Petani
                    </h6>
                    <span class="info-riwayat-count" id="info-ta-items-badge">0 Item</span>
                </div>
                <div class="info-riwayat-table-wrap">
                    <table class="info-riwayat-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis Tanaman</th>
                                <th>Berat Item</th>
                            </tr>
                        </thead>
                        <tbody id="info-ta-items-tbody">
                            <tr>
                                <td colspan="3" class="info-riwayat-empty">Tidak ada rincian item setoran.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="info-ta-calc-grid mb-3">
                <div class="info-calc-box">
                    <span class="info-detail-label">Berat Setoran</span>
                    <strong class="info-calc-val text-slate-800" id="info-ta-berat-setoran">0 Kg</strong>
                </div>
                <div class="info-calc-box info-calc-rapaksi">
                    <span class="info-detail-label">Potongan Rapaksi</span>
                    <strong class="info-calc-val text-rose-700" id="info-ta-rapaksi">0 Kg</strong>
                </div>
                <div class="info-calc-box info-calc-akhir">
                    <span class="info-detail-label">Berat Akhir</span>
                    <strong class="info-calc-val text-emerald-800" id="info-ta-berat-akhir">0 Kg</strong>
                </div>
                <div class="info-calc-box">
                    <span class="info-detail-label">Harga /Kg</span>
                    <strong class="info-calc-val text-blue-800" id="info-ta-harga">Rp 0</strong>
                </div>
                <div class="info-calc-box info-calc-uang">
                    <span class="info-detail-label">Jumlah Uang Diterima</span>
                    <strong class="info-calc-val text-emerald-900" id="info-ta-jumlah-uang">Rp 0</strong>
                </div>
            </div>

            <div class="info-detail-item">
                <span class="info-detail-label">Keterangan</span>
                <strong class="info-detail-val" id="info-ta-keterangan">-</strong>
            </div>
        </div>

        <div class="custom-modal-footer">
            <button type="button" class="btn-modal-cancel" onclick="closeModal('modalInfoTransaksiAkhir')">Tutup</button>
            <a href="#" target="_blank" rel="noopener" class="btn-modal-save bg-slate-800 hover:bg-slate-900 shadow-slate-800/20" id="btn-info-print-ta" style="text-decoration: none;">
                <i class="fa-solid fa-print mr-1"></i>
                Print Bukti Transaksi
            </a>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL 4: KONFIRMASI HAPUS (DIPANGGIL DARI MODAL EDIT)
======================================================== -->
<div id="modalDeleteTransaksiAkhir" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalDeleteTaTitle">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700" id="modalDeleteTaTitle">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Transaksi Akhir
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteTransaksiAkhir')" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $taFormAction ?>" method="POST">
            <input type="hidden" name="action" value="delete_transaksi_akhir">
            <input type="hidden" id="delete-ta-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-ta-petani">-</h5>
                <p class="text-xs text-slate-500 mb-3">
                    Kode: <span id="delete-ta-kode" class="font-bold text-slate-700">-</span>
                    (<span id="delete-ta-uang" class="font-bold text-emerald-700">-</span>)
                </p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2.5 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data transaksi akhir ini? Data Setoran Petani terkait tidak akan ikut terhapus.
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteTransaksiAkhir')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus Transaksi
                </button>
            </div>
        </form>
    </div>
</div>
