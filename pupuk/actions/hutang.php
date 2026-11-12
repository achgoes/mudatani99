<?php
/**
 * mudatani99 - CRUD Hutang Piutang Pupuk (hutang_pupuk)
 * File: pupuk/actions/hutang.php
 */

if ($action === 'add_hutang') {
    $activeView           = ($returnView === 'kelola') ? 'hutang' : $returnView;
    $anggota_id           = (int)($_POST['anggota_id'] ?? 0);
    $pengelolaan_pupuk_id = (int)($_POST['pengelolaan_pupuk_id'] ?? 0);
    $jumlah_karung_raw    = trim($_POST['jumlah_karung'] ?? '');
    $jumlah_berat_raw     = trim($_POST['jumlah_berat'] ?? '');
    $harga_raw            = trim($_POST['harga'] ?? ($_POST['jumlah_harga'] ?? ''));
    $jumlah_hutang_raw    = trim($_POST['jumlah_hutang'] ?? '');
    $sisa_hutang_raw      = trim($_POST['sisa_hutang'] ?? '');
    $tanggal_raw          = trim($_POST['tanggal'] ?? '');
    $keterangan           = formatTitleCase($_POST['keterangan'] ?? '');
    $status               = $_POST['status'] ?? 'Belum Lunas';

    if (!in_array($status, ['Belum Lunas', 'Lunas'], true)) {
        $status = 'Belum Lunas';
    }

    $reqKarung        = parseKarungInt($jumlah_karung_raw);
    $reqBerat         = parseBeratFloat($jumlah_berat_raw);
    $harga            = formatRupiahStr($harga_raw);
    $hargaFloat       = parseRupiahFloat($harga);
    $calcTotalHtg     = $reqKarung * $hargaFloat;
    $jumlah_hutang    = ($jumlah_hutang_raw !== '' && parseRupiahFloat($jumlah_hutang_raw) > 0)
        ? formatRupiahStr($jumlah_hutang_raw)
        : formatRupiahStr($calcTotalHtg);
    $totalHutangFloat = parseRupiahFloat($jumlah_hutang);

    if ($status === 'Lunas') {
        $sisa_hutang   = 'Rp 0';
        $tanggal_lunas = date('Y-m-d H:i:s');
    } else {
        $sisaFloat     = ($sisa_hutang_raw !== '') ? parseRupiahFloat($sisa_hutang_raw) : $totalHutangFloat;
        if ($sisaFloat <= 0 && $totalHutangFloat > 0) {
            $sisaFloat = $totalHutangFloat;
        } elseif ($totalHutangFloat > 0 && $sisaFloat > $totalHutangFloat) {
            $sisaFloat = $totalHutangFloat;
        }
        $sisa_hutang   = formatRupiahStr($sisaFloat);
        $tanggal_lunas = null;
    }

    $tanggal = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_raw) ? $tanggal_raw : date('Y-m-d');

    if ($anggota_id <= 0 || $pengelolaan_pupuk_id <= 0 || $reqKarung <= 0 || $reqBerat <= 0 || $hargaFloat <= 0 || $totalHutangFloat <= 0) {
        $feedbackMessage = "Nama Petani, Nama Pupuk, Jumlah Karung (> 0), Jumlah Berat (> 0), dan Harga Satuan (> 0) wajib diisi dengan benar.";
        $feedbackType    = "danger";
    } else {
        if ($keterangan === '') {
            $keterangan = '-';
        }
        $keterangan = mb_substr($keterangan, 0, 255, 'UTF-8');

        // Ambil data petani dari anggota_petani
        $petaniStmt = $conn->prepare("SELECT id, nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
        $petaniStmt->bind_param("i", $anggota_id);
        $petaniStmt->execute();
        $petaniRes = $petaniStmt->get_result();
        $petaniRow = $petaniRes ? $petaniRes->fetch_assoc() : null;
        $petaniStmt->close();

        // Ambil data stok dari pengelolaan_pupuk
        $stokStmt = $conn->prepare("
            SELECT p.id, p.pupuk_id, COALESCE(m.nama_produk, p.nama_pupuk) AS nama_pupuk, p.jumlah_karung, p.jumlah_berat
            FROM pengelolaan_pupuk p
            LEFT JOIN master_produk m ON p.pupuk_id = m.id AND m.jenis = 'Pupuk'
            WHERE p.id = ? LIMIT 1
        ");
        $stokStmt->bind_param("i", $pengelolaan_pupuk_id);
        $stokStmt->execute();
        $stokRes = $stokStmt->get_result();
        $stokRow = $stokRes ? $stokRes->fetch_assoc() : null;
        $stokStmt->close();

        if (!$petaniRow) {
            $feedbackMessage = "Nama petani tidak ditemukan pada Daftar Anggota Petani.";
            $feedbackType    = "danger";
        } elseif (!$stokRow) {
            $feedbackMessage = "Data pupuk tidak ditemukan pada tabel Stok Pupuk.";
            $feedbackType    = "danger";
        } else {
            $tersediaKarung = parseKarungInt($stokRow['jumlah_karung']);
            $tersediaBerat  = parseBeratFloat($stokRow['jumlah_berat']);
            if ($reqKarung > $tersediaKarung) {
                $feedbackMessage = "Stok karung untuk {$stokRow['nama_pupuk']} tidak mencukupi! Stok tersedia: {$stokRow['jumlah_karung']}, diminta: {$reqKarung} Karung.";
                $feedbackType    = "danger";
            } elseif ($reqBerat > ($tersediaBerat + 0.01)) {
                $feedbackMessage = "Stok berat untuk {$stokRow['nama_pupuk']} tidak mencukupi! Stok tersedia: {$stokRow['jumlah_berat']}, diminta: " . formatBeratStr($reqBerat) . ".";
                $feedbackType    = "danger";
            } else {
                $nama_petani   = formatTitleCase($petaniRow['nama_petani']);
                $pupuk_id      = $stokRow['pupuk_id'] ? (int)$stokRow['pupuk_id'] : null;
                $nama_pupuk    = formatTitleCase($stokRow['nama_pupuk']);
                $jumlah_karung = formatKarungStr($reqKarung);
                $jumlah_berat  = formatBeratStr($reqBerat);

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

                        // Auto-sinkronisasi: kurangi jumlah_karung dan jumlah_berat di pengelolaan_pupuk
                        adjustStokPengelolaanPupuk($conn, $pengelolaan_pupuk_id, $pupuk_id, -$reqKarung, -$reqBerat);

                        // Auto-sinkronisasi: salin ke riwayat_hutang_pupuk & catat pembayaran jika sudah ada pelunasan/DP
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
    }
} elseif ($action === 'edit_hutang') {
    $activeView           = ($returnView === 'kelola') ? 'hutang' : $returnView;
    $id                   = (int)($_POST['id'] ?? 0);
    $anggota_id           = (int)($_POST['anggota_id'] ?? 0);
    $pengelolaan_pupuk_id = (int)($_POST['pengelolaan_pupuk_id'] ?? 0);
    $jumlah_karung_raw    = trim($_POST['jumlah_karung'] ?? '');
    $jumlah_berat_raw     = trim($_POST['jumlah_berat'] ?? '');
    $harga_raw            = trim($_POST['harga'] ?? ($_POST['jumlah_harga'] ?? ''));
    $jumlah_hutang_raw    = trim($_POST['jumlah_hutang'] ?? '');
    $sisa_hutang_raw      = trim($_POST['sisa_hutang'] ?? '');
    $tanggal_raw          = trim($_POST['tanggal'] ?? '');
    $keterangan           = formatTitleCase($_POST['keterangan'] ?? '');
    $status               = $_POST['status'] ?? 'Belum Lunas';

    if (!in_array($status, ['Belum Lunas', 'Lunas'], true)) {
        $status = 'Belum Lunas';
    }

    $newKarung        = parseKarungInt($jumlah_karung_raw);
    $newBerat         = parseBeratFloat($jumlah_berat_raw);
    $harga            = formatRupiahStr($harga_raw);
    $hargaFloat       = parseRupiahFloat($harga);
    $calcTotalHtg     = $newKarung * $hargaFloat;
    $jumlah_hutang    = ($jumlah_hutang_raw !== '' && parseRupiahFloat($jumlah_hutang_raw) > 0)
        ? formatRupiahStr($jumlah_hutang_raw)
        : formatRupiahStr($calcTotalHtg);
    $totalHutangFloat = parseRupiahFloat($jumlah_hutang);

    $tanggal = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_raw) ? $tanggal_raw : date('Y-m-d');

    if ($id <= 0 || $anggota_id <= 0 || $pengelolaan_pupuk_id <= 0 || $newKarung <= 0 || $newBerat <= 0 || $hargaFloat <= 0 || $totalHutangFloat <= 0) {
        $feedbackMessage = "ID, Nama Petani, Nama Pupuk, Jumlah Karung (> 0), Jumlah Berat (> 0), dan Harga Satuan (> 0) wajib diisi.";
        $feedbackType    = "danger";
    } else {
        if ($keterangan === '') {
            $keterangan = '-';
        }

        // Ambil data hutang_pupuk lama untuk menghitung selisih stok
        $oldStmt = $conn->prepare("SELECT * FROM hutang_pupuk WHERE id = ? LIMIT 1");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRes = $oldStmt->get_result();
        $oldRow = $oldRes ? $oldRes->fetch_assoc() : null;
        $oldStmt->close();

        // Ambil data petani
        $petaniStmt = $conn->prepare("SELECT id, nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
        $petaniStmt->bind_param("i", $anggota_id);
        $petaniStmt->execute();
        $petaniRes = $petaniStmt->get_result();
        $petaniRow = $petaniRes ? $petaniRes->fetch_assoc() : null;
        $petaniStmt->close();

        // Ambil data pengelolaan_pupuk baru
        $stokStmt = $conn->prepare("
            SELECT p.id, p.pupuk_id, COALESCE(m.nama_produk, p.nama_pupuk) AS nama_pupuk, p.jumlah_karung, p.jumlah_berat
            FROM pengelolaan_pupuk p
            LEFT JOIN master_produk m ON p.pupuk_id = m.id AND m.jenis = 'Pupuk'
            WHERE p.id = ? LIMIT 1
        ");
        $stokStmt->bind_param("i", $pengelolaan_pupuk_id);
        $stokStmt->execute();
        $stokRes = $stokStmt->get_result();
        $stokRow = $stokRes ? $stokRes->fetch_assoc() : null;
        $stokStmt->close();

        if (!$oldRow) {
            $feedbackMessage = "Data hutang piutang yang akan diedit tidak ditemukan.";
            $feedbackType    = "danger";
        } elseif (!$petaniRow) {
            $feedbackMessage = "Nama petani tidak ditemukan pada Daftar Anggota Petani.";
            $feedbackType    = "danger";
        } elseif (!$stokRow) {
            $feedbackMessage = "Data pupuk tidak ditemukan pada Stok Pupuk.";
            $feedbackType    = "danger";
        } else {
            $oldPengelolaanId = $oldRow['pengelolaan_pupuk_id'] ? (int)$oldRow['pengelolaan_pupuk_id'] : null;
            $oldPupukId       = $oldRow['pupuk_id'] ? (int)$oldRow['pupuk_id'] : null;
            if (!$oldPengelolaanId && $oldPupukId) {
                $qOldStok = $conn->prepare("SELECT id FROM pengelolaan_pupuk WHERE pupuk_id = ? ORDER BY id ASC LIMIT 1");
                if ($qOldStok) {
                    $qOldStok->bind_param("i", $oldPupukId);
                    $qOldStok->execute();
                    $rOldStok = $qOldStok->get_result();
                    if ($rOldStok && ($rowOldStok = $rOldStok->fetch_assoc())) {
                        $oldPengelolaanId = (int)$rowOldStok['id'];
                    }
                    $qOldStok->close();
                }
            }
            $oldKarung = parseKarungInt($oldRow['jumlah_karung']);
            $oldBerat  = parseBeratFloat($oldRow['jumlah_berat']);

            $tersediaSekarang      = parseKarungInt($stokRow['jumlah_karung']);
            $tersediaBeratSekarang = parseBeratFloat($stokRow['jumlah_berat']);
            $isSameStockRow        = ($oldPengelolaanId === $pengelolaan_pupuk_id);
            $batasMaksKarung       = $isSameStockRow ? ($tersediaSekarang + $oldKarung) : $tersediaSekarang;
            $batasMaksBerat        = $isSameStockRow ? ($tersediaBeratSekarang + $oldBerat) : $tersediaBeratSekarang;

            if ($newKarung > $batasMaksKarung) {
                $feedbackMessage = "Stok karung untuk {$stokRow['nama_pupuk']} tidak mencukupi! Maksimal karung yang dapat digunakan: {$batasMaksKarung} Karung.";
                $feedbackType    = "danger";
            } elseif ($newBerat > ($batasMaksBerat + 0.01)) {
                $feedbackMessage = "Stok berat untuk {$stokRow['nama_pupuk']} tidak mencukupi! Maksimal berat yang dapat digunakan: " . formatBeratStr($batasMaksBerat) . ".";
                $feedbackType    = "danger";
            } else {
                $nama_petani   = formatTitleCase($petaniRow['nama_petani']);
                $pupuk_id      = $stokRow['pupuk_id'] ? (int)$stokRow['pupuk_id'] : null;
                $nama_pupuk    = formatTitleCase($stokRow['nama_pupuk']);
                $jumlah_karung = formatKarungStr($newKarung);
                $jumlah_berat  = formatBeratStr($newBerat);

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

                // 1. Kembalikan stok lama terlebih dahulu
                adjustStokPengelolaanPupuk($conn, $oldPengelolaanId, $oldPupukId, +$oldKarung, +$oldBerat);

                // 2. Kurangi stok baru
                adjustStokPengelolaanPupuk($conn, $pengelolaan_pupuk_id, $pupuk_id, -$newKarung, -$newBerat);

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
                        // 4. Sinkronkan ke riwayat_hutang_pupuk & pembayaran_hutang_pupuk
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
            $oldPengelolaanId = $oldRow['pengelolaan_pupuk_id'] ? (int)$oldRow['pengelolaan_pupuk_id'] : null;
            $oldPupukId       = $oldRow['pupuk_id'] ? (int)$oldRow['pupuk_id'] : null;
            $oldKarung        = parseKarungInt($oldRow['jumlah_karung']);
            $oldBerat         = parseBeratFloat($oldRow['jumlah_berat']);
            $karungLabel      = $oldRow['jumlah_karung'];

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
                    // Auto-sinkronisasi: kembalikan jumlah_karung & jumlah_berat ke pengelolaan_pupuk
                    adjustStokPengelolaanPupuk($conn, $oldPengelolaanId, $oldPupukId, +$oldKarung, +$oldBerat);

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
