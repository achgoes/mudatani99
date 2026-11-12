<?php
require_once __DIR__ . '/../config/app.php';

// Proteksi sesi login
requireLogin();

require_once ROOT_PATH . '/config/koneksi.php';

// Helper, migrasi tabel database, fungsi kalkulasi & auto-sinkronisasi
require_once __DIR__ . '/helpers.php';

// Proses Aksi Form POST (Tambah/Edit/Hapus Obat, Penjualan, Hutang & Pembayaran)
require_once __DIR__ . '/actions.php';

// Ambil data untuk tampilan, dropdown & flash/toast message
require_once __DIR__ . '/data.php';

// Konfigurasi judul halaman
if ($activeView === 'riwayat_stok') {
    $pageTitle           = "Riwayat Stok Obat | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Riwayat Stok Obat";
    $currentMenuSubtitle = "Jejak seluruh pengelolaan stok obat: tambah baru, akumulasi, edit, hapus, serta perubahan dari transaksi.";
} elseif ($activeView === 'penjualan') {
    $pageTitle           = "Penjualan Obat | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Penjualan Obat";
    $currentMenuSubtitle = "Pencatatan transaksi penjualan langsung obat (tunai/transfer/QRIS) yang otomatis mengurangi Stok Obat.";
} elseif ($activeView === 'riwayat_penjualan') {
    $pageTitle           = "Riwayat Penjualan Obat | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Riwayat Penjualan Obat";
    $currentMenuSubtitle = "Jejak aktivitas penjualan obat: transaksi baru, pembaruan item, maupun pembatalan.";
} elseif ($activeView === 'hutang') {
    $pageTitle           = "Hutang Piutang Obat | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Hutang Piutang Obat";
    $currentMenuSubtitle = "Pencatatan hutang piutang obat anggota petani yang tersinkronisasi otomatis dengan Stok Obat.";
} elseif ($activeView === 'riwayat') {
    $pageTitle           = "Riwayat Hutang / Pelunasan Obat | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Riwayat Hutang / Pelunasan Obat";
    $currentMenuSubtitle = "Jejak setiap pembayaran hutang obat petani: tanggal, nominal, metode bayar, dan sisa hutang setelah bayar.";
} else {
    $pageTitle           = "Stok Obat | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Stok Obat";
    $currentMenuSubtitle = "Manajemen inventaris stok obat pertanian kelompok tani (kemasan Pcs / Botol) yang tersinkronisasi otomatis dengan Penjualan & Hutang Piutang.";
}
$extraCss = ['css/data-page.css', 'obat/style.css'];
$extraJs  = ['obat/script.js'];

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
