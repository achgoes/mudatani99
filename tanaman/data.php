<?php
$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $pName = htmlspecialchars($_GET['name'] ?? 'Tanaman');
    if ($_GET['status'] === 'added') {
        $toastTitle   = "Berhasil Ditambahkan!";
        $toastMessage = "Data pengelolaan tanaman <b>$pName</b> berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle   = "Berhasil Diperbarui!";
        $toastMessage = "Data pengelolaan tanaman <b>$pName</b> berhasil diperbarui.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = "Data pengelolaan tanaman berhasil dihapus.";
        $toastType    = "success";
    }
}

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// Ambil daftar master tanaman (khusus jenis = 'Tanaman' dari tabel master_produk)
$masterTanamanList = [];
$resMaster = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Tanaman' ORDER BY id ASC");
if ($resMaster) {
    while ($row = $resMaster->fetch_assoc()) {
        $row['nama_produk'] = formatTitleCase($row['nama_produk']);
        $row['keterangan']  = formatTitleCase($row['keterangan']);
        $masterTanamanList[] = $row;
    }
}

// Ambil daftar pengelolaan_tanaman (LEFT JOIN dengan master_produk jenis = 'Tanaman')
$tanamanList  = [];
$totalKemasan = 0;
$totalBerat   = 0.0;

$resTanaman = $conn->query("
    SELECT t.id, t.tanaman_id,
           COALESCE(m.nama_produk, t.nama_tanaman) AS nama_tanaman,
           t.jumlah_kemasan, t.jumlah_berat, t.keterangan, t.created_at
    FROM pengelolaan_tanaman t
    LEFT JOIN master_produk m ON t.tanaman_id = m.id AND m.jenis = 'Tanaman'
    ORDER BY t.id ASC
");
if ($resTanaman) {
    while ($row = $resTanaman->fetch_assoc()) {
        $row['nama_tanaman']   = formatTitleCase($row['nama_tanaman']);
        $row['jumlah_kemasan'] = formatTitleCase($row['jumlah_kemasan']);
        $row['jumlah_berat']   = formatTitleCase($row['jumlah_berat']);
        $row['keterangan']     = formatTitleCase($row['keterangan']);
        $tanamanList[] = $row;

        if (preg_match('/([0-9]+([.,][0-9]+)?)/', $row['jumlah_kemasan'], $mKemasan)) {
            $totalKemasan += (int)str_replace(',', '.', $mKemasan[1]);
        }
        if (preg_match('/([0-9]+([.,][0-9]+)?)/', $row['jumlah_berat'], $mBerat)) {
            $totalBerat += (float)str_replace(',', '.', $mBerat[1]);
        }
    }
}

