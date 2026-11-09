<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();

$pageTitle = "Dashboard | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle = "Ringkasan Dashboard";
$currentMenuSubtitle = "Pantau perkembangan kelompok tani, inventaris pupuk, tanaman, dan arus kas.";

$placeholder = [
    'icon'  => 'fa-solid fa-chart-pie',
    'title' => 'Ringkasan Dashboard',
    'text'  => 'Area konten dashboard saat ini dikosongkan terlebih dahulu sesuai instruksi. Menu navigasi samping dengan dropdown submenu dan fungsi slider telah siap digunakan.',
    'badges' => [
        ['icon' => 'fa-solid fa-sliders text-agro-600', 'label' => 'Slider Buka-Tutup Aktif'],
        ['icon' => 'fa-solid fa-chevron-down text-agro-600', 'label' => 'Dropdown Submenu Berfungsi'],
        ['icon' => 'fa-solid fa-file-code text-agro-600', 'label' => 'CSS & PHP Terpisah Rapi'],
    ],
    'actions' => [
        ['href' => 'anggota/', 'icon' => 'fa-solid fa-users', 'label' => 'Buka Menu Anggota'],
        ['href' => 'pupuk/', 'icon' => 'fa-solid fa-flask-vial', 'label' => 'Buka Menu Pupuk', 'dark' => true],
    ],
];

require_once ROOT_PATH . '/layouts/placeholder.php';
