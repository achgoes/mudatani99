<!-- =========================================================
     TAMPILAN 2: RIWAYAT PENGELOLAAN STOK TANAMAN
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Catatan Stok</h5>
            <div class="stat-value"><?= count($riwayatStokList) ?></div>
            <div class="stat-desc">Seluruh aktivitas keluar-masuk &amp; edit</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-boxes-packing"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Stok Masuk / Kembali</h5>
            <div class="stat-value"><?= $countStokMasuk ?> <span class="text-sm font-semibold">Kali</span></div>
            <div class="stat-desc">Total: +<?= number_format($totalKemasanMasukLog, 0, ',', '.') ?> Pcs</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Stok Keluar</h5>
            <div class="stat-value"><?= $countStokKeluar ?> <span class="text-sm font-semibold">Kali</span></div>
            <div class="stat-desc">Total: -<?= number_format($totalKemasanKeluarLog, 0, ',', '.') ?> Pcs</div>
        </div>
        <div class="stat-icon-wrap stat-icon-rose">
            <i class="fa-solid fa-arrow-trend-down"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Edit &amp; Hapus Stok</h5>
            <div class="stat-value"><?= $countStokEditHapus ?> <span class="text-sm font-semibold">Kali</span></div>
            <div class="stat-desc">Penyesuaian manual atau penghapusan</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-pen-to-square"></i>
        </div>
    </div>
</div>

<!-- Tabel Riwayat Pengelolaan Stok Tanaman -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Riwayat Pengelolaan Stok Tanaman</h3>
            <p>Menampilkan <?= count($riwayatStokList) ?> catatan perubahan stok gudang (terbaru di atas).</p>
        </div>

        <div class="table-card-actions">
            <!-- Filter Rentang Tanggal -->
            <div class="tanaman-date-filter-group">
                <input type="date" id="riwayat-stok-date-from" class="tanaman-date-input" aria-label="Dari Tanggal" title="Dari Tanggal">
                <span class="tanaman-date-sep">s/d</span>
                <input type="date" id="riwayat-stok-date-to" class="tanaman-date-input" aria-label="Sampai Tanggal" title="Sampai Tanggal">
                <button type="button" class="btn-filter-tanggal" onclick="applyRiwayatStokDateFilter()" title="Terapkan Filter Tanggal">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
            </div>

            <!-- Filter Sumber -->
            <select id="riwayat-stok-sumber-filter" onchange="filterRiwayatStokTable()" class="tanaman-filter-select" aria-label="Filter Sumber Perubahan Stok">
                <option value="ALL">Semua Sumber</option>
                <option value="Kelola Stok">Kelola Stok</option>
                <option value="Penjualan">Penjualan</option>
                <option value="Hutang Piutang">Hutang Piutang</option>
            </select>

            <!-- Pencarian -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="riwayat-stok-search-input" placeholder="Cari tanaman, aktivitas, keterangan, petugas..." onkeyup="filterRiwayatStokTable()">
            </div>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-riwayat-stok">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Waktu</th>
                    <th>Sumber</th>
                    <th>Aktivitas</th>
                    <th>Nama Tanaman</th>
                    <th>Stok Sebelum</th>
                    <th>Perubahan (+/-)</th>
                    <th>Stok Sesudah</th>
                    <th>Keterangan</th>
                    <th>Dicatat Oleh</th>
                </tr>
            </thead>
            <tbody id="table-riwayat-stok-tbody">
                <?php if (empty($riwayatStokList)): ?>
                <tr>
                    <td colspan="10" class="table-empty-cell">
                        <i class="fa-solid fa-boxes-packing text-3xl mb-2 block"></i>
                        Belum ada riwayat pengelolaan stok tanaman yang tercatat.
                    </td>
                </tr>
                <?php else: ?>
                    <?php
                        $sumberClassMap = [
                            'Kelola Stok'    => 'badge-sumber-stok',
                            'Penjualan'      => 'badge-sumber-jual',
                            'Hutang Piutang' => 'badge-sumber-hutang',
                        ];
                        $no = 1;
                        foreach ($riwayatStokList as $rs):
                            $tglIso    = !empty($rs['created_at']) ? substr((string)$rs['created_at'], 0, 10) : '';
                            $waktuFmt  = !empty($rs['created_at']) ? date('d/m/Y H:i', strtotime($rs['created_at'])) : '-';
                            $sumberCls = $sumberClassMap[$rs['sumber']] ?? 'badge-sumber-stok';

                            if ($rs['delta_sign'] > 0) {
                                $deltaCls = 'badge-delta-plus';
                            } elseif ($rs['delta_sign'] < 0) {
                                $deltaCls = 'badge-delta-minus';
                            } else {
                                $deltaCls = 'badge-delta-neutral';
                            }

                            $isDelete = ($rs['aksi'] === 'Hapus Stok');
                            $isEdit   = (strpos((string)$rs['aksi'], 'Edit') !== false || strpos((string)$rs['aksi'], 'Revisi') !== false);
                            if ($isDelete) {
                                $aksiCls = 'badge-status-belum';
                            } elseif ($isEdit) {
                                $aksiCls = 'badge-aksi-diperbarui';
                            } elseif ($rs['delta_sign'] > 0) {
                                $aksiCls = 'badge-status-lunas';
                            } else {
                                $aksiCls = 'badge-status-belum';
                            }
                    ?>
                    <tr class="riwayat-stok-row-item" data-status="<?= htmlspecialchars($rs['sumber']) ?>" data-tanggal="<?= htmlspecialchars($tglIso) ?>">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <span class="badge-tanggal-tanaman">
                                <i class="fa-regular fa-clock text-slate-500"></i>
                                <?= htmlspecialchars($waktuFmt) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-sumber-stok-base <?= $sumberCls ?>">
                                <?= htmlspecialchars($rs['sumber']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-status-hutang <?= $aksiCls ?>">
                                <?= htmlspecialchars($rs['aksi']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-nama-tanaman">
                                <i class="fa-solid fa-wheat-awn"></i>
                                <?= htmlspecialchars($rs['nama_tanaman']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="hutang-item-stack">
                                <span class="text-xs font-semibold text-slate-600"><?= htmlspecialchars($rs['kemasan_sebelum']) ?></span>
                                <span class="text-[11px] text-slate-400"><?= htmlspecialchars($rs['berat_sebelum']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="hutang-item-stack">
                                <span class="<?= $deltaCls ?>"><?= htmlspecialchars($rs['perubahan_kemasan']) ?></span>
                                <span class="text-[11px] font-semibold text-slate-500"><?= htmlspecialchars($rs['perubahan_berat']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="hutang-item-stack">
                                <span class="badge-kemasan-tanaman">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                    <?= htmlspecialchars($rs['kemasan_sesudah']) ?>
                                </span>
                                <span class="nik-badge">
                                    <i class="fa-solid fa-scale-balanced text-agro-600"></i>
                                    <?= htmlspecialchars($rs['berat_sesudah']) ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-keterangan">
                                <i class="fa-regular fa-note-sticky"></i>
                                <?= htmlspecialchars($rs['keterangan']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-xs font-bold text-slate-700"><?= htmlspecialchars($rs['dicatat_oleh']) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
