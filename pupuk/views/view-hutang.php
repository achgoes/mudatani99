<!-- =========================================================
     TAMPILAN 2: HUTANG PIUTANG PUPUK
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Data Hutang</h5>
            <div class="stat-value"><?= count($hutangList) ?></div>
            <div class="stat-desc">Transaksi hutang pupuk petani</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-file-invoice"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Belum Lunas</h5>
            <div class="stat-value"><?= $countBelumLunas ?> <span class="text-sm font-semibold">Data</span></div>
            <div class="stat-desc">Total: <?= number_format($karungBelumLunas, 0, ',', '.') ?> Karung</div>
        </div>
        <div class="stat-icon-wrap stat-icon-rose">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Sudah Lunas</h5>
            <div class="stat-value"><?= $countLunas ?> <span class="text-sm font-semibold">Data</span></div>
            <div class="stat-desc">Total: <?= number_format($karungLunas, 0, ',', '.') ?> Karung</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Pupuk Dipakai</h5>
            <div class="stat-value"><?= number_format($totalKarungHutang, 0, ',', '.') ?> <span class="text-sm font-semibold">Karung</span></div>
            <div class="stat-desc">Akumulasi berat: <?= number_format($totalBeratHutang, 0, ',', '.') ?> Kg</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
    </div>
</div>

<!-- Tabel Hutang Piutang -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Hutang Piutang Pupuk Petani</h3>
            <p>Menampilkan <?= count($hutangList) ?> data hutang piutang pupuk petani (tersinkronisasi otomatis dengan data Stok Pupuk).</p>
        </div>

        <div class="table-card-actions">
            <!-- Filter Status (Lunas / Belum Lunas) -->
            <select id="hutang-status-filter" onchange="filterHutangTable()" class="pupuk-filter-select" aria-label="Filter Status Hutang">
                <option value="ALL">Semua Status</option>
                <option value="Belum Lunas">Belum Lunas</option>
                <option value="Lunas">Lunas</option>
            </select>

            <!-- Pencarian -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="hutang-search-input" placeholder="Cari petani, pupuk, karung, status..." onkeyup="filterHutangTable()">
            </div>

            <!-- Tombol Tambah Hutang Piutang -->
            <button type="button" class="btn-add-user" onclick="openModalTambahHutang()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Hutang Pupuk</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-hutang-data">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Nama Petani</th>
                    <th>Nama Pupuk</th>
                    <th>Jumlah Karung</th>
                    <th>Jumlah Berat</th>
                    <th>Harga</th>
                    <th>Jumlah Hutang</th>
                    <th>Sisa Hutang</th>
                    <th>Keterangan</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-hutang-tbody">
                <?php if (empty($hutangList)): ?>
                <tr>
                    <td colspan="12" class="table-empty-cell">
                        <i class="fa-solid fa-hand-holding-dollar text-3xl mb-2 block"></i>
                        Belum ada data hutang piutang pupuk. Silakan klik tombol <b>Tambah Hutang Pupuk</b> atau jalankan <code>query2.sql</code>.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($hutangList as $row): ?>
                    <?php
                        $nParts = preg_split('/\s+/', trim($row['nama_petani']));
                        if (count($nParts) >= 2) {
                            $initials = strtoupper(mb_substr($nParts[0], 0, 1) . mb_substr($nParts[1], 0, 1));
                        } else {
                            $initials = strtoupper(mb_substr($row['nama_petani'], 0, 2));
                        }
                        $isLunas = ($row['status'] === 'Lunas');
                        $tglFormatted = (!empty($row['tanggal']) && $row['tanggal'] !== '0000-00-00')
                            ? date('d/m/Y', strtotime($row['tanggal']))
                            : (!empty($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '-');
                    ?>
                    <tr class="hutang-row-item" data-status="<?= htmlspecialchars($row['status']) ?>">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge"><?= htmlspecialchars($initials) ?></div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_petani']) ?></h4>
                                    <span>ID Anggota: #<?= (int)$row['anggota_id'] ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-nama-pupuk">
                                <i class="fa-solid fa-flask-vial"></i>
                                <?= htmlspecialchars($row['nama_pupuk']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-karung-pupuk">
                                <i class="fa-solid fa-boxes-stacked"></i>
                                <?= htmlspecialchars($row['jumlah_karung']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="nik-badge">
                                <i class="fa-solid fa-scale-balanced text-agro-600"></i>
                                <?= htmlspecialchars($row['jumlah_berat']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-harga-pupuk" title="Harga satuan per kantong">
                                <i class="fa-solid fa-tag"></i>
                                <?= htmlspecialchars($row['harga']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-jumlah-hutang" title="Total Nilai Hutang (Jumlah Karung × Harga Satuan)">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                <?= htmlspecialchars($row['jumlah_hutang']) ?>
                            </span>
                        </td>
                        <td>
                            <?php
                                $sisaVal = parseRupiahFloat($row['sisa_hutang']);
                                $sisaBadgeClass = ($isLunas || $sisaVal <= 0.0) ? 'badge-sisa-lunas' : 'badge-sisa-belum';
                            ?>
                            <span class="<?= $sisaBadgeClass ?>" title="Sisa Hutang Belum Dibayar">
                                <i class="fa-solid fa-coins"></i>
                                <?= $isLunas ? 'Rp 0' : htmlspecialchars($row['sisa_hutang']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-keterangan">
                                <i class="fa-regular fa-note-sticky"></i>
                                <?= htmlspecialchars($row['keterangan'] ?: '-') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-tanggal-pupuk">
                                <i class="fa-regular fa-calendar-days text-slate-500"></i>
                                <?= htmlspecialchars($tglFormatted) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($isLunas): ?>
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
                            <div class="action-btn-group justify-end">
                                <button
                                    type="button"
                                    class="btn-table-info"
                                    onclick="openModalInfoHutang(<?= (int)$row['id'] ?>)"
                                    title="Lihat detail hutang dan jejak pembayarannya"
                                >
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>Info</span>
                                </button>

                                <?php if ($isLunas): ?>
                                    <!-- Form Cepat Batal Lunas -->
                                    <form action="<?= appUrl('pupuk/?view=' . urlencode($activeView)) ?>" method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle_status_hutang">
                                        <input type="hidden" name="return_view" value="<?= htmlspecialchars($activeView) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                        <input type="hidden" name="new_status" value="Belum Lunas">
                                        <button type="submit" class="btn-table-batal-lunas" title="Ubah status kembali menjadi Belum Lunas">
                                            <i class="fa-solid fa-rotate-left"></i>
                                            <span>Batal Lunas</span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Aksi Bayar: Membuka Pop Up Bayar Jumlah Rupiah -->
                                    <button
                                        type="button"
                                        class="btn-table-bayar"
                                        onclick="openModalBayarHutang(<?= htmlspecialchars(json_encode($row)) ?>)"
                                        title="Bayar Hutang Pupuk Petani (Buka Pop Up Bayar)"
                                    >
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                        <span>Bayar</span>
                                    </button>
                                <?php endif; ?>

                                <button
                                    type="button"
                                    class="btn-table-edit"
                                    onclick="openModalEditHutang(<?= htmlspecialchars(json_encode($row)) ?>)"
                                    title="Edit Data Hutang Pupuk"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeleteHutang(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_petani'])) ?>', '<?= htmlspecialchars(addslashes($row['nama_pupuk'])) ?>', '<?= htmlspecialchars(addslashes($row['jumlah_karung'])) ?>')"
                                    title="Hapus Data & Kembalikan Stok Karung"
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
