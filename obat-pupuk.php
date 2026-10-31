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

$feedbackMessage = "";
$feedbackType    = "";

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $pName = htmlspecialchars($_GET['name'] ?? 'Produk');
    if ($_GET['status'] === 'added') {
        $toastTitle   = "Berhasil Ditambahkan!";
        $toastMessage = "Data master <b>$pName</b> berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle   = "Berhasil Diperbarui!";
        $toastMessage = "Data master <b>$pName</b> berhasil diperbarui.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = "Data master produk berhasil dihapus.";
        $toastType    = "success";
    }
}

$allowedJenis = ['Obat', 'Pupuk', 'Tanaman'];

// Proses Aksi Form POST (Tambah, Edit, Hapus Master Obat, Pupuk, dan Tanaman)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TAMBAH PRODUK MASTER
    if ($action === 'add') {
        $nama_produk = trim($_POST['nama_produk'] ?? '');
        $jenis       = trim($_POST['jenis'] ?? '');
        $keterangan  = trim($_POST['keterangan'] ?? '');

        if (empty($nama_produk) || !in_array($jenis, $allowedJenis, true)) {
            $feedbackMessage = "Nama Produk dan Jenis (Obat / Pupuk / Tanaman) wajib diisi dengan benar.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $stmt = $conn->prepare("INSERT INTO master_produk (nama_produk, jenis, keterangan) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $nama_produk, $jenis, $keterangan);
                if ($stmt->execute()) {
                    header("Location: obat-pupuk.php?status=added&name=" . urlencode($nama_produk));
                    exit;
                } else {
                    $feedbackMessage = "Gagal menambahkan produk: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }

    // 2. EDIT PRODUK MASTER
    elseif ($action === 'edit') {
        $id          = (int)($_POST['id'] ?? 0);
        $nama_produk = trim($_POST['nama_produk'] ?? '');
        $jenis       = trim($_POST['jenis'] ?? '');
        $keterangan  = trim($_POST['keterangan'] ?? '');

        if ($id <= 0 || empty($nama_produk) || !in_array($jenis, $allowedJenis, true)) {
            $feedbackMessage = "ID, Nama Produk, dan Jenis (Obat / Pupuk / Tanaman) wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $stmt = $conn->prepare("UPDATE master_produk SET nama_produk = ?, jenis = ?, keterangan = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("sssi", $nama_produk, $jenis, $keterangan, $id);
                if ($stmt->execute()) {
                    header("Location: obat-pupuk.php?status=updated&name=" . urlencode($nama_produk));
                    exit;
                } else {
                    $feedbackMessage = "Gagal memperbarui produk: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }

    // 3. HAPUS PRODUK MASTER
    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM master_produk WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header("Location: obat-pupuk.php?status=deleted");
                    exit;
                } else {
                    $feedbackMessage = "Gagal menghapus produk: " . $conn->error;
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

// Ambil seluruh data master_produk
$produkList   = [];
$countObat    = 0;
$countPupuk   = 0;
$countTanaman = 0;

// Opsi pengurutan (Sort) hanya diperbolehkan pada nomor, nama produk, dan jenis
$allowedSortCols = [
    'nomor' => 'id',
    'id'    => 'id',
    'nama'  => 'nama_produk',
    'jenis' => 'jenis'
];
$sortColParam   = strtolower($_GET['sort'] ?? 'nomor');
$sortOrderParam = strtolower($_GET['order'] ?? 'asc');

if (!array_key_exists($sortColParam, $allowedSortCols)) {
    $sortColParam = 'nomor';
}
if (!in_array($sortOrderParam, ['asc', 'desc'], true)) {
    $sortOrderParam = 'asc';
}

$dbSortField = $allowedSortCols[$sortColParam];
$dbSortDir   = strtoupper($sortOrderParam);

$orderBySql = "$dbSortField $dbSortDir";
if ($dbSortField === 'jenis') {
    $orderBySql .= ", nama_produk ASC";
} elseif ($dbSortField === 'nama_produk') {
    $orderBySql .= ", id ASC";
}

$result = $conn->query("SELECT id, nama_produk, jenis, keterangan, created_at FROM master_produk ORDER BY $orderBySql");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $produkList[] = $row;
        if ($row['jenis'] === 'Obat') {
            $countObat++;
        } elseif ($row['jenis'] === 'Pupuk') {
            $countPupuk++;
        } elseif ($row['jenis'] === 'Tanaman') {
            $countTanaman++;
        }
    }
}

$pageTitle           = "Master Obat, Pupuk, dan Tanaman | mudatani99";
$currentMenuTitle    = "Master Obat, Pupuk, dan Tanaman";
$currentMenuSubtitle = "Katalog data master produk obat pertanian, pupuk, dan komoditas tanaman kelompok tani.";
$extraCss            = ['css/obat-pupuk.css'];

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once __DIR__ . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">

            <!-- Ringkasan Statistik Master Produk -->
            <div class="user-stats-grid">
                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Total Produk</h5>
                        <div class="stat-value"><?= count($produkList) ?></div>
                        <div class="stat-desc">Semua item terdaftar</div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-green">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>

                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Master Obat</h5>
                        <div class="stat-value"><?= $countObat ?></div>
                        <div class="stat-desc">Pestisida & Obat Hama</div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-amber">
                        <i class="fa-solid fa-shield-virus"></i>
                    </div>
                </div>

                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Master Pupuk</h5>
                        <div class="stat-value"><?= $countPupuk ?></div>
                        <div class="stat-desc">Pupuk Subsidi & Organik</div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-blue">
                        <i class="fa-solid fa-flask-vial"></i>
                    </div>
                </div>

                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Master Tanaman</h5>
                        <div class="stat-value"><?= $countTanaman ?></div>
                        <div class="stat-desc">Bibit & Komoditas Tanam</div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-green">
                        <i class="fa-solid fa-wheat-awn"></i>
                    </div>
                </div>
            </div>

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
                                                <span>ID Produk: #<?= (int)$row['id'] ?></span>
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

        </div>
    </main>
</div>

<!-- ========================================================
     MODAL 1: TAMBAH PRODUK (OBAT / PUPUK / TANAMAN)
======================================================== -->
<div id="modalTambahProduk" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-plus-circle text-agro-600"></i>
                Tambah Master Produk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahProduk')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="obat-pupuk.php" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-nama-produk">Nama Produk <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="add-nama-produk"
                        name="nama_produk"
                        required
                        class="modal-input"
                        placeholder="Contoh: Pupuk Urea Bersubsidi / Inpari 32 / Virtako"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-jenis">Jenis <span class="text-rose-500">*</span></label>
                    <select id="add-jenis" name="jenis" required class="modal-input">
                        <option value="">-- Pilih Jenis (Obat / Pupuk / Tanaman) --</option>
                        <option value="Obat">Obat</option>
                        <option value="Pupuk">Pupuk</option>
                        <option value="Tanaman">Tanaman</option>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-keterangan">Keterangan</label>
                    <input
                        type="text"
                        id="add-keterangan"
                        name="keterangan"
                        class="modal-input"
                        placeholder="Contoh: Pupuk Nitrogen 46% / Varietas unggul panen 115 HST"
                    >
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahProduk')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 2: EDIT PRODUK (OBAT / PUPUK / TANAMAN)
======================================================== -->
<div id="modalEditProduk" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Master Produk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditProduk')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="obat-pupuk.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">

            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-nama-produk">Nama Produk <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="edit-nama-produk"
                        name="nama_produk"
                        required
                        class="modal-input"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-jenis">Jenis <span class="text-rose-500">*</span></label>
                    <select id="edit-jenis" name="jenis" required class="modal-input">
                        <option value="Obat">Obat</option>
                        <option value="Pupuk">Pupuk</option>
                        <option value="Tanaman">Tanaman</option>
                    </select>
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
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditProduk')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 3: KONFIRMASI HAPUS PRODUK
======================================================== -->
<div id="modalDeleteProduk" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Master Produk
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteProduk')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="obat-pupuk.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-name-display">-</h5>
                <p class="text-xs text-slate-500 mb-2">Jenis: <span id="delete-jenis-display" class="font-bold text-slate-700">-</span></p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus produk ini dari daftar master?
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteProduk')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambahProduk() {
    const modal = document.getElementById('modalTambahProduk');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const inp = document.getElementById('add-nama-produk');
            if (inp) inp.focus();
        }, 150);
    }
}

function openModalEditProduk(data) {
    if (!data) return;
    document.getElementById('edit-id').value          = data.id;
    document.getElementById('edit-nama-produk').value = data.nama_produk;
    document.getElementById('edit-jenis').value       = data.jenis;
    document.getElementById('edit-keterangan').value  = data.keterangan;

    const modal = document.getElementById('modalEditProduk');
    if (modal) modal.classList.add('show');
}

function openModalDeleteProduk(id, nama, jenis) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;
    document.getElementById('delete-jenis-display').innerText = jenis;

    const modal = document.getElementById('modalDeleteProduk');
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

// ========================================================
// PENGURUTAN (SORT) TABEL MASTER PRODUK
// Hanya berlaku untuk Nomor, Nama Produk, dan Jenis
// ========================================================
let currentSortColumn = <?= json_encode($sortColParam === 'id' ? 'nomor' : $sortColParam) ?>;
let currentSortOrder  = <?= json_encode($sortOrderParam) ?>;

function sortTableBy(column) {
    if (!['nomor', 'nama', 'jenis'].includes(column)) return;

    if (currentSortColumn === column) {
        currentSortOrder = (currentSortOrder === 'asc') ? 'desc' : 'asc';
    } else {
        currentSortColumn = column;
        currentSortOrder  = 'asc';
    }

    applyTableSort();
}

function handleSortSelectChange(val) {
    const parts = val.split('-');
    if (parts.length === 2 && ['nomor', 'nama', 'jenis'].includes(parts[0]) && ['asc', 'desc'].includes(parts[1])) {
        currentSortColumn = parts[0];
        currentSortOrder  = parts[1];
        applyTableSort();
    }
}

function updateSortUI() {
    ['nomor', 'nama', 'jenis'].forEach(col => {
        const th   = document.getElementById(`th-sort-${col}`);
        const icon = document.getElementById(`sort-icon-${col}`);
        if (!th || !icon) return;

        if (col === currentSortColumn) {
            th.classList.add('active-sort');
            if (currentSortOrder === 'asc') {
                icon.className = 'fa-solid fa-sort-up sort-icon';
            } else {
                icon.className = 'fa-solid fa-sort-down sort-icon';
            }
        } else {
            th.classList.remove('active-sort');
            icon.className = 'fa-solid fa-sort sort-icon';
        }
    });

    const sortSelect = document.getElementById('produk-sort-select');
    if (sortSelect) {
        sortSelect.value = `${currentSortColumn}-${currentSortOrder}`;
    }
}

function applyTableSort() {
    const tbody = document.getElementById('table-produk-tbody');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('.produk-row-item'));
    if (rows.length === 0) return;

    rows.sort((a, b) => {
        if (currentSortColumn === 'nomor') {
            const valA = parseInt(a.getAttribute('data-nomor') || '0', 10);
            const valB = parseInt(b.getAttribute('data-nomor') || '0', 10);
            return (currentSortOrder === 'asc') ? (valA - valB) : (valB - valA);
        } else if (currentSortColumn === 'nama') {
            const valA = (a.getAttribute('data-nama') || '').trim();
            const valB = (b.getAttribute('data-nama') || '').trim();
            const cmp = valA.localeCompare(valB, 'id', { sensitivity: 'base' });
            return (currentSortOrder === 'asc') ? cmp : -cmp;
        } else if (currentSortColumn === 'jenis') {
            const valA = (a.getAttribute('data-jenis') || '').trim();
            const valB = (b.getAttribute('data-jenis') || '').trim();
            const cmp = valA.localeCompare(valB, 'id', { sensitivity: 'base' });
            if (cmp !== 0) {
                return (currentSortOrder === 'asc') ? cmp : -cmp;
            }
            // Pengurutan sekunder berdasarkan nama produk
            const subA = (a.getAttribute('data-nama') || '').trim();
            const subB = (b.getAttribute('data-nama') || '').trim();
            return subA.localeCompare(subB, 'id', { sensitivity: 'base' });
        }
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
    updateSortUI();
}

function filterProdukTable() {
    const query = (document.getElementById('produk-search-input')?.value || '').toLowerCase();
    const jenisFilter = document.getElementById('produk-jenis-filter')?.value || 'ALL';
    const rows = document.querySelectorAll('.produk-row-item');

    let visibleCount = 0;
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowJenis = row.getAttribute('data-jenis') || '';
        const matchQuery = text.includes(query);
        const matchJenis = (jenisFilter === 'ALL' || rowJenis === jenisFilter);
        const isShow = matchQuery && matchJenis;
        row.style.display = isShow ? '' : 'none';
        if (isShow) visibleCount++;
    });

    let emptyRow = document.getElementById('table-produk-empty-search');
    if (visibleCount === 0 && rows.length > 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'table-produk-empty-search';
            emptyRow.innerHTML = `
                <td colspan="5" class="table-empty-cell py-6 text-center text-slate-500">
                    <i class="fa-solid fa-magnifying-glass text-2xl mb-2 text-slate-400 block"></i>
                    Tidak ada produk yang sesuai dengan kriteria pencarian atau filter.
                </td>
            `;
            document.getElementById('table-produk-tbody')?.appendChild(emptyRow);
        }
        emptyRow.style.display = '';
    } else if (emptyRow) {
        emptyRow.style.display = 'none';
    }
}

// Inisialisasi status pengurutan saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    updateSortUI();
});

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
