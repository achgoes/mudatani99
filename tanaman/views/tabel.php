<!-- Tabel Pengelolaan Tanaman -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Pengelolaan Tanaman</h3>
            <p>Menampilkan <?= count($tanamanList) ?> data tanaman (diambil dari Master Tanaman) beserta jumlah kemasan (Pcs), jumlah berat, dan keterangan.</p>
        </div>

        <div class="table-card-actions">
            <!-- Pencarian -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="tanaman-search-input" placeholder="Cari nama tanaman, kemasan, berat, keterangan..." onkeyup="filterTanamanTable()">
            </div>

            <!-- Tombol Tambah Pengelolaan Tanaman -->
            <button type="button" class="btn-add-user" onclick="openModalTambahTanaman()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Data Tanaman</span>
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
                        Belum ada data pengelolaan tanaman. Silakan klik tombol <b>Tambah Data Tanaman</b> atau jalankan <code>query.sql</code>.
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
                                    title="Edit Data Tanaman"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeleteTanaman(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_tanaman'])) ?>', '<?= htmlspecialchars(addslashes($row['jumlah_kemasan'])) ?>')"
                                    title="Hapus Data Tanaman"
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

