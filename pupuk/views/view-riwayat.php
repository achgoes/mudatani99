<!-- =========================================================
     TAMPILAN 3: RIWAYAT HUTANG & PELUNASAN PUPUK
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Transaksi Pembayaran</h5>
            <div class="stat-value"><?= count($pembayaranList) ?> <span class="text-sm font-semibold">Kali</span></div>
            <div class="stat-desc">Dari <?= count($petaniPembayarIds) ?> petani</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Uang Masuk</h5>
            <div class="stat-value stat-value-rupiah"><?= htmlspecialchars(formatRupiahStr($totalTerbayar)) ?></div>
            <div class="stat-desc">Jumlah semua pembayaran tercatat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Pembayaran Pelunasan</h5>
            <div class="stat-value"><?= $countPelunasan ?> <span class="text-sm font-semibold">Kali</span></div>
            <div class="stat-desc">Pembayaran yang membuat hutang lunas</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Sisa Piutang</h5>
            <div class="stat-value stat-value-rupiah"><?= htmlspecialchars(formatRupiahStr($totalSisaPiutang)) ?></div>
            <div class="stat-desc"><?= $countBelumLunas ?> hutang belum lunas</div>
        </div>
        <div class="stat-icon-wrap stat-icon-rose">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
    </div>
</div>

<!-- Tabel Riwayat Hutang & Pelunasan -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Riwayat Pembayaran Hutang Pupuk Petani</h3>
            <p>Menampilkan <?= count($pembayaranList) ?> transaksi pembayaran (terbaru di atas).</p>
        </div>

        <div class="table-card-actions">
            <!-- Filter Rentang Tanggal -->
            <div class="pupuk-date-filter-group">
                <input type="date" id="hutang-date-from" class="pupuk-date-input" aria-label="Dari Tanggal" title="Dari Tanggal">
                <span class="pupuk-date-sep">s/d</span>
                <input type="date" id="hutang-date-to" class="pupuk-date-input" aria-label="Sampai Tanggal" title="Sampai Tanggal">
                <button type="button" class="btn-filter-tanggal" onclick="applyHutangDateFilter()" title="Terapkan Filter Tanggal">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
            </div>

            <!-- Filter Status (Lunas / Belum Lunas) -->
            <select id="hutang-status-filter" onchange="filterHutangTable()" class="pupuk-filter-select" aria-label="Filter Status Hutang">
                <option value="ALL">Semua Status</option>
                <option value="Belum Lunas">Belum Lunas</option>
                <option value="Lunas">Lunas</option>
            </select>

            <!-- Pencarian -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="hutang-search-input" placeholder="Cari petani, pupuk, metode, tanggal..." onkeyup="filterHutangTable()">
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Pembayaran (1 baris = 1 kali pembayaran) -->
    <div class="table-responsive-custom">
        <table class="user-table" id="table-riwayat-bayar">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Tanggal Bayar</th>
                    <th>Nama Petani</th>
                    <th>Nama Pupuk</th>
                    <th>Total Hutang</th>
                    <th>Jumlah Bayar</th>
                    <th>Sisa Hutang</th>
                    <th>Metode Bayar</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-hutang-tbody">
                <?php if (empty($pembayaranList)): ?>
                <tr>
                    <td colspan="11" class="table-empty-cell">
                        <i class="fa-solid fa-clock-rotate-left text-3xl mb-2 block"></i>
                        Belum ada pembayaran yang tercatat. Pembayaran akan muncul di sini setelah tombol <b>Bayar</b> dipakai di menu
                        <a href="<?= appUrl('pupuk/?view=hutang') ?>" class="link-riwayat-ke-hutang">Hutang Piutang</a>.
                    </td>
                </tr>
                <?php else: ?>
                    <?php
                        $metodeIconMap = [
                            'Tunai'              => 'fa-money-bill',
                            'Transfer'           => 'fa-building-columns',
                            'Potong Hasil Panen' => 'fa-wheat-awn',
                        ];
                        $no = 1;
                        foreach ($pembayaranList as $bp):
                        $bpParts = preg_split('/\s+/', trim($bp['nama_petani']));
                        $bpInitials = (count($bpParts) >= 2)
                            ? strtoupper(mb_substr($bpParts[0], 0, 1) . mb_substr($bpParts[1], 0, 1))
                            : strtoupper(mb_substr($bp['nama_petani'], 0, 2));
                        $bpLunas   = ($bp['status'] === 'Lunas');
                        $bpTglIso  = (!empty($bp['tanggal_bayar']) && $bp['tanggal_bayar'] !== '0000-00-00')
                            ? substr($bp['tanggal_bayar'], 0, 10)
                            : '';
                        $bpTgl     = ($bpTglIso !== '')
                            ? date('d/m/Y', strtotime($bpTglIso))
                            : '-';
                        $bpIcon    = $metodeIconMap[$bp['metode_bayar']] ?? 'fa-money-bill';
                    ?>
                    <tr class="hutang-row-item" data-status="<?= htmlspecialchars($bp['status']) ?>" data-tanggal="<?= htmlspecialchars($bpTglIso) ?>">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <span class="badge-tanggal-pupuk">
                                <i class="fa-regular fa-calendar-days text-slate-500"></i>
                                <?= htmlspecialchars($bpTgl) ?>
                            </span>
                            <span class="bayar-ke-label">Pembayaran ke-<?= (int)$bp['pembayaran_ke'] ?></span>
                        </td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge"><?= htmlspecialchars($bpInitials) ?></div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($bp['nama_petani']) ?></h4>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-nama-pupuk">
                                <i class="fa-solid fa-flask-vial"></i>
                                <?= htmlspecialchars($bp['nama_pupuk']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-jumlah-hutang" title="Total hutang awal">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                <?= htmlspecialchars($bp['total_hutang_str']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-jumlah-bayar" title="Nominal yang dibayar pada transaksi ini">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <?= htmlspecialchars($bp['jumlah_bayar_str']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="<?= ((int)$bp['sisa_hutang'] <= 0) ? 'badge-sisa-lunas' : 'badge-sisa-belum' ?>" title="Sisa hutang setelah pembayaran ini">
                                <i class="fa-solid fa-coins"></i>
                                <?= htmlspecialchars($bp['sisa_hutang_str']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-metode-bayar">
                                <i class="fa-solid <?= $bpIcon ?>"></i>
                                <?= htmlspecialchars($bp['metode_bayar']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($bpLunas): ?>
                                <span class="badge-status-hutang badge-status-lunas">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Lunas
                                </span>
                            <?php else: ?>
                                <span class="badge-status-hutang badge-status-belum">
                                    <i class="fa-solid fa-clock"></i>
                                    Belum Lunas
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge-keterangan">
                                <i class="fa-regular fa-note-sticky"></i>
                                <?= htmlspecialchars($bp['keterangan'] ?: '-') ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-btn-group justify-end">
                                <button
                                    type="button"
                                    class="btn-table-info"
                                    onclick="openModalInfoHutang(<?= (int)$bp['hutang_id'] ?>, <?= (int)$bp['id'] ?>)"
                                    title="Lihat info lengkap hutang dan seluruh riwayat pembayarannya"
                                >
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>Info Lengkap</span>
                                </button>
                                <a
                                    href="<?= htmlspecialchars(appUrl('pupuk/print-pembayaran.php?id=' . (int)$bp['id'])) ?>"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn-table-print"
                                    title="Print bukti pembayaran transaksi ini"
                                >
                                    <i class="fa-solid fa-print"></i>
                                    <span>Print</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
