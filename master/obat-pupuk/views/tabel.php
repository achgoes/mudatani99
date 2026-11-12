<!-- Tabel Master Obat, Pupuk, dan Tanaman -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Master Obat, Pupuk, dan Tanaman</h3>
            <p>Menampilkan <?= count($produkList) ?> data produk berdasarkan jenis (Obat, Pupuk, dan Tanaman) beserta keterangan.</p>
        </div>

        <div class="table-card-actions">
            <!-- Urutkan Berdasarkan (Nomor, Nama Produk, dan Jenis) -->
            <div class="sort-select-wrapper" title="Urutkan data berdasarkan Nomor, Nama Produk, atau Jenis">
                <i class="fa-solid fa-arrow-down-short-wide sort-select-icon"></i>
                <select id="produk-sort-select" onchange="handleSortSelectChange(this.value)" class="produk-filter-select" aria-label="Urutkan Data Produk">
                    <option value="nomor-asc">Urutan: Nomor (1 &rarr; Akhir)</option>
                    <option value="nomor-desc">Urutan: Nomor (Akhir &rarr; 1)</option>
                    <option value="nama-asc">Urutan: Nama Produk (A &rarr; Z)</option>
                    <option value="nama-desc">Urutan: Nama Produk (Z &rarr; A)</option>
                    <option value="jenis-asc">Urutan: Jenis (A &rarr; Z)</option>
                    <option value="jenis-desc">Urutan: Jenis (Z &rarr; A)</option>
                </select>
            </div>

            <!-- Filter Jenis -->
            <select id="produk-jenis-filter" onchange="filterProdukTable()" class="produk-filter-select" aria-label="Filter berdasarkan Jenis">
                <option value="ALL">Semua Jenis</option>
                <option value="Obat">Obat</option>
                <option value="Pupuk">Pupuk</option>
                <option value="Tanaman">Tanaman</option>
            </select>

            <!-- Pencarian -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="produk-search-input" placeholder="Cari nama produk, jenis, keterangan..." onkeyup="filterProdukTable()">
            </div>

            <!-- Tombol Tambah Produk -->
            <button type="button" class="btn-add-user" onclick="openModalTambahProduk()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Produk</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-produk-data">
            <thead>
                <tr>
                    <th class="col-nomor sortable-th" id="th-sort-nomor" onclick="sortTableBy('nomor')" title="Klik untuk mengurutkan berdasarkan Nomor">
                        <div class="th-sort-wrapper">
                            <span>Nomor</span>
                            <i class="fa-solid fa-sort-up sort-icon" id="sort-icon-nomor"></i>
                        </div>
                    </th>
                    <th class="sortable-th" id="th-sort-nama" onclick="sortTableBy('nama')" title="Klik untuk mengurutkan berdasarkan Nama Produk">
                        <div class="th-sort-wrapper">
                            <span>Nama Produk</span>
                            <i class="fa-solid fa-sort sort-icon" id="sort-icon-nama"></i>
                        </div>
                    </th>
                    <th class="col-jenis sortable-th" id="th-sort-jenis" onclick="sortTableBy('jenis')" title="Klik untuk mengurutkan berdasarkan Jenis">
                        <div class="th-sort-wrapper">
                            <span>Jenis</span>
                            <i class="fa-solid fa-sort sort-icon" id="sort-icon-jenis"></i>
                        </div>
                    </th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-produk-tbody">
                <?php if (empty($produkList)): ?>
                <tr id="empty-row-initial">
                    <td colspan="5" class="table-empty-cell">
                        <i class="fa-solid fa-box-open text-3xl mb-2 block"></i>
                        Belum ada data master produk. Silakan klik tombol <b>Tambah Produk</b> atau jalankan <code>query.sql</code>.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($produkList as $row): ?>
                    <?php
                        $jenis = $row['jenis'];
                        if ($jenis === 'Obat') {
                            $badgeStyle = 'badge-jenis-obat';
                            $badgeIcon  = 'fa-solid fa-shield-virus';
                            $avatarIcon = 'fa-solid fa-prescription-bottle-medical';
                        } elseif ($jenis === 'Pupuk') {
                            $badgeStyle = 'badge-jenis-pupuk';
                            $badgeIcon  = 'fa-solid fa-flask-vial';
                            $avatarIcon = 'fa-solid fa-flask-vial';
                        } else {
                            $badgeStyle = 'badge-jenis-tanaman';
                            $badgeIcon  = 'fa-solid fa-seedling';
                            $avatarIcon = 'fa-solid fa-wheat-awn';
                        }
                        $currNo = $no++;
                    ?>
                    <tr class="produk-row-item"
                        data-id="<?= (int)$row['id'] ?>"
                        data-nomor="<?= $currNo ?>"
                        data-nama="<?= htmlspecialchars(mb_strtolower($row['nama_produk'], 'UTF-8')) ?>"
                        data-jenis="<?= htmlspecialchars($jenis) ?>">
                        <td class="font-semibold text-slate-500 cell-nomor"><?= $currNo ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge">
                                    <i class="<?= $avatarIcon ?>"></i>
                                </div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_produk']) ?></h4>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-jenis-produk <?= $badgeStyle ?>">
                                <i class="<?= $badgeIcon ?>"></i>
                                <?= htmlspecialchars($jenis) ?>
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
                                    onclick="openModalEditProduk(<?= htmlspecialchars(json_encode($row)) ?>)"
                                    title="Edit Data Produk"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeleteProduk(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_produk'])) ?>', '<?= htmlspecialchars(addslashes($row['jenis'])) ?>')"
                                    title="Hapus Data Produk"
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

