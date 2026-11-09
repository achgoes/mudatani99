<!-- Quick Stats Summary -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Total Pengguna</h5>
            <div class="stat-value"><?= count($usersList) ?></div>
            <div class="stat-desc">Akun terdaftar aktif</div>
        </div>
        <div class="stat-icon-wrap stat-icon-green">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>NIK Terakhir</h5>
            <div class="stat-value"><?= htmlspecialchars(end($usersList)['nik'] ?? '0000') ?></div>
            <div class="stat-desc">Saran NIK baru: <?= $nextSuggestedNik ?></div>
        </div>
        <div class="stat-icon-wrap stat-icon-blue">
            <i class="fa-solid fa-id-card"></i>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-info">
            <h5>Akun Anda</h5>
            <div class="stat-value text-base font-bold truncate max-w-[140px]"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
            <div class="stat-desc">NIK: <?= htmlspecialchars($_SESSION['nik'] ?? '-') ?></div>
        </div>
        <div class="stat-icon-wrap stat-icon-amber">
            <i class="fa-solid fa-user-shield"></i>
        </div>
    </div>
</div>
