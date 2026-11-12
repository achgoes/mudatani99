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
        } elseif ($activeView === 'penjualan' || $activeView === 'riwayat_penjualan') {
            $toastMessage = "Transaksi penjualan pupuk untuk <b>$pName</b> disimpan &amp; Stok Pupuk otomatis dikurangi.";
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
        } elseif ($activeView === 'penjualan' || $activeView === 'riwayat_penjualan') {
            $toastMessage = "Transaksi penjualan pupuk untuk <b>$pName</b> diperbarui &amp; Stok Pupuk otomatis disinkronkan.";
        } else {
            $toastMessage = "Data hutang pupuk petani <b>$pName</b> diperbarui & data Stok Pupuk otomatis disinkronkan.";
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle = "Berhasil Dihapus!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok pupuk berhasil dihapus.";
        } elseif ($activeView === 'penjualan' || $activeView === 'riwayat_penjualan') {
            $extraInfo = $rKarung !== '' ? " Stok <b>$rKarung</b> telah dikembalikan ke Stok Pupuk." : "";
            $toastMessage = "Transaksi penjualan pupuk berhasil dihapus." . $extraInfo;
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
$hutangItemsGrouped  = getAllHutangItemsGrouped($conn);
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
        $hid = (int)$row['id'];
        $row['nama_petani'] = formatTitleCase($row['nama_petani']);

        $rowItems = $hutangItemsGrouped[$hid] ?? [];
        if (empty($rowItems)) {
            $rowItems = [normalizeHutangItemRow($row)];
        }

        $itemNames   = [];
        $kNum        = 0;
        $bNum        = 0.0;
        $sumSubtotal = 0.0;
        foreach ($rowItems as $it) {
            if (!in_array($it['nama_pupuk'], $itemNames, true)) {
                $itemNames[] = $it['nama_pupuk'];
            }
            $kNum        += (int)$it['karung_num'];
            $bNum        += (float)$it['berat_num'];
            $sumSubtotal += (float)$it['subtotal_num'];
        }

        $row['items']         = $rowItems;
        $row['nama_pupuk']    = implode(', ', $itemNames);
        $row['jumlah_karung'] = formatKarungStr($kNum);
        $row['jumlah_berat']  = formatBeratStr($bNum);
        $row['harga']         = (count($rowItems) === 1) ? $rowItems[0]['harga'] : 'Rp 0';
        $row['karung_num']    = $kNum;
        $row['berat_num']     = $bNum;

        if (!empty($row['jumlah_hutang']) && $row['jumlah_hutang'] !== 'Rp 0') {
            $row['jumlah_hutang'] = formatRupiahStr($row['jumlah_hutang']);
        } else {
            $row['jumlah_hutang'] = formatRupiahStr($sumSubtotal);
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

// 6. Daftar Penjualan Pupuk (Berdiri sendiri, terpisah dari Hutang Piutang)
$penjualanItemsGrouped     = getAllPenjualanItemsGrouped($conn);
$penjualanList             = [];
$penjualanInfoMap          = [];
$totalPendapatanPenjualan  = 0;
$totalKarungPenjualan      = 0;
$totalBeratPenjualan       = 0.0;
$penjualanBulanIniCount    = 0;
$penjualanBulanIniNominal  = 0;
$currentYearMonth          = date('Y-m');

$resPenjualan = $conn->query("
    SELECT j.id, j.kode_transaksi, j.anggota_id,
           COALESCE(a.nama_petani, j.nama_pembeli) AS nama_pembeli,
           COALESCE(NULLIF(a.alamat, ''), NULLIF(j.alamat_pembeli, ''), '-') AS alamat_pembeli,
           j.nama_pupuk, j.jumlah_karung, j.jumlah_berat,
           j.total_harga, j.metode_bayar, j.tanggal_jual,
           j.keterangan, j.dicatat_oleh, j.created_at
    FROM penjualan_pupuk j
    LEFT JOIN anggota_petani a ON j.anggota_id = a.id
    ORDER BY j.tanggal_jual DESC, j.id DESC
");
if ($resPenjualan) {
    while ($row = $resPenjualan->fetch_assoc()) {
        $pid = (int)$row['id'];
        $row['nama_pembeli']   = formatTitleCase($row['nama_pembeli']);
        $row['alamat_pembeli'] = formatTitleCase($row['alamat_pembeli'] ?: '-');
        $row['dicatat_oleh']   = formatTitleCase($row['dicatat_oleh'] ?: '-');
        $row['keterangan']     = formatTitleCase($row['keterangan'] ?: '-');

        $rowItems = $penjualanItemsGrouped[$pid] ?? [];
        $itemNames = [];
        $kNum      = 0;
        $bNum      = 0.0;
        $sumSub    = 0;
        foreach ($rowItems as $it) {
            if (!in_array($it['nama_pupuk'], $itemNames, true)) {
                $itemNames[] = $it['nama_pupuk'];
            }
            $kNum   += (int)$it['karung_num'];
            $bNum   += (float)$it['berat_num'];
            $sumSub += (int)$it['subtotal_num'];
        }

        if (!empty($rowItems)) {
            $row['nama_pupuk']    = implode(', ', $itemNames);
            $row['jumlah_karung'] = formatKarungStr($kNum);
            $row['jumlah_berat']  = formatBeratStr($bNum);
        } else {
            $kNum = parseKarungInt($row['jumlah_karung']);
            $bNum = parseBeratFloat($row['jumlah_berat']);
            $row['nama_pupuk']    = formatTitleCase($row['nama_pupuk']);
            $row['jumlah_karung'] = formatKarungStr($kNum);
            $row['jumlah_berat']  = formatBeratStr($bNum);
        }

        $totalInt = (int)$row['total_harga'] > 0 ? (int)$row['total_harga'] : $sumSub;
        $row['items']           = $rowItems;
        $row['karung_num']      = $kNum;
        $row['berat_num']       = $bNum;
        $row['total_harga_num'] = $totalInt;
        $row['total_harga_str'] = formatRupiahStr($totalInt);
        $row['tanggal_jual']    = !empty($row['tanggal_jual']) ? $row['tanggal_jual'] : substr((string)$row['created_at'], 0, 10);

        $penjualanList[] = $row;
        $penjualanInfoMap[$pid] = $row;

        $totalPendapatanPenjualan += $totalInt;
        $totalKarungPenjualan     += $kNum;
        $totalBeratPenjualan      += $bNum;
        if (strpos((string)$row['tanggal_jual'], $currentYearMonth) === 0) {
            $penjualanBulanIniCount++;
            $penjualanBulanIniNominal += $totalInt;
        }
    }
}

// 7. Riwayat Penjualan Pupuk (Log aktivitas Terjual / Diperbarui / Dibatalkan)
$riwayatPenjualanList = [];
$countLogTerjual      = 0;
$countLogDiperbarui   = 0;
$countLogDibatalkan   = 0;

$resRiwayatJual = $conn->query("
    SELECT r.id, r.penjualan_id, r.kode_transaksi, r.aksi,
           r.nama_pembeli, r.rincian_item, r.total_karung, r.total_berat,
           r.total_harga, r.metode_bayar, r.tanggal_jual,
           r.dicatat_oleh, r.keterangan, r.created_at
    FROM riwayat_penjualan_pupuk r
    ORDER BY r.id DESC
");
if ($resRiwayatJual) {
    while ($row = $resRiwayatJual->fetch_assoc()) {
        $row['nama_pembeli']    = formatTitleCase($row['nama_pembeli']);
        $row['dicatat_oleh']    = formatTitleCase($row['dicatat_oleh'] ?: '-');
        $row['keterangan']      = formatTitleCase($row['keterangan'] ?: '-');
        $row['total_harga_num'] = (int)$row['total_harga'];
        $row['total_harga_str'] = formatRupiahStr((int)$row['total_harga']);
        $row['tanggal_jual']    = !empty($row['tanggal_jual']) ? $row['tanggal_jual'] : substr((string)$row['created_at'], 0, 10);

        $riwayatPenjualanList[] = $row;

        if ($row['aksi'] === 'Terjual') {
            $countLogTerjual++;
        } elseif ($row['aksi'] === 'Diperbarui') {
            $countLogDiperbarui++;
        } elseif ($row['aksi'] === 'Dibatalkan') {
            $countLogDibatalkan++;
        }
    }
}

// 8. Riwayat Pengelolaan Stok Pupuk (Seluruh perubahan stok gudang)
$riwayatStokList      = [];
$countStokMasuk       = 0;
$countStokKeluar      = 0;
$countStokEditHapus   = 0;
$totalKarungMasukLog  = 0;
$totalKarungKeluarLog = 0;

$resRiwayatStok = $conn->query("
    SELECT r.id, r.pengelolaan_pupuk_id, r.pupuk_id,
           COALESCE(m.nama_produk, p.nama_pupuk, r.nama_pupuk) AS nama_pupuk,
           r.sumber, r.aksi,
           r.karung_sebelum, r.berat_sebelum,
           r.perubahan_karung, r.perubahan_berat,
           r.karung_sesudah, r.berat_sesudah,
           r.keterangan, r.dicatat_oleh, r.created_at
    FROM riwayat_stok_pupuk r
    LEFT JOIN pengelolaan_pupuk p ON r.pengelolaan_pupuk_id = p.id
    LEFT JOIN master_produk m ON COALESCE(p.pupuk_id, r.pupuk_id) = m.id AND m.jenis = 'Pupuk'
    ORDER BY r.id DESC
");
if ($resRiwayatStok) {
    while ($row = $resRiwayatStok->fetch_assoc()) {
        $row['nama_pupuk']   = formatTitleCase($row['nama_pupuk']);
        $row['dicatat_oleh'] = formatTitleCase($row['dicatat_oleh'] ?: '-');
        $row['keterangan']   = formatTitleCase($row['keterangan'] ?: '-');

        $deltaSign = 0;
        if (strpos((string)$row['perubahan_karung'], '+') === 0) {
            $deltaSign = 1;
        } elseif (strpos((string)$row['perubahan_karung'], '-') === 0) {
            $deltaSign = -1;
        }
        $deltaNum = parseKarungInt($row['perubahan_karung']);
        $row['delta_sign'] = $deltaSign;

        if ($row['aksi'] === 'Edit Stok' || $row['aksi'] === 'Hapus Stok') {
            $countStokEditHapus++;
        }
        if ($deltaSign > 0) {
            $countStokMasuk++;
            $totalKarungMasukLog += $deltaNum;
        } elseif ($deltaSign < 0) {
            $countStokKeluar++;
            $totalKarungKeluarLog += $deltaNum;
        }

        $riwayatStokList[] = $row;
    }
}
