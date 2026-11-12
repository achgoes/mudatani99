<?php
require_once __DIR__ . '/../config/app.php';

// Proteksi sesi login
requireLogin();

require_once ROOT_PATH . '/config/koneksi.php';

// Helper, migrasi tabel database, fungsi kalkulasi & auto-sinkronisasi
require_once __DIR__ . '/helpers.php';

// Proses Aksi Form POST (Tambah/Edit/Hapus Pupuk & Hutang, Bayar, Status)
require_once __DIR__ . '/actions.php';

// Ambil data untuk tampilan, dropdown & flash/toast message
require_once __DIR__ . '/data.php';

// Konfigurasi judul halaman
if ($activeView === 'penjualan') {
    $pageTitle           = "Penjualan Pupuk | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Penjualan Pupuk";
    $currentMenuSubtitle = "Pencatatan transaksi penjualan langsung pupuk (tunai/transfer) yang otomatis mengurangi Stok Pupuk.";
} elseif ($activeView === 'riwayat_penjualan') {
    $pageTitle           = "Riwayat Penjualan Pupuk | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Riwayat Penjualan Pupuk";
    $currentMenuSubtitle = "Jejak aktivitas penjualan pupuk: transaksi baru, pembaruan item, maupun pembatalan.";
} elseif ($activeView === 'hutang') {
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
    $currentMenuSubtitle = "Manajemen inventaris stok pupuk kelompok tani yang tersinkronisasi otomatis dengan transaksi Penjualan & Hutang Piutang.";
}
$extraCss = ['css/data-page.css', 'pupuk/style.css'];
$extraJs  = ['pupuk/script.js'];

require_once ROOT_PATH . '/layouts/header.php';
require_once ROOT_PATH . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once ROOT_PATH . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">

            <?php require __DIR__ . '/views/tabs.php'; ?>

            <?php if ($activeView === 'kelola'): ?>
                <?php require __DIR__ . '/views/view-stok.php'; ?>
            <?php elseif ($activeView === 'penjualan'): ?>
                <?php require __DIR__ . '/views/view-penjualan.php'; ?>
            <?php elseif ($activeView === 'riwayat_penjualan'): ?>
                <?php require __DIR__ . '/views/view-riwayat-penjualan.php'; ?>
            <?php elseif ($activeView === 'riwayat'): ?>
                <?php require __DIR__ . '/views/view-riwayat.php'; ?>
            <?php else: ?>
                <?php require __DIR__ . '/views/view-hutang.php'; ?>
            <?php endif; ?>

        </div>
    </main>
</div>

<!-- ========================================================
     MODAL COMPONENTS (STOK, PENJUALAN, HUTANG, BAYAR & INFO)
======================================================== -->
<?php
require __DIR__ . '/views/modals-stok.php';
require __DIR__ . '/views/modals-penjualan.php';
require __DIR__ . '/views/modals-hutang.php';
require __DIR__ . '/views/modals-bayar.php';
require __DIR__ . '/views/modal-info.php';
require __DIR__ . '/views/toast-config.php';
?>

<?php require_once ROOT_PATH . '/layouts/footer.php'; ?>
