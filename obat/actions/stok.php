<?php
/**
 * mudatani99 - CRUD Stok Obat (pengelolaan_obat)
 * File: obat/actions/stok.php
 */

$allowedSatuanObat = ['Pcs', 'Botol'];

if ($action === 'add') {
    $obat_id        = (int)($_POST['obat_id'] ?? 0);
    $jumlah_angka   = trim($_POST['jumlah_angka'] ?? '');
    $satuan_kemasan = trim($_POST['satuan_kemasan'] ?? '');
    $jumlah_berat   = formatTitleCase($_POST['jumlah_berat'] ?? '');
    $keterangan     = formatTitleCase($_POST['keterangan'] ?? '');

    $addKemasan = parseKemasanInt($jumlah_angka);
    $addBerat   = parseBeratFloat($jumlah_berat);

    if ($obat_id <= 0 || $addKemasan <= 0 || !in_array($satuan_kemasan, $allowedSatuanObat, true) || $addBerat <= 0) {
        $feedbackMessage = "Nama Obat, Jumlah Kemasan (> 0 Pcs / Botol), dan Jumlah Berat (> 0) wajib diisi dengan benar.";
        $feedbackType    = "danger";
    } else {
        if ($keterangan === '') {
            $keterangan = '-';
        }

        // Pastikan obat_id valid di master_produk dengan jenis = 'Obat'
        $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Obat' LIMIT 1");
        $cekStmt->bind_param("i", $obat_id);
        $cekStmt->execute();
        $cekRes = $cekStmt->get_result();
        $obatRow = $cekRes ? $cekRes->fetch_assoc() : null;
        $cekStmt->close();

        if (!$obatRow) {
            $feedbackMessage = "Nama obat yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
            $feedbackType    = "danger";
        } else {
            $nama_obat = formatTitleCase($obatRow['nama_produk']);

            // Cek apakah produk ini SUDAH ADA di tabel stok (pengelolaan_obat)
            $cleanName = trim($nama_obat);
            $cekExist = $conn->prepare("
                SELECT id, obat_id, nama_obat, jumlah_kemasan, jumlah_berat, keterangan
                FROM pengelolaan_obat
                WHERE obat_id = ? OR (obat_id IS NULL AND LOWER(TRIM(nama_obat)) = LOWER(?))
                ORDER BY id ASC
            ");
            $cekExist->bind_param("is", $obat_id, $cleanName);
            $cekExist->execute();
            $resExist = $cekExist->get_result();
            $existingRows = [];
            while ($r = $resExist->fetch_assoc()) {
                $existingRows[] = $r;
            }
            $cekExist->close();

            if (!empty($existingRows)) {
                // Akumulasikan ke baris yang sudah ada, TIDAK membuat list nomor baru
                $targetRow = $existingRows[0];
                $targetId  = (int)$targetRow['id'];

                $currKemasan = parseKemasanInt($targetRow['jumlah_kemasan']);
                $currSatuan  = parseSatuanKemasan($targetRow['jumlah_kemasan'], $satuan_kemasan);
                $currBerat   = parseBeratFloat($targetRow['jumlah_berat']);

                if (count($existingRows) > 1) {
                    for ($i = 1; $i < count($existingRows); $i++) {
                        $dupId = (int)$existingRows[$i]['id'];
                        $currKemasan += parseKemasanInt($existingRows[$i]['jumlah_kemasan']);
                        $currBerat   += parseBeratFloat($existingRows[$i]['jumlah_berat']);

                        $conn->query("UPDATE hutang_obat SET pengelolaan_obat_id = $targetId WHERE pengelolaan_obat_id = $dupId");
                        $conn->query("UPDATE riwayat_hutang_obat SET pengelolaan_obat_id = $targetId WHERE pengelolaan_obat_id = $dupId");
                        $conn->query("UPDATE hutang_obat_items SET pengelolaan_obat_id = $targetId WHERE pengelolaan_obat_id = $dupId");
                        $conn->query("UPDATE penjualan_obat_items SET pengelolaan_obat_id = $targetId WHERE pengelolaan_obat_id = $dupId");
                        $conn->query("DELETE FROM pengelolaan_obat WHERE id = $dupId");
                    }
                }

                $totalKemasan = $currKemasan + $addKemasan;
                $totalBerat   = $currBerat + $addBerat;

                $newKemasanStr = formatKemasanStr($totalKemasan, $currSatuan);
                $newBeratStr   = formatBeratStr($totalBerat);

                $finalKeterangan = ($keterangan !== '-' && $keterangan !== '')
                    ? $keterangan
                    : ($targetRow['keterangan'] ?: '-');

                $updStmt = $conn->prepare("UPDATE pengelolaan_obat SET obat_id = ?, nama_obat = ?, jumlah_kemasan = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
                if ($updStmt) {
                    $updStmt->bind_param("issssi", $obat_id, $nama_obat, $newKemasanStr, $newBeratStr, $finalKeterangan, $targetId);
                    if ($updStmt->execute()) {
                        $updStmt->close();
                        recordRiwayatStokObat(
                            $conn,
                            $targetId,
                            $obat_id,
                            $nama_obat,
                            'Kelola Stok',
                            'Tambah Akumulasi',
                            formatKemasanStr($currKemasan, $currSatuan),
                            formatBeratStr($currBerat),
                            formatDeltaKemasanStr($addKemasan, $currSatuan),
                            formatDeltaBeratStr($addBerat),
                            $newKemasanStr,
                            $newBeratStr,
                            $keterangan
                        );
                        header("Location: " . appUrl("obat/?status=stock_added&name=" . urlencode($nama_obat) . "&add_k=" . $addKemasan . "&satuan=" . urlencode($currSatuan) . "&tot_k=" . $totalKemasan . "&tot_b=" . urlencode($newBeratStr)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menjumlahkan stok obat: " . $conn->error;
                        $feedbackType    = "danger";
                        $updStmt->close();
                    }
                }
            } else {
                // Produk belum ada stok sebelumnya: Buat baris baru
                $newKemasanStr = formatKemasanStr($addKemasan, $satuan_kemasan);
                $newBeratStr   = formatBeratStr($addBerat);

                $insStmt = $conn->prepare("INSERT INTO pengelolaan_obat (obat_id, nama_obat, jumlah_kemasan, jumlah_berat, keterangan) VALUES (?, ?, ?, ?, ?)");
                if ($insStmt) {
                    $insStmt->bind_param("issss", $obat_id, $nama_obat, $newKemasanStr, $newBeratStr, $keterangan);
                    if ($insStmt->execute()) {
                        $newStokId = (int)$insStmt->insert_id;
                        $insStmt->close();
                        recordRiwayatStokObat(
                            $conn,
                            $newStokId,
                            $obat_id,
                            $nama_obat,
                            'Kelola Stok',
                            'Tambah Baru',
                            '0 ' . $satuan_kemasan,
                            '0 Kg',
                            formatDeltaKemasanStr($addKemasan, $satuan_kemasan),
                            formatDeltaBeratStr($addBerat),
                            $newKemasanStr,
                            $newBeratStr,
                            $keterangan
                        );
                        header("Location: " . appUrl("obat/?status=added&name=" . urlencode($nama_obat)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menambahkan stok obat baru: " . $conn->error;
                        $feedbackType    = "danger";
                        $insStmt->close();
                    }
                }
            }
        }
    }
} elseif ($action === 'edit') {
    $id             = (int)($_POST['id'] ?? 0);
    $obat_id        = (int)($_POST['obat_id'] ?? 0);
    $jumlah_angka   = trim($_POST['jumlah_angka'] ?? '');
    $satuan_kemasan = trim($_POST['satuan_kemasan'] ?? '');
    $jumlah_berat   = formatTitleCase($_POST['jumlah_berat'] ?? '');
    $keterangan     = formatTitleCase($_POST['keterangan'] ?? '');

    $editKemasan = parseKemasanInt($jumlah_angka);
    $editBerat   = parseBeratFloat($jumlah_berat);

    if ($id <= 0 || $obat_id <= 0 || $editKemasan <= 0 || !in_array($satuan_kemasan, $allowedSatuanObat, true) || $editBerat <= 0) {
        $feedbackMessage = "ID, Nama Obat, Jumlah Kemasan (> 0 Pcs / Botol), dan Jumlah Berat (> 0) wajib diisi.";
        $feedbackType    = "danger";
    } else {
        if ($keterangan === '') {
            $keterangan = '-';
        }
        $jumlah_kemasan = formatKemasanStr($editKemasan, $satuan_kemasan);
        $jumlah_berat   = formatBeratStr($editBerat);

        $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Obat' LIMIT 1");
        $cekStmt->bind_param("i", $obat_id);
        $cekStmt->execute();
        $cekRes = $cekStmt->get_result();
        $obatRow = $cekRes ? $cekRes->fetch_assoc() : null;
        $cekStmt->close();

        if (!$obatRow) {
            $feedbackMessage = "Nama obat yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
            $feedbackType    = "danger";
        } else {
            $oldStokStmt = $conn->prepare("SELECT nama_obat, jumlah_kemasan, jumlah_berat FROM pengelolaan_obat WHERE id = ? LIMIT 1");
            $oldStokRow  = null;
            if ($oldStokStmt) {
                $oldStokStmt->bind_param("i", $id);
                $oldStokStmt->execute();
                $oldStokRes = $oldStokStmt->get_result();
                $oldStokRow = $oldStokRes ? $oldStokRes->fetch_assoc() : null;
                $oldStokStmt->close();
            }

            $nama_obat = formatTitleCase($obatRow['nama_produk']);
            $stmt = $conn->prepare("UPDATE pengelolaan_obat SET obat_id = ?, nama_obat = ?, jumlah_kemasan = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("issssi", $obat_id, $nama_obat, $jumlah_kemasan, $jumlah_berat, $keterangan, $id);
                if ($stmt->execute()) {
                    $oldK       = $oldStokRow ? parseKemasanInt($oldStokRow['jumlah_kemasan']) : $editKemasan;
                    $oldSatuan  = $oldStokRow ? parseSatuanKemasan($oldStokRow['jumlah_kemasan'], $satuan_kemasan) : $satuan_kemasan;
                    $oldB       = $oldStokRow ? parseBeratFloat($oldStokRow['jumlah_berat']) : $editBerat;
                    recordRiwayatStokObat(
                        $conn,
                        $id,
                        $obat_id,
                        $nama_obat,
                        'Kelola Stok',
                        'Edit Stok',
                        formatKemasanStr($oldK, $oldSatuan),
                        formatBeratStr($oldB),
                        formatDeltaKemasanStr($editKemasan - $oldK, $satuan_kemasan),
                        formatDeltaBeratStr($editBerat - $oldB),
                        $jumlah_kemasan,
                        $jumlah_berat,
                        $keterangan
                    );

                    // Sinkronkan nama obat pada hutang_obat & riwayat_hutang_obat
                    $syncH = $conn->prepare("UPDATE hutang_obat SET obat_id = ?, nama_obat = ? WHERE pengelolaan_obat_id = ?");
                    if ($syncH) {
                        $syncH->bind_param("isi", $obat_id, $nama_obat, $id);
                        $syncH->execute();
                        $syncH->close();
                    }
                    $syncR = $conn->prepare("UPDATE riwayat_hutang_obat SET obat_id = ?, nama_obat = ? WHERE pengelolaan_obat_id = ?");
                    if ($syncR) {
                        $syncR->bind_param("isi", $obat_id, $nama_obat, $id);
                        $syncR->execute();
                        $syncR->close();
                    }
                    $syncB = $conn->prepare("
                        UPDATE pembayaran_hutang_obat b
                        INNER JOIN hutang_obat h ON b.hutang_id = h.id
                        SET b.nama_obat = ?
                        WHERE h.pengelolaan_obat_id = ?
                    ");
                    if ($syncB) {
                        $syncB->bind_param("si", $nama_obat, $id);
                        $syncB->execute();
                        $syncB->close();
                    }

                    header("Location: " . appUrl("obat/?status=updated&name=" . urlencode($nama_obat)));
                    exit;
                } else {
                    $feedbackMessage = "Gagal memperbarui data obat: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }
} elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $oldStokStmt = $conn->prepare("
            SELECT o.obat_id, COALESCE(m.nama_produk, o.nama_obat) AS nama_obat, o.jumlah_kemasan, o.jumlah_berat, o.keterangan
            FROM pengelolaan_obat o
            LEFT JOIN master_produk m ON o.obat_id = m.id AND m.jenis = 'Obat'
            WHERE o.id = ? LIMIT 1
        ");
        $oldStokRow = null;
        if ($oldStokStmt) {
            $oldStokStmt->bind_param("i", $id);
            $oldStokStmt->execute();
            $oldStokRes = $oldStokStmt->get_result();
            $oldStokRow = $oldStokRes ? $oldStokRes->fetch_assoc() : null;
            $oldStokStmt->close();
        }

        $stmt = $conn->prepare("DELETE FROM pengelolaan_obat WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($oldStokRow) {
                    $oldK      = parseKemasanInt($oldStokRow['jumlah_kemasan']);
                    $oldSatuan = parseSatuanKemasan($oldStokRow['jumlah_kemasan'], 'Kemasan');
                    $oldB      = parseBeratFloat($oldStokRow['jumlah_berat']);
                    recordRiwayatStokObat(
                        $conn,
                        null,
                        !empty($oldStokRow['obat_id']) ? (int)$oldStokRow['obat_id'] : null,
                        (string)$oldStokRow['nama_obat'],
                        'Kelola Stok',
                        'Hapus Stok',
                        formatKemasanStr($oldK, $oldSatuan),
                        formatBeratStr($oldB),
                        formatDeltaKemasanStr(-$oldK, $oldSatuan),
                        formatDeltaBeratStr(-$oldB),
                        '0 ' . $oldSatuan,
                        '0 Kg',
                        'Data stok obat dihapus dari daftar stok'
                    );
                }
                header("Location: " . appUrl("obat/?status=deleted"));
                exit;
            } else {
                $feedbackMessage = "Gagal menghapus data obat: " . $conn->error;
                $feedbackType    = "danger";
            }
            $stmt->close();
        }
    }
}
