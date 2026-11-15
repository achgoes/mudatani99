<?php
/**
 * mudatani99 - CRUD Manajemen Setoran Petani
 * File: setoran/actions.php
 */

$feedbackMessage = "";
$feedbackType    = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_setoran') {
        $anggotaId  = (int)($_POST['anggota_id'] ?? 0);
        $tanggalRaw = trim($_POST['tanggal'] ?? '');
        $keterangan = formatTitleCaseSetoran($_POST['keterangan'] ?? '');

        $tanggal = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalRaw) ? $tanggalRaw : date('Y-m-d');
        $parsed  = parseSetoranItemsFromPost($conn, $_POST);

        if ($anggotaId <= 0) {
            $feedbackMessage = "Nama Petani wajib dipilih dari daftar Anggota Petani.";
            $feedbackType    = "danger";
        } elseif ($parsed['error'] !== null) {
            $feedbackMessage = $parsed['error'];
            $feedbackType    = "danger";
        } else {
            // Pastikan anggota_id valid di anggota_petani
            $petaniRow = null;
            $cekAnggota = $conn->prepare("SELECT id, nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
            if ($cekAnggota) {
                $cekAnggota->bind_param("i", $anggotaId);
                $cekAnggota->execute();
                $resAnggota = $cekAnggota->get_result();
                $petaniRow  = $resAnggota ? $resAnggota->fetch_assoc() : null;
                $cekAnggota->close();
            }

            if (!$petaniRow) {
                $feedbackMessage = "Anggota petani yang dipilih tidak ditemukan pada Daftar Anggota Petani.";
                $feedbackType    = "danger";
            } else {
                if ($keterangan === '') {
                    $keterangan = '-';
                }
                $keterangan    = mb_substr($keterangan, 0, 255, 'UTF-8');
                $namaPetani    = mb_substr(formatTitleCaseSetoran($petaniRow['nama_petani']), 0, 100, 'UTF-8');
                $items         = $parsed['items'];
                $jumlahBerat   = formatBeratSetoranStr($parsed['total_berat']);
                $namaTanaman   = $parsed['summary_nama_tanaman'];
                $kodeSetoran   = generateKodeSetoranPetani($conn, $tanggal);
                $dicatatOleh   = mb_substr(formatTitleCaseSetoran($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-')), 0, 100, 'UTF-8');

                $conn->begin_transaction();
                $insStmt = $conn->prepare("
                    INSERT INTO setoran_petani (
                        kode_setoran, anggota_id, nama_petani, nama_tanaman,
                        jumlah_berat, tanggal, keterangan, dicatat_oleh
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                if ($insStmt) {
                    $insStmt->bind_param(
                        "sissssss",
                        $kodeSetoran,
                        $anggotaId,
                        $namaPetani,
                        $namaTanaman,
                        $jumlahBerat,
                        $tanggal,
                        $keterangan,
                        $dicatatOleh
                    );

                    if ($insStmt->execute()) {
                        $newSetoranId = (int)$insStmt->insert_id;
                        $insStmt->close();

                        if (saveSetoranPetaniItems($conn, $newSetoranId, $items)) {
                            $conn->commit();
                            header('Location: ' . appUrl('setoran/?status=added&name=' . urlencode($namaPetani) . '&berat=' . urlencode($jumlahBerat)));
                            exit;
                        } else {
                            $feedbackMessage = dbErrorUntukPengguna('simpan detail item setoran petani', $conn->error);
                            $feedbackType    = "danger";
                            $conn->rollback();
                        }
                    } else {
                        $feedbackMessage = dbErrorUntukPengguna('simpan setoran petani', $conn->error);
                        $feedbackType    = "danger";
                        $insStmt->close();
                        $conn->rollback();
                    }
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('siapkan query tambah setoran petani', $conn->error);
                    $feedbackType    = "danger";
                    $conn->rollback();
                }
            }
        }
    } elseif ($action === 'delete_setoran') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $delStmt = $conn->prepare("DELETE FROM setoran_petani WHERE id = ?");
            if ($delStmt) {
                $delStmt->bind_param("i", $id);
                if ($delStmt->execute()) {
                    $delStmt->close();
                    header('Location: ' . appUrl('setoran/?status=deleted'));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('hapus data setoran petani', $conn->error);
                    $feedbackType    = "danger";
                    $delStmt->close();
                }
            } else {
                $feedbackMessage = dbErrorUntukPengguna('siapkan query hapus setoran petani', $conn->error);
                $feedbackType    = "danger";
            }
        }
    }
}
