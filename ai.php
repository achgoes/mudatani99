<?php
$pageTitle = "Penyuluh AI Pintar | mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
$currentMenuTitle = "Penyuluh Pertanian AI Pintar";
$currentMenuSubtitle = "Asisten kecerdasan buatan berbasis Gemini untuk konsultasi agronomi, dosis pemupukan, dan analisis kelompok tani.";

require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once __DIR__ . '/layouts/topbar.php'; ?>

    <main class="app-content-area">
        <div class="menu-placeholder-card fade-in">
            <div class="placeholder-icon-box placeholder-icon-amber">
                <i class="fa-solid fa-robot"></i>
            </div>
            <h2 class="placeholder-title">Penyuluh AI Pintar (Gemini)</h2>
            <p class="placeholder-subtitle">
                Halaman konsultasi kecerdasan buatan bagi pengurus dan petani. Siap memberikan rekomendasi dosis pupuk presisi, solusi penanganan hama tanaman, serta evaluasi data kelompok tani.
            </p>

            <div class="placeholder-badges">
                <span class="placeholder-badge"><i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i> Gemini 3 Flash Model</span>
                <span class="placeholder-badge"><i class="fa-brands fa-google text-blue-600"></i> Google Search Grounding</span>
                <span class="placeholder-badge"><i class="fa-solid fa-calculator text-emerald-600"></i> Kalkulator Dosis Pupuk</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="btn-quick-action btn-quick-action-dark">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
