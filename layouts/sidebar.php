<?php
// Definisi menu sidebar. Tambah menu/submenu baru cukup dengan menambah item di array ini.
// 'view' = nilai ?view= pada submenu (null = tampilan default modul).
$sidebarMenu = [
    ['title' => 'Menu Utama', 'items' => [
        ['type' => 'link', 'label' => 'Ringkasan Dashboard', 'icon' => 'fa-chart-pie', 'route' => 'dashboard'],
    ]],
    ['title' => 'Data Master', 'items' => [
        ['type' => 'group', 'label' => 'Master', 'icon' => 'fa-layer-group text-emerald-400', 'submenu' => [
            ['label' => 'User', 'route' => 'master/user', 'view' => null],
            ['label' => 'Obat, Pupuk, dan Tanaman', 'route' => 'master/obat-pupuk', 'view' => null],
        ]],
    ]],
    ['title' => 'Manajemen Poktan', 'items' => [
        ['type' => 'group', 'label' => 'Data Anggota', 'icon' => 'fa-users', 'submenu' => [
            ['label' => 'Daftar Anggota Petani', 'route' => 'anggota', 'view' => null],
            ['label' => 'Pemetaan & Luas Lahan', 'route' => 'anggota', 'view' => 'lahan'],
        ]],
        ['type' => 'group', 'label' => 'Inventaris Pupuk', 'icon' => 'fa-flask-vial', 'submenu' => [
            ['label' => 'Stok Pupuk', 'route' => 'pupuk', 'view' => null],
            ['label' => 'Riwayat Stok Pupuk', 'route' => 'pupuk', 'view' => 'riwayat_stok'],
            ['label' => 'Penjualan', 'route' => 'pupuk', 'view' => 'penjualan'],
            ['label' => 'Riwayat Penjualan', 'route' => 'pupuk', 'view' => 'riwayat_penjualan'],
            ['label' => 'Hutang Piutang', 'route' => 'pupuk', 'view' => 'hutang'],
            ['label' => 'Riwayat Hutang / Pelunasan', 'route' => 'pupuk', 'view' => 'riwayat'],
        ]],
        ['type' => 'group', 'label' => 'Inventaris Obat', 'icon' => 'fa-shield-virus', 'submenu' => [
            ['label' => 'Stok Obat', 'route' => 'obat', 'view' => null],
            ['label' => 'Riwayat Stok Obat', 'route' => 'obat', 'view' => 'riwayat_stok'],
            ['label' => 'Penjualan', 'route' => 'obat', 'view' => 'penjualan'],
            ['label' => 'Riwayat Penjualan', 'route' => 'obat', 'view' => 'riwayat_penjualan'],
            ['label' => 'Hutang Piutang', 'route' => 'obat', 'view' => 'hutang'],
            ['label' => 'Riwayat Hutang / Pelunasan', 'route' => 'obat', 'view' => 'riwayat'],
        ]],
        ['type' => 'group', 'label' => 'Inventaris Tanaman', 'icon' => 'fa-wheat-awn', 'submenu' => [
            ['label' => 'Pengelolaan Tanaman', 'route' => 'tanaman', 'view' => null],
        ]],
    ]],
];

$currentView = $_GET['view'] ?? '';

// Daftar view bernama per rute, dipakai agar submenu default tidak aktif bersamaan dengan submenu ber-view
$routeViews = [];
foreach ($sidebarMenu as $section) {
    foreach ($section['items'] as $item) {
        foreach ($item['submenu'] ?? [] as $sub) {
            if ($sub['view'] !== null) {
                $routeViews[$sub['route']][] = $sub['view'];
            }
        }
    }
}

$isSubActive = function (array $sub) use ($currentView, $routeViews): bool {
    if (APP_ROUTE !== $sub['route']) {
        return false;
    }
    if ($sub['view'] === null) {
        return !in_array($currentView, $routeViews[$sub['route']] ?? [], true);
    }
    return $currentView === $sub['view'];
};

$subUrl = fn(array $sub): string => appUrl($sub['route'] . '/' . ($sub['view'] !== null ? '?view=' . $sub['view'] : ''));
?>
<!-- Mobile Overlay Backdrop -->
<div id="sidebar-backdrop" aria-hidden="true"></div>

<!-- Main Sliding Sidebar -->
<aside id="main-sidebar" aria-label="Sidebar Menu">
    <!-- Brand Header -->
    <div class="sidebar-header">
        <a href="<?= appUrl('dashboard/') ?>" class="sidebar-brand">
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
        <?php foreach ($sidebarMenu as $section): ?>
            <div class="nav-section-title"><?= htmlspecialchars($section['title']) ?></div>

            <?php foreach ($section['items'] as $item): ?>
                <?php if ($item['type'] === 'link'): ?>
                    <a href="<?= appUrl($item['route'] . '/') ?>" class="nav-item-link <?= (APP_ROUTE === $item['route']) ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fa-solid <?= $item['icon'] ?>"></i></span>
                        <span class="nav-text"><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                <?php else: ?>
                    <?php
                    $groupActive = in_array(true, array_map($isSubActive, $item['submenu']), true)
                        || in_array(APP_ROUTE, array_column($item['submenu'], 'route'), true);
                    ?>
                    <div class="nav-dropdown-item <?= $groupActive ? 'open' : '' ?>">
                        <button type="button" class="nav-dropdown-toggle <?= $groupActive ? 'active' : '' ?>" aria-expanded="<?= $groupActive ? 'true' : 'false' ?>">
                            <div class="flex items-center">
                                <span class="nav-icon"><i class="fa-solid <?= $item['icon'] ?>"></i></span>
                                <span class="nav-text"><?= htmlspecialchars($item['label']) ?></span>
                            </div>
                            <i class="fa-solid fa-chevron-down nav-chevron"></i>
                        </button>
                        <div class="nav-submenu">
                            <?php foreach ($item['submenu'] as $sub): ?>
                                <a href="<?= $subUrl($sub) ?>" class="submenu-link <?= $isSubActive($sub) ? 'active' : '' ?>">
                                    <span class="submenu-bullet"></span>
                                    <span><?= htmlspecialchars($sub['label']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
</aside>
