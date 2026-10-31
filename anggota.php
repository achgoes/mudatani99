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
require_once __DIR__ . '/helpers/db-error.php';

// Pastikan tabel anggota_petani tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `anggota_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_petani` VARCHAR(100) NOT NULL,
    `alamat` VARCHAR(255) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Pastikan tabel master_produk tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `master_produk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_produk` VARCHAR(100) NOT NULL,
    `jenis` ENUM('Obat', 'Pupuk', 'Tanaman') NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Pastikan tabel lahan_petani tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `lahan_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `anggota_id` INT(11) NOT NULL,
    `luas_lahan` VARCHAR(50) NOT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `ditanami` VARCHAR(100) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_anggota_id` (`anggota_id`),
    KEY `idx_tanaman_id` (`tanaman_id`),
    CONSTRAINT `fk_lahan_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Pastikan kolom tanaman_id ada pada tabel lahan_petani lama
$colCheck = $conn->query("SHOW COLUMNS FROM `lahan_petani` LIKE 'tanaman_id'");
if ($colCheck && $colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE `lahan_petani` ADD COLUMN `tanaman_id` INT(11) DEFAULT NULL AFTER `luas_lahan`, ADD KEY `idx_tanaman_id` (`tanaman_id`)");
}

// Tentukan tampilan aktif: 'daftar' (default) atau 'lahan' (Pemetaan & Luas Lahan)
$activeView = (isset($_GET['view']) && $_GET['view'] === 'lahan') ? 'lahan' : 'daftar';

$feedbackMessage = "";
$feedbackType    = "";

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $itemName = htmlspecialchars($_GET['name'] ?? 'Data');
    if ($_GET['status'] === 'added') {
        $toastTitle   = "Berhasil Ditambahkan!";
        $toastMessage = ($activeView === 'lahan')
            ? "Data lahan petani <b>$itemName</b> berhasil disimpan."
            : "Data anggota petani <b>$itemName</b> berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle   = "Berhasil Diperbarui!";
        $toastMessage = ($activeView === 'lahan')
            ? "Data lahan petani <b>$itemName</b> berhasil diperbarui."
            : "Data anggota petani <b>$itemName</b> berhasil diperbarui.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = ($activeView === 'lahan')
            ? "Data pemetaan lahan berhasil dihapus."
            : "Data anggota petani berhasil dihapus.";
        $toastType    = "success";
    }
}

// Proses Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // =====================================================================
    // A. CRUD DAFTAR ANGGOTA PETANI
    // =====================================================================
    if ($action === 'add') {
        $nama_petani = trim($_POST['nama_petani'] ?? '');
        $alamat      = trim($_POST['alamat'] ?? '');
        $keterangan  = trim($_POST['keterangan'] ?? '');

        if (empty($nama_petani) || empty($alamat)) {
            $feedbackMessage = "Nama Petani dan Alamat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $stmt = $conn->prepare("INSERT INTO anggota_petani (nama_petani, alamat, keterangan) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $nama_petani, $alamat, $keterangan);
                if ($stmt->execute()) {
                    header("Location: anggota.php?status=added&name=" . urlencode($nama_petani));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('tambah anggota petani', $conn->error);
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'edit') {
        $id          = (int)($_POST['id'] ?? 0);
        $nama_petani = trim($_POST['nama_petani'] ?? '');
        $alamat      = trim($_POST['alamat'] ?? '');
        $keterangan  = trim($_POST['keterangan'] ?? '');

        if ($id <= 0 || empty($nama_petani) || empty($alamat)) {
            $feedbackMessage = "ID, Nama Petani, dan Alamat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $stmt = $conn->prepare("UPDATE anggota_petani SET nama_petani = ?, alamat = ?, keterangan = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("sssi", $nama_petani, $alamat, $keterangan, $id);
                if ($stmt->execute()) {
                    header("Location: anggota.php?status=updated&name=" . urlencode($nama_petani));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('perbarui anggota petani', $conn->error);
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM anggota_petani WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header("Location: anggota.php?status=deleted");
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('hapus anggota petani', $conn->error);
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }

    // =====================================================================
    // B. CRUD PEMETAAN & LUAS LAHAN
    //    - Nama Petani diambil dari anggota_petani
    //    - Ditanami diambil dari master_produk (Filter: jenis = 'Tanaman')
    // =====================================================================
    elseif ($action === 'add_lahan') {
        $activeView = 'lahan';
        $anggota_id = (int)($_POST['anggota_id'] ?? 0);
        $luas_lahan = trim($_POST['luas_lahan'] ?? '');
        $tanaman_id = (int)($_POST['tanaman_id'] ?? 0);
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($anggota_id <= 0 || empty($luas_lahan) || $tanaman_id <= 0) {
            $feedbackMessage = "Nama Petani, Luas Lahan, dan Tanaman wajib dipilih.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            // Pastikan anggota_id valid
            $cekStmt = $conn->prepare("SELECT nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
            $cekStmt->bind_param("i", $anggota_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $petaniRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            // Pastikan tanaman_id valid di master_produk dengan jenis = 'Tanaman'
            $cekTnmStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
            $cekTnmStmt->bind_param("i", $tanaman_id);
            $cekTnmStmt->execute();
            $cekTnmRes = $cekTnmStmt->get_result();
            $tanamanRow = $cekTnmRes ? $cekTnmRes->fetch_assoc() : null;
            $cekTnmStmt->close();

            if (!$petaniRow) {
                $feedbackMessage = "Petani yang dipilih tidak ditemukan di daftar anggota.";
                $feedbackType    = "danger";
            } elseif (!$tanamanRow) {
                $feedbackMessage = "Komoditas tanaman yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $ditanami = $tanamanRow['nama_produk'];
                $stmt = $conn->prepare("INSERT INTO lahan_petani (anggota_id, luas_lahan, tanaman_id, ditanami, keterangan) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("isiss", $anggota_id, $luas_lahan, $tanaman_id, $ditanami, $keterangan);
                    if ($stmt->execute()) {
                        header("Location: anggota.php?view=lahan&status=added&name=" . urlencode($petaniRow['nama_petani']));
                        exit;
                    } else {
                        $feedbackMessage = dbErrorUntukPengguna('tambah data lahan', $conn->error);
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    } elseif ($action === 'edit_lahan') {
        $activeView = 'lahan';
        $id         = (int)($_POST['id'] ?? 0);
        $anggota_id = (int)($_POST['anggota_id'] ?? 0);
        $luas_lahan = trim($_POST['luas_lahan'] ?? '');
        $tanaman_id = (int)($_POST['tanaman_id'] ?? 0);
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($id <= 0 || $anggota_id <= 0 || empty($luas_lahan) || $tanaman_id <= 0) {
            $feedbackMessage = "ID, Nama Petani, Luas Lahan, dan Tanaman wajib dipilih.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $cekStmt = $conn->prepare("SELECT nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
            $cekStmt->bind_param("i", $anggota_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $petaniRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            // Pastikan tanaman_id valid di master_produk dengan jenis = 'Tanaman'
            $cekTnmStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
            $cekTnmStmt->bind_param("i", $tanaman_id);
            $cekTnmStmt->execute();
            $cekTnmRes = $cekTnmStmt->get_result();
            $tanamanRow = $cekTnmRes ? $cekTnmRes->fetch_assoc() : null;
            $cekTnmStmt->close();

            if (!$petaniRow) {
                $feedbackMessage = "Petani yang dipilih tidak ditemukan di daftar anggota.";
                $feedbackType    = "danger";
            } elseif (!$tanamanRow) {
                $feedbackMessage = "Komoditas tanaman yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $ditanami = $tanamanRow['nama_produk'];
                $stmt = $conn->prepare("UPDATE lahan_petani SET anggota_id = ?, luas_lahan = ?, tanaman_id = ?, ditanami = ?, keterangan = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("isisii", $anggota_id, $luas_lahan, $tanaman_id, $ditanami, $keterangan, $id);
                    if ($stmt->execute()) {
                        header("Location: anggota.php?view=lahan&status=updated&name=" . urlencode($petaniRow['nama_petani']));
                        exit;
                    } else {
                        $feedbackMessage = dbErrorUntukPengguna('perbarui data lahan', $conn->error);
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    } elseif ($action === 'delete_lahan') {
        $activeView = 'lahan';
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM lahan_petani WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header("Location: anggota.php?view=lahan&status=deleted");
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('hapus data lahan', $conn->error);
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }
}

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// Ambil seluruh data anggota petani (digunakan baik untuk tabel Daftar Anggota maupun dropdown Nama Petani di Pemetaan Lahan)
$anggotaList = [];
$resAnggota = $conn->query("SELECT id, nama_petani, alamat, keterangan, created_at FROM anggota_petani ORDER BY id ASC");
if ($resAnggota) {
    while ($row = $resAnggota->fetch_assoc()) {
        $anggotaList[] = $row;
    }
}

// Ambil daftar komoditas tanaman dari tabel master_produk (Filter khusus: jenis = 'Tanaman')
$tanamanList = [];
$resTanaman = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Tanaman' ORDER BY id ASC");
if ($resTanaman) {
    while ($row = $resTanaman->fetch_assoc()) {
        $tanamanList[] = $row;
    }
}

// Ambil seluruh data pemetaan & luas lahan (JOIN anggota_petani & LEFT JOIN master_produk jenis Tanaman)
$lahanList = [];
$totalLuasHa = 0.0;
$resLahan = $conn->query("
    SELECT l.id, l.anggota_id, a.nama_petani, l.luas_lahan, l.tanaman_id,
           COALESCE(m.nama_produk, l.ditanami) AS ditanami,
           l.keterangan, l.created_at
    FROM lahan_petani l
    INNER JOIN anggota_petani a ON l.anggota_id = a.id
    LEFT JOIN master_produk m ON l.tanaman_id = m.id AND m.jenis = 'Tanaman'
    ORDER BY l.id ASC
");
if ($resLahan) {
    while ($row = $resLahan->fetch_assoc()) {
        $lahanList[] = $row;
        // Hitung akumulasi luas lahan jika mengandung angka
        if (preg_match('/([0-9]+([.,][0-9]+)?)/', $row['luas_lahan'], $matches)) {
            $totalLuasHa += (float)str_replace(',', '.', $matches[1]);
        }
    }
}

if ($activeView === 'lahan') {
    $pageTitle           = "Pemetaan & Luas Lahan | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Pemetaan & Luas Lahan";
    $currentMenuSubtitle = "Data pemetaan luas lahan garapan anggota petani, komoditas yang ditanami, dan keterangan.";
} else {
    $pageTitle           = "Daftar Anggota Petani | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Daftar Anggota Petani";
    $currentMenuSubtitle = "Manajemen data anggota petani kelompok tani, alamat tempat tinggal, dan keterangan.";
}
$extraCss = ['css/anggota.css'];

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once __DIR__ . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">

            <!-- Navigasi Tab Cepat Antar Submenu Anggota -->
            <div class="anggota-tab-nav">
                <a href="anggota.php" class="anggota-tab-btn <?= ($activeView === 'daftar') ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Daftar Anggota Petani</span>
                </a>
                <a href="anggota.php?view=lahan" class="anggota-tab-btn <?= ($activeView === 'lahan') ? 'active' : '' ?>">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Pemetaan & Luas Lahan</span>
                </a>
            </div>

            <?php if ($activeView === 'lahan'): ?>
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
                                                    <span>ID Anggota: #<?= (int)$row['anggota_id'] ?></span>
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

            <?php else: ?>
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
            <?php endif; ?>

        </div>
    </main>
</div>

<!-- ========================================================
     MODAL ANGGOTA 1: TAMBAH ANGGOTA PETANI
======================================================== -->
<div id="modalTambahAnggota" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-user-plus text-agro-600"></i>
                Tambah Anggota Petani
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahAnggota')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="anggota.php" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-nama">Nama Petani <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-nama"
                        name="nama_petani"
                        required
                        class="modal-input"
                        placeholder="Contoh: Slamet Riyadi"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-alamat">Alamat <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-alamat"
                        name="alamat"
                        required
                        class="modal-input"
                        placeholder="Contoh: Dusun Krajan RT 02 / RW 01, Desa Makmur"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Anggota Aktif / Lahan Blok Utara 1.5 Ha"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahAnggota')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Anggota
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL ANGGOTA 2: EDIT ANGGOTA PETANI
======================================================== -->
<div id="modalEditAnggota" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Anggota Petani
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditAnggota')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="anggota.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-nama">Nama Petani <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-nama"
                        name="nama_petani"
                        required
                        class="modal-input"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-alamat">Alamat <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-alamat"
                        name="alamat"
                        required
                        class="modal-input"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="edit-keterangan"
                        name="keterangan"
                        class="modal-input"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditAnggota')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL ANGGOTA 3: KONFIRMASI HAPUS ANGGOTA PETANI
======================================================== -->
<div id="modalDeleteAnggota" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Anggota Petani
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteAnggota')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="anggota.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-2" id="delete-name-display">-</h5>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus anggota petani ini beserta data lahannya? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteAnggota')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL LAHAN 1: TAMBAH DATA PEMETAAN & LUAS LAHAN
======================================================== -->
<div id="modalTambahLahan" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-map-location-dot text-agro-600"></i>
                Tambah Data Pemetaan & Luas Lahan
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahLahan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="anggota.php?view=lahan" method="POST">
            <input type="hidden" name="action" value="add_lahan">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-lahan-anggota">Nama Petani (Dari Daftar Anggota) <span class="text-rose-500">*</span></label>
                    <select id="add-lahan-anggota" name="anggota_id" required class="modal-input">
                        <option value="">-- Pilih Nama Petani --</option>
                        <?php foreach ($anggotaList as $a): ?>
                            <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['nama_petani']) ?> (<?= htmlspecialchars($a['alamat']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Diambil langsung dari tabel Daftar Anggota Petani.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-lahan-luas">Luas Lahan <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-lahan-luas"
                        name="luas_lahan"
                        required
                        class="modal-input"
                        placeholder="Contoh: 1.5 Ha"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-lahan-tanaman">Ditanami (Dari Master Tanaman) <span class="text-rose-500">*</span></label>
                    <select id="add-lahan-tanaman" name="tanaman_id" required class="modal-input">
                        <option value="">-- Pilih Komoditas Tanaman --</option>
                        <?php foreach ($tanamanList as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Diambil dari Master Obat, Pupuk, dan Tanaman (khusus jenis Tanaman).</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-lahan-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-lahan-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Blok Utara A-1, Irigasi Teknis"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahLahan')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Data Lahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL LAHAN 2: EDIT DATA PEMETAAN & LUAS LAHAN
======================================================== -->
<div id="modalEditLahan" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Pemetaan & Luas Lahan
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditLahan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="anggota.php?view=lahan" method="POST">
            <input type="hidden" name="action" value="edit_lahan">
            <input type="hidden" id="edit-lahan-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-lahan-anggota">Nama Petani (Dari Daftar Anggota) <span class="text-rose-500">*</span></label>
                    <select id="edit-lahan-anggota" name="anggota_id" required class="modal-input">
                        <option value="">-- Pilih Nama Petani --</option>
                        <?php foreach ($anggotaList as $a): ?>
                            <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['nama_petani']) ?> (<?= htmlspecialchars($a['alamat']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-lahan-luas">Luas Lahan <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-lahan-luas"
                        name="luas_lahan"
                        required
                        class="modal-input"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-lahan-tanaman">Ditanami (Dari Master Tanaman) <span class="text-rose-500">*</span></label>
                    <select id="edit-lahan-tanaman" name="tanaman_id" required class="modal-input">
                        <option value="">-- Pilih Komoditas Tanaman --</option>
                        <?php foreach ($tanamanList as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Diambil dari Master Obat, Pupuk, dan Tanaman (khusus jenis Tanaman).</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-lahan-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="edit-lahan-keterangan"
                        name="keterangan"
                        class="modal-input"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditLahan')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL LAHAN 3: KONFIRMASI HAPUS DATA LAHAN
======================================================== -->
<div id="modalDeleteLahan" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Data Lahan
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteLahan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="anggota.php?view=lahan" method="POST">
            <input type="hidden" name="action" value="delete_lahan">
            <input type="hidden" id="delete-lahan-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-lahan-name-display">-</h5>
                <p class="text-xs text-slate-500 mb-2">Luas Lahan: <span id="delete-lahan-luas-display" class="font-bold text-slate-700">-</span></p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data pemetaan lahan ini?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteLahan')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Handlers: Daftar Anggota Petani
function openModalTambahAnggota() {
    const modal = document.getElementById('modalTambahAnggota');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const namaInput = document.getElementById('add-nama');
            if (namaInput) namaInput.focus();
        }, 150);
    }
}

function openModalEditAnggota(data) {
    if (!data) return;
    document.getElementById('edit-id').value         = data.id;
    document.getElementById('edit-nama').value       = data.nama_petani;
    document.getElementById('edit-alamat').value     = data.alamat;
    document.getElementById('edit-keterangan').value = data.keterangan;

    const modal = document.getElementById('modalEditAnggota');
    if (modal) modal.classList.add('show');
}

function openModalDeleteAnggota(id, nama) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;

    const modal = document.getElementById('modalDeleteAnggota');
    if (modal) modal.classList.add('show');
}

// Modal Handlers: Pemetaan & Luas Lahan
function openModalTambahLahan() {
    const modal = document.getElementById('modalTambahLahan');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const sel = document.getElementById('add-lahan-anggota');
            if (sel) sel.focus();
        }, 150);
    }
}

function openModalEditLahan(data) {
    if (!data) return;
    document.getElementById('edit-lahan-id').value         = data.id;
    document.getElementById('edit-lahan-anggota').value    = data.anggota_id;
    document.getElementById('edit-lahan-luas').value       = data.luas_lahan;
    document.getElementById('edit-lahan-keterangan').value = data.keterangan;

    const tanamanSelect = document.getElementById('edit-lahan-tanaman');
    if (tanamanSelect) {
        if (data.tanaman_id) {
            tanamanSelect.value = data.tanaman_id;
        } else {
            // Fallback pencocokan berdasarkan teks nama produk jika tanaman_id belum terisi
            tanamanSelect.value = '';
            for (let i = 0; i < tanamanSelect.options.length; i++) {
                if (tanamanSelect.options[i].text.trim().toLowerCase() === (data.ditanami || '').trim().toLowerCase()) {
                    tanamanSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    const modal = document.getElementById('modalEditLahan');
    if (modal) modal.classList.add('show');
}

function openModalDeleteLahan(id, nama, luas) {
    document.getElementById('delete-lahan-id').value = id;
    document.getElementById('delete-lahan-name-display').innerText = nama;
    document.getElementById('delete-lahan-luas-display').innerText = luas;

    const modal = document.getElementById('modalDeleteLahan');
    if (modal) modal.classList.add('show');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('show');
}

document.querySelectorAll('.custom-modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
            backdrop.classList.remove('show');
        }
    });
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.custom-modal-backdrop.show').forEach(m => m.classList.remove('show'));
    }
});

function filterTableRows(inputId, rowSelector) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const query = input.value.toLowerCase();
    const rows  = document.querySelectorAll(rowSelector);

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

function showToast(title, message, type = 'success', durationMs = 1000) {
    let portal = document.getElementById('toast-portal');
    if (!portal) {
        portal = document.createElement('div');
        portal.id = 'toast-portal';
        portal.className = 'toast-portal-topright';
        document.body.appendChild(portal);
    }

    const toast = document.createElement('div');
    toast.className = `toast-item-card toast-${type}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('title', 'Klik notifikasi untuk menutup seketika');

    const iconClass = (type === 'success') ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';

    toast.innerHTML = `
        <div class="toast-card-inner">
            <div class="toast-icon-wrapper">
                <i class="${iconClass}"></i>
            </div>
            <div class="toast-content-wrapper">
                <div class="toast-title-text">${title}</div>
                <div class="toast-body-text">${message}</div>
            </div>
            <button type="button" class="toast-close-btn" aria-label="Tutup notifikasi">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="toast-progress-container">
            <div class="toast-progress-bar" style="animation-duration: ${durationMs}ms;"></div>
        </div>
    `;

    portal.appendChild(toast);

    let isDismissed = false;
    function dismiss() {
        if (isDismissed) return;
        isDismissed = true;
        toast.classList.add('toast-dismissing');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 320);
    }

    const autoTimer = setTimeout(dismiss, durationMs);

    toast.addEventListener('click', () => {
        clearTimeout(autoTimer);
        dismiss();
    });
}

<?php if (!empty($toastTitle)): ?>
document.addEventListener('DOMContentLoaded', () => {
    showToast(
        <?= json_encode($toastTitle) ?>,
        <?= json_encode($toastMessage) ?>,
        <?= json_encode($toastType) ?>,
        1000
    );

    if (window.history.replaceState) {
        const viewParam = <?= json_encode($activeView === 'lahan' ? '?view=lahan' : '') ?>;
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + viewParam;
        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
