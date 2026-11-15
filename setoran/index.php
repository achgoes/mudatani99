<?php
require_once __DIR__ . '/../config/app.php';

// Proteksi sesi login
requireLogin();

require_once ROOT_PATH . '/config/koneksi.php';
require_once ROOT_PATH . '/helpers/db-error.php';

// Helper & auto-migrasi tabel setoran petani
require_once __DIR__ . '/helpers.php';

// Proses aksi POST (Tambah & Hapus Setoran Petani)
require_once __DIR__ . '/actions.php';

// Ambil data untuk tampilan, dropdown & flash toast
require_once __DIR__ . '/data.php';

$pageTitle           = "Manajemen Setoran Petani | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle    = "Manajemen Setoran Petani";
$currentMenuSubtitle = "Pencatatan setoran tanaman anggota petani per item beserta total berat, tanggal, dan bukti cetak.";

$extraCss = ['css/data-page.css', 'setoran/style.css'];
$extraJs  = ['setoran/script.js'];

require_once ROOT_PATH . '/layouts/header.php';
require_once ROOT_PATH . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once ROOT_PATH . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">
            <?php require __DIR__ . '/views/view-setoran.php'; ?>
        </div>
    </main>
</div>

<?php
require __DIR__ . '/views/modals-setoran.php';
require __DIR__ . '/views/toast-config.php';
?>

<?php require_once ROOT_PATH . '/layouts/footer.php'; ?>
