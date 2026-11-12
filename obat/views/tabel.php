<!-- Tabel Pengelolaan Obat -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Pengelolaan Obat</h3>
            <p>Menampilkan <?= count($obatList) ?> data obat (diambil dari Master Obat) beserta jumlah kemasan (Pcs / Botol), jumlah berat, dan keterangan.</p>
        </div>

        <div class="table-card-actions">
            <!-- Pencarian -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="obat-search-input" placeholder="Cari nama obat, kemasan, berat, keterangan..." onkeyup="filterObatTable()">
            </div>

            <!-- Tombol Tambah Pengelolaan Obat -->
            <button type="button" class="btn-add-user" onclick="openModalTambahObat()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Data Obat</span>
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
                        Belum ada data pengelolaan obat. Silakan klik tombol <b>Tambah Data Obat</b> atau jalankan <code>query.sql</code>.
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
                                    title="Edit Data Obat"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeleteObat(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_obat'])) ?>', '<?= htmlspecialchars(addslashes($row['jumlah_kemasan'])) ?>')"
                                    title="Hapus Data Obat"
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

