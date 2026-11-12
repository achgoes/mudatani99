<?php
/**
 * mudatani99 - Data Loader & Flash/Toast Message Pupuk
 * File: pupuk/data.php
 */

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $pName   = htmlspecialchars($_GET['name'] ?? 'Pupuk');
    $rKarung = htmlspecialchars($_GET['karung'] ?? '');
    $rStatus = htmlspecialchars($_GET['st'] ?? '');

    if ($_GET['status'] === 'added') {
        $toastTitle = "Berhasil Ditambahkan!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok pupuk baru <b>$pName</b> berhasil disimpan.";
        } else {
            $toastMessage = "Data hutang pupuk petani <b>$pName</b> disimpan & data Stok Pupuk otomatis dikurangi.";
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'stock_added') {
        $toastTitle   = "Stok Berhasil Ditambahkan!";
        $addK         = htmlspecialchars($_GET['add_k'] ?? '');
        $totK         = htmlspecialchars($_GET['tot_k'] ?? '');
        $totB         = htmlspecialchars($_GET['tot_b'] ?? '');
        $extraDetail  = ($addK !== '' && $totK !== '')
            ? " bertambah <b>+$addK Karung</b>. Total stok saat ini: <b>$totK Karung ($totB)</b>."
            : " berhasil diakumulasikan ke stok yang sudah ada.";
        $toastMessage = "Stok pupuk <b>$pName</b>" . $extraDetail;
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle = "Berhasil Diperbarui!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok pupuk <b>$pName</b> berhasil diperbarui.";
        } else {
            $toastMessage = "Data hutang pupuk petani <b>$pName</b> diperbarui & data Stok Pupuk otomatis disinkronkan.";
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle = "Berhasil Dihapus!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok pupuk berhasil dihapus.";
        } else {
            $extraInfo = $rKarung !== '' ? " Stok <b>$rKarung</b> telah dikembalikan ke Stok Pupuk." : "";
            $toastMessage = "Data hutang piutang berhasil dihapus." . $extraInfo;
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'status_changed') {
        $toastTitle   = "Status Diperbarui!";
        $toastMessage = "Status hutang pupuk <b>$pName</b> berhasil diubah menjadi <b>$rStatus</b>.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'paid') {
        $toastTitle   = "Pembayaran Berhasil!";
        $nominal      = htmlspecialchars($_GET['nominal'] ?? '');
        $nominalText  = ($nominal !== '') ? " sebesar <b>$nominal</b>" : "";
        $paidStatus   = ($rStatus !== '') ? $rStatus : 'Lunas';
        $toastMessage = "Pembayaran hutang pupuk petani <b>$pName</b>" . $nominalText . " telah berhasil dicatat &amp; status kini <b>$paidStatus</b>.";
        $toastType    = "success";
    }
}

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// =====================================================================
// AMBIL DATA UNTUK TAMPILAN & DROPDOWN
// =====================================================================

// 1. Daftar Anggota Petani
$anggotaList = [];
$resAnggota = $conn->query("SELECT id, nama_petani, alamat, keterangan FROM anggota_petani ORDER BY id ASC");
if ($resAnggota) {
    while ($row = $resAnggota->fetch_assoc()) {
        $row['nama_petani'] = formatTitleCase($row['nama_petani']);
        $anggotaList[] = $row;
    }
}

// 2. Daftar Master Pupuk
$masterPupukList = [];
$resMaster = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Pupuk' ORDER BY id ASC");
if ($resMaster) {
    while ($row = $resMaster->fetch_assoc()) {
        $row['nama_produk'] = formatTitleCase($row['nama_produk']);
        $row['keterangan']  = formatTitleCase($row['keterangan']);
        $masterPupukList[] = $row;
    }
}

// 3. Daftar Stok Pupuk
$pupukList   = [];
$totalKarung = 0;
$totalBerat  = 0.0;

$resPupuk = $conn->query("
    SELECT p.id, p.pupuk_id,
           COALESCE(m.nama_produk, p.nama_pupuk) AS nama_pupuk,
           p.jumlah_karung, p.jumlah_berat, p.keterangan, p.created_at
    FROM pengelolaan_pupuk p
    LEFT JOIN master_produk m ON p.pupuk_id = m.id AND m.jenis = 'Pupuk'
    ORDER BY p.id ASC
");
if ($resPupuk) {
    while ($row = $resPupuk->fetch_assoc()) {
        $row['nama_pupuk']    = formatTitleCase($row['nama_pupuk']);
        $row['jumlah_karung'] = formatTitleCase($row['jumlah_karung']);
        $row['jumlah_berat']  = formatTitleCase($row['jumlah_berat']);
        $row['keterangan']    = formatTitleCase($row['keterangan']);

        $karungNum = parseKarungInt($row['jumlah_karung']);
        $beratNum  = parseBeratFloat($row['jumlah_berat']);
        $row['karung_num']       = $karungNum;
        $row['berat_num']        = $beratNum;
        $row['berat_per_karung'] = ($karungNum > 0) ? round($beratNum / $karungNum, 2) : 50;

        $pupukList[] = $row;

        $totalKarung += $karungNum;
        $totalBerat  += $beratNum;
    }
}

$stokMapByPupukId   = [];
$stokMapByNameLower = [];
foreach ($pupukList as $pk) {
    if (!empty($pk['pupuk_id'])) {
        $stokMapByPupukId[(int)$pk['pupuk_id']] = $pk;
    }
    $cleanNameLower = mb_strtolower(trim($pk['nama_pupuk']));
    if ($cleanNameLower !== '') {
        $stokMapByNameLower[$cleanNameLower] = $pk;
    }
}

// 4. Daftar Hutang Piutang Pupuk
$hutangList          = [];
$totalKarungHutang   = 0;
$totalBeratHutang    = 0.0;
$countLunas          = 0;
$countBelumLunas     = 0;
$karungLunas         = 0;
$karungBelumLunas    = 0;

$resHutang = $conn->query("
    SELECT h.id, h.anggota_id,
           COALESCE(a.nama_petani, h.nama_petani) AS nama_petani,
           a.alamat AS alamat_petani,
           h.pengelolaan_pupuk_id, h.pupuk_id,
           COALESCE(m.nama_produk, p.nama_pupuk, h.nama_pupuk) AS nama_pupuk,
           h.jumlah_karung, h.jumlah_berat,
           COALESCE(h.harga, 'Rp 0') AS harga,
           COALESCE(h.jumlah_hutang, 'Rp 0') AS jumlah_hutang,
           COALESCE(h.sisa_hutang, 'Rp 0') AS sisa_hutang,
           h.keterangan,
           h.tanggal,
           h.status, h.tanggal_lunas, h.created_at
    FROM hutang_pupuk h
    LEFT JOIN anggota_petani a ON h.anggota_id = a.id
    LEFT JOIN pengelolaan_pupuk p ON h.pengelolaan_pupuk_id = p.id
    LEFT JOIN master_produk m ON COALESCE(p.pupuk_id, h.pupuk_id) = m.id AND m.jenis = 'Pupuk'
    ORDER BY h.id ASC
");
if ($resHutang) {
    while ($row = $resHutang->fetch_assoc()) {
        $row['nama_petani']   = formatTitleCase($row['nama_petani']);
        $row['nama_pupuk']    = formatTitleCase($row['nama_pupuk']);
        $row['jumlah_karung'] = formatTitleCase($row['jumlah_karung']);
        $row['jumlah_berat']  = formatTitleCase($row['jumlah_berat']);
        $row['harga']         = formatRupiahStr($row['harga'] ?? 'Rp 0');

        $kNum = parseKarungInt($row['jumlah_karung']);
        $bNum = parseBeratFloat($row['jumlah_berat']);
        $row['karung_num'] = $kNum;
        $row['berat_num']  = $bNum;

        $hargaNum = parseRupiahFloat($row['harga']);
        if (empty($row['jumlah_hutang']) || $row['jumlah_hutang'] === 'Rp 0') {
            $row['jumlah_hutang'] = formatRupiahStr($kNum * $hargaNum);
        } else {
            $row['jumlah_hutang'] = formatRupiahStr($row['jumlah_hutang']);
        }

        if ($row['status'] === 'Lunas') {
            $row['sisa_hutang'] = 'Rp 0';
        } elseif (empty($row['sisa_hutang']) || $row['sisa_hutang'] === 'Rp 0') {
            $row['sisa_hutang'] = $row['jumlah_hutang'];
        } else {
            $row['sisa_hutang'] = formatRupiahStr($row['sisa_hutang']);
        }

        $row['keterangan'] = stripHutangPaymentNote(formatTitleCase($row['keterangan']));
        $row['tanggal']    = !empty($row['tanggal']) ? $row['tanggal'] : substr($row['created_at'], 0, 10);

        $hutangList[] = $row;

        $totalKarungHutang += $kNum;
        $totalBeratHutang  += $bNum;

        if ($row['status'] === 'Lunas') {
            $countLunas++;
            $karungLunas += $kNum;
        } else {
            $countBelumLunas++;
            $karungBelumLunas += $kNum;
        }
    }
}

// 5. Riwayat Pembayaran Hutang
$pembayaranList      = [];
$pembayaranByHutang  = [];
$totalTerbayar       = 0;
$countPelunasan      = 0;
$petaniPembayarIds   = [];

$resBayar = $conn->query("
    SELECT b.id, b.hutang_id, b.anggota_id,
           COALESCE(a.nama_petani, b.nama_petani) AS nama_petani,
           COALESCE(m.nama_produk, p.nama_pupuk, h.nama_pupuk, b.nama_pupuk) AS nama_pupuk,
           h.jumlah_karung, h.jumlah_berat,
           b.pembayaran_ke, b.tanggal_bayar,
           b.total_hutang, b.sisa_sebelum, b.jumlah_bayar, b.sisa_hutang,
           b.metode_bayar, b.status, b.keterangan, b.dicatat_oleh, b.created_at
    FROM pembayaran_hutang_pupuk b
    LEFT JOIN hutang_pupuk h ON b.hutang_id = h.id
    LEFT JOIN anggota_petani a ON b.anggota_id = a.id
    LEFT JOIN pengelolaan_pupuk p ON h.pengelolaan_pupuk_id = p.id
    LEFT JOIN master_produk m ON COALESCE(p.pupuk_id, h.pupuk_id) = m.id AND m.jenis = 'Pupuk'
    ORDER BY b.tanggal_bayar DESC, b.id DESC
");
if ($resBayar) {
    while ($row = $resBayar->fetch_assoc()) {
        $row['nama_petani']      = formatTitleCase($row['nama_petani']);
        $row['nama_pupuk']       = formatTitleCase($row['nama_pupuk']);
        $row['keterangan']       = formatTitleCase($row['keterangan'] ?: '-');
        $row['dicatat_oleh']     = formatTitleCase($row['dicatat_oleh'] ?: '-');
        $row['total_hutang_str'] = formatRupiahStr($row['total_hutang']);
        $row['sisa_sebelum_str'] = formatRupiahStr($row['sisa_sebelum']);
        $row['jumlah_bayar_str'] = formatRupiahStr($row['jumlah_bayar']);
        $row['sisa_hutang_str']  = formatRupiahStr($row['sisa_hutang']);

        $pembayaranList[] = $row;
        $pembayaranByHutang[(int)$row['hutang_id']][] = $row;

        $totalTerbayar += (int)$row['jumlah_bayar'];
        $petaniPembayarIds[(int)$row['anggota_id']] = true;
        if ($row['status'] === 'Lunas') {
            $countPelunasan++;
        }
    }
}

$totalSisaPiutang = 0.0;
$hutangInfoMap    = [];
foreach ($hutangList as $hRow) {
    if ($hRow['status'] !== 'Lunas') {
        $totalSisaPiutang += parseRupiahFloat($hRow['sisa_hutang']);
    }
    $hutangInfoMap[(int)$hRow['id']] = $hRow;
}
