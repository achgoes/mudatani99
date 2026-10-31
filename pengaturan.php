<?php
$pageTitle = "Pengaturan Sistem | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle = "Pengaturan Sistem";
$currentMenuSubtitle = "Konfigurasi profil Kelompok Tani Harapan Makmur, hak akses pengguna, dan keamanan akun.";

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once __DIR__ . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="menu-placeholder-card fade-in">
            <div class="placeholder-icon-box">
                <i class="fa-solid fa-gear"></i>
            </div>
            <h2 class="placeholder-title">Pengaturan & Konfigurasi</h2>
            <p class="placeholder-subtitle">
                Halaman konfigurasi kelompok tani. Digunakan untuk memperbarui data legalitas Poktan, nomor register penyuluh, integrasi API e-RDKK, serta manajemen kata sandi pengurus.
            </p>

            <div class="placeholder-badges">
                <span class="placeholder-badge"><i class="fa-solid fa-building-wheat text-agro-600"></i> Profil & SK Poktan</span>
                <span class="placeholder-badge"><i class="fa-solid fa-key text-amber-600"></i> Kata Sandi & Keamanan</span>
                <span class="placeholder-badge"><i class="fa-solid fa-database text-blue-600"></i> Backup & Database</span>
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
