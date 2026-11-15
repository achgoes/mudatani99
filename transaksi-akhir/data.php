<?php
/**
 * mudatani99 - Data Loader & Flash Toast Transaksi Akhir
 * File: transaksi-akhir/data.php
 */

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

if (isset($_GET['status'])) {
    $pName = htmlspecialchars($_GET['name'] ?? 'Petani');
    $pUang = htmlspecialchars($_GET['uang'] ?? '');

    if ($_GET['status'] === 'added') {
        $toastTitle   = "Transaksi Akhir Disimpan!";
        $uangInfo     = ($pUang !== '') ? " dengan jumlah uang <b>$pUang</b>" : "";
        $toastMessage = "Transaksi akhir atas nama <b>$pName</b>" . $uangInfo . " berhasil dicatat.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle   = "Transaksi Akhir Diperbarui!";
        $uangInfo     = ($pUang !== '') ? " (Jumlah uang: <b>$pUang</b>)" : "";
        $toastMessage = "Perubahan transaksi akhir <b>$pName</b>" . $uangInfo . " berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = "Data transaksi akhir telah dihapus dari sistem.";
        $toastType    = "success";
    }
}

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// 1. Item detail setoran petani dikelompokkan per setoran_id
$setoranItemsGrouped = getSetoranItemsGroupedForTa($conn);

// 2. Daftar Transaksi Akhir
$transaksiList       = [];
$transaksiInfoMap    = [];
$usedSetoranMap      = [];
$totalBeratAkhirSum  = 0.0;
$totalJumlahUangSum  = 0;
$bulanIniCount       = 0;
$bulanIniUangSum     = 0;
$currentYearMonth    = date('Y-m');

$resTa = $conn->query("
    SELECT t.id, t.kode_transaksi, t.setoran_id, t.anggota_id,
           COALESCE(a.nama_petani, s.nama_petani, t.nama_petani) AS nama_petani,
           COALESCE(NULLIF(a.alamat, ''), '-') AS alamat_petani,
           s.kode_setoran,
           s.nama_tanaman AS setoran_tanaman,
           s.jumlah_berat AS setoran_jumlah_berat_raw,
           s.tanggal AS tanggal_setoran,
           s.keterangan AS keterangan_setoran,
           t.berat_setoran, t.rapaksi, t.berat_akhir,
           t.harga_per_kg, t.jumlah_uang, t.tanggal,
           t.keterangan, t.dicatat_oleh, t.created_at
    FROM transaksi_akhir t
    LEFT JOIN setoran_petani s ON t.setoran_id = s.id
    LEFT JOIN anggota_petani a ON COALESCE(t.anggota_id, s.anggota_id) = a.id
    ORDER BY t.tanggal DESC, t.id DESC
");

if ($resTa) {
    while ($row = $resTa->fetch_assoc()) {
        $tid = (int)$row['id'];
        $sid = (int)$row['setoran_id'];

        $usedSetoranMap[$sid] = $tid;

        // Sinkronkan berat setoran dari data setoran_petani terbaru jika tersedia
        $beratSetoranNum = ($row['setoran_jumlah_berat_raw'] !== null && $row['setoran_jumlah_berat_raw'] !== '')
            ? round(parseDecimalTa($row['setoran_jumlah_berat_raw']), 2)
            : round((float)$row['berat_setoran'], 2);
        $rapaksiNum    = round((float)$row['rapaksi'], 2);
        $beratAkhirNum = round(max(0.0, $beratSetoranNum - $rapaksiNum), 2);
        $hargaNum      = (int)$row['harga_per_kg'];
        $jumlahUangNum = (int)round($beratAkhirNum * $hargaNum);

        $row['nama_petani']       = formatTitleCaseTa($row['nama_petani']);
        $row['alamat_petani']     = formatTitleCaseTa($row['alamat_petani'] ?: '-');
        $row['setoran_tanaman']   = formatTitleCaseTa($row['setoran_tanaman'] ?: '-');
        $row['keterangan']        = formatTitleCaseTa($row['keterangan'] ?: '-');
        $row['keterangan_setoran']= formatTitleCaseTa($row['keterangan_setoran'] ?: '-');
        $row['dicatat_oleh']      = formatTitleCaseTa($row['dicatat_oleh'] ?: '-');

        $row['berat_setoran_num'] = $beratSetoranNum;
        $row['berat_setoran_str'] = formatBeratTaStr($beratSetoranNum);
        $row['rapaksi_num']       = $rapaksiNum;
        $row['rapaksi_plain']     = formatBeratPlainTa($rapaksiNum);
        $row['rapaksi_str']       = formatBeratTaStr($rapaksiNum);
        $row['berat_akhir_num']   = $beratAkhirNum;
        $row['berat_akhir_str']   = formatBeratTaStr($beratAkhirNum);
        $row['harga_per_kg_num']  = $hargaNum;
        $row['harga_per_kg_str']  = formatRupiahTaStr($hargaNum);
        $row['jumlah_uang_num']   = $jumlahUangNum;
        $row['jumlah_uang_str']   = formatRupiahTaStr($jumlahUangNum);
        $row['tanggal']           = !empty($row['tanggal']) ? $row['tanggal'] : substr((string)$row['created_at'], 0, 10);
        $row['setoran_items']     = $setoranItemsGrouped[$sid] ?? [];

        $transaksiList[]        = $row;
        $transaksiInfoMap[$tid] = $row;

        $totalBeratAkhirSum += $beratAkhirNum;
        $totalJumlahUangSum += $jumlahUangNum;

        if (strpos((string)$row['tanggal'], $currentYearMonth) === 0) {
            $bulanIniCount++;
            $bulanIniUangSum += $jumlahUangNum;
        }
    }
}

// 3. Daftar Setoran Petani untuk dropdown Tambah/Edit Transaksi Akhir
$setoranOptions = [];
$availableSetoranCount = 0;
$resSetoranOpt = $conn->query("
    SELECT s.id, s.kode_setoran, s.anggota_id,
           COALESCE(a.nama_petani, s.nama_petani) AS nama_petani,
           COALESCE(NULLIF(a.alamat, ''), '-') AS alamat_petani,
           s.nama_tanaman, s.jumlah_berat, s.tanggal, s.keterangan
    FROM setoran_petani s
    LEFT JOIN anggota_petani a ON s.anggota_id = a.id
    ORDER BY s.tanggal DESC, s.id DESC
");

if ($resSetoranOpt) {
    while ($row = $resSetoranOpt->fetch_assoc()) {
        $sid      = (int)$row['id'];
        $beratNum = round(parseDecimalTa($row['jumlah_berat']), 2);
        $usedById = $usedSetoranMap[$sid] ?? 0;

        $row['nama_petani']      = formatTitleCaseTa($row['nama_petani']);
        $row['alamat_petani']    = formatTitleCaseTa($row['alamat_petani'] ?: '-');
        $row['nama_tanaman']     = formatTitleCaseTa($row['nama_tanaman'] ?: '-');
        $row['keterangan']       = formatTitleCaseTa($row['keterangan'] ?: '-');
        $row['berat_num']        = $beratNum;
        $row['berat_str']        = formatBeratTaStr($beratNum);
        $row['used_ta_id']       = $usedById;
        $row['items']            = $setoranItemsGrouped[$sid] ?? [];

        if ($usedById === 0) {
            $availableSetoranCount++;
        }

        $setoranOptions[] = $row;
    }
}
