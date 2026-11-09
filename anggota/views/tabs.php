<!-- Navigasi Tab Cepat Antar Submenu Anggota -->
<div class="anggota-tab-nav">
    <a href="<?= appUrl('anggota/') ?>" class="anggota-tab-btn <?= ($activeView === 'daftar') ? 'active' : '' ?>">
        <i class="fa-solid fa-users"></i>
        <span>Daftar Anggota Petani</span>
    </a>
    <a href="<?= appUrl('anggota/?view=lahan') ?>" class="anggota-tab-btn <?= ($activeView === 'lahan') ? 'active' : '' ?>">
        <i class="fa-solid fa-map-location-dot"></i>
        <span>Pemetaan & Luas Lahan</span>
    </a>
</div>
