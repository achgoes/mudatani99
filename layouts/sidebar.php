<?php
// Active menu helper
$activePage = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Overlay Backdrop -->
<div id="sidebar-backdrop" aria-hidden="true"></div>

<!-- Main Sliding Sidebar -->
<aside id="main-sidebar" aria-label="Sidebar Menu">
    <!-- Brand Header -->
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="fa-solid fa-seedling"></i>
            </div>
            <div class="sidebar-brand-text">
                <h3>mudatani99</h3>
                <p>Poktan Harapan Makmur</p>
            </div>
        </a>
        <button type="button" class="btn-sidebar-close" id="btnSidebarClose" title="Tutup Menu" aria-label="Tutup Menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Navigation Menus with Dropdown Accordions -->
    <nav class="sidebar-nav-container">
        
        <!-- SECTION 1: UTAMA -->
        <div class="nav-section-title">Menu Utama</div>
        
        <!-- 1. Dashboard Link -->
        <a href="dashboard.php" class="nav-item-link <?= ($activePage == 'dashboard.php') ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
            <span class="nav-text">Ringkasan Dashboard</span>
        </a>

        <!-- SECTION: DATA MASTER -->
        <div class="nav-section-title">Data Master</div>

        <!-- Master Menu (Dropdown) -->
        <div class="nav-dropdown-item <?= in_array($activePage, ['user.php', 'obat-pupuk.php']) ? 'open' : '' ?>">
            <button type="button" class="nav-dropdown-toggle <?= in_array($activePage, ['user.php', 'obat-pupuk.php']) ? 'active' : '' ?>" aria-expanded="<?= in_array($activePage, ['user.php', 'obat-pupuk.php']) ? 'true' : 'false' ?>">
                <div class="flex items-center">
                    <span class="nav-icon"><i class="fa-solid fa-layer-group text-emerald-400"></i></span>
                    <span class="nav-text">Master</span>
                </div>
                <i class="fa-solid fa-chevron-down nav-chevron"></i>
            </button>
            <div class="nav-submenu">
                <a href="user.php" class="submenu-link <?= ($activePage == 'user.php') ? 'active' : '' ?>">
                    <span class="submenu-bullet"></span>
                    <span>User</span>
                </a>
                <a href="obat-pupuk.php" class="submenu-link <?= ($activePage == 'obat-pupuk.php') ? 'active' : '' ?>">
                    <span class="submenu-bullet"></span>
                    <span>Obat, Pupuk, dan Tanaman</span>
                </a>
            </div>
        </div>

        <!-- SECTION 2: DATA & MANAJEMEN -->
        <div class="nav-section-title">Manajemen Poktan</div>

        <!-- 2. Menu Anggota (Dropdown) -->
        <?php $currentView = $_GET['view'] ?? ''; ?>
        <div class="nav-dropdown-item <?= ($activePage == 'anggota.php') ? 'open' : '' ?>">
            <button type="button" class="nav-dropdown-toggle <?= ($activePage == 'anggota.php') ? 'active' : '' ?>" aria-expanded="<?= ($activePage == 'anggota.php') ? 'true' : 'false' ?>">
                <div class="flex items-center">
                    <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
                    <span class="nav-text">Data Anggota</span>
                </div>
                <i class="fa-solid fa-chevron-down nav-chevron"></i>
            </button>
            <div class="nav-submenu">
                <a href="anggota.php" class="submenu-link <?= ($activePage == 'anggota.php' && $currentView !== 'lahan') ? 'active' : '' ?>">
                    <span class="submenu-bullet"></span>
                    <span>Daftar Anggota Petani</span>
                </a>
                <a href="anggota.php?view=lahan" class="submenu-link <?= ($activePage == 'anggota.php' && $currentView === 'lahan') ? 'active' : '' ?>">
                    <span class="submenu-bullet"></span>
                    <span>Pemetaan & Luas Lahan</span>
                </a>
            </div>
        </div>

        <!-- 3. Menu Pupuk (Dropdown) -->
        <div class="nav-dropdown-item <?= ($activePage == 'pupuk.php') ? 'open' : '' ?>">
            <button type="button" class="nav-dropdown-toggle <?= ($activePage == 'pupuk.php') ? 'active' : '' ?>" aria-expanded="<?= ($activePage == 'pupuk.php') ? 'true' : 'false' ?>">
                <div class="flex items-center">
                    <span class="nav-icon"><i class="fa-solid fa-flask-vial"></i></span>
                    <span class="nav-text">Inventaris Pupuk</span>
                </div>
                <i class="fa-solid fa-chevron-down nav-chevron"></i>
            </button>
            <div class="nav-submenu">
                <a href="pupuk.php" class="submenu-link <?= ($activePage == 'pupuk.php' && !in_array($currentView, ['hutang', 'riwayat'], true)) ? 'active' : '' ?>">
                    <span class="submenu-bullet"></span>
                    <span>Stok Pupuk</span>
                </a>
                <a href="pupuk.php?view=hutang" class="submenu-link <?= ($activePage == 'pupuk.php' && $currentView === 'hutang') ? 'active' : '' ?>">
                    <span class="submenu-bullet"></span>
                    <span>Hutang Piutang</span>
                </a>
                <a href="pupuk.php?view=riwayat" class="submenu-link <?= ($activePage == 'pupuk.php' && $currentView === 'riwayat') ? 'active' : '' ?>">
                    <span class="submenu-bullet"></span>
                    <span>Riwayat Hutang / Pelunasan</span>
                </a>
            </div>
        </div>

        <!-- 4. Menu Obat (Dropdown) -->
        <div class="nav-dropdown-item <?= ($activePage == 'obat.php') ? 'open' : '' ?>">
            <button type="button" class="nav-dropdown-toggle <?= ($activePage == 'obat.php') ? 'active' : '' ?>" aria-expanded="<?= ($activePage == 'obat.php') ? 'true' : 'false' ?>">
                <div class="flex items-center">
                    <span class="nav-icon"><i class="fa-solid fa-shield-virus"></i></span>
                    <span class="nav-text">Inventaris Obat</span>
                </div>
                <i class="fa-solid fa-chevron-down nav-chevron"></i>
            </button>
            <div class="nav-submenu">
                <a href="obat.php" class="submenu-link <?= ($activePage == 'obat.php') ? 'active' : '' ?>">
                    <span class="submenu-bullet"></span>
                    <span>Pengelolaan Obat</span>
                </a>
            </div>
        </div>

        <!-- 5. Menu Tanaman (Dropdown) -->
        <div class="nav-dropdown-item <?= ($activePage == 'tanaman.php') ? 'open' : '' ?>">
            <button type="button" class="nav-dropdown-toggle <?= ($activePage == 'tanaman.php') ? 'active' : '' ?>" aria-expanded="<?= ($activePage == 'tanaman.php') ? 'true' : 'false' ?>">
                <div class="flex items-center">
                    <span class="nav-icon"><i class="fa-solid fa-wheat-awn"></i></span>
                    <span class="nav-text">Inventaris Tanaman</span>
                </div>
                <i class="fa-solid fa-chevron-down nav-chevron"></i>
            </button>
            <div class="nav-submenu">
                <a href="tanaman.php" class="submenu-link <?= ($activePage == 'tanaman.php') ? 'active' : '' ?>">
                    <span class="submenu-bullet"></span>
                    <span>Pengelolaan Tanaman</span>
                </a>
            </div>
        </div>

        <!-- 5. Menu Transaksi (Dropdown) -->
        <div class="nav-dropdown-item <?= ($activePage == 'transaksi.php') ? 'open' : '' ?>">
            <button type="button" class="nav-dropdown-toggle <?= ($activePage == 'transaksi.php') ? 'active' : '' ?>" aria-expanded="<?= ($activePage == 'transaksi.php') ? 'true' : 'false' ?>">
                <div class="flex items-center">
                    <span class="nav-icon"><i class="fa-solid fa-money-bill-transfer"></i></span>
                    <span class="nav-text">Kas & Transaksi</span>
                </div>
                <i class="fa-solid fa-chevron-down nav-chevron"></i>
            </button>
            <div class="nav-submenu">
                <a href="transaksi.php" class="submenu-link">
                    <span class="submenu-bullet"></span>
                    <span>Buku Kas Masuk</span>
                </a>
                <a href="transaksi.php?view=keluar" class="submenu-link">
                    <span class="submenu-bullet"></span>
                    <span>Buku Kas Keluar</span>
                </a>
                <a href="transaksi.php?view=laporan" class="submenu-link">
                    <span class="submenu-bullet"></span>
                    <span>Laporan Keuangan Kas</span>
                </a>
            </div>
        </div>

        <!-- SECTION 3: KECERDASAN BUATAN -->
        <div class="nav-section-title">Kecerdasan Buatan</div>

        <!-- 6. Menu AI (Dropdown) -->
        <div class="nav-dropdown-item <?= ($activePage == 'ai.php') ? 'open' : '' ?>">
            <button type="button" class="nav-dropdown-toggle <?= ($activePage == 'ai.php') ? 'active' : '' ?>" aria-expanded="<?= ($activePage == 'ai.php') ? 'true' : 'false' ?>">
                <div class="flex items-center">
                    <span class="nav-icon"><i class="fa-solid fa-robot text-amber-300"></i></span>
                    <span class="nav-text">Penyuluh AI Pintar</span>
                </div>
                <i class="fa-solid fa-chevron-down nav-chevron"></i>
            </button>
            <div class="nav-submenu">
                <a href="ai.php" class="submenu-link">
                    <span class="submenu-bullet"></span>
                    <span>Konsultasi Tanya Jawab AI</span>
                </a>
                <a href="ai.php?view=analisis" class="submenu-link">
                    <span class="submenu-bullet"></span>
                    <span>Rekomendasi Dosis Pupuk</span>
                </a>
            </div>
        </div>

        <!-- SECTION 4: SISTEM -->
        <div class="nav-section-title">Konfigurasi</div>

        <!-- 7. Menu Pengaturan (Dropdown) -->
        <div class="nav-dropdown-item <?= ($activePage == 'pengaturan.php') ? 'open' : '' ?>">
            <button type="button" class="nav-dropdown-toggle <?= ($activePage == 'pengaturan.php') ? 'active' : '' ?>" aria-expanded="<?= ($activePage == 'pengaturan.php') ? 'true' : 'false' ?>">
                <div class="flex items-center">
                    <span class="nav-icon"><i class="fa-solid fa-gear"></i></span>
                    <span class="nav-text">Pengaturan</span>
                </div>
                <i class="fa-solid fa-chevron-down nav-chevron"></i>
            </button>
            <div class="nav-submenu">
                <a href="pengaturan.php" class="submenu-link">
                    <span class="submenu-bullet"></span>
                    <span>Profil Kelompok Tani</span>
                </a>
                <a href="pengaturan.php?view=akun" class="submenu-link">
                    <span class="submenu-bullet"></span>
                    <span>Pengaturan Akun & Sandi</span>
                </a>
            </div>
        </div>

    </nav>
</aside>
