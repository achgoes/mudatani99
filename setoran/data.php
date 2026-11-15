<?php
/**
 * mudatani99 - Data Loader & Flash Toast Manajemen Setoran Petani
 * File: setoran/data.php
 */

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

if (isset($_GET['status'])) {
    $pName  = htmlspecialchars($_GET['name'] ?? 'Petani');
    $pBerat = htmlspecialchars($_GET['berat'] ?? '');

    if ($_GET['status'] === 'added') {
        $toastTitle   = "Setoran Berhasil Dicatat!";
        $beratInfo    = ($pBerat !== '') ? " dengan total berat <b>$pBerat</b>" : "";
        $toastMessage = "Data setoran tanaman atas nama <b>$pName</b>" . $beratInfo . " berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = "Data setoran petani telah dihapus dari sistem.";
        $toastType    = "success";
    }
}

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// 1. Daftar Anggota Petani (sumber dropdown Nama Petani)
$anggotaList = [];
$resAnggota  = $conn->query("SELECT id, nama_petani, alamat, keterangan FROM anggota_petani ORDER BY id ASC");
if ($resAnggota) {
    while ($row = $resAnggota->fetch_assoc()) {
        $row['nama_petani'] = formatTitleCaseSetoran($row['nama_petani']);
        $row['alamat']      = formatTitleCaseSetoran($row['alamat']);
        $anggotaList[]      = $row;
    }
}

// 2. Daftar Master Tanaman (sumber dropdown Setor Tanaman dari master_produk jenis = 'Tanaman')
$masterTanamanList = [];
$resMaster         = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Tanaman' ORDER BY id ASC");
if ($resMaster) {
    while ($row = $resMaster->fetch_assoc()) {
        $row['nama_produk'] = formatTitleCaseSetoran($row['nama_produk']);
        $row['keterangan']  = formatTitleCaseSetoran($row['keterangan']);
        $masterTanamanList[] = $row;
    }
}

// 3. Daftar Setoran Petani beserta item tanaman & berat per item
$setoranItemsGrouped = getAllSetoranItemsGrouped($conn);
$setoranList         = [];
$setoranInfoMap      = [];
$totalBeratSetoran   = 0.0;
$petaniPenyetorIds   = [];
$setoranBulanIniCount = 0;
$setoranBulanIniBerat = 0.0;
$currentYearMonth    = date('Y-m');

$resSetoran = $conn->query("
    SELECT s.id, s.kode_setoran, s.anggota_id,
           COALESCE(a.nama_petani, s.nama_petani) AS nama_petani,
           COALESCE(NULLIF(a.alamat, ''), '-') AS alamat_petani,
           s.nama_tanaman, s.jumlah_berat, s.tanggal,
           s.keterangan, s.dicatat_oleh, s.created_at
    FROM setoran_petani s
    LEFT JOIN anggota_petani a ON s.anggota_id = a.id
    ORDER BY s.tanggal DESC, s.id DESC
");

if ($resSetoran) {
    while ($row = $resSetoran->fetch_assoc()) {
        $sid = (int)$row['id'];
        $row['nama_petani']   = formatTitleCaseSetoran($row['nama_petani']);
        $row['alamat_petani'] = formatTitleCaseSetoran($row['alamat_petani'] ?: '-');
        $row['keterangan']    = formatTitleCaseSetoran($row['keterangan'] ?: '-');
        $row['dicatat_oleh']  = formatTitleCaseSetoran($row['dicatat_oleh'] ?: '-');

        $rowItems  = $setoranItemsGrouped[$sid] ?? [];
        $itemNames = [];
        $sumBerat  = 0.0;

        foreach ($rowItems as $it) {
            if (!in_array($it['nama_tanaman'], $itemNames, true)) {
                $itemNames[] = $it['nama_tanaman'];
            }
            $sumBerat += (float)$it['berat_num'];
        }

        if (!empty($rowItems)) {
            $row['nama_tanaman'] = implode(', ', $itemNames);
            $beratNum            = round($sumBerat, 2);
            $row['jumlah_berat'] = formatBeratSetoranStr($beratNum);
        } else {
            $beratNum            = parseBeratSetoranFloat($row['jumlah_berat']);
            $row['nama_tanaman'] = formatTitleCaseSetoran($row['nama_tanaman']);
            $row['jumlah_berat'] = formatBeratSetoranStr($beratNum);
            $rowItems[] = [
                'id'           => 0,
                'setoran_id'   => $sid,
                'tanaman_id'   => null,
                'nama_tanaman' => $row['nama_tanaman'],
                'berat'        => $row['jumlah_berat'],
                'berat_num'    => $beratNum,
            ];
        }

        $row['items']     = $rowItems;
        $row['berat_num'] = $beratNum;
        $row['tanggal']   = !empty($row['tanggal']) ? $row['tanggal'] : substr((string)$row['created_at'], 0, 10);

        $setoranList[]        = $row;
        $setoranInfoMap[$sid] = $row;

        $totalBeratSetoran += $beratNum;
        $petaniPenyetorIds[(int)$row['anggota_id']] = true;

        if (strpos((string)$row['tanggal'], $currentYearMonth) === 0) {
            $setoranBulanIniCount++;
            $setoranBulanIniBerat += $beratNum;
        }
    }
}
