<?php
/**
 * mudatani99 - Pembayaran & Perubahan Status Hutang Pupuk
 * File: pupuk/actions/pembayaran.php
 */

if ($action === 'toggle_status_hutang') {
    $activeView = ($returnView === 'kelola') ? 'hutang' : $returnView;
    $id         = (int)($_POST['id'] ?? 0);
    $newStatus  = $_POST['new_status'] ?? 'Lunas';
    if (!in_array($newStatus, ['Belum Lunas', 'Lunas'], true)) {
        $newStatus = 'Lunas';
    }

    if ($id > 0) {
        $tanggal_lunas = ($newStatus === 'Lunas') ? date('Y-m-d H:i:s') : null;
        if ($newStatus === 'Lunas') {
            $stmt = $conn->prepare("UPDATE hutang_pupuk SET status = ?, sisa_hutang = 'Rp 0', tanggal_lunas = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ssi", $newStatus, $tanggal_lunas, $id);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $stmt = $conn->prepare("UPDATE hutang_pupuk SET status = ?, sisa_hutang = jumlah_hutang, tanggal_lunas = NULL WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $newStatus, $id);
                $stmt->execute();
                $stmt->close();
            }

            // Sisa hutang kembali penuh, jadi jejak pembayaran sebelumnya dianggap batal
            $delBayar = $conn->prepare("DELETE FROM pembayaran_hutang_pupuk WHERE hutang_id = ?");
            if ($delBayar) {
                $delBayar->bind_param("i", $id);
                $delBayar->execute();
                $delBayar->close();
            }
        }
        syncRiwayatHutangRow($conn, $id);

        $qName = $conn->prepare("SELECT nama_petani FROM hutang_pupuk WHERE id = ? LIMIT 1");
        $qName->bind_param("i", $id);
        $qName->execute();
        $rName = $qName->get_result();
        $nRow  = $rName ? $rName->fetch_assoc() : null;
        $qName->close();
        $petaniName = $nRow['nama_petani'] ?? 'Petani';

        header("Location: " . appUrl("pupuk/?{$viewQuery}status=status_changed&name=" . urlencode($petaniName) . "&st=" . urlencode($newStatus)));
        exit;
    }
} elseif ($action === 'pay_hutang') {
    $activeView    = ($returnView === 'kelola') ? 'hutang' : $returnView;
    $id            = (int)($_POST['id'] ?? 0);
    $bayar_nominal = formatRupiahStr($_POST['bayar_nominal'] ?? '');
    $tanggal_bayar = trim($_POST['tanggal_bayar'] ?? '');
    $catatan_bayar = formatTitleCase($_POST['catatan_bayar'] ?? '');
    $metode_bayar  = $_POST['metode_bayar'] ?? 'Tunai';
    if (!in_array($metode_bayar, ['Tunai', 'Transfer', 'Potong Hasil Panen'], true)) {
        $metode_bayar = 'Tunai';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_bayar)) {
        $tanggal_bayar = '';
    }

    if ($id > 0) {
        $tglBayar = (!empty($tanggal_bayar)) ? ($tanggal_bayar . ' ' . date('H:i:s')) : date('Y-m-d H:i:s');

        $qRow = $conn->prepare("SELECT anggota_id, nama_petani, nama_pupuk, keterangan, harga, jumlah_hutang, sisa_hutang FROM hutang_pupuk WHERE id = ? LIMIT 1");
        $qRow->bind_param("i", $id);
        $qRow->execute();
        $resRow = $qRow->get_result();
        $curHutang = $resRow ? $resRow->fetch_assoc() : null;
        $qRow->close();

        $petaniName = $curHutang['nama_petani'] ?? 'Petani';
        $curSisaVal = parseRupiahFloat($curHutang['sisa_hutang'] ?? '');
        if ($curSisaVal <= 0) {
            $curSisaVal = parseRupiahFloat($curHutang['jumlah_hutang'] ?? '');
        }

        $nomBayarVal = parseRupiahFloat($bayar_nominal);
        if ($nomBayarVal <= 0) {
            $nomBayarVal = $curSisaVal;
        }
        $nomBayarVal = min($nomBayarVal, $curSisaVal);

        $newSisaVal = max(0.0, $curSisaVal - $nomBayarVal);
        $isLunas    = ($newSisaVal <= 0.0);
        $newStatus  = $isLunas ? 'Lunas' : 'Belum Lunas';
        $newSisaStr = $isLunas ? 'Rp 0' : formatRupiahStr($newSisaVal);
        $tglLunas   = $isLunas ? $tglBayar : null;

        $finalKeterangan = $curHutang['keterangan'] ?? '-';
        $catatanText     = !empty($catatan_bayar) && $catatan_bayar !== '-' ? $catatan_bayar : $metode_bayar;
        $bayarNote       = "Bayar: " . formatRupiahStr($nomBayarVal) . " (" . $catatanText . ")";
        $finalKeterangan = ($finalKeterangan !== '-' && $finalKeterangan !== '')
            ? ($finalKeterangan . ' | ' . $bayarNote)
            : $bayarNote;

        $stmt = $conn->prepare("UPDATE hutang_pupuk SET status = ?, sisa_hutang = ?, tanggal_lunas = ?, keterangan = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssi", $newStatus, $newSisaStr, $tglLunas, $finalKeterangan, $id);
            if ($stmt->execute()) {
                $stmt->close();
                syncRiwayatHutangRow($conn, $id);

                // Catat jejak pembayaran ke pembayaran_hutang_pupuk (1 baris per pembayaran)
                if ($curHutang) {
                    $pembayaranKe = 1;
                    $qKe = $conn->prepare("SELECT COUNT(*) AS cnt FROM pembayaran_hutang_pupuk WHERE hutang_id = ?");
                    if ($qKe) {
                        $qKe->bind_param("i", $id);
                        $qKe->execute();
                        $resKe = $qKe->get_result();
                        $rowKe = $resKe ? $resKe->fetch_assoc() : null;
                        $pembayaranKe = (int)($rowKe['cnt'] ?? 0) + 1;
                        $qKe->close();
                    }

                    $bAnggotaId   = (int)$curHutang['anggota_id'];
                    $bNamaPetani  = formatTitleCase($curHutang['nama_petani']);
                    $bNamaPupuk   = formatTitleCase($curHutang['nama_pupuk']);
                    $bTanggal     = substr($tglBayar, 0, 10);
                    $bTotalHutang = (int)round(parseRupiahFloat($curHutang['jumlah_hutang'] ?? ''));
                    $bSisaSebelum = (int)round($curSisaVal);
                    $bJumlahBayar = (int)round($nomBayarVal);
                    $bSisaSesudah = (int)round($newSisaVal);
                    $bKeterangan  = ($catatan_bayar !== '') ? $catatan_bayar : '-';
                    $bDicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));

                    $insBayar = $conn->prepare("
                        INSERT INTO pembayaran_hutang_pupuk (
                            hutang_id, anggota_id, nama_petani, nama_pupuk, pembayaran_ke,
                            tanggal_bayar, total_hutang, sisa_sebelum, jumlah_bayar, sisa_hutang,
                            metode_bayar, status, keterangan, dicatat_oleh
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    if ($insBayar) {
                        $insBayar->bind_param(
                            "iissisiiiissss",
                            $id,
                            $bAnggotaId,
                            $bNamaPetani,
                            $bNamaPupuk,
                            $pembayaranKe,
                            $bTanggal,
                            $bTotalHutang,
                            $bSisaSebelum,
                            $bJumlahBayar,
                            $bSisaSesudah,
                            $metode_bayar,
                            $newStatus,
                            $bKeterangan,
                            $bDicatatOleh
                        );
                        $insBayar->execute();
                        $insBayar->close();
                    }
                }

                header("Location: " . appUrl("pupuk/?{$viewQuery}status=paid&name=" . urlencode($petaniName) . "&nominal=" . urlencode(formatRupiahStr($nomBayarVal)) . "&st=" . urlencode($newStatus)));
                exit;
            }
            $stmt->close();
        }
    }
}
