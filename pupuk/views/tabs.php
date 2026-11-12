<!-- Navigasi Tab Cepat Antar Submenu Pupuk -->
<div class="pupuk-tab-nav">
    <a href="<?= appUrl('pupuk/') ?>" class="pupuk-tab-btn <?= ($activeView === 'kelola') ? 'active' : '' ?>">
        <i class="fa-solid fa-flask-vial"></i>
        <span>Stok Pupuk</span>
        <span class="pupuk-tab-badge"><?= count($pupukList) ?></span>
    </a>
    <a href="<?= appUrl('pupuk/?view=penjualan') ?>" class="pupuk-tab-btn <?= ($activeView === 'penjualan') ? 'active' : '' ?>">
        <i class="fa-solid fa-cart-shopping"></i>
        <span>Penjualan</span>
        <span class="pupuk-tab-badge"><?= count($penjualanList) ?></span>
    </a>
    <a href="<?= appUrl('pupuk/?view=riwayat_penjualan') ?>" class="pupuk-tab-btn <?= ($activeView === 'riwayat_penjualan') ? 'active' : '' ?>">
        <i class="fa-solid fa-receipt"></i>
        <span>Riwayat Penjualan</span>
        <span class="pupuk-tab-badge"><?= count($riwayatPenjualanList) ?></span>
    </a>
    <a href="<?= appUrl('pupuk/?view=hutang') ?>" class="pupuk-tab-btn <?= ($activeView === 'hutang') ? 'active' : '' ?>">
        <i class="fa-solid fa-hand-holding-dollar"></i>
        <span>Hutang Piutang</span>
        <span class="pupuk-tab-badge"><?= $countBelumLunas ?> Belum Lunas</span>
    </a>
    <a href="<?= appUrl('pupuk/?view=riwayat') ?>" class="pupuk-tab-btn <?= ($activeView === 'riwayat') ? 'active' : '' ?>">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Riwayat Hutang / Pelunasan</span>
        <span class="pupuk-tab-badge"><?= count($pembayaranList) ?></span>
    </a>
</div>
