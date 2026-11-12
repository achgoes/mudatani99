<?php
/**
 * mudatani99 - CRUD Stok Pupuk (pengelolaan_pupuk)
 * File: pupuk/actions/stok.php
 */

if ($action === 'add') {
    $pupuk_id      = (int)($_POST['pupuk_id'] ?? 0);
    $jumlah_karung = formatTitleCase($_POST['jumlah_karung'] ?? '');
    $jumlah_berat  = formatTitleCase($_POST['jumlah_berat'] ?? '');
    $keterangan    = formatTitleCase($_POST['keterangan'] ?? '');

    $addKarung = parseKarungInt($jumlah_karung);
    $addBerat  = parseBeratFloat($jumlah_berat);

    if ($pupuk_id <= 0 || $addKarung <= 0 || $addBerat <= 0) {
        $feedbackMessage = "Nama Pupuk, Jumlah Karung (> 0), dan Jumlah Berat (> 0) wajib diisi dengan benar.";
        $feedbackType    = "danger";
    } else {
        if ($keterangan === '') {
            $keterangan = '-';
        }

        // Pastikan pupuk_id valid di master_produk dengan jenis = 'Pupuk'
        $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Pupuk' LIMIT 1");
        $cekStmt->bind_param("i", $pupuk_id);
        $cekStmt->execute();
        $cekRes = $cekStmt->get_result();
        $pupukRow = $cekRes ? $cekRes->fetch_assoc() : null;
        $cekStmt->close();

        if (!$pupukRow) {
            $feedbackMessage = "Nama pupuk yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
            $feedbackType    = "danger";
        } else {
            $nama_pupuk = formatTitleCase($pupukRow['nama_produk']);

            // Cek apakah produk ini SUDAH ADA di tabel stok (pengelolaan_pupuk)
            $cleanName = trim($nama_pupuk);
            $cekExist = $conn->prepare("
                SELECT id, pupuk_id, nama_pupuk, jumlah_karung, jumlah_berat, keterangan 
                FROM pengelolaan_pupuk 
                WHERE pupuk_id = ? OR (pupuk_id IS NULL AND LOWER(TRIM(nama_pupuk)) = LOWER(?))
                ORDER BY id ASC
            ");
            $cekExist->bind_param("is", $pupuk_id, $cleanName);
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

                $currKarung = parseKarungInt($targetRow['jumlah_karung']);
                $currBerat  = parseBeratFloat($targetRow['jumlah_berat']);

                if (count($existingRows) > 1) {
                    for ($i = 1; $i < count($existingRows); $i++) {
                        $dupId = (int)$existingRows[$i]['id'];
                        $currKarung += parseKarungInt($existingRows[$i]['jumlah_karung']);
                        $currBerat  += parseBeratFloat($existingRows[$i]['jumlah_berat']);

                        $conn->query("UPDATE hutang_pupuk SET pengelolaan_pupuk_id = $targetId WHERE pengelolaan_pupuk_id = $dupId");
                        $conn->query("UPDATE riwayat_hutang_pupuk SET pengelolaan_pupuk_id = $targetId WHERE pengelolaan_pupuk_id = $dupId");
                        $conn->query("DELETE FROM pengelolaan_pupuk WHERE id = $dupId");
                    }
                }

                $totalKarung = $currKarung + $addKarung;
                $totalBerat  = $currBerat + $addBerat;

                $newKarungStr = formatKarungStr($totalKarung);
                $newBeratStr  = formatBeratStr($totalBerat);

                $finalKeterangan = ($keterangan !== '-' && $keterangan !== '')
                    ? $keterangan
                    : ($targetRow['keterangan'] ?: '-');

                $updStmt = $conn->prepare("UPDATE pengelolaan_pupuk SET pupuk_id = ?, nama_pupuk = ?, jumlah_karung = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
                if ($updStmt) {
                    $updStmt->bind_param("issssi", $pupuk_id, $nama_pupuk, $newKarungStr, $newBeratStr, $finalKeterangan, $targetId);
                    if ($updStmt->execute()) {
                        $updStmt->close();
                        recordRiwayatStokPupuk(
                            $conn,
                            $targetId,
                            $pupuk_id,
                            $nama_pupuk,
                            'Kelola Stok',
                            'Tambah Akumulasi',
                            formatKarungStr($currKarung),
                            formatBeratStr($currBerat),
                            formatDeltaKarungStr($addKarung),
                            formatDeltaBeratStr($addBerat),
                            $newKarungStr,
                            $newBeratStr,
                            $keterangan
                        );
                        header("Location: " . appUrl("pupuk/?status=stock_added&name=" . urlencode($nama_pupuk) . "&add_k=" . $addKarung . "&tot_k=" . $totalKarung . "&tot_b=" . urlencode($newBeratStr)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menjumlahkan stok pupuk: " . $conn->error;
                        $feedbackType    = "danger";
                        $updStmt->close();
                    }
                }
            } else {
                // Produk belum ada stok sebelumnya: Buat baris baru
                $newKarungStr = formatKarungStr($addKarung);
                $newBeratStr  = formatBeratStr($addBerat);

                $insStmt = $conn->prepare("INSERT INTO pengelolaan_pupuk (pupuk_id, nama_pupuk, jumlah_karung, jumlah_berat, keterangan) VALUES (?, ?, ?, ?, ?)");
                if ($insStmt) {
                    $insStmt->bind_param("issss", $pupuk_id, $nama_pupuk, $newKarungStr, $newBeratStr, $keterangan);
                    if ($insStmt->execute()) {
                        $newStokId = (int)$insStmt->insert_id;
                        $insStmt->close();
                        recordRiwayatStokPupuk(
                            $conn,
                            $newStokId,
                            $pupuk_id,
                            $nama_pupuk,
                            'Kelola Stok',
                            'Tambah Baru',
                            '0 Karung',
                            '0 Kg',
                            formatDeltaKarungStr($addKarung),
                            formatDeltaBeratStr($addBerat),
                            $newKarungStr,
                            $newBeratStr,
                            $keterangan
                        );
                        header("Location: " . appUrl("pupuk/?status=added&name=" . urlencode($nama_pupuk)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menambahkan stok pupuk baru: " . $conn->error;
                        $feedbackType    = "danger";
                        $insStmt->close();
                    }
                }
            }
        }
    }
} elseif ($action === 'edit') {
    $id            = (int)($_POST['id'] ?? 0);
    $pupuk_id      = (int)($_POST['pupuk_id'] ?? 0);
    $jumlah_karung = formatTitleCase($_POST['jumlah_karung'] ?? '');
    $jumlah_berat  = formatTitleCase($_POST['jumlah_berat'] ?? '');
    $keterangan    = formatTitleCase($_POST['keterangan'] ?? '');

    if ($id <= 0 || $pupuk_id <= 0 || empty($jumlah_karung) || empty($jumlah_berat)) {
        $feedbackMessage = "ID, Nama Pupuk, Jumlah Karung, dan Jumlah Berat wajib diisi.";
        $feedbackType    = "danger";
    } else {
        if ($keterangan === '') {
            $keterangan = '-';
        }
        if (preg_match('/^[0-9]+([.,][0-9]+)?$/', $jumlah_karung)) {
            $jumlah_karung .= ' Karung';
        }
        if (preg_match('/^[0-9]+([.,][0-9]+)?$/', $jumlah_berat)) {
            $jumlah_berat .= ' Kg';
        }

        $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Pupuk' LIMIT 1");
        $cekStmt->bind_param("i", $pupuk_id);
        $cekStmt->execute();
        $cekRes = $cekStmt->get_result();
        $pupukRow = $cekRes ? $cekRes->fetch_assoc() : null;
        $cekStmt->close();

        if (!$pupukRow) {
            $feedbackMessage = "Nama pupuk yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
            $feedbackType    = "danger";
        } else {
            $oldStokStmt = $conn->prepare("SELECT nama_pupuk, jumlah_karung, jumlah_berat FROM pengelolaan_pupuk WHERE id = ? LIMIT 1");
            $oldStokRow  = null;
            if ($oldStokStmt) {
                $oldStokStmt->bind_param("i", $id);
                $oldStokStmt->execute();
                $oldStokRes = $oldStokStmt->get_result();
                $oldStokRow = $oldStokRes ? $oldStokRes->fetch_assoc() : null;
                $oldStokStmt->close();
            }

            $nama_pupuk = formatTitleCase($pupukRow['nama_produk']);
            $stmt = $conn->prepare("UPDATE pengelolaan_pupuk SET pupuk_id = ?, nama_pupuk = ?, jumlah_karung = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("issssi", $pupuk_id, $nama_pupuk, $jumlah_karung, $jumlah_berat, $keterangan, $id);
                if ($stmt->execute()) {
                    $oldK = $oldStokRow ? parseKarungInt($oldStokRow['jumlah_karung']) : parseKarungInt($jumlah_karung);
                    $oldB = $oldStokRow ? parseBeratFloat($oldStokRow['jumlah_berat']) : parseBeratFloat($jumlah_berat);
                    $newK = parseKarungInt($jumlah_karung);
                    $newB = parseBeratFloat($jumlah_berat);
                    recordRiwayatStokPupuk(
                        $conn,
                        $id,
                        $pupuk_id,
                        $nama_pupuk,
                        'Kelola Stok',
                        'Edit Stok',
                        formatKarungStr($oldK),
                        formatBeratStr($oldB),
                        formatDeltaKarungStr($newK - $oldK),
                        formatDeltaBeratStr($newB - $oldB),
                        $jumlah_karung,
                        $jumlah_berat,
                        $keterangan
                    );

                    // Sinkronkan nama pupuk pada hutang_pupuk & riwayat_hutang_pupuk
                    $syncH = $conn->prepare("UPDATE hutang_pupuk SET pupuk_id = ?, nama_pupuk = ? WHERE pengelolaan_pupuk_id = ?");
                    if ($syncH) {
                        $syncH->bind_param("isi", $pupuk_id, $nama_pupuk, $id);
                        $syncH->execute();
                        $syncH->close();
                    }
                    $syncR = $conn->prepare("UPDATE riwayat_hutang_pupuk SET pupuk_id = ?, nama_pupuk = ? WHERE pengelolaan_pupuk_id = ?");
                    if ($syncR) {
                        $syncR->bind_param("isi", $pupuk_id, $nama_pupuk, $id);
                        $syncR->execute();
                        $syncR->close();
                    }
                    $syncB = $conn->prepare("
                        UPDATE pembayaran_hutang_pupuk b
                        INNER JOIN hutang_pupuk h ON b.hutang_id = h.id
                        SET b.nama_pupuk = ?
                        WHERE h.pengelolaan_pupuk_id = ?
                    ");
                    if ($syncB) {
                        $syncB->bind_param("si", $nama_pupuk, $id);
                        $syncB->execute();
                        $syncB->close();
                    }

                    header("Location: " . appUrl("pupuk/?status=updated&name=" . urlencode($nama_pupuk)));
                    exit;
                } else {
                    $feedbackMessage = "Gagal memperbarui data pupuk: " . $conn->error;
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
            SELECT p.pupuk_id, COALESCE(m.nama_produk, p.nama_pupuk) AS nama_pupuk, p.jumlah_karung, p.jumlah_berat, p.keterangan
            FROM pengelolaan_pupuk p
            LEFT JOIN master_produk m ON p.pupuk_id = m.id AND m.jenis = 'Pupuk'
            WHERE p.id = ? LIMIT 1
        ");
        $oldStokRow = null;
        if ($oldStokStmt) {
            $oldStokStmt->bind_param("i", $id);
            $oldStokStmt->execute();
            $oldStokRes = $oldStokStmt->get_result();
            $oldStokRow = $oldStokRes ? $oldStokRes->fetch_assoc() : null;
            $oldStokStmt->close();
        }

        $stmt = $conn->prepare("DELETE FROM pengelolaan_pupuk WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($oldStokRow) {
                    $oldK = parseKarungInt($oldStokRow['jumlah_karung']);
                    $oldB = parseBeratFloat($oldStokRow['jumlah_berat']);
                    recordRiwayatStokPupuk(
                        $conn,
                        null,
                        !empty($oldStokRow['pupuk_id']) ? (int)$oldStokRow['pupuk_id'] : null,
                        (string)$oldStokRow['nama_pupuk'],
                        'Kelola Stok',
                        'Hapus Stok',
                        formatKarungStr($oldK),
                        formatBeratStr($oldB),
                        formatDeltaKarungStr(-$oldK),
                        formatDeltaBeratStr(-$oldB),
                        '0 Karung',
                        '0 Kg',
                        'Data stok pupuk dihapus dari daftar stok'
                    );
                }
                header("Location: " . appUrl("pupuk/?status=deleted"));
                exit;
            } else {
                $feedbackMessage = "Gagal menghapus data pupuk: " . $conn->error;
                $feedbackType    = "danger";
            }
            $stmt->close();
        }
    }
}
