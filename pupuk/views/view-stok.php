<!-- =========================================================
     TAMPILAN 1: STOK PUPUK
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Jenis Pupuk</h5>
            <div class="stat-value"><?= count($pupukList) ?></div>
            <div class="stat-desc">Data inventaris tercatat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-flask-vial"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Sisa Stok Karung</h5>
            <div class="stat-value"><?= number_format($totalKarung, 0, ',', '.') ?> <span class="text-sm font-semibold">Karung</span></div>
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
            <div class="stat-value"><?= number_format($totalKarungHutang, 0, ',', '.') ?> <span class="text-sm font-semibold">Karung</span></div>
            <div class="stat-desc"><?= $countBelumLunas ?> Belum Lunas &bull; <?= $countLunas ?> Lunas</div>
        </div>
        <div class="stat-icon-wrap stat-icon-rose">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
    </div>
</div>

<!-- Tabel Stok Pupuk -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Stok Pupuk</h3>
            <p>Menampilkan <?= count($pupukList) ?> data stok pupuk (diambil dari Master Pupuk) beserta sisa jumlah karung, jumlah berat, dan keterangan.</p>
        </div>

        <div class="table-card-actions">
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="pupuk-search-input" placeholder="Cari nama pupuk, karung, berat, keterangan..." onkeyup="filterPupukTable()">
            </div>

            <button type="button" class="btn-add-user" onclick="openModalTambahPupuk()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Stok Pupuk</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-pupuk-data">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Nama Pupuk</th>
                    <th>Jumlah Karung</th>
                    <th>Jumlah Berat</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pupukList)): ?>
                <tr>
                    <td colspan="6" class="table-empty-cell">
                        <i class="fa-solid fa-boxes-stacked text-3xl mb-2 block"></i>
                        Belum ada data stok pupuk. Silakan klik tombol <b>Tambah Stok Pupuk</b> atau jalankan <code>query.sql</code>.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($pupukList as $row): ?>
                    <tr class="pupuk-row-item">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge">
                                    <i class="fa-solid fa-flask-vial"></i>
                                </div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_pupuk']) ?></h4>
                                    <span>ID Master: #<?= (int)($row['pupuk_id'] ?? $row['id']) ?></span>
                                </div>
                            </div>
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
                                    onclick="openModalEditPupuk(<?= htmlspecialchars(json_encode($row)) ?>)"
                                    title="Edit Data Pupuk"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeletePupuk(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_pupuk'])) ?>', '<?= htmlspecialchars(addslashes($row['jumlah_karung'])) ?>')"
                                    title="Hapus Data Pupuk"
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
