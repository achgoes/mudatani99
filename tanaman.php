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

// Pastikan tabel pengelolaan_tanaman tersedia di database dengan kolom jumlah_kemasan
$conn->query("CREATE TABLE IF NOT EXISTS `pengelolaan_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tanaman_inv_id` (`tanaman_id`),
    CONSTRAINT `fk_pengelolaan_tanaman_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Migrasi otomatis jika tabel lama masih menggunakan kolom jumlah_karung
$colOld = $conn->query("SHOW COLUMNS FROM `pengelolaan_tanaman` LIKE 'jumlah_karung'");
if ($colOld && $colOld->num_rows > 0) {
    $conn->query("ALTER TABLE `pengelolaan_tanaman` CHANGE COLUMN `jumlah_karung` `jumlah_kemasan` VARCHAR(50) NOT NULL");
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
    $pName = htmlspecialchars($_GET['name'] ?? 'Tanaman');
    if ($_GET['status'] === 'added') {
        $toastTitle   = "Berhasil Ditambahkan!";
        $toastMessage = "Data pengelolaan tanaman <b>$pName</b> berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle   = "Berhasil Diperbarui!";
        $toastMessage = "Data pengelolaan tanaman <b>$pName</b> berhasil diperbarui.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = "Data pengelolaan tanaman berhasil dihapus.";
        $toastType    = "success";
    }
}

// Proses Aksi Form POST (Tambah, Edit, Hapus Pengelolaan Tanaman)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TAMBAH PENGELOLAAN TANAMAN
    if ($action === 'add') {
        $tanaman_id   = (int)($_POST['tanaman_id'] ?? 0);
        $jumlah_angka = trim($_POST['jumlah_angka'] ?? '');
        $jumlah_berat = formatTitleCase($_POST['jumlah_berat'] ?? '');
        $keterangan   = formatTitleCase($_POST['keterangan'] ?? '');

        if ($tanaman_id <= 0 || $jumlah_angka === '' || empty($jumlah_berat)) {
            $feedbackMessage = "Nama Tanaman, Jumlah Kemasan (Pcs), dan Jumlah Berat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            // Satuan kemasan untuk inventaris tanaman adalah Pcs saja
            $angkaBersih    = preg_replace('/[^0-9.,]/', '', $jumlah_angka);
            $jumlah_kemasan = ($angkaBersih !== '' ? $angkaBersih : $jumlah_angka) . ' Pcs';

            if (preg_match('/^[0-9]+([.,][0-9]+)?$/', $jumlah_berat)) {
                $jumlah_berat .= ' Kg';
            }

            // Pastikan tanaman_id valid di master_produk dengan jenis = 'Tanaman'
            $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
            $cekStmt->bind_param("i", $tanaman_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $tanamanRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            if (!$tanamanRow) {
                $feedbackMessage = "Nama tanaman yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $nama_tanaman = formatTitleCase($tanamanRow['nama_produk']);
                $stmt = $conn->prepare("INSERT INTO pengelolaan_tanaman (tanaman_id, nama_tanaman, jumlah_kemasan, jumlah_berat, keterangan) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("issss", $tanaman_id, $nama_tanaman, $jumlah_kemasan, $jumlah_berat, $keterangan);
                    if ($stmt->execute()) {
                        header("Location: tanaman.php?status=added&name=" . urlencode($nama_tanaman));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menambahkan data tanaman: " . $conn->error;
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    }

    // 2. EDIT PENGELOLAAN TANAMAN
    elseif ($action === 'edit') {
        $id           = (int)($_POST['id'] ?? 0);
        $tanaman_id   = (int)($_POST['tanaman_id'] ?? 0);
        $jumlah_angka = trim($_POST['jumlah_angka'] ?? '');
        $jumlah_berat = formatTitleCase($_POST['jumlah_berat'] ?? '');
        $keterangan   = formatTitleCase($_POST['keterangan'] ?? '');

        if ($id <= 0 || $tanaman_id <= 0 || $jumlah_angka === '' || empty($jumlah_berat)) {
            $feedbackMessage = "ID, Nama Tanaman, Jumlah Kemasan (Pcs), dan Jumlah Berat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $angkaBersih    = preg_replace('/[^0-9.,]/', '', $jumlah_angka);
            $jumlah_kemasan = ($angkaBersih !== '' ? $angkaBersih : $jumlah_angka) . ' Pcs';

            if (preg_match('/^[0-9]+([.,][0-9]+)?$/', $jumlah_berat)) {
                $jumlah_berat .= ' Kg';
            }

            // Pastikan tanaman_id valid di master_produk dengan jenis = 'Tanaman'
            $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
            $cekStmt->bind_param("i", $tanaman_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $tanamanRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            if (!$tanamanRow) {
                $feedbackMessage = "Nama tanaman yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $nama_tanaman = formatTitleCase($tanamanRow['nama_produk']);
                $stmt = $conn->prepare("UPDATE pengelolaan_tanaman SET tanaman_id = ?, nama_tanaman = ?, jumlah_kemasan = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("issssi", $tanaman_id, $nama_tanaman, $jumlah_kemasan, $jumlah_berat, $keterangan, $id);
                    if ($stmt->execute()) {
                        header("Location: tanaman.php?status=updated&name=" . urlencode($nama_tanaman));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal memperbarui data tanaman: " . $conn->error;
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    }

    // 3. HAPUS PENGELOLAAN TANAMAN
    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM pengelolaan_tanaman WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header("Location: tanaman.php?status=deleted");
                    exit;
                } else {
                    $feedbackMessage = "Gagal menghapus data tanaman: " . $conn->error;
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

// Ambil daftar master tanaman (khusus jenis = 'Tanaman' dari tabel master_produk)
$masterTanamanList = [];
$resMaster = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Tanaman' ORDER BY id ASC");
if ($resMaster) {
    while ($row = $resMaster->fetch_assoc()) {
        $row['nama_produk'] = formatTitleCase($row['nama_produk']);
        $row['keterangan']  = formatTitleCase($row['keterangan']);
        $masterTanamanList[] = $row;
    }
}

// Ambil daftar pengelolaan_tanaman (LEFT JOIN dengan master_produk jenis = 'Tanaman')
$tanamanList  = [];
$totalKemasan = 0;
$totalBerat   = 0.0;

$resTanaman = $conn->query("
    SELECT t.id, t.tanaman_id,
           COALESCE(m.nama_produk, t.nama_tanaman) AS nama_tanaman,
           t.jumlah_kemasan, t.jumlah_berat, t.keterangan, t.created_at
    FROM pengelolaan_tanaman t
    LEFT JOIN master_produk m ON t.tanaman_id = m.id AND m.jenis = 'Tanaman'
    ORDER BY t.id ASC
");
if ($resTanaman) {
    while ($row = $resTanaman->fetch_assoc()) {
        $row['nama_tanaman']   = formatTitleCase($row['nama_tanaman']);
        $row['jumlah_kemasan'] = formatTitleCase($row['jumlah_kemasan']);
        $row['jumlah_berat']   = formatTitleCase($row['jumlah_berat']);
        $row['keterangan']     = formatTitleCase($row['keterangan']);
        $tanamanList[] = $row;

        if (preg_match('/([0-9]+([.,][0-9]+)?)/', $row['jumlah_kemasan'], $mKemasan)) {
            $totalKemasan += (int)str_replace(',', '.', $mKemasan[1]);
        }
        if (preg_match('/([0-9]+([.,][0-9]+)?)/', $row['jumlah_berat'], $mBerat)) {
            $totalBerat += (float)str_replace(',', '.', $mBerat[1]);
        }
    }
}

$pageTitle           = "Pengelolaan Tanaman | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle    = "Pengelolaan Tanaman";
$currentMenuSubtitle = "Manajemen inventaris tanaman dan bibit kelompok tani berdasarkan data Master Tanaman, jumlah kemasan (Pcs), berat, dan keterangan.";
$extraCss            = ['css/tanaman.css'];

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once __DIR__ . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">

            <!-- Ringkasan Statistik Pengelolaan Tanaman -->
            <div class="user-stats-grid">
                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Total Data Tanaman</h5>
                        <div class="stat-value"><?= count($tanamanList) ?></div>
                        <div class="stat-desc">Data inventaris tercatat</div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-green">
                        <i class="fa-solid fa-wheat-awn"></i>
                    </div>
                </div>

                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Total Jumlah Kemasan</h5>
                        <div class="stat-value"><?= number_format($totalKemasan, 0, ',', '.') ?> <span class="text-sm font-semibold">Pcs</span></div>
                        <div class="stat-desc">Akumulasi kemasan tanaman/benih</div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-amber">
                        <i class="fa-solid fa-boxes-stacked"></i>
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
                                                <span>ID Master: #<?= (int)($row['tanaman_id'] ?? $row['id']) ?></span>
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

        </div>
    </main>
</div>

<!-- ========================================================
     MODAL 1: TAMBAH PENGELOLAAN TANAMAN
======================================================== -->
<div id="modalTambahTanaman" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-wheat-awn text-agro-600"></i>
                Tambah Data Pengelolaan Tanaman
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahTanaman')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="tanaman.php" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-tanaman-id">Nama Tanaman (Dari Master - Tanaman) <span class="text-rose-500">*</span></label>
                    <select id="add-tanaman-id" name="tanaman_id" required class="modal-input">
                        <option value="">-- Pilih Nama Tanaman --</option>
                        <?php foreach ($masterTanamanList as $mt): ?>
                            <option value="<?= (int)$mt['id'] ?>"><?= htmlspecialchars($mt['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="modal-helper-text">Diambil dari menu Master Obat, Pupuk, dan Tanaman (khusus jenis Tanaman).</p>
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
                            placeholder="Contoh: 20"
                        >
                        <select name="satuan_kemasan" class="modal-input select-satuan-tanaman">
                            <option value="Pcs">Pcs</option>
                        </select>
                    </div>
                    <p class="modal-helper-text">Satuan kemasan tanaman menggunakan <b>Pcs</b>.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jumlah-berat">Jumlah Berat <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-jumlah-berat"
                        name="jumlah_berat"
                        required
                        class="modal-input"
                        placeholder="Contoh: 500 Kg"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Stok Benih Unggul Siap Tanam"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahTanaman')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Data Tanaman
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 2: EDIT PENGELOLAAN TANAMAN
======================================================== -->
<div id="modalEditTanaman" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Pengelolaan Tanaman
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditTanaman')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="tanaman.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-tanaman-id">Nama Tanaman (Dari Master - Tanaman) <span class="text-rose-500">*</span></label>
                    <select id="edit-tanaman-id" name="tanaman_id" required class="modal-input">
                        <option value="">-- Pilih Nama Tanaman --</option>
                        <?php foreach ($masterTanamanList as $mt): ?>
                            <option value="<?= (int)$mt['id'] ?>"><?= htmlspecialchars($mt['nama_produk']) ?></option>
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
                        <select name="satuan_kemasan" class="modal-input select-satuan-tanaman">
                            <option value="Pcs">Pcs</option>
                        </select>
                    </div>
                    <p class="modal-helper-text">Satuan kemasan tanaman menggunakan <b>Pcs</b>.</p>
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
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditTanaman')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 3: KONFIRMASI HAPUS PENGELOLAAN TANAMAN
======================================================== -->
<div id="modalDeleteTanaman" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Data Tanaman
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteTanaman')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="tanaman.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-name-display">-</h5>
                <p class="text-xs text-slate-500 mb-2">Jumlah Kemasan: <span id="delete-kemasan-display" class="font-bold text-slate-700">-</span></p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus data pengelolaan tanaman ini?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteTanaman')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambahTanaman() {
    const modal = document.getElementById('modalTambahTanaman');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const sel = document.getElementById('add-tanaman-id');
            if (sel) sel.focus();
        }, 150);
    }
}

function openModalEditTanaman(data) {
    if (!data) return;
    document.getElementById('edit-id').value           = data.id;
    document.getElementById('edit-jumlah-berat').value = data.jumlah_berat;
    document.getElementById('edit-keterangan').value   = data.keterangan;

    // Ambil angka dari jumlah_kemasan (satuan otomatis Pcs)
    const rawKemasan = (data.jumlah_kemasan || '').trim();
    const numMatch = rawKemasan.match(/([0-9]+([.,][0-9]+)?)/);
    document.getElementById('edit-jumlah-angka').value = numMatch ? numMatch[1].replace(',', '.') : '';

    const tanamanSelect = document.getElementById('edit-tanaman-id');
    if (tanamanSelect) {
        if (data.tanaman_id) {
            tanamanSelect.value = data.tanaman_id;
        } else {
            tanamanSelect.value = '';
            for (let i = 0; i < tanamanSelect.options.length; i++) {
                if (tanamanSelect.options[i].text.trim().toLowerCase() === (data.nama_tanaman || '').trim().toLowerCase()) {
                    tanamanSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    const modal = document.getElementById('modalEditTanaman');
    if (modal) modal.classList.add('show');
}

function openModalDeleteTanaman(id, nama, kemasan) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;
    document.getElementById('delete-kemasan-display').innerText = kemasan;

    const modal = document.getElementById('modalDeleteTanaman');
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

function filterTanamanTable() {
    const query = (document.getElementById('tanaman-search-input')?.value || '').toLowerCase();
    const rows  = document.querySelectorAll('.tanaman-row-item');

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
