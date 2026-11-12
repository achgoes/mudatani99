<!-- =========================================================
     TAMPILAN 3: PENJUALAN OBAT (BERDIRI SENDIRI)
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Transaksi</h5>
            <div class="stat-value"><?= count($penjualanList) ?></div>
            <div class="stat-desc">Nota penjualan obat tercatat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Pendapatan</h5>
            <div class="stat-value stat-value-rupiah"><?= htmlspecialchars(formatRupiahStr($totalPendapatanPenjualan)) ?></div>
            <div class="stat-desc">Akumulasi nilai penjualan obat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-money-bill-trend-up"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Obat Terjual</h5>
            <div class="stat-value"><?= number_format($totalKemasanPenjualan, 0, ',', '.') ?> <span class="text-sm font-semibold">Kemasan</span></div>
            <div class="stat-desc">Total berat: <?= number_format($totalBeratPenjualan, 0, ',', '.') ?> Kg</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-prescription-bottle-medical"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Penjualan Bulan Ini</h5>
            <div class="stat-value"><?= $penjualanBulanIniCount ?> <span class="text-sm font-semibold">Nota</span></div>
            <div class="stat-desc">Omzet: <?= htmlspecialchars(formatRupiahStr($penjualanBulanIniNominal)) ?></div>
        </div>
        <div class="stat-icon-wrap stat-icon-rose">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
    </div>
</div>

<!-- Tabel Transaksi Penjualan Obat -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Transaksi Penjualan Obat</h3>
            <p>Menampilkan <?= count($penjualanList) ?> transaksi penjualan obat (stok otomatis berkurang dari Stok Obat).</p>
        </div>

        <div class="table-card-actions">
            <!-- Filter Rentang Tanggal -->
            <div class="obat-date-filter-group">
                <input type="date" id="penjualan-date-from" class="obat-date-input" aria-label="Dari Tanggal" title="Dari Tanggal">
                <span class="obat-date-sep">s/d</span>
                <input type="date" id="penjualan-date-to" class="obat-date-input" aria-label="Sampai Tanggal" title="Sampai Tanggal">
                <button type="button" class="btn-filter-tanggal" onclick="applyPenjualanDateFilter()" title="Terapkan Filter Tanggal">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
            </div>

            <!-- Filter Metode Bayar -->
            <select id="penjualan-metode-filter" onchange="filterPenjualanTable()" class="obat-filter-select" aria-label="Filter Metode Bayar">
                <option value="ALL">Semua Metode</option>
                <option value="Tunai">Tunai</option>
                <option value="Transfer">Transfer</option>
                <option value="QRIS">QRIS</option>
            </select>

            <!-- Pencarian -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="penjualan-search-input" placeholder="Cari nota, pembeli, obat, metode..." onkeyup="filterPenjualanTable()">
            </div>

            <!-- Tombol Tambah Penjualan -->
            <button type="button" class="btn-add-user" onclick="openModalTambahPenjualan()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Penjualan</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-penjualan-data">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Kode &amp; Tanggal</th>
                    <th>Nama Pembeli</th>
                    <th>Nama Obat</th>
                    <th>Jumlah Kemasan</th>
                    <th>Jumlah Berat</th>
                    <th>Harga Satuan</th>
                    <th>Total Harga</th>
                    <th>Metode Bayar</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-penjualan-tbody">
                <?php if (empty($penjualanList)): ?>
                <tr>
                    <td colspan="11" class="table-empty-cell">
                        <i class="fa-solid fa-cart-shopping text-3xl mb-2 block"></i>
                        Belum ada transaksi penjualan obat. Silakan klik tombol <b>Tambah Penjualan</b> untuk mencatat penjualan baru.
                    </td>
                </tr>
                <?php else: ?>
                    <?php
                        $metodeIconMap = [
                            'Tunai'    => 'fa-money-bill',
                            'Transfer' => 'fa-building-columns',
                            'QRIS'     => 'fa-qrcode',
                        ];
                        $no = 1;
                        foreach ($penjualanList as $row):
                            $nParts = preg_split('/\s+/', trim($row['nama_pembeli']));
                            if (count($nParts) >= 2) {
                                $initials = strtoupper(mb_substr($nParts[0], 0, 1) . mb_substr($nParts[1], 0, 1));
                            } else {
                                $initials = strtoupper(mb_substr($row['nama_pembeli'], 0, 2));
                            }
                            $tglIso       = preg_match('/^\d{4}-\d{2}-\d{2}/', (string)$row['tanggal_jual'], $mTgl) ? $mTgl[0] : '';
                            $tglFormatted = ($tglIso !== '') ? date('d/m/Y', strtotime($tglIso)) : '-';
                            $mIcon        = $metodeIconMap[$row['metode_bayar']] ?? 'fa-money-bill';
                            $isAnggota    = !empty($row['anggota_id']);
                    ?>
                    <tr class="penjualan-row-item" data-metode="<?= htmlspecialchars($row['metode_bayar']) ?>" data-tanggal="<?= htmlspecialchars($tglIso) ?>">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <span class="badge-kode-transaksi"><?= htmlspecialchars($row['kode_transaksi']) ?></span>
                            <span class="badge-tanggal-obat mt-1">
                                <i class="fa-regular fa-calendar-days text-slate-500"></i>
                                <?= htmlspecialchars($tglFormatted) ?>
                            </span>
                        </td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge"><?= htmlspecialchars($initials) ?></div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_pembeli']) ?></h4>
                                    <span class="badge-pembeli-tipe"><?= $isAnggota ? 'Anggota Poktan' : 'Pembeli Umum' ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="hutang-item-stack">
                                <?php foreach ($row['items'] as $jItem): ?>
                                    <span class="badge-nama-obat">
                                        <i class="fa-solid fa-shield-virus"></i>
                                        <?= htmlspecialchars($jItem['nama_obat']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <div class="hutang-item-stack">
                                <?php foreach ($row['items'] as $jItem): ?>
                                    <span class="badge-kemasan-obat">
                                        <i class="fa-solid fa-prescription-bottle"></i>
                                        <?= htmlspecialchars($jItem['jumlah_kemasan']) ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if (count($row['items']) > 1): ?>
                                    <span class="hutang-item-total-note">Total: <?= htmlspecialchars($row['jumlah_kemasan']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="hutang-item-stack">
                                <?php foreach ($row['items'] as $jItem): ?>
                                    <span class="nik-badge">
                                        <i class="fa-solid fa-scale-balanced text-agro-600"></i>
                                        <?= htmlspecialchars($jItem['jumlah_berat']) ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if (count($row['items']) > 1): ?>
                                    <span class="hutang-item-total-note">Total: <?= htmlspecialchars($row['jumlah_berat']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="hutang-item-stack">
                                <?php foreach ($row['items'] as $jItem): ?>
                                    <span class="badge-harga-obat" title="Harga satuan per kemasan">
                                        <i class="fa-solid fa-tag"></i>
                                        <?= htmlspecialchars($jItem['harga']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge-jumlah-bayar" title="Total nilai penjualan">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <?= htmlspecialchars($row['total_harga_str']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-metode-bayar">
                                <i class="fa-solid <?= $mIcon ?>"></i>
                                <?= htmlspecialchars($row['metode_bayar']) ?>
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
                                    onclick="openModalInfoPenjualan(<?= (int)$row['id'] ?>)"
                                    title="Lihat detail transaksi penjualan"
                                >
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>Info</span>
                                </button>

                                <a
                                    href="<?= htmlspecialchars(appUrl('obat/print-penjualan.php?id=' . (int)$row['id'])) ?>"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn-table-print"
                                    title="Cetak nota penjualan"
                                >
                                    <i class="fa-solid fa-print"></i>
                                    <span>Nota</span>
                                </a>

                                <button
                                    type="button"
                                    class="btn-table-edit"
                                    onclick="openModalEditPenjualan(<?= htmlspecialchars(json_encode($row)) ?>)"
                                    title="Edit transaksi penjualan"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeletePenjualan(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['kode_transaksi'])) ?>', '<?= htmlspecialchars(addslashes($row['nama_pembeli'])) ?>', '<?= htmlspecialchars(addslashes($row['jumlah_kemasan'])) ?>')"
                                    title="Hapus transaksi & kembalikan stok obat"
                                >
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
