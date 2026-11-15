<!-- =========================================================
     MANAJEMEN TRANSAKSI AKHIR - DAFTAR TRANSAKSI AKHIR
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Transaksi</h5>
            <div class="stat-value"><?= count($transaksiList) ?></div>
            <div class="stat-desc"><?= count($petaniTransaksiIds) ?> petani tercatat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-file-invoice"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Jumlah Setoran</h5>
            <div class="stat-value"><?= htmlspecialchars(formatBeratSetoranStr($totalJumlahSetoran)) ?></div>
            <div class="stat-desc">Akumulasi berat kotor setoran</div>
        </div>
        <div class="stat-icon-wrap stat-icon-indigo">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Rapaksi</h5>
            <div class="stat-value"><?= htmlspecialchars(formatBeratSetoranStr($totalRapaksi)) ?></div>
            <div class="stat-desc">Akumulasi potongan rapaksi</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-scissors"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Berat Final</h5>
            <div class="stat-value"><?= htmlspecialchars(formatBeratSetoranStr($totalBeratFinal)) ?></div>
            <div class="stat-desc">Jumlah setoran dikurangi rapaksi</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-weight-scale"></i>
        </div>
    </div>
</div>

<!-- Tabel Daftar Transaksi Akhir -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Transaksi Akhir Petani</h3>
            <p>Menampilkan <?= count($transaksiList) ?> transaksi akhir berdasarkan data setoran petani, tanaman, rapaksi, dan harga per Kg.</p>
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
                <input type="text" id="ta-search-input" placeholder="Cari petani, tanaman, total uang, ket..." onkeyup="filterTransaksiAkhirTable()">
            </div>

            <!-- Tombol Tambah Transaksi Akhir -->
            <button type="button" class="btn-add-user" onclick="openModalTambahTransaksiAkhir()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Transaksi Akhir</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-transaksi-akhir-data">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Nama Petani</th>
                    <th>Tanaman</th>
                    <th>Jumlah Setoran</th>
                    <th>Rapaksi</th>
                    <th>Berat Final</th>
                    <th>Harga (/Kg)</th>
                    <th>Total Uang</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-ta-tbody">
                <?php if (empty($transaksiList)): ?>
                <tr id="ta-empty-default">
                    <td colspan="11" class="table-empty-cell">
                        <i class="fa-solid fa-file-invoice text-3xl mb-2 block"></i>
                        Belum ada data transaksi akhir. Silakan klik tombol <b>Tambah Transaksi Akhir</b> untuk mencatat perhitungan berat final.
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
                            $tglIso       = preg_match('/^\d{4}-\d{2}-\d{2}/', (string)$row['tanggal'], $mTgl) ? $mTgl[0] : '';
                            $tglFormatted = ($tglIso !== '') ? date('d/m/Y', strtotime($tglIso)) : '-';
                    ?>
                    <tr class="ta-row-item" data-tanggal="<?= htmlspecialchars($tglIso) ?>">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge"><?= htmlspecialchars($initials) ?></div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_petani']) ?></h4>
                                    <span class="badge-kode-transaksi"><?= htmlspecialchars($row['kode_transaksi']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-nama-tanaman" title="Diambil dari Master Tanaman">
                                <i class="fa-solid fa-wheat-awn"></i>
                                <?= htmlspecialchars($row['nama_tanaman']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-jumlah-setoran" title="Diambil dari jumlah berat di Setoran Petani">
                                <i class="fa-solid fa-scale-balanced"></i>
                                <?= htmlspecialchars($row['jumlah_setoran']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-rapaksi" title="Nilai potongan rapaksi">
                                <i class="fa-solid fa-scissors"></i>
                                <?= htmlspecialchars($row['rapaksi']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-berat-final" title="Hasil perhitungan Jumlah Setoran dikurangi Rapaksi">
                                <i class="fa-solid fa-weight-scale"></i>
                                <?= htmlspecialchars($row['berat_final']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-harga-kg" title="Harga per kilogram">
                                <i class="fa-solid fa-tag"></i>
                                <?= htmlspecialchars($row['harga_per_kg_fmt']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-total-uang" title="Total uang (Berat Final dikali Harga per Kg)">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <?= htmlspecialchars($row['jumlah_uang_fmt']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-tanggal-ta">
                                <i class="fa-regular fa-calendar-days text-slate-500"></i>
                                <?= htmlspecialchars($tglFormatted) ?>
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
                                    title="Lihat info lengkap transaksi akhir"
                                >
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>Info Lengkap</span>
                                </button>

                                <a
                                    href="<?= htmlspecialchars(appUrl('transaksi-akhir/print-transaksi-akhir.php?id=' . (int)$row['id'])) ?>"
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
                                    title="Edit data transaksi akhir"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeleteTransaksiAkhir(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['kode_transaksi'])) ?>', '<?= htmlspecialchars(addslashes($row['nama_petani'])) ?>', '<?= htmlspecialchars(addslashes($row['berat_final'])) ?>')"
                                    title="Hapus data transaksi akhir"
                                >
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="ta-empty-filter" class="hidden">
                        <td colspan="11" class="table-empty-cell">
                            <i class="fa-solid fa-filter-circle-xmark text-3xl mb-2 block"></i>
                            Tidak ada data transaksi akhir yang cocok dengan pencarian atau rentang tanggal yang dipilih.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
