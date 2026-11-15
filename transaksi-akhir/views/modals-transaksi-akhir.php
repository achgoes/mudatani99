<?php
$taFormAction = appUrl('transaksi-akhir/');
?>
<!-- ========================================================
     MODAL 1: TAMBAH TRANSAKSI AKHIR
======================================================== -->
<div id="modalTambahTransaksiAkhir" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalTambahTATitle">
    <div class="custom-modal-dialog modal-ta-dialog">
        <div class="custom-modal-header">
            <h4 id="modalTambahTATitle">
                <i class="fa-solid fa-file-invoice text-agro-600"></i>
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

                <?php if (empty($anggotaList) || empty($masterTanamanList)): ?>
                <div class="mb-3 p-3 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-amber-600 mt-0.5"></i>
                    <div>
                        <?php if (empty($anggotaList)): ?>
                            <div>Data <b>Anggota Petani</b> masih kosong. Silakan tambahkan petani pada menu Data Anggota terlebih dahulu.</div>
                        <?php endif; ?>
                        <?php if (empty($masterTanamanList)): ?>
                            <div>Data <b>Master Tanaman</b> masih kosong. Silakan tambahkan jenis tanaman pada menu Master Obat, Pupuk, dan Tanaman terlebih dahulu.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-ta-anggota">Nama Petani (Dari Anggota Petani) <span class="text-rose-500">*</span></label>
                        <select id="add-ta-anggota" name="anggota_id" required class="modal-input">
                            <option value="">-- Pilih Nama Petani --</option>
                            <?php foreach ($anggotaList as $ag): ?>
                                <option value="<?= (int)$ag['id'] ?>" data-nama="<?= htmlspecialchars($ag['nama_petani']) ?>" data-setoran="<?= htmlspecialchars((string)$ag['total_setoran_num']) ?>">
                                    <?= htmlspecialchars($ag['nama_petani']) ?> (Setoran: <?= htmlspecialchars($ag['total_setoran_str']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="modal-helper-text">Menampilkan sisa setoran petani yang belum masuk Transaksi Akhir.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-ta-tanaman">Tanaman (Dari Master Tanaman) <span class="text-rose-500">*</span></label>
                        <select id="add-ta-tanaman" name="tanaman_id" required class="modal-input">
                            <option value="">-- Pilih Jenis Tanaman --</option>
                            <?php foreach ($masterTanamanList as $mt): ?>
                                <option value="<?= (int)$mt['id'] ?>" data-nama="<?= htmlspecialchars($mt['nama_produk']) ?>">
                                    <?= htmlspecialchars($mt['nama_produk']) ?> (Setoran: 0 Kg)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="modal-helper-text" id="add-ta-tanaman-helper">Pilih Nama Petani untuk melihat sisa setoran per tanaman (Kg).</p>
                    </div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-ta-harga">Harga (/Kg) <span class="text-rose-500">*</span></label>
                        <div class="input-berat-group">
                            <span class="berat-unit-badge">Rp</span>
                            <input
                                type="text"
                                inputmode="numeric"
                                id="add-ta-harga"
                                name="harga_per_kg"
                                required
                                class="modal-input ta-harga-input"
                                placeholder="Contoh: 12.500"
                            >
                            <span class="berat-unit-badge">/Kg</span>
                        </div>
                        <p class="modal-helper-text">Masukkan harga beli per kilogram (Rp).</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-ta-tanggal">Tanggal <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="add-ta-tanggal"
                            name="tanggal"
                            required
                            class="modal-input"
                            value="<?= date('Y-m-d') ?>"
                        >
                        <p class="modal-helper-text">Tanggal pencatatan transaksi akhir.</p>
                    </div>
                </div>

                <div class="ta-calc-box">
                    <div class="ta-calc-box-header">
                        <i class="fa-solid fa-calculator text-agro-600"></i>
                        <span>Perhitungan Berat Final &amp; Total Uang</span>
                    </div>

                    <div class="modal-form-grid-3">
                        <div class="modal-form-group mb-0">
                            <label class="modal-label" for="add-ta-jumlah-setoran">Jumlah Setoran <span class="text-rose-500">*</span></label>
                            <input
                                type="text"
                                id="add-ta-jumlah-setoran"
                                name="jumlah_setoran"
                                readonly
                                class="modal-input font-bold text-slate-800 bg-slate-100"
                                value="0 Kg"
                            >
                            <p class="modal-helper-text">Sisa setoran petani pada tanaman yang dipilih.</p>
                        </div>

                        <div class="modal-form-group mb-0">
                            <label class="modal-label" for="add-ta-rapaksi">Rapaksi <span class="text-rose-500">*</span></label>
                            <div class="input-berat-group">
                                <input
                                    type="number"
                                    id="add-ta-rapaksi"
                                    name="rapaksi"
                                    min="0"
                                    step="0.01"
                                    required
                                    class="modal-input"
                                    placeholder="Contoh: 2.5"
                                    value="0"
                                >
                                <span class="berat-unit-badge">Kg</span>
                            </div>
                            <p class="modal-helper-text">Masukkan nilai potongan rapaksi (Kg).</p>
                        </div>

                        <div class="modal-form-group mb-0">
                            <label class="modal-label" for="add-ta-berat-final">Berat Final</label>
                            <input
                                type="text"
                                id="add-ta-berat-final"
                                name="berat_final"
                                readonly
                                class="modal-input font-extrabold text-emerald-800 bg-emerald-50/80 border-emerald-200"
                                value="0 Kg"
                            >
                            <p class="modal-helper-text">Otomatis: Jumlah Setoran - Rapaksi.</p>
                        </div>
                    </div>

                    <div class="modal-form-group mb-0 mt-3">
                        <label class="modal-label" for="add-ta-total-uang">Total Uang</label>
                        <input
                            type="text"
                            id="add-ta-total-uang"
                            readonly
                            class="modal-input font-extrabold text-emerald-900 bg-emerald-50 border-emerald-300"
                            value="Rp 0"
                        >
                        <p class="modal-helper-text">Otomatis: Berat Final &times; Harga (/Kg).</p>
                    </div>
                </div>

                <div class="modal-form-group mb-0">
                    <label class="modal-label" for="add-ta-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-ta-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Potongan kadar air dan kotoran 5%"
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
     MODAL 2: EDIT TRANSAKSI AKHIR
======================================================== -->
<div id="modalEditTransaksiAkhir" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalEditTATitle">
    <div class="custom-modal-dialog modal-ta-dialog">
        <div class="custom-modal-header">
            <h4 id="modalEditTATitle">
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

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-ta-anggota">Nama Petani (Dari Anggota Petani) <span class="text-rose-500">*</span></label>
                        <select id="edit-ta-anggota" name="anggota_id" required class="modal-input">
                            <option value="">-- Pilih Nama Petani --</option>
                            <?php foreach ($anggotaList as $ag): ?>
                                <option value="<?= (int)$ag['id'] ?>" data-nama="<?= htmlspecialchars($ag['nama_petani']) ?>" data-setoran="<?= htmlspecialchars((string)$ag['total_setoran_num']) ?>">
                                    <?= htmlspecialchars($ag['nama_petani']) ?> (Setoran: <?= htmlspecialchars($ag['total_setoran_str']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="modal-helper-text">Menampilkan sisa setoran petani termasuk saldo transaksi ini.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-ta-tanaman">Tanaman (Dari Master Tanaman) <span class="text-rose-500">*</span></label>
                        <select id="edit-ta-tanaman" name="tanaman_id" required class="modal-input">
                            <option value="">-- Pilih Jenis Tanaman --</option>
                            <?php foreach ($masterTanamanList as $mt): ?>
                                <option value="<?= (int)$mt['id'] ?>" data-nama="<?= htmlspecialchars($mt['nama_produk']) ?>">
                                    <?= htmlspecialchars($mt['nama_produk']) ?> (Setoran: 0 Kg)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="modal-helper-text" id="edit-ta-tanaman-helper">Sisa setoran tanaman untuk petani yang dipilih.</p>
                    </div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-ta-harga">Harga (/Kg) <span class="text-rose-500">*</span></label>
                        <div class="input-berat-group">
                            <span class="berat-unit-badge">Rp</span>
                            <input
                                type="text"
                                inputmode="numeric"
                                id="edit-ta-harga"
                                name="harga_per_kg"
                                required
                                class="modal-input ta-harga-input"
                                placeholder="Contoh: 12.500"
                            >
                            <span class="berat-unit-badge">/Kg</span>
                        </div>
                        <p class="modal-helper-text">Ubah harga beli per kilogram (Rp).</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="edit-ta-tanggal">Tanggal <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="edit-ta-tanggal"
                            name="tanggal"
                            required
                            class="modal-input"
                        >
                        <p class="modal-helper-text">Tanggal pencatatan transaksi akhir.</p>
                    </div>
                </div>

                <div class="ta-calc-box">
                    <div class="ta-calc-box-header">
                        <i class="fa-solid fa-calculator text-agro-600"></i>
                        <span>Perhitungan Berat Final &amp; Total Uang</span>
                    </div>

                    <div class="modal-form-grid-3">
                        <div class="modal-form-group mb-0">
                            <label class="modal-label" for="edit-ta-jumlah-setoran">Jumlah Setoran <span class="text-rose-500">*</span></label>
                            <input
                                type="text"
                                id="edit-ta-jumlah-setoran"
                                name="jumlah_setoran"
                                readonly
                                class="modal-input font-bold text-slate-800 bg-slate-100"
                                value="0 Kg"
                            >
                            <p class="modal-helper-text">Sisa setoran tanaman pada petani ini.</p>
                        </div>

                        <div class="modal-form-group mb-0">
                            <label class="modal-label" for="edit-ta-rapaksi">Rapaksi <span class="text-rose-500">*</span></label>
                            <div class="input-berat-group">
                                <input
                                    type="number"
                                    id="edit-ta-rapaksi"
                                    name="rapaksi"
                                    min="0"
                                    step="0.01"
                                    required
                                    class="modal-input"
                                    placeholder="Contoh: 2.5"
                                >
                                <span class="berat-unit-badge">Kg</span>
                            </div>
                            <p class="modal-helper-text">Ubah nilai potongan rapaksi (Kg).</p>
                        </div>

                        <div class="modal-form-group mb-0">
                            <label class="modal-label" for="edit-ta-berat-final">Berat Final</label>
                            <input
                                type="text"
                                id="edit-ta-berat-final"
                                name="berat_final"
                                readonly
                                class="modal-input font-extrabold text-emerald-800 bg-emerald-50/80 border-emerald-200"
                                value="0 Kg"
                            >
                            <p class="modal-helper-text">Otomatis: Jumlah Setoran - Rapaksi.</p>
                        </div>
                    </div>

                    <div class="modal-form-group mb-0 mt-3">
                        <label class="modal-label" for="edit-ta-total-uang">Total Uang</label>
                        <input
                            type="text"
                            id="edit-ta-total-uang"
                            readonly
                            class="modal-input font-extrabold text-emerald-900 bg-emerald-50 border-emerald-300"
                            value="Rp 0"
                        >
                        <p class="modal-helper-text">Otomatis: Berat Final &times; Harga (/Kg).</p>
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

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditTransaksiAkhir')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 3: INFO LENGKAP TRANSAKSI AKHIR
======================================================== -->
<div id="modalInfoTransaksiAkhir" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalInfoTATitle">
    <div class="custom-modal-dialog modal-info-ta-dialog">
        <div class="custom-modal-header">
            <h4 id="modalInfoTATitle">
                <i class="fa-solid fa-circle-info text-blue-600"></i>
                Info Lengkap Transaksi Akhir
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

            <div class="info-ta-calc-grid">
                <div class="info-calc-card">
                    <span class="info-detail-label">Jumlah Setoran</span>
                    <strong class="info-calc-val text-blue-800" id="info-ta-jumlah-setoran">0 Kg</strong>
                </div>
                <div class="info-calc-card info-calc-card-amber">
                    <span class="info-detail-label">Rapaksi</span>
                    <strong class="info-calc-val text-amber-800" id="info-ta-rapaksi">0 Kg</strong>
                </div>
                <div class="info-calc-card info-calc-card-green">
                    <span class="info-detail-label">Berat Final</span>
                    <strong class="info-calc-val text-emerald-800" id="info-ta-berat-final">0 Kg</strong>
                </div>
            </div>

            <div class="modal-form-grid-2 mb-3">
                <div class="info-detail-item">
                    <span class="info-detail-label">Tanaman (Master Tanaman)</span>
                    <strong class="info-detail-val" id="info-ta-tanaman">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Harga (/Kg)</span>
                    <strong class="info-detail-val text-emerald-700" id="info-ta-harga">Rp 0 /Kg</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Total Uang (Berat Final &times; Harga)</span>
                    <strong class="info-detail-val text-emerald-800" id="info-ta-total-uang">Rp 0</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Tanggal Transaksi</span>
                    <strong class="info-detail-val" id="info-ta-tgl">-</strong>
                </div>
            </div>

            <div class="modal-form-grid-2 mb-4">
                <div class="info-detail-item">
                    <span class="info-detail-label">Dicatat Oleh</span>
                    <strong class="info-detail-val" id="info-ta-petugas">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Keterangan</span>
                    <strong class="info-detail-val" id="info-ta-keterangan">-</strong>
                </div>
            </div>

            <div class="info-riwayat-section">
                <div class="info-riwayat-header">
                    <h6>
                        <i class="fa-solid fa-scale-balanced text-agro-600"></i>
                        Rincian Data Setoran Petani Terkait
                    </h6>
                    <span class="info-riwayat-count" id="info-ta-setoran-badge">0 Nota</span>
                </div>
                <div class="info-riwayat-table-wrap">
                    <table class="info-riwayat-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode &amp; Tanggal</th>
                                <th>Tanaman Disetor</th>
                                <th>Berat Setoran</th>
                            </tr>
                        </thead>
                        <tbody id="info-ta-setoran-tbody">
                            <tr>
                                <td colspan="4" class="info-riwayat-empty">Belum ada rincian setoran petani.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="custom-modal-footer">
            <button type="button" class="btn-modal-cancel" onclick="closeModal('modalInfoTransaksiAkhir')">Tutup</button>
            <a href="#" target="_blank" rel="noopener" class="btn-modal-save bg-slate-800 hover:bg-slate-900 shadow-slate-800/20" id="btn-info-print-ta" style="text-decoration: none;">
                <i class="fa-solid fa-print mr-1"></i>
                Print Transaksi Akhir
            </a>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL 4: KONFIRMASI HAPUS TRANSAKSI AKHIR
======================================================== -->
<div id="modalDeleteTransaksiAkhir" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalDeleteTATitle">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700" id="modalDeleteTATitle">
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
                    (Berat Final: <span id="delete-ta-berat" class="font-bold text-emerald-700">-</span>)
                </p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2.5 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data transaksi akhir ini? Tindakan ini tidak dapat dibatalkan.
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
