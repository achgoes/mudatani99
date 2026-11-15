<?php
/**
 * mudatani99 - CRUD Manajemen Transaksi Akhir
 * File: transaksi-akhir/actions.php
 */

$feedbackMessage = "";
$feedbackType    = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_transaksi_akhir') {
        $setoranId  = (int)($_POST['setoran_id'] ?? 0);
        $rapaksiRaw = trim((string)($_POST['rapaksi'] ?? '0'));
        $rapaksi    = ($rapaksiRaw === '') ? 0.0 : round(parseDecimalTa($rapaksiRaw), 2);
        $hargaPerKg = parseRupiahIntTa($_POST['harga_per_kg'] ?? '');
        $tanggalRaw = trim((string)($_POST['tanggal'] ?? ''));
        $tanggal    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalRaw) ? $tanggalRaw : date('Y-m-d');
        $keterangan = formatTitleCaseTa($_POST['keterangan'] ?? '');

        if ($setoranId <= 0) {
            $feedbackMessage = "Data Setoran Petani wajib dipilih terlebih dahulu.";
            $feedbackType    = "danger";
        } elseif ($rapaksi < 0) {
            $feedbackMessage = "Rapaksi tidak boleh bernilai negatif.";
            $feedbackType    = "danger";
        } elseif ($hargaPerKg <= 0) {
            $feedbackMessage = "Harga /Kg wajib diisi lebih dari Rp 0.";
            $feedbackType    = "danger";
        } else {
            // Ambil referensi setoran_petani
            $setoranRow = null;
            $stmtSet = $conn->prepare("
                SELECT s.id, s.kode_setoran, s.anggota_id,
                       COALESCE(a.nama_petani, s.nama_petani) AS nama_petani,
                       s.jumlah_berat
                FROM setoran_petani s
                LEFT JOIN anggota_petani a ON s.anggota_id = a.id
                WHERE s.id = ?
                LIMIT 1
            ");
            if ($stmtSet) {
                $stmtSet->bind_param("i", $setoranId);
                $stmtSet->execute();
                $resSet     = $stmtSet->get_result();
                $setoranRow = $resSet ? $resSet->fetch_assoc() : null;
                $stmtSet->close();
            }

            if (!$setoranRow) {
                $feedbackMessage = "Data Setoran Petani yang dipilih tidak ditemukan.";
                $feedbackType    = "danger";
            } else {
                // Pastikan setoran ini belum memiliki transaksi akhir
                $dupCount = 0;
                $stmtDup = $conn->prepare("SELECT COUNT(*) AS cnt FROM transaksi_akhir WHERE setoran_id = ?");
                if ($stmtDup) {
                    $stmtDup->bind_param("i", $setoranId);
                    $stmtDup->execute();
                    $resDup = $stmtDup->get_result();
                    if ($resDup && ($rDup = $resDup->fetch_assoc())) {
                        $dupCount = (int)$rDup['cnt'];
                    }
                    $stmtDup->close();
                }

                $beratSetoran = round(parseDecimalTa($setoranRow['jumlah_berat']), 2);

                if ($dupCount > 0) {
                    $feedbackMessage = "Setoran {$setoranRow['kode_setoran']} sudah diproses di Transaksi Akhir. Gunakan tombol Edit jika ingin mengubahnya.";
                    $feedbackType    = "danger";
                } elseif ($beratSetoran <= 0) {
                    $feedbackMessage = "Berat setoran pada data yang dipilih tidak valid (0 Kg).";
                    $feedbackType    = "danger";
                } elseif ($rapaksi > $beratSetoran) {
                    $feedbackMessage = "Rapaksi (" . formatBeratTaStr($rapaksi) . ") tidak boleh melebihi Berat Setoran (" . formatBeratTaStr($beratSetoran) . ").";
                    $feedbackType    = "danger";
                } else {
                    if ($keterangan === '') {
                        $keterangan = '-';
                    }
                    $keterangan    = mb_substr($keterangan, 0, 255, 'UTF-8');
                    $anggotaId     = !empty($setoranRow['anggota_id']) ? (int)$setoranRow['anggota_id'] : null;
                    $namaPetani    = mb_substr(formatTitleCaseTa($setoranRow['nama_petani']), 0, 100, 'UTF-8');
                    $beratAkhir    = round(max(0.0, $beratSetoran - $rapaksi), 2);
                    $jumlahUang    = (int)round($beratAkhir * $hargaPerKg);
                    $kodeTransaksi = generateKodeTransaksiAkhir($conn, $tanggal);
                    $dicatatOleh   = mb_substr(formatTitleCaseTa($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-')), 0, 100, 'UTF-8');

                    $insStmt = $conn->prepare("
                        INSERT INTO transaksi_akhir (
                            kode_transaksi, setoran_id, anggota_id, nama_petani,
                            berat_setoran, rapaksi, berat_akhir,
                            harga_per_kg, jumlah_uang, tanggal, keterangan, dicatat_oleh
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    if ($insStmt) {
                        $insStmt->bind_param(
                            "siisdddiisss",
                            $kodeTransaksi,
                            $setoranId,
                            $anggotaId,
                            $namaPetani,
                            $beratSetoran,
                            $rapaksi,
                            $beratAkhir,
                            $hargaPerKg,
                            $jumlahUang,
                            $tanggal,
                            $keterangan,
                            $dicatatOleh
                        );

                        if ($insStmt->execute()) {
                            $insStmt->close();
                            header('Location: ' . appUrl('transaksi-akhir/?status=added&name=' . urlencode($namaPetani) . '&uang=' . urlencode(formatRupiahTaStr($jumlahUang))));
                            exit;
                        } else {
                            $feedbackMessage = dbErrorUntukPengguna('simpan transaksi akhir', $conn->error);
                            $feedbackType    = "danger";
                            $insStmt->close();
                        }
                    } else {
                        $feedbackMessage = dbErrorUntukPengguna('siapkan query tambah transaksi akhir', $conn->error);
                        $feedbackType    = "danger";
                    }
                }
            }
        }
    } elseif ($action === 'edit_transaksi_akhir') {
        $id         = (int)($_POST['id'] ?? 0);
        $setoranId  = (int)($_POST['setoran_id'] ?? 0);
        $rapaksiRaw = trim((string)($_POST['rapaksi'] ?? '0'));
        $rapaksi    = ($rapaksiRaw === '') ? 0.0 : round(parseDecimalTa($rapaksiRaw), 2);
        $hargaPerKg = parseRupiahIntTa($_POST['harga_per_kg'] ?? '');
        $tanggalRaw = trim((string)($_POST['tanggal'] ?? ''));
        $tanggal    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalRaw) ? $tanggalRaw : date('Y-m-d');
        $keterangan = formatTitleCaseTa($_POST['keterangan'] ?? '');

        if ($id <= 0 || $setoranId <= 0) {
            $feedbackMessage = "ID transaksi dan Data Setoran Petani wajib dipilih.";
            $feedbackType    = "danger";
        } elseif ($rapaksi < 0) {
            $feedbackMessage = "Rapaksi tidak boleh bernilai negatif.";
            $feedbackType    = "danger";
        } elseif ($hargaPerKg <= 0) {
            $feedbackMessage = "Harga /Kg wajib diisi lebih dari Rp 0.";
            $feedbackType    = "danger";
        } else {
            $setoranRow = null;
            $stmtSet = $conn->prepare("
                SELECT s.id, s.kode_setoran, s.anggota_id,
                       COALESCE(a.nama_petani, s.nama_petani) AS nama_petani,
                       s.jumlah_berat
                FROM setoran_petani s
                LEFT JOIN anggota_petani a ON s.anggota_id = a.id
                WHERE s.id = ?
                LIMIT 1
            ");
            if ($stmtSet) {
                $stmtSet->bind_param("i", $setoranId);
                $stmtSet->execute();
                $resSet     = $stmtSet->get_result();
                $setoranRow = $resSet ? $resSet->fetch_assoc() : null;
                $stmtSet->close();
            }

            if (!$setoranRow) {
                $feedbackMessage = "Data Setoran Petani yang dipilih tidak ditemukan.";
                $feedbackType    = "danger";
            } else {
                $dupCount = 0;
                $stmtDup = $conn->prepare("SELECT COUNT(*) AS cnt FROM transaksi_akhir WHERE setoran_id = ? AND id != ?");
                if ($stmtDup) {
                    $stmtDup->bind_param("ii", $setoranId, $id);
                    $stmtDup->execute();
                    $resDup = $stmtDup->get_result();
                    if ($resDup && ($rDup = $resDup->fetch_assoc())) {
                        $dupCount = (int)$rDup['cnt'];
                    }
                    $stmtDup->close();
                }

                $beratSetoran = round(parseDecimalTa($setoranRow['jumlah_berat']), 2);

                if ($dupCount > 0) {
                    $feedbackMessage = "Setoran {$setoranRow['kode_setoran']} sudah terhubung dengan transaksi akhir lainnya.";
                    $feedbackType    = "danger";
                } elseif ($rapaksi > $beratSetoran) {
                    $feedbackMessage = "Rapaksi (" . formatBeratTaStr($rapaksi) . ") tidak boleh melebihi Berat Setoran (" . formatBeratTaStr($beratSetoran) . ").";
                    $feedbackType    = "danger";
                } else {
                    if ($keterangan === '') {
                        $keterangan = '-';
                    }
                    $keterangan  = mb_substr($keterangan, 0, 255, 'UTF-8');
                    $anggotaId   = !empty($setoranRow['anggota_id']) ? (int)$setoranRow['anggota_id'] : null;
                    $namaPetani  = mb_substr(formatTitleCaseTa($setoranRow['nama_petani']), 0, 100, 'UTF-8');
                    $beratAkhir  = round(max(0.0, $beratSetoran - $rapaksi), 2);
                    $jumlahUang  = (int)round($beratAkhir * $hargaPerKg);
                    $dicatatOleh = mb_substr(formatTitleCaseTa($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-')), 0, 100, 'UTF-8');

                    $updStmt = $conn->prepare("
                        UPDATE transaksi_akhir
                        SET setoran_id = ?, anggota_id = ?, nama_petani = ?,
                            berat_setoran = ?, rapaksi = ?, berat_akhir = ?,
                            harga_per_kg = ?, jumlah_uang = ?, tanggal = ?,
                            keterangan = ?, dicatat_oleh = ?
                        WHERE id = ?
                    ");

                    if ($updStmt) {
                        $updStmt->bind_param(
                            "iisdddiisssi",
                            $setoranId,
                            $anggotaId,
                            $namaPetani,
                            $beratSetoran,
                            $rapaksi,
                            $beratAkhir,
                            $hargaPerKg,
                            $jumlahUang,
                            $tanggal,
                            $keterangan,
                            $dicatatOleh,
                            $id
                        );

                        if ($updStmt->execute()) {
                            $updStmt->close();
                            header('Location: ' . appUrl('transaksi-akhir/?status=updated&name=' . urlencode($namaPetani) . '&uang=' . urlencode(formatRupiahTaStr($jumlahUang))));
                            exit;
                        } else {
                            $feedbackMessage = dbErrorUntukPengguna('perbarui transaksi akhir', $conn->error);
                            $feedbackType    = "danger";
                            $updStmt->close();
                        }
                    } else {
                        $feedbackMessage = dbErrorUntukPengguna('siapkan query edit transaksi akhir', $conn->error);
                        $feedbackType    = "danger";
                    }
                }
            }
        }
    } elseif ($action === 'delete_transaksi_akhir') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $delStmt = $conn->prepare("DELETE FROM transaksi_akhir WHERE id = ?");
            if ($delStmt) {
                $delStmt->bind_param("i", $id);
                if ($delStmt->execute()) {
                    $delStmt->close();
                    header('Location: ' . appUrl('transaksi-akhir/?status=deleted'));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('hapus transaksi akhir', $conn->error);
                    $feedbackType    = "danger";
                    $delStmt->close();
                }
            } else {
                $feedbackMessage = dbErrorUntukPengguna('siapkan query hapus transaksi akhir', $conn->error);
                $feedbackType    = "danger";
            }
        }
    }
}
