<?php
$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $pName = htmlspecialchars($_GET['name'] ?? 'Obat');
    if ($_GET['status'] === 'added') {
        $toastTitle   = "Berhasil Ditambahkan!";
        $toastMessage = "Data pengelolaan obat <b>$pName</b> berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle   = "Berhasil Diperbarui!";
        $toastMessage = "Data pengelolaan obat <b>$pName</b> berhasil diperbarui.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = "Data pengelolaan obat berhasil dihapus.";
        $toastType    = "success";
    }
}

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// Ambil daftar master obat (khusus jenis = 'Obat' dari tabel master_produk)
$masterObatList = [];
$resMaster = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Obat' ORDER BY id ASC");
if ($resMaster) {
    while ($row = $resMaster->fetch_assoc()) {
        $row['nama_produk'] = formatTitleCase($row['nama_produk']);
        $row['keterangan']  = formatTitleCase($row['keterangan']);
        $masterObatList[] = $row;
    }
}

// Ambil daftar pengelolaan_obat (LEFT JOIN dengan master_produk jenis = 'Obat')
$obatList     = [];
$totalKemasan = 0;
$totalBerat   = 0.0;

$resObat = $conn->query("
    SELECT o.id, o.obat_id,
           COALESCE(m.nama_produk, o.nama_obat) AS nama_obat,
           o.jumlah_kemasan, o.jumlah_berat, o.keterangan, o.created_at
    FROM pengelolaan_obat o
    LEFT JOIN master_produk m ON o.obat_id = m.id AND m.jenis = 'Obat'
    ORDER BY o.id ASC
");
if ($resObat) {
    while ($row = $resObat->fetch_assoc()) {
        $row['nama_obat']      = formatTitleCase($row['nama_obat']);
        $row['jumlah_kemasan'] = formatTitleCase($row['jumlah_kemasan']);
        $row['jumlah_berat']   = formatTitleCase($row['jumlah_berat']);
        $row['keterangan']     = formatTitleCase($row['keterangan']);
        $obatList[] = $row;

        if (preg_match('/([0-9]+([.,][0-9]+)?)/', $row['jumlah_kemasan'], $mKemasan)) {
            $totalKemasan += (int)str_replace(',', '.', $mKemasan[1]);
        }
        if (preg_match('/([0-9]+([.,][0-9]+)?)/', $row['jumlah_berat'], $mBerat)) {
            $totalBerat += (float)str_replace(',', '.', $mBerat[1]);
        }
    }
}

