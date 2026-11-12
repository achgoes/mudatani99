<?php
/**
 * mudatani99 - CRUD Hutang Piutang Pupuk (hutang_pupuk & hutang_pupuk_items)
 * File: pupuk/actions/hutang.php
 */

/**
 * Pembungkus kompatibilitas untuk mem-parsing item hutang dari helper bersama.
 */
function parseHutangItemsPayload(mysqli $conn, array $post, array $oldStockAllowance = []): array {
    $res = parsePupukTransactionItems($conn, $post, $oldStockAllowance);
    return [
        'error'              => $res['error'],
        'items'              => $res['items'],
        'total_karung'       => $res['total_karung'],
        'total_berat'        => $res['total_berat'],
        'total_hutang'       => $res['total_nominal'],
        'summary_nama_pupuk' => $res['summary_nama_pupuk'],
    ];
}

if ($action === 'add_hutang') {
    $activeView      = ($returnView === 'kelola') ? 'hutang' : $returnView;
    $anggota_id      = (int)($_POST['anggota_id'] ?? 0);
    $sisa_hutang_raw = trim($_POST['sisa_hutang'] ?? '');
    $tanggal_raw     = trim($_POST['tanggal'] ?? '');
    $keterangan      = formatTitleCase($_POST['keterangan'] ?? '');
    $status          = $_POST['status'] ?? 'Belum Lunas';

    if (!in_array($status, ['Belum Lunas', 'Lunas'], true)) {
        $status = 'Belum Lunas';
    }

    $tanggal = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_raw) ? $tanggal_raw : date('Y-m-d');
    $parsed  = parseHutangItemsPayload($conn, $_POST);

    if ($anggota_id <= 0) {
        $feedbackMessage = "Nama Petani wajib dipilih terlebih dahulu.";
        $feedbackType    = "danger";
    } elseif ($parsed['error'] !== null) {
        $feedbackMessage = $parsed['error'];
        $feedbackType    = "danger";
    } else {
        if ($keterangan === '') {
            $keterangan = '-';
        }
        $keterangan = mb_substr($keterangan, 0, 255, 'UTF-8');

        $petaniStmt = $conn->prepare("SELECT id, nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
        $petaniStmt->bind_param("i", $anggota_id);
        $petaniStmt->execute();
        $petaniRes = $petaniStmt->get_result();
        $petaniRow = $petaniRes ? $petaniRes->fetch_assoc() : null;
        $petaniStmt->close();

        if (!$petaniRow) {
            $feedbackMessage = "Nama petani tidak ditemukan pada Daftar Anggota Petani.";
            $feedbackType    = "danger";
        } else {
            $items            = $parsed['items'];
            $totalHutangFloat = $parsed['total_hutang'];
            $jumlah_hutang    = formatRupiahStr($totalHutangFloat);

            if ($status === 'Lunas') {
                $sisa_hutang   = 'Rp 0';
                $tanggal_lunas = date('Y-m-d H:i:s');
            } else {
                $sisaFloat = ($sisa_hutang_raw !== '') ? parseRupiahFloat($sisa_hutang_raw) : $totalHutangFloat;
                if ($sisaFloat <= 0 && $totalHutangFloat > 0) {
                    $sisaFloat = $totalHutangFloat;
                } elseif ($totalHutangFloat > 0 && $sisaFloat > $totalHutangFloat) {
                    $sisaFloat = $totalHutangFloat;
                }
                $sisa_hutang   = formatRupiahStr($sisaFloat);
                $tanggal_lunas = null;
            }

            $nama_petani          = formatTitleCase($petaniRow['nama_petani']);
            $pengelolaan_pupuk_id = (count($items) === 1) ? $items[0]['pengelolaan_pupuk_id'] : null;
            $pupuk_id             = (count($items) === 1) ? $items[0]['pupuk_id'] : null;
            $nama_pupuk           = $parsed['summary_nama_pupuk'];
            $jumlah_karung        = formatKarungStr($parsed['total_karung']);
            $jumlah_berat         = formatBeratStr($parsed['total_berat']);
            $harga                = (count($items) === 1) ? $items[0]['harga'] : 'Rp 0';

            $conn->begin_transaction();
            $insStmt = $conn->prepare("
                INSERT INTO hutang_pupuk (
                    anggota_id, nama_petani, pengelolaan_pupuk_id, pupuk_id,
                    nama_pupuk, jumlah_karung, jumlah_berat, harga,
                    jumlah_hutang, sisa_hutang,
                    keterangan, tanggal, status, tanggal_lunas
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($insStmt) {
                $insStmt->bind_param(
                    "isiissssssssss",
                    $anggota_id,
                    $nama_petani,
                    $pengelolaan_pupuk_id,
                    $pupuk_id,
                    $nama_pupuk,
                    $jumlah_karung,
                    $jumlah_berat,
                    $harga,
                    $jumlah_hutang,
                    $sisa_hutang,
                    $keterangan,
                    $tanggal,
                    $status,
                    $tanggal_lunas
                );
                if ($insStmt->execute()) {
                    $newHutangId = (int)$insStmt->insert_id;
                    $insStmt->close();

                    saveHutangItems($conn, $newHutangId, $items);
                    adjustHutangItemsStock($conn, $items, -1);

                    syncRiwayatHutangRow($conn, $newHutangId);
                    ensureBackfillPembayaranHutang($conn);
                    $conn->commit();

                    header("Location: " . appUrl("pupuk/?{$viewQuery}status=added&name=" . urlencode($nama_petani)));
                    exit;
                } else {
                    $feedbackMessage = "Gagal menyimpan data hutang piutang: " . $conn->error;
                    $feedbackType    = "danger";
                    $insStmt->close();
                    $conn->rollback();
                }
            } else {
                $conn->rollback();
            }
        }
    }
} elseif ($action === 'edit_hutang') {
    $activeView      = ($returnView === 'kelola') ? 'hutang' : $returnView;
    $id              = (int)($_POST['id'] ?? 0);
    $anggota_id      = (int)($_POST['anggota_id'] ?? 0);
    $sisa_hutang_raw = trim($_POST['sisa_hutang'] ?? '');
    $tanggal_raw     = trim($_POST['tanggal'] ?? '');
    $keterangan      = formatTitleCase($_POST['keterangan'] ?? '');
    $status          = $_POST['status'] ?? 'Belum Lunas';

    if (!in_array($status, ['Belum Lunas', 'Lunas'], true)) {
        $status = 'Belum Lunas';
    }

    $tanggal = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_raw) ? $tanggal_raw : date('Y-m-d');

    if ($id <= 0 || $anggota_id <= 0) {
        $feedbackMessage = "ID transaksi dan Nama Petani wajib diisi.";
        $feedbackType    = "danger";
    } else {
        $oldStmt = $conn->prepare("SELECT * FROM hutang_pupuk WHERE id = ? LIMIT 1");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRes = $oldStmt->get_result();
        $oldRow = $oldRes ? $oldRes->fetch_assoc() : null;
        $oldStmt->close();

        $petaniStmt = $conn->prepare("SELECT id, nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
        $petaniStmt->bind_param("i", $anggota_id);
        $petaniStmt->execute();
        $petaniRes = $petaniStmt->get_result();
        $petaniRow = $petaniRes ? $petaniRes->fetch_assoc() : null;
        $petaniStmt->close();

        if (!$oldRow) {
            $feedbackMessage = "Data hutang piutang yang akan diedit tidak ditemukan.";
            $feedbackType    = "danger";
        } elseif (!$petaniRow) {
            $feedbackMessage = "Nama petani tidak ditemukan pada Daftar Anggota Petani.";
            $feedbackType    = "danger";
        } else {
            $oldItems = getHutangItemsByHutangId($conn, $id);
            if (empty($oldItems)) {
                $oldItems = [normalizeHutangItemRow($oldRow)];
            }

            $oldAllowance = buildStockAllowanceFromItems($conn, $oldItems);

            $parsed = parseHutangItemsPayload($conn, $_POST, $oldAllowance);
            if ($parsed['error'] !== null) {
                $feedbackMessage = $parsed['error'];
                $feedbackType    = "danger";
            } else {
                if ($keterangan === '') {
                    $keterangan = '-';
                }

                $newItems             = $parsed['items'];
                $totalHutangFloat     = $parsed['total_hutang'];
                $jumlah_hutang        = formatRupiahStr($totalHutangFloat);
                $nama_petani          = formatTitleCase($petaniRow['nama_petani']);
                $pengelolaan_pupuk_id = (count($newItems) === 1) ? $newItems[0]['pengelolaan_pupuk_id'] : null;
                $pupuk_id             = (count($newItems) === 1) ? $newItems[0]['pupuk_id'] : null;
                $nama_pupuk           = $parsed['summary_nama_pupuk'];
                $jumlah_karung        = formatKarungStr($parsed['total_karung']);
                $jumlah_berat         = formatBeratStr($parsed['total_berat']);
                $harga                = (count($newItems) === 1) ? $newItems[0]['harga'] : 'Rp 0';

                if ($status === 'Lunas') {
                    $sisa_hutang   = 'Rp 0';
                    $tanggal_lunas = $oldRow['tanggal_lunas'] ?: date('Y-m-d H:i:s');
                } else {
                    $tanggal_lunas = null;
                    $sisaFloat     = ($sisa_hutang_raw !== '') ? parseRupiahFloat($sisa_hutang_raw) : $totalHutangFloat;
                    if ($sisaFloat <= 0 && $totalHutangFloat > 0) {
                        $sisaFloat = $totalHutangFloat;
                    } elseif ($totalHutangFloat > 0 && $sisaFloat > $totalHutangFloat) {
                        $sisaFloat = $totalHutangFloat;
                    }
                    $sisa_hutang = formatRupiahStr($sisaFloat);
                    if ($sisaFloat >= $totalHutangFloat) {
                        $keterangan = stripHutangPaymentNote($keterangan);
                    }
                }
                $keterangan = mb_substr($keterangan, 0, 255, 'UTF-8');

                $conn->begin_transaction();

                // 1. Kembalikan stok seluruh item lama
                adjustHutangItemsStock($conn, $oldItems, +1);

                // 2. Kurangi stok seluruh item baru
                adjustHutangItemsStock($conn, $newItems, -1);

                // 3. Update baris hutang_pupuk
                $updStmt = $conn->prepare("
                    UPDATE hutang_pupuk
                    SET anggota_id = ?, nama_petani = ?, pengelolaan_pupuk_id = ?, pupuk_id = ?,
                        nama_pupuk = ?, jumlah_karung = ?, jumlah_berat = ?, harga = ?,
                        jumlah_hutang = ?, sisa_hutang = ?,
                        keterangan = ?, tanggal = ?, status = ?, tanggal_lunas = ?
                    WHERE id = ?
                ");
                if ($updStmt) {
                    $updStmt->bind_param(
                        "isiissssssssssi",
                        $anggota_id,
                        $nama_petani,
                        $pengelolaan_pupuk_id,
                        $pupuk_id,
                        $nama_pupuk,
                        $jumlah_karung,
                        $jumlah_berat,
                        $harga,
                        $jumlah_hutang,
                        $sisa_hutang,
                        $keterangan,
                        $tanggal,
                        $status,
                        $tanggal_lunas,
                        $id
                    );
                    if ($updStmt->execute()) {
                        $updStmt->close();
                        saveHutangItems($conn, $id, $newItems);
                        syncRiwayatHutangRow($conn, $id);

                        $totalHutangInt = (int)round($totalHutangFloat);
                        $sisaHutangInt  = ($status === 'Lunas') ? 0 : (int)round(parseRupiahFloat($sisa_hutang));
                        syncPembayaranOnHutangUpdate($conn, $id, $anggota_id, $nama_petani, $nama_pupuk, $totalHutangInt, $sisaHutangInt, $status, stripHutangPaymentNote($keterangan));

                        $conn->commit();
                        header("Location: " . appUrl("pupuk/?{$viewQuery}status=updated&name=" . urlencode($nama_petani)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal memperbarui data hutang: " . $conn->error;
                        $feedbackType    = "danger";
                        $updStmt->close();
                        $conn->rollback();
                    }
                } else {
                    $conn->rollback();
                }
            }
        }
    }
} elseif ($action === 'delete_hutang') {
    $activeView = ($returnView === 'kelola') ? 'hutang' : $returnView;
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $oldStmt = $conn->prepare("SELECT * FROM hutang_pupuk WHERE id = ? LIMIT 1");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRes = $oldStmt->get_result();
        $oldRow = $oldRes ? $oldRes->fetch_assoc() : null;
        $oldStmt->close();

        if ($oldRow) {
            $oldItems = getHutangItemsByHutangId($conn, $id);
            if (empty($oldItems)) {
                $oldItems = [normalizeHutangItemRow($oldRow)];
            }
            $karungLabel = $oldRow['jumlah_karung'];

            $delR = $conn->prepare("DELETE FROM riwayat_hutang_pupuk WHERE hutang_id = ?");
            if ($delR) {
                $delR->bind_param("i", $id);
                $delR->execute();
                $delR->close();
            }

            $delH = $conn->prepare("DELETE FROM hutang_pupuk WHERE id = ?");
            if ($delH) {
                $delH->bind_param("i", $id);
                if ($delH->execute()) {
                    $delH->close();
                    adjustHutangItemsStock($conn, $oldItems, +1);

                    header("Location: " . appUrl("pupuk/?{$viewQuery}status=deleted&karung=" . urlencode($karungLabel)));
                    exit;
                } else {
                    $feedbackMessage = "Gagal menghapus data hutang: " . $conn->error;
                    $feedbackType    = "danger";
                    $delH->close();
                }
            }
        }
    }
}
