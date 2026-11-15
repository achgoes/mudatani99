<?php
/**
 * mudatani99 - Data Loader & Flash Toast Manajemen Transaksi Akhir
 * File: transaksi-akhir/data.php
 */

$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

if (isset($_GET['status'])) {
    $pName  = htmlspecialchars($_GET['name'] ?? 'Petani');
    $pBerat = htmlspecialchars($_GET['berat'] ?? '');

    if ($_GET['status'] === 'added') {
        $toastTitle   = "Transaksi Akhir Disimpan!";
        $beratInfo    = ($pBerat !== '') ? " dengan berat final <b>$pBerat</b>" : "";
        $toastMessage = "Data transaksi akhir atas nama <b>$pName</b>" . $beratInfo . " berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $toastTitle   = "Transaksi Akhir Diperbarui!";
        $beratInfo    = ($pBerat !== '') ? " dengan berat final <b>$pBerat</b>" : "";
        $toastMessage = "Data transaksi akhir atas nama <b>$pName</b>" . $beratInfo . " berhasil diperbarui.";
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

// 1. Rekap Setoran Petani per anggota_id (sumber Jumlah Setoran, berat per tanaman & rincian nota setoran)
$rekapSetoranPerAnggota = getRekapSetoranPerAnggota($conn);

// 2. Daftar Anggota Petani (sumber dropdown Nama Petani beserta total setoran masing-masing)
$anggotaList = [];
$resAnggota  = $conn->query("SELECT id, nama_petani, alamat, keterangan FROM anggota_petani ORDER BY id ASC");
if ($resAnggota) {
    while ($row = $resAnggota->fetch_assoc()) {
        $aid                      = (int)$row['id'];
        $rekap                    = $rekapSetoranPerAnggota[$aid] ?? null;
        $row['nama_petani']       = formatTitleCaseSetoran($row['nama_petani']);
        $row['alamat']            = formatTitleCaseSetoran($row['alamat']);
        $row['total_setoran_num']  = $rekap ? (float)$rekap['total_berat'] : 0.0;
        $row['total_setoran_str']  = $rekap ? (string)$rekap['total_berat_str'] : '0 Kg';
        $row['jumlah_nota']        = $rekap ? (int)$rekap['jumlah_nota'] : 0;
        $row['berat_per_tanaman']  = $rekap ? ($rekap['berat_per_tanaman'] ?? []) : [];
        $anggotaList[]             = $row;
    }
}

// 3. Daftar Master Tanaman (sumber dropdown Tanaman dari master_produk jenis = 'Tanaman')
$masterTanamanList = [];
$resMasterTanaman  = $conn->query("SELECT id, nama_produk, keterangan FROM master_produk WHERE jenis = 'Tanaman' ORDER BY id ASC");
if ($resMasterTanaman) {
    while ($row = $resMasterTanaman->fetch_assoc()) {
        $row['id']          = (int)$row['id'];
        $row['nama_produk'] = formatTitleCaseSetoran($row['nama_produk']);
        $row['keterangan']  = formatTitleCaseSetoran($row['keterangan']);
        $masterTanamanList[] = $row;
    }
}

// 4. Daftar Transaksi Akhir
$transaksiList            = [];
$transaksiInfoMap         = [];
$totalJumlahSetoran       = 0.0;
$totalRapaksi             = 0.0;
$totalBeratFinal          = 0.0;
$totalUangTransaksiAkhir  = 0;
$petaniTransaksiIds       = [];

$resTransaksi = $conn->query("
    SELECT t.id, t.kode_transaksi, t.anggota_id,
           COALESCE(a.nama_petani, t.nama_petani) AS nama_petani,
           COALESCE(NULLIF(a.alamat, ''), '-') AS alamat_petani,
           t.tanaman_id,
           COALESCE(m.nama_produk, NULLIF(t.nama_tanaman, ''), '-') AS nama_tanaman,
           t.jumlah_setoran, t.rapaksi, t.berat_final, t.harga_per_kg, t.jumlah_uang,
           t.tanggal, t.keterangan, t.dicatat_oleh, t.created_at
    FROM transaksi_akhir t
    LEFT JOIN anggota_petani a ON t.anggota_id = a.id
    LEFT JOIN master_produk m ON t.tanaman_id = m.id AND m.jenis = 'Tanaman'
    ORDER BY t.tanggal DESC, t.id DESC
");

if ($resTransaksi) {
    while ($row = $resTransaksi->fetch_assoc()) {
        $tid       = (int)$row['id'];
        $anggotaId = (int)$row['anggota_id'];
        $tanamanId = !empty($row['tanaman_id']) ? (int)$row['tanaman_id'] : null;

        $jumlahSetoranNum = round(parseBeratSetoranFloat($row['jumlah_setoran']), 2);
        $rapaksiNum       = round(parseBeratSetoranFloat($row['rapaksi']), 2);
        $beratFinalNum    = max(0.0, round($jumlahSetoranNum - $rapaksiNum, 2));
        $hargaPerKgNum    = max(0, (int)($row['harga_per_kg'] ?? 0));
        $jumlahUangRaw    = max(0, (int)($row['jumlah_uang'] ?? 0));
        $jumlahUangNum    = ($jumlahUangRaw <= 0 && $beratFinalNum > 0 && $hargaPerKgNum > 0)
            ? (int)round($beratFinalNum * $hargaPerKgNum)
            : $jumlahUangRaw;

        $rekapAnggota = $rekapSetoranPerAnggota[$anggotaId] ?? null;

        $row['id']                  = $tid;
        $row['anggota_id']          = $anggotaId;
        $row['tanaman_id']          = $tanamanId;
        $row['nama_petani']         = formatTitleCaseSetoran($row['nama_petani']);
        $row['alamat_petani']       = formatTitleCaseSetoran($row['alamat_petani'] ?: '-');
        $row['nama_tanaman']        = formatTitleCaseSetoran($row['nama_tanaman'] ?: '-');
        $row['keterangan']          = formatTitleCaseSetoran($row['keterangan'] ?: '-');
        $row['dicatat_oleh']        = formatTitleCaseSetoran($row['dicatat_oleh'] ?: '-');
        $row['jumlah_setoran_num']  = $jumlahSetoranNum;
        $row['jumlah_setoran']      = formatBeratSetoranStr($jumlahSetoranNum);
        $row['rapaksi_num']         = $rapaksiNum;
        $row['rapaksi']             = formatBeratSetoranStr($rapaksiNum);
        $row['berat_final_num']     = $beratFinalNum;
        $row['berat_final']         = formatBeratSetoranStr($beratFinalNum);
        $row['harga_per_kg_num']    = $hargaPerKgNum;
        $row['harga_per_kg_rupiah'] = formatRupiahTransaksiAkhir($hargaPerKgNum);
        $row['harga_per_kg_fmt']    = formatRupiahTransaksiAkhir($hargaPerKgNum) . ' /Kg';
        $row['jumlah_uang_num']     = $jumlahUangNum;
        $row['jumlah_uang_fmt']     = formatRupiahTransaksiAkhir($jumlahUangNum);
        $row['tanggal']             = !empty($row['tanggal']) ? $row['tanggal'] : substr((string)$row['created_at'], 0, 10);
        $row['live_setoran_num']    = $rekapAnggota ? (float)$rekapAnggota['total_berat'] : $jumlahSetoranNum;
        $row['live_setoran_str']    = $rekapAnggota ? (string)$rekapAnggota['total_berat_str'] : $row['jumlah_setoran'];
        $row['setoran_riwayat']     = $rekapAnggota ? $rekapAnggota['setoran_list'] : [];

        $transaksiList[]        = $row;
        $transaksiInfoMap[$tid] = $row;

        $totalJumlahSetoran      += $jumlahSetoranNum;
        $totalRapaksi            += $rapaksiNum;
        $totalBeratFinal         += $beratFinalNum;
        $totalUangTransaksiAkhir += $jumlahUangNum;
        $petaniTransaksiIds[$anggotaId] = true;
    }
}
