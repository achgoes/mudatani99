<!-- =========================================================
     TAMPILAN 1: STOK OBAT
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Jenis Obat</h5>
            <div class="stat-value"><?= count($obatList) ?></div>
            <div class="stat-desc">Data inventaris tercatat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-shield-virus"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Sisa Stok Kemasan</h5>
            <div class="stat-value"><?= number_format($totalKemasan, 0, ',', '.') ?> <span class="text-sm font-semibold">Pcs / Botol</span></div>
            <div class="stat-desc">Tersedia di gudang saat ini</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-prescription-bottle-medical"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Sisa Jumlah Berat</h5>
            <div class="stat-value"><?= number_format($totalBerat, 0, ',', '.') ?> <span class="text-sm font-semibold">Kg</span></div>
            <div class="stat-desc">Akumulasi berat gudang</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Terpakai di Hutang</h5>
            <div class="stat-value"><?= number_format($totalKemasanHutang, 0, ',', '.') ?> <span class="text-sm font-semibold">Kemasan</span></div>
            <div class="stat-desc"><?= $countBelumLunas ?> Belum Lunas &bull; <?= $countLunas ?> Lunas</div>
        </div>
        <div class="stat-icon-wrap stat-icon-rose">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
    </div>
</div>

<!-- Tabel Stok Obat -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Stok Obat</h3>
            <p>Menampilkan <?= count($obatList) ?> data stok obat tersedia (diambil dari Master Obat).</p>
        </div>

        <div class="table-card-actions">
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="obat-search-input" placeholder="Cari nama obat, kemasan, berat, keterangan..." onkeyup="filterObatTable()">
            </div>

            <button type="button" class="btn-add-user" onclick="openModalTambahObat()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Stok Obat</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-obat-data">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Nama Obat</th>
                    <th>Jumlah Kemasan</th>
                    <th>Jumlah Berat</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($obatList)): ?>
                <tr>
                    <td colspan="6" class="table-empty-cell">
                        <i class="fa-solid fa-shield-virus text-3xl mb-2 block"></i>
                        Belum ada data stok obat. Silakan klik tombol <b>Tambah Stok Obat</b> atau jalankan <code>01-stok-obat.sql</code>.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($obatList as $row): ?>
                    <tr class="obat-row-item">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge">
                                    <i class="fa-solid fa-prescription-bottle-medical"></i>
                                </div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_obat']) ?></h4>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-kemasan-obat">
                                <i class="fa-solid fa-prescription-bottle"></i>
                                <?= htmlspecialchars($row['jumlah_kemasan']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="nik-badge">
                                <i class="fa-solid fa-scale-balanced text-agro-600"></i>
                                <?= htmlspecialchars($row['jumlah_berat']) ?>
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
                                    class="btn-table-edit"
                                    onclick="openModalEditObat(<?= htmlspecialchars(json_encode($row)) ?>)"
                                    title="Edit Data Stok Obat"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeleteObat(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_obat'])) ?>', '<?= htmlspecialchars(addslashes($row['jumlah_kemasan'])) ?>')"
                                    title="Hapus Data Stok Obat"
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
