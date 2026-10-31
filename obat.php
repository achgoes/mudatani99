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

// Pastikan tabel master_produk tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `master_produk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_produk` VARCHAR(100) NOT NULL,
    `jenis` ENUM('Obat', 'Pupuk', 'Tanaman') NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Pastikan tabel pengelolaan_obat tersedia di database dengan kolom jumlah_kemasan
$conn->query("CREATE TABLE IF NOT EXISTS `pengelolaan_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_obat_id` (`obat_id`),
    CONSTRAINT `fk_pengelolaan_obat_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Migrasi otomatis jika tabel lama masih menggunakan kolom jumlah_karung
$colOld = $conn->query("SHOW COLUMNS FROM `pengelolaan_obat` LIKE 'jumlah_karung'");
if ($colOld && $colOld->num_rows > 0) {
    $conn->query("ALTER TABLE `pengelolaan_obat` CHANGE COLUMN `jumlah_karung` `jumlah_kemasan` VARCHAR(50) NOT NULL");
}

/**
 * Format teks otomatis menjadi Title Case (Huruf depan kapital, sisanya kecil)
 */
function formatTitleCase(string $text): string {
    $clean = trim(preg_replace('/\s+/', ' ', $text));
    if ($clean === '' || $clean === '-') {
        return $clean;
    }
    return mb_convert_case(mb_strtolower($clean, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

$feedbackMessage = "";
$feedbackType    = "";

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $pName = htmlspecialchars($_GET['name'] ?? 'Obat');
    if ($_GET['status'] === 'added') {
        $toastTitle   = "Berhasil Ditambahkan!";
        $toastMessage = "Data pengelolaan obat <b>$pName</b> berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle   = "Berhasil Diperbarui!";
        $toastMessage = "Data pengelolaan obat <b>$pName</b> berhasil diperbarui.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = "Data pengelolaan obat berhasil dihapus.";
        $toastType    = "success";
    }
}

$allowedSatuanObat = ['Pcs', 'Botol'];

// Proses Aksi Form POST (Tambah, Edit, Hapus Pengelolaan Obat)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TAMBAH PENGELOLAAN OBAT
    if ($action === 'add') {
        $obat_id        = (int)($_POST['obat_id'] ?? 0);
        $jumlah_angka   = trim($_POST['jumlah_angka'] ?? '');
        $satuan_kemasan = trim($_POST['satuan_kemasan'] ?? '');
        $jumlah_berat   = formatTitleCase($_POST['jumlah_berat'] ?? '');
        $keterangan     = formatTitleCase($_POST['keterangan'] ?? '');

        if ($obat_id <= 0 || $jumlah_angka === '' || !in_array($satuan_kemasan, $allowedSatuanObat, true) || empty($jumlah_berat)) {
            $feedbackMessage = "Nama Obat, Jumlah Kemasan (Pcs / Botol), dan Jumlah Berat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            // Ambil angka bersih lalu gabungkan dengan satuan pilihan (Pcs / Botol)
            $angkaBersih    = preg_replace('/[^0-9.,]/', '', $jumlah_angka);
            $jumlah_kemasan = ($angkaBersih !== '' ? $angkaBersih : $jumlah_angka) . ' ' . $satuan_kemasan;

            if (preg_match('/^[0-9]+([.,][0-9]+)?$/', $jumlah_berat)) {
                $jumlah_berat .= ' Kg';
            }

            // Pastikan obat_id valid di master_produk dengan jenis = 'Obat'
            $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Obat' LIMIT 1");
            $cekStmt->bind_param("i", $obat_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $obatRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            if (!$obatRow) {
                $feedbackMessage = "Nama obat yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $nama_obat = formatTitleCase($obatRow['nama_produk']);
                $stmt = $conn->prepare("INSERT INTO pengelolaan_obat (obat_id, nama_obat, jumlah_kemasan, jumlah_berat, keterangan) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("issss", $obat_id, $nama_obat, $jumlah_kemasan, $jumlah_berat, $keterangan);
                    if ($stmt->execute()) {
                        header("Location: obat.php?status=added&name=" . urlencode($nama_obat));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menambahkan data obat: " . $conn->error;
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    }

    // 2. EDIT PENGELOLAAN OBAT
    elseif ($action === 'edit') {
        $id             = (int)($_POST['id'] ?? 0);
        $obat_id        = (int)($_POST['obat_id'] ?? 0);
        $jumlah_angka   = trim($_POST['jumlah_angka'] ?? '');
        $satuan_kemasan = trim($_POST['satuan_kemasan'] ?? '');
        $jumlah_berat   = formatTitleCase($_POST['jumlah_berat'] ?? '');
        $keterangan     = formatTitleCase($_POST['keterangan'] ?? '');

        if ($id <= 0 || $obat_id <= 0 || $jumlah_angka === '' || !in_array($satuan_kemasan, $allowedSatuanObat, true) || empty($jumlah_berat)) {
            $feedbackMessage = "ID, Nama Obat, Jumlah Kemasan (Pcs / Botol), dan Jumlah Berat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $angkaBersih    = preg_replace('/[^0-9.,]/', '', $jumlah_angka);
            $jumlah_kemasan = ($angkaBersih !== '' ? $angkaBersih : $jumlah_angka) . ' ' . $satuan_kemasan;

            if (preg_match('/^[0-9]+([.,][0-9]+)?$/', $jumlah_berat)) {
                $jumlah_berat .= ' Kg';
            }

            // Pastikan obat_id valid di master_produk dengan jenis = 'Obat'
            $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Obat' LIMIT 1");
            $cekStmt->bind_param("i", $obat_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $obatRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            if (!$obatRow) {
                $feedbackMessage = "Nama obat yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $nama_obat = formatTitleCase($obatRow['nama_produk']);
                $stmt = $conn->prepare("UPDATE pengelolaan_obat SET obat_id = ?, nama_obat = ?, jumlah_kemasan = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("issssi", $obat_id, $nama_obat, $jumlah_kemasan, $jumlah_berat, $keterangan, $id);
                    if ($stmt->execute()) {
                        header("Location: obat.php?status=updated&name=" . urlencode($nama_obat));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal memperbarui data obat: " . $conn->error;
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    }

    // 3. HAPUS PENGELOLAAN OBAT
    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM pengelolaan_obat WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header("Location: obat.php?status=deleted");
                    exit;
                } else {
                    $feedbackMessage = "Gagal menghapus data obat: " . $conn->error;
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

// Ambil daftar master obat (khusus jenis = 'Obat' dari tabel master_produk)
$masterObatList = [];
$resMaster = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Obat' ORDER BY id ASC");
if ($resMaster) {
    while ($row = $resMaster->fetch_assoc()) {
        $row['nama_produk'] = formatTitleCase($row['nama_produk']);
        $row['keterangan']  = formatTitleCase($row['keterangan']);
        $masterObatList[] = $row;
    }
}

// Ambil daftar pengelolaan_obat (LEFT JOIN dengan master_produk jenis = 'Obat')
$obatList     = [];
$totalKemasan = 0;
$totalBerat   = 0.0;

$resObat = $conn->query("
    SELECT o.id, o.obat_id,
           COALESCE(m.nama_produk, o.nama_obat) AS nama_obat,
           o.jumlah_kemasan, o.jumlah_berat, o.keterangan, o.created_at
    FROM pengelolaan_obat o
    LEFT JOIN master_produk m ON o.obat_id = m.id AND m.jenis = 'Obat'
    ORDER BY o.id ASC
");
if ($resObat) {
    while ($row = $resObat->fetch_assoc()) {
        $row['nama_obat']      = formatTitleCase($row['nama_obat']);
        $row['jumlah_kemasan'] = formatTitleCase($row['jumlah_kemasan']);
        $row['jumlah_berat']   = formatTitleCase($row['jumlah_berat']);
        $row['keterangan']     = formatTitleCase($row['keterangan']);
        $obatList[] = $row;

        if (preg_match('/([0-9]+([.,][0-9]+)?)/', $row['jumlah_kemasan'], $mKemasan)) {
            $totalKemasan += (int)str_replace(',', '.', $mKemasan[1]);
        }
        if (preg_match('/([0-9]+([.,][0-9]+)?)/', $row['jumlah_berat'], $mBerat)) {
            $totalBerat += (float)str_replace(',', '.', $mBerat[1]);
        }
    }
}

$pageTitle           = "Pengelolaan Obat | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle    = "Pengelolaan Obat";
$currentMenuSubtitle = "Manajemen inventaris obat pertanian kelompok tani berdasarkan data Master Obat, jumlah kemasan (Pcs / Botol), berat, dan keterangan.";
$extraCss            = ['css/obat.css'];

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once __DIR__ . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">

            <!-- Ringkasan Statistik Pengelolaan Obat -->
            <div class="user-stats-grid">
                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Total Data Obat</h5>
                        <div class="stat-value"><?= count($obatList) ?></div>
                        <div class="stat-desc">Data inventaris tercatat</div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-green">
                        <i class="fa-solid fa-shield-virus"></i>
                    </div>
                </div>

                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Total Jumlah Kemasan</h5>
                        <div class="stat-value"><?= number_format($totalKemasan, 0, ',', '.') ?> <span class="text-sm font-semibold">Pcs / Botol</span></div>
                        <div class="stat-desc">Akumulasi kemasan obat</div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-amber">
                        <i class="fa-solid fa-prescription-bottle-medical"></i>
                    </div>
                </div>

                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Total Jumlah Berat</h5>
                        <div class="stat-value"><?= number_format($totalBerat, 0, ',', '.') ?> <span class="text-sm font-semibold">Kg</span></div>
                        <div class="stat-desc">Akumulasi berat gudang</div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-blue">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </div>
                </div>
            </div>

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
                                                <span>ID Master: #<?= (int)($row['obat_id'] ?? $row['id']) ?></span>
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

        </div>
    </main>
</div>

<!-- ========================================================
     MODAL 1: TAMBAH PENGELOLAAN OBAT
======================================================== -->
<div id="modalTambahObat" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-shield-virus text-agro-600"></i>
                Tambah Data Pengelolaan Obat
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahObat')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="obat.php" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-obat-id">Nama Obat (Dari Master - Obat) <span class="text-rose-500">*</span></label>
                    <select id="add-obat-id" name="obat_id" required class="modal-input">
                        <option value="">-- Pilih Nama Obat --</option>
                        <?php foreach ($masterObatList as $mo): ?>
                            <option value="<?= (int)$mo['id'] ?>"><?= htmlspecialchars($mo['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Diambil dari menu Master Obat, Pupuk, dan Tanaman (khusus jenis Obat).</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jumlah-angka">Jumlah Kemasan <span class="text-rose-500">*</span></label>
                    <div class="input-kemasan-group">
                        <input
                            type="number"
                            min="1"
                            step="any"
                            id="add-jumlah-angka"
                            name="jumlah_angka"
                            required
                            class="modal-input"
                            placeholder="Contoh: 12"
                        >
                        <select id="add-satuan-kemasan" name="satuan_kemasan" required class="modal-input select-satuan-obat">
                            <option value="Pcs">Pcs</option>
                            <option value="Botol">Botol</option>
                        </select>
                    </div>
                    <p class="modal-helper-text">Pilih satuan kemasan obat: <b>Pcs</b> atau <b>Botol</b>.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jumlah-berat">Jumlah Berat <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-jumlah-berat"
                        name="jumlah_berat"
                        required
                        class="modal-input"
                        placeholder="Contoh: 12 Kg"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Stok Obat Pengendali Hama Gudang Poktan"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahObat')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Data Obat
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 2: EDIT PENGELOLAAN OBAT
======================================================== -->
<div id="modalEditObat" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Pengelolaan Obat
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditObat')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="obat.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-obat-id">Nama Obat (Dari Master - Obat) <span class="text-rose-500">*</span></label>
                    <select id="edit-obat-id" name="obat_id" required class="modal-input">
                        <option value="">-- Pilih Nama Obat --</option>
                        <?php foreach ($masterObatList as $mo): ?>
                            <option value="<?= (int)$mo['id'] ?>"><?= htmlspecialchars($mo['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-jumlah-angka">Jumlah Kemasan <span class="text-rose-500">*</span></label>
                    <div class="input-kemasan-group">
                        <input
                            type="number"
                            min="1"
                            step="any"
                            id="edit-jumlah-angka"
                            name="jumlah_angka"
                            required
                            class="modal-input"
                        >
                        <select id="edit-satuan-kemasan" name="satuan_kemasan" required class="modal-input select-satuan-obat">
                            <option value="Pcs">Pcs</option>
                            <option value="Botol">Botol</option>
                        </select>
                    </div>
                    <p class="modal-helper-text">Pilih satuan kemasan obat: <b>Pcs</b> atau <b>Botol</b>.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-jumlah-berat">Jumlah Berat <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-jumlah-berat"
                        name="jumlah_berat"
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
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditObat')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 3: KONFIRMASI HAPUS PENGELOLAAN OBAT
======================================================== -->
<div id="modalDeleteObat" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Data Obat
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteObat')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="obat.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-name-display">-</h5>
                <p class="text-xs text-slate-500 mb-2">Jumlah Kemasan: <span id="delete-kemasan-display" class="font-bold text-slate-700">-</span></p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data pengelolaan obat ini?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteObat')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambahObat() {
    const modal = document.getElementById('modalTambahObat');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const sel = document.getElementById('add-obat-id');
            if (sel) sel.focus();
        }, 150);
    }
}

function openModalEditObat(data) {
    if (!data) return;
    document.getElementById('edit-id').value           = data.id;
    document.getElementById('edit-jumlah-berat').value = data.jumlah_berat;
    document.getElementById('edit-keterangan').value   = data.keterangan;

    // Pisahkan angka dan satuan (Pcs / Botol) dari jumlah_kemasan
    const rawKemasan = (data.jumlah_kemasan || '').trim();
    const numMatch = rawKemasan.match(/([0-9]+([.,][0-9]+)?)/);
    document.getElementById('edit-jumlah-angka').value = numMatch ? numMatch[1].replace(',', '.') : '';

    const satuanSelect = document.getElementById('edit-satuan-kemasan');
    if (satuanSelect) {
        if (rawKemasan.toLowerCase().includes('botol')) {
            satuanSelect.value = 'Botol';
        } else {
            satuanSelect.value = 'Pcs';
        }
    }

    const obatSelect = document.getElementById('edit-obat-id');
    if (obatSelect) {
        if (data.obat_id) {
            obatSelect.value = data.obat_id;
        } else {
            obatSelect.value = '';
            for (let i = 0; i < obatSelect.options.length; i++) {
                if (obatSelect.options[i].text.trim().toLowerCase() === (data.nama_obat || '').trim().toLowerCase()) {
                    obatSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    const modal = document.getElementById('modalEditObat');
    if (modal) modal.classList.add('show');
}

function openModalDeleteObat(id, nama, kemasan) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;
    document.getElementById('delete-kemasan-display').innerText = kemasan;

    const modal = document.getElementById('modalDeleteObat');
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

function filterObatTable() {
    const query = (document.getElementById('obat-search-input')?.value || '').toLowerCase();
    const rows  = document.querySelectorAll('.obat-row-item');

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
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
