<?php
require_once __DIR__ . '/../config/app.php';

// Proteksi sesi login
requireLogin();

require_once ROOT_PATH . '/config/koneksi.php';
require_once ROOT_PATH . '/helpers/db-error.php';

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/data.php';

if ($activeView === 'lahan') {
    $pageTitle           = "Pemetaan & Luas Lahan | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Pemetaan & Luas Lahan";
    $currentMenuSubtitle = "Data pemetaan luas lahan garapan anggota petani, komoditas yang ditanami, dan keterangan.";
} else {
    $pageTitle           = "Daftar Anggota Petani | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
    $currentMenuTitle    = "Daftar Anggota Petani";
    $currentMenuSubtitle = "Manajemen data anggota petani kelompok tani, alamat tempat tinggal, dan keterangan.";
}
$extraCss = ['css/data-page.css', 'anggota/style.css'];
$extraJs  = ['anggota/script.js'];

require_once ROOT_PATH . '/layouts/header.php';
require_once ROOT_PATH . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once ROOT_PATH . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">

            <?php require __DIR__ . '/views/tabs.php'; ?>

            <?php if ($activeView === 'lahan'): ?>
                <?php require __DIR__ . '/views/view-lahan.php'; ?>
            <?php else: ?>
                <?php require __DIR__ . '/views/view-anggota.php'; ?>
            <?php endif; ?>

        </div>
    </main>
</div>

<?php require __DIR__ . '/views/modals-anggota.php'; ?>

<?php require __DIR__ . '/views/modals-lahan.php'; ?>

<?php require __DIR__ . '/views/toast-config.php'; ?>

<?php require_once ROOT_PATH . '/layouts/footer.php'; ?>
