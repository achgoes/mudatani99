<?php
/**
 * mudatani99 - CRUD Stok Tanaman (pengelolaan_tanaman)
 * File: tanaman/actions/stok.php
 */

if ($action === 'add') {
    $tanaman_id   = (int)($_POST['tanaman_id'] ?? 0);
    $jumlah_angka = trim($_POST['jumlah_angka'] ?? '');
    $jumlah_berat = formatTitleCase($_POST['jumlah_berat'] ?? '');
    $keterangan   = formatTitleCase($_POST['keterangan'] ?? '');

    $addKemasan = parseKemasanInt($jumlah_angka);
    $addBerat   = parseBeratFloat($jumlah_berat);

    if ($tanaman_id <= 0 || $addKemasan <= 0 || $addBerat <= 0) {
        $feedbackMessage = "Nama Tanaman, Jumlah Kemasan (> 0 Pcs), dan Jumlah Berat (> 0) wajib diisi dengan benar.";
        $feedbackType    = "danger";
    } else {
        if ($keterangan === '') {
            $keterangan = '-';
        }

        // Pastikan tanaman_id valid di master_produk dengan jenis = 'Tanaman'
        $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
        $cekStmt->bind_param("i", $tanaman_id);
        $cekStmt->execute();
        $cekRes = $cekStmt->get_result();
        $tanamanRow = $cekRes ? $cekRes->fetch_assoc() : null;
        $cekStmt->close();

        if (!$tanamanRow) {
            $feedbackMessage = "Nama tanaman yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
            $feedbackType    = "danger";
        } else {
            $nama_tanaman = formatTitleCase($tanamanRow['nama_produk']);

            // Cek apakah produk ini SUDAH ADA di tabel stok (pengelolaan_tanaman)
            $cleanName = trim($nama_tanaman);
            $cekExist = $conn->prepare("
                SELECT id, tanaman_id, nama_tanaman, jumlah_kemasan, jumlah_berat, keterangan
                FROM pengelolaan_tanaman
                WHERE tanaman_id = ? OR (tanaman_id IS NULL AND LOWER(TRIM(nama_tanaman)) = LOWER(?))
                ORDER BY id ASC
            ");
            $cekExist->bind_param("is", $tanaman_id, $cleanName);
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
                $currBerat   = parseBeratFloat($targetRow['jumlah_berat']);

                if (count($existingRows) > 1) {
                    for ($i = 1; $i < count($existingRows); $i++) {
                        $dupId = (int)$existingRows[$i]['id'];
                        $currKemasan += parseKemasanInt($existingRows[$i]['jumlah_kemasan']);
                        $currBerat   += parseBeratFloat($existingRows[$i]['jumlah_berat']);

                        $conn->query("UPDATE hutang_tanaman SET pengelolaan_tanaman_id = $targetId WHERE pengelolaan_tanaman_id = $dupId");
                        $conn->query("UPDATE riwayat_hutang_tanaman SET pengelolaan_tanaman_id = $targetId WHERE pengelolaan_tanaman_id = $dupId");
                        $conn->query("UPDATE hutang_tanaman_items SET pengelolaan_tanaman_id = $targetId WHERE pengelolaan_tanaman_id = $dupId");
                        $conn->query("UPDATE penjualan_tanaman_items SET pengelolaan_tanaman_id = $targetId WHERE pengelolaan_tanaman_id = $dupId");
                        $conn->query("DELETE FROM pengelolaan_tanaman WHERE id = $dupId");
                    }
                }

                $totalKemasan = $currKemasan + $addKemasan;
                $totalBerat   = $currBerat + $addBerat;

                $newKemasanStr = formatKemasanStr($totalKemasan);
                $newBeratStr   = formatBeratStr($totalBerat);

                $finalKeterangan = ($keterangan !== '-' && $keterangan !== '')
                    ? $keterangan
                    : ($targetRow['keterangan'] ?: '-');

                $updStmt = $conn->prepare("UPDATE pengelolaan_tanaman SET tanaman_id = ?, nama_tanaman = ?, jumlah_kemasan = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
                if ($updStmt) {
                    $updStmt->bind_param("issssi", $tanaman_id, $nama_tanaman, $newKemasanStr, $newBeratStr, $finalKeterangan, $targetId);
                    if ($updStmt->execute()) {
                        $updStmt->close();
                        recordRiwayatStokTanaman(
                            $conn,
                            $targetId,
                            $tanaman_id,
                            $nama_tanaman,
                            'Kelola Stok',
                            'Tambah Akumulasi',
                            formatKemasanStr($currKemasan),
                            formatBeratStr($currBerat),
                            formatDeltaKemasanStr($addKemasan),
                            formatDeltaBeratStr($addBerat),
                            $newKemasanStr,
                            $newBeratStr,
                            $keterangan
                        );
                        header("Location: " . appUrl("tanaman/?status=stock_added&name=" . urlencode($nama_tanaman) . "&add_k=" . $addKemasan . "&tot_k=" . $totalKemasan . "&tot_b=" . urlencode($newBeratStr)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menjumlahkan stok tanaman: " . $conn->error;
                        $feedbackType    = "danger";
                        $updStmt->close();
                    }
                }
            } else {
                // Produk belum ada stok sebelumnya: Buat baris baru
                $newKemasanStr = formatKemasanStr($addKemasan);
                $newBeratStr   = formatBeratStr($addBerat);

                $insStmt = $conn->prepare("INSERT INTO pengelolaan_tanaman (tanaman_id, nama_tanaman, jumlah_kemasan, jumlah_berat, keterangan) VALUES (?, ?, ?, ?, ?)");
                if ($insStmt) {
                    $insStmt->bind_param("issss", $tanaman_id, $nama_tanaman, $newKemasanStr, $newBeratStr, $keterangan);
                    if ($insStmt->execute()) {
                        $newStokId = (int)$insStmt->insert_id;
                        $insStmt->close();
                        recordRiwayatStokTanaman(
                            $conn,
                            $newStokId,
                            $tanaman_id,
                            $nama_tanaman,
                            'Kelola Stok',
                            'Tambah Baru',
                            '0 Pcs',
                            '0 Kg',
                            formatDeltaKemasanStr($addKemasan),
                            formatDeltaBeratStr($addBerat),
                            $newKemasanStr,
                            $newBeratStr,
                            $keterangan
                        );
                        header("Location: " . appUrl("tanaman/?status=added&name=" . urlencode($nama_tanaman)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menambahkan stok tanaman baru: " . $conn->error;
                        $feedbackType    = "danger";
                        $insStmt->close();
                    }
                }
            }
        }
    }
} elseif ($action === 'edit') {
    $id           = (int)($_POST['id'] ?? 0);
    $tanaman_id   = (int)($_POST['tanaman_id'] ?? 0);
    $jumlah_angka = trim($_POST['jumlah_angka'] ?? '');
    $jumlah_berat = formatTitleCase($_POST['jumlah_berat'] ?? '');
    $keterangan   = formatTitleCase($_POST['keterangan'] ?? '');

    $editKemasan = parseKemasanInt($jumlah_angka);
    $editBerat   = parseBeratFloat($jumlah_berat);

    if ($id <= 0 || $tanaman_id <= 0 || $editKemasan <= 0 || $editBerat <= 0) {
        $feedbackMessage = "ID, Nama Tanaman, Jumlah Kemasan (> 0 Pcs), dan Jumlah Berat (> 0) wajib diisi.";
        $feedbackType    = "danger";
    } else {
        if ($keterangan === '') {
            $keterangan = '-';
        }
        $jumlah_kemasan = formatKemasanStr($editKemasan);
        $jumlah_berat   = formatBeratStr($editBerat);

        $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
        $cekStmt->bind_param("i", $tanaman_id);
        $cekStmt->execute();
        $cekRes = $cekStmt->get_result();
        $tanamanRow = $cekRes ? $cekRes->fetch_assoc() : null;
        $cekStmt->close();

        if (!$tanamanRow) {
            $feedbackMessage = "Nama tanaman yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
            $feedbackType    = "danger";
        } else {
            $oldStokStmt = $conn->prepare("SELECT nama_tanaman, jumlah_kemasan, jumlah_berat FROM pengelolaan_tanaman WHERE id = ? LIMIT 1");
            $oldStokRow  = null;
            if ($oldStokStmt) {
                $oldStokStmt->bind_param("i", $id);
                $oldStokStmt->execute();
                $oldStokRes = $oldStokStmt->get_result();
                $oldStokRow = $oldStokRes ? $oldStokRes->fetch_assoc() : null;
                $oldStokStmt->close();
            }

            $nama_tanaman = formatTitleCase($tanamanRow['nama_produk']);
            $stmt = $conn->prepare("UPDATE pengelolaan_tanaman SET tanaman_id = ?, nama_tanaman = ?, jumlah_kemasan = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("issssi", $tanaman_id, $nama_tanaman, $jumlah_kemasan, $jumlah_berat, $keterangan, $id);
                if ($stmt->execute()) {
                    $oldK = $oldStokRow ? parseKemasanInt($oldStokRow['jumlah_kemasan']) : $editKemasan;
                    $oldB = $oldStokRow ? parseBeratFloat($oldStokRow['jumlah_berat']) : $editBerat;
                    recordRiwayatStokTanaman(
                        $conn,
                        $id,
                        $tanaman_id,
                        $nama_tanaman,
                        'Kelola Stok',
                        'Edit Stok',
                        formatKemasanStr($oldK),
                        formatBeratStr($oldB),
                        formatDeltaKemasanStr($editKemasan - $oldK),
                        formatDeltaBeratStr($editBerat - $oldB),
                        $jumlah_kemasan,
                        $jumlah_berat,
                        $keterangan
                    );

                    // Sinkronkan nama tanaman pada hutang_tanaman & riwayat_hutang_tanaman
                    $syncH = $conn->prepare("UPDATE hutang_tanaman SET tanaman_id = ?, nama_tanaman = ? WHERE pengelolaan_tanaman_id = ?");
                    if ($syncH) {
                        $syncH->bind_param("isi", $tanaman_id, $nama_tanaman, $id);
                        $syncH->execute();
                        $syncH->close();
                    }
                    $syncR = $conn->prepare("UPDATE riwayat_hutang_tanaman SET tanaman_id = ?, nama_tanaman = ? WHERE pengelolaan_tanaman_id = ?");
                    if ($syncR) {
                        $syncR->bind_param("isi", $tanaman_id, $nama_tanaman, $id);
                        $syncR->execute();
                        $syncR->close();
                    }
                    $syncB = $conn->prepare("
                        UPDATE pembayaran_hutang_tanaman b
                        INNER JOIN hutang_tanaman h ON b.hutang_id = h.id
                        SET b.nama_tanaman = ?
                        WHERE h.pengelolaan_tanaman_id = ?
                    ");
                    if ($syncB) {
                        $syncB->bind_param("si", $nama_tanaman, $id);
                        $syncB->execute();
                        $syncB->close();
                    }

                    header("Location: " . appUrl("tanaman/?status=updated&name=" . urlencode($nama_tanaman)));
                    exit;
                } else {
                    $feedbackMessage = "Gagal memperbarui data tanaman: " . $conn->error;
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
            SELECT t.tanaman_id, COALESCE(m.nama_produk, t.nama_tanaman) AS nama_tanaman, t.jumlah_kemasan, t.jumlah_berat, t.keterangan
            FROM pengelolaan_tanaman t
            LEFT JOIN master_produk m ON t.tanaman_id = m.id AND m.jenis = 'Tanaman'
            WHERE t.id = ? LIMIT 1
        ");
        $oldStokRow = null;
        if ($oldStokStmt) {
            $oldStokStmt->bind_param("i", $id);
            $oldStokStmt->execute();
            $oldStokRes = $oldStokStmt->get_result();
            $oldStokRow = $oldStokRes ? $oldStokRes->fetch_assoc() : null;
            $oldStokStmt->close();
        }

        $stmt = $conn->prepare("DELETE FROM pengelolaan_tanaman WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($oldStokRow) {
                    $oldK = parseKemasanInt($oldStokRow['jumlah_kemasan']);
                    $oldB = parseBeratFloat($oldStokRow['jumlah_berat']);
                    recordRiwayatStokTanaman(
                        $conn,
                        null,
                        !empty($oldStokRow['tanaman_id']) ? (int)$oldStokRow['tanaman_id'] : null,
                        (string)$oldStokRow['nama_tanaman'],
                        'Kelola Stok',
                        'Hapus Stok',
                        formatKemasanStr($oldK),
                        formatBeratStr($oldB),
                        formatDeltaKemasanStr(-$oldK),
                        formatDeltaBeratStr(-$oldB),
                        '0 Pcs',
                        '0 Kg',
                        'Data stok tanaman dihapus dari daftar stok'
                    );
                }
                header("Location: " . appUrl("tanaman/?status=deleted"));
                exit;
            } else {
                $feedbackMessage = "Gagal menghapus data tanaman: " . $conn->error;
                $feedbackType    = "danger";
            }
            $stmt->close();
        }
    }
}
