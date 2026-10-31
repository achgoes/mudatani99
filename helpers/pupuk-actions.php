<?php
/**
 * mudatani99 - Form Action Handler (CRUD Stok Pupuk & Hutang Piutang)
 * File: helpers/pupuk-actions.php
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nonaktifkan trigger sesi MySQL agar sinkronisasi PHP yang tervalidasi tidak mengeksekusi pengurangan 2 kali
    $conn->query("SET @disable_hutang_trigger = 1");

    $action     = $_POST['action'] ?? '';
    $returnView = $_POST['return_view'] ?? $activeView;
    if (!in_array($returnView, ['kelola', 'hutang', 'riwayat'], true)) {
        $returnView = 'kelola';
    }
    $viewQuery = ($returnView !== 'kelola') ? "view=" . urlencode($returnView) . "&" : "";

    // =====================================================================
    // A. CRUD STOK PUPUK (PENGELOLAAN PUPUK)
    // =====================================================================
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
                            header("Location: pupuk.php?status=stock_added&name=" . urlencode($nama_pupuk) . "&add_k=" . $addKarung . "&tot_k=" . $totalKarung . "&tot_b=" . urlencode($newBeratStr));
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
                            $insStmt->close();
                            header("Location: pupuk.php?status=added&name=" . urlencode($nama_pupuk));
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
                $nama_pupuk = formatTitleCase($pupukRow['nama_produk']);
                $stmt = $conn->prepare("UPDATE pengelolaan_pupuk SET pupuk_id = ?, nama_pupuk = ?, jumlah_karung = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("issssi", $pupuk_id, $nama_pupuk, $jumlah_karung, $jumlah_berat, $keterangan, $id);
                    if ($stmt->execute()) {
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

                        header("Location: pupuk.php?status=updated&name=" . urlencode($nama_pupuk));
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
            $stmt = $conn->prepare("DELETE FROM pengelolaan_pupuk WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header("Location: pupuk.php?status=deleted");
                    exit;
                } else {
                    $feedbackMessage = "Gagal menghapus data pupuk: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }

    // =====================================================================
    // B. CRUD HUTANG PIUTANG & RIWAYAT PELUNASAN (DENGAN AUTO-SINKRONISASI STOK)
    // =====================================================================
    elseif ($action === 'add_hutang') {
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

        $reqKarung      = parseKarungInt($jumlah_karung_raw);
        $reqBerat       = parseBeratFloat($jumlah_berat_raw);
        $harga          = formatRupiahStr($harga_raw);
        $hargaFloat     = parseRupiahFloat($harga);
        $calcTotalHtg   = $reqKarung * $hargaFloat;
        $jumlah_hutang  = ($jumlah_hutang_raw !== '') ? formatRupiahStr($jumlah_hutang_raw) : formatRupiahStr($calcTotalHtg);

        if ($status === 'Lunas') {
            $sisa_hutang   = 'Rp 0';
            $tanggal_lunas = date('Y-m-d H:i:s');
        } else {
            $sisa_hutang   = ($sisa_hutang_raw !== '') ? formatRupiahStr($sisa_hutang_raw) : $jumlah_hutang;
            $tanggal_lunas = null;
        }

        $tanggal = (!empty($tanggal_raw)) ? $tanggal_raw : date('Y-m-d');

        if ($anggota_id <= 0 || $pengelolaan_pupuk_id <= 0 || $reqKarung <= 0 || $reqBerat <= 0) {
            $feedbackMessage = "Nama Petani, Nama Pupuk, Jumlah Karung (> 0), dan Jumlah Berat (> 0) wajib diisi dengan benar.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }

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
                if ($reqKarung > $tersediaKarung) {
                    $feedbackMessage = "Stok karung untuk {$stokRow['nama_pupuk']} tidak mencukupi! Stok tersedia: {$stokRow['jumlah_karung']}, diminta: {$reqKarung} Karung.";
                    $feedbackType    = "danger";
                } else {
                    $nama_petani   = formatTitleCase($petaniRow['nama_petani']);
                    $pupuk_id      = $stokRow['pupuk_id'] ? (int)$stokRow['pupuk_id'] : null;
                    $nama_pupuk    = formatTitleCase($stokRow['nama_pupuk']);
                    $jumlah_karung = formatKarungStr($reqKarung);
                    $jumlah_berat  = formatBeratStr($reqBerat);

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

                            // Auto-sinkronisasi: salin ke riwayat_hutang_pupuk
                            syncRiwayatHutangRow($conn, $newHutangId);

                            header("Location: pupuk.php?{$viewQuery}status=added&name=" . urlencode($nama_petani));
                            exit;
                        } else {
                            $feedbackMessage = "Gagal menyimpan data hutang piutang: " . $conn->error;
                            $feedbackType    = "danger";
                            $insStmt->close();
                        }
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

        $newKarung      = parseKarungInt($jumlah_karung_raw);
        $newBerat       = parseBeratFloat($jumlah_berat_raw);
        $harga          = formatRupiahStr($harga_raw);
        $hargaFloat     = parseRupiahFloat($harga);
        $calcTotalHtg   = $newKarung * $hargaFloat;
        $jumlah_hutang  = ($jumlah_hutang_raw !== '') ? formatRupiahStr($jumlah_hutang_raw) : formatRupiahStr($calcTotalHtg);

        $tanggal = (!empty($tanggal_raw)) ? $tanggal_raw : date('Y-m-d');

        if ($id <= 0 || $anggota_id <= 0 || $pengelolaan_pupuk_id <= 0 || $newKarung <= 0 || $newBerat <= 0) {
            $feedbackMessage = "ID, Nama Petani, Nama Pupuk, Jumlah Karung (> 0), dan Jumlah Berat (> 0) wajib diisi.";
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
                $oldKarung        = parseKarungInt($oldRow['jumlah_karung']);
                $oldBerat         = parseBeratFloat($oldRow['jumlah_berat']);

                $tersediaSekarang = parseKarungInt($stokRow['jumlah_karung']);
                $batasMaksKarung  = ($oldPengelolaanId === $pengelolaan_pupuk_id)
                    ? ($tersediaSekarang + $oldKarung)
                    : $tersediaSekarang;

                if ($newKarung > $batasMaksKarung) {
                    $feedbackMessage = "Stok karung untuk {$stokRow['nama_pupuk']} tidak mencukupi! Maksimal karung yang dapat digunakan: {$batasMaksKarung} Karung.";
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
                        $sisa_hutang   = ($sisa_hutang_raw !== '') ? formatRupiahStr($sisa_hutang_raw) : $jumlah_hutang;
                    }

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

                            $updB = $conn->prepare("UPDATE pembayaran_hutang_pupuk SET anggota_id = ?, nama_petani = ?, nama_pupuk = ? WHERE hutang_id = ?");
                            if ($updB) {
                                $updB->bind_param("issi", $anggota_id, $nama_petani, $nama_pupuk, $id);
                                $updB->execute();
                                $updB->close();
                            }
                            if ($status === 'Belum Lunas' && parseRupiahFloat($sisa_hutang) >= parseRupiahFloat($jumlah_hutang)) {
                                $delB = $conn->prepare("DELETE FROM pembayaran_hutang_pupuk WHERE hutang_id = ?");
                                if ($delB) {
                                    $delB->bind_param("i", $id);
                                    $delB->execute();
                                    $delB->close();
                                }
                            } else {
                                ensureBackfillPembayaranHutang($conn);
                            }

                            header("Location: pupuk.php?{$viewQuery}status=updated&name=" . urlencode($nama_petani));
                            exit;
                        } else {
                            $feedbackMessage = "Gagal memperbarui data hutang: " . $conn->error;
                            $feedbackType    = "danger";
                            $updStmt->close();
                        }
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

                        header("Location: pupuk.php?{$viewQuery}status=deleted&karung=" . urlencode($karungLabel));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menghapus data hutang: " . $conn->error;
                        $feedbackType    = "danger";
                        $delH->close();
                    }
                }
            }
        }
    } elseif ($action === 'toggle_status_hutang') {
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

            header("Location: pupuk.php?{$viewQuery}status=status_changed&name=" . urlencode($petaniName) . "&st=" . urlencode($newStatus));
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

                    header("Location: pupuk.php?{$viewQuery}status=paid&name=" . urlencode($petaniName) . "&nominal=" . urlencode(formatRupiahStr($nomBayarVal)) . "&st=" . urlencode($newStatus));
                    exit;
                }
                $stmt->close();
            }
        }
    }
}

