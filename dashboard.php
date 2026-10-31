<?php
$pageTitle = "Dashboard | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle = "Ringkasan Dashboard";
$currentMenuSubtitle = "Pantau perkembangan kelompok tani, inventaris pupuk, tanaman, dan arus kas.";

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<!-- Main Layout Container -->
<div class="app-main-layout">
    <?php require_once __DIR__ . '/layouts/topbar.php'; ?>

    <!-- Main Content Area (Clean Placeholder) -->
    <main class="app-content-area">
        <div class="menu-placeholder-card fade-in">
            <div class="placeholder-icon-box">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <h2 class="placeholder-title">Ringkasan Dashboard</h2>
            <p class="placeholder-subtitle">
                Area konten dashboard saat ini dikosongkan terlebih dahulu sesuai instruksi. Menu navigasi samping dengan dropdown submenu dan fungsi slider telah siap digunakan.
            </p>

            <div class="placeholder-badges">
                <span class="placeholder-badge">
                    <i class="fa-solid fa-sliders text-agro-600"></i> Slider Buka-Tutup Aktif
                </span>
                <span class="placeholder-badge">
                    <i class="fa-solid fa-chevron-down text-agro-600"></i> Dropdown Submenu Berfungsi
                </span>
                <span class="placeholder-badge">
                    <i class="fa-solid fa-file-code text-agro-600"></i> CSS & PHP Terpisah Rapi
                </span>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="anggota.php" class="btn-quick-action">
                    <i class="fa-solid fa-users"></i>
                    <span>Buka Menu Anggota</span>
                </a>
                <a href="pupuk.php" class="btn-quick-action btn-quick-action-dark">
                    <i class="fa-solid fa-flask-vial"></i>
                    <span>Buka Menu Pupuk</span>
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
