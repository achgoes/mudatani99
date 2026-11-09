<!-- Navigasi Tab Cepat Antar Submenu Pupuk -->
<div class="pupuk-tab-nav">
    <a href="<?= appUrl('pupuk/') ?>" class="pupuk-tab-btn <?= ($activeView === 'kelola') ? 'active' : '' ?>">
        <i class="fa-solid fa-flask-vial"></i>
        <span>Stok Pupuk</span>
        <span class="pupuk-tab-badge"><?= count($pupukList) ?></span>
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

<!-- Banner Informasi Auto-Sinkronisasi Antar Tabel -->
<div class="sync-info-banner">
    <div class="sync-banner-left">
        <div class="sync-banner-icon">
            <i class="fa-solid fa-arrows-rotate"></i>
        </div>
        <div>
            <strong>Auto-Sinkronisasi Aktif:</strong>
            Saat pupuk dipakai pada <b>Hutang Piutang</b>, stok karung &amp; berat pada <b>Stok Pupuk</b> otomatis berkurang. Jika data hutang dihapus, jumlah karung otomatis kembali ke stok gudang.
        </div>
    </div>
</div>
