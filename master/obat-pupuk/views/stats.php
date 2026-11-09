<!-- Ringkasan Statistik Master Produk -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Produk</h5>
            <div class="stat-value"><?= count($produkList) ?></div>
            <div class="stat-desc">Semua item terdaftar</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-layer-group"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Master Obat</h5>
            <div class="stat-value"><?= $countObat ?></div>
            <div class="stat-desc">Pestisida & Obat Hama</div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-shield-virus"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Master Pupuk</h5>
            <div class="stat-value"><?= $countPupuk ?></div>
            <div class="stat-desc">Pupuk Subsidi & Organik</div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-flask-vial"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Master Tanaman</h5>
            <div class="stat-value"><?= $countTanaman ?></div>
            <div class="stat-desc">Bibit & Komoditas Tanam</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-wheat-awn"></i>
        </div>
    </div>
</div>

