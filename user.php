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

$feedbackMessage = "";
$feedbackMessage = "";
$feedbackType    = ""; // 'success' atau 'danger'

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'added') {
        $uName = htmlspecialchars($_GET['name'] ?? 'Pengguna');
        $uNik  = htmlspecialchars($_GET['nik'] ?? '');
        $toastTitle   = "Berhasil Ditambahkan!";
        $toastMessage = "Pengguna <b>$uName</b>" . ($uNik ? " (NIK: $uNik)" : "") . " berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $uName = htmlspecialchars($_GET['name'] ?? 'Pengguna');
        $uNik  = htmlspecialchars($_GET['nik'] ?? '');
        $toastTitle   = "Berhasil Diperbarui!";
        $toastMessage = "Data pengguna <b>$uName</b>" . ($uNik ? " (NIK: $uNik)" : "") . " berhasil diperbarui.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = "Data pengguna berhasil dihapus dari sistem.";
        $toastType    = "success";
    }
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

// Proses Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ==========================================
    // 1. AKSI TAMBAH USER
    // ==========================================
    if ($action === 'add') {
        $nik      = trim($_POST['nik'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $nama     = formatTitleCase($_POST['nama'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validasi input
        if (empty($nik) || empty($username) || empty($nama) || empty($password)) {
            $feedbackMessage = "Semua bidang (NIK, Username, Nama, dan Password) wajib diisi.";
            $feedbackType    = "danger";
        } elseif (!preg_match('/^[0-9]{4}$/', $nik)) {
            $feedbackMessage = "NIK harus berupa 4 digit angka (contoh: 0001, 0005).";
            $feedbackType    = "danger";
        } else {
            // Cek keunikan NIK atau Username
            $checkStmt = $conn->prepare("SELECT id, nik, username FROM users WHERE nik = ? OR username = ? LIMIT 1");
            $checkStmt->bind_param("ss", $nik, $username);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();

            if ($checkRes && $checkRes->num_rows > 0) {
                $existing = $checkRes->fetch_assoc();
                if ($existing['nik'] === $nik) {
                    $feedbackMessage = "NIK '$nik' sudah terdaftar pada pengguna lain.";
                } else {
                    $feedbackMessage = "Username '$username' sudah digunakan. Silakan gunakan username lain.";
                }
                $feedbackType = "danger";
            } else {
                // Simpan user baru (menggunakan password_hash untuk keamanan)
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $conn->prepare("INSERT INTO users (nik, username, nama, password) VALUES (?, ?, ?, ?)");
                $insertStmt->bind_param("ssss", $nik, $username, $nama, $hashedPassword);

                if ($insertStmt->execute()) {
                    header("Location: user.php?status=added&name=" . urlencode($nama) . "&nik=" . urlencode($nik));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('tambah user', $conn->error);
                    $feedbackType    = "danger";
                }
                $insertStmt->close();
            }
            $checkStmt->close();
        }
    }

    // ==========================================
    // 2. AKSI EDIT USER
    // ==========================================
    elseif ($action === 'edit') {
        $id       = (int)($_POST['id'] ?? 0);
        $nik      = trim($_POST['nik'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $nama     = formatTitleCase($_POST['nama'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($id <= 0 || empty($nik) || empty($username) || empty($nama)) {
            $feedbackMessage = "ID, NIK, Username, dan Nama lengkap wajib diisi.";
            $feedbackType    = "danger";
        } elseif (!preg_match('/^[0-9]{4}$/', $nik)) {
            $feedbackMessage = "NIK harus berupa 4 digit angka (contoh: 0001, 0005).";
            $feedbackType    = "danger";
        } else {
            // Cek duplikasi NIK atau Username pada user LAIN
            $checkStmt = $conn->prepare("SELECT id, nik, username FROM users WHERE (nik = ? OR username = ?) AND id != ? LIMIT 1");
            $checkStmt->bind_param("ssi", $nik, $username, $id);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();

            if ($checkRes && $checkRes->num_rows > 0) {
                $existing = $checkRes->fetch_assoc();
                if ($existing['nik'] === $nik) {
                    $feedbackMessage = "NIK '$nik' telah digunakan oleh pengguna lain.";
                } else {
                    $feedbackMessage = "Username '$username' telah digunakan oleh pengguna lain.";
                }
                $feedbackType = "danger";
            } else {
                // Update dengan atau tanpa ganti password
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE users SET nik = ?, username = ?, nama = ?, password = ? WHERE id = ?");
                    $updateStmt->bind_param("ssssi", $nik, $username, $nama, $hashedPassword, $id);
                } else {
                    $updateStmt = $conn->prepare("UPDATE users SET nik = ?, username = ?, nama = ? WHERE id = ?");
                    $updateStmt->bind_param("sssi", $nik, $username, $nama, $id);
                }

                if ($updateStmt->execute()) {
                    // Jika mengedit akun sendiri yang sedang login, update sesi
                    if ($id === (int)$_SESSION['user_id']) {
                        $_SESSION['nama']     = $nama;
                        $_SESSION['username'] = $username;
                        $_SESSION['nik']      = $nik;
                    }
                    header("Location: user.php?status=updated&name=" . urlencode($nama) . "&nik=" . urlencode($nik));
                    exit;
                } else {
                    $feedbackMessage = "Gagal memperbarui data user: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $updateStmt->close();
            }
            $checkStmt->close();
        }
    }

    // ==========================================
    // 3. AKSI HAPUS USER
    // ==========================================
    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id === (int)$_SESSION['user_id']) {
            $feedbackMessage = "Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.";
            $feedbackType    = "danger";
        } else {
            $delStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $delStmt->bind_param("i", $id);
            if ($delStmt->execute()) {
                header("Location: user.php?status=deleted");
                exit;
            } else {
                $feedbackMessage = "Gagal menghapus user: " . $conn->error;
                $feedbackType    = "danger";
            }
            $delStmt->close();
        }
    }
}

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// Ambil semua daftar pengguna dari database
$usersList = [];
$maxNikInt = 0;
$query = "SELECT id, nik, username, nama, created_at FROM users ORDER BY nik ASC, id ASC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['nama'] = formatTitleCase($row['nama']);
        $usersList[] = $row;
        $nikNum = (int)$row['nik'];
        if ($nikNum > $maxNikInt) {
            $maxNikInt = $nikNum;
        }
    }
}
$nextSuggestedNik = str_pad((string)($maxNikInt + 1), 4, '0', STR_PAD_LEFT);

// Data untuk Header
$pageTitle           = "Master Data User | mudatani99";
$currentMenuTitle    = "Master Data User";
$currentMenuSubtitle = "Daftar pengguna terdaftar pada sistem mudatani99, kelola akun, NIK, dan hak akses.";
$extraCss            = ['css/user.css'];

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<!-- Main Layout Container -->
<div class="app-main-layout">
    <?php require_once __DIR__ . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">
            
            <!-- Quick Stats Summary -->
            <div class="user-stats-grid">
                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Total Pengguna</h5>
                        <div class="stat-value"><?= count($usersList) ?></div>
                        <div class="stat-desc">Akun terdaftar aktif</div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-green">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>NIK Terakhir</h5>
                        <div class="stat-value"><?= htmlspecialchars(end($usersList)['nik'] ?? '0000') ?></div>
                        <div class="stat-desc">Saran NIK baru: <?= $nextSuggestedNik ?></div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-blue">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                </div>

                <div class="user-stat-card">
                    <div class="stat-info">
                        <h5>Akun Anda</h5>
                        <div class="stat-value text-base font-bold truncate max-w-[140px]"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                        <div class="stat-desc">NIK: <?= htmlspecialchars($_SESSION['nik'] ?? '-') ?></div>
                    </div>
                    <div class="stat-icon-wrap stat-icon-amber">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="user-table-card">
                <div class="table-card-header">
                    <div class="table-title-group">
                        <h3>Daftar Pengguna Terdaftar</h3>
                        <p>Total <?= count($usersList) ?> akun memiliki akses login dengan Username atau NIK.</p>
                    </div>

                    <div class="table-card-actions">
                        <!-- Search Box -->
                        <div class="search-input-wrap">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="user-search-input" placeholder="Cari NIK, Username, atau Nama..." onkeyup="filterUserTable()">
                        </div>

                        <!-- Tombol Tambah User -->
                        <button type="button" class="btn-add-user" onclick="openModalTambah()">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Tambah User</span>
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="table-responsive-custom">
                    <table class="user-table" id="table-user-data">
                        <thead>
                            <tr>
                                <th class="col-nomor">No</th>
                                <th>Pengguna / Nama</th>
                                <th>NIK (4 Digit)</th>
                                <th>Username</th>
                                <th>Terdaftar Sejak</th>
                                <th class="col-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usersList)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-400">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                                    Belum ada data user di dalam tabel users.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($usersList as $u): ?>
                                <?php 
                                    $isMe = ($u['id'] == $_SESSION['user_id']);
                                    // Inisial avatar
                                    $nParts = preg_split('/\s+/', trim($u['nama']));
                                    $initials = '';
                                    if (count($nParts) >= 2) {
                                        $initials = strtoupper(mb_substr($nParts[0], 0, 1) . mb_substr($nParts[1], 0, 1));
                                    } else {
                                        $initials = strtoupper(mb_substr($u['nama'], 0, 2));
                                    }
                                ?>
                                <tr class="user-row-item">
                                    <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                                    <td>
                                        <div class="user-profile-cell">
                                            <div class="user-avatar-badge"><?= htmlspecialchars($initials) ?></div>
                                            <div class="user-name-cell">
                                                <h4><?= htmlspecialchars($u['nama']) ?></h4>
                                                <?php if ($isMe): ?>
                                                    <span class="badge-current-user"><i class="fa-solid fa-circle-check"></i> Sesi Anda</span>
                                                <?php else: ?>
                                                    <span>User ID: #<?= $u['id'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="nik-badge">
                                            <i class="fa-regular fa-id-card text-agro-600"></i>
                                            <?= htmlspecialchars($u['nik']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="username-pill">@<?= htmlspecialchars($u['username']) ?></span>
                                    </td>
                                    <td class="text-slate-500 text-xs">
                                        <?= !empty($u['created_at']) ? date('d M Y, H:i', strtotime($u['created_at'])) : '-' ?>
                                    </td>
                                    <td>
                                        <div class="action-btn-group justify-end">
                                            <!-- Tombol Edit User -->
                                            <button 
                                                type="button" 
                                                class="btn-table-edit"
                                                onclick="openModalEdit(<?= htmlspecialchars(json_encode($u)) ?>)"
                                                title="Edit Data User"
                                            >
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                <span>Edit</span>
                                            </button>

                                            <!-- Tombol Hapus User (Hanya jika bukan user aktif) -->
                                            <?php if (!$isMe): ?>
                                            <button 
                                                type="button" 
                                                class="btn-table-delete"
                                                onclick="openModalDelete(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['nama'])) ?>', '<?= htmlspecialchars($u['nik']) ?>')"
                                                title="Hapus User"
                                            >
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                            <?php endif; ?>
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
     MODAL 1: TAMBAH USER BARU
======================================================== -->
<div id="modalTambahUser" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-user-plus text-agro-600"></i>
                Tambah Pengguna Baru
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahUser')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="user.php" method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-nik">NIK Pengguna (4 Digit Angka) <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="add-nik" 
                        name="nik" 
                        required 
                        maxlength="4" 
                        pattern="[0-9]{4}" 
                        inputmode="numeric" 
                        class="modal-input font-mono" 
                        placeholder="Contoh: <?= $nextSuggestedNik ?>"
                        value="<?= $nextSuggestedNik ?>"
                    >
                    <p class="modal-helper-text">Format 4 digit angka, misal: 0001, 0002, dst.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-username">Username <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="add-username" 
                        name="username" 
                        required 
                        class="modal-input" 
                        placeholder="Contoh: ahmad_makmur"
                        autocomplete="off"
                    >
                    <p class="modal-helper-text">Digunakan untuk login ke sistem, huruf kecil & tanpa spasi.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-nama">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="add-nama" 
                        name="nama" 
                        required 
                        class="modal-input" 
                        placeholder="Contoh: Ahmad Dahlan"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-password">Kata Sandi (Password) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="add-password" 
                            name="password" 
                            required 
                            class="modal-input pr-10" 
                            placeholder="Minimal 6 karakter"
                        >
                        <button type="button" onclick="togglePassField('add-password', 'add-eye')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="add-eye" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahUser')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 2: EDIT USER
======================================================== -->
<div id="modalEditUser" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Pengguna
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditUser')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="user.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">
            
            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-nik">NIK Pengguna (4 Digit) <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="edit-nik" 
                        name="nik" 
                        required 
                        maxlength="4" 
                        pattern="[0-9]{4}" 
                        inputmode="numeric" 
                        class="modal-input font-mono" 
                    >
                    <p class="modal-helper-text">Format 4 digit angka dimulai dari 0001.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-username">Username <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="edit-username" 
                        name="username" 
                        required 
                        class="modal-input" 
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-nama">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="edit-nama" 
                        name="nama" 
                        required 
                        class="modal-input" 
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-password">Kata Sandi Baru (Opsional)</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="edit-password" 
                            name="password" 
                            class="modal-input pr-10" 
                            placeholder="Kosongkan jika tidak ingin mengubah kata sandi"
                        >
                        <button type="button" onclick="togglePassField('edit-password', 'edit-eye')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="edit-eye" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <p class="modal-helper-text text-amber-600">
                        <i class="fa-solid fa-circle-info text-xs"></i> Hanya isi field ini jika ingin mengganti kata sandi.
                    </p>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditUser')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================
     MODAL 3: KONFIRMASI HAPUS USER
======================================================== -->
<div id="modalDeleteUser" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog max-w-sm">
        <div class="custom-modal-header bg-rose-50/50">
            <h4 class="text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                Hapus Pengguna
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalDeleteUser')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="user.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete-id" name="id" value="">

            <div class="custom-modal-body text-center py-5">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-regular fa-trash-can"></i>
                </div>
                <h5 class="font-bold text-slate-800 text-base mb-1" id="delete-name-display">-</h5>
                <p class="text-xs text-slate-500 mb-2">NIK: <span id="delete-nik-display" class="font-mono font-bold text-slate-700">-</span></p>
                <p class="text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
                    Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="custom-modal-footer justify-center">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteUser')">Batal</button>
                <button type="submit" class="btn-modal-save bg-rose-600 hover:bg-rose-700 shadow-rose-600/30">
                    <i class="fa-solid fa-trash-can mr-1"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Controller Functions
function openModalTambah() {
    const modal = document.getElementById('modalTambahUser');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const nikInput = document.getElementById('add-nik');
            if (nikInput) nikInput.focus();
        }, 150);
    }
}

function openModalEdit(userData) {
    if (!userData) return;
    document.getElementById('edit-id').value       = userData.id;
    document.getElementById('edit-nik').value      = userData.nik;
    document.getElementById('edit-username').value = userData.username;
    document.getElementById('edit-nama').value     = userData.nama;
    document.getElementById('edit-password').value = '';

    const modal = document.getElementById('modalEditUser');
    if (modal) modal.classList.add('show');
}

function openModalDelete(id, nama, nik) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;
    document.getElementById('delete-nik-display').innerText = nik;

    const modal = document.getElementById('modalDeleteUser');
    if (modal) modal.classList.add('show');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('show');
}

// Close modals when clicking backdrop
document.querySelectorAll('.custom-modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
            backdrop.classList.remove('show');
        }
    });
});

// ESC key closes any open modal
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.custom-modal-backdrop.show').forEach(m => m.classList.remove('show'));
    }
});

// Toggle Password Field Eye
function togglePassField(fieldId, eyeId) {
    const field = document.getElementById(fieldId);
    const eye   = document.getElementById(eyeId);
    if (!field || !eye) return;

    if (field.type === 'password') {
        field.type = 'text';
        eye.className = 'fa-regular fa-eye-slash';
    } else {
        field.type = 'password';
        eye.className = 'fa-regular fa-eye';
    }
}

// Live Search Filter for Users Table
function filterUserTable() {
    const query = document.getElementById('user-search-input').value.toLowerCase();
    const rows  = document.querySelectorAll('.user-row-item');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Otomatis ubah input Nama menjadi Huruf Depan Kapital dan sisanya kecil (Title Case)
function toTitleCaseLive(str) {
    return str.toLowerCase().replace(/(?:^|\s)\S/g, (match) => match.toUpperCase());
}

document.querySelectorAll('#add-nama, #edit-nama').forEach(inp => {
    inp.addEventListener('input', () => {
        const start = inp.selectionStart;
        const end   = inp.selectionEnd;
        inp.value   = toTitleCaseLive(inp.value);
        if (start !== null && end !== null) {
            inp.setSelectionRange(start, end);
        }
    });
});

/**
 * Global Top-Right Toast Notification Function
 * @param {string} title - Judul notifikasi
 * @param {string} message - Isi pesan notifikasi
 * @param {string} type - 'success' | 'danger'
 * @param {number} durationMs - Durasi tampil dalam ms (default: 1000ms / 1 detik)
 */
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

    // Hilang otomatis setelah durasi 1 detik (1000ms)
    const autoTimer = setTimeout(dismiss, durationMs);

    // Notifikasi jika ditekan / diklik maka langsung hilang seketika
    toast.addEventListener('click', () => {
        clearTimeout(autoTimer);
        dismiss();
    });
}

<?php if (!empty($toastTitle)): ?>
// Panggil toast notifikasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    showToast(
        <?= json_encode($toastTitle) ?>,
        <?= json_encode($toastMessage) ?>,
        <?= json_encode($toastType) ?>,
        1000
    );

    // Bersihkan parameter query status dari URL browser tanpa reload
    if (window.history.replaceState) {
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
