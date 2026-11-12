<?php
/**
 * mudatani99 - Data Loader & Flash/Toast Message Tanaman
 * File: tanaman/data.php
 */

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    $pName    = htmlspecialchars($_GET['name'] ?? 'Tanaman');
    $rKemasan = htmlspecialchars($_GET['kemasan'] ?? '');
    $rStatus  = htmlspecialchars($_GET['st'] ?? '');

    if ($_GET['status'] === 'added') {
        $toastTitle = "Berhasil Ditambahkan!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok tanaman baru <b>$pName</b> berhasil disimpan.";
        } elseif ($activeView === 'penjualan' || $activeView === 'riwayat_penjualan') {
            $toastMessage = "Transaksi penjualan tanaman untuk <b>$pName</b> disimpan &amp; Stok Tanaman otomatis dikurangi.";
        } else {
            $toastMessage = "Data hutang tanaman petani <b>$pName</b> disimpan &amp; data Stok Tanaman otomatis dikurangi.";
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'stock_added') {
        $toastTitle   = "Stok Berhasil Ditambahkan!";
        $addK         = htmlspecialchars($_GET['add_k'] ?? '');
        $totK         = htmlspecialchars($_GET['tot_k'] ?? '');
        $totB         = htmlspecialchars($_GET['tot_b'] ?? '');
        $extraDetail  = ($addK !== '' && $totK !== '')
            ? " bertambah <b>+$addK Pcs</b>. Total stok saat ini: <b>$totK Pcs ($totB)</b>."
            : " berhasil diakumulasikan ke stok yang sudah ada.";
        $toastMessage = "Stok tanaman <b>$pName</b>" . $extraDetail;
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle = "Berhasil Diperbarui!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok tanaman <b>$pName</b> berhasil diperbarui.";
        } elseif ($activeView === 'penjualan' || $activeView === 'riwayat_penjualan') {
            $toastMessage = "Transaksi penjualan tanaman untuk <b>$pName</b> diperbarui &amp; Stok Tanaman otomatis disinkronkan.";
        } else {
            $toastMessage = "Data hutang tanaman petani <b>$pName</b> diperbarui &amp; data Stok Tanaman otomatis disinkronkan.";
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle = "Berhasil Dihapus!";
        if ($activeView === 'kelola') {
            $toastMessage = "Data stok tanaman berhasil dihapus.";
        } elseif ($activeView === 'penjualan' || $activeView === 'riwayat_penjualan') {
            $extraInfo = $rKemasan !== '' ? " Stok <b>$rKemasan</b> telah dikembalikan ke Stok Tanaman." : "";
            $toastMessage = "Transaksi penjualan tanaman berhasil dihapus." . $extraInfo;
        } else {
            $extraInfo = $rKemasan !== '' ? " Stok <b>$rKemasan</b> telah dikembalikan ke Stok Tanaman." : "";
            $toastMessage = "Data hutang piutang berhasil dihapus." . $extraInfo;
        }
        $toastType = "success";
    } elseif ($_GET['status'] === 'status_changed') {
        $toastTitle   = "Status Diperbarui!";
        $toastMessage = "Status hutang tanaman <b>$pName</b> berhasil diubah menjadi <b>$rStatus</b>.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'paid') {
        $toastTitle   = "Pembayaran Berhasil!";
        $nominal      = htmlspecialchars($_GET['nominal'] ?? '');
        $nominalText  = ($nominal !== '') ? " sebesar <b>$nominal</b>" : "";
        $paidStatus   = ($rStatus !== '') ? $rStatus : 'Lunas';
        $toastMessage = "Pembayaran hutang tanaman petani <b>$pName</b>" . $nominalText . " telah berhasil dicatat &amp; status kini <b>$paidStatus</b>.";
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

// 2. Daftar Master Tanaman (khusus jenis = 'Tanaman' dari tabel master_produk)
$masterTanamanList = [];
$resMaster = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Tanaman' ORDER BY id ASC");
if ($resMaster) {
    while ($row = $resMaster->fetch_assoc()) {
        $row['nama_produk'] = formatTitleCase($row['nama_produk']);
        $row['keterangan']  = formatTitleCase($row['keterangan']);
        $masterTanamanList[] = $row;
    }
}

// 3. Daftar Stok Tanaman
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

        $kemasanNum = parseKemasanInt($row['jumlah_kemasan']);
        $beratNum   = parseBeratFloat($row['jumlah_berat']);
        $row['kemasan_num']       = $kemasanNum;
        $row['berat_num']         = $beratNum;
        $row['berat_per_kemasan'] = ($kemasanNum > 0) ? round($beratNum / $kemasanNum, 2) : 10;

        $tanamanList[] = $row;

        $totalKemasan += $kemasanNum;
        $totalBerat   += $beratNum;
    }
}

$stokMapByTanamanId = [];
$stokMapByNameLower = [];
foreach ($tanamanList as $tn) {
    if (!empty($tn['tanaman_id'])) {
        $stokMapByTanamanId[(int)$tn['tanaman_id']] = $tn;
    }
    $cleanNameLower = mb_strtolower(trim($tn['nama_tanaman']));
    if ($cleanNameLower !== '') {
        $stokMapByNameLower[$cleanNameLower] = $tn;
    }
}

// 4. Daftar Hutang Piutang Tanaman
$hutangItemsGrouped = getAllHutangTanamanItemsGrouped($conn);
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
           h.pengelolaan_tanaman_id, h.tanaman_id,
           COALESCE(m.nama_produk, t.nama_tanaman, h.nama_tanaman) AS nama_tanaman,
           h.jumlah_kemasan, h.jumlah_berat,
           COALESCE(h.harga, 'Rp 0') AS harga,
           COALESCE(h.jumlah_hutang, 'Rp 0') AS jumlah_hutang,
           COALESCE(h.sisa_hutang, 'Rp 0') AS sisa_hutang,
           h.keterangan,
           h.tanggal,
           h.status, h.tanggal_lunas, h.created_at
    FROM hutang_tanaman h
    LEFT JOIN anggota_petani a ON h.anggota_id = a.id
    LEFT JOIN pengelolaan_tanaman t ON h.pengelolaan_tanaman_id = t.id
    LEFT JOIN master_produk m ON COALESCE(t.tanaman_id, h.tanaman_id) = m.id AND m.jenis = 'Tanaman'
    ORDER BY h.id ASC
");
if ($resHutang) {
    while ($row = $resHutang->fetch_assoc()) {
        $hid = (int)$row['id'];
        $row['nama_petani'] = formatTitleCase($row['nama_petani']);

        $rowItems = $hutangItemsGrouped[$hid] ?? [];
        if (empty($rowItems)) {
            $rowItems = [normalizeHutangTanamanItemRow($row)];
        }

        $itemNames   = [];
        $kNum        = 0;
        $bNum        = 0.0;
        $sumSubtotal = 0.0;
        foreach ($rowItems as $it) {
            if (!in_array($it['nama_tanaman'], $itemNames, true)) {
                $itemNames[] = $it['nama_tanaman'];
            }
            $kNum        += (int)$it['kemasan_num'];
            $bNum        += (float)$it['berat_num'];
            $sumSubtotal += (float)$it['subtotal_num'];
        }

        $row['items']          = $rowItems;
        $row['nama_tanaman']   = implode(', ', $itemNames);
        $row['jumlah_kemasan'] = formatKemasanStr($kNum);
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

        $row['keterangan'] = stripHutangTanamanPaymentNote(formatTitleCase($row['keterangan']));
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

// 5. Riwayat Pembayaran Hutang Tanaman
$pembayaranList     = [];
$pembayaranByHutang = [];
$totalTerbayar      = 0;
$countPelunasan     = 0;
$petaniPembayarIds  = [];

$resBayar = $conn->query("
    SELECT b.id, b.hutang_id, b.anggota_id,
           COALESCE(a.nama_petani, b.nama_petani) AS nama_petani,
           COALESCE(m.nama_produk, t.nama_tanaman, h.nama_tanaman, b.nama_tanaman) AS nama_tanaman,
           h.jumlah_kemasan, h.jumlah_berat,
           b.pembayaran_ke, b.tanggal_bayar,
           b.total_hutang, b.sisa_sebelum, b.jumlah_bayar, b.sisa_hutang,
           b.metode_bayar, b.status, b.keterangan, b.dicatat_oleh, b.created_at
    FROM pembayaran_hutang_tanaman b
    LEFT JOIN hutang_tanaman h ON b.hutang_id = h.id
    LEFT JOIN anggota_petani a ON b.anggota_id = a.id
    LEFT JOIN pengelolaan_tanaman t ON h.pengelolaan_tanaman_id = t.id
    LEFT JOIN master_produk m ON COALESCE(t.tanaman_id, h.tanaman_id) = m.id AND m.jenis = 'Tanaman'
    ORDER BY b.tanggal_bayar DESC, b.id DESC
");
if ($resBayar) {
    while ($row = $resBayar->fetch_assoc()) {
        $row['nama_petani']      = formatTitleCase($row['nama_petani']);
        $row['nama_tanaman']     = formatTitleCase($row['nama_tanaman']);
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

// 6. Daftar Penjualan Tanaman (Berdiri sendiri, terpisah dari Hutang Piutang)
$penjualanItemsGrouped    = getAllPenjualanTanamanItemsGrouped($conn);
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
           j.nama_tanaman, j.jumlah_kemasan, j.jumlah_berat,
           j.total_harga, j.metode_bayar, j.tanggal_jual,
           j.keterangan, j.dicatat_oleh, j.created_at
    FROM penjualan_tanaman j
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
            if (!in_array($it['nama_tanaman'], $itemNames, true)) {
                $itemNames[] = $it['nama_tanaman'];
            }
            $kNum   += (int)$it['kemasan_num'];
            $bNum   += (float)$it['berat_num'];
            $sumSub += (int)$it['subtotal_num'];
        }

        if (!empty($rowItems)) {
            $row['nama_tanaman']   = implode(', ', $itemNames);
            $row['jumlah_kemasan'] = formatKemasanStr($kNum);
            $row['jumlah_berat']   = formatBeratStr($bNum);
        } else {
            $kNum                  = parseKemasanInt($row['jumlah_kemasan']);
            $bNum                  = parseBeratFloat($row['jumlah_berat']);
            $row['nama_tanaman']   = formatTitleCase($row['nama_tanaman']);
            $row['jumlah_kemasan'] = formatKemasanStr($kNum);
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

// 7. Riwayat Penjualan Tanaman (Log aktivitas Terjual / Diperbarui / Dibatalkan)
$riwayatPenjualanList = [];
$countLogTerjual      = 0;
$countLogDiperbarui   = 0;
$countLogDibatalkan   = 0;

$resRiwayatJual = $conn->query("
    SELECT r.id, r.penjualan_id, r.kode_transaksi, r.aksi,
           r.nama_pembeli, r.rincian_item, r.total_kemasan, r.total_berat,
           r.total_harga, r.metode_bayar, r.tanggal_jual,
           r.dicatat_oleh, r.keterangan, r.created_at
    FROM riwayat_penjualan_tanaman r
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

// 8. Riwayat Pengelolaan Stok Tanaman (Seluruh perubahan stok gudang)
$riwayatStokList       = [];
$countStokMasuk        = 0;
$countStokKeluar       = 0;
$countStokEditHapus    = 0;
$totalKemasanMasukLog  = 0;
$totalKemasanKeluarLog = 0;

$resRiwayatStok = $conn->query("
    SELECT r.id, r.pengelolaan_tanaman_id, r.tanaman_id,
           COALESCE(m.nama_produk, t.nama_tanaman, r.nama_tanaman) AS nama_tanaman,
           r.sumber, r.aksi,
           r.kemasan_sebelum, r.berat_sebelum,
           r.perubahan_kemasan, r.perubahan_berat,
           r.kemasan_sesudah, r.berat_sesudah,
           r.keterangan, r.dicatat_oleh, r.created_at
    FROM riwayat_stok_tanaman r
    LEFT JOIN pengelolaan_tanaman t ON r.pengelolaan_tanaman_id = t.id
    LEFT JOIN master_produk m ON COALESCE(t.tanaman_id, r.tanaman_id) = m.id AND m.jenis = 'Tanaman'
    ORDER BY r.id DESC
");
if ($resRiwayatStok) {
    while ($row = $resRiwayatStok->fetch_assoc()) {
        $row['nama_tanaman'] = formatTitleCase($row['nama_tanaman']);
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
