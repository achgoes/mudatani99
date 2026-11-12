<?php
/**
 * mudatani99 - CRUD Penjualan Tanaman (penjualan_tanaman, penjualan_tanaman_items & riwayat_penjualan_tanaman)
 * Berdiri sendiri (tidak terkait dengan hutang piutang), namun mengambil & mengurangi Stok Tanaman.
 * File: tanaman/actions/penjualan.php
 */

/**
 * Menyelesaikan identitas pembeli (nama & alamat) dari anggota_id atau input form manual.
 */
function resolvePembeliPenjualanTanaman(mysqli $conn, int $anggotaId, string $namaInput, string $alamatInput = ''): array {
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

    $pembeli = resolvePembeliPenjualanTanaman($conn, $anggotaIdRaw, $namaInput, $alamatInput);
    $parsed  = parseTanamanTransactionItems($conn, $_POST);

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
        $jumlahKemasan = formatKemasanStr($parsed['total_kemasan']);
        $jumlahBerat   = formatBeratStr($parsed['total_berat']);
        $namaTanaman   = $parsed['summary_nama_tanaman'];
        $kodeTransaksi = generateKodePenjualanTanaman($conn, $tanggalJual);
        $dicatatOleh   = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));
        $anggotaIdDb   = $pembeli['anggota_id'];
        $namaPembeli   = $pembeli['nama_pembeli'];
        $alamatPembeli = $pembeli['alamat_pembeli'];

        $conn->begin_transaction();
        $insStmt = $conn->prepare("
            INSERT INTO penjualan_tanaman (
                kode_transaksi, anggota_id, nama_pembeli, alamat_pembeli, nama_tanaman,
                jumlah_kemasan, jumlah_berat, total_harga, metode_bayar,
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
                $namaTanaman,
                $jumlahKemasan,
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

                savePenjualanTanamanItems($conn, $newPenjualanId, $items);
                adjustTanamanItemsStock($conn, $items, -1, [
                    'sumber'     => 'Penjualan',
                    'aksi'       => 'Penjualan',
                    'keterangan' => "Nota {$kodeTransaksi} ({$namaPembeli})",
                ]);
                recordRiwayatPenjualanTanaman(
                    $conn,
                    $newPenjualanId,
                    $kodeTransaksi,
                    'Terjual',
                    $namaPembeli,
                    $items,
                    $jumlahKemasan,
                    $jumlahBerat,
                    $totalHarga,
                    $metodeBayar,
                    $tanggalJual,
                    $keterangan
                );

                $conn->commit();
                header("Location: " . appUrl("tanaman/?{$viewQuery}status=added&name=" . urlencode($namaPembeli)));
                exit;
            } else {
                $feedbackMessage = "Gagal menyimpan transaksi penjualan tanaman: " . $conn->error;
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
    $pembeli     = resolvePembeliPenjualanTanaman($conn, $anggotaIdRaw, $namaInput, $alamatInput);

    if ($id <= 0 || $pembeli['nama_pembeli'] === '') {
        $feedbackMessage = "ID transaksi dan Nama Pembeli wajib diisi.";
        $feedbackType    = "danger";
    } else {
        $oldStmt = $conn->prepare("SELECT * FROM penjualan_tanaman WHERE id = ? LIMIT 1");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRes = $oldStmt->get_result();
        $oldRow = $oldRes ? $oldRes->fetch_assoc() : null;
        $oldStmt->close();

        if (!$oldRow) {
            $feedbackMessage = "Data penjualan tanaman yang akan diedit tidak ditemukan.";
            $feedbackType    = "danger";
        } else {
            $oldItems     = getPenjualanTanamanItemsById($conn, $id);
            $oldAllowance = buildTanamanStockAllowanceFromItems($conn, $oldItems);
            $parsed       = parseTanamanTransactionItems($conn, $_POST, $oldAllowance);

            if ($parsed['error'] !== null) {
                $feedbackMessage = $parsed['error'];
                $feedbackType    = "danger";
            } else {
                if ($keterangan === '') {
                    $keterangan = '-';
                }
                $keterangan    = mb_substr($keterangan, 0, 255, 'UTF-8');
                $newItems      = $parsed['items'];
                $totalHarga    = (int)round((float)$parsed['total_nominal']);
                $jumlahKemasan = formatKemasanStr($parsed['total_kemasan']);
                $jumlahBerat   = formatBeratStr($parsed['total_berat']);
                $namaTanaman   = $parsed['summary_nama_tanaman'];
                $dicatatOleh   = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));
                $anggotaIdDb   = $pembeli['anggota_id'];
                $namaPembeli   = $pembeli['nama_pembeli'];
                $alamatPembeli = $pembeli['alamat_pembeli'];

                $conn->begin_transaction();

                // 1. Kembalikan stok item lama, lalu kurangi stok item baru
                adjustTanamanItemsStock($conn, $oldItems, +1, [
                    'sumber'     => 'Penjualan',
                    'aksi'       => 'Revisi Penjualan (Kembali)',
                    'keterangan' => "Edit Nota {$oldRow['kode_transaksi']} ({$namaPembeli})",
                ]);
                adjustTanamanItemsStock($conn, $newItems, -1, [
                    'sumber'     => 'Penjualan',
                    'aksi'       => 'Revisi Penjualan (Keluar)',
                    'keterangan' => "Edit Nota {$oldRow['kode_transaksi']} ({$namaPembeli})",
                ]);

                $updStmt = $conn->prepare("
                    UPDATE penjualan_tanaman
                    SET anggota_id = ?, nama_pembeli = ?, alamat_pembeli = ?, nama_tanaman = ?,
                        jumlah_kemasan = ?, jumlah_berat = ?, total_harga = ?,
                        metode_bayar = ?, tanggal_jual = ?, keterangan = ?, dicatat_oleh = ?
                    WHERE id = ?
                ");
                if ($updStmt) {
                    $updStmt->bind_param(
                        "isssssissssi",
                        $anggotaIdDb,
                        $namaPembeli,
                        $alamatPembeli,
                        $namaTanaman,
                        $jumlahKemasan,
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
                        savePenjualanTanamanItems($conn, $id, $newItems);
                        recordRiwayatPenjualanTanaman(
                            $conn,
                            $id,
                            $oldRow['kode_transaksi'],
                            'Diperbarui',
                            $namaPembeli,
                            $newItems,
                            $jumlahKemasan,
                            $jumlahBerat,
                            $totalHarga,
                            $metodeBayar,
                            $tanggalJual,
                            $keterangan
                        );

                        $conn->commit();
                        header("Location: " . appUrl("tanaman/?{$viewQuery}status=updated&name=" . urlencode($namaPembeli)));
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
        $oldStmt = $conn->prepare("SELECT * FROM penjualan_tanaman WHERE id = ? LIMIT 1");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRes = $oldStmt->get_result();
        $oldRow = $oldRes ? $oldRes->fetch_assoc() : null;
        $oldStmt->close();

        if ($oldRow) {
            $oldItems     = getPenjualanTanamanItemsById($conn, $id);
            $kemasanLabel = $oldRow['jumlah_kemasan'];

            $conn->begin_transaction();
            recordRiwayatPenjualanTanaman(
                $conn,
                null,
                $oldRow['kode_transaksi'],
                'Dibatalkan',
                formatTitleCase($oldRow['nama_pembeli']),
                $oldItems,
                $oldRow['jumlah_kemasan'],
                $oldRow['jumlah_berat'],
                (int)$oldRow['total_harga'],
                $oldRow['metode_bayar'],
                $oldRow['tanggal_jual'],
                'Transaksi penjualan dihapus dan stok tanaman dikembalikan'
            );

            $delStmt = $conn->prepare("DELETE FROM penjualan_tanaman WHERE id = ?");
            if ($delStmt) {
                $delStmt->bind_param("i", $id);
                if ($delStmt->execute()) {
                    $delStmt->close();
                    adjustTanamanItemsStock($conn, $oldItems, +1, [
                        'sumber'     => 'Penjualan',
                        'aksi'       => 'Hapus Penjualan',
                        'keterangan' => "Hapus Nota {$oldRow['kode_transaksi']} ({$oldRow['nama_pembeli']})",
                    ]);
                    $conn->commit();

                    header("Location: " . appUrl("tanaman/?{$viewQuery}status=deleted&kemasan=" . urlencode($kemasanLabel)));
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
