<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();

$pageTitle = "Pengaturan Sistem | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle = "Pengaturan Sistem";
$currentMenuSubtitle = "Konfigurasi profil Kelompok Tani Harapan Makmur, hak akses pengguna, dan keamanan akun.";

$placeholder = [
    'icon'    => 'fa-solid fa-gear',
    'title'   => 'Pengaturan & Konfigurasi',
    'text'    => 'Halaman konfigurasi kelompok tani. Digunakan untuk memperbarui data legalitas Poktan, nomor register penyuluh, integrasi API e-RDKK, serta manajemen kata sandi pengurus.',
    'badges'  => [
        ['icon' => 'fa-solid fa-building-wheat text-agro-600', 'label' => 'Profil & SK Poktan'],
        ['icon' => 'fa-solid fa-key text-amber-600', 'label' => 'Kata Sandi & Keamanan'],
        ['icon' => 'fa-solid fa-database text-blue-600', 'label' => 'Backup & Database'],
    ],
    'actions' => [
        ['href' => 'dashboard/', 'icon' => 'fa-solid fa-arrow-left', 'label' => 'Kembali ke Dashboard', 'dark' => true],
    ],
];

require_once ROOT_PATH . '/layouts/placeholder.php';

