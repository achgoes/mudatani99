<?php
$pageTitle = "Kas & Transaksi | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle = "Kas & Transaksi";
$currentMenuSubtitle = "Pencatatan kas masuk dari tebus pupuk, penjualan hasil panen, dan pengeluaran operasional kelompok.";

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once __DIR__ . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="menu-placeholder-card fade-in">
            <div class="placeholder-icon-box">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <h2 class="placeholder-title">Kas & Transaksi Poktan</h2>
            <p class="placeholder-subtitle">
                Halaman menu pencatatan keuangan dan kas kelompok tani. Digunakan untuk merekapitulasi arus kas masuk, pengeluaran belanja persediaan pupuk/bibit, dan laporan saldo kas bersih kelompok.
            </p>

            <div class="placeholder-badges">
                <span class="placeholder-badge"><i class="fa-solid fa-arrow-trend-up text-emerald-600"></i> Kas Masuk (Penebusan/Iuran)</span>
                <span class="placeholder-badge"><i class="fa-solid fa-arrow-trend-down text-rose-600"></i> Kas Keluar (Belanja Gudang)</span>
                <span class="placeholder-badge"><i class="fa-solid fa-scale-balanced text-blue-600"></i> Laporan Laba Rugi Kas</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="btn-quick-action btn-quick-action-dark">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
