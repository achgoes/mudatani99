<?php
require_once __DIR__ . '/../config/app.php';

// Proteksi sesi login
requireLogin();

require_once ROOT_PATH . '/config/koneksi.php';

// Helper, migrasi tabel database, fungsi kalkulasi & auto-sinkronisasi
require_once __DIR__ . '/helpers.php';

// Proses Aksi Form POST (Tambah/Edit/Hapus Tanaman, Penjualan, Hutang & Pembayaran)
require_once __DIR__ . '/actions.php';

// Ambil data untuk tampilan, dropdown & flash/toast message
require_once __DIR__ . '/data.php';

// Konfigurasi judul halaman
if ($activeView === 'riwayat_stok') {
    $pageTitle           = "Riwayat Stok Tanaman | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Riwayat Stok Tanaman";
    $currentMenuSubtitle = "Jejak seluruh pengelolaan stok tanaman: tambah baru, akumulasi, edit, hapus, serta perubahan dari transaksi.";
} elseif ($activeView === 'penjualan') {
    $pageTitle           = "Penjualan Tanaman | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Penjualan Tanaman";
    $currentMenuSubtitle = "Pencatatan transaksi penjualan langsung tanaman dan bibit (tunai/transfer/QRIS) yang otomatis mengurangi Stok Tanaman.";
} elseif ($activeView === 'riwayat_penjualan') {
    $pageTitle           = "Riwayat Penjualan Tanaman | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Riwayat Penjualan Tanaman";
    $currentMenuSubtitle = "Jejak aktivitas penjualan tanaman: transaksi baru, pembaruan item, maupun pembatalan.";
} elseif ($activeView === 'hutang') {
    $pageTitle           = "Hutang Piutang Tanaman | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Hutang Piutang Tanaman";
    $currentMenuSubtitle = "Pencatatan hutang piutang tanaman anggota petani yang tersinkronisasi otomatis dengan Stok Tanaman.";
} elseif ($activeView === 'riwayat') {
    $pageTitle           = "Riwayat Hutang / Pelunasan Tanaman | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Riwayat Hutang / Pelunasan Tanaman";
    $currentMenuSubtitle = "Jejak setiap pembayaran hutang tanaman petani: tanggal, nominal, metode bayar, dan sisa hutang setelah bayar.";
} else {
    $pageTitle           = "Stok Tanaman | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Stok Tanaman";
    $currentMenuSubtitle = "Manajemen inventaris stok tanaman dan bibit kelompok tani (kemasan Pcs) yang tersinkronisasi otomatis dengan Penjualan & Hutang Piutang.";
}
$extraCss = ['css/data-page.css', 'tanaman/style.css'];
$extraJs  = ['tanaman/script.js'];

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
            <?php elseif ($activeView === 'riwayat_stok'): ?>
                <?php require __DIR__ . '/views/view-riwayat-stok.php'; ?>
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
