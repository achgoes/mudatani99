<?php
$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $itemName = htmlspecialchars($_GET['name'] ?? 'Data');
    if ($_GET['status'] === 'added') {
        $toastTitle   = "Berhasil Ditambahkan!";
        $toastMessage = ($activeView === 'lahan')
            ? "Data lahan petani <b>$itemName</b> berhasil disimpan."
            : "Data anggota petani <b>$itemName</b> berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle   = "Berhasil Diperbarui!";
        $toastMessage = ($activeView === 'lahan')
            ? "Data lahan petani <b>$itemName</b> berhasil diperbarui."
            : "Data anggota petani <b>$itemName</b> berhasil diperbarui.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = ($activeView === 'lahan')
            ? "Data pemetaan lahan berhasil dihapus."
            : "Data anggota petani berhasil dihapus.";
        $toastType    = "success";
    }
}

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// Ambil seluruh data anggota petani (digunakan baik untuk tabel Daftar Anggota maupun dropdown Nama Petani di Pemetaan Lahan)
$anggotaList = [];
$resAnggota = $conn->query("SELECT id, nama_petani, alamat, keterangan, created_at FROM anggota_petani ORDER BY id ASC");
if ($resAnggota) {
    while ($row = $resAnggota->fetch_assoc()) {
        $anggotaList[] = $row;
    }
}

// Ambil daftar komoditas tanaman dari tabel master_produk (Filter khusus: jenis = 'Tanaman')
$tanamanList = [];
$resTanaman = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Tanaman' ORDER BY id ASC");
if ($resTanaman) {
    while ($row = $resTanaman->fetch_assoc()) {
        $tanamanList[] = $row;
    }
}

// Ambil seluruh data pemetaan & luas lahan (JOIN anggota_petani & LEFT JOIN master_produk jenis Tanaman)
$lahanList = [];
$totalLuasHa = 0.0;
$resLahan = $conn->query("
    SELECT l.id, l.anggota_id, a.nama_petani, l.luas_lahan, l.tanaman_id,
           COALESCE(m.nama_produk, l.ditanami) AS ditanami,
           l.keterangan, l.created_at
    FROM lahan_petani l
    INNER JOIN anggota_petani a ON l.anggota_id = a.id
    LEFT JOIN master_produk m ON l.tanaman_id = m.id AND m.jenis = 'Tanaman'
    ORDER BY l.id ASC
");
if ($resLahan) {
    while ($row = $resLahan->fetch_assoc()) {
        $lahanList[] = $row;
        // Hitung akumulasi luas lahan jika mengandung angka
        if (preg_match('/([0-9]+([.,][0-9]+)?)/', $row['luas_lahan'], $matches)) {
            $totalLuasHa += (float)str_replace(',', '.', $matches[1]);
        }
    }
}
