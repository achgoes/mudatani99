<?php
if (!isset($currentMenuTitle)) {
    $currentMenuTitle = "Ringkasan Dashboard";
}
if (!isset($currentMenuSubtitle)) {
    $currentMenuSubtitle = "Pantau perkembangan kelompok tani, pupuk, dan transaksi.";
}

// Data pengguna yang sedang login dari session
$loggedName = $_SESSION['nama'] ?? 'Budi Santoso';
$loggedNik  = $_SESSION['nik'] ?? '0001';
$loggedUser = $_SESSION['username'] ?? 'admin';
$nameParts  = preg_split('/\s+/', trim($loggedName));
$avatarInitials = '';
if (!empty($nameParts[0])) {
    if (count($nameParts) >= 2 && !empty($nameParts[1])) {
        $avatarInitials = strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[1], 0, 1));
    } else {
        $avatarInitials = strtoupper(mb_substr($nameParts[0], 0, min(2, mb_strlen($nameParts[0]))));
    }
} else {
    $avatarInitials = 'US';
}

// Format tanggal server dalam Bahasa Indonesia
date_default_timezone_set('Asia/Jakarta');
$hariIndo = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];
$bulanIndo = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

$hariIni       = $hariIndo[date('l')] ?? date('l');
$tglIni        = date('d');
$blnIni        = $bulanIndo[(int)date('m')] ?? date('F');
$thnIni        = date('Y');
$tanggalServer = "$hariIni, $tglIni $blnIni $thnIni";
?>
<!-- Topbar with Slider Trigger & Top-Right Profile Dropdown -->
<header class="app-topbar">
    <div class="topbar-left">
        <!-- Sidebar Slider Toggle Button -->
        <button type="button" class="btn-slider-trigger" title="Buka / Tutup Slider Menu Samping" aria-label="Slider Menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="topbar-breadcrumb">
            <h1 class="topbar-page-title"><?= htmlspecialchars($currentMenuTitle) ?></h1>
            <p class="topbar-subtext hidden sm:block"><?= htmlspecialchars($currentMenuSubtitle) ?></p>
        </div>
    </div>

    <div class="topbar-right">
        <!-- Tanggal Server Badge -->
        <div class="badge-season hidden lg:inline-flex" title="Tanggal Server">
            <i class="fa-regular fa-calendar-days text-agro-600"></i>
            <span><?= htmlspecialchars($tanggalServer) ?></span>
        </div>

        <!-- Profil Akun Dropdown (Pojok Kanan Atas) -->
        <div class="topbar-user-dropdown-wrapper" id="topbarUserDropdownWrapper">
            <button
                type="button"
                class="topbar-user-btn"
                id="topbarUserBtn"
                aria-expanded="false"
                aria-haspopup="true"
                aria-label="Menu Profil Akun: <?= htmlspecialchars($loggedName) ?>"
                title="Klik untuk membuka menu profil &amp; keluar"
            >
                <div class="topbar-user-avatar">
                    <?= htmlspecialchars($avatarInitials) ?>
                    <span class="avatar-online-dot" aria-hidden="true"></span>
                </div>
                <div class="topbar-user-meta hidden sm:flex">
                    <span class="topbar-user-name truncate"><?= htmlspecialchars($loggedName) ?></span>
                    <span class="topbar-user-role truncate">NIK: <?= htmlspecialchars($loggedNik) ?></span>
                </div>
                <i class="fa-solid fa-chevron-down topbar-user-chevron"></i>
            </button>

            <!-- Dropdown Menu Popover -->
            <div class="topbar-user-dropdown-menu" id="topbarUserMenu" role="menu" aria-label="Menu Profil">
                <div class="dropdown-user-header">
                    <div class="dropdown-user-avatar-lg">
                        <?= htmlspecialchars($avatarInitials) ?>
                        <span class="avatar-online-dot-lg" aria-hidden="true"></span>
                    </div>
                    <div class="dropdown-user-details truncate">
                        <h4 class="dropdown-user-fullname truncate"><?= htmlspecialchars($loggedName) ?></h4>
                        <div class="dropdown-user-badge-wrap">
                            <span class="dropdown-user-badge">
                                <i class="fa-solid fa-id-card text-agro-600"></i>
                                NIK: <?= htmlspecialchars($loggedNik) ?>
                            </span>
                        </div>
                        <span class="dropdown-user-username">@<?= htmlspecialchars($loggedUser) ?></span>
                    </div>
                </div>

                <div class="dropdown-menu-divider"></div>

                <div class="dropdown-menu-footer">
                    <a href="<?= appUrl('logout.php') ?>" class="dropdown-logout-btn" role="menuitem" title="Keluar dari akun">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Keluar Sesi</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
