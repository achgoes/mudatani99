<!-- Table Card -->
<div class="user-table-card">
    <div class="table-card-header">
        <div class="table-title-group">
            <h3>Daftar Pengguna Terdaftar</h3>
            <p>Total <?= count($usersList) ?> akun memiliki akses login dengan Username atau NIK.</p>
        </div>

        <div class="table-card-actions">
            <!-- Search Box -->
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="user-search-input" placeholder="Cari NIK, Username, atau Nama..." onkeyup="filterUserTable()">
            </div>

            <!-- Tombol Tambah User -->
            <button type="button" class="btn-add-user" onclick="openModalTambah()">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah User</span>
            </button>
        </div>
    </div>

    <!-- Table Content -->
    <div class="table-responsive-custom">
        <table class="user-table" id="table-user-data">
            <thead>
                <tr>
                    <th class="col-nomor">No</th>
                    <th>Pengguna / Nama</th>
                    <th>NIK (4 Digit)</th>
                    <th>Username</th>
                    <th>Terdaftar Sejak</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usersList)): ?>
                <tr>
                    <td colspan="6" class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                        Belum ada data user di dalam tabel users.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($usersList as $u): ?>
                    <?php 
                        $isMe = ($u['id'] == $_SESSION['user_id']);
                        // Inisial avatar
                        $nParts = preg_split('/\s+/', trim($u['nama']));
                        $initials = '';
                        if (count($nParts) >= 2) {
                            $initials = strtoupper(mb_substr($nParts[0], 0, 1) . mb_substr($nParts[1], 0, 1));
                        } else {
                            $initials = strtoupper(mb_substr($u['nama'], 0, 2));
                        }
                    ?>
                    <tr class="user-row-item">
                        <td class="font-semibold text-slate-500"><?= $no++ ?></td>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-badge"><?= htmlspecialchars($initials) ?></div>
                                <div class="user-name-cell">
                                    <h4><?= htmlspecialchars($u['nama']) ?></h4>
                                    <?php if ($isMe): ?>
                                        <span class="badge-current-user"><i class="fa-solid fa-circle-check"></i> Sesi Anda</span>
                                    <?php else: ?>
                                        <span>User ID: #<?= $u['id'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="nik-badge">
                                <i class="fa-regular fa-id-card text-agro-600"></i>
                                <?= htmlspecialchars($u['nik']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="username-pill">@<?= htmlspecialchars($u['username']) ?></span>
                        </td>
                        <td class="text-slate-500 text-xs">
                            <?= !empty($u['created_at']) ? date('d M Y, H:i', strtotime($u['created_at'])) : '-' ?>
                        </td>
                        <td>
                            <div class="action-btn-group justify-end">
                                <!-- Tombol Edit User -->
                                <button 
                                    type="button" 
                                    class="btn-table-edit"
                                    onclick="openModalEdit(<?= htmlspecialchars(json_encode($u)) ?>)"
                                    title="Edit Data User"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <!-- Tombol Hapus User (Hanya jika bukan user aktif) -->
                                <?php if (!$isMe): ?>
                                <button 
                                    type="button" 
                                    class="btn-table-delete"
                                    onclick="openModalDelete(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['nama'])) ?>', '<?= htmlspecialchars($u['nik']) ?>')"
                                    title="Hapus User"
                                >
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
