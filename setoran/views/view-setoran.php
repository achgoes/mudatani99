<!-- =========================================================
     MANAJEMEN SETORAN PETANI - DAFTAR SETORAN
========================================================= -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Setoran</h5>
            <div class="stat-value"><?= count($setoranList) ?></div>
            <div class="stat-desc">Transaksi setoran tercatat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Berat Setoran</h5>
            <div class="stat-value"><?= htmlspecialchars(formatBeratSetoranStr($totalBeratSetoran)) ?></div>
            <div class="stat-desc">Akumulasi seluruh hasil setor</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Petani Penyetor</h5>
            <div class="stat-value"><?= count($petaniPenyetorIds) ?> <span class="text-sm font-semibold">Petani</span></div>
            <div class="stat-desc">Anggota poktan yang menyetor</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Setoran Bulan Ini</h5>
            <div class="stat-value"><?= $setoranBulanIniCount ?> <span class="text-sm font-semibold">Nota</span></div>
            <div class="stat-desc">Total: <?= htmlspecialchars(formatBeratSetoranStr($setoranBulanIniBerat)) ?></div>
        </div>
        <div class="stat-icon-wrap stat-icon-rose">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
    </div>
</div>

<!-- Tabel Daftar Setoran Petani -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Setoran Tanaman Petani</h3>
            <p>Menampilkan <?= count($setoranList) ?> data setoran dari anggota petani berdasarkan master tanaman.</p>
        </div>

        <div class="table-card-actions">
            <!-- Filter Rentang Tanggal -->
            <div class="setoran-date-filter-group">
                <input type="date" id="setoran-date-from" class="setoran-date-input" aria-label="Dari Tanggal" title="Dari Tanggal">
                <span class="setoran-date-sep">s/d</span>
                <input type="date" id="setoran-date-to" class="setoran-date-input" aria-label="Sampai Tanggal" title="Sampai Tanggal">
                <button type="button" class="btn-filter-tanggal" onclick="filterSetoranTable()" title="Terapkan Filter Tanggal">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
            </div>

            <!-- Pencarian -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="setoran-search-input" placeholder="Cari petani, tanaman, kode, ket..." onkeyup="filterSetoranTable()">
            </div>

            <!-- Tombol Tambah Setoran -->
            <button type="button" class="btn-add-user" onclick="openModalTambahSetoran()">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Setoran</span>
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="user-table" id="table-setoran-data">
            <thead>
                <tr>
                    <th class="col-nomor">Nomor</th>
                    <th>Nama Petani</th>
                    <th>Setor Tanaman</th>
                    <th>Berat</th>
                    <th>Jumlah Berat</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-setoran-tbody">
                <?php if (empty($setoranList)): ?>
                <tr id="setoran-empty-default">
                    <td colspan="8" class="table-empty-cell">
                        <i class="fa-solid fa-scale-balanced text-3xl mb-2 block"></i>
                        Belum ada data setoran petani. Silakan klik tombol <b>Tambah Setoran</b> untuk mencatat setoran baru.
                    </td>
                </tr>
                <?php else: ?>
                    <?php
                        $no = 1;
                        foreach ($setoranList as $row):
                            $nParts = preg_split('/\s+/', trim($row['nama_petani']));
                            if (count($nParts) >= 2) {
                                $initials = strtoupper(mb_substr($nParts[0], 0, 1) . mb_substr($nParts[1], 0, 1));
                            } else {
                                $initials = strtoupper(mb_substr($row['nama_petani'], 0, 2));
                            }
                            $tglIso       = preg_match('/^\d{4}-\d{2}-\d{2}/', (string)$row['tanggal'], $mTgl) ? $mTgl[0] : '';
                            $tglFormatted = ($tglIso !== '') ? date('d/m/Y', strtotime($tglIso)) : '-';
                    ?>
                    <tr class="setoran-row-item" data-tanggal="<?= htmlspecialchars($tglIso) ?>">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge"><?= htmlspecialchars($initials) ?></div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($row['nama_petani']) ?></h4>
                                    <span class="badge-kode-setoran"><?= htmlspecialchars($row['kode_setoran']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="setoran-item-stack">
                                <?php foreach ($row['items'] as $sItem): ?>
                                    <span class="badge-nama-tanaman">
                                        <i class="fa-solid fa-wheat-awn"></i>
                                        <?= htmlspecialchars($sItem['nama_tanaman']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <div class="setoran-item-stack">
                                <?php foreach ($row['items'] as $sItem): ?>
                                    <span class="nik-badge">
                                        <i class="fa-solid fa-weight-hanging text-agro-600"></i>
                                        <?= htmlspecialchars($sItem['berat']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge-jumlah-berat" title="Total berat seluruh item setoran">
                                <i class="fa-solid fa-scale-balanced"></i>
                                <?= htmlspecialchars($row['jumlah_berat']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-tanggal-setoran">
                                <i class="fa-regular fa-calendar-days text-slate-500"></i>
                                <?= htmlspecialchars($tglFormatted) ?>
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
                                    class="btn-table-info"
                                    onclick="openModalInfoSetoran(<?= (int)$row['id'] ?>)"
                                    title="Lihat info lengkap setoran petani"
                                    aria-label="Lihat info lengkap setoran petani"
                                >
                                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                </button>

                                <a
                                    href="<?= htmlspecialchars(appUrl('setoran/print-setoran.php?id=' . (int)$row['id'])) ?>"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn-table-print"
                                    title="Cetak bukti setoran petani"
                                    aria-label="Cetak bukti setoran petani"
                                >
                                    <i class="fa-solid fa-print" aria-hidden="true"></i>
                                </a>

                                <button
                                    type="button"
                                    class="btn-table-delete"
                                    onclick="openModalDeleteSetoran(<?= (int)$row['id'] ?>, '<?= htmlspecialchars(addslashes($row['kode_setoran'])) ?>', '<?= htmlspecialchars(addslashes($row['nama_petani'])) ?>', '<?= htmlspecialchars(addslashes($row['jumlah_berat'])) ?>')"
                                    title="Hapus data setoran petani"
                                    aria-label="Hapus data setoran petani"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="setoran-empty-filter" class="hidden">
                        <td colspan="8" class="table-empty-cell">
                            <i class="fa-solid fa-filter-circle-xmark text-3xl mb-2 block"></i>
                            Tidak ada data setoran yang cocok dengan pencarian atau rentang tanggal yang dipilih.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
