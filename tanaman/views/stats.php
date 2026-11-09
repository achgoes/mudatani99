<!-- Ringkasan Statistik Pengelolaan Tanaman -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Data Tanaman</h5>
            <div class="stat-value"><?= count($tanamanList) ?></div>
            <div class="stat-desc">Data inventaris tercatat</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-wheat-awn"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Jumlah Kemasan</h5>
            <div class="stat-value"><?= number_format($totalKemasan, 0, ',', '.') ?> <span class="text-sm font-semibold">Pcs</span></div>
            <div class="stat-desc">Akumulasi kemasan tanaman/benih</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-boxes-stacked"></i>
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

