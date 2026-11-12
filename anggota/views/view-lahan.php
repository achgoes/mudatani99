<!-- =========================================================
     TAMPILAN 2: PEMETAAN & LUAS LAHAN
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Data Lahan</h5>
            <div class="stat-value"><?= count($lahanList) ?></div>
            <div class="stat-desc">Petak lahan terdaftar</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-map-location-dot"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Estimasi Luas</h5>
            <div class="stat-value"><?= number_format($totalLuasHa, 2, ',', '.') ?> <span class="text-sm font-semibold">Ha</span></div>
            <div class="stat-desc">Akumulasi lahan garapan</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-ruler-combined"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Daftar Anggota</h5>
            <div class="stat-value"><?= count($anggotaList) ?></div>
            <div class="stat-desc">Petani tersedia dipilih</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>
</div>

<!-- Tabel Pemetaan & Luas Lahan -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Pemetaan & Luas Lahan Petani</h3>
            <p>Menampilkan <?= count($lahanList) ?> data lahan beserta nama petani (dari daftar anggota), luas lahan, komoditas yang ditanami, dan keterangan.</p>
        </div>

        <div class="table-card-actions">
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="lahan-search-input" placeholder="Cari petani, luas, tanaman, keterangan..." onkeyup="filterTableRows('lahan-search-input', '.lahan-row-item')">
            </div>

            <button type="button" class="btn-add-user" onclick="openModalTambahLahan()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Data Lahan</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-lahan-data">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Nama Petani</th>
                    <th>Luas Lahan</th>
                    <th>Ditanami</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lahanList)): ?>
                <tr>
                    <td colspan="6" class="table-empty-cell">
                        <i class="fa-solid fa-map text-3xl mb-2 block"></i>
                        Belum ada data pemetaan lahan. Silakan klik tombol <b>Tambah Data Lahan</b>.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($lahanList as $row): ?>
                    <?php
                        $nParts = preg_split('/\s+/', trim($row['nama_petani']));
                        if (count($nParts) >= 2) {
                            $initials = strtoupper(mb_substr($nParts[0], 0, 1) . mb_substr($nParts[1], 0, 1));
                        } else {
                            $initials = strtoupper(mb_substr($row['nama_petani'], 0, 2));
                        }
                    ?>
                    <tr class="lahan-row-item">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge"><?= htmlspecialchars($initials) ?></div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_petani']) ?></h4>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="nik-badge">
                                <i class="fa-solid fa-ruler-combined text-agro-600"></i>
                                <?= htmlspecialchars($row['luas_lahan']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-tanaman-lahan">
                                <i class="fa-solid fa-seedling"></i>
                                <?= htmlspecialchars($row['ditanami']) ?>
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
                                    onclick="openModalEditLahan(<?= htmlspecialchars(json_encode($row)) ?>)"
                                    title="Edit Data Lahan"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeleteLahan(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_petani'])) ?>', '<?= htmlspecialchars(addslashes($row['luas_lahan'])) ?>')"
                                    title="Hapus Data Lahan"
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
