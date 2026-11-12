<!-- =========================================================
     TAMPILAN 1: STOK TANAMAN
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Jenis Tanaman</h5>
            <div class="stat-value"><?= count($tanamanList) ?></div>
            <div class="stat-desc">Data inventaris tercatat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-wheat-awn"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Sisa Stok Kemasan</h5>
            <div class="stat-value"><?= number_format($totalKemasan, 0, ',', '.') ?> <span class="text-sm font-semibold">Pcs</span></div>
            <div class="stat-desc">Tersedia di gudang saat ini</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-boxes-stacked"></i>
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
            <div class="stat-value"><?= number_format($totalKemasanHutang, 0, ',', '.') ?> <span class="text-sm font-semibold">Pcs</span></div>
            <div class="stat-desc"><?= $countBelumLunas ?> Belum Lunas &bull; <?= $countLunas ?> Lunas</div>
        </div>
        <div class="stat-icon-wrap stat-icon-rose">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
    </div>
</div>

<!-- Tabel Stok Tanaman -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Stok Tanaman</h3>
            <p>Menampilkan <?= count($tanamanList) ?> data stok tanaman tersedia (diambil dari Master Tanaman).</p>
        </div>

        <div class="table-card-actions">
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="tanaman-search-input" placeholder="Cari nama tanaman, kemasan, berat, keterangan..." onkeyup="filterTanamanTable()">
            </div>

            <button type="button" class="btn-add-user" onclick="openModalTambahTanaman()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Stok Tanaman</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-tanaman-data">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Nama Tanaman</th>
                    <th>Jumlah Kemasan</th>
                    <th>Jumlah Berat</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tanamanList)): ?>
                <tr>
                    <td colspan="6" class="table-empty-cell">
                        <i class="fa-solid fa-seedling text-3xl mb-2 block"></i>
                        Belum ada data stok tanaman. Silakan klik tombol <b>Tambah Stok Tanaman</b> atau jalankan <code>01-stok-tanaman.sql</code>.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($tanamanList as $row): ?>
                    <tr class="tanaman-row-item">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge">
                                    <i class="fa-solid fa-wheat-awn"></i>
                                </div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_tanaman']) ?></h4>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-kemasan-tanaman">
                                <i class="fa-solid fa-boxes-stacked"></i>
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
                                    onclick="openModalEditTanaman(<?= htmlspecialchars(json_encode($row)) ?>)"
                                    title="Edit Data Stok Tanaman"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeleteTanaman(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_tanaman'])) ?>', '<?= htmlspecialchars(addslashes($row['jumlah_kemasan'])) ?>')"
                                    title="Hapus Data Stok Tanaman"
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
