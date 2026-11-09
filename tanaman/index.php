<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();

require_once ROOT_PATH . '/config/koneksi.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/data.php';

$pageTitle           = "Pengelolaan Tanaman | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle    = "Pengelolaan Tanaman";
$currentMenuSubtitle = "Manajemen inventaris tanaman dan bibit kelompok tani berdasarkan data Master Tanaman, jumlah kemasan (Pcs), berat, dan keterangan.";
$extraCss            = ['css/data-page.css', 'tanaman/style.css'];
$extraJs             = ['tanaman/script.js'];

require_once ROOT_PATH . '/layouts/header.php';
require_once ROOT_PATH . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once ROOT_PATH . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">
            <?php require __DIR__ . '/views/stats.php'; ?>
            <?php require __DIR__ . '/views/tabel.php'; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/views/modals.php'; ?>
<?php require __DIR__ . '/views/toast-config.php'; ?>
<?php require_once ROOT_PATH . '/layouts/footer.php'; ?>

