<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();

$pageTitle = "Kas & Transaksi | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle = "Kas & Transaksi";
$currentMenuSubtitle = "Pencatatan kas masuk dari tebus pupuk, penjualan hasil panen, dan pengeluaran operasional kelompok.";

$placeholder = [
    'icon'    => 'fa-solid fa-money-bill-transfer',
    'title'   => 'Kas & Transaksi Poktan',
    'text'    => 'Halaman menu pencatatan keuangan dan kas kelompok tani. Digunakan untuk merekapitulasi arus kas masuk, pengeluaran belanja persediaan pupuk/bibit, dan laporan saldo kas bersih kelompok.',
    'badges'  => [
        ['icon' => 'fa-solid fa-arrow-trend-up text-emerald-600', 'label' => 'Kas Masuk (Penebusan/Iuran)'],
        ['icon' => 'fa-solid fa-arrow-trend-down text-rose-600', 'label' => 'Kas Keluar (Belanja Gudang)'],
        ['icon' => 'fa-solid fa-scale-balanced text-blue-600', 'label' => 'Laporan Laba Rugi Kas'],
    ],
    'actions' => [
        ['href' => 'dashboard/', 'icon' => 'fa-solid fa-arrow-left', 'label' => 'Kembali ke Dashboard', 'dark' => true],
    ],
];

require_once ROOT_PATH . '/layouts/placeholder.php';

