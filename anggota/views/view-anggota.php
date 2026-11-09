<!-- =========================================================
     TAMPILAN 1: DAFTAR ANGGOTA PETANI
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Anggota Petani</h5>
            <div class="stat-value"><?= count($anggotaList) ?></div>
            <div class="stat-desc">Petani terdaftar di Poktan</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Petak Lahan</h5>
            <div class="stat-value"><?= count($lahanList) ?></div>
            <div class="stat-desc">Terdata di Pemetaan Lahan</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-map-location-dot"></i>
        </div>
    </div>
</div>

<!-- Tabel Daftar Anggota Petani -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Anggota Petani</h3>
            <p>Menampilkan <?= count($anggotaList) ?> anggota petani terdaftar beserta alamat dan keterangan.</p>
        </div>

        <div class="table-card-actions">
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="anggota-search-input" placeholder="Cari nama petani, alamat, keterangan..." onkeyup="filterTableRows('anggota-search-input', '.anggota-row-item')">
            </div>

            <button type="button" class="btn-add-user" onclick="openModalTambahAnggota()">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah Anggota</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-anggota-data">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Nama Petani</th>
                    <th>Alamat</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($anggotaList)): ?>
                <tr>
                    <td colspan="5" class="table-empty-cell">
                        <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                        Belum ada data anggota petani. Silakan klik tombol <b>Tambah Anggota</b> atau jalankan <code>query.sql</code>.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($anggotaList as $row): ?>
                    <?php
                        $nParts = preg_split('/\s+/', trim($row['nama_petani']));
                        if (count($nParts) >= 2) {
                            $initials = strtoupper(mb_substr($nParts[0], 0, 1) . mb_substr($nParts[1], 0, 1));
                        } else {
                            $initials = strtoupper(mb_substr($row['nama_petani'], 0, 2));
                        }
                    ?>
                    <tr class="anggota-row-item">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge"><?= htmlspecialchars($initials) ?></div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_petani']) ?></h4>
                                    <span>ID Anggota: #<?= (int)$row['id'] ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="alamat-cell">
                                <i class="fa-solid fa-location-dot"></i>
                                <span><?= htmlspecialchars($row['alamat']) ?></span>
                            </div>
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
                                    onclick="openModalEditAnggota(<?= htmlspecialchars(json_encode($row)) ?>)"
                                    title="Edit Anggota Petani"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeleteAnggota(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_petani'])) ?>')"
                                    title="Hapus Anggota Petani"
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
