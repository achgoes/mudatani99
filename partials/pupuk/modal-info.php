<!-- ========================================================
     MODAL 8: INFO DETAIL HUTANG & JEJAK PEMBAYARAN
======================================================== -->
<div id="modalInfoHutang" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog modal-info-hutang-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-circle-info text-blue-600"></i>
                Info Detail Hutang &amp; Jejak Pembayaran
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalInfoHutang')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="custom-modal-body">
            <!-- Header Identitas Petani & Status -->
            <div class="info-hutang-top-banner">
                <div class="info-hutang-petani-meta">
                    <div class="user-avatar-badge" id="info-hutang-avatar">PT</div>
                    <div>
                        <h5 id="info-hutang-nama-petani">-</h5>
                        <p id="info-hutang-sub-petani">ID Anggota: #0</p>
                    </div>
                </div>
                <div id="info-hutang-status-wrap"></div>
            </div>

            <!-- Grid Detail Pinjaman Pupuk -->
            <div class="info-hutang-detail-grid">
                <div class="info-detail-item">
                    <span class="info-detail-label">Nama Pupuk</span>
                    <strong class="info-detail-val" id="info-hutang-pupuk">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Kuantitas Pinjam</span>
                    <strong class="info-detail-val" id="info-hutang-kuantitas">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Harga Satuan</span>
                    <strong class="info-detail-val" id="info-hutang-harga">Rp 0</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Tanggal Pinjam</span>
                    <strong class="info-detail-val" id="info-hutang-tgl-pinjam">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Tanggal Lunas</span>
                    <strong class="info-detail-val" id="info-hutang-tgl-lunas">-</strong>
                </div>
                <div class="info-detail-item">
                    <span class="info-detail-label">Keterangan Pinjaman</span>
                    <strong class="info-detail-val" id="info-hutang-keterangan">-</strong>
                </div>
            </div>

            <!-- Ringkasan Nominal Tagihan (3 Kotak) -->
            <div class="info-hutang-summary-cards">
                <div class="info-summary-box info-summary-total">
                    <span class="info-summary-title">Total Hutang Awal</span>
                    <strong class="info-summary-amount" id="info-hutang-total">Rp 0</strong>
                </div>
                <div class="info-summary-box info-summary-paid">
                    <span class="info-summary-title">Total Sudah Dibayar</span>
                    <strong class="info-summary-amount" id="info-hutang-terbayar">Rp 0</strong>
                    <small id="info-hutang-kali-bayar">0 kali pembayaran</small>
                </div>
                <div class="info-summary-box info-summary-remaining">
                    <span class="info-summary-title">Sisa Hutang</span>
                    <strong class="info-summary-amount" id="info-hutang-sisa">Rp 0</strong>
                </div>
            </div>

            <!-- Tabel Jejak / Riwayat Pembayaran -->
            <div class="info-riwayat-section">
                <div class="info-riwayat-header">
                    <h6>
                        <i class="fa-solid fa-clock-rotate-left text-agro-600"></i>
                        Jejak Riwayat Pembayaran Petani
                    </h6>
                    <span class="info-riwayat-count" id="info-riwayat-badge">0 Transaksi</span>
                </div>

                <div class="info-riwayat-table-wrap">
                    <table class="info-riwayat-table">
                        <thead>
                            <tr>
                                <th>Tahap</th>
                                <th>Tanggal Bayar</th>
                                <th>Sisa Sebelum</th>
                                <th>Jumlah Bayar</th>
                                <th>Sisa Hutang</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="info-riwayat-tbody">
                            <tr>
                                <td colspan="8" class="info-riwayat-empty">Belum ada riwayat pembayaran untuk hutang ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="custom-modal-footer">
            <button type="button" class="btn-modal-cancel" onclick="closeModal('modalInfoHutang')">Tutup</button>
            <button type="button" class="btn-modal-save bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30" id="btn-info-bayar-sekarang" style="display: none;">
                <i class="fa-solid fa-money-bill-wave mr-1"></i>
                Bayar Hutang Ini
            </button>
        </div>
    </div>
</div>

