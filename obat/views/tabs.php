<!-- Navigasi Tab Cepat Antar Submenu Obat -->
<div class="obat-tab-nav">
    <a href="<?= appUrl('obat/') ?>" class="obat-tab-btn <?= ($activeView === 'kelola') ? 'active' : '' ?>">
        <i class="fa-solid fa-shield-virus"></i>
        <span>Stok Obat</span>
        <span class="obat-tab-badge"><?= count($obatList) ?></span>
    </a>
    <a href="<?= appUrl('obat/?view=riwayat_stok') ?>" class="obat-tab-btn <?= ($activeView === 'riwayat_stok') ? 'active' : '' ?>">
        <i class="fa-solid fa-boxes-packing"></i>
        <span>Riwayat Stok Obat</span>
        <span class="obat-tab-badge"><?= count($riwayatStokList) ?></span>
    </a>
    <a href="<?= appUrl('obat/?view=penjualan') ?>" class="obat-tab-btn <?= ($activeView === 'penjualan') ? 'active' : '' ?>">
        <i class="fa-solid fa-cart-shopping"></i>
        <span>Penjualan</span>
        <span class="obat-tab-badge"><?= count($penjualanList) ?></span>
    </a>
    <a href="<?= appUrl('obat/?view=riwayat_penjualan') ?>" class="obat-tab-btn <?= ($activeView === 'riwayat_penjualan') ? 'active' : '' ?>">
        <i class="fa-solid fa-receipt"></i>
        <span>Riwayat Penjualan</span>
        <span class="obat-tab-badge"><?= count($riwayatPenjualanList) ?></span>
    </a>
    <a href="<?= appUrl('obat/?view=hutang') ?>" class="obat-tab-btn <?= ($activeView === 'hutang') ? 'active' : '' ?>">
        <i class="fa-solid fa-hand-holding-dollar"></i>
        <span>Hutang Piutang</span>
        <span class="obat-tab-badge"><?= $countBelumLunas ?> Belum Lunas</span>
    </a>
    <a href="<?= appUrl('obat/?view=riwayat') ?>" class="obat-tab-btn <?= ($activeView === 'riwayat') ? 'active' : '' ?>">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Riwayat Hutang / Pelunasan</span>
        <span class="obat-tab-badge"><?= count($pembayaranList) ?></span>
    </a>
</div>
