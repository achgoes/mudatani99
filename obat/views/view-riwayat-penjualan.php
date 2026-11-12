<!-- =========================================================
     TAMPILAN 4: RIWAYAT PENJUALAN OBAT
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Catatan Riwayat</h5>
            <div class="stat-value"><?= count($riwayatPenjualanList) ?></div>
            <div class="stat-desc">Seluruh aktivitas penjualan tercatat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-receipt"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Transaksi Terjual</h5>
            <div class="stat-value"><?= $countLogTerjual ?> <span class="text-sm font-semibold">Kali</span></div>
            <div class="stat-desc">Pencatatan penjualan baru</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Transaksi Diperbarui</h5>
            <div class="stat-value"><?= $countLogDiperbarui ?> <span class="text-sm font-semibold">Kali</span></div>
            <div class="stat-desc">Riwayat revisi transaksi penjualan</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-pen-to-square"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Dibatalkan / Dihapus</h5>
            <div class="stat-value"><?= $countLogDibatalkan ?> <span class="text-sm font-semibold">Kali</span></div>
            <div class="stat-desc">Stok obat otomatis dikembalikan</div>
        </div>
        <div class="stat-icon-wrap stat-icon-rose">
            <i class="fa-solid fa-rotate-left"></i>
        </div>
    </div>
</div>

<!-- Tabel Riwayat Penjualan -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Riwayat Aktivitas Penjualan Obat</h3>
            <p>Menampilkan <?= count($riwayatPenjualanList) ?> log riwayat penjualan obat (terbaru di atas).</p>
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

            <!-- Filter Jenis Aksi -->
            <select id="penjualan-metode-filter" onchange="filterPenjualanTable()" class="obat-filter-select" aria-label="Filter Aktivitas Penjualan">
                <option value="ALL">Semua Aktivitas</option>
                <option value="Terjual">Terjual</option>
                <option value="Diperbarui">Diperbarui</option>
                <option value="Dibatalkan">Dibatalkan</option>
            </select>

            <!-- Pencarian -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="penjualan-search-input" placeholder="Cari nota, pembeli, item, petugas..." onkeyup="filterPenjualanTable()">
            </div>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-riwayat-penjualan">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Tanggal &amp; Kode Nota</th>
                    <th>Aktivitas</th>
                    <th>Pembeli</th>
                    <th>Rincian Item Obat</th>
                    <th>Total Kuantitas</th>
                    <th>Total Harga</th>
                    <th>Metode Bayar</th>
                    <th>Dicatat Oleh</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-penjualan-tbody">
                <?php if (empty($riwayatPenjualanList)): ?>
                <tr>
                    <td colspan="10" class="table-empty-cell">
                        <i class="fa-solid fa-receipt text-3xl mb-2 block"></i>
                        Belum ada riwayat penjualan obat. Catat transaksi baru di menu
                        <a href="<?= appUrl('obat/?view=penjualan') ?>" class="link-riwayat-ke-hutang">Penjualan</a>.
                    </td>
                </tr>
                <?php else: ?>
                    <?php
                        $aksiClassMap = [
                            'Terjual'    => 'badge-status-lunas',
                            'Diperbarui' => 'badge-aksi-diperbarui',
                            'Dibatalkan' => 'badge-status-belum',
                        ];
                        $aksiIconMap = [
                            'Terjual'    => 'fa-circle-check',
                            'Diperbarui' => 'fa-pen-to-square',
                            'Dibatalkan' => 'fa-ban',
                        ];
                        $no = 1;
                        foreach ($riwayatPenjualanList as $rp):
                            $tglIso   = preg_match('/^\d{4}-\d{2}-\d{2}/', (string)$rp['tanggal_jual'], $mTgl) ? $mTgl[0] : '';
                            $tglFmt   = ($tglIso !== '') ? date('d/m/Y', strtotime($tglIso)) : '-';
                            $waktuLog = !empty($rp['created_at']) ? date('d/m/Y H:i', strtotime($rp['created_at'])) : '-';
                            $aksiCls  = $aksiClassMap[$rp['aksi']] ?? 'badge-status-lunas';
                            $aksiIco  = $aksiIconMap[$rp['aksi']] ?? 'fa-circle-check';
                            $hasActiveNota = !empty($rp['penjualan_id']) && isset($penjualanInfoMap[(int)$rp['penjualan_id']]);
                    ?>
                    <tr class="penjualan-row-item" data-metode="<?= htmlspecialchars($rp['aksi']) ?>" data-tanggal="<?= htmlspecialchars($tglIso) ?>">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <span class="badge-kode-transaksi"><?= htmlspecialchars($rp['kode_transaksi']) ?></span>
                            <span class="badge-tanggal-obat mt-1">
                                <i class="fa-regular fa-calendar-days text-slate-500"></i>
                                <?= htmlspecialchars($tglFmt) ?>
                            </span>
                            <span class="bayar-ke-label">Log: <?= htmlspecialchars($waktuLog) ?></span>
                        </td>
                        <td>
                            <span class="badge-status-hutang <?= $aksiCls ?>">
                                <i class="fa-solid <?= $aksiIco ?>"></i>
                                <?= htmlspecialchars($rp['aksi']) ?>
                            </span>
                        </td>
                        <td class="font-semibold text-slate-800"><?= htmlspecialchars($rp['nama_pembeli']) ?></td>
                        <td>
                            <div class="text-xs text-slate-700 font-medium max-w-xs leading-relaxed">
                                <?= htmlspecialchars($rp['rincian_item']) ?>
                            </div>
                        </td>
                        <td>
                            <div class="hutang-item-stack">
                                <span class="badge-kemasan-obat">
                                    <i class="fa-solid fa-prescription-bottle"></i>
                                    <?= htmlspecialchars($rp['total_kemasan']) ?>
                                </span>
                                <span class="nik-badge">
                                    <i class="fa-solid fa-scale-balanced text-agro-600"></i>
                                    <?= htmlspecialchars($rp['total_berat']) ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-jumlah-bayar">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <?= htmlspecialchars($rp['total_harga_str']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-metode-bayar"><?= htmlspecialchars($rp['metode_bayar']) ?></span>
                        </td>
                        <td>
                            <div class="info-ket-cell">
                                <span class="font-semibold text-xs text-slate-700"><?= htmlspecialchars($rp['dicatat_oleh']) ?></span>
                                <span class="info-dicatat-oleh"><?= htmlspecialchars($rp['keterangan']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="action-btn-group justify-end">
                                <?php if ($hasActiveNota): ?>
                                    <button
                                        type="button"
                                        class="btn-table-info"
                                        onclick="openModalInfoPenjualan(<?= (int)$rp['penjualan_id'] ?>)"
                                        title="Lihat detail nota penjualan aktif"
                                    >
                                        <i class="fa-solid fa-circle-info"></i>
                                        <span>Info</span>
                                    </button>
                                    <a
                                        href="<?= htmlspecialchars(appUrl('obat/print-penjualan.php?id=' . (int)$rp['penjualan_id'])) ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="btn-table-print"
                                        title="Print nota penjualan"
                                    >
                                        <i class="fa-solid fa-print"></i>
                                        <span>Nota</span>
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 font-semibold italic">Nota Dihapus</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
