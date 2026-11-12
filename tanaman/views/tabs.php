<!-- Navigasi Tab Cepat Antar Submenu Tanaman -->
<div class="tanaman-tab-nav">
    <a href="<?= appUrl('tanaman/') ?>" class="tanaman-tab-btn <?= ($activeView === 'kelola') ? 'active' : '' ?>">
        <i class="fa-solid fa-wheat-awn"></i>
        <span>Stok Tanaman</span>
        <span class="tanaman-tab-badge"><?= count($tanamanList) ?></span>
    </a>
    <a href="<?= appUrl('tanaman/?view=riwayat_stok') ?>" class="tanaman-tab-btn <?= ($activeView === 'riwayat_stok') ? 'active' : '' ?>">
        <i class="fa-solid fa-boxes-packing"></i>
        <span>Riwayat Stok Tanaman</span>
        <span class="tanaman-tab-badge"><?= count($riwayatStokList) ?></span>
    </a>
    <a href="<?= appUrl('tanaman/?view=penjualan') ?>" class="tanaman-tab-btn <?= ($activeView === 'penjualan') ? 'active' : '' ?>">
        <i class="fa-solid fa-cart-shopping"></i>
        <span>Penjualan</span>
        <span class="tanaman-tab-badge"><?= count($penjualanList) ?></span>
    </a>
    <a href="<?= appUrl('tanaman/?view=riwayat_penjualan') ?>" class="tanaman-tab-btn <?= ($activeView === 'riwayat_penjualan') ? 'active' : '' ?>">
        <i class="fa-solid fa-receipt"></i>
        <span>Riwayat Penjualan</span>
        <span class="tanaman-tab-badge"><?= count($riwayatPenjualanList) ?></span>
    </a>
    <a href="<?= appUrl('tanaman/?view=hutang') ?>" class="tanaman-tab-btn <?= ($activeView === 'hutang') ? 'active' : '' ?>">
        <i class="fa-solid fa-hand-holding-dollar"></i>
        <span>Hutang Piutang</span>
        <span class="tanaman-tab-badge"><?= $countBelumLunas ?> Belum Lunas</span>
    </a>
    <a href="<?= appUrl('tanaman/?view=riwayat') ?>" class="tanaman-tab-btn <?= ($activeView === 'riwayat') ? 'active' : '' ?>">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Riwayat Hutang / Pelunasan</span>
        <span class="tanaman-tab-badge"><?= count($pembayaranList) ?></span>
    </a>
</div>
