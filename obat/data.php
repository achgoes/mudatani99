<?php
/**
 * mudatani99 - Data Loader & Flash/Toast Message Obat
 * File: obat/data.php
 */

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $pName    = htmlspecialchars($_GET['name'] ?? 'Obat');
    $rKemasan = htmlspecialchars($_GET['kemasan'] ?? '');
    $rStatus  = htmlspecialchars($_GET['st'] ?? '');

    if ($_GET['status'] === 'added') {
        $toastTitle = "Berhasil Ditambahkan!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok obat baru <b>$pName</b> berhasil disimpan.";
        } elseif ($activeView === 'penjualan' || $activeView === 'riwayat_penjualan') {
            $toastMessage = "Transaksi penjualan obat untuk <b>$pName</b> disimpan &amp; Stok Obat otomatis dikurangi.";
        } else {
            $toastMessage = "Data hutang obat petani <b>$pName</b> disimpan &amp; data Stok Obat otomatis dikurangi.";
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'stock_added') {
        $toastTitle   = "Stok Berhasil Ditambahkan!";
        $addK         = htmlspecialchars($_GET['add_k'] ?? '');
        $satuanLbl    = htmlspecialchars($_GET['satuan'] ?? 'Kemasan');
        $totK         = htmlspecialchars($_GET['tot_k'] ?? '');
        $totB         = htmlspecialchars($_GET['tot_b'] ?? '');
        $extraDetail  = ($addK !== '' && $totK !== '')
            ? " bertambah <b>+$addK $satuanLbl</b>. Total stok saat ini: <b>$totK $satuanLbl ($totB)</b>."
            : " berhasil diakumulasikan ke stok yang sudah ada.";
        $toastMessage = "Stok obat <b>$pName</b>" . $extraDetail;
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle = "Berhasil Diperbarui!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok obat <b>$pName</b> berhasil diperbarui.";
        } elseif ($activeView === 'penjualan' || $activeView === 'riwayat_penjualan') {
            $toastMessage = "Transaksi penjualan obat untuk <b>$pName</b> diperbarui &amp; Stok Obat otomatis disinkronkan.";
        } else {
            $toastMessage = "Data hutang obat petani <b>$pName</b> diperbarui &amp; data Stok Obat otomatis disinkronkan.";
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle = "Berhasil Dihapus!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok obat berhasil dihapus.";
        } elseif ($activeView === 'penjualan' || $activeView === 'riwayat_penjualan') {
            $extraInfo = $rKemasan !== '' ? " Stok <b>$rKemasan</b> telah dikembalikan ke Stok Obat." : "";
            $toastMessage = "Transaksi penjualan obat berhasil dihapus." . $extraInfo;
        } else {
            $extraInfo = $rKemasan !== '' ? " Stok <b>$rKemasan</b> telah dikembalikan ke Stok Obat." : "";
            $toastMessage = "Data hutang piutang berhasil dihapus." . $extraInfo;
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'status_changed') {
        $toastTitle   = "Status Diperbarui!";
        $toastMessage = "Status hutang obat <b>$pName</b> berhasil diubah menjadi <b>$rStatus</b>.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'paid') {
        $toastTitle   = "Pembayaran Berhasil!";
        $nominal      = htmlspecialchars($_GET['nominal'] ?? '');
        $nominalText  = ($nominal !== '') ? " sebesar <b>$nominal</b>" : "";
        $paidStatus   = ($rStatus !== '') ? $rStatus : 'Lunas';
        $toastMessage = "Pembayaran hutang obat petani <b>$pName</b>" . $nominalText . " telah berhasil dicatat &amp; status kini <b>$paidStatus</b>.";
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

// 2. Daftar Master Obat (khusus jenis = 'Obat' dari tabel master_produk)
$masterObatList = [];
$resMaster = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Obat' ORDER BY id ASC");
if ($resMaster) {
    while ($row = $resMaster->fetch_assoc()) {
        $row['nama_produk'] = formatTitleCase($row['nama_produk']);
        $row['keterangan']  = formatTitleCase($row['keterangan']);
        $masterObatList[] = $row;
    }
}

// 3. Daftar Stok Obat
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

        $kemasanNum = parseKemasanInt($row['jumlah_kemasan']);
        $satuan     = parseSatuanKemasan($row['jumlah_kemasan'], 'Pcs');
        $beratNum   = parseBeratFloat($row['jumlah_berat']);
        $row['kemasan_num']       = $kemasanNum;
        $row['satuan_kemasan']    = $satuan;
        $row['berat_num']         = $beratNum;
        $row['berat_per_kemasan'] = ($kemasanNum > 0) ? round($beratNum / $kemasanNum, 2) : 1;

        $obatList[] = $row;

        $totalKemasan += $kemasanNum;
        $totalBerat   += $beratNum;
    }
}

$stokMapByObatId    = [];
$stokMapByNameLower = [];
foreach ($obatList as $ob) {
    if (!empty($ob['obat_id'])) {
        $stokMapByObatId[(int)$ob['obat_id']] = $ob;
    }
    $cleanNameLower = mb_strtolower(trim($ob['nama_obat']));
    if ($cleanNameLower !== '') {
        $stokMapByNameLower[$cleanNameLower] = $ob;
    }
}

// 4. Daftar Hutang Piutang Obat
$hutangItemsGrouped = getAllHutangObatItemsGrouped($conn);
$hutangList         = [];
$totalKemasanHutang = 0;
$totalBeratHutang   = 0.0;
$countLunas         = 0;
$countBelumLunas    = 0;
$kemasanLunas       = 0;
$kemasanBelumLunas  = 0;

$resHutang = $conn->query("
    SELECT h.id, h.anggota_id,
           COALESCE(a.nama_petani, h.nama_petani) AS nama_petani,
           a.alamat AS alamat_petani,
           h.pengelolaan_obat_id, h.obat_id,
           COALESCE(m.nama_produk, o.nama_obat, h.nama_obat) AS nama_obat,
           h.jumlah_kemasan, h.jumlah_berat,
           COALESCE(h.harga, 'Rp 0') AS harga,
           COALESCE(h.jumlah_hutang, 'Rp 0') AS jumlah_hutang,
           COALESCE(h.sisa_hutang, 'Rp 0') AS sisa_hutang,
           h.keterangan,
           h.tanggal,
           h.status, h.tanggal_lunas, h.created_at
    FROM hutang_obat h
    LEFT JOIN anggota_petani a ON h.anggota_id = a.id
    LEFT JOIN pengelolaan_obat o ON h.pengelolaan_obat_id = o.id
    LEFT JOIN master_produk m ON COALESCE(o.obat_id, h.obat_id) = m.id AND m.jenis = 'Obat'
    ORDER BY h.id ASC
");
if ($resHutang) {
    while ($row = $resHutang->fetch_assoc()) {
        $hid = (int)$row['id'];
        $row['nama_petani'] = formatTitleCase($row['nama_petani']);

        $rowItems = $hutangItemsGrouped[$hid] ?? [];
        if (empty($rowItems)) {
            $rowItems = [normalizeHutangObatItemRow($row)];
        }

        $itemNames   = [];
        $kNum        = 0;
        $bNum        = 0.0;
        $sumSubtotal = 0.0;
        foreach ($rowItems as $it) {
            if (!in_array($it['nama_obat'], $itemNames, true)) {
                $itemNames[] = $it['nama_obat'];
            }
            $kNum        += (int)$it['kemasan_num'];
            $bNum        += (float)$it['berat_num'];
            $sumSubtotal += (float)$it['subtotal_num'];
        }
        $summarySatuan = resolveSummarySatuanKemasan($rowItems, 'Kemasan');

        $row['items']          = $rowItems;
        $row['nama_obat']      = implode(', ', $itemNames);
        $row['jumlah_kemasan'] = formatKemasanStr($kNum, $summarySatuan);
        $row['jumlah_berat']   = formatBeratStr($bNum);
        $row['harga']          = (count($rowItems) === 1) ? $rowItems[0]['harga'] : 'Rp 0';
        $row['kemasan_num']    = $kNum;
        $row['berat_num']      = $bNum;

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

        $row['keterangan'] = stripHutangObatPaymentNote(formatTitleCase($row['keterangan']));
        $row['tanggal']    = !empty($row['tanggal']) ? $row['tanggal'] : substr($row['created_at'], 0, 10);

        $hutangList[] = $row;

        $totalKemasanHutang += $kNum;
        $totalBeratHutang   += $bNum;

        if ($row['status'] === 'Lunas') {
            $countLunas++;
            $kemasanLunas += $kNum;
        } else {
            $countBelumLunas++;
            $kemasanBelumLunas += $kNum;
        }
    }
}

// 5. Riwayat Pembayaran Hutang Obat
$pembayaranList     = [];
$pembayaranByHutang = [];
$totalTerbayar      = 0;
$countPelunasan     = 0;
$petaniPembayarIds  = [];

$resBayar = $conn->query("
    SELECT b.id, b.hutang_id, b.anggota_id,
           COALESCE(a.nama_petani, b.nama_petani) AS nama_petani,
           COALESCE(m.nama_produk, o.nama_obat, h.nama_obat, b.nama_obat) AS nama_obat,
           h.jumlah_kemasan, h.jumlah_berat,
           b.pembayaran_ke, b.tanggal_bayar,
           b.total_hutang, b.sisa_sebelum, b.jumlah_bayar, b.sisa_hutang,
           b.metode_bayar, b.status, b.keterangan, b.dicatat_oleh, b.created_at
    FROM pembayaran_hutang_obat b
    LEFT JOIN hutang_obat h ON b.hutang_id = h.id
    LEFT JOIN anggota_petani a ON b.anggota_id = a.id
    LEFT JOIN pengelolaan_obat o ON h.pengelolaan_obat_id = o.id
    LEFT JOIN master_produk m ON COALESCE(o.obat_id, h.obat_id) = m.id AND m.jenis = 'Obat'
    ORDER BY b.tanggal_bayar DESC, b.id DESC
");
if ($resBayar) {
    while ($row = $resBayar->fetch_assoc()) {
        $row['nama_petani']      = formatTitleCase($row['nama_petani']);
        $row['nama_obat']        = formatTitleCase($row['nama_obat']);
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

// 6. Daftar Penjualan Obat (Berdiri sendiri, terpisah dari Hutang Piutang)
$penjualanItemsGrouped    = getAllPenjualanObatItemsGrouped($conn);
$penjualanList            = [];
$penjualanInfoMap         = [];
$totalPendapatanPenjualan = 0;
$totalKemasanPenjualan    = 0;
$totalBeratPenjualan      = 0.0;
$penjualanBulanIniCount   = 0;
$penjualanBulanIniNominal = 0;
$currentYearMonth         = date('Y-m');

$resPenjualan = $conn->query("
    SELECT j.id, j.kode_transaksi, j.anggota_id,
           COALESCE(a.nama_petani, j.nama_pembeli) AS nama_pembeli,
           COALESCE(NULLIF(a.alamat, ''), NULLIF(j.alamat_pembeli, ''), '-') AS alamat_pembeli,
           j.nama_obat, j.jumlah_kemasan, j.jumlah_berat,
           j.total_harga, j.metode_bayar, j.tanggal_jual,
           j.keterangan, j.dicatat_oleh, j.created_at
    FROM penjualan_obat j
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

        $rowItems  = $penjualanItemsGrouped[$pid] ?? [];
        $itemNames = [];
        $kNum      = 0;
        $bNum      = 0.0;
        $sumSub    = 0;
        foreach ($rowItems as $it) {
            if (!in_array($it['nama_obat'], $itemNames, true)) {
                $itemNames[] = $it['nama_obat'];
            }
            $kNum   += (int)$it['kemasan_num'];
            $bNum   += (float)$it['berat_num'];
            $sumSub += (int)$it['subtotal_num'];
        }

        if (!empty($rowItems)) {
            $summarySatuan         = resolveSummarySatuanKemasan($rowItems, 'Kemasan');
            $row['nama_obat']      = implode(', ', $itemNames);
            $row['jumlah_kemasan'] = formatKemasanStr($kNum, $summarySatuan);
            $row['jumlah_berat']   = formatBeratStr($bNum);
        } else {
            $kNum                  = parseKemasanInt($row['jumlah_kemasan']);
            $satuan                = parseSatuanKemasan($row['jumlah_kemasan'], 'Kemasan');
            $bNum                  = parseBeratFloat($row['jumlah_berat']);
            $row['nama_obat']      = formatTitleCase($row['nama_obat']);
            $row['jumlah_kemasan'] = formatKemasanStr($kNum, $satuan);
            $row['jumlah_berat']   = formatBeratStr($bNum);
        }

        $totalInt = (int)$row['total_harga'] > 0 ? (int)$row['total_harga'] : $sumSub;
        $row['items']           = $rowItems;
        $row['kemasan_num']     = $kNum;
        $row['berat_num']       = $bNum;
        $row['total_harga_num'] = $totalInt;
        $row['total_harga_str'] = formatRupiahStr($totalInt);
        $row['tanggal_jual']    = !empty($row['tanggal_jual']) ? $row['tanggal_jual'] : substr((string)$row['created_at'], 0, 10);

        $penjualanList[] = $row;
        $penjualanInfoMap[$pid] = $row;

        $totalPendapatanPenjualan += $totalInt;
        $totalKemasanPenjualan    += $kNum;
        $totalBeratPenjualan      += $bNum;
        if (strpos((string)$row['tanggal_jual'], $currentYearMonth) === 0) {
            $penjualanBulanIniCount++;
            $penjualanBulanIniNominal += $totalInt;
        }
    }
}

// 7. Riwayat Penjualan Obat (Log aktivitas Terjual / Diperbarui / Dibatalkan)
$riwayatPenjualanList = [];
$countLogTerjual      = 0;
$countLogDiperbarui   = 0;
$countLogDibatalkan   = 0;

$resRiwayatJual = $conn->query("
    SELECT r.id, r.penjualan_id, r.kode_transaksi, r.aksi,
           r.nama_pembeli, r.rincian_item, r.total_kemasan, r.total_berat,
           r.total_harga, r.metode_bayar, r.tanggal_jual,
           r.dicatat_oleh, r.keterangan, r.created_at
    FROM riwayat_penjualan_obat r
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

// 8. Riwayat Pengelolaan Stok Obat (Seluruh perubahan stok gudang)
$riwayatStokList       = [];
$countStokMasuk        = 0;
$countStokKeluar       = 0;
$countStokEditHapus    = 0;
$totalKemasanMasukLog  = 0;
$totalKemasanKeluarLog = 0;

$resRiwayatStok = $conn->query("
    SELECT r.id, r.pengelolaan_obat_id, r.obat_id,
           COALESCE(m.nama_produk, o.nama_obat, r.nama_obat) AS nama_obat,
           r.sumber, r.aksi,
           r.kemasan_sebelum, r.berat_sebelum,
           r.perubahan_kemasan, r.perubahan_berat,
           r.kemasan_sesudah, r.berat_sesudah,
           r.keterangan, r.dicatat_oleh, r.created_at
    FROM riwayat_stok_obat r
    LEFT JOIN pengelolaan_obat o ON r.pengelolaan_obat_id = o.id
    LEFT JOIN master_produk m ON COALESCE(o.obat_id, r.obat_id) = m.id AND m.jenis = 'Obat'
    ORDER BY r.id DESC
");
if ($resRiwayatStok) {
    while ($row = $resRiwayatStok->fetch_assoc()) {
        $row['nama_obat']    = formatTitleCase($row['nama_obat']);
        $row['dicatat_oleh'] = formatTitleCase($row['dicatat_oleh'] ?: '-');
        $row['keterangan']   = formatTitleCase($row['keterangan'] ?: '-');

        $deltaSign = 0;
        if (strpos((string)$row['perubahan_kemasan'], '+') === 0) {
            $deltaSign = 1;
        } elseif (strpos((string)$row['perubahan_kemasan'], '-') === 0) {
            $deltaSign = -1;
        }
        $deltaNum = parseKemasanInt($row['perubahan_kemasan']);
        $row['delta_sign'] = $deltaSign;

        if ($row['aksi'] === 'Edit Stok' || $row['aksi'] === 'Hapus Stok') {
            $countStokEditHapus++;
        }
        if ($deltaSign > 0) {
            $countStokMasuk++;
            $totalKemasanMasukLog += $deltaNum;
        } elseif ($deltaSign < 0) {
            $countStokKeluar++;
            $totalKemasanKeluarLog += $deltaNum;
        }

        $riwayatStokList[] = $row;
    }
}
