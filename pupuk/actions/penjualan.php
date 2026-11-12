<?php
/**
 * mudatani99 - CRUD Penjualan Pupuk (penjualan_pupuk, penjualan_pupuk_items & riwayat_penjualan_pupuk)
 * Berdiri sendiri (tidak terkait dengan hutang piutang), namun mengambil & mengurangi Stok Pupuk.
 * File: pupuk/actions/penjualan.php
 */

/**
 * Menyelesaikan identitas pembeli (nama & alamat) dari anggota_id atau input form manual.
 */
function resolvePembeliPenjualan(mysqli $conn, int $anggotaId, string $namaInput, string $alamatInput = ''): array {
    $cleanNama   = formatTitleCase($namaInput);
    $cleanAlamat = formatTitleCase($alamatInput);
    if ($anggotaId > 0) {
        $stmt = $conn->prepare("SELECT id, nama_petani, alamat FROM anggota_petani WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $anggotaId);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();
            if ($row) {
                $alamatAnggota = formatTitleCase($row['alamat'] ?? '-');
                return [
                    'anggota_id'     => (int)$row['id'],
                    'nama_pembeli'   => formatTitleCase($row['nama_petani']),
                    'alamat_pembeli' => mb_substr($alamatAnggota !== '' ? $alamatAnggota : '-', 0, 255, 'UTF-8'),
                ];
            }
        }
    }

    return [
        'anggota_id'     => null,
        'nama_pembeli'   => mb_substr($cleanNama, 0, 100, 'UTF-8'),
        'alamat_pembeli' => mb_substr($cleanAlamat !== '' ? $cleanAlamat : '-', 0, 255, 'UTF-8'),
    ];
}

if ($action === 'add_penjualan') {
    $activeView   = ($returnView === 'kelola') ? 'penjualan' : $returnView;
    $anggotaIdRaw = (int)($_POST['anggota_id'] ?? 0);
    $namaInput    = trim($_POST['nama_pembeli'] ?? '');
    $alamatInput  = trim($_POST['alamat_pembeli'] ?? '');
    $metodeBayar  = $_POST['metode_bayar'] ?? 'Tunai';
    $tanggalRaw   = trim($_POST['tanggal_jual'] ?? '');
    $keterangan   = formatTitleCase($_POST['keterangan'] ?? '');

    if (!in_array($metodeBayar, ['Tunai', 'Transfer', 'QRIS'], true)) {
        $metodeBayar = 'Tunai';
    }
    $tanggalJual = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalRaw) ? $tanggalRaw : date('Y-m-d');

    $pembeli = resolvePembeliPenjualan($conn, $anggotaIdRaw, $namaInput, $alamatInput);
    $parsed  = parsePupukTransactionItems($conn, $_POST);

    if ($pembeli['nama_pembeli'] === '') {
        $feedbackMessage = "Nama Pembeli wajib diisi atau dipilih dari daftar anggota petani.";
        $feedbackType    = "danger";
    } elseif ($parsed['error'] !== null) {
        $feedbackMessage = $parsed['error'];
        $feedbackType    = "danger";
    } else {
        if ($keterangan === '') {
            $keterangan = '-';
        }
        $keterangan    = mb_substr($keterangan, 0, 255, 'UTF-8');
        $items         = $parsed['items'];
        $totalHarga    = (int)round((float)$parsed['total_nominal']);
        $jumlahKarung  = formatKarungStr($parsed['total_karung']);
        $jumlahBerat   = formatBeratStr($parsed['total_berat']);
        $namaPupuk     = $parsed['summary_nama_pupuk'];
        $kodeTransaksi = generateKodePenjualan($conn, $tanggalJual);
        $dicatatOleh   = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));
        $anggotaIdDb   = $pembeli['anggota_id'];
        $namaPembeli   = $pembeli['nama_pembeli'];
        $alamatPembeli = $pembeli['alamat_pembeli'];

        $conn->begin_transaction();
        $insStmt = $conn->prepare("
            INSERT INTO penjualan_pupuk (
                kode_transaksi, anggota_id, nama_pembeli, alamat_pembeli, nama_pupuk,
                jumlah_karung, jumlah_berat, total_harga, metode_bayar,
                tanggal_jual, keterangan, dicatat_oleh
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($insStmt) {
            $insStmt->bind_param(
                "sisssssissss",
                $kodeTransaksi,
                $anggotaIdDb,
                $namaPembeli,
                $alamatPembeli,
                $namaPupuk,
                $jumlahKarung,
                $jumlahBerat,
                $totalHarga,
                $metodeBayar,
                $tanggalJual,
                $keterangan,
                $dicatatOleh
            );
            if ($insStmt->execute()) {
                $newPenjualanId = (int)$insStmt->insert_id;
                $insStmt->close();

                savePenjualanItems($conn, $newPenjualanId, $items);
                adjustHutangItemsStock($conn, $items, -1);
                recordRiwayatPenjualan(
                    $conn,
                    $newPenjualanId,
                    $kodeTransaksi,
                    'Terjual',
                    $namaPembeli,
                    $items,
                    $jumlahKarung,
                    $jumlahBerat,
                    $totalHarga,
                    $metodeBayar,
                    $tanggalJual,
                    $keterangan
                );

                $conn->commit();
                header("Location: " . appUrl("pupuk/?{$viewQuery}status=added&name=" . urlencode($namaPembeli)));
                exit;
            } else {
                $feedbackMessage = "Gagal menyimpan transaksi penjualan pupuk: " . $conn->error;
                $feedbackType    = "danger";
                $insStmt->close();
                $conn->rollback();
            }
        } else {
            $conn->rollback();
        }
    }
} elseif ($action === 'edit_penjualan') {
    $activeView   = ($returnView === 'kelola') ? 'penjualan' : $returnView;
    $id           = (int)($_POST['id'] ?? 0);
    $anggotaIdRaw = (int)($_POST['anggota_id'] ?? 0);
    $namaInput    = trim($_POST['nama_pembeli'] ?? '');
    $alamatInput  = trim($_POST['alamat_pembeli'] ?? '');
    $metodeBayar  = $_POST['metode_bayar'] ?? 'Tunai';
    $tanggalRaw   = trim($_POST['tanggal_jual'] ?? '');
    $keterangan   = formatTitleCase($_POST['keterangan'] ?? '');

    if (!in_array($metodeBayar, ['Tunai', 'Transfer', 'QRIS'], true)) {
        $metodeBayar = 'Tunai';
    }
    $tanggalJual = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalRaw) ? $tanggalRaw : date('Y-m-d');
    $pembeli     = resolvePembeliPenjualan($conn, $anggotaIdRaw, $namaInput, $alamatInput);

    if ($id <= 0 || $pembeli['nama_pembeli'] === '') {
        $feedbackMessage = "ID transaksi dan Nama Pembeli wajib diisi.";
        $feedbackType    = "danger";
    } else {
        $oldStmt = $conn->prepare("SELECT * FROM penjualan_pupuk WHERE id = ? LIMIT 1");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRes = $oldStmt->get_result();
        $oldRow = $oldRes ? $oldRes->fetch_assoc() : null;
        $oldStmt->close();

        if (!$oldRow) {
            $feedbackMessage = "Data penjualan pupuk yang akan diedit tidak ditemukan.";
            $feedbackType    = "danger";
        } else {
            $oldItems     = getPenjualanItemsById($conn, $id);
            $oldAllowance = buildStockAllowanceFromItems($conn, $oldItems);
            $parsed       = parsePupukTransactionItems($conn, $_POST, $oldAllowance);

            if ($parsed['error'] !== null) {
                $feedbackMessage = $parsed['error'];
                $feedbackType    = "danger";
            } else {
                if ($keterangan === '') {
                    $keterangan = '-';
                }
                $keterangan   = mb_substr($keterangan, 0, 255, 'UTF-8');
                $newItems     = $parsed['items'];
                $totalHarga   = (int)round((float)$parsed['total_nominal']);
                $jumlahKarung = formatKarungStr($parsed['total_karung']);
                $jumlahBerat  = formatBeratStr($parsed['total_berat']);
                $namaPupuk    = $parsed['summary_nama_pupuk'];
                $dicatatOleh   = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));
                $anggotaIdDb   = $pembeli['anggota_id'];
                $namaPembeli   = $pembeli['nama_pembeli'];
                $alamatPembeli = $pembeli['alamat_pembeli'];

                $conn->begin_transaction();

                // 1. Kembalikan stok item lama, lalu kurangi stok item baru
                adjustHutangItemsStock($conn, $oldItems, +1);
                adjustHutangItemsStock($conn, $newItems, -1);

                $updStmt = $conn->prepare("
                    UPDATE penjualan_pupuk
                    SET anggota_id = ?, nama_pembeli = ?, alamat_pembeli = ?, nama_pupuk = ?,
                        jumlah_karung = ?, jumlah_berat = ?, total_harga = ?,
                        metode_bayar = ?, tanggal_jual = ?, keterangan = ?, dicatat_oleh = ?
                    WHERE id = ?
                ");
                if ($updStmt) {
                    $updStmt->bind_param(
                        "isssssissssi",
                        $anggotaIdDb,
                        $namaPembeli,
                        $alamatPembeli,
                        $namaPupuk,
                        $jumlahKarung,
                        $jumlahBerat,
                        $totalHarga,
                        $metodeBayar,
                        $tanggalJual,
                        $keterangan,
                        $dicatatOleh,
                        $id
                    );
                    if ($updStmt->execute()) {
                        $updStmt->close();
                        savePenjualanItems($conn, $id, $newItems);
                        recordRiwayatPenjualan(
                            $conn,
                            $id,
                            $oldRow['kode_transaksi'],
                            'Diperbarui',
                            $namaPembeli,
                            $newItems,
                            $jumlahKarung,
                            $jumlahBerat,
                            $totalHarga,
                            $metodeBayar,
                            $tanggalJual,
                            $keterangan
                        );

                        $conn->commit();
                        header("Location: " . appUrl("pupuk/?{$viewQuery}status=updated&name=" . urlencode($namaPembeli)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal memperbarui transaksi penjualan: " . $conn->error;
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
} elseif ($action === 'delete_penjualan') {
    $activeView = ($returnView === 'kelola') ? 'penjualan' : $returnView;
    $id         = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $oldStmt = $conn->prepare("SELECT * FROM penjualan_pupuk WHERE id = ? LIMIT 1");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRes = $oldStmt->get_result();
        $oldRow = $oldRes ? $oldRes->fetch_assoc() : null;
        $oldStmt->close();

        if ($oldRow) {
            $oldItems    = getPenjualanItemsById($conn, $id);
            $karungLabel = $oldRow['jumlah_karung'];

            $conn->begin_transaction();
            recordRiwayatPenjualan(
                $conn,
                null,
                $oldRow['kode_transaksi'],
                'Dibatalkan',
                formatTitleCase($oldRow['nama_pembeli']),
                $oldItems,
                $oldRow['jumlah_karung'],
                $oldRow['jumlah_berat'],
                (int)$oldRow['total_harga'],
                $oldRow['metode_bayar'],
                $oldRow['tanggal_jual'],
                'Transaksi penjualan dihapus dan stok pupuk dikembalikan'
            );

            $delStmt = $conn->prepare("DELETE FROM penjualan_pupuk WHERE id = ?");
            if ($delStmt) {
                $delStmt->bind_param("i", $id);
                if ($delStmt->execute()) {
                    $delStmt->close();
                    adjustHutangItemsStock($conn, $oldItems, +1);
                    $conn->commit();

                    header("Location: " . appUrl("pupuk/?{$viewQuery}status=deleted&karung=" . urlencode($karungLabel)));
                    exit;
                } else {
                    $feedbackMessage = "Gagal menghapus transaksi penjualan: " . $conn->error;
                    $feedbackType    = "danger";
                    $delStmt->close();
                    $conn->rollback();
                }
            } else {
                $conn->rollback();
            }
        }
    }
}
