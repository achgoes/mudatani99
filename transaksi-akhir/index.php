<?php
require_once __DIR__ . '/../config/app.php';

// Proteksi sesi login
requireLogin();

require_once ROOT_PATH . '/config/koneksi.php';
require_once ROOT_PATH . '/helpers/db-error.php';

// Helper & auto-migrasi tabel transaksi akhir
require_once __DIR__ . '/helpers.php';

// Proses aksi POST (Tambah, Edit & Hapus Transaksi Akhir)
require_once __DIR__ . '/actions.php';

// Ambil data untuk tampilan, dropdown & flash toast
require_once __DIR__ . '/data.php';

$pageTitle           = "Transaksi Akhir | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle    = "Transaksi Akhir";
$currentMenuSubtitle = "Rekapitulasi jumlah setoran petani, potongan rapaksi, dan perhitungan berat final.";

$extraCss = ['css/data-page.css', 'transaksi-akhir/style.css'];
$extraJs  = ['transaksi-akhir/script.js'];

require_once ROOT_PATH . '/layouts/header.php';
require_once ROOT_PATH . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once ROOT_PATH . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">
            <?php require __DIR__ . '/views/view-transaksi-akhir.php'; ?>
        </div>
    </main>
</div>

<?php
require __DIR__ . '/views/modals-transaksi-akhir.php';
require __DIR__ . '/views/toast-config.php';
?>

<?php require_once ROOT_PATH . '/layouts/footer.php'; ?>
