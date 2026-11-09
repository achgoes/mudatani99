<?php
$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $pName = htmlspecialchars($_GET['name'] ?? 'Produk');
    if ($_GET['status'] === 'added') {
        $toastTitle   = "Berhasil Ditambahkan!";
        $toastMessage = "Data master <b>$pName</b> berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle   = "Berhasil Diperbarui!";
        $toastMessage = "Data master <b>$pName</b> berhasil diperbarui.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = "Data master produk berhasil dihapus.";
        $toastType    = "success";
    }
}

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// Ambil seluruh data master_produk
$produkList   = [];
$countObat    = 0;
$countPupuk   = 0;
$countTanaman = 0;

// Opsi pengurutan (Sort) hanya diperbolehkan pada nomor, nama produk, dan jenis
$allowedSortCols = [
    'nomor' => 'id',
    'id'    => 'id',
    'nama'  => 'nama_produk',
    'jenis' => 'jenis'
];
$sortColParam   = strtolower($_GET['sort'] ?? 'nomor');
$sortOrderParam = strtolower($_GET['order'] ?? 'asc');

if (!array_key_exists($sortColParam, $allowedSortCols)) {
    $sortColParam = 'nomor';
}
if (!in_array($sortOrderParam, ['asc', 'desc'], true)) {
    $sortOrderParam = 'asc';
}

$dbSortField = $allowedSortCols[$sortColParam];
$dbSortDir   = strtoupper($sortOrderParam);

$orderBySql = "$dbSortField $dbSortDir";
if ($dbSortField === 'jenis') {
    $orderBySql .= ", nama_produk ASC";
} elseif ($dbSortField === 'nama_produk') {
    $orderBySql .= ", id ASC";
}

$result = $conn->query("SELECT id, nama_produk, jenis, keterangan, created_at FROM master_produk ORDER BY $orderBySql");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $produkList[] = $row;
        if ($row['jenis'] === 'Obat') {
            $countObat++;
        } elseif ($row['jenis'] === 'Pupuk') {
            $countPupuk++;
        } elseif ($row['jenis'] === 'Tanaman') {
            $countTanaman++;
        }
    }
}

