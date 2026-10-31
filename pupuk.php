<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi sesi login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/koneksi.php';

// Helper, migrasi tabel database, fungsi kalkulasi & auto-sinkronisasi
require_once __DIR__ . '/helpers/pupuk-helper.php';

// Tentukan tampilan submenu aktif: 'kelola' (default), 'hutang', atau 'riwayat'
$viewParam  = $_GET['view'] ?? 'kelola';
$activeView = in_array($viewParam, ['hutang', 'riwayat'], true) ? $viewParam : 'kelola';

$feedbackMessage = "";
$feedbackType    = "";

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $pName   = htmlspecialchars($_GET['name'] ?? 'Pupuk');
    $rKarung = htmlspecialchars($_GET['karung'] ?? '');
    $rStatus = htmlspecialchars($_GET['st'] ?? '');

    if ($_GET['status'] === 'added') {
        $toastTitle = "Berhasil Ditambahkan!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok pupuk baru <b>$pName</b> berhasil disimpan.";
        } else {
            $toastMessage = "Data hutang pupuk petani <b>$pName</b> disimpan & data Stok Pupuk otomatis dikurangi.";
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'stock_added') {
        $toastTitle   = "Stok Berhasil Ditambahkan!";
        $addK         = htmlspecialchars($_GET['add_k'] ?? '');
        $totK         = htmlspecialchars($_GET['tot_k'] ?? '');
        $totB         = htmlspecialchars($_GET['tot_b'] ?? '');
        $extraDetail  = ($addK !== '' && $totK !== '')
            ? " bertambah <b>+$addK Karung</b>. Total stok saat ini: <b>$totK Karung ($totB)</b>."
            : " berhasil diakumulasikan ke stok yang sudah ada.";
        $toastMessage = "Stok pupuk <b>$pName</b>" . $extraDetail;
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle = "Berhasil Diperbarui!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok pupuk <b>$pName</b> berhasil diperbarui.";
        } else {
            $toastMessage = "Data hutang pupuk petani <b>$pName</b> diperbarui & data Stok Pupuk otomatis disinkronkan.";
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle = "Berhasil Dihapus!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok pupuk berhasil dihapus.";
        } else {
            $extraInfo = $rKarung !== '' ? " Stok <b>$rKarung</b> telah dikembalikan ke Stok Pupuk." : "";
            $toastMessage = "Data hutang piutang berhasil dihapus." . $extraInfo;
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'status_changed') {
        $toastTitle   = "Status Diperbarui!";
        $toastMessage = "Status hutang pupuk <b>$pName</b> berhasil diubah menjadi <b>$rStatus</b>.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'paid') {
        $toastTitle   = "Pembayaran Berhasil!";
        $nominal      = htmlspecialchars($_GET['nominal'] ?? '');
        $nominalText  = ($nominal !== '') ? " sebesar <b>$nominal</b>" : "";
        $paidStatus   = ($rStatus !== '') ? $rStatus : 'Lunas';
        $toastMessage = "Pembayaran hutang pupuk petani <b>$pName</b>" . $nominalText . " telah berhasil dicatat &amp; status kini <b>$paidStatus</b>.";
        $toastType    = "success";
    }
}

// Proses Aksi Form POST (Tambah/Edit/Hapus Pupuk & Hutang, Bayar, Status)
require_once __DIR__ . '/helpers/pupuk-actions.php';

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// =====================================================================
// AMBIL DATA UNTUK TAMPILAN & DROPDOWN
// =====================================================================

// 1. Daftar Anggota Petani
$anggotaList = [];
$resAnggota = $conn->query("SELECT id, nama_petani, alamat, keterangan FROM anggota_petani ORDER BY id ASC");
if ($resAnggota) {
    while ($row = $resAnggota->fetch_assoc()) {
        $row['nama_petani'] = formatTitleCase($row['nama_petani']);
        $anggotaList[] = $row;
    }
}

// 2. Daftar Master Pupuk
$masterPupukList = [];
$resMaster = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Pupuk' ORDER BY id ASC");
if ($resMaster) {
    while ($row = $resMaster->fetch_assoc()) {
        $row['nama_produk'] = formatTitleCase($row['nama_produk']);
        $row['keterangan']  = formatTitleCase($row['keterangan']);
        $masterPupukList[] = $row;
    }
}

// 3. Daftar Stok Pupuk
$pupukList   = [];
$totalKarung = 0;
$totalBerat  = 0.0;

$resPupuk = $conn->query("
    SELECT p.id, p.pupuk_id,
           COALESCE(m.nama_produk, p.nama_pupuk) AS nama_pupuk,
           p.jumlah_karung, p.jumlah_berat, p.keterangan, p.created_at
    FROM pengelolaan_pupuk p
    LEFT JOIN master_produk m ON p.pupuk_id = m.id AND m.jenis = 'Pupuk'
    ORDER BY p.id ASC
");
if ($resPupuk) {
    while ($row = $resPupuk->fetch_assoc()) {
        $row['nama_pupuk']    = formatTitleCase($row['nama_pupuk']);
        $row['jumlah_karung'] = formatTitleCase($row['jumlah_karung']);
        $row['jumlah_berat']  = formatTitleCase($row['jumlah_berat']);
        $row['keterangan']    = formatTitleCase($row['keterangan']);

        $karungNum = parseKarungInt($row['jumlah_karung']);
        $beratNum  = parseBeratFloat($row['jumlah_berat']);
        $row['karung_num']       = $karungNum;
        $row['berat_num']        = $beratNum;
        $row['berat_per_karung'] = ($karungNum > 0) ? round($beratNum / $karungNum, 2) : 50;

        $pupukList[] = $row;

        $totalKarung += $karungNum;
        $totalBerat  += $beratNum;
    }
}

$stokMapByPupukId   = [];
$stokMapByNameLower = [];
foreach ($pupukList as $pk) {
    if (!empty($pk['pupuk_id'])) {
        $stokMapByPupukId[(int)$pk['pupuk_id']] = $pk;
    }
    $cleanNameLower = mb_strtolower(trim($pk['nama_pupuk']));
    if ($cleanNameLower !== '') {
        $stokMapByNameLower[$cleanNameLower] = $pk;
    }
}

// 4. Daftar Hutang Piutang Pupuk
$hutangList          = [];
$totalKarungHutang   = 0;
$totalBeratHutang    = 0.0;
$countLunas          = 0;
$countBelumLunas     = 0;
$karungLunas         = 0;
$karungBelumLunas    = 0;

$resHutang = $conn->query("
    SELECT h.id, h.anggota_id,
           COALESCE(a.nama_petani, h.nama_petani) AS nama_petani,
           a.alamat AS alamat_petani,
           h.pengelolaan_pupuk_id, h.pupuk_id,
           COALESCE(m.nama_produk, p.nama_pupuk, h.nama_pupuk) AS nama_pupuk,
           h.jumlah_karung, h.jumlah_berat,
           COALESCE(h.harga, 'Rp 0') AS harga,
           COALESCE(h.jumlah_hutang, 'Rp 0') AS jumlah_hutang,
           COALESCE(h.sisa_hutang, 'Rp 0') AS sisa_hutang,
           h.keterangan,
           h.tanggal,
           h.status, h.tanggal_lunas, h.created_at
    FROM hutang_pupuk h
    LEFT JOIN anggota_petani a ON h.anggota_id = a.id
    LEFT JOIN pengelolaan_pupuk p ON h.pengelolaan_pupuk_id = p.id
    LEFT JOIN master_produk m ON COALESCE(p.pupuk_id, h.pupuk_id) = m.id AND m.jenis = 'Pupuk'
    ORDER BY h.id ASC
");
if ($resHutang) {
    while ($row = $resHutang->fetch_assoc()) {
        $row['nama_petani']   = formatTitleCase($row['nama_petani']);
        $row['nama_pupuk']    = formatTitleCase($row['nama_pupuk']);
        $row['jumlah_karung'] = formatTitleCase($row['jumlah_karung']);
        $row['jumlah_berat']  = formatTitleCase($row['jumlah_berat']);
        $row['harga']         = formatRupiahStr($row['harga'] ?? 'Rp 0');

        $kNum = parseKarungInt($row['jumlah_karung']);
        $bNum = parseBeratFloat($row['jumlah_berat']);
        $row['karung_num'] = $kNum;
        $row['berat_num']  = $bNum;

        $hargaNum = parseRupiahFloat($row['harga']);
        if (empty($row['jumlah_hutang']) || $row['jumlah_hutang'] === 'Rp 0') {
            $row['jumlah_hutang'] = formatRupiahStr($kNum * $hargaNum);
        } else {
            $row['jumlah_hutang'] = formatRupiahStr($row['jumlah_hutang']);
        }

        if ($row['status'] === 'Lunas') {
            $row['sisa_hutang'] = 'Rp 0';
        } elseif (empty($row['sisa_hutang']) || $row['sisa_hutang'] === 'Rp 0') {
            $row['sisa_hutang'] = $row['jumlah_hutang'];
        } else {
            $row['sisa_hutang'] = formatRupiahStr($row['sisa_hutang']);
        }

        $row['keterangan'] = formatTitleCase($row['keterangan']);
        $row['tanggal']    = !empty($row['tanggal']) ? $row['tanggal'] : substr($row['created_at'], 0, 10);

        $hutangList[] = $row;

        $totalKarungHutang += $kNum;
        $totalBeratHutang  += $bNum;

        if ($row['status'] === 'Lunas') {
            $countLunas++;
            $karungLunas += $kNum;
        } else {
            $countBelumLunas++;
            $karungBelumLunas += $kNum;
        }
    }
}

// 5. Riwayat Pembayaran Hutang
$pembayaranList      = [];
$pembayaranByHutang  = [];
$totalTerbayar       = 0;
$countPelunasan      = 0;
$petaniPembayarIds   = [];

$resBayar = $conn->query("
    SELECT b.id, b.hutang_id, b.anggota_id,
           COALESCE(a.nama_petani, b.nama_petani) AS nama_petani,
           b.nama_pupuk, b.pembayaran_ke, b.tanggal_bayar,
           b.total_hutang, b.sisa_sebelum, b.jumlah_bayar, b.sisa_hutang,
           b.metode_bayar, b.status, b.keterangan, b.dicatat_oleh, b.created_at
    FROM pembayaran_hutang_pupuk b
    LEFT JOIN anggota_petani a ON b.anggota_id = a.id
    ORDER BY b.tanggal_bayar DESC, b.id DESC
");
if ($resBayar) {
    while ($row = $resBayar->fetch_assoc()) {
        $row['nama_petani']      = formatTitleCase($row['nama_petani']);
        $row['nama_pupuk']       = formatTitleCase($row['nama_pupuk']);
        $row['keterangan']       = formatTitleCase($row['keterangan'] ?: '-');
        $row['dicatat_oleh']     = formatTitleCase($row['dicatat_oleh'] ?: '-');
        $row['total_hutang_str'] = formatRupiahStr($row['total_hutang']);
        $row['sisa_sebelum_str'] = formatRupiahStr($row['sisa_sebelum']);
        $row['jumlah_bayar_str'] = formatRupiahStr($row['jumlah_bayar']);
        $row['sisa_hutang_str']  = formatRupiahStr($row['sisa_hutang']);

        $pembayaranList[] = $row;
        $pembayaranByHutang[(int)$row['hutang_id']][] = $row;

        $totalTerbayar += (int)$row['jumlah_bayar'];
        $petaniPembayarIds[(int)$row['anggota_id']] = true;
        if ($row['status'] === 'Lunas') {
            $countPelunasan++;
        }
    }
}

$totalSisaPiutang = 0.0;
$hutangInfoMap    = [];
foreach ($hutangList as $hRow) {
    if ($hRow['status'] !== 'Lunas') {
        $totalSisaPiutang += parseRupiahFloat($hRow['sisa_hutang']);
    }
    $hutangInfoMap[(int)$hRow['id']] = $hRow;
}

// Konfigurasi judul halaman
if ($activeView === 'hutang') {
    $pageTitle           = "Hutang Piutang Pupuk | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Hutang Piutang Pupuk";
    $currentMenuSubtitle = "Pencatatan hutang piutang pupuk anggota petani yang tersinkronisasi otomatis dengan Stok Pupuk.";
} elseif ($activeView === 'riwayat') {
    $pageTitle           = "Riwayat Hutang / Pelunasan Pupuk | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Riwayat Hutang / Pelunasan Pupuk";
    $currentMenuSubtitle = "Jejak setiap pembayaran hutang pupuk petani: tanggal, nominal, metode bayar, dan sisa hutang setelah bayar.";
} else {
    $pageTitle           = "Stok Pupuk | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Stok Pupuk";
    $currentMenuSubtitle = "Manajemen inventaris stok pupuk kelompok tani yang tersinkronisasi otomatis dengan transaksi Hutang Piutang.";
}
$extraCss = ['css/pupuk.css'];

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once __DIR__ . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">

            <!-- Navigasi Tab Cepat Antar Submenu Pupuk -->
            <div class="pupuk-tab-nav">
                <a href="pupuk.php" class="pupuk-tab-btn <?= ($activeView === 'kelola') ? 'active' : '' ?>">
                    <i class="fa-solid fa-flask-vial"></i>
                    <span>Stok Pupuk</span>
                    <span class="pupuk-tab-badge"><?= count($pupukList) ?></span>
                </a>
                <a href="pupuk.php?view=hutang" class="pupuk-tab-btn <?= ($activeView === 'hutang') ? 'active' : '' ?>">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Hutang Piutang</span>
                    <span class="pupuk-tab-badge"><?= $countBelumLunas ?> Belum Lunas</span>
                </a>
                <a href="pupuk.php?view=riwayat" class="pupuk-tab-btn <?= ($activeView === 'riwayat') ? 'active' : '' ?>">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Riwayat Hutang / Pelunasan</span>
                    <span class="pupuk-tab-badge"><?= count($pembayaranList) ?></span>
                </a>
            </div>

            <!-- Banner Informasi Auto-Sinkronisasi Antar Tabel -->
            <div class="sync-info-banner">
                <div class="sync-banner-left">
                    <div class="sync-banner-icon">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </div>
                    <div>
                        <strong>Auto-Sinkronisasi Aktif:</strong>
                        Saat pupuk dipakai pada <b>Hutang Piutang</b>, stok karung &amp; berat pada <b>Stok Pupuk</b> otomatis berkurang. Jika data hutang dihapus, jumlah karung otomatis kembali ke stok gudang.
                    </div>
                </div>
            </div>

            <?php if ($activeView === 'kelola'): ?>
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

            <?php else: ?>
                <!-- =========================================================
                     TAMPILAN 2 & 3: HUTANG PIUTANG / RIWAYAT HUTANG & PELUNASAN
                ========================================================= -->
                <?php if ($activeView === 'riwayat'): ?>
                <div class="user-stats-grid">
                    <div class="user-stat-card">
                        <div class="stat-info">
                            <h5>Transaksi Pembayaran</h5>
                            <div class="stat-value"><?= count($pembayaranList) ?> <span class="text-sm font-semibold">Kali</span></div>
                            <div class="stat-desc">Dari <?= count($petaniPembayarIds) ?> petani</div>
                        </div>
                        <div class="stat-icon-wrap stat-icon-blue">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>

                    <div class="user-stat-card">
                        <div class="stat-info">
                            <h5>Total Uang Masuk</h5>
                            <div class="stat-value stat-value-rupiah"><?= htmlspecialchars(formatRupiahStr($totalTerbayar)) ?></div>
                            <div class="stat-desc">Jumlah semua pembayaran tercatat</div>
                        </div>
                        <div class="stat-icon-wrap stat-icon-green">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>
                    </div>

                    <div class="user-stat-card">
                        <div class="stat-info">
                            <h5>Pembayaran Pelunasan</h5>
                            <div class="stat-value"><?= $countPelunasan ?> <span class="text-sm font-semibold">Kali</span></div>
                            <div class="stat-desc">Pembayaran yang membuat hutang lunas</div>
                        </div>
                        <div class="stat-icon-wrap stat-icon-amber">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>

                    <div class="user-stat-card">
                        <div class="stat-info">
                            <h5>Sisa Piutang</h5>
                            <div class="stat-value stat-value-rupiah"><?= htmlspecialchars(formatRupiahStr($totalSisaPiutang)) ?></div>
                            <div class="stat-desc"><?= $countBelumLunas ?> hutang belum lunas</div>
                        </div>
                        <div class="stat-icon-wrap stat-icon-rose">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>
                    </div>
                </div>
                <?php else: ?>
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
                <?php endif; ?>

                <!-- Tabel Hutang Piutang / Riwayat Hutang & Pelunasan -->
                <div class="user-table-card">
                    <div class="table-card-header">
                        <div class="table-title-group">
                            <?php if ($activeView === 'riwayat'): ?>
                                <h3>Riwayat Pembayaran Hutang Pupuk Petani</h3>
                                <p>Menampilkan <?= count($pembayaranList) ?> transaksi pembayaran, terbaru di atas. Klik <b>Info</b> untuk melihat seluruh jejak pembayaran satu hutang.</p>
                            <?php else: ?>
                                <h3>Daftar Hutang Piutang Pupuk Petani</h3>
                                <p>Menampilkan <?= count($hutangList) ?> data hutang piutang pupuk petani (tersinkronisasi otomatis dengan data Stok Pupuk).</p>
                            <?php endif; ?>
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
                                <input type="text" id="hutang-search-input" placeholder="<?= ($activeView === 'riwayat') ? 'Cari petani, pupuk, metode, tanggal...' : 'Cari petani, pupuk, karung, status...' ?>" onkeyup="filterHutangTable()">
                            </div>

                            <?php if ($activeView === 'hutang'): ?>
                                <!-- Tombol Tambah Hutang Piutang -->
                                <button type="button" class="btn-add-user" onclick="openModalTambahHutang()">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Tambah Hutang Pupuk</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($activeView === 'riwayat'): ?>
                    <!-- Tabel Riwayat Pembayaran (1 baris = 1 kali pembayaran) -->
                    <div class="table-responsive-custom">
                        <table class="user-table" id="table-riwayat-bayar">
                            <thead>
                                <tr>
                                    <th class="col-nomor">Nomor</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Nama Petani</th>
                                    <th>Nama Pupuk</th>
                                    <th>Total Hutang</th>
                                    <th>Jumlah Bayar</th>
                                    <th>Sisa Hutang</th>
                                    <th>Metode Bayar</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                    <th class="col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-hutang-tbody">
                                <?php if (empty($pembayaranList)): ?>
                                <tr>
                                    <td colspan="11" class="table-empty-cell">
                                        <i class="fa-solid fa-clock-rotate-left text-3xl mb-2 block"></i>
                                        Belum ada pembayaran yang tercatat. Pembayaran akan muncul di sini setelah tombol <b>Bayar</b> dipakai di menu
                                        <a href="pupuk.php?view=hutang" class="link-riwayat-ke-hutang">Hutang Piutang</a>.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php
                                        $metodeIconMap = [
                                            'Tunai'              => 'fa-money-bill',
                                            'Transfer'           => 'fa-building-columns',
                                            'Potong Hasil Panen' => 'fa-wheat-awn',
                                        ];
                                        $no = 1;
                                        foreach ($pembayaranList as $bp):
                                        $bpParts = preg_split('/\s+/', trim($bp['nama_petani']));
                                        $bpInitials = (count($bpParts) >= 2)
                                            ? strtoupper(mb_substr($bpParts[0], 0, 1) . mb_substr($bpParts[1], 0, 1))
                                            : strtoupper(mb_substr($bp['nama_petani'], 0, 2));
                                        $bpLunas   = ($bp['status'] === 'Lunas');
                                        $bpTgl     = (!empty($bp['tanggal_bayar']) && $bp['tanggal_bayar'] !== '0000-00-00')
                                            ? date('d/m/Y', strtotime($bp['tanggal_bayar']))
                                            : '-';
                                        $bpIcon    = $metodeIconMap[$bp['metode_bayar']] ?? 'fa-money-bill';
                                    ?>
                                    <tr class="hutang-row-item" data-status="<?= htmlspecialchars($bp['status']) ?>">
                                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                                        <td>
                                            <span class="badge-tanggal-pupuk">
                                                <i class="fa-regular fa-calendar-days text-slate-500"></i>
                                                <?= htmlspecialchars($bpTgl) ?>
                                            </span>
                                            <span class="bayar-ke-label">Pembayaran ke-<?= (int)$bp['pembayaran_ke'] ?></span>
                                        </td>
                                        <td>
                                            <div class="user-profile-cell">
                                                <div class="user-avatar-badge"><?= htmlspecialchars($bpInitials) ?></div>
                                                <div class="user-name-cell">
                                                    <h4><?= htmlspecialchars($bp['nama_petani']) ?></h4>
                                                    <span>ID Anggota: #<?= (int)$bp['anggota_id'] ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-nama-pupuk">
                                                <i class="fa-solid fa-flask-vial"></i>
                                                <?= htmlspecialchars($bp['nama_pupuk']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-jumlah-hutang" title="Total hutang awal">
                                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                                <?= htmlspecialchars($bp['total_hutang_str']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-jumlah-bayar" title="Nominal yang dibayar pada transaksi ini">
                                                <i class="fa-solid fa-money-bill-wave"></i>
                                                <?= htmlspecialchars($bp['jumlah_bayar_str']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="<?= ((int)$bp['sisa_hutang'] <= 0) ? 'badge-sisa-lunas' : 'badge-sisa-belum' ?>" title="Sisa hutang setelah pembayaran ini">
                                                <i class="fa-solid fa-coins"></i>
                                                <?= htmlspecialchars($bp['sisa_hutang_str']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-metode-bayar">
                                                <i class="fa-solid <?= $bpIcon ?>"></i>
                                                <?= htmlspecialchars($bp['metode_bayar']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($bpLunas): ?>
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
                                            <span class="badge-keterangan">
                                                <i class="fa-regular fa-note-sticky"></i>
                                                <?= htmlspecialchars($bp['keterangan'] ?: '-') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-btn-group justify-end">
                                                <button
                                                    type="button"
                                                    class="btn-table-info"
                                                    onclick="openModalInfoHutang(<?= (int)$bp['hutang_id'] ?>, <?= (int)$bp['id'] ?>)"
                                                    title="Lihat detail hutang dan seluruh jejak pembayarannya"
                                                >
                                                    <i class="fa-solid fa-circle-info"></i>
                                                    <span>Info</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
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
                                                    <form action="pupuk.php?view=<?= urlencode($activeView) ?>" method="POST" class="inline">
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
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<!-- ========================================================
     MODAL COMPONENTS (STOK, HUTANG & INFO)
======================================================== -->
<?php
require_once __DIR__ . '/partials/pupuk/modals-stok.php';
require_once __DIR__ . '/partials/pupuk/modals-hutang.php';
require_once __DIR__ . '/partials/pupuk/modal-info.php';
?>

<!-- ========================================================
     JAVASCRIPT INTEGRATION & DATA BINDING
======================================================== -->
<script>
    const HUTANG_INFO_MAP = <?= json_encode($hutangInfoMap, JSON_UNESCAPED_UNICODE) ?>;
    const PEMBAYARAN_BY_HUTANG = <?= json_encode($pembayaranByHutang, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="js/pupuk.js"></script>

<?php if (!empty($toastTitle)): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    showToast(
        <?= json_encode($toastTitle) ?>,
        <?= json_encode($toastMessage) ?>,
        <?= json_encode($toastType) ?>,
        1400
    );

    if (window.history.replaceState) {
        const viewParam = <?= json_encode($activeView !== 'kelola' ? '?view=' . $activeView : '') ?>;
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + viewParam;
        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
