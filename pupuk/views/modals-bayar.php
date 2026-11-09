<!-- ========================================================
     MODAL 7: BAYAR HUTANG PUPUK (POP UP BAYAR JUMLAH RUPIAH)
======================================================== -->
<div id="modalBayarHutang" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-md">
        <div class="custom-modal-header bg-emerald-50/70 border-b border-emerald-100">
            <h4 class="text-emerald-800">
                <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                Bayar Hutang Pupuk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalBayarHutang')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('pupuk/?view=' . urlencode($activeView === 'kelola' ? 'hutang' : $activeView)) ?>" method="POST">
            <input type="hidden" name="action" value="pay_hutang">
            <input type="hidden" name="return_view" value="<?= htmlspecialchars($activeView === 'kelola' ? 'hutang' : $activeView) ?>">
            <input type="hidden" id="bayar-id" name="id" value="">

            <div class="custom-modal-body">
                <!-- Info Ringkasan Tagihan -->
                <div class="p-3.5 mb-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200">
                    <div class="flex items-center justify-between mb-2 pb-2 border-b border-emerald-200/60">
                        <span class="text-xs text-slate-500 font-medium">Petani</span>
                        <strong class="text-sm text-slate-800" id="bayar-petani-display">-</strong>
                    </div>
                    <div class="flex items-center justify-between mb-2 pb-2 border-b border-emerald-200/60">
                        <span class="text-xs text-slate-500 font-medium">Pupuk &amp; Jumlah</span>
                        <span class="text-xs text-slate-700 font-semibold" id="bayar-pupuk-display">-</span>
                    </div>
                    <div class="flex items-center justify-between mb-2 pb-2 border-b border-emerald-200/60">
                        <span class="text-xs text-slate-500 font-medium">Jumlah Hutang Total</span>
                        <span class="text-xs text-blue-800 font-bold" id="bayar-jumlah-hutang-display">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-emerald-700 font-bold uppercase tracking-wider">Sisa Tagihan Hutang</span>
                        <span class="text-base font-extrabold text-emerald-700" id="bayar-tagihan-display">Rp 0</span>
                    </div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="bayar-nominal">Jumlah Pembayaran (Rupiah) <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="bayar-nominal"
                        name="bayar_nominal"
                        required
                        class="modal-input font-bold text-emerald-800 text-base"
                        placeholder="Contoh: Rp 750.000"
                        oninput="formatInputRupiah(this)"
                    >
                    <p class="modal-helper-text">Masukkan jumlah rupiah yang dibayarkan oleh petani untuk pelunasan.</p>
                </div>

                <div class="modal-form-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="bayar-metode">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select id="bayar-metode" name="metode_bayar" required class="modal-input">
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                            <option value="Potong Hasil Panen">Potong Hasil Panen</option>
                        </select>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="bayar-tanggal">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            id="bayar-tanggal"
                            name="tanggal_bayar"
                            required
                            class="modal-input"
                            value="<?= date('Y-m-d') ?>"
                        >
                    </div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="bayar-catatan">Catatan / Keterangan Pembayaran</label>
                    <input
                        type="text"
                        id="bayar-catatan"
                        name="catatan_bayar"
                        class="modal-input"
                        placeholder="Contoh: Pelunasan tunai via bendahara poktan"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalBayarHutang')">Batal</button>
                <button type="submit" class="btn-modal-save bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30">
                    <i class="fa-solid fa-check-double mr-1"></i>
                    Konfirmasi &amp; Lunasi
                </button>
            </div>
        </form>
    </div>
</div>
