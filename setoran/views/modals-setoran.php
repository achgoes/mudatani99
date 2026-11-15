<?php
$setoranFormAction = appUrl('setoran/');
?>
<!-- ========================================================
     MODAL 1: TAMBAH SETORAN PETANI (MULTI-ITEM TANAMAN)
======================================================== -->
<div id="modalTambahSetoran" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalTambahSetoranTitle">
    <div class="custom-modal-dialog modal-setoran-dialog">
        <div class="custom-modal-header">
            <h4 id="modalTambahSetoranTitle">
                <i class="fa-solid fa-scale-balanced text-agro-600"></i>
                Tambah Setoran Petani
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahSetoran')" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $setoranFormAction ?>" method="POST" id="formTambahSetoran" novalidate>
            <input type="hidden" name="action" value="add_setoran">

            <div class="custom-modal-body">
                <div id="add-setoran-alert" class="hidden mb-3 p-3 rounded-xl border border-rose-200 bg-rose-50/95 text-rose-800 text-xs flex items-start gap-2.5 shadow-sm">
                    <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1" id="add-setoran-alert-text">
                        Mohon lengkapi semua kolom wajib sebelum menyimpan.
                    </div>
                    <button type="button" class="text-rose-400 hover:text-rose-600 ml-auto" onclick="document.getElementById('add-setoran-alert').classList.add('hidden')" aria-label="Tutup peringatan">
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
                        <label class="modal-label" for="add-setoran-anggota">Nama Petani (Dari Anggota Petani) <span class="text-rose-500">*</span></label>
                        <select id="add-setoran-anggota" name="anggota_id" required class="modal-input">
                            <option value="">-- Pilih Nama Petani --</option>
                            <?php foreach ($anggotaList as $ag): ?>
                                <option value="<?= (int)$ag['id'] ?>">
                                    <?= htmlspecialchars($ag['nama_petani']) ?> (<?= htmlspecialchars($ag['alamat']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="modal-helper-text">Data petani diambil langsung dari Daftar Anggota Petani.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-setoran-tanggal">Tanggal Setoran <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="add-setoran-tanggal"
                            name="tanggal"
                            required
                            class="modal-input"
                            value="<?= date('Y-m-d') ?>"
                        >
                        <p class="modal-helper-text">Tanggal pencatatan setoran tanaman petani.</p>
                    </div>
                </div>

                <!-- Daftar Item Setoran Tanaman & Berat per Item -->
                <div class="setoran-items-section">
                    <div class="setoran-items-header">
                        <div>
                            <span class="modal-label mb-0">Daftar Setor Tanaman &amp; Berat per Item <span class="text-rose-500">*</span></span>
                            <p class="modal-helper-text mt-0.5">Jenis tanaman diambil dari Master Tanaman. Tambahkan item jika petani menyetor lebih dari satu komoditas.</p>
                        </div>
                        <button type="button" class="btn-add-setoran-item" onclick="addSetoranItemRow()">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Item</span>
                        </button>
                    </div>
                    <div id="add-setoran-items-container" class="setoran-items-container"></div>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="add-setoran-total-berat">Jumlah Berat Total <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-setoran-total-berat"
                            name="jumlah_berat"
                            readonly
                            class="modal-input font-bold text-emerald-800 bg-emerald-50/70"
                            value="0 Kg"
                        >
                        <p class="modal-helper-text">Otomatis dijumlahkan dari berat seluruh item tanaman.</p>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="add-setoran-keterangan">Keterangan</label>
                        <input
                            type="text"
                            id="add-setoran-keterangan"
                            name="keterangan"
                            class="modal-input"
                            placeholder="Contoh: Setoran panen perdana blok utara"
                        >
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahSetoran')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Setoran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 2: INFO LENGKAP SETORAN PETANI
======================================================== -->
<div id="modalInfoSetoran" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalInfoSetoranTitle">
    <div class="custom-modal-dialog modal-info-setoran-dialog">
        <div class="custom-modal-header">
            <h4 id="modalInfoSetoranTitle">
                <i class="fa-solid fa-circle-info text-blue-600"></i>
                Info Lengkap Setoran Petani
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalInfoSetoran')" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="custom-modal-body">
            <div class="info-setoran-top-banner">
                <div class="info-setoran-petani-meta">
                    <div class="user-avatar-badge" id="info-setoran-avatar">PT</div>
                    <div>
                        <h5 id="info-setoran-nama-petani">-</h5>
                        <p id="info-setoran-alamat">-</p>
                    </div>
                </div>
                <div id="info-setoran-kode-wrap"></div>
            </div>

            <div class="info-setoran-detail-grid">
                <div class="info-detail-item">
                    <span class="info-detail-label">Tanggal Setoran</span>
                    <strong class="info-detail-val" id="info-setoran-tgl">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Jumlah Berat Total</span>
                    <strong class="info-detail-val text-emerald-700" id="info-setoran-total-berat">0 Kg</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Dicatat Oleh</span>
                    <strong class="info-detail-val" id="info-setoran-petugas">-</strong>
                </div>
            </div>

            <div class="info-riwayat-section mb-4">
                <div class="info-riwayat-header">
                    <h6>
                        <i class="fa-solid fa-wheat-awn text-agro-600"></i>
                        Rincian Item Tanaman Disetor
                    </h6>
                    <span class="info-riwayat-count" id="info-setoran-items-badge">0 Item</span>
                </div>
                <div class="info-riwayat-table-wrap">
                    <table class="info-riwayat-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Setor Tanaman</th>
                                <th>Berat</th>
                            </tr>
                        </thead>
                        <tbody id="info-setoran-items-tbody">
                            <tr>
                                <td colspan="3" class="info-riwayat-empty">Tidak ada rincian item setoran.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="info-detail-item">
                <span class="info-detail-label">Keterangan</span>
                <strong class="info-detail-val" id="info-setoran-keterangan">-</strong>
            </div>
        </div>

        <div class="custom-modal-footer">
            <button type="button" class="btn-modal-cancel" onclick="closeModal('modalInfoSetoran')">Tutup</button>
            <a href="#" target="_blank" rel="noopener" class="btn-modal-save bg-slate-800 hover:bg-slate-900 shadow-slate-800/20" id="btn-info-print-setoran" style="text-decoration: none;">
                <i class="fa-solid fa-print mr-1"></i>
                Print Bukti Setoran
            </a>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL 3: KONFIRMASI HAPUS SETORAN PETANI
======================================================== -->
<div id="modalDeleteSetoran" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalDeleteSetoranTitle">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700" id="modalDeleteSetoranTitle">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Setoran Petani
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteSetoran')" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= $setoranFormAction ?>" method="POST">
            <input type="hidden" name="action" value="delete_setoran">
            <input type="hidden" id="delete-setoran-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-setoran-petani">-</h5>
                <p class="text-xs text-slate-500 mb-3">
                    Kode: <span id="delete-setoran-kode" class="font-bold text-slate-700">-</span>
                    (<span id="delete-setoran-berat" class="font-bold text-emerald-700">-</span>)
                </p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2.5 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data setoran petani ini? Data item setoran terkait juga akan dihapus permanen.
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteSetoran')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus Setoran
                </button>
            </div>
        </form>
    </div>
</div>
