<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();

$pageTitle = "Penyuluh AI Pintar | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle = "Penyuluh Pertanian AI Pintar";
$currentMenuSubtitle = "Asisten kecerdasan buatan berbasis Gemini untuk konsultasi agronomi, dosis pemupukan, dan analisis kelompok tani.";

$placeholder = [
    'icon'      => 'fa-solid fa-robot',
    'iconClass' => 'placeholder-icon-amber',
    'title'     => 'Penyuluh AI Pintar (Gemini)',
    'text'      => 'Halaman konsultasi kecerdasan buatan bagi pengurus dan petani. Siap memberikan rekomendasi dosis pupuk presisi, solusi penanganan hama tanaman, serta evaluasi data kelompok tani.',
    'badges'    => [
        ['icon' => 'fa-solid fa-wand-magic-sparkles text-amber-500', 'label' => 'Gemini 3 Flash Model'],
        ['icon' => 'fa-brands fa-google text-blue-600', 'label' => 'Google Search Grounding'],
        ['icon' => 'fa-solid fa-calculator text-emerald-600', 'label' => 'Kalkulator Dosis Pupuk'],
    ],
    'actions'   => [
        ['href' => 'dashboard/', 'icon' => 'fa-solid fa-arrow-left', 'label' => 'Kembali ke Dashboard', 'dark' => true],
    ],
];

require_once ROOT_PATH . '/layouts/placeholder.php';
