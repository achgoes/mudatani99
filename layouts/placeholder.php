<?php
/**
 * Kerangka halaman placeholder menu (header + sidebar + topbar + kartu info + footer).
 *
 * Variabel yang dibaca (di-set oleh index.php modul):
 *   $pageTitle, $currentMenuTitle, $currentMenuSubtitle  Judul halaman & topbar
 *   $placeholder = [
 *       'icon'      => 'fa-solid fa-chart-pie',            Kelas ikon Font Awesome lengkap
 *       'iconClass' => 'placeholder-icon-amber',           (opsional) class tambahan kotak ikon
 *       'title'     => '...', 'text' => '...',
 *       'badges'    => [['icon' => 'fa-solid fa-... text-...', 'label' => '...']],
 *       'actions'   => [['href' => 'anggota/', 'icon' => 'fa-solid fa-users', 'label' => '...', 'dark' => false]],
 *   ]
 */
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<div class="app-main-layout">
    <?php require_once __DIR__ . '/topbar.php'; ?>

    <main class="app-content-area">
        <div class="menu-placeholder-card fade-in">
            <div class="placeholder-icon-box <?= htmlspecialchars($placeholder['iconClass'] ?? '') ?>">
                <i class="<?= $placeholder['icon'] ?>"></i>
            </div>
            <h2 class="placeholder-title"><?= htmlspecialchars($placeholder['title']) ?></h2>
            <p class="placeholder-subtitle"><?= htmlspecialchars($placeholder['text']) ?></p>

            <div class="placeholder-badges">
                <?php foreach ($placeholder['badges'] as $badge): ?>
                    <span class="placeholder-badge"><i class="<?= $badge['icon'] ?>"></i> <?= htmlspecialchars($badge['label']) ?></span>
                <?php endforeach; ?>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3">
                <?php foreach ($placeholder['actions'] as $action): ?>
                    <a href="<?= appUrl($action['href']) ?>" class="btn-quick-action<?= !empty($action['dark']) ? ' btn-quick-action-dark' : '' ?>">
                        <i class="<?= $action['icon'] ?>"></i>
                        <span><?= htmlspecialchars($action['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>