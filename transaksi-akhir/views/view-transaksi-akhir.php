<!-- =========================================================
     MANAJEMEN SETORAN PETANI - TRANSAKSI AKHIR
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Transaksi Akhir</h5>
            <div class="stat-value"><?= count($transaksiList) ?></div>
            <div class="stat-desc">Setoran petani telah diproses</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Berat Akhir</h5>
            <div class="stat-value"><?= htmlspecialchars(formatBeratTaStr($totalBeratAkhirSum)) ?></div>
            <div class="stat-desc">Berat bersih setelah rapaksi</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Jumlah Uang</h5>
            <div class="stat-value stat-value-rupiah"><?= htmlspecialchars(formatRupiahTaStr($totalJumlahUangSum)) ?></div>
            <div class="stat-desc">Akumulasi nilai transaksi akhir</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Transaksi Bulan Ini</h5>
            <div class="stat-value"><?= $bulanIniCount ?> <span class="text-sm font-semibold">Nota</span></div>
            <div class="stat-desc">Total: <?= htmlspecialchars(formatRupiahTaStr($bulanIniUangSum)) ?></div>
        </div>
        <div class="stat-icon-wrap stat-icon-rose">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
    </div>
</div>

<!-- Tabel Daftar Transaksi Akhir -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Transaksi Akhir Setoran Petani</h3>
            <p>Menampilkan <?= count($transaksiList) ?> transaksi akhir hasil kalkulasi berat setoran dikurangi rapaksi dikali harga per Kg.</p>
        </div>

        <div class="table-card-actions">
            <!-- Filter Rentang Tanggal -->
            <div class="ta-date-filter-group">
                <input type="date" id="ta-date-from" class="ta-date-input" aria-label="Dari Tanggal" title="Dari Tanggal">
                <span class="ta-date-sep">s/d</span>
                <input type="date" id="ta-date-to" class="ta-date-input" aria-label="Sampai Tanggal" title="Sampai Tanggal">
                <button type="button" class="btn-filter-tanggal" onclick="filterTransaksiAkhirTable()" title="Terapkan Filter Tanggal">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
            </div>

            <!-- Pencarian -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="ta-search-input" placeholder="Cari petani, kode, keterangan..." onkeyup="filterTransaksiAkhirTable()">
            </div>

            <!-- Tombol Tambah Transaksi Akhir -->
            <button type="button" class="btn-add-user" onclick="openModalTambahTransaksiAkhir()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Transaksi Akhir</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-ta-data">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Nama Petani</th>
                    <th>Berat Setoran</th>
                    <th>Rapaksi</th>
                    <th>Berat Akhir</th>
                    <th>Harga /Kg</th>
                    <th>Jumlah Uang</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-ta-tbody">
                <?php if (empty($transaksiList)): ?>
                <tr id="ta-empty-default">
                    <td colspan="9" class="table-empty-cell">
                        <i class="fa-solid fa-file-invoice-dollar text-3xl mb-2 block"></i>
                        Belum ada data transaksi akhir. Silakan klik tombol <b>Tambah Transaksi Akhir</b> untuk menghitung setoran petani.
                    </td>
                </tr>
                <?php else: ?>
                    <?php
                        $no = 1;
                        foreach ($transaksiList as $row):
                            $nParts = preg_split('/\s+/', trim($row['nama_petani']));
                            if (count($nParts) >= 2) {
                                $initials = strtoupper(mb_substr($nParts[0], 0, 1) . mb_substr($nParts[1], 0, 1));
                            } else {
                                $initials = strtoupper(mb_substr($row['nama_petani'], 0, 2));
                            }
                            $tglIso = preg_match('/^\d{4}-\d{2}-\d{2}/', (string)$row['tanggal'], $mTgl) ? $mTgl[0] : '';
                    ?>
                    <tr class="ta-row-item" data-tanggal="<?= htmlspecialchars($tglIso) ?>">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge"><?= htmlspecialchars($initials) ?></div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_petani']) ?></h4>
                                    <span class="badge-kode-ta"><?= htmlspecialchars($row['kode_transaksi']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-berat-setoran" title="Total berat dari Setoran Petani">
                                <i class="fa-solid fa-scale-balanced"></i>
                                <?= htmlspecialchars($row['berat_setoran_str']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-rapaksi" title="Potongan rapaksi">
                                <i class="fa-solid fa-minus"></i>
                                <?= htmlspecialchars($row['rapaksi_str']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="nik-badge" title="Berat Akhir = Berat Setoran - Rapaksi">
                                <i class="fa-solid fa-weight-hanging text-agro-600"></i>
                                <?= htmlspecialchars($row['berat_akhir_str']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-harga-kg">
                                <i class="fa-solid fa-tag"></i>
                                <?= htmlspecialchars($row['harga_per_kg_str']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-jumlah-uang" title="Jumlah Uang = Berat Akhir x Harga /Kg">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <?= htmlspecialchars($row['jumlah_uang_str']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-keterangan">
                                <i class="fa-regular fa-note-sticky"></i>
                                <?= htmlspecialchars($row['keterangan'] ?: '-') ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-btn-group justify-end">
                                <button
                                    type="button"
                                    class="btn-table-info"
                                    onclick="openModalInfoTransaksiAkhir(<?= (int)$row['id'] ?>)"
                                    title="Lihat informasi lanjutan transaksi akhir"
                                >
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>Info</span>
                                </button>

                                <a
                                    href="<?= htmlspecialchars(appUrl('transaksi-akhir/print-transaksi.php?id=' . (int)$row['id'])) ?>"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn-table-print"
                                    title="Cetak bukti transaksi akhir"
                                >
                                    <i class="fa-solid fa-print"></i>
                                    <span>Print</span>
                                </a>

                                <button
                                    type="button"
                                    class="btn-table-edit"
                                    onclick="openModalEditTransaksiAkhir(<?= (int)$row['id'] ?>)"
                                    title="Edit atau hapus transaksi akhir"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="ta-empty-filter" class="hidden">
                        <td colspan="9" class="table-empty-cell">
                            <i class="fa-solid fa-filter-circle-xmark text-3xl mb-2 block"></i>
                            Tidak ada data transaksi akhir yang cocok dengan pencarian atau rentang tanggal yang dipilih.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
