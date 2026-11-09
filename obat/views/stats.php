<!-- Ringkasan Statistik Pengelolaan Obat -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Data Obat</h5>
            <div class="stat-value"><?= count($obatList) ?></div>
            <div class="stat-desc">Data inventaris tercatat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-shield-virus"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Jumlah Kemasan</h5>
            <div class="stat-value"><?= number_format($totalKemasan, 0, ',', '.') ?> <span class="text-sm font-semibold">Pcs / Botol</span></div>
            <div class="stat-desc">Akumulasi kemasan obat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-prescription-bottle-medical"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Jumlah Berat</h5>
            <div class="stat-value"><?= number_format($totalBerat, 0, ',', '.') ?> <span class="text-sm font-semibold">Kg</span></div>
            <div class="stat-desc">Akumulasi berat gudang</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
    </div>
</div>

