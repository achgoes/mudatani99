<?php
require_once __DIR__ . '/../../config/app.php';

// Proteksi sesi login
requireLogin();

require_once ROOT_PATH . '/config/koneksi.php';
require_once ROOT_PATH . '/helpers/db-error.php';

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/data.php';

// Data untuk Header
$pageTitle           = "Master Data User | mudatani99";
$currentMenuTitle    = "Master Data User";
$currentMenuSubtitle = "Daftar pengguna terdaftar pada sistem mudatani99, kelola akun, NIK, dan hak akses.";
$extraCss            = ['css/data-page.css'];
$extraJs             = ['master/user/script.js'];

require_once ROOT_PATH . '/layouts/header.php';
require_once ROOT_PATH . '/layouts/sidebar.php';
?>

<!-- Main Layout Container -->
<div class="app-main-layout">
    <?php require_once ROOT_PATH . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="user-page-wrapper fade-in">
            
            <?php require __DIR__ . '/views/stats.php'; ?>

            <?php require __DIR__ . '/views/tabel.php'; ?>

        </div>
    </main>
</div>

<?php require __DIR__ . '/views/modal-tambah.php'; ?>

<?php require __DIR__ . '/views/modal-edit.php'; ?>

<?php require __DIR__ . '/views/modal-hapus.php'; ?>

<?php require __DIR__ . '/views/toast-config.php'; ?>

<?php require_once ROOT_PATH . '/layouts/footer.php'; ?>
